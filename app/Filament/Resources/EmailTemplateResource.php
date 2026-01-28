<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailTemplateResource\Pages;
use App\Models\EmailTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    
    protected static ?string $navigationLabel = 'Plantillas de Email';
    
    protected static ?string $modelLabel = 'Plantilla de Email';
    
    protected static ?string $pluralModelLabel = 'Plantillas de Email';
    
    protected static ?string $navigationGroup = 'Configuración';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $slug = 'email-templates';

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
                            ->placeholder('Ej: Bienvenida - Cliente'),
                        
                        Forms\Components\Select::make('type')
                            ->label('Tipo de Plantilla')
                            ->options([
                                'welcome' => 'Bienvenida',
                                'password_reset' => 'Creación/Recuperación de Contraseña',
                                'service_expiring' => 'Próximo Vencimiento',
                                'product_purchased' => 'Producto Único Adquirido',
                                'service_expired_grace' => 'Servicio Vencido - Periodo de Gracia',
                                'service_expired_suspended' => 'Servicio Vencido y Suspendido',
                                'service_cancelled' => 'Servicio Cancelado',
                                'invoice_created' => 'Factura Creada',
                                'payment_received' => 'Pago Recibido',
                                'ticket_created' => 'Ticket Creado',
                                'ticket_replied' => 'Respuesta a Ticket',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Si cambia el tipo, limpiar variación
                                $set('is_variation', false);
                                $set('parent_template_id', null);
                            }),
                        
                        Forms\Components\Select::make('recipient_type')
                            ->label('Destinatario')
                            ->options([
                                'user' => 'Usuario (Cliente)',
                                'admin' => 'Administrativo',
                                'both' => 'Ambos',
                            ])
                            ->required()
                            ->default('user'),
                        
                        Forms\Components\Toggle::make('is_variation')
                            ->label('Es una Variación')
                            ->default(false)
                            ->live()
                            ->helperText('Marcar si esta es una variación de otra plantilla'),
                        
                        Forms\Components\Select::make('parent_template_id')
                            ->label('Plantilla Padre')
                            ->relationship('parentTemplate', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Forms\Get $get) => $get('is_variation'))
                            ->required(fn (Forms\Get $get) => $get('is_variation')),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activa')
                            ->default(true),
                        
                        Forms\Components\Toggle::make('auto_send')
                            ->label('Envío Automático')
                            ->default(true)
                            ->helperText('Enviar automáticamente cuando se cumplan las condiciones'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Contenido del Email')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label('Asunto')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Bienvenido a {{company_name}}')
                            ->helperText('Usa variables como {{client_name}}, {{service_name}}, etc.'),
                        
                        Forms\Components\RichEditor::make('body')
                            ->label('Cuerpo del Email (HTML)')
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'link',
                                'bulletList',
                                'orderedList',
                                'blockquote',
                            ])
                            ->helperText('Usa variables como {{client_name}}, {{service_name}}, {{due_date}}, etc.'),
                    ]),
                
                Forms\Components\Section::make('Variables Disponibles')
                    ->schema([
                        Forms\Components\Textarea::make('variables')
                            ->label('Variables (JSON)')
                            ->rows(5)
                            ->columnSpanFull()
                            ->helperText('Lista de variables disponibles para este tipo de plantilla (formato JSON)')
                            ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state)
                            ->dehydrateStateUsing(fn ($state) => is_string($state) ? json_decode($state, true) : $state),
                    ])
                    ->collapsible()
                    ->collapsed(),
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
                        'welcome' => 'Bienvenida',
                        'password_reset' => 'Contraseña',
                        'service_expiring' => 'Próximo Vencimiento',
                        'product_purchased' => 'Producto Adquirido',
                        'service_expired_grace' => 'Vencido - Gracia',
                        'service_expired_suspended' => 'Vencido - Suspendido',
                        'service_cancelled' => 'Cancelado',
                        'invoice_created' => 'Factura Creada',
                        'payment_received' => 'Pago Recibido',
                        'ticket_created' => 'Ticket Creado',
                        'ticket_replied' => 'Respuesta Ticket',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'welcome',
                        'success' => 'password_reset',
                        'warning' => 'service_expiring',
                        'info' => 'product_purchased',
                        'danger' => 'service_expired_grace',
                        'danger' => 'service_expired_suspended',
                        'gray' => 'service_cancelled',
                    ]),
                
                Tables\Columns\TextColumn::make('recipient_type')
                    ->label('Destinatario')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'user' => 'Cliente',
                        'admin' => 'Admin',
                        'both' => 'Ambos',
                        default => $state,
                    })
                    ->colors([
                        'info' => 'user',
                        'warning' => 'admin',
                        'success' => 'both',
                    ]),
                
                Tables\Columns\IconColumn::make('is_variation')
                    ->label('Variación')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('auto_send')
                    ->label('Auto Envío')
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
                        'welcome' => 'Bienvenida',
                        'password_reset' => 'Contraseña',
                        'service_expiring' => 'Próximo Vencimiento',
                        'product_purchased' => 'Producto Adquirido',
                        'service_expired_grace' => 'Vencido - Gracia',
                        'service_expired_suspended' => 'Vencido - Suspendido',
                        'service_cancelled' => 'Cancelado',
                    ]),
                
                Tables\Filters\SelectFilter::make('recipient_type')
                    ->label('Destinatario')
                    ->options([
                        'user' => 'Cliente',
                        'admin' => 'Admin',
                        'both' => 'Ambos',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_variation')
                    ->label('Variación')
                    ->placeholder('Todas')
                    ->trueLabel('Solo variaciones')
                    ->falseLabel('Solo plantillas principales'),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activa')
                    ->placeholder('Todas')
                    ->trueLabel('Solo activas')
                    ->falseLabel('Solo inactivas'),
            ])
            ->actions([
                Tables\Actions\Action::make('create_variation')
                    ->label('Crear Variación')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->visible(fn (EmailTemplate $record) => !$record->is_variation)
                    ->url(fn (EmailTemplate $record) => route('filament.admin.resources.email-templates.create', ['parent' => $record->id])),
                
                Tables\Actions\Action::make('preview')
                    ->label('Vista Previa')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalContent(fn (EmailTemplate $record) => view('filament.partials.email-preview', ['template' => $record]))
                    ->modalHeading(fn (EmailTemplate $record) => 'Vista Previa: ' . $record->name)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),
                
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
            'index' => Pages\ListEmailTemplates::route('/'),
            'create' => Pages\CreateEmailTemplate::route('/create'),
            'edit' => Pages\EditEmailTemplate::route('/{record}/edit'),
        ];
    }
}
