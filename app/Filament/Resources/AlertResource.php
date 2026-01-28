<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlertResource\Pages;
use App\Models\Alert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AlertResource extends Resource
{
    protected static ?string $model = Alert::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';
    
    protected static ?string $navigationLabel = 'Alertas';
    
    protected static ?string $modelLabel = 'Alerta';
    
    protected static ?string $pluralModelLabel = 'Alertas';
    
    protected static ?string $navigationGroup = 'Gestión';
    
    protected static ?int $navigationSort = 3;
    
    protected static ?string $slug = 'alerts';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Alerta')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'service_expiring' => 'Servicio Próximo a Vencer',
                                'invoice_overdue' => 'Factura Vencida',
                                'payment_pending' => 'Pago Pendiente',
                                'service_expired' => 'Servicio Vencido',
                            ])
                            ->required(),
                        
                        Forms\Components\Select::make('priority')
                            ->label('Prioridad')
                            ->options([
                                'low' => 'Baja',
                                'medium' => 'Media',
                                'high' => 'Alta',
                                'urgent' => 'Urgente',
                            ])
                            ->default('medium')
                            ->required(),
                        
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'sent' => 'Enviada',
                                'dismissed' => 'Descartada',
                                'resolved' => 'Resuelta',
                            ])
                            ->default('pending')
                            ->required(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Detalles')
                    ->schema([
                        Forms\Components\Textarea::make('message')
                            ->label('Mensaje')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\DatePicker::make('trigger_date')
                            ->label('Fecha de Activación')
                            ->required(),
                        
                        Forms\Components\DatePicker::make('sent_at')
                            ->label('Fecha de Envío')
                            ->nullable(),
                        
                        Forms\Components\DatePicker::make('resolved_at')
                            ->label('Fecha de Resolución')
                            ->nullable(),
                    ])->columns(3),
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
                        'service_expiring' => 'Servicio Vence',
                        'invoice_overdue' => 'Factura Vencida',
                        'payment_pending' => 'Pago Pendiente',
                        'service_expired' => 'Servicio Vencido',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'service_expiring',
                        'danger' => 'invoice_overdue',
                        'info' => 'payment_pending',
                        'danger' => 'service_expired',
                    ]),
                
                Tables\Columns\TextColumn::make('message')
                    ->label('Mensaje')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->message),
                
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioridad')
                    ->badge()
                    ->colors([
                        'gray' => 'low',
                        'info' => 'medium',
                        'warning' => 'high',
                        'danger' => 'urgent',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'low' => 'Baja',
                        'medium' => 'Media',
                        'high' => 'Alta',
                        'urgent' => 'Urgente',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'sent',
                        'gray' => 'dismissed',
                        'primary' => 'resolved',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'pending' => 'Pendiente',
                        'sent' => 'Enviada',
                        'dismissed' => 'Descartada',
                        'resolved' => 'Resuelta',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('trigger_date')
                    ->label('Fecha Activación')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => 
                        $record->trigger_date < now() && $record->status === 'pending' ? 'danger' : null
                    ),
                
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
                        'service_expiring' => 'Servicio Próximo a Vencer',
                        'invoice_overdue' => 'Factura Vencida',
                        'payment_pending' => 'Pago Pendiente',
                        'service_expired' => 'Servicio Vencido',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'sent' => 'Enviada',
                        'dismissed' => 'Descartada',
                        'resolved' => 'Resuelta',
                    ]),
                
                Tables\Filters\SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options([
                        'low' => 'Baja',
                        'medium' => 'Media',
                        'high' => 'Alta',
                        'urgent' => 'Urgente',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_resolved')
                    ->label('Marcar Resuelta')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Alert $record) => $record->status !== 'resolved')
                    ->requiresConfirmation()
                    ->action(fn (Alert $record) => $record->markAsResolved()),
                
                Tables\Actions\Action::make('dismiss')
                    ->label('Descartar')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn (Alert $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(fn (Alert $record) => $record->dismiss()),
                
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('trigger_date', 'asc');
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
            'index' => Pages\ListAlerts::route('/'),
            'create' => Pages\CreateAlert::route('/create'),
            'edit' => Pages\EditAlert::route('/{record}/edit'),
        ];
    }
}
