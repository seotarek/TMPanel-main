<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HostingSubscriptionResource\Pages;
use App\Models\HostingSubscription;
use App\Models\TMServer;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class HostingSubscriptionResource extends Resource
{
    protected static ?string $model = HostingSubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Hosting Services';

    protected static ?string $label = 'Subscriptions';

    protected static ?int $navigationSort = 2;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Hosting Subscription Information')->schema([

//                    Forms\Components\Placeholder::make('Website Link')
//                        ->hidden(function ($record) {
//                            if (isset($record->exists)) {
//                                return false;
//                            } else {
//                                return true;
//                            }
//                        })
//                        ->content(fn($record) => new HtmlString('
//                    <a href="http://' . $record->domain . '" target="_blank" class="text-sm font-medium text-primary-600 dark:text-primary-400">
//                           http://' . $record->domain . '
//                    </a>')),

                    Forms\Components\TextInput::make('domain')
                        ->required()
                        ->regex('/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i')
                        ->disabled(function ($record) {
                            if (isset($record->exists)) {
                                return $record->exists;
                            } else {
                                return false;
                            }
                        })
                        ->suffixIcon('heroicon-m-globe-alt')
                        ->columnSpanFull(),

                    Forms\Components\Select::make('customer_id')
                        ->label('Customer')
                        ->options(
                            \App\Models\Customer::all()->pluck('name', 'id')
                        )
                        ->required()->columnSpanFull(),

                    Forms\Components\Select::make('hosting_plan_id')
                        ->label('Hosting Plan')
                        ->options(
                            \App\Models\HostingPlan::all()->pluck('name', 'id')
                        )
                        ->required()->columnSpanFull(),

                    Forms\Components\Checkbox::make('advanced')
                        ->live()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('system_username')
                        ->hidden(fn(Forms\Get $get): bool => !$get('advanced'))
                        ->disabled(function ($record) {
                            if (isset($record->exists)) {
                                return $record->exists;
                            } else {
                                return false;
                            }
                        })
                        ->suffixIcon('heroicon-m-user'),

                    Forms\Components\TextInput::make('system_password')
                        ->hidden(fn(Forms\Get $get): bool => !$get('advanced'))
                        ->disabled(function ($record) {
                            if (isset($record->exists)) {
                                return $record->exists;
                            } else {
                                return false;
                            }
                        })
                        ->suffixIcon('heroicon-m-lock-closed'),
                ]),

            ]);
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

                Tables\Columns\TextColumn::make('domain')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('system_username')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),

//                Tables\Columns\TextColumn::make('hostingPlan.name')
//                    ->searchable()
//                    ->sortable(),


            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('visit')
                    ->label('Open website')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn($record): string => 'http://' . $record->domain, true),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
         //   Pages\ViewHos::class,
            Pages\EditHostingSubscription::class,
            Pages\ManageHostingSubscriptionDatabases::class,
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
            // 'index' => Pages\ManageHostingSubscriptions::route('/'),
            'index' => Pages\ListHostingSubscriptions::route('/'),
            'create' => Pages\CreateHostingSubscription::route('/create'),
            'edit' => Pages\EditHostingSubscription::route('/{record}/edit'),

            'databases' => Pages\ManageHostingSubscriptionDatabases::route('/{record}/databases'),
        ];
    }
}
