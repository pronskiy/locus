<?php

namespace Locus;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class PhpPlugin implements PluginInterface, EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
        ];
    }

    private static function installPhp(Composer $composer)
    {
        $package = $composer->getPackage();

        $requires = $package->getRequires();

        if (!isset($requires['php'])) {
            return;
        }

        $php_version = $requires['php']->getPrettyConstraint();

        $vendor_dir = $composer->getConfig()->get('vendor-dir');
        PhpInstaller::downloadPhp($php_version,  $vendor_dir . '/php/', $vendor_dir . '/pronskiy/locus/bin');
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
