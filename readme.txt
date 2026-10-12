=== CM Order Source Tracker ===
Contributors: claritymomentum
Tags: woocommerce, utm, attribution, order source, tracking
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 1.0.0
WC requires at least: 7.0
WC tested up to: 9.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Captures UTM parameters, referrer, and landing page from the visitor session and attaches first-click and last-click attribution data to WooCommerce orders.

== Description ==

**Know exactly where every order came from.**

CM Order Source Tracker silently captures the UTM parameters, referrer URL, and landing page from each visitor session and stores them against the WooCommerce order as first-click and last-click attribution data.

No analytics account required. No JavaScript injection. No external requests. Just clean, server-side order meta you own and control.

**What it captures:**

* `utm_source`, `utm_medium`, `utm_campaign`, `utm_content`, `utm_term`
* HTTP referrer
* Landing page URL
* Source type classification: paid search, paid social, email, organic, social, referral, or direct
* GA4 client ID (from `_ga` cookie, if available)

**How it works:**

1. Visitor lands on your store - the plugin reads UTM params and referrer from the URL
2. Data is written to two cookies: `cm_ost_first` (set once, never overwritten) and `cm_ost_last` (updated on every UTM visit)
3. When the order is placed, both attribution payloads are saved as order meta (`_cm_ost_first` and `_cm_ost_last`)
4. A Source column appears in the orders list, and a detail panel in each order view

**Built by Clarity Momentum**

CM Order Source Tracker is built and maintained by [Clarity Momentum](https://claritymomentum.io) - a WooCommerce and eCommerce development consultancy.

Need server-side event tracking to bypass iOS restrictions and ad blockers? Check out [SignalStream](https://claritymomentum.io/signalstream/) - server-side GA4 and Meta CAPI relay for WooCommerce.

Processing card payments? Our [Moneris WooCommerce Gateway](https://claritymomentum.io/products/moneris-woocommerce-checkout/) supports Moneris Checkout and HPP with full WooCommerce Subscriptions support.

Shipping with Amazon? Our [Amazon Shipping for WooCommerce](https://claritymomentum.io/products/amazon-shipping-woocommerce/) plugin connects your WooCommerce store directly to Amazon Shipping carrier rates and label printing.

Questions or custom development? [Contact Clarity Momentum](https://claritymomentum.io/contact-us/).

== Installation ==

1. Upload the `cm-order-source-tracker` folder to `/wp-content/plugins/`
2. Activate through the Plugins menu
3. No configuration needed - attribution capture starts immediately

== Frequently Asked Questions ==

= Does this slow down my store? =

No. Cookie reads and writes are in-process PHP operations. There are no external API calls, no database queries on the front end, and no JavaScript added to the page.

= Does it work with WooCommerce HPOS (High-Performance Order Storage)? =

Yes. The plugin declares HPOS compatibility and the orders list column works on both the HPOS orders screen and the legacy post-based screen.

= What is the difference between first click and last click? =

First click is the UTM data from the very first visit that had UTMs or a referrer - it never changes once set. Last click is updated on every subsequent visit that has UTM parameters. Both are stored so you can compare.

= Does it work if the customer visits multiple times before ordering? =

Yes. First click is captured on the initial visit and preserved in a 30-day cookie. Last click is updated on each visit that carries UTM parameters.

= Does it capture data if the customer clears cookies? =

No. Attribution relies on browser cookies. If a customer clears cookies between visits, new first-click data will be captured on the next visit.

= Is this GDPR compliant? =

The cookies set by this plugin are functional/analytics cookies that track session attribution. You should disclose them in your cookie policy. The data is stored only in your own WordPress database - nothing is sent to third parties.

= What happens to order meta if I uninstall the plugin? =

Running the standard WordPress uninstall (delete from the Plugins page) will remove all `_cm_ost_first` and `_cm_ost_last` meta from both the legacy postmeta table and the HPOS orders meta table.

== Changelog ==

= 1.0.0 =
* Initial release
* First-click and last-click attribution capture
* UTM parameter extraction and sanitization
* Source type classification (paid_search, paid_social, email, social, organic, referral, direct)
* GA4 client ID capture from _ga cookie
* Orders list Source column (HPOS + legacy)
* Order admin UTM detail panel
* HPOS compatibility declaration
