# AGENTS.md: Sepordeh WooCommerce Gateway

## Project goal
WooCommerce payment gateway plugin that connects stores to the Sepordeh payment service.

## Platform and language
- WordPress plugin, PHP, WooCommerce gateway API
- One JS file for Cart/Checkout Blocks (`assets/js/sepordeh-checkout.js`)

## Architecture
- `index.php`: plugin header, gateway registration, Blocks registration
- `class-woocommerce-gateway-sepordeh.php`: class `WC_SpDeh` (settings, payment request, callback verification)
- `sepordeh-class-block.php`: Blocks integration

## Naming
- Keep the class `WC_SpDeh`, gateway ID `WC_SpDeh`, and filter `woo_sepordeh_logo`.
- Follow WordPress PHP coding standards for new code.

## Do not change without an explicit request
- Public class names, gateway ID, hook names, filters
- Settings keys (`merchantcode`, `automaticGateway`, `success_massage`, `failed_massage`) and how they are stored
- Existing features or backward compatibility
- Do not rewrite whole files. Keep changes small and reviewable.

## Security
- Validate, sanitize, and escape all input and output.
- Use nonces and capability checks for admin actions.
- Never log sensitive payment data. Never store card data.
- Never put real merchant codes or keys in code, tests, README, or commits.

## Compatibility
- Minimum versions are not yet confirmed. Do not raise PHP, WordPress, or WooCommerce requirements without approval.

## Tests
- None yet. Planned: PHPCS, PHPUnit, E2E against a mock Sepordeh API.

## Changes and commits
- Conventional messages: `feat:`, `fix:`, `docs:`, `refactor:`, `chore:`
- Record user-visible changes in `readme.txt` changelog.

## Release
1. Develop and test on GitHub.
2. Unify version strings (`index.php`, `readme.txt`).
3. Tag the release.
4. Copy the approved version to the SVN `trunk`/`tags` (separate process, keep `assets`, `tags`, `trunk` intact).
5. Publish on WordPress.org.
