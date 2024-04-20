<?php

namespace Locus;

use Archive_Tar;
use Composer\Semver\VersionParser;

class PhpInstaller
{
    public static function downloadPhp(string $php_version, $download_path, $bin_dir): bool
    {
        $os = match (php_uname('s')) {
            'Darwin' => 'macos',
            'Linux' => 'linux',
            'Windows NT' => 'win',
        };

        $arch = match (php_uname('m')) {
            'arm64' => 'aarch64',
            'x86_64' => 'x86_64',
        };

        $php_version = self::normalizePhpVersion($php_version);

        $php_file = 'php-' . $php_version . '-cli-' . $os . '-' . $arch . '.tar.gz';

        $base_url = 'https://dl.static-php.dev/static-php-cli/common/';

        $downloadUrl = $base_url . $php_file;

        is_dir($download_path) || mkdir(directory: $download_path, recursive: true);
        is_dir($bin_dir) || mkdir(directory: $bin_dir, recursive: true);

        self::download($downloadUrl, $download_path.$php_file);
        
        $ar = new Archive_Tar($download_path . $php_file);
        $ar->extract($bin_dir);

        return true;
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
            if ($constraint->matches($semverParser->parseConstraints($version))) {
                $php_version = $version;
                break;
            }
        }

        return $php_version;
    }

    public static function download($file_source, $file_target)
    {
        $context_options= [
            'ssl' => [
                'cafile' => __DIR__.'/cacert.pem',
                'verify_peer'=> true,
                'verify_peer_name'=> true,
            ],
        ];
        file_put_contents(
            $file_target,
            fopen($file_source, 'r', false, stream_context_create($context_options))
        );
    }
}
