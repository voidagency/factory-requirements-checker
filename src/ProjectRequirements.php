<?php

namespace Factory\Requirements;

/**
 * This class specifies all requirements and optional recommendations that
 * are necessary to run Factory.
 */
class ProjectRequirements extends RequirementCollection
{
    const REQUIRED_PHP_VERSION_3x = '5.5.9';
    const REQUIRED_PHP_VERSION_4x = '7.1.3';
    const REQUIRED_PHP_VERSION_5x = '8.1';

    public function __construct($rootDir)
    {
        $installedPhpVersion = phpversion();
        $symfonyVersion = null;
        if (file_exists($kernel = $rootDir.'/vendor/symfony/http-kernel/Kernel.php')) {
            $contents = file_get_contents($kernel);
            preg_match('{const VERSION += +\'([^\']+)\'}', $contents, $matches);
            $symfonyVersion = $matches[1];
        }

        $rootDir = $this->getComposerRootDir($rootDir);
        $options = $this->readComposer($rootDir);

        $phpVersion = self::REQUIRED_PHP_VERSION_3x;
        if (null !== $symfonyVersion) {
            if (version_compare($symfonyVersion, '5.0.0', '>=')) {
                $phpVersion = self::REQUIRED_PHP_VERSION_5x;
            } elseif (version_compare($symfonyVersion, '4.0.0', '>=')) {
                $phpVersion = self::REQUIRED_PHP_VERSION_4x;
            }
        }

        $this->addRequirement(
            version_compare($installedPhpVersion, $phpVersion, '>='),
            sprintf('PHP version must be at least %s (%s installed)', $phpVersion, $installedPhpVersion),
            sprintf('You are running PHP version "<strong>%s</strong>", but Factory needs at least PHP "<strong>%s</strong>" to run.
            Before using Factory, upgrade your PHP installation, preferably to the latest version.',
                $installedPhpVersion, $phpVersion),
            sprintf('Install PHP %s or newer (installed version is %s)', $phpVersion, $installedPhpVersion)
        );

        if (version_compare($installedPhpVersion, $phpVersion, '>=')) {
            $this->addRequirement(
                in_array(@date_default_timezone_get(), \DateTimeZone::listIdentifiers(), true),
                sprintf('Configured default timezone "%s" must be supported by your installation of PHP', @date_default_timezone_get()),
                'Your default timezone is not supported by PHP. Check for typos in your <strong>php.ini</strong> file and have a look at the list of deprecated timezones at <a href="http://php.net/manual/en/timezones.others.php">http://php.net/manual/en/timezones.others.php</a>.'
            );
        }

//        $this->addRequirement(
//            is_dir($rootDir.'/'.$options['vendor-dir'].'/composer'),
//            'Vendor libraries must be installed',
//            'Vendor libraries are missing. Install composer following instructions from <a href="http://getcomposer.org/">http://getcomposer.org/</a>. '.
//            'Then run "<strong>php composer.phar install</strong>" to install them.'
//        );

        if (is_dir($cacheDir = $rootDir.'/'.$options['var-dir'].'/cache')) {
            $this->addRequirement(
                is_writable($cacheDir),
                sprintf('%s/cache/ directory must be writable', $options['var-dir']),
                sprintf('Change the permissions of "<strong>%s/cache/</strong>" directory so that the web server can write into it.', $options['var-dir'])
            );
        }

        if (is_dir($logsDir = $rootDir.'/'.$options['var-dir'].'/log')) {
            $this->addRequirement(
                is_writable($logsDir),
                sprintf('%s/log/ directory must be writable', $options['var-dir']),
                sprintf('Change the permissions of "<strong>%s/log/</strong>" directory so that the web server can write into it.', $options['var-dir'])
            );
        }

        if (version_compare($installedPhpVersion, $phpVersion, '>=')) {
            $this->addRequirement(
                in_array(@date_default_timezone_get(), \DateTimeZone::listIdentifiers(), true),
                sprintf('Configured default timezone "%s" must be supported by your installation of PHP', @date_default_timezone_get()),
                'Your default timezone is not supported by PHP. Check for typos in your <strong>php.ini</strong> file and have a look at the list of deprecated timezones at <a href="http://php.net/manual/en/timezones.others.php">http://php.net/manual/en/timezones.others.php</a>.'
            );
        }
    }

    private function getComposerRootDir($rootDir)
    {
        $dir = $rootDir;
        while (!file_exists($dir.'/composer.json')) {
            if ($dir === dirname($dir)) {
                return $rootDir;
            }

            $dir = dirname($dir);
        }

        return $dir;
    }

    private function readComposer($rootDir)
    {
        $composer = json_decode(file_get_contents($rootDir.'/composer.json'), true);
        $options = array(
            'bin-dir' => 'bin',
            'conf-dir' => 'conf',
            'etc-dir' => 'etc',
            'src-dir' => 'src',
            'var-dir' => 'var',
            'public-dir' => 'public',
            'vendor-dir' => 'vendor',
        );

        foreach (array_keys($options) as $key) {
            if (isset($composer['extra'][$key])) {
                $options[$key] = $composer['extra'][$key];
            } elseif (isset($composer['extra']['symfony-'.$key])) {
                $options[$key] = $composer['extra']['symfony-'.$key];
            } elseif (isset($composer['config'][$key])) {
                $options[$key] = $composer['config'][$key];
            }
        }

        return $options;
    }
}
