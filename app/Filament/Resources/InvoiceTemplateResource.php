<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceTemplateResource\Pages;
use App\Models\InvoiceTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceTemplateResource extends Resource
{
    protected static ?string $model = InvoiceTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    
    protected static ?string $navigationLabel = 'Plantillas de Factura';
    
    protected static ?string $modelLabel = 'Plantilla';
    
    protected static ?string $pluralModelLabel = 'Plantillas';
    
    protected static ?string $navigationGroup = 'Facturación';
    
    protected static ?int $navigationSort = 3;
    
    protected static ?string $slug = 'invoice-templates';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Plantilla')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Factura Legal Estándar'),
                        
                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'invoice' => 'Factura',
                                'remision' => 'Remisión',
                                'cuenta_cobro' => 'Cuenta de Cobro',
                            ])
                            ->required()
                            ->default('invoice'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activa')
                            ->default(true),
                        
                        Forms\Components\Toggle::make('is_default')
                            ->label('Plantilla por Defecto')
                            ->default(false)
                            ->helperText('Marcar como plantilla predeterminada para este tipo'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Contenido HTML')
                    ->schema([
                        Forms\Components\Textarea::make('html_content')
                            ->label('Contenido HTML')
                            ->required()
                            ->rows(20)
                            ->columnSpanFull()
                            ->helperText('Usa variables como {{client_name}}, {{invoice_number}}, {{total_amount}}, etc.')
                            ->extraAttributes(['style' => 'font-family: monospace;']),
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
                
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'invoice' => 'Factura',
                        'remision' => 'Remisión',
                        'cuenta_cobro' => 'Cuenta de Cobro',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'invoice',
                        'success' => 'remision',
                        'info' => 'cuenta_cobro',
                    ]),
                
                Tables\Columns\IconColumn::make('is_default')
                    ->label('Por Defecto')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
                
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
                        'invoice' => 'Factura',
                        'remision' => 'Remisión',
                        'cuenta_cobro' => 'Cuenta de Cobro',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activa')
                    ->placeholder('Todas')
                    ->trueLabel('Solo activas')
                    ->falseLabel('Solo inactivas'),
            ])
            ->actions([
                Tables\Actions\Action::make('set_default')
                    ->label('Marcar como Defecto')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (InvoiceTemplate $record) => !$record->is_default)
                    ->requiresConfirmation()
                    ->action(fn (InvoiceTemplate $record) => $record->setAsDefault()),
                
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
            'index' => Pages\ListInvoiceTemplates::route('/'),
            'create' => Pages\CreateInvoiceTemplate::route('/create'),
            'edit' => Pages\EditInvoiceTemplate::route('/{record}/edit'),
        ];
    }
}
