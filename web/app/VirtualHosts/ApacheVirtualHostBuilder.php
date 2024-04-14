<?php

namespace App\VirtualHosts;

class ApacheVirtualHostBuilder
{
    public $port = 80;

    public $domain;
    public $domainPublic;

    public $domainRoot;
    public $homeRoot;

    public $user;
    public $userGroup;
    public $additionalServices = [];

    public $sslCertificateFile = null;
    public $sslCertificateKeyFile = null;
    public $sslCertificateChainFile = null;

    public $passengerAppRoot = null;
    public $passengerAppType = null;
    public $passengerStartupFile = null;

    public function setPort($port)
    {
        $this->port = $port;
    }
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    public function setDomainPublic($domainPublic)
    {
        $this->domainPublic = $domainPublic;
    }

    public function setDomainRoot($domainRoot)
    {
        $this->domainRoot = $domainRoot;
    }

    public function setHomeRoot($homeRoot)
    {
        $this->homeRoot = $homeRoot;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function setUserGroup($userGroup)
    {
        $this->userGroup = $userGroup;
    }

    public function setAdditionalServices($additionalServices)
    {
        $this->additionalServices = $additionalServices;
    }

    public function setSSLCertificateFile($sslCertificateFile)
    {
        $this->sslCertificateFile = $sslCertificateFile;
    }

    public function setSSLCertificateKeyFile($sslCertificateKeyFile)
    {
        $this->sslCertificateKeyFile = $sslCertificateKeyFile;
    }

    public function setSSLCertificateChainFile($sslCertificateChainFile)
    {
        $this->sslCertificateChainFile = $sslCertificateChainFile;
    }

    public function setPassengerAppRoot($passengerAppRoot)
    {
        $this->passengerAppRoot = $passengerAppRoot;
    }

    public function setPassengerAppType($passengerAppType)
    {
        $this->passengerAppType = $passengerAppType;
    }

    public function setPassengerStartupFile($passengerStartupFile)
    {
        $this->passengerStartupFile = $passengerStartupFile;
    }

    public function buildConfig()
    {
        $settings = [
            'port' => $this->port,
            'domain' => $this->domain,
            'domainPublic' => $this->domainPublic,
            'domainRoot' => $this->domainRoot,
            'homeRoot' => $this->homeRoot,
            'user' => $this->user,
            'group' => $this->userGroup,
            'enableRuid2' => true,
            'sslCertificateFile' => $this->sslCertificateFile,
            'sslCertificateKeyFile' => $this->sslCertificateKeyFile,
            'sslCertificateChainFile' => $this->sslCertificateChainFile,
            'passengerAppRoot' => $this->passengerAppRoot,
            'passengerAppType' => $this->passengerAppType,
            'passengerStartupFile' => $this->passengerStartupFile,
        ];

        $apacheVirtualHostConfigs = app()->virtualHostManager->getConfigs($this->additionalServices);

        $settings = array_merge($settings, $apacheVirtualHostConfigs);

        $apache2Sample = view('actions.samples.ubuntu.apache2-conf', $settings)->render();

        return $apache2Sample;
    }
}
