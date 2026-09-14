<?php

namespace PublishPress\StripePhp;

if (! function_exists(__NAMESPACE__ . '\\register2Dot0Dot0Dot2')) {
    // Register at 1, init at 2: see outdated-lib-plugin-1 include.php comment.
    // WPLoader must load outdated plugins before updated-lib-plugin.
    if (! class_exists('PublishPress\\StripePhp\\Versions', false)) {
        require_once __DIR__ . '/Versions.php';

        add_action('plugins_loaded', [Versions::class, 'initializeLatestVersion'], 2, 0);
    }

    add_action('plugins_loaded', __NAMESPACE__ . '\\register2Dot0Dot0Dot2', 1, 0);

    function register2Dot0Dot0Dot2()
    {
        if (! class_exists('PublishPress\\Stripe\\StripeClient', false)) {
            $versions = Versions::getInstance();
            $versions->register('2.0.0.2', __NAMESPACE__ . '\\initialize2Dot0Dot0Dot2');
        }
    }

    function initialize2Dot0Dot0Dot2()
    {
        require_once __DIR__ . '/autoload.php';
    }
}
