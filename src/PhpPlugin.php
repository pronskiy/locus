<?php

namespace Locus;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Installer\PackageEvent;

class PhpPlugin implements PluginInterface, EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
//            PackageEvents::PRE_PACKAGE_INSTALL => 'prePackageInstall',
        ];
    }

    public static function prePackageInstall(PackageEvent $event)
    {
        self::installPhp($event->getComposer());
    }
    
    public static function normalizePhpVersion(string $php_version): string
    {
        $php_version = rtrim(str_replace(['^', '~', '*', ' ', '>', '='], '', $php_version), '.');

        if (substr_count($php_version, '.') == 2) {
            return $php_version;
        }

        $versionData = file_get_contents('https://phpreleases.com/api/releases/' . $php_version);
        $php_releases = json_decode($versionData, flags: JSON_THROW_ON_ERROR);

        $php_version = sprintf('%d.%d.%d', $php_releases[0]->major, $php_releases[0]->minor, $php_releases[0]->release); 
        
        return $php_version;
    }
    
    private static function installPhp(Composer $composer)
    {
        $package = $composer->getPackage();
        
        $os = match(php_uname('s')) {
            'Darwin' => 'macos',
            'Linux' => 'linux',
            'Windows NT' => 'win',
        };

        $arch = match(php_uname('m')) {
            'arm64' => 'aarch64',
            'x86_64' => 'x86_64',
        };

        $requires = $package->getRequires();
        
        if (!isset($requires['php'])) {
            return;
        }
        
        $php_version = $requires['php']->getPrettyConstraint();

        $php_version = self::normalizePhpVersion($php_version);

        $php_file = 'php-' . $php_version . '-cli-' . $os . '-' . $arch . '.tar.gz';

        $base_url = 'https://dl.static-php.dev/static-php-cli/common/';

        $downloadUrl = $base_url . $php_file;
        $vendorDir = $composer->getConfig()->get('vendor-dir');
        $downloadPath = $vendorDir . '/php/';
        $binDir = $vendorDir . '/pronskiy/locus/bin';
        
        is_dir($downloadPath) || mkdir(directory: $downloadPath, recursive: true);
        is_dir($binDir) || mkdir(directory: $binDir, recursive: true);

        file_put_contents($downloadPath.$php_file, fopen($downloadUrl, 'r'));

        $phar = new \PharData($downloadPath.$php_file);
        $phar->extractTo(directory: $binDir, overwrite: true);
    }

    public function activate(Composer $composer, IOInterface $io)
    {
        self::installPhp($composer);
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        // TODO: Implement deactivate() method.
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // TODO: Implement uninstall() method.
    }
}
