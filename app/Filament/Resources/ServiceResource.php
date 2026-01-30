<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use App\Models\Client;
use App\Models\Product;
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
                Forms\Components\Section::make('Origen del Servicio')
                    ->schema([
                        Forms\Components\Select::make('creation_mode')
                            ->label('Modo de Creación')
                            ->options([
                                'manual' => 'Crear Manualmente',
                                'products' => 'Seleccionar de Productos',
                            ])
                            ->default('manual')
                            ->live()
                            ->required()
                            ->helperText('Crear manualmente o seleccionar de productos predefinidos'),
                        
                        Forms\Components\Select::make('product_ids')
                            ->label('Productos')
                            ->multiple()
                            ->options(Product::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => $get('creation_mode') === 'products')
                            ->required(fn ($get) => $get('creation_mode') === 'products')
                            ->helperText('Puede seleccionar múltiples productos. Se crearán servicios separados para cada uno.')
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state && count($state) > 0) {
                                    $product = Product::find($state[0]);
                                    if ($product) {
                                        $set('name', $product->name);
                                        $set('description', $product->description);
                                        $set('currency', $product->currency);
                                        $set('price', $product->price);
                                        $set('tax_enabled', $product->tax_enabled);
                                        $set('tax_percentage', $product->tax_percentage);
                                        
                                        if ($product->type === 'recurring') {
                                            $set('type', 'recurrente');
                                            // Calcular billing_cycle basado en duration
                                            if ($product->duration_unit === 'months') {
                                                $set('billing_cycle', $product->duration_value);
                                            } elseif ($product->duration_unit === 'years') {
                                                $set('billing_cycle', $product->duration_value * 12);
                                            } else {
                                                $set('billing_cycle', 1);
                                            }
                                            // Calcular fecha de vencimiento
                                            $expirationDate = $product->calculateExpirationDate(now());
                                            $set('next_due_date', $expirationDate->toDateString());
                                        } else {
                                            $set('type', 'unico');
                                            $set('billing_cycle', 0);
                                            $set('next_due_date', now()->toDateString());
                                        }
                                    }
                                }
                            }),
                    ])->columns(1),
                
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
                            ->placeholder('Ej: Hosting 5GB')
                            ->disabled(fn ($get) => $get('creation_mode') === 'products'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->disabled(fn ($get) => $get('creation_mode') === 'products'),
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
                            ->suffix(fn ($get) => $get('currency') ?? 'COP')
                            ->helperText(fn ($get) => 
                                $get('currency') === 'USD' 
                                    ? 'El precio se cobrará en COP según la tasa de cambio configurada'
                                    : 'Precio en pesos colombianos'
                            ),
                        
                        Forms\Components\Toggle::make('tax_enabled')
                            ->label('Aplicar Impuesto')
                            ->default(false)
                            ->live()
                            ->helperText('Habilitar impuesto sobre el precio del servicio'),
                        
                        Forms\Components\TextInput::make('tax_percentage')
                            ->label('Porcentaje de Impuesto (%)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix('%')
                            ->visible(fn ($get) => $get('tax_enabled'))
                            ->required(fn ($get) => $get('tax_enabled'))
                            ->helperText('Porcentaje de impuesto a aplicar sobre el precio'),
                        
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
                            ->displayFormat('d/m/Y')
                            ->disabled(fn ($get) => $get('creation_mode') === 'products'),
                        
                        Forms\Components\Hidden::make('product_id')
                            ->default(fn ($get) => $get('product_ids') ? $get('product_ids')[0] ?? null : null),
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
                    ->sortable()
                    ->description(fn ($record) => 
                        $record->tax_enabled 
                            ? "IVA {$record->tax_percentage}%: " . number_format($record->getTaxAmount(), 2) . " {$record->currency}"
                            : null
                    ),
                
                Tables\Columns\IconColumn::make('tax_enabled')
                    ->label('Impuesto')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                
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
                Tables\Actions\Action::make('generate_invoice')
                    ->label('Generar Factura')
                    ->icon('heroicon-o-document-plus')
                    ->color('primary')
                    ->url(fn (Service $record) => route('filament.admin.resources.invoices.create', ['service_id' => $record->id]))
                    ->visible(fn (Service $record) => $record->status === 'activo'),
                
                Tables\Actions\Action::make('renew')
                    ->label('Renovar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Service $record) => $record->renew()),
                
                Tables\Actions\EditAction::make(),
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
