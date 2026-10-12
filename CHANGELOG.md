# CM Order Source Tracker for WooCommerce - Changelog

## 1.0.0 - 2026-10-11

Initial release.

- First-click and last-click attribution capture via 30-day cookies
- Captures UTM parameters (source, medium, campaign, content, term), referrer, and landing page
- Source type classification: paid_search, paid_social, email, social, organic, referral, direct
- GA4 client ID capture from `_ga` cookie
- Source column in WooCommerce orders list (HPOS + legacy post-based)
- UTM detail panel in order admin view
- HPOS (High-Performance Order Storage) compatibility
- Clean uninstall removes all order meta from both postmeta and HPOS tables
- 52 unit tests, zero external dependencies
