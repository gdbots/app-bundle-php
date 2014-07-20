<?php

/*
 * Custom composer script handler for  a collection of apps that have
 * a single composer.json and shared configs, autoloader, etc.
 *
 */
namespace Gdbots\Symfony\Distribution\Composer;

use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as BaseScriptHandler;
use Composer\Script\CommandEvent;

class ScriptHandler extends BaseScriptHandler
{
    /**
     * Builds the bootstrap files for each app.
     *
     * @param $event CommandEvent A instance
     */
    public static function buildBootstrap(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $apps = $options['symfony-apps'];
        $appDir = $options['symfony-app-dir'];

        foreach ($apps as $app) {
            $targetAppDir = $app . '/' . $appDir;
            if (!is_dir($targetAppDir)) {
                $event->getIO()->write('The symfony-app-dir ('. $targetAppDir .') specified in composer.json was not found in ' . getcwd() . ', can not build bootstrap file.');
                return;
            }
            static::executeBuildBootstrap($targetAppDir, $options['process-timeout']);
        }
    }

    /**
     * Clears the cache for each app
     *
     * @param $event CommandEvent A instance
     */
    public static function clearCache(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $apps = $options['symfony-apps'];
        $appDir = $options['symfony-app-dir'];

        foreach ($apps as $app) {
            $targetAppDir = $app . '/' . $appDir;
            if (!is_dir($targetAppDir)) {
                $event->getIO()->write('The symfony-app-dir ('. $targetAppDir .') specified in composer.json was not found in ' . getcwd() . ', can not clear the cache.');
                return;
            }
            static::executeCommand($event, $targetAppDir, 'cache:clear --no-warmup', $options['process-timeout']);
        }
    }

    /**
     * Installs the assets under the web root directory for each app
     *
     * For better interoperability, assets are copied instead of symlinked by default.
     *
     * Even if symlinks work on Windows, this is only true on Windows Vista and later,
     * but then, only when running the console with admin rights or when disabling the
     * strict user permission checks (which can be done on Windows 7 but not on Windows
     * Vista).
     *
     * @param $event CommandEvent A instance
     */
    public static function installAssets(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $apps = $options['symfony-apps'];
        $appDir = $options['symfony-app-dir'];
        $webDir = $options['symfony-web-dir'];

        $symlink = '';
        if ($options['symfony-assets-install'] == 'symlink') {
            $symlink = '--symlink ';
        } elseif ($options['symfony-assets-install'] == 'relative') {
            $symlink = '--symlink --relative ';
        }

        foreach ($apps as $app) {
            $targetAppDir = $app . '/' . $appDir;
            $targetWebDir = $app . '/' . $webDir;
            if (!is_dir($targetWebDir)) {
                $event->getIO()->write('The symfony-web-dir ('. $targetWebDir .') specified in composer.json was not found in ' . getcwd() . ', can not install assets.');
                return;
            }
            static::executeCommand($event, $targetAppDir, 'assets:install ' . $symlink . escapeshellarg($targetWebDir));
        }
    }

    /**
     * Updates the requirements file for each app
     *
     * @param $event CommandEvent A instance
     */
    public static function installRequirementsFile(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $apps = $options['symfony-apps'];
        $appDir = $options['symfony-app-dir'];
        $webDir = $options['symfony-web-dir'];

        $class = new \ReflectionClass(__CLASS__);
        $parentClassDir = dirname($class->getParentClass()->getFileName());

        foreach ($apps as $app) {
            $targetAppDir = $app . '/' . $appDir;
            $targetWebDir = $app . '/' . $webDir;
            if (!is_dir($targetAppDir)) {
                $event->getIO()->write('The symfony-app-dir ('. $targetAppDir .') specified in composer.json was not found in ' . getcwd() . ', can not install the requirements file.');
                return;
            }
            copy($parentClassDir . '/../Resources/skeleton/app/SymfonyRequirements.php', $targetAppDir . '/SymfonyRequirements.php');
            copy($parentClassDir . '/../Resources/skeleton/app/check.php', $targetAppDir . '/check.php');

            if (is_file($targetWebDir . '/config.php')) {
                copy($parentClassDir . '/../Resources/skeleton/web/config.php', $targetWebDir . '/config.php');
            }
        }
    }
}
