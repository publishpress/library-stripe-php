<?php

namespace PublishPress\StripePhp;

if (! function_exists(__NAMESPACE__ . '\\register2Dot0Dot0Dot1')) {
    // Register at 1, init at 2: StripeClient is a class, so init-before-register
    // would make class_exists true and skip outdated registration. WPLoader must
    // load this plugin before updated-lib-plugin so Versions (not VersionLoader)
    // owns initializeLatestVersion.
    if (! class_exists('PublishPress\\StripePhp\\Versions', false)) {
        require_once __DIR__ . '/Versions.php';

        add_action('plugins_loaded', [Versions::class, 'initializeLatestVersion'], 2, 0);
    }

    add_action('plugins_loaded', __NAMESPACE__ . '\\register2Dot0Dot0Dot1', 1, 0);

    function register2Dot0Dot0Dot1()
    {
        if (! class_exists('PublishPress\\Stripe\\StripeClient', false)) {
            $versions = Versions::getInstance();
            $versions->register('2.0.0.1', __NAMESPACE__ . '\\initialize2Dot0Dot0Dot1');
        }
    }

    function initialize2Dot0Dot0Dot1()
    {
        require_once __DIR__ . '/autoload.php';
    }
}
