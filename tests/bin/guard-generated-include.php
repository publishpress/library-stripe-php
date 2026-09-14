<?php

$include = dirname(__DIR__, 2) . '/lib/include.php';
$autoload = dirname(__DIR__, 2) . '/lib/autoload.php';

$contents = file_get_contents($include);
if ($contents === false) {
    fwrite(STDERR, "Could not read {$include}\n");
    exit(1);
}

$autoloadContents = file_get_contents($autoload);
if ($autoloadContents === false
    || ! preg_match('/ComposerAutoloaderInit[0-9a-f]+/', $autoloadContents, $match)
) {
    fwrite(STDERR, "Could not find ComposerAutoloaderInit class in {$autoload}\n");
    exit(1);
}

$initClass = $match[0];

$guarded = <<<PHP
        if (! class_exists('PublishPress\\Stripe\\StripeClient', false)
            && ! class_exists('{$initClass}', false)
        ) {
            require_once __DIR__ . '/autoload.php';
        }

PHP;

if (strpos($contents, "class_exists('{$initClass}', false)") !== false
    && substr_count($contents, 'require_once __DIR__ . \'/autoload.php\';') === 1
    && strpos($contents, "class_exists('PublishPress\\\\Composer\\\\Autoload\\\\ClassLoader', false)") === false
) {
    exit(0);
}

$pattern = '/function\s+(initialize[A-Za-z0-9]+)\s*\(\s*\)\s*\{(.*?)\n    \}/s';
if (! preg_match($pattern, $contents, $fnMatch)) {
    fwrite(STDERR, "Could not find initialize function in {$include}\n");
    exit(1);
}

$fnName = $fnMatch[1];
$body = $fnMatch[2];

$body = preg_replace(
    '/\n        if \(! class_exists\([^\n]+?\n(?:.*?\n)*?        \}\n?/s',
    "\n",
    $body
);
$body = preg_replace(
    '/\n        require_once __DIR__ \. \'\/autoload\.php\';\n/',
    "\n",
    $body
);

$newBody = "\n" . $guarded . ltrim($body, "\n");
$replacement = "function {$fnName}()\n    {{$newBody}\n    }";

$contents = preg_replace($pattern, $replacement, $contents, 1, $count);
if ($count !== 1) {
    fwrite(STDERR, "Expected to rewrite one initialize function, changed {$count}\n");
    exit(1);
}

file_put_contents($include, $contents);
