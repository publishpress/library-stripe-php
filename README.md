# Prefixed Stripe PHP library for PublishPress

Prefixed version of [`stripe/stripe-php`](https://github.com/stripe/stripe-php).
Namespace `Stripe` becomes `PublishPress\Stripe`.

## How to update the prefixed library

1. Change the pinned upstream version in `require-dev` (`stripe/stripe-php`) to the target fixed version.
2. Set `version` to that upstream version plus the next fourth digit (`20.3.1` becomes `20.3.1.1` for the first prefixed build).
3. Run `composer update`. Strauss prefixes into `lib/`; the generator refreshes `include.php` and `VersionLoader.php`.
4. Run `composer test:unit`, then `composer test:integration`.
5. Review the prefixed code in `lib/` by hand and commit.
6. Create a GitHub release named with the four-digit version so Packagist sees the tag.

Then run `composer update` in the plugins that consume the package.
