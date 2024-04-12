<?php

namespace Locus;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Semver\VersionParser;

class PhpPlugin implements PluginInterface, EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
        ];
    }

    public static function normalizePhpVersion(string $php_version): string
    {
        $semverParser = new VersionParser();
        $constraint = $semverParser->parseConstraints($php_version);

        // @TODO Un-hardcode this list
        $available_versions = array_reverse([
            '8.0.30', 
            '8.1.23', '8.1.25', '8.1.26', '8.1.27', 
            '8.2.10', '8.2.12', '8.2.13', '8.2.14', '8.2.15', '8.2.16', '8.2.17', 
            '8.3.0', '8.3.1', '8.3.2', '8.3.3', '8.3.4',
        ]);
        
        foreach ($available_versions as $version) {
            if ($constraint->matches( $semverParser->parseConstraints($version))) {
                $php_version = $version;
                break;
            }
        }
        
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
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // TODO: Cleanup
    }
}
