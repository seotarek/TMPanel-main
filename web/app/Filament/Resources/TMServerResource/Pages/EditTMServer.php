<?php

namespace App\Filament\Resources\TMServerResource\Pages;

use App\Filament\Resources\TMServerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTMServer extends EditRecord
{
    protected static string $resource = TMServerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
