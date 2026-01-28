<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'Facturas';
    
    protected static ?string $modelLabel = 'Factura';
    
    protected static ?string $pluralModelLabel = 'Facturas';
    
    protected static ?string $navigationGroup = 'Facturación';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $slug = 'invoices';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        if (!$user->role) {
            return false;
        }
        
        return in_array($user->role->slug ?? '', ['super-admin', 'admin-operativo', 'contador']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de Factura')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Cliente')
                            ->relationship('client', 'company_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('company_name')
                                    ->label('Razón Social')
                                    ->required(),
                                Forms\Components\TextInput::make('email_login')
                                    ->label('Email')
                                    ->email()
                                    ->required(),
                            ]),
                        
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Número de Factura')
                            ->default(fn () => Invoice::generateInvoiceNumber())
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        Forms\Components\Select::make('pdf_template')
                            ->label('Plantilla PDF')
                            ->options([
                                'legal' => 'Factura Legal',
                                'cuenta_cobro' => 'Cuenta de Cobro',
                            ])
                            ->default('legal')
                            ->required(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Detalles Financieros')
                    ->schema([
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Monto Total')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->step(0.01),
                        
                        Forms\Components\Select::make('currency')
                            ->label('Moneda')
                            ->options([
                                'COP' => 'COP (Pesos Colombianos)',
                                'USD' => 'USD (Dólares)',
                            ])
                            ->default('COP')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state === 'USD') {
                                    // Calcular TRM automáticamente desde settings
                                    $trmBase = \App\Models\Setting::get('trm_base', 4000);
                                    $spread = \App\Models\Setting::get('bold_spread_percentage', 3);
                                    $trmWithSpread = $trmBase * (1 + ($spread / 100));
                                    $set('trm_snapshot', round($trmWithSpread, 4));
                                } else {
                                    $set('trm_snapshot', null);
                                }
                            }),
                        
                        Forms\Components\TextInput::make('trm_snapshot')
                            ->label('TRM (Tasa de Cambio)')
                            ->numeric()
                            ->visible(fn (Forms\Get $get) => $get('currency') === 'USD')
                            ->step(0.0001)
                            ->default(fn (Forms\Get $get) => 
                                $get('currency') === 'USD' 
                                    ? round(\App\Models\Setting::get('trm_base', 4000) * (1 + (\App\Models\Setting::get('bold_spread_percentage', 3) / 100)), 4)
                                    : null
                            )
                            ->helperText('Tasa de cambio con spread aplicado (se calcula automáticamente)'),
                    ])->columns(3),
                
                Forms\Components\Section::make('Fechas')
                    ->schema([
                        Forms\Components\DatePicker::make('issue_date')
                            ->label('Fecha de Emisión')
                            ->default(now())
                            ->required(),
                        
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Fecha de Vencimiento')
                            ->default(now()->addDays(30))
                            ->required(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Estado y Concepto')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'borrador' => 'Borrador',
                                'pendiente' => 'Pendiente',
                                'pagada' => 'Pagada',
                                'anulada' => 'Anulada',
                            ])
                            ->default('borrador')
                            ->required(),
                        
                        Forms\Components\Textarea::make('concept')
                            ->label('Concepto')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Número')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('client.company_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Monto')
                    ->money(fn ($record) => $record->currency === 'USD' ? 'USD' : 'COP')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('currency')
                    ->label('Moneda')
                    ->badge()
                    ->colors([
                        'success' => 'COP',
                        'warning' => 'USD',
                    ]),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'gray' => 'borrador',
                        'warning' => 'pendiente',
                        'success' => 'pagada',
                        'danger' => 'anulada',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'borrador' => 'Borrador',
                        'pendiente' => 'Pendiente',
                        'pagada' => 'Pagada',
                        'anulada' => 'Anulada',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('issue_date')
                    ->label('Emisión')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Vencimiento')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->due_date < now() && $record->status !== 'pagada' ? 'danger' : null),
                
                Tables\Columns\TextColumn::make('payments_count')
                    ->label('Pagos')
                    ->counts('payments')
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'borrador' => 'Borrador',
                        'pendiente' => 'Pendiente',
                        'pagada' => 'Pagada',
                        'anulada' => 'Anulada',
                    ]),
                
                Tables\Filters\SelectFilter::make('currency')
                    ->label('Moneda')
                    ->options([
                        'COP' => 'COP',
                        'USD' => 'USD',
                    ]),
                
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'company_name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (Invoice $record) => route('admin.invoices.pdf', $record))
                    ->openUrlInNewTab()
                    ->color('success'),
                
                Tables\Actions\Action::make('mark_paid')
                    ->label('Marcar Pagada')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Invoice $record) => $record->status !== 'pagada')
                    ->requiresConfirmation()
                    ->action(function (Invoice $record) {
                        $record->markAsPaid();
                        \Filament\Notifications\Notification::make()
                            ->title('Factura marcada como pagada')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
