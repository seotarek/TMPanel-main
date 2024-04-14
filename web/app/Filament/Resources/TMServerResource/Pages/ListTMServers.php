<?php

namespace App\Filament\Resources\TMServerResource\Pages;

use App\Filament\Resources\TMServerResource;
use App\Models\TMServer;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ManageRecords;

class ListTMServers extends ManageRecords
{
    protected static string $resource = TMServerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Sync Servers Resources')->action(function() {
                $findTMServers = TMServer::all();
                if ($findTMServers->count() > 0) {
                    foreach ($findTMServers as $TMServer) {
                        $TMServer->syncResources();
                    }
                }
            }),
            Actions\CreateAction::make(),
        ];
    }
}
