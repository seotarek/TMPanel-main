#=========================================================================#
# TM HOSTING PANEL - Default Web Domain Template                       #
# DO NOT MODIFY THIS FILE! CHANGES WILL BE LOST WHEN REBUILDING DOMAINS   #
# https://to2mor.com/docs/server-administration/web-templates.html    #
#=========================================================================#

<VirtualHost *:{{$port}}>


    ServerName {{$domain}}
    DocumentRoot {{$domainPublic}}
    SetEnv APP_DOMAIN {{$domain}}

    @if(isset($enableRuid2) and $enableRuid2)

    #RDocumentChRoot {{$domainPublic}}
    #SuexecUserGroup {{$user}} {{$group}}
    #RUidGid {{$user}} {{$group}}

    @endif



    <Directory {{$domainPublic}}>

        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted

        @if(isset($enableRuid2) and $enableRuid2)

        RMode config
        RUidGid {{$user}} {{$group}}

        @endif

        @if($passengerAppRoot !== null)

        PassengerAppRoot {{$passengerAppRoot}}

        PassengerAppType {{$passengerAppType}}

        @if($passengerStartupFile !== null)
        PassengerStartupFile {{$passengerStartupFile}}
        @endif

        @else

        @php
        $appendOpenBaseDirs = $homeRoot;
        if (isset($phpAdminValueOpenBaseDirs)
                && is_array($phpAdminValueOpenBaseDirs)
                && !empty($phpAdminValueOpenBaseDirs)) {
            $appendOpenBaseDirs .= ':' . implode(':', $phpAdminValueOpenBaseDirs);
        }
        @endphp

        php_admin_value open_basedir {{$appendOpenBaseDirs}}

        php_admin_value upload_tmp_dir {{$homeRoot}}/tmp
        php_admin_value session.save_path {{$homeRoot}}/tmp
        php_admin_value sys_temp_dir {{$homeRoot}}/tmp

        @endif

    </Directory>

    @if(!empty($sslCertificateFile) and !empty($sslCertificateKeyFile))

    SSLEngine on
    SSLCertificateFile {{$sslCertificateFile}}
    SSLCertificateKeyFile {{$sslCertificateKeyFile}}

    @if (!empty($sslCertificateChainFile))

    SSLCertificateChainFile {{$sslCertificateChainFile}}

    @endif

    Include /etc/apache2/TM/options-ssl-apache.conf

    @endif

</VirtualHost>

