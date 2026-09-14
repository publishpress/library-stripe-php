<?php

$baseDirectory = dirname(__DIR__) . '/lib/stripe/stripe-php';
$directories = [
    $baseDirectory . '/lib',
    $baseDirectory,
];

if (! is_dir($baseDirectory)) {
    return;
}

foreach ($directories as $directory) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (! $file->isFile() || ! in_array($file->getExtension(), ['php', 'md'], true)) {
            continue;
        }

        $path = $file->getPathname();
        $contents = file_get_contents($path);
        if (false === $contents) {
            fwrite(STDERR, "Could not read {$path}\n");
            exit(1);
        }

        $updated = preg_replace_callback(
            '/(?<!PublishPress)\\\\Stripe\\\\/',
            function () {
                return '\\PublishPress\\Stripe\\';
            },
            $contents
        );
        $keyPrefixes = '(?:' . implode('|', ['sk', 'rk', 'pk']) . ')';
        $keyModes = '(?:' . implode('|', ['test', 'live']) . ')';

        $updated = preg_replace(
            '/\b' . $keyPrefixes . '_' . $keyModes . '_[A-Za-z0-9_\.]+/',
            'STRIPE_KEY_REDACTED',
            $updated
        );
        $updated = preg_replace('/\b' . 'whsec' . '_[A-Za-z0-9_\.]+/', 'STRIPE_WEBHOOK_SECRET_REDACTED', $updated);

        if (null === $updated) {
            fwrite(STDERR, "Could not rewrite {$path}\n");
            exit(1);
        }

        if ($updated !== $contents) {
            file_put_contents($path, $updated);
        }
    }
}
