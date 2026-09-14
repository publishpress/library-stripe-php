<?php

namespace Helper;

use Codeception\Exception\ExtensionException;
use Codeception\Extension;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Copies fixture plugins into the WordPress test install before suites run.
 *
 * Replaces publishpress/codeception-extension-extended-copier, which extends
 * tad\WPBrowser\Extension\Copier (removed in wp-browser 4).
 *
 * Destinations often already exist under a container-owned plugins directory
 * that the host cannot recreate. Existing dest dirs are emptied and filled
 * in place instead of being deleted.
 */
class FixtureCopier extends Extension
{
    public function _initialize(): void
    {
        parent::_initialize();

        if (! $this->shouldCopy()) {
            return;
        }

        $files = $this->config['files'] ?? [];
        if ($files === []) {
            return;
        }

        foreach ($files as $key => $value) {
            if (is_int($key)) {
                [$source, $destination] = $this->splitMapping((string) $value);
            } else {
                $source = $this->resolveSource((string) $key);
                $destination = $this->resolveDestination((string) $value);
            }

            $this->ensureSource($source);
            $this->prepareDestination($destination);
            $this->copyPath($source, $destination);
        }
    }

    /**
     * Global extensions boot before the suite is known. Skip when the CLI
     * asked for Unit only so unit tests do not require a writable wp_test.
     */
    private function shouldCopy(): bool
    {
        $argv = $_SERVER['argv'] ?? [];
        $seenRun = false;

        foreach ($argv as $arg) {
            if ($arg === 'run') {
                $seenRun = true;
                continue;
            }

            if (! $seenRun || ($arg !== '' && $arg[0] === '-')) {
                continue;
            }

            $suites = explode(',', $arg);

            return in_array('Integration', $suites, true);
        }

        return true;
    }

    private function splitMapping(string $mapping): array
    {
        $parts = explode(':', $mapping, 2);
        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw new ExtensionException($this, sprintf('Invalid files mapping [%s].', $mapping));
        }

        return [
            $this->resolveSource($parts[0]),
            $this->resolveDestination($parts[1]),
        ];
    }

    private function resolveSource(string $source): string
    {
        if ($source !== '' && $source[0] !== '/') {
            $source = $this->getRootDir() . $source;
        }

        $resolved = realpath($source);
        if ($resolved === false) {
            throw new ExtensionException($this, sprintf('Source file [%s] does not exist.', $source));
        }

        return $resolved;
    }

    private function resolveDestination(string $destination): string
    {
        if ($destination !== '' && $destination[0] !== '/') {
            $destination = $this->getRootDir() . $destination;
        }

        return rtrim($destination, '/');
    }

    private function ensureSource(string $source): void
    {
        if (! is_readable($source)) {
            throw new ExtensionException($this, sprintf('Source file [%s] is not readable.', $source));
        }
    }

    private function prepareDestination(string $destination): void
    {
        if (is_dir($destination)) {
            $this->emptyDirectory($destination);
            if (! is_writable($destination)) {
                throw new ExtensionException(
                    $this,
                    sprintf('Destination dir [%s] is not writeable.', $destination)
                );
            }
            return;
        }

        if (file_exists($destination)) {
            if (! is_writable($destination) && ! is_writable(dirname($destination))) {
                throw new ExtensionException(
                    $this,
                    sprintf('Destination file [%s] is not writeable.', $destination)
                );
            }
            $this->removePath($destination);
            return;
        }

        $parent = dirname($destination);
        if (! is_dir($parent) && ! mkdir($parent, 0777, true) && ! is_dir($parent)) {
            throw new ExtensionException(
                $this,
                sprintf('Could not create destination parent dir [%s].', $parent)
            );
        }

        if (! is_writable($parent)) {
            throw new ExtensionException(
                $this,
                sprintf('Destination parent dir [%s] is not writeable.', $parent)
            );
        }
    }

    private function copyPath(string $source, string $destination): void
    {
        if (is_file($source)) {
            if (! copy($source, $destination)) {
                throw new ExtensionException($this, sprintf('Copy of [%s:%s] failed.', $source, $destination));
            }
            return;
        }

        if (! is_dir($destination) && ! mkdir($destination, 0777, true) && ! is_dir($destination)) {
            throw new ExtensionException($this, sprintf('Copy of [%s:%s] failed.', $source, $destination));
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            /** @var SplFileInfo $item */
            $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathname();
            if ($item->isDir()) {
                if (! is_dir($target) && ! mkdir($target, 0777, true) && ! is_dir($target)) {
                    throw new ExtensionException($this, sprintf('Copy of [%s:%s] failed.', $source, $destination));
                }
                continue;
            }

            if (! copy($item->getPathname(), $target)) {
                throw new ExtensionException($this, sprintf('Copy of [%s:%s] failed.', $source, $destination));
            }
        }
    }

    private function emptyDirectory(string $path): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            /** @var SplFileInfo $item */
            if ($item->isDir()) {
                rmdir($item->getPathname());
                continue;
            }
            unlink($item->getPathname());
        }
    }

    private function removePath(string $path): void
    {
        if (is_file($path) || is_link($path)) {
            unlink($path);
            return;
        }

        if (! is_dir($path)) {
            return;
        }

        $this->emptyDirectory($path);
        rmdir($path);
    }
}
