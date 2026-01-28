<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationSettingResource\Pages;
use App\Models\NotificationSetting;
use App\Models\EmailTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationSettingResource extends Resource
{
    protected static ?string $model = NotificationSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    
    protected static ?string $navigationLabel = 'Configuración de Notificaciones';
    
    protected static ?string $modelLabel = 'Configuración de Notificación';
    
    protected static ?string $pluralModelLabel = 'Configuraciones de Notificaciones';
    
    protected static ?string $navigationGroup = 'Mensajería';
    
    protected static ?int $navigationSort = 3;
    
    protected static ?string $slug = 'notification-settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Notificación')
                    ->schema([
                        Forms\Components\Select::make('module')
                            ->label('Módulo')
                            ->options([
                                'service' => 'Servicios',
                                'invoice' => 'Facturas',
                                'payment' => 'Pagos',
                                'ticket' => 'Tickets',
                                'user' => 'Usuarios',
                                'client' => 'Clientes',
                            ])
                            ->required()
                            ->live(),
                        
                        Forms\Components\Select::make('event_type')
                            ->label('Tipo de Evento')
                            ->options(function (Forms\Get $get) {
                                $module = $get('module');
                                return match($module) {
                                    'service' => [
                                        'created' => 'Servicio Creado',
                                        'expiring' => 'Próximo a Vencer',
                                        'expired' => 'Vencido',
                                        'suspended' => 'Suspendido',
                                        'cancelled' => 'Cancelado',
                                        'renewed' => 'Renovado',
                                    ],
                                    'invoice' => [
                                        'created' => 'Factura Creada',
                                        'sent' => 'Factura Enviada',
                                        'overdue' => 'Factura Vencida',
                                        'paid' => 'Factura Pagada',
                                    ],
                                    'payment' => [
                                        'received' => 'Pago Recibido',
                                        'approved' => 'Pago Aprobado',
                                        'rejected' => 'Pago Rechazado',
                                    ],
                                    'ticket' => [
                                        'created' => 'Ticket Creado',
                                        'replied' => 'Respuesta Recibida',
                                        'resolved' => 'Ticket Resuelto',
                                        'closed' => 'Ticket Cerrado',
                                    ],
                                    'user' => [
                                        'created' => 'Usuario Creado',
                                        'password_reset' => 'Recuperación de Contraseña',
                                    ],
                                    'client' => [
                                        'created' => 'Cliente Creado',
                                        'updated' => 'Cliente Actualizado',
                                    ],
                                    default => [],
                                };
                            })
                            ->required(),
                        
                        Forms\Components\Select::make('recipient_type')
                            ->label('Tipo de Destinatario')
                            ->options([
                                'user' => 'Usuario (Cliente)',
                                'admin' => 'Administrativo',
                                'both' => 'Ambos',
                            ])
                            ->required()
                            ->default('user'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Descripción de cuándo y por qué se envía esta notificación'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Canales de Notificación')
                    ->schema([
                        Forms\Components\Toggle::make('email_enabled')
                            ->label('Email Habilitado')
                            ->default(true),
                        
                        Forms\Components\Toggle::make('sms_enabled')
                            ->label('SMS Habilitado')
                            ->default(false),
                        
                        Forms\Components\Toggle::make('push_enabled')
                            ->label('Push Notification Habilitado')
                            ->default(false),
                        
                        Forms\Components\Toggle::make('in_app_enabled')
                            ->label('Notificación en App Habilitado')
                            ->default(true),
                    ])->columns(2),
                
                Forms\Components\Section::make('Plantilla de Email')
                    ->schema([
                        Forms\Components\Select::make('template_id')
                            ->label('Plantilla de Email')
                            ->relationship('template', 'name')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn (EmailTemplate $record) => "{$record->name} ({$record->type})")
                            ->helperText('Seleccionar la plantilla de email a usar para esta notificación')
                            ->visible(fn (Forms\Get $get) => $get('email_enabled')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('module')
                    ->label('Módulo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'service' => 'Servicios',
                        'invoice' => 'Facturas',
                        'payment' => 'Pagos',
                        'ticket' => 'Tickets',
                        'user' => 'Usuarios',
                        'client' => 'Clientes',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'service',
                        'success' => 'invoice',
                        'info' => 'payment',
                        'warning' => 'ticket',
                        'gray' => 'user',
                        'danger' => 'client',
                    ]),
                
                Tables\Columns\TextColumn::make('event_type')
                    ->label('Evento')
                    ->searchable()
                    ->sortable(),
                
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
                
                Tables\Columns\IconColumn::make('email_enabled')
                    ->label('Email')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('sms_enabled')
                    ->label('SMS')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('push_enabled')
                    ->label('Push')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('in_app_enabled')
                    ->label('En App')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('template.name')
                    ->label('Plantilla')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('module')
                    ->label('Módulo')
                    ->options([
                        'service' => 'Servicios',
                        'invoice' => 'Facturas',
                        'payment' => 'Pagos',
                        'ticket' => 'Tickets',
                        'user' => 'Usuarios',
                        'client' => 'Clientes',
                    ]),
                
                Tables\Filters\SelectFilter::make('recipient_type')
                    ->label('Destinatario')
                    ->options([
                        'user' => 'Cliente',
                        'admin' => 'Admin',
                        'both' => 'Ambos',
                    ]),
                
                Tables\Filters\TernaryFilter::make('email_enabled')
                    ->label('Email Habilitado')
                    ->placeholder('Todas')
                    ->trueLabel('Solo con email')
                    ->falseLabel('Solo sin email'),
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
            ->defaultSort('module', 'asc');
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
            'index' => Pages\ListNotificationSettings::route('/'),
            'create' => Pages\CreateNotificationSetting::route('/create'),
            'edit' => Pages\EditNotificationSetting::route('/{record}/edit'),
        ];
    }
}
