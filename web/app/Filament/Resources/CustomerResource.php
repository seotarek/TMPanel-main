<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use App\Models\TMServer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Hosting Services';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        $TMServers = [];
        $TMServers[0] = 'Main Server';
        $getTMServers = TMServer::all();
        foreach ($getTMServers as $TMServer) {
            $TMServers[$TMServer->id] = $TMServer->name;
        }

        $schema = [];
        if ($getTMServers->count() > 0) {
            $schema[] = Forms\Components\Select::make('TM_server_id')
                ->label('Server')
                ->options($TMServers)
                ->default(0)
                ->hidden(function($record) {
                    if ($record) {
                        return $record->TM_server_id == 0;
                    } else {
                        return false;
                    }
                })
                ->disabled(function($record) {
                    if ($record) {
                        return $record->id > 0;
                    } else {
                        return false;
                    }
                })
                ->columnSpanFull();
        }

        $schemaAppend = [

            Forms\Components\TextInput::make('name')
                ->prefixIcon('heroicon-s-user')
                ->required()->columnSpanFull(),

            Forms\Components\TextInput::make('username')
                ->unique(Customer::class, 'username', ignoreRecord: true)
                ->prefixIcon('heroicon-s-user')
                ->required(),
            Forms\Components\TextInput::make('password')
                ->password()
                ->prefixIcon('heroicon-s-lock-closed')
                ->required(),

            Forms\Components\TextInput::make('email')
                ->prefixIcon('heroicon-s-envelope')
                ->unique(Customer::class, 'email', ignoreRecord: true)
                ->email()
                ->required(),

            Forms\Components\TextInput::make('phone'),
            Forms\Components\TextInput::make('address'),
            Forms\Components\TextInput::make('city'),
            Forms\Components\TextInput::make('state'),
            Forms\Components\TextInput::make('zip'),
            Forms\Components\TextInput::make('country'),
            Forms\Components\TextInput::make('company'),

        ];

        $schema = array_merge($schema, $schemaAppend);

        return $form
            ->schema($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
//                Tables\Columns\TextColumn::make('TM_server_id')
//                    ->label('Server')
//                    ->badge()
//                    ->state(function ($record) {
//                        if ($record->TM_server_id > 0) {
//                            $TMServer = TMServer::where('id', $record->TM_server_id)->first();
//                            if ($TMServer) {
//                                return $TMServer->name;
//                            }
//                        }
//                        return 'MAIN';
//                    })
//                    ->searchable()
//                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Customer Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ManageCustomers::route('/'),
            //            'index' => Pages\ListCustomers::route('/'),
            //            'create' => Pages\CreateCustomer::route('/create'),
            //            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
