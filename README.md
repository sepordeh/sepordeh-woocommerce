# Sepordeh Payment Gateway for WooCommerce

Adds the [Sepordeh](https://sepordeh.com) online payment gateway to WooCommerce stores.

- **Target platform:** WordPress + WooCommerce
- **Language:** PHP (plus one JavaScript file for the Checkout Block)
- **WordPress.org page:** https://wordpress.org/plugins/sepordeh-woocommerce
- **Development repository:** this GitHub repository (WordPress.org releases are published through SVN, see [Development](#development))

## Features

Derived from the source code:

- Registers a WooCommerce payment gateway (class `WC_SpDeh`, gateway ID `WC_SpDeh`).
- Creates an invoice through the Sepordeh API, redirects the customer to the payment page, and verifies the invoice on return.
- Optional "direct gateway" mode that sends the customer straight to the payment page.
- Configurable success and failure messages. Supported placeholders: `{transaction_id}` and `{fault}`.
- Declares compatibility with WooCommerce Cart/Checkout Blocks and includes a Blocks integration (`sepordeh-class-block.php`, `assets/js/sepordeh-checkout.js`).
- Logo can be changed with the `woo_sepordeh_logo` filter.
- Translation template: `languages/sepordeh-woocommerce.pot`.

## Requirements

| Item | Value |
|---|---|
| WordPress | `Requires at least: 4.5` (from `readme.txt`) |
| Tested up to | WordPress 6.8.2 (from `readme.txt`) |
| WooCommerce | Needs review (not declared in the plugin header) |
| PHP | Needs review (not declared in the files inspected) |

## Compatible versions

Plugin version in `index.php`: `2.0.0`. `readme.txt` declares `2.0`.
Needs review: unify the version string across files.

## File structure

```
index.php                                   Plugin header, bootstrap, Blocks registration
class-woocommerce-gateway-sepordeh.php      Gateway class WC_SpDeh
sepordeh-class-block.php                    Checkout Block integration
assets/js/sepordeh-checkout.js              Checkout Block script
assets/images/logo.svg, logo.png            Gateway logo
languages/sepordeh-woocommerce.pot          Translation template
readme.txt                                  WordPress.org readme
```

## Installation

1. Download the plugin and install it from the WordPress dashboard (Plugins > Add New > Upload), or copy the folder to `wp-content/plugins/`.
2. Activate **Sepordeh Payment Gateway for WooCommerce**.
3. Go to WooCommerce > Settings > Payments and enable the Sepordeh gateway.

## Configuration

Gateway settings (from `init_form_fields`):

| Setting | Purpose |
|---|---|
| Enable/Disable | Turns the gateway on or off |
| Title / Description | Text shown at checkout |
| Merchant code (`merchantcode`) | Merchant code from your Sepordeh account |
| Direct gateway (`automaticGateway`) | Redirect straight to the payment page |
| Success message (`success_massage`) | Message after successful payment |
| Failure message (`failed_massage`) | Message after failed payment |

## Payment flow

The plugin calls these Sepordeh API endpoints (taken from the source):

- `https://api.sepordeh.com/merchant/invoices/add`
- `https://api.sepordeh.com/merchant/invoices/pay/...`
- `https://api.sepordeh.com/merchant/invoices/verify`

## Errors and logs

Needs review: the logging behavior has not been analyzed yet.

## Testing

No automated tests exist yet. Planned: PHPCS, PHPUnit, and E2E tests against a mock Sepordeh API.

## Development

- Development happens on GitHub. Official WordPress.org releases are published separately through SVN.
- See `AGENTS.md` for rules that apply to AI-assisted development.

## Project status

Active. Known items needing review:

- The Blocks integration reads settings from the option `woocommerce_sepordeh_gateway_settings`, while the gateway ID is `WC_SpDeh`. Verify that the option name is correct.
- Version strings differ between `index.php` (2.0.0) and `readme.txt` (2.0).
- Git tags `1.2.0`, `1.3.0`, `1.4.0`, `v5.0.0` and `alpha` exist from earlier history. Confirm the versioning scheme before the next release.

## License

GPLv3 or later (from the plugin header). https://www.gnu.org/licenses/gpl-3.0.en.html

## Documentation

- End-user documentation: needs review (official Sepordeh docs URL to be added).
