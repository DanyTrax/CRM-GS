<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailConfigurationResource\Pages;
use App\Models\EmailConfiguration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class EmailConfigurationResource extends Resource
{
    protected static ?string $model = EmailConfiguration::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationLabel = 'Configuración de Email';
    
    protected static ?string $modelLabel = 'Configuración de Email';
    
    protected static ?string $pluralModelLabel = 'Configuraciones de Email';
    
    protected static ?string $navigationGroup = 'Mensajería';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $slug = 'email-configurations';

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
                            ->placeholder('Ej: SMTP Principal, Zoho Mail'),
                        
                        Forms\Components\Select::make('provider')
                            ->label('Proveedor')
                            ->options([
                                'smtp' => 'SMTP',
                                'zoho' => 'Zoho Mail',
                                'sendgrid' => 'SendGrid',
                                'mailgun' => 'Mailgun',
                            ])
                            ->required()
                            ->default('smtp')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Limpiar campos según el proveedor seleccionado
                                if ($state !== 'smtp') {
                                    $set('smtp_host', null);
                                    $set('smtp_port', null);
                                    $set('smtp_encryption', null);
                                    $set('smtp_username', null);
                                    $set('smtp_password', null);
                                }
                                if ($state !== 'zoho') {
                                    $set('zoho_client_id', null);
                                    $set('zoho_client_secret', null);
                                    $set('zoho_refresh_token', null);
                                }
                            }),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activa')
                            ->default(true),
                        
                        Forms\Components\Toggle::make('is_default')
                            ->label('Configuración por Defecto')
                            ->default(false)
                            ->helperText('Marcar como configuración predeterminada'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Configuración SMTP')
                    ->schema([
                        Forms\Components\TextInput::make('smtp_host')
                            ->label('Host SMTP')
                            ->maxLength(255)
                            ->placeholder('smtp.gmail.com')
                            ->visible(fn (Forms\Get $get) => $get('provider') === 'smtp'),
                        
                        Forms\Components\TextInput::make('smtp_port')
                            ->label('Puerto SMTP')
                            ->numeric()
                            ->default(587)
                            ->visible(fn (Forms\Get $get) => $get('provider') === 'smtp'),
                        
                        Forms\Components\Select::make('smtp_encryption')
                            ->label('Encriptación')
                            ->options([
                                'tls' => 'TLS',
                                'ssl' => 'SSL',
                            ])
                            ->default('tls')
                            ->visible(fn (Forms\Get $get) => $get('provider') === 'smtp'),
                        
                        Forms\Components\TextInput::make('smtp_username')
                            ->label('Usuario SMTP')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get) => $get('provider') === 'smtp'),
                        
                        Forms\Components\TextInput::make('smtp_password')
                            ->label('Contraseña SMTP')
                            ->password()
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get) => $get('provider') === 'smtp'),
                    ])
                    ->columns(2)
                    ->visible(fn (Forms\Get $get) => $get('provider') === 'smtp'),
                
                Forms\Components\Section::make('Configuración Zoho')
                    ->schema([
                        Forms\Components\TextInput::make('zoho_client_id')
                            ->label('Client ID')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get) => $get('provider') === 'zoho'),
                        
                        Forms\Components\TextInput::make('zoho_client_secret')
                            ->label('Client Secret')
                            ->password()
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get) => $get('provider') === 'zoho'),
                        
                        Forms\Components\Textarea::make('zoho_refresh_token')
                            ->label('Refresh Token')
                            ->rows(3)
                            ->columnSpanFull()
                            ->visible(fn (Forms\Get $get) => $get('provider') === 'zoho')
                            ->helperText('Token de actualización de Zoho (se obtiene mediante OAuth)'),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('provider') === 'zoho'),
                
                Forms\Components\Section::make('Configuración de Remitente')
                    ->schema([
                        Forms\Components\TextInput::make('from_email')
                            ->label('Email Remitente')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('from_name')
                            ->label('Nombre Remitente')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('reply_to_email')
                            ->label('Email de Respuesta')
                            ->email()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('reply_to_name')
                            ->label('Nombre de Respuesta')
                            ->maxLength(255),
                    ])->columns(2),
                
                Forms\Components\Section::make('Configuración Adicional')
                    ->schema([
                        Forms\Components\TextInput::make('rate_limit')
                            ->label('Límite de Envío (por hora)')
                            ->numeric()
                            ->default(100)
                            ->helperText('Número máximo de emails que se pueden enviar por hora'),
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
                
                Tables\Columns\TextColumn::make('provider')
                    ->label('Proveedor')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'smtp' => 'SMTP',
                        'zoho' => 'Zoho',
                        'sendgrid' => 'SendGrid',
                        'mailgun' => 'Mailgun',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'smtp',
                        'success' => 'zoho',
                        'info' => 'sendgrid',
                        'warning' => 'mailgun',
                    ]),
                
                Tables\Columns\TextColumn::make('from_email')
                    ->label('Email Remitente')
                    ->searchable()
                    ->copyable(),
                
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
                Tables\Filters\SelectFilter::make('provider')
                    ->label('Proveedor')
                    ->options([
                        'smtp' => 'SMTP',
                        'zoho' => 'Zoho',
                        'sendgrid' => 'SendGrid',
                        'mailgun' => 'Mailgun',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activa')
                    ->placeholder('Todas')
                    ->trueLabel('Solo activas')
                    ->falseLabel('Solo inactivas'),
            ])
            ->actions([
                Tables\Actions\Action::make('test')
                    ->label('Probar Configuración')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (EmailConfiguration $record) {
                        try {
                            // Aplicar configuración temporalmente
                            $record->applyToMailConfig();
                            
                            // Intentar enviar email de prueba
                            \Mail::raw('Este es un email de prueba desde la configuración: ' . $record->name, function ($message) use ($record) {
                                $message->to($record->from_email)
                                    ->subject('Prueba de Configuración - ' . $record->name);
                            });
                            
                            Notification::make()
                                ->title('Email de prueba enviado')
                                ->body('Se envió un email de prueba a ' . $record->from_email)
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al enviar email de prueba')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                
                Tables\Actions\Action::make('set_default')
                    ->label('Marcar como Defecto')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (EmailConfiguration $record) => !$record->is_default)
                    ->requiresConfirmation()
                    ->action(fn (EmailConfiguration $record) => $record->setAsDefault()),
                
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
            'index' => Pages\ListEmailConfigurations::route('/'),
            'create' => Pages\CreateEmailConfiguration::route('/create'),
            'edit' => Pages\EditEmailConfiguration::route('/{record}/edit'),
        ];
    }
}
