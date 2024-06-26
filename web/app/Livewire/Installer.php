<?php

namespace App\Livewire;

use App\Filament\Enums\ServerApplicationType;
use App\Installers\Server\Applications\NodeJsInstaller;
use App\Installers\Server\Applications\PHPInstaller;
use App\Installers\Server\Applications\PythonInstaller;
use App\Installers\Server\Applications\RubyInstaller;
use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use JaOcero\RadioDeck\Forms\Components\RadioDeck;
use Livewire\Component;

class Installer extends Page
{

    protected static string $layout = 'filament-panels::components.layout.base';

    protected static string $view = 'livewire.installer';

    public $step = 1;

    public $name;

    public $email;

    public $password;

    public $password_confirmation;

    public $livewire = true;

    public $install_log_file_path = 'logs/installer.log';
    public $install_log = 'Loading...';

    public $server_application_type = 'apache_php';
    public $server_php_modules = [];
    public $server_php_versions = [];

    public $server_nodejs_versions = [
        '20'
    ];

    public $server_python_versions = [
        '3.10'
    ];

    public $server_ruby_versions = [
        '3.4'
    ];

    public function form(Form $form): Form
    {

        if (empty($this->server_php_versions)) {
            $this->server_php_versions = ['8.2'];
        }

        if (empty($this->server_php_modules)) {
            $this->server_php_modules = array_keys($this->_getPHPModules());
        }

        $step1 = [
            TextInput::make('name')
                ->label('Name')
                ->required(),

            TextInput::make('email')
                ->label('Email')
                ->required()
                ->email(),

            TextInput::make('password')
                ->label('Password')
                ->required()
                ->password(),

            TextInput::make('password_confirmation')
                ->label('Confirm Password')
                ->same('password')
                ->required()
                ->password(),
        ];

        $startOnStep = 1;
        $findUserCount = User::count();
        if ($findUserCount >= 1) {
            $startOnStep = 2;
            $step1 = [
                Section::make()
                    ->heading('Admin user account already created')
                    ->description('You can continue to configure your hosting server.')
            ];
        }


            return $form
            ->schema([

                Wizard::make([

                    Wizard\Step::make('Step 1')
                        ->description('Create your admin account')
                        ->schema($step1)->afterValidation(function () use ($findUserCount) {

                            if ($findUserCount == 0) {
                                $createUser = new User();
                                $createUser->name = $this->name;
                                $createUser->email = $this->email;
                                $createUser->password = bcrypt($this->password);
                                $createUser->save();
                            }

                        }),

                    Wizard\Step::make('Step 2')
                        ->description('Configure your hosting server')
                        ->schema([

                            RadioDeck::make('server_application_type')
                                ->live()
                                ->default('apache_php')
                                ->options(ServerApplicationType::class)
                                ->icons(ServerApplicationType::class)
                                ->descriptions(ServerApplicationType::class)
                                ->required()
                                ->color('primary')
                                ->columns(2),

                            // PHP Configuration
                            CheckboxList::make('server_php_versions')
                                ->hidden(function (Get $get) {
                                    return $get('server_application_type') !== 'apache_php';
                                })
                                ->label('PHP Version')
                                ->options($this->_getPHPVersions())
                                ->columns(5)
                                ->required(),

                            CheckboxList::make('server_php_modules')
                                ->hidden(function (Get $get) {
                                    return $get('server_application_type') !== 'apache_php';
                                })
                                ->label('PHP Modules')
                                ->columns(5)
                                ->options($this->_getPHPModules()),
                            // End of PHP Configuration

                            // Node.js Configuration
                            CheckboxList::make('server_nodejs_versions')
                                ->hidden(function (Get $get) {
                                    return $get('server_application_type') !== 'apache_nodejs';
                                })
                                ->label('Node.js Version')
                                ->default([
                                    '14'
                                ])
                                ->options($this->_getNodeJsVersions())
                                ->columns(6)
                                ->required()
                                ->default('14'),

                            // End of Node.js Configuration

                            // Python Configuration

                            CheckboxList::make('server_python_versions')
                                ->hidden(function (Get $get) {
                                    return $get('server_application_type') !== 'apache_python';
                                })
                                ->label('Python Version')
                                ->default([
                                    '3.10'
                                ])
                                ->options($this->_getPythonVersions())
                                ->columns(6)
                                ->required()
                                ->default('3.10'),

                            // End of Python Configuration

                            // Ruby Configuration

                            CheckboxList::make('server_ruby_versions')
                                ->hidden(function (Get $get) {
                                    return $get('server_application_type') !== 'apache_ruby';
                                })
                                ->label('Ruby Version')
                                ->default([
                                    '3.4'
                                ])
                                ->options($this->_getRubyVersions())
                                ->columns(6)
                                ->required()
                                ->default('3.4'),

                            // End of Ruby Configuration

                        ])->afterValidation(function () {

                            $this->install_log = 'Prepare installation...';
                            if (is_file(storage_path('server-app-configuration.json'))) {
                                unlink(storage_path('server-app-configuration.json'));
                            }

                            // file_put_contents(storage_path('server-app-configuration.json'), json_encode($serverAppConfiguration));

                            if ($this->server_application_type == 'apache_php') {
                                 $phpInstaller = new PHPInstaller();
                                 $phpInstaller->setPHPVersions($this->server_php_versions);
                                 $phpInstaller->setPHPModules($this->server_php_modules);
                                 $phpInstaller->setLogFilePath(storage_path($this->install_log_file_path));
                                 $phpInstaller->install();
                            } else if ($this->server_application_type == 'apache_nodejs') {
                                 $nodeJsInstaller = new NodeJsInstaller();
                                 $nodeJsInstaller->setNodeJsVersions($this->server_nodejs_versions);
                                 $nodeJsInstaller->setLogFilePath(storage_path($this->install_log_file_path));
                                 $nodeJsInstaller->install();
                            }elseif ($this->server_application_type == 'apache_python') {
                                 $pythonInstaller = new PythonInstaller();
                                 $pythonInstaller->setPythonVersions($this->server_python_versions);
                                 $pythonInstaller->setLogFilePath(storage_path($this->install_log_file_path));
                                 $pythonInstaller->install();
                            }elseif ($this->server_application_type == 'apache_ruby') {
                                 $rubyInstaller = new RubyInstaller();
                                 $rubyInstaller->setRubyVersions($this->server_ruby_versions);
                                 $rubyInstaller->setLogFilePath(storage_path($this->install_log_file_path));
                                 $rubyInstaller->install();
                            }

                        }),

                    Wizard\Step::make('Step 3')
                        ->description('Finish installation')
                        ->schema([

                            TextInput::make('install_log')
                                ->view('livewire.installer-install-log')
                                ->label('Installation Log'),

                        ])

                ])
                    ->persistStepInQueryString()
                    ->startOnStep($startOnStep)
                    ->submitAction(new HtmlString(Blade::render(<<<BLADE
                        <x-filament::button
                            type="submit"
                            size="sm"
                            color="primary"
                            wire:click="install"
                        >
                            Submit
                        </x-filament::button>
                    BLADE)))

            ]);
    }

    public function installLog()
    {
        if (is_file(storage_path($this->install_log_file_path))) {
            $this->install_log = file_get_contents(storage_path($this->install_log_file_path));
            $this->install_log = nl2br($this->install_log);
        } else {
            $this->install_log = 'Waiting for installation log...';
        }
    }

    private function _getNodeJsVersions()
    {
        $versions = [];
        $nodeJsVersions = [
            '14',
            '16',
            '17',
            '18',
            '19',
            '20',
        ];
        foreach ($nodeJsVersions as $version) {
            $versions[$version] = 'Node.js '.$version;
        }
        return $versions;
    }

    private function _getRubyVersions()
    {
        $versions = [];
        $rubyVersions = [
            '2.7',
            '3.0',
            '3.1',
            '3.2',
            '3.3',
            '3.4',
        ];
        foreach ($rubyVersions as $version) {
            $versions[$version] = 'Ruby '.$version;
        }
        return $versions;
    }

    private function _getPythonVersions()
    {
        $versions = [];
        $pythonVersions = [
            '2.7',
            '3.6',
            '3.7',
            '3.8',
            '3.9',
            '3.10',
        ];
        foreach ($pythonVersions as $version) {
            $versions[$version] = 'Python '.$version;
        }
        return $versions;
    }

    private function _getPHPVersions()
    {
        $versions = [];
        $phpVersions = [
            '7.4',
            '8.0',
            '8.1',
            '8.2',
            '8.3',
        ];
        foreach ($phpVersions as $version) {
            $versions[$version] = 'PHP '.$version;
        }
        return $versions;
    }

    private function _getPHPModules()
    {
        $modules = [];
        $phpModules = [
            'bcmath' => 'BCMath',
            'bz2' => 'Bzip2',
            'calendar' => 'Calendar',
            'ctype' => 'Ctype',
            'curl' => 'Curl',
            'dom' => 'DOM',
            'fileinfo' => 'Fileinfo',
            'gd' => 'GD',
            'intl' => 'Intl',
            'mbstring' => 'Mbstring',
            'mysql' => 'MySQL',
            'opcache' => 'OPcache',
            'sqlite3' => 'SQLite3',
            'xmlrpc' => 'XML-RPC',
            'zip' => 'Zip',
        ];
        foreach ($phpModules as $module => $name) {
            $modules[$module] = $name;
        }
        return $modules;
    }

    public function install()
    {
        file_put_contents(storage_path('installed'), 'installed-'.date('Y-m-d H:i:s'));

        return redirect('/admin/login');
    }

}
