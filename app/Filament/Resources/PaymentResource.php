<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    
    protected static ?string $navigationLabel = 'Pagos';
    
    protected static ?string $modelLabel = 'Pago';
    
    protected static ?string $pluralModelLabel = 'Pagos';
    
    protected static ?string $navigationGroup = 'Facturación';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $slug = 'payments';
    
    public static function shouldRegisterRoutes(): bool
    {
        return true;
    }

    public static function canViewAny(): bool
    {
        // Temporalmente permitir a todos, luego agregar permisos por roles
        return true;
        
        // Código original comentado para referencia:
        // $user = auth()->user();
        // if (!$user) {
        //     return false;
        // }
        // 
        // if (!$user->relationLoaded('role')) {
        //     $user->load('role');
        // }
        // 
        // if (!$user->role) {
        //     return false;
        // }
        // 
        // return in_array($user->role->slug ?? '', ['super-admin', 'admin-operativo', 'contador']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Pago')
                    ->schema([
                        Forms\Components\Select::make('invoice_id')
                            ->label('Factura')
                            ->relationship('invoice', 'invoice_number')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state) {
                                    $invoice = \App\Models\Invoice::find($state);
                                    if ($invoice) {
                                        // Pre-llenar el monto con el total de la factura
                                        $set('amount_paid', $invoice->total_amount);
                                    }
                                }
                            }),
                        
                        Forms\Components\Select::make('method')
                            ->label('Método de Pago')
                            ->options([
                                'Bold' => 'Bold (Automático)',
                                'Transferencia' => 'Transferencia Bancaria',
                                'Efectivo' => 'Efectivo',
                            ])
                            ->default('Bold')
                            ->required()
                            ->live(),
                        
                        Forms\Components\TextInput::make('transaction_id')
                            ->label('ID de Transacción')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get) => $get('method') === 'Bold')
                            ->helperText('ID de transacción de Bold (se llena automáticamente desde webhook)'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Monto y Aprobación')
                    ->schema([
                        Forms\Components\TextInput::make('amount_paid')
                            ->label('Monto Pagado')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->step(0.01),
                        
                        Forms\Components\FileUpload::make('proof_file')
                            ->label('Comprobante de Pago')
                            ->image()
                            ->directory('payments/proofs')
                            ->visibility('private')
                            ->visible(fn (Forms\Get $get) => in_array($get('method'), ['Transferencia', 'Efectivo']))
                            ->helperText('Subir imagen del comprobante de pago'),
                        
                        Forms\Components\DateTimePicker::make('approved_at')
                            ->label('Fecha de Aprobación')
                            ->default(now())
                            ->visible(fn (Forms\Get $get) => $get('method') !== 'Bold')
                            ->helperText('Para pagos manuales, marcar cuando se aprueba'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->label('Factura')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('invoice.client.company_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Monto')
                    ->money('COP')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('method')
                    ->label('Método')
                    ->badge()
                    ->colors([
                        'success' => 'Bold',
                        'info' => 'Transferencia',
                        'warning' => 'Efectivo',
                    ]),
                
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('ID Transacción')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('approved_at')
                    ->label('Aprobado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Fecha Aprobación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('method')
                    ->label('Método')
                    ->options([
                        'Bold' => 'Bold',
                        'Transferencia' => 'Transferencia',
                        'Efectivo' => 'Efectivo',
                    ]),
                
                Tables\Filters\TernaryFilter::make('approved_at')
                    ->label('Aprobado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo aprobados')
                    ->falseLabel('Solo pendientes')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('approved_at'),
                        false: fn (Builder $query) => $query->whereNull('approved_at'),
                    ),
                
                Tables\Filters\SelectFilter::make('invoice_id')
                    ->label('Factura')
                    ->relationship('invoice', 'invoice_number')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Payment $record) => !$record->isApproved())
                    ->requiresConfirmation()
                    ->action(fn (Payment $record) => $record->approve()),
                
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
