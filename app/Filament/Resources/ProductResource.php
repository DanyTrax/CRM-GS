<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    
    protected static ?string $navigationLabel = 'Productos';
    
    protected static ?string $modelLabel = 'Producto';
    
    protected static ?string $pluralModelLabel = 'Productos';
    
    protected static ?string $navigationGroup = 'Gestión';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Producto')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del Producto')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Office 365 Business Standard')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if (empty($set('slug'))) {
                                    $set('slug', \Illuminate\Support\Str::slug($state));
                                }
                            }),
                        
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL amigable del producto'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Tipo y Duración')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Tipo de Producto')
                            ->options([
                                'one_time' => 'Un Solo Consumo',
                                'recurring' => 'Con Tiempo Determinado',
                            ])
                            ->required()
                            ->default('recurring')
                            ->live()
                            ->helperText('Un solo consumo: se factura una vez. Con tiempo: se renueva periódicamente.'),
                        
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('duration_value')
                                    ->label('Duración (Valor)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->visible(fn ($get) => $get('type') === 'recurring')
                                    ->required(fn ($get) => $get('type') === 'recurring')
                                    ->helperText('Ej: 1, 12, 24, etc.'),
                                
                                Forms\Components\Select::make('duration_unit')
                                    ->label('Unidad de Duración')
                                    ->options([
                                        'days' => 'Días',
                                        'months' => 'Meses',
                                        'years' => 'Años',
                                    ])
                                    ->default('months')
                                    ->visible(fn ($get) => $get('type') === 'recurring')
                                    ->required(fn ($get) => $get('type') === 'recurring')
                                    ->helperText('Unidad de tiempo para la duración'),
                            ])
                            ->visible(fn ($get) => $get('type') === 'recurring'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Precio e Impuestos')
                    ->schema([
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
                            ->helperText('Habilitar impuesto sobre el precio del producto'),
                        
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
                    ])->columns(2),
                
                Forms\Components\Section::make('Estado')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->helperText('Los productos inactivos no aparecerán al crear servicios'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'one_time' => 'Un Solo Consumo',
                        'recurring' => 'Con Tiempo',
                        default => $state,
                    })
                    ->colors([
                        'info' => 'one_time',
                        'success' => 'recurring',
                    ]),
                
                Tables\Columns\TextColumn::make('formatted_duration')
                    ->label('Duración')
                    ->getStateUsing(fn (Product $record) => $record->getFormattedDuration())
                    ->default('N/A'),
                
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money(fn ($record) => $record->currency)
                    ->sortable()
                    ->description(fn ($record) => 
                        $record->tax_enabled 
                            ? "IVA {$record->tax_percentage}%: " . number_format($record->getTaxAmount(), 2) . " {$record->currency}"
                            : null
                    ),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('services_count')
                    ->label('Servicios')
                    ->counts('services')
                    ->sortable(),
                
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
                        'one_time' => 'Un Solo Consumo',
                        'recurring' => 'Con Tiempo',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo')
                    ->placeholder('Todos')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
            ])
            ->actions([
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
