<?php

namespace App\Filament\Resources\Users;

use App\Enums\User\UserGenderEnum;
use App\Enums\UserStatusEnum;
use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\Role;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Get;
use Filament\Forms\Set;
use SebastianBergmann\CodeCoverage\Driver\Selector;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'User';

    protected static function hasRole($roleIds, string $roleName): bool
    {
        if (empty($roleIds) || !is_array($roleIds)) {
            return false;
        }

        return Role::whereIn('id', $roleIds)->where('name', $roleName)->exists();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
    ->schema([ // pastikan memakai schema() kalau konteksnya Form Resource
        TextInput::make('name')->required(),
        TextInput::make('username')->required(),
        Select::make('status')
            ->required()
            ->options(UserStatusEnum::options())
            ->default(UserStatusEnum::ACTIVE->value)
            ->native(false), // optional, biar dropdown lebih bagus (Filament UI)

        // GANTI: pakai MultiSelect untuk belongsToMany
        MultiSelect::make('roles')
            ->label('Roles')
            ->relationship('roles', 'name')
            ->preload()
            ->searchable()
            ->reactive()
            ->afterStateUpdated(function ($state, $set, $get) {
                // Pastikan $state adalah array of ids
                $selectedIds = is_array($state) ? $state : [];
                if (empty($selectedIds)) {
                    return;
                }

                $roleNames = Role::whereIn('id', $selectedIds)->pluck('name')->toArray();

                if (in_array('Dosen', $roleNames) && in_array('Mahasiswa', $roleNames)) {
                    // Pilih strategi: hapus 'Mahasiswa' supaya tetap ada 'Dosen'
                    $filtered = array_filter($roleNames, fn($r) => $r !== 'Mahasiswa');
                    $newIds = Role::whereIn('name', $filtered)->pluck('id')->toArray();

                    // set ulang nilai multi select
                    $set('roles', $newIds);

                    Notification::make()
                        ->title('Role "Dosen" dan "Mahasiswa" tidak bisa dipilih bersamaan.')
                        ->warning()
                        ->send();
                }
            }),

        TextInput::make('nip')
            ->label('NIP')
            ->hidden(fn ($get) => ! self::hasRole($get('roles'), 'Dosen')),

        TextInput::make('nim')
            ->label('NIM')
            ->hidden(fn ($get) => ! self::hasRole($get('roles'), 'Mahasiswa')),

        TextInput::make('email')->label('Email address')->email(),
        TextInput::make('phone_number')->tel(),
        TextInput::make('address'),
        TextInput::make('gender')->numeric(),
        TextInput::make('password')->password()->required(),
    ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('User')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('username')
                    ->searchable(),
                SelectColumn::make('status')
                ->options(UserStatusEnum::options())
                ->sortable()
                ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone_number')
                    ->searchable(),
                TextColumn::make('address')
                    ->searchable(),
                SelectColumn::make('status')
                    ->options(UserGenderEnum::options())
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
