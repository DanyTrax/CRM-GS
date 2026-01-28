<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    
    protected static ?string $navigationLabel = 'Servicios';
    
    protected static ?string $modelLabel = 'Servicio';
    
    protected static ?string $pluralModelLabel = 'Servicios';
    
    protected static ?string $navigationGroup = 'Gestión';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Servicio')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Cliente')
                            ->relationship('client', 'company_name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del Servicio')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Hosting 5GB'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3),
                    ])->columns(1),
                
                Forms\Components\Section::make('Configuración de Facturación')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'unico' => 'Único',
                                'recurrente' => 'Recurrente',
                            ])
                            ->required()
                            ->default('recurrente')
                            ->live()
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => 
                                $state === 'unico' ? $set('billing_cycle', 0) : null
                            ),
                        
                        Forms\Components\Select::make('currency')
                            ->label('Moneda')
                            ->options([
                                'COP' => 'COP (Pesos Colombianos)',
                                'USD' => 'USD (Dólares)',
                            ])
                            ->required()
                            ->default('COP'),
                        
                        Forms\Components\TextInput::make('price')
                            ->label('Precio')
                            ->numeric()
                            ->required()
                            ->prefix(fn ($get) => $get('currency') === 'USD' ? '$' : '$')
                            ->suffix(fn ($get) => $get('currency') ?? 'COP'),
                        
                        Forms\Components\Select::make('billing_cycle')
                            ->label('Ciclo de Facturación (meses)')
                            ->options([
                                1 => '1 mes',
                                3 => '3 meses',
                                6 => '6 meses',
                                12 => '12 meses',
                            ])
                            ->default(1)
                            ->required()
                            ->visible(fn ($get) => $get('type') === 'recurrente'),
                        
                        Forms\Components\DatePicker::make('next_due_date')
                            ->label('Próxima Fecha de Vencimiento')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Estado')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'activo' => 'Activo',
                                'suspendido' => 'Suspendido',
                                'cancelado' => 'Cancelado',
                            ])
                            ->default('activo')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.company_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Servicio')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'unico' => 'Único',
                        'recurrente' => 'Recurrente',
                        default => $state,
                    })
                    ->colors([
                        'info' => 'unico',
                        'success' => 'recurrente',
                    ]),
                
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money(fn ($record) => $record->currency)
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('next_due_date')
                    ->label('Próximo Vencimiento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => 
                        Carbon::parse($record->next_due_date)->isPast() ? 'danger' : 
                        (Carbon::parse($record->next_due_date)->diffInDays(now()) <= 7 ? 'warning' : 'success')
                    ),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'success' => 'activo',
                        'warning' => 'suspendido',
                        'danger' => 'cancelado',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'activo' => 'Activo',
                        'suspendido' => 'Suspendido',
                        'cancelado' => 'Cancelado',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'unico' => 'Único',
                        'recurrente' => 'Recurrente',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'activo' => 'Activo',
                        'suspendido' => 'Suspendido',
                        'cancelado' => 'Cancelado',
                    ]),
                
                Tables\Filters\Filter::make('next_due_date')
                    ->label('Próximo a Vencer')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('next_due_date', '<=', now()->addDays(7))
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('renew')
                    ->label('Renovar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Service $record) => $record->renew()),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('next_due_date', 'asc');
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
