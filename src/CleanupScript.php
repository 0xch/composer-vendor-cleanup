<?php

namespace Oxch\Composer;

use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Script\Event;
use Composer\Util\Filesystem;

class CleanupScript
{
    /** @var Filesystem $filesystem */
    protected static $filesystem;
    protected static $rules;
    protected static $vendorDir;

    public static function cleanVendor(Event $event)
    {
        $composer = $event->getComposer();
        self::$filesystem = new Filesystem();
        self::$rules = CleanupRules::getRules();
        self::$vendorDir = $composer->getConfig()->get('vendor-dir');
        $repository = $composer->getRepositoryManager()->getLocalRepository();

        foreach ($repository->getPackages() as $package) {
            if ($package instanceof PackageInterface) {
                self::cleanPackage($package, $event->getIO());
            }
        }
    }

    protected static function cleanPackage(PackageInterface $package, IOInterface $io)
    {
        $targetDir = $package->getTargetDir();
        $packageName = $package->getPrettyName();
        $packageDir = $targetDir ? $packageName . '/' . $targetDir : $packageName;

        $rules = isset(self::$rules[$packageName]) ? self::$rules[$packageName] : null;
        if (!$rules) {
            return false;
        }

        $dir = self::$filesystem->normalizePath(realpath(self::$vendorDir . '/' . $packageDir));
        if (!is_dir($dir)) {
            return false;
        }

        foreach ((array)$rules as $part) {
            // Split patterns for single globs
            $patterns = explode(' ', trim($part));

            foreach ($patterns as $pattern) {
                try {
                    foreach (glob($dir . '/' . $pattern) as $file) {
                        self::$filesystem->remove($file);
                    }
                } catch (\Exception $e) {
                    $io->write("Could not parse $packageDir ($pattern): " . $e->getMessage());
                }
            }
        }

        return true;
    }
}
