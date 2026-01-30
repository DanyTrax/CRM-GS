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
use Illuminate\Support\HtmlString;

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
                        Forms\Components\Placeholder::make('zoho_redirect_uri')
                            ->label('Paso 1: Configurar Redirect URI en Zoho API Console')
                            ->content(fn () => new HtmlString('
                                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg mb-4">
                                    <p class="text-sm text-blue-800 dark:text-blue-200 font-medium mb-2">
                                        📋 Copia esta URL y configúrala como Redirect URI en Zoho API Console:
                                    </p>
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded border border-blue-300 dark:border-blue-700">
                                        <code class="text-sm text-blue-900 dark:text-blue-100 break-all">' . route('zoho.oauth.callback') . '</code>
                                    </div>
                                    <p class="text-xs text-blue-700 dark:text-blue-300 mt-2">
                                        Ve a <a href="https://api-console.zoho.com/" target="_blank" class="underline font-medium">Zoho API Console</a>, crea una aplicación y pega esta URL en "Redirect URI"
                                    </p>
                                </div>
                            '))
                            ->visible(fn (Forms\Get $get) => $get('provider') === 'zoho')
                            ->columnSpanFull(),
                        
                        Forms\Components\Placeholder::make('zoho_warning')
                            ->label('')
                            ->content(fn (Forms\Get $get) => new HtmlString('
                                <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg mb-4">
                                    <p class="text-sm text-red-800 dark:text-red-200 font-medium mb-2">
                                        ⚠️ Debes autorizar con la cuenta: <strong>' . ($get('from_email') ?? 'soporte@acdoblevia.com') . '</strong>
                                    </p>
                                    <p class="text-sm text-red-700 dark:text-red-300">
                                        Cierra sesión en Zoho o usa ventana privada si sueles entrar con otro correo.
                                    </p>
                                </div>
                            '))
                            ->visible(fn (Forms\Get $get) => $get('provider') === 'zoho')
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('zoho_client_id')
                            ->label('Paso 2: Client ID')
                            ->maxLength(255)
                            ->required(fn (Forms\Get $get) => $get('provider') === 'zoho')
                            ->visible(fn (Forms\Get $get) => $get('provider') === 'zoho')
                            ->helperText('Client ID desde Zoho API Console (después de crear la aplicación)'),
                        
                        Forms\Components\TextInput::make('zoho_client_secret')
                            ->label('Paso 3: Client Secret')
                            ->password()
                            ->maxLength(255)
                            ->required(fn (Forms\Get $get) => $get('provider') === 'zoho')
                            ->visible(fn (Forms\Get $get) => $get('provider') === 'zoho')
                            ->helperText('Client Secret desde Zoho API Console'),
                        
                        Forms\Components\Placeholder::make('zoho_authorize_section')
                            ->label('Paso 4: Autorizar con Zoho')
                            ->content(function (Forms\Get $get, $record) {
                                $hasCredentials = ($record && $record->zoho_client_id && $record->zoho_client_secret) || 
                                                  ($get('zoho_client_id') && $get('zoho_client_secret'));
                                $configId = $record ? $record->id : null;
                                $authorizeUrl = $configId ? route('zoho.oauth.authorize', ['config_id' => $configId]) : '#';
                                
                                if (!$hasCredentials) {
                                    return new HtmlString('
                                        <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg mb-4">
                                            <p class="text-sm text-yellow-800 dark:text-yellow-200 mb-2">
                                                ⚠️ Completa los pasos 2 y 3 (Client ID y Client Secret) y guarda los cambios para habilitar la autorización.
                                            </p>
                                            <button disabled class="inline-flex items-center gap-2 px-4 py-2 bg-gray-400 text-white font-medium rounded-lg cursor-not-allowed opacity-50">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span>Autorizar con Zoho y Generar Refresh Token Automáticamente</span>
                                            </button>
                                        </div>
                                    ');
                                }
                                
                                return new HtmlString('
                                    <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg mb-4">
                                        <p class="text-sm text-green-800 dark:text-green-200 mb-3">
                                            Redirige a Zoho para autorizar y obtiene el Refresh Token automáticamente. Usa la misma cuenta que el Email Remitente.
                                        </p>
                                        <div id="zoho-authorize-button-container">
                                            <a href="' . $authorizeUrl . '" 
                                               class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span>Autorizar con Zoho y Generar Refresh Token Automáticamente</span>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        </div>
                                        <p class="text-xs text-green-700 dark:text-green-300 mt-3 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>Asegúrate de haber guardado los cambios antes de hacer clic.</span>
                                        </p>
                                    </div>
                                ');
                            })
                            ->visible(fn (Forms\Get $get) => $get('provider') === 'zoho')
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('zoho_refresh_token')
                            ->label('Refresh Token')
                            ->rows(3)
                            ->columnSpanFull()
                            ->visible(fn (Forms\Get $get) => $get('provider') === 'zoho')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Token de actualización de Zoho (generado automáticamente)'),
                        
                        Forms\Components\Placeholder::make('zoho_token_status')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record || !$record->zoho_refresh_token) {
                                    return new \Illuminate\Support\HtmlString('
                                        <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                                ⚠️ Refresh Token no configurado. Usa el botón de autorización arriba para generarlo.
                                            </p>
                                        </div>
                                    ');
                                }
                                
                                return new \Illuminate\Support\HtmlString('
                                    <div class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                        <p class="text-sm text-green-800 dark:text-green-200 flex items-center gap-2">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>Refresh Token configurado correctamente</span>
                                        </p>
                                    </div>
                                ');
                            })
                            ->visible(fn (Forms\Get $get) => $get('provider') === 'zoho')
                            ->columnSpanFull(),
                        
                        Forms\Components\Placeholder::make('zoho_error_help')
                            ->label('')
                            ->content(fn () => new HtmlString('
                                <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                    <p class="text-sm text-yellow-800 dark:text-yellow-200 flex items-start gap-2">
                                        <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>
                                            <strong>Si recibes el error "URL_RULE_NOT_CONFIGURED":</strong> Haz clic en "Limpiar" arriba, guarda los cambios, y luego usa el botón "Autorizar con Zoho" para regenerar el token.
                                        </span>
                                    </p>
                                </div>
                            '))
                            ->visible(fn (Forms\Get $get) => $get('provider') === 'zoho')
                            ->columnSpanFull(),
                        
                        Forms\Components\Placeholder::make('zoho_instructions')
                            ->label('¿Cómo funciona el botón?')
                            ->content(fn (Forms\Get $get) => new HtmlString('
                                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                    <ol class="list-decimal list-inside space-y-2 text-sm text-blue-800 dark:text-blue-200">
                                        <li>Envía el Client ID a Zoho para iniciar la autorización</li>
                                        <li>Zoho redirige a la página de autorización</li>
                                        <li>Inicias sesión con la cuenta correcta (' . ($get('from_email') ?? 'soporte@acdoblevia.com') . ')</li>
                                        <li>Autorizas la aplicación</li>
                                        <li>Zoho redirige de vuelta con un código de autorización</li>
                                        <li>El sistema intercambia el código por el Refresh Token y lo guarda automáticamente</li>
                                    </ol>
                                </div>
                            '))
                            ->visible(fn (Forms\Get $get) => $get('provider') === 'zoho')
                            ->columnSpanFull(),
                        
                        Forms\Components\Placeholder::make('zoho_verification')
                            ->label('Verificación de Configuración')
                            ->content(function ($record) {
                                if (!$record) {
                                    return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-600 dark:text-gray-400">Guarda primero para verificar</p>');
                                }
                                
                                $checks = [];
                                $allPassed = true;
                                
                                if ($record->zoho_client_id) {
                                    $checks[] = ['label' => 'Client ID configurado', 'passed' => true];
                                } else {
                                    $checks[] = ['label' => 'Client ID configurado', 'passed' => false];
                                    $allPassed = false;
                                }
                                
                                if ($record->zoho_client_secret) {
                                    $checks[] = ['label' => 'Client Secret configurado', 'passed' => true];
                                } else {
                                    $checks[] = ['label' => 'Client Secret configurado', 'passed' => false];
                                    $allPassed = false;
                                }
                                
                                if ($record->zoho_refresh_token) {
                                    $checks[] = ['label' => 'Refresh Token configurado', 'passed' => true];
                                } else {
                                    $checks[] = ['label' => 'Refresh Token configurado', 'passed' => false];
                                    $allPassed = false;
                                }
                                
                                $icon = $allPassed 
                                    ? '<svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
                                    : '<svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>';
                                
                                $bgColor = $allPassed ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800';
                                $textColor = $allPassed ? 'text-green-800 dark:text-green-200' : 'text-yellow-800 dark:text-yellow-200';
                                
                                $checksHtml = '';
                                foreach ($checks as $check) {
                                    $checkIcon = $check['passed'] 
                                        ? '<svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>'
                                        : '<svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>';
                                    
                                    $checksHtml .= '<li class="flex items-center gap-2 ' . ($check['passed'] ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300') . '">' . $checkIcon . '<span>' . $check['label'] . '</span></li>';
                                }
                                
                                return new \Illuminate\Support\HtmlString('
                                    <div class="p-4 ' . $bgColor . ' border rounded-lg">
                                        <div class="flex items-center gap-2 ' . $textColor . ' mb-3">
                                            ' . $icon . '
                                            <span class="font-medium">' . ($allPassed ? 'Configuración correcta' : 'Configuración incompleta') . '</span>
                                        </div>
                                        <ul class="space-y-1 text-sm">
                                            ' . $checksHtml . '
                                        </ul>
                                    </div>
                                ');
                            })
                            ->visible(fn (Forms\Get $get) => $get('provider') === 'zoho')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('provider') === 'zoho'),
                
                Forms\Components\Section::make('Configuración de Remitente')
                    ->schema([
                        Forms\Components\TextInput::make('from_email')
                            ->label('Email Remitente')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->helperText(fn (Forms\Get $get) => $get('provider') === 'zoho' 
                                ? 'Este debe ser el mismo correo con el que autorizarás en Zoho (Paso 5)' 
                                : 'Email desde el cual se enviarán los correos'),
                        
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
