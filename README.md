# WooCommerce Advanced Shipping Rules

Custom WooCommerce shipping method that demonstrates senior-level WooCommerce API usage, business rules, and clean architecture. It adds **Advanced Rules Shipping**, a method that prices shipments by weight tiers, cart value thresholds, and product tags (e.g., fragile items).

## Features
- Weight-based tiers with configurable rates
- Free shipping threshold by cart value
- Tag-based surcharge (e.g., `fragile`)
- Base rate per shipment
- Logging via `wc_get_logger()`
- Admin settings tab + method-level settings fields
- Ready for translations (`.pot` included)

## Use cases
- Stores with fragile goods that need surcharges
- Tiered pricing for light vs. heavy parcels
- Free shipping above a marketing threshold

## Installation
1. Copy the `woocommerce-advanced-shipping-rules` folder into `wp-content/plugins/`.
2. Activate **WooCommerce Advanced Shipping Rules** in WP Admin.
3. In WooCommerce → Settings → Shipping → Shipping zones, add the **Advanced Rules Shipping** method to a zone and configure rates.

## Configuration
- **Method settings (per zone):** Base rate, weight tiers, fragile tag slug, fragile surcharge, free-shipping threshold.
- **Global settings (tab "Advanced Shipping Rules")**: duplicates key knobs like fragile surcharge/threshold for demo purposes.
- See `admin/views/settings-page.php` for the admin view markup. Add your screenshot at `assets/screenshot-1.png` and reference it in the plugin page or README.

## Technical notes
- Hooks: `woocommerce_shipping_init`, `woocommerce_shipping_methods`, `plugins_loaded` (textdomain), `woocommerce_update_options_shipping_{id}`.
- Shipping class: `WASR\Shipping_Method` in `includes/class-wasr-shipping-method.php`.
- Bootstrap: `WASR\Plugin` in `includes/class-wasr-plugin.php`.
- Logger: `WASR\Logger` wraps `wc_get_logger()` with source `wasr`.
- Settings page: `WASR\Admin\Settings` adds a WooCommerce settings tab.
- Autoload: PSR-4 `WASR\` → `includes/` via Composer.

## Development
```bash
composer install
composer lint
composer test
```

## Testing
`tests/test-shipping-rules.php` contains a minimal PHPUnit stub; extend with WooCommerce test helpers as needed.

## Coding standards
Configured PHPCS with WordPress Core/Docs/Extra (`phpcs.xml`).

## License
GPL-2.0-or-later. See `LICENSE`.
