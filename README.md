# CM Order Source Tracker for WooCommerce

[![Tests](https://img.shields.io/badge/tests-52%20passing-brightgreen)](tests/)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue)](composer.json)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-7.0%2B-purple)](cm-order-source-tracker.php)
[![License](https://img.shields.io/badge/license-GPL--2.0--or--later-blue)](https://www.gnu.org/licenses/gpl-2.0.html)

Captures UTM parameters, referrer, and landing page from the visitor session and attaches first-click and last-click attribution data to WooCommerce orders.

## Features

- Captures all 5 UTM parameters plus referrer, landing page, and GA4 client ID
- Stores two attribution models per order: first click (set once) and last click (updated on every UTM visit)
- Source type classification: `paid_search`, `paid_social`, `email`, `social`, `organic`, `referral`, `direct`
- Source column in the orders list (HPOS + legacy post-based)
- UTM detail panel in the order admin view
- No external requests, no JavaScript, no third-party dependencies
- Full HPOS (High-Performance Order Storage) compatibility
- Clean uninstall - removes all order meta on plugin deletion

## Order Meta

| Key | Model | When set |
|-----|-------|----------|
| `_cm_ost_first` | First click | Set once on first visit with UTMs or referrer |
| `_cm_ost_last` | Last click | Updated on every visit with UTMs |

Payload shape:

```json
{
  "utm_source": "google",
  "utm_medium": "cpc",
  "utm_campaign": "black-friday",
  "utm_content": "banner-a",
  "utm_term": "woocommerce plugins",
  "referrer": "https://google.com/",
  "landing_page": "https://example.com/products/widget/",
  "source_type": "paid_search",
  "ga_client_id": "123456789.1728000000"
}
```

## Requirements

- PHP 8.1+
- WordPress 6.0+
- WooCommerce 7.0+

## Installation

```bash
composer require claritymomentum/cm-order-source-tracker
```

Or download and drop into `/wp-content/plugins/`.

## Development

```bash
composer install
composer test      # PHPUnit - 52 tests
composer phpcs     # PHPCS with WordPress coding standards
```

## Architecture

```
includes/
├── Tracker/
│   ├── UtmExtractor.php      # Pure: extract + sanitize UTM params
│   ├── SourceClassifier.php  # Pure: classify source type, generate label
│   ├── CookieStore.php       # Read/write first+last click cookies
│   └── OrderCapture.php      # WP hooks: capture on init, save to order
└── Admin/
    ├── OrderSourceColumn.php  # Orders list column (HPOS + legacy)
    └── OrderSourceMetaBox.php # UTM detail panel in order view
```

## Related Products

- [SignalStream](https://claritymomentum.io/signalstream/) - Server-side GA4 and Meta CAPI relay for WooCommerce. Bypass iOS privacy restrictions and ad blockers with server-side event delivery.
- [Moneris WooCommerce Gateway](https://claritymomentum.io/products/moneris-woocommerce-checkout/) - Moneris Checkout and HPP payment gateway with WooCommerce Subscriptions support.
- [Amazon Shipping for WooCommerce](https://claritymomentum.io/products/amazon-shipping-woocommerce/) - Live Amazon Shipping rates and label printing in WooCommerce.

## License

GPL-2.0-or-later. See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).

Built by [Clarity Momentum](https://claritymomentum.io).
