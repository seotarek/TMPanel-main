<?php

namespace App\Filament\Pages\Settings;

use Closure;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;

class Settings extends BaseSettings
{
    protected static ?string $navigationGroup = 'Server Management';

    protected static ?int $navigationSort = 4;

    public function schema(): array|Closure
    {
        return [
            Tabs::make('Settings')
                ->schema([
                    Tabs\Tab::make('General')
                        ->schema([
                            TextInput::make('general.brand_name'),
                            TextInput::make('general.master_domain'),
                            TextInput::make('general.master_email'),
                            TextInput::make('general.master_country'),
                            TextInput::make('general.master_locality'),
                            TextInput::make('general.organization_name'),
                        ]),
                ]),
        ];
    }
}
