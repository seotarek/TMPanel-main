<?php

namespace App\Models;

use App\Events\DomainIsCreated;
use App\Events\ModelDomainDeleting;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $fillable = [
        'domain',
        'domain_root',
        'ip',
        'hosting_subscription_id',
        'server_application_type'
    ];

    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {

            $findHostingSubscription = HostingSubscription::where('id', $model->hosting_subscription_id)->first();
            if (! $findHostingSubscription) {
                return;
            }

            $findHostingPlan = HostingPlan::where('id', $findHostingSubscription->hosting_plan_id)->first();
            if (! $findHostingPlan) {
                return;
            }

            $model->server_application_type = $findHostingPlan->default_server_application_type;

            if ($model->is_main == 1) {
              //  $allDomainsRoot = '/home/'.$this->user.'/public_html';
                $model->domain_root = '/home/'.$findHostingSubscription->system_username;
                $model->domain_public = '/home/'.$findHostingSubscription->system_username.'/public_html';
                $model->home_root = '/home/'.$findHostingSubscription->system_username;
            } else {
             //   $allDomainsRoot = '/home/'.$model->user.'/domains';
                $model->domain_root = '/home/'.$findHostingSubscription->system_username.'/domains/'.$model->domain;
                $model->domain_public = $model->domain_root.'/public_html';
                $model->home_root = '/home/'.$findHostingSubscription->user;
            }

            $model->save();

            $model->configureVirtualHost();

            event(new DomainIsCreated($model));

        });

        static::saved(function ($model) {
            $model->configureVirtualHost();
        });

        static::deleting(function ($model) {
            event(new ModelDomainDeleting($model));
        });

    }

    public function hostingSubscription()
    {
        return $this->belongsTo(HostingSubscription::class);
    }

    public function configureVirtualHost()
    {
        $findHostingSubscription = \App\Models\HostingSubscription::where('id', $this->hosting_subscription_id)
            ->first();
        if (!$findHostingSubscription) {
            throw new \Exception('Hosting subscription not found');
        }

        $findHostingPlan = \App\Models\HostingPlan::where('id', $findHostingSubscription->hosting_plan_id)
            ->first();
        if (!$findHostingPlan) {
            throw new \Exception('Hosting plan not found');
        }

        if (!is_dir($this->domain_root)) {
            mkdir($this->domain_root, 0755, true);
        }
        if (!is_dir($this->domain_public)) {
            mkdir($this->domain_public, 0755, true);
        }
        if (!is_dir($this->home_root)) {
            mkdir($this->home_root, 0755, true);
        }

        if ($this->is_installed_default_app_template == null) {
            $this->is_installed_default_app_template = 1;
            $this->save();
            if ($this->server_application_type == 'apache_php') {
                if (!is_file($this->domain_public . '/index.php')) {
                    $indexContent = view('actions.samples.apache.php.app-php-sample')->render();
                    file_put_contents($this->domain_public . '/index.php', $indexContent);
                }
                if (!is_dir($this->domain_public . '/templates')) {
                    mkdir($this->domain_public . '/templates', 0755, true);
                }
                if (!is_file($this->domain_public . '/templates/index.html')) {
                    $indexContent = view('actions.samples.apache.php.app-index-html')->render();
                    file_put_contents($this->domain_public . '/templates/index.html', $indexContent);
                }
            }

            if ($this->server_application_type == 'apache_nodejs') {
                if (!is_file($this->domain_public . '/app.js')) {
                    $indexContent = view('actions.samples.apache.nodejs.app-nodejs-sample')->render();
                    file_put_contents($this->domain_public . '/app.js', $indexContent);
                }
                if (!is_dir($this->domain_public . '/templates')) {
                    mkdir($this->domain_public . '/templates', 0755, true);
                }
                if (!is_file($this->domain_public . '/templates/index.html')) {
                    $indexContent = view('actions.samples.apache.nodejs.app-index-html')->render();
                    file_put_contents($this->domain_public . '/templates/index.html', $indexContent);
                }
            }

            if ($this->server_application_type == 'apache_python') {
                if (!is_file($this->domain_public . '/app.py')) {
                    $indexContent = view('actions.samples.apache.python.app-python-sample')->render();
                    file_put_contents($this->domain_public . '/app.py', $indexContent);
                }
                if (!is_file($this->domain_public . '/passenger_wsgi.py')) {
                    $indexContent = view('actions.samples.apache.python.app-passanger-wsgi-sample')->render();
                    file_put_contents($this->domain_public . '/passenger_wsgi.py', $indexContent);
                }
                if (!is_dir($this->domain_public . '/templates')) {
                    mkdir($this->domain_public . '/templates', 0755, true);
                }
                if (!is_file($this->domain_public . '/templates/index.html')) {
                    $indexContent = view('actions.samples.apache.python.app-index-html')->render();
                    file_put_contents($this->domain_public . '/templates/index.html', $indexContent);
                }
            }
        }

        $webUserGroup = $findHostingSubscription->system_username;

        // Fix file permissions
        shell_exec('chown -R '.$findHostingSubscription->system_username.':'.$webUserGroup.' '.$this->homeRoot);
        shell_exec('chown -R '.$findHostingSubscription->system_username.':'.$webUserGroup.' '.$this->domain_root);
        shell_exec('chown -R '.$findHostingSubscription->system_username.':'.$webUserGroup.' '.$this->domain_public);

        shell_exec('chmod -R 775 '.$this->home_root);
        shell_exec('chmod -R 775 '.$this->domain_root);
        shell_exec('chmod -R 775 '.$this->domain_public);

        $apacheVirtualHostBuilder = new \App\VirtualHosts\ApacheVirtualHostBuilder();
        $apacheVirtualHostBuilder->setDomain($this->domain);
        $apacheVirtualHostBuilder->setDomainPublic($this->domain_public);
        $apacheVirtualHostBuilder->setDomainRoot($this->domain_root);
        $apacheVirtualHostBuilder->setHomeRoot($this->home_root);
        $apacheVirtualHostBuilder->setUser($findHostingSubscription->system_username);
        $apacheVirtualHostBuilder->setUserGroup($webUserGroup);
        $apacheVirtualHostBuilder->setAdditionalServices($findHostingPlan->additional_services);

        if ($this->server_application_type == 'apache_nodejs') {
            $apacheVirtualHostBuilder->setPassengerAppRoot($this->domain_public);
            $apacheVirtualHostBuilder->setPassengerAppType('node');
            $apacheVirtualHostBuilder->setPassengerStartupFile('app.js');
        }

        if ($this->server_application_type == 'apache_python') {
            $apacheVirtualHostBuilder->setPassengerAppRoot($this->domain_public);
            $apacheVirtualHostBuilder->setPassengerAppType('python');
        }

        $apacheBaseConfig = $apacheVirtualHostBuilder->buildConfig();

        if (!empty($apacheBaseConfig)) {
            file_put_contents('/etc/apache2/sites-available/'.$this->domain.'.conf', $apacheBaseConfig);
            shell_exec('ln -s /etc/apache2/sites-available/'.$this->domain.'.conf /etc/apache2/sites-enabled/'.$this->domain.'.conf');
        }


        // Reload apache
        shell_exec('systemctl reload apache2');

        $findDomainSSLCertificate = \App\Models\DomainSslCertificate::where('domain', $this->domain)
            ->first();

        if ($findDomainSSLCertificate) {

            $sslCertificateFile = $this->home_root . '/certs/' . $this->domain . '/public/cert.pem';
            $sslCertificateKeyFile = $this->home_root . '/certs/' . $this->domain . '/private/key.private.pem';
            $sslCertificateChainFile = $this->home_root . '/certs/' . $this->domain . '/public/fullchain.pem';

            if (!empty($findDomainSSLCertificate->certificate)) {
                if (!is_dir($this->home_root . '/certs/' . $this->domain . '/public')) {
                    mkdir($this->home_root . '/certs/' . $this->domain . '/public', 0755, true);
                }
                file_put_contents($sslCertificateFile, $findDomainSSLCertificate->certificate);
            }
            if (!empty($findDomainSSLCertificate->private_key)) {
                if (!is_dir($this->home_root . '/certs/' . $this->domain . '/private')) {
                    mkdir($this->home_root . '/certs/' . $this->domain . '/private', 0755, true);
                }
                file_put_contents($sslCertificateKeyFile, $findDomainSSLCertificate->private_key);
            }
            if (!empty($findDomainSSLCertificate->certificate_chain)) {
                if (!is_dir($this->home_root . '/certs/' . $this->domain . '/public')) {
                    mkdir($this->home_root . '/certs/' . $this->domain . '/public', 0755, true);
                }
                file_put_contents($sslCertificateChainFile, $findDomainSSLCertificate->certificate_chain);
            }

            $apacheVirtualHostBuilder->setPort(443);
            $apacheVirtualHostBuilder->setSSLCertificateFile($sslCertificateFile);
            $apacheVirtualHostBuilder->setSSLCertificateKeyFile($sslCertificateKeyFile);
            $apacheVirtualHostBuilder->setSSLCertificateChainFile($sslCertificateChainFile);

            $apacheBaseConfigWithSSL = $apacheVirtualHostBuilder->buildConfig();
            if (!empty($apacheBaseConfigWithSSL)) {

                // Add SSL options conf file
                $apache2SSLOptionsSample = view('actions.samples.ubuntu.apache2-ssl-options-conf')->render();
                $apache2SSLOptionsFilePath = '/etc/apache2/TM/options-ssl-apache.conf';

                if (!file_exists($apache2SSLOptionsFilePath)) {
                    if (!is_dir('/etc/apache2/TM')) {
                        mkdir('/etc/apache2/TM');
                    }
                    file_put_contents($apache2SSLOptionsFilePath, $apache2SSLOptionsSample);
                }

                file_put_contents('/etc/apache2/sites-available/'.$this->domain.'-ssl.conf', $apacheBaseConfigWithSSL);
                shell_exec('ln -s /etc/apache2/sites-available/'.$this->domain.'-ssl.conf /etc/apache2/sites-enabled/'.$this->domain.'-ssl.conf');

                // Reload apache
                shell_exec('systemctl reload apache2');

            }

        }

    }
}
