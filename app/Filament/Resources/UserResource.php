<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Spatie\Permission\Models\Role; // <-- REQUIRED IMPORT

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    // Optional: Only show to Super Admin or users with 'manage users' permission
    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('Super Admin'); 
        // OR: return auth()->user()->can('manage users'); 
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                // CORRECTED PASSWORD FIELD (Handles null on edit form)
                TextInput::make('password')
                    ->password()
                    // If state is filled, hash it; otherwise return null to be ignored
                    ->dehydrateStateUsing(function ($state) {
                        return filled($state) ? bcrypt($state) : null;
                    })
                    // Required only on the 'create' operation
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->minLength(8)
                    // Dehydrate only if the field is filled (i.e., prevent saving an empty string)
                    ->dehydrated(fn ($state): bool => filled($state)), 
                    
                // SPATIE ROLES SELECTOR
                Select::make('roles')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->options(fn () => Role::pluck('name', 'id'))
                    ->preload()
                    ->searchable()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('email')
                    ->sortable()
                    ->searchable(),
                    
                // DISPLAY ROLES
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->listWithLineBreaks(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // ... filters and actions remain here
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}