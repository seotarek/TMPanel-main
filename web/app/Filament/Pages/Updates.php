<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Updates extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static string $view = 'filament.pages.updates';

    protected static ?string $navigationGroup = 'Server Management';

    protected static ?string $navigationLabel = 'Updates';

    protected static ?int $navigationSort = 1;

    public $logFilePath = '/usr/local/TM/update/update.log';

    public function startUpdate()
    {
        // Start update

        $output = '';
        $output .= exec('mkdir -p /usr/local/TM/update');
        $output .= exec('wget https://raw.githubusercontent.com/seotarek/TMPanel/main/update/update-web-panel.sh -O /usr/local/TM/update/update-web-panel.sh');
        $output .= exec('chmod +x /usr/local/TM/update/update-web-panel.sh');
        $output .= shell_exec('bash /usr/local/TM/update/update-web-panel.sh >> ' . $this->logFilePath . ' &');

        return redirect('/admin/update-log');
    }

    protected function getViewData(): array
    {
        return [

        ];
    }
}
