<?php

namespace App\Installers\Server\Applications;

class RubyInstaller
{
    public $rubyVersions = [];

    public $logFilePath = '/var/log/TM/ruby-installer.log';

    public function setRubyVersions($versions)
    {
        $this->rubyVersions = $versions;
    }

    public function setLogFilePath($path)
    {
        $this->logFilePath = $path;
    }

    public function install()
    {
        $commands = [];
        foreach ($this->rubyVersions as $rubyVersion) {
            $commands[] = 'apt install -y ruby' . $rubyVersion;
            $commands[] = 'apt install -y ruby' . $rubyVersion . '-dev';
            $commands[] = 'apt install -y ruby' . $rubyVersion . '-bundler';
        }

        $shellFileContent = '';
        foreach ($commands as $command) {
            $shellFileContent .= $command . PHP_EOL;
        }
        $shellFileContent .= 'echo "All packages installed successfully!"' . PHP_EOL;
        $shellFileContent .= 'echo "DONE!"' . PHP_EOL;
        $shellFileContent .= 'rm -f /tmp/ruby-installer.sh';

        file_put_contents('/tmp/ruby-installer.sh', $shellFileContent);

        shell_exec('bash /tmp/ruby-installer.sh >> ' . $this->logFilePath . ' &');

    }
}
