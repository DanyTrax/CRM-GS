<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'Clientes';
    
    protected static ?string $modelLabel = 'Cliente';
    
    protected static ?string $pluralModelLabel = 'Clientes';
    
    protected static ?string $navigationGroup = 'Gestión';
    
    protected static ?int $navigationSort = 1;

    // Control de acceso simple (sin Shield)
    public static function canViewAny(): bool
    {
        // Permitir acceso a usuarios con rol admin o super-admin
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        
        // Cargar relación si no está cargada
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        if (!$user->role) {
            return false;
        }
        
        return in_array($user->role->slug ?? '', ['super-admin', 'admin-operativo', 'contador']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\TextInput::make('company_name')
                            ->label('Razón Social')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('tax_id')
                            ->label('NIT/Cédula')
                            ->maxLength(50),
                        
                        Forms\Components\TextInput::make('email_login')
                            ->label('Email de Acceso')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('email_billing')
                            ->label('Email de Facturación')
                            ->email()
                            ->maxLength(255),
                    ])->columns(2),
                
                Forms\Components\Section::make('Información de Contacto')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(20),
                        
                        Forms\Components\Textarea::make('address')
                            ->label('Dirección')
                            ->rows(3),
                    ])->columns(1),
                
                Forms\Components\Section::make('Estado')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'borrador' => 'Borrador',
                                'activo' => 'Activo',
                                'suspendido' => 'Suspendido',
                            ])
                            ->default('borrador')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Razón Social')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('tax_id')
                    ->label('NIT/Cédula')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('email_login')
                    ->label('Email')
                    ->searchable()
                    ->icon('heroicon-m-envelope'),
                
                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'warning' => 'borrador',
                        'success' => 'activo',
                        'danger' => 'suspendido',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'borrador' => 'Borrador',
                        'activo' => 'Activo',
                        'suspendido' => 'Suspendido',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'borrador' => 'Borrador',
                        'activo' => 'Activo',
                        'suspendido' => 'Suspendido',
                    ]),
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
