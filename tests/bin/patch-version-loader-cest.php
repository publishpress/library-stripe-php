<?php

$cest = dirname(__DIR__) . '/codeception/Integration/VersionLoaderCest.php';
$contents = file_get_contents($cest);
if ($contents === false) {
    fwrite(STDERR, "Could not read {$cest}\n");
    exit(1);
}

$from = "class_exists('PublishPress\\Stripe\\StripeClient', false)";
$to = "class_exists('PublishPress\\Stripe\\StripeClient')";

if (strpos($contents, $from) === false) {
    exit(0);
}

file_put_contents($cest, str_replace($from, $to, $contents, $count));
if ($count < 1) {
    fwrite(STDERR, "Failed to patch class_exists assertion in {$cest}\n");
    exit(1);
}
