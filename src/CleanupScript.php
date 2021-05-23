<?php

namespace Oxch\Composer;

use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Script\Event;
use Composer\Util\Filesystem;
use Exception;

class CleanupScript
{
    const DEFAULT_RULES_FILENAME = 'rules.json';
    const PACKAGES_KEY = 'packages';
    const DEFAULTS_KEY = 'defaults';

    /** @var Filesystem $filesystem */
    protected static $filesystem;
    protected static $rulesConfig;
    protected static $vendorDir;
    protected static $filesDeleted = ['amount' => 0, 'freeSpace' => 0];

    public static function cleanVendor(Event $event)
    {
        $composer = $event->getComposer();
        static::$filesystem = new Filesystem();
        $rulesConfigFilename = self::rulesConfigFilename($event);
        static::$rulesConfig = json_decode(file_get_contents($rulesConfigFilename), true);
        static::$vendorDir = $composer->getConfig()->get('vendor-dir');
        $repository = $composer->getRepositoryManager()->getLocalRepository();

        foreach ($repository->getPackages() as $package) {
            if ($package instanceof PackageInterface) {
                static::cleanSinglePackage($package, $event->getIO());
            }
        }

        $freeSpaceMb = round(static::$filesDeleted['freeSpace']/1024/1024,2);
        $event->getIO()->write("Removed " . static::$filesDeleted['amount'] . " files and saved " . $freeSpaceMb . " MB of disc space.");
    }

    protected static function cleanSinglePackage(PackageInterface $package, IOInterface $io)
    {
        $targetDir = $package->getTargetDir();
        $packageName = $package->getPrettyName();
        $packageDir = $targetDir ? $packageName . '/' . $targetDir : $packageName;

        if (!isset(static::$rulesConfig[self::PACKAGES_KEY][$packageName])) {
            return false;
        }

        $defaultFilesToRemove = implode(' ', static::$rulesConfig[self::DEFAULTS_KEY]);
        $filesToRemove = trim($defaultFilesToRemove . ' ' . static::$rulesConfig[self::PACKAGES_KEY][$packageName]);

        $dir = static::$filesystem->normalizePath(realpath(static::$vendorDir . '/' . $packageDir));
        if (!is_dir($dir)) {
            return false;
        }

        $patterns = explode(' ', trim($filesToRemove));
        foreach ($patterns as $pattern) {
            try {
                foreach (glob($dir . '/' . $pattern) as $file) {
                    static::$filesDeleted['amount']++;
                    static::$filesDeleted['freeSpace'] += filesize($file);
                    static::$filesystem->remove($file);
                }
            } catch (Exception $e) {
                $io->write("Could not remove $packageDir ($pattern): " . $e->getMessage());
            }
        }

        return true;
    }

    private static function rulesConfigFilename(Event $event)
    {
        $defaultRulesConfigFilename = __DIR__ . '/../' . static::DEFAULT_RULES_FILENAME;
        $rulesConfigFilename = $defaultRulesConfigFilename;

        $userRulesConfigFilename = isset($event->getArguments()[0]) ? $event->getArguments()[0] : false;
        if ($userRulesConfigFilename) {
            $userRulesConfigFilename = getcwd() . '/' . $userRulesConfigFilename; // path starting at working directory
            if (file_exists($userRulesConfigFilename)) {
                $rulesConfigFilename = $userRulesConfigFilename;
            } else {
                if (!$event->getIO()->askConfirmation("Can not read passed rules config file. Using default config. Continue?")) {
                    exit; // abort
                }
            }
        }
        return $rulesConfigFilename;
    }
}
