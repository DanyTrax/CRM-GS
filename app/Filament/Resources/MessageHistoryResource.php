<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageHistoryResource\Pages;
use App\Models\MessageHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MessageHistoryResource extends Resource
{
    protected static ?string $model = MessageHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';
    
    protected static ?string $navigationLabel = 'Historial de Mensajes';
    
    protected static ?string $modelLabel = 'Mensaje';
    
    protected static ?string $pluralModelLabel = 'Historial de Mensajes';
    
    protected static ?string $navigationGroup = 'Mensajería';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $slug = 'message-history';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Mensaje')
                    ->schema([
                        Forms\Components\Select::make('message_type')
                            ->label('Tipo de Mensaje')
                            ->options([
                                'email' => 'Email',
                                'sms' => 'SMS',
                                'push' => 'Push Notification',
                                'in_app' => 'Notificación en App',
                            ])
                            ->required(),
                        
                        Forms\Components\Select::make('recipient_type')
                            ->label('Tipo de Destinatario')
                            ->options([
                                'user' => 'Usuario (Cliente)',
                                'admin' => 'Administrativo',
                                'both' => 'Ambos',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('recipient_email')
                            ->label('Email del Destinatario')
                            ->email()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('recipient_phone')
                            ->label('Teléfono del Destinatario')
                            ->tel()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('subject')
                            ->label('Asunto')
                            ->maxLength(255),
                        
                        Forms\Components\RichEditor::make('body')
                            ->label('Cuerpo del Mensaje')
                            ->columnSpanFull(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Estado y Configuración')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'sent' => 'Enviado',
                                'failed' => 'Fallido',
                                'delivered' => 'Entregado',
                                'read' => 'Leído',
                            ])
                            ->required(),
                        
                        Forms\Components\Select::make('provider')
                            ->label('Proveedor')
                            ->options([
                                'smtp' => 'SMTP',
                                'zoho' => 'Zoho',
                                'sendgrid' => 'SendGrid',
                                'mailgun' => 'Mailgun',
                            ])
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('external_id')
                            ->label('ID Externo')
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('error_message')
                            ->label('Mensaje de Error')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('message_type')
                    ->label('Tipo')
                    ->badge()
                    ->colors([
                        'primary' => 'email',
                        'success' => 'sms',
                        'info' => 'push',
                        'warning' => 'in_app',
                    ]),
                
                Tables\Columns\TextColumn::make('recipient_email')
                    ->label('Destinatario')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('subject')
                    ->label('Asunto')
                    ->searchable()
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'sent',
                        'danger' => 'failed',
                        'info' => 'delivered',
                        'primary' => 'read',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'pending' => 'Pendiente',
                        'sent' => 'Enviado',
                        'failed' => 'Fallido',
                        'delivered' => 'Entregado',
                        'read' => 'Leído',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('provider')
                    ->label('Proveedor')
                    ->badge()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Enviado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('message_type')
                    ->label('Tipo de Mensaje')
                    ->options([
                        'email' => 'Email',
                        'sms' => 'SMS',
                        'push' => 'Push',
                        'in_app' => 'En App',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'sent' => 'Enviado',
                        'failed' => 'Fallido',
                        'delivered' => 'Entregado',
                        'read' => 'Leído',
                    ]),
                
                Tables\Filters\SelectFilter::make('provider')
                    ->label('Proveedor')
                    ->options([
                        'smtp' => 'SMTP',
                        'zoho' => 'Zoho',
                        'sendgrid' => 'SendGrid',
                        'mailgun' => 'Mailgun',
                    ]),
                
                Tables\Filters\Filter::make('failed')
                    ->label('Solo Fallidos')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'failed')),
            ])
            ->actions([
                Tables\Actions\Action::make('view_body')
                    ->label('Ver Contenido')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalContent(fn (MessageHistory $record) => view('filament.partials.message-preview', ['message' => $record]))
                    ->modalHeading(fn (MessageHistory $record) => 'Contenido del Mensaje: ' . $record->subject)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),
                
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
            'index' => Pages\ListMessageHistory::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
