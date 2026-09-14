<?php

class LoaderCest
{
    public function loadsPrefixedStripeClient(UnitTester $I)
    {
        require_once dirname(__DIR__, 3) . '/lib/include.php';

        $I->assertTrue(class_exists('PublishPress\\Stripe\\StripeClient'));
        $I->assertFalse(class_exists('Stripe\\StripeClient', false));
    }
}
