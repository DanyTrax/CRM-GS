<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    
    protected static ?string $navigationLabel = 'Medios de Pago';
    
    protected static ?string $modelLabel = 'Medio de Pago';
    
    protected static ?string $pluralModelLabel = 'Medios de Pago';
    
    protected static ?string $navigationGroup = 'Facturación';
    
    protected static ?int $navigationSort = 3;
    
    protected static ?string $slug = 'payment-methods';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información General')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Bold, Transferencia Bancaria, Efectivo')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if (empty($state)) {
                                    return;
                                }
                                // Generar slug automáticamente
                                $set('slug', Str::slug($state));
                            }),
                        
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug (Identificador)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Identificador único del medio de pago (se genera automáticamente)'),
                        
                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'automatic' => 'Automático (Gateway)',
                                'manual' => 'Manual',
                                'gateway' => 'Pasarela de Pago',
                            ])
                            ->required()
                            ->default('manual')
                            ->live(),
                        
                        Forms\Components\Select::make('provider')
                            ->label('Proveedor')
                            ->options([
                                'bold' => 'Bold',
                                'payu' => 'PayU',
                                'stripe' => 'Stripe',
                                'paypal' => 'PayPal',
                                'mercadopago' => 'Mercado Pago',
                            ])
                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['automatic', 'gateway']))
                            ->required(fn (Forms\Get $get) => in_array($get('type'), ['automatic', 'gateway'])),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                        
                        Forms\Components\Toggle::make('is_default')
                            ->label('Medio de Pago por Defecto')
                            ->default(false)
                            ->helperText('Marcar como medio de pago predeterminado'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Configuración de Comisiones')
                    ->schema([
                        Forms\Components\TextInput::make('fee_percentage')
                            ->label('Comisión Porcentual (%)')
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->suffix('%')
                            ->helperText('Porcentaje de comisión sobre el monto'),
                        
                        Forms\Components\TextInput::make('fee_fixed')
                            ->label('Comisión Fija')
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->prefix('$')
                            ->helperText('Comisión fija independiente del monto'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Límites de Monto')
                    ->schema([
                        Forms\Components\TextInput::make('min_amount')
                            ->label('Monto Mínimo')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('$')
                            ->helperText('Monto mínimo permitido (dejar vacío para sin límite)'),
                        
                        Forms\Components\TextInput::make('max_amount')
                            ->label('Monto Máximo')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('$')
                            ->helperText('Monto máximo permitido (dejar vacío para sin límite)'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Configuración de Monedas')
                    ->schema([
                        Forms\Components\CheckboxList::make('accepted_currencies')
                            ->label('Monedas Aceptadas')
                            ->options([
                                'COP' => 'COP (Pesos Colombianos)',
                                'USD' => 'USD (Dólares)',
                            ])
                            ->columns(2)
                            ->helperText('Seleccionar las monedas que acepta este medio de pago'),
                    ]),
                
                Forms\Components\Section::make('Configuración de Aprobación')
                    ->schema([
                        Forms\Components\Toggle::make('requires_approval')
                            ->label('Requiere Aprobación Manual')
                            ->default(false)
                            ->helperText('Los pagos con este medio requieren aprobación manual'),
                        
                        Forms\Components\Toggle::make('auto_approve')
                            ->label('Aprobación Automática')
                            ->default(false)
                            ->helperText('Aprobar automáticamente los pagos (solo para medios automáticos)')
                            ->visible(fn (Forms\Get $get) => $get('type') === 'automatic'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Configuración de Gateway (Solo para medios automáticos)')
                    ->schema([
                        Forms\Components\KeyValue::make('configuration')
                            ->label('Configuración')
                            ->keyLabel('Clave')
                            ->valueLabel('Valor')
                            ->columnSpanFull()
                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['automatic', 'gateway']))
                            ->helperText('Configuración específica del proveedor (API keys, tokens, etc.)'),
                        
                        Forms\Components\TextInput::make('webhook_url')
                            ->label('URL de Webhook')
                            ->url()
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['automatic', 'gateway']))
                            ->helperText('URL donde el proveedor enviará notificaciones de pago'),
                        
                        Forms\Components\TextInput::make('webhook_secret')
                            ->label('Secret de Webhook')
                            ->password()
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['automatic', 'gateway']))
                            ->helperText('Secret para validar las peticiones del webhook'),
                    ])
                    ->visible(fn (Forms\Get $get) => in_array($get('type'), ['automatic', 'gateway'])),
                
                Forms\Components\Section::make('Información para el Cliente')
                    ->schema([
                        Forms\Components\Textarea::make('instructions')
                            ->label('Instrucciones')
                            ->rows(4)
                            ->columnSpanFull()
                            ->helperText('Instrucciones que se mostrarán al cliente para realizar el pago'),
                        
                        Forms\Components\TextInput::make('icon')
                            ->label('Icono/Imagen')
                            ->maxLength(255)
                            ->helperText('URL o ruta del icono/imagen del medio de pago'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'automatic' => 'Automático',
                        'manual' => 'Manual',
                        'gateway' => 'Pasarela',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'automatic',
                        'info' => 'manual',
                        'primary' => 'gateway',
                    ]),
                
                Tables\Columns\TextColumn::make('provider')
                    ->label('Proveedor')
                    ->badge()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('fee_info')
                    ->label('Comisión')
                    ->getStateUsing(function (PaymentMethod $record) {
                        $fees = [];
                        if ($record->fee_percentage > 0) {
                            $fees[] = $record->fee_percentage . '%';
                        }
                        if ($record->fee_fixed > 0) {
                            $fees[] = '$' . number_format($record->fee_fixed, 2);
                        }
                        return $fees ? implode(' + ', $fees) : 'Sin comisión';
                    })
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('is_default')
                    ->label('Por Defecto')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('payments_count')
                    ->label('Pagos')
                    ->counts('payments')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'automatic' => 'Automático',
                        'manual' => 'Manual',
                        'gateway' => 'Pasarela',
                    ]),
                
                Tables\Filters\SelectFilter::make('provider')
                    ->label('Proveedor')
                    ->options([
                        'bold' => 'Bold',
                        'payu' => 'PayU',
                        'stripe' => 'Stripe',
                        'paypal' => 'PayPal',
                        'mercadopago' => 'Mercado Pago',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo')
                    ->placeholder('Todas')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
            ])
            ->actions([
                Tables\Actions\Action::make('set_default')
                    ->label('Marcar como Defecto')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (PaymentMethod $record) => !$record->is_default)
                    ->requiresConfirmation()
                    ->action(function (PaymentMethod $record) {
                        $record->setAsDefault();
                        Notification::make()
                            ->title('Medio de pago actualizado')
                            ->body("{$record->name} ahora es el medio de pago por defecto")
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
            ->defaultSort('is_default', 'desc')
            ->defaultSort('name', 'asc');
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
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
