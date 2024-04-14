TM_PHP=/usr/local/TM/php/bin/php

$TM_PHP -r "copy('https://github.com/acmephp/acmephp/releases/download/1.0.1/acmephp.phar', 'acmephp.phar');"
$TM_PHP -r "copy('https://github.com/acmephp/acmephp/releases/download/1.0.1/acmephp.phar.pubkey', 'acmephp.phar.pubkey');"
$TM_PHP acmephp.phar --version
