<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TMServerResource\Pages;
use App\Filament\Resources\TMServerResource\RelationManagers;
use App\Models\TMServer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TMServerResource extends Resource
{
    protected static ?string $model = TMServer::class;

    protected static ?string $navigationIcon = 'heroicon-o-server-stack';

    protected static ?string $navigationGroup = 'Server Clustering';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->placeholder('Enter the name of the server')
                ->columnSpanFull(),

                Forms\Components\TextInput::make('ip')
                    ->label('IP Address')
                    ->placeholder('Enter the IP address of the server')
                    ->required(),

                Forms\Components\TextInput::make('port')
                    ->label('Port')
                    ->default('22')
                    ->placeholder('Enter the port of the server')
                    ->required(),

                Forms\Components\TextInput::make('username')
                    ->label('Username')
                    ->placeholder('Enter the username of the server')
                    ->required(),

                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->placeholder('Enter the password of the server')
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip')
                    ->label('IP Address')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('Refresh Status')
                    ->action(function ($record) {
                        $record->healthCheck();
                    }),
                Tables\Actions\Action::make('Update Server')
                    ->action(function ($record) {
                        $record->updateServer();
                    }),
                Tables\Actions\Action::make('Sync Resources')
                    ->action(function ($record) {
                        $record->syncResources();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListTMServers::route('/'),
//            'create' => Pages\CreateTMServer::route('/create'),
//            'edit' => Pages\EditTMServer::route('/{record}/edit'),
        ];
    }
}
