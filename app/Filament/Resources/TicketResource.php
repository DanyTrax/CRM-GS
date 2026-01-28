<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Models\Ticket;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    
    protected static ?string $navigationLabel = 'Tickets';
    
    protected static ?string $modelLabel = 'Ticket';
    
    protected static ?string $pluralModelLabel = 'Tickets';
    
    protected static ?string $navigationGroup = 'Soporte';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $slug = 'tickets';
    
    public static function canViewAny(): bool
    {
        return true; // Temporalmente permitir a todos
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
    
    public static function shouldRegisterRoutes(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Ticket')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Cliente')
                            ->relationship('client', 'company_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\TextInput::make('ticket_number')
                            ->label('Número de Ticket')
                            ->default(fn () => 'TKT-' . str_pad((Ticket::withTrashed()->max('id') ?? 0) + 1, 6, '0', STR_PAD_LEFT))
                            ->required()
                            ->unique(ignoreRecord: true, table: 'tickets')
                            ->maxLength(255)
                            ->disabled(fn ($record) => $record !== null),
                        
                        Forms\Components\TextInput::make('subject')
                            ->label('Asunto')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),
                
                Forms\Components\Section::make('Detalles')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                        
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
                                'open' => 'Abierto',
                                'in_progress' => 'En Progreso',
                                'resolved' => 'Resuelto',
                                'closed' => 'Cerrado',
                            ])
                            ->default('open')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('Número')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('client.company_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('subject')
                    ->label('Asunto')
                    ->searchable()
                    ->limit(50),
                
                Tables\Columns\BadgeColumn::make('priority')
                    ->label('Prioridad')
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
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'open',
                        'warning' => 'in_progress',
                        'info' => 'resolved',
                        'gray' => 'closed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'open' => 'Abierto',
                        'in_progress' => 'En Progreso',
                        'resolved' => 'Resuelto',
                        'closed' => 'Cerrado',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'open' => 'Abierto',
                        'in_progress' => 'En Progreso',
                        'resolved' => 'Resuelto',
                        'closed' => 'Cerrado',
                    ]),
                
                Tables\Filters\SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options([
                        'low' => 'Baja',
                        'medium' => 'Media',
                        'high' => 'Alta',
                        'urgent' => 'Urgente',
                    ]),
                
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'company_name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
