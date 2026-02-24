# CF7 Asset Optimizer

[![Latest Version](https://img.shields.io/packagist/v/renzojohnson/cf7-asset-optimizer.svg)](https://packagist.org/packages/renzojohnson/cf7-asset-optimizer)
[![PHP Version](https://img.shields.io/packagist/php-v/renzojohnson/cf7-asset-optimizer.svg)](https://packagist.org/packages/renzojohnson/cf7-asset-optimizer)
[![License](https://img.shields.io/packagist/l/renzojohnson/cf7-asset-optimizer.svg)](https://github.com/renzojohnson/cf7-asset-optimizer/blob/main/LICENSE)

Only loads Contact Form 7 scripts and styles on pages that actually contain a form. Zero configuration.

Fixes [Contact Form 7 #1278](https://github.com/rocklobster-in/contact-form-7/issues/1278) — CF7 enqueues its JS, CSS, reCAPTCHA, Turnstile, and Stripe scripts on every single page, even pages with no form. This plugin detects whether the current page has a CF7 form and dequeues all CF7 assets when no form is present.

**Author:** [Renzo Johnson](https://renzojohnson.com)

## Requirements

- PHP 8.4+
- WordPress 6.4+
- Contact Form 7 5.0+

## Installation

### Via Composer

```
composer require renzojohnson/cf7-asset-optimizer
```

### Manual

Download and upload to `/wp-content/plugins/cf7-asset-optimizer/`, then activate.

### As mu-plugin

Copy `cf7-asset-optimizer.php` to `/wp-content/mu-plugins/`.

## How It Works

The plugin hooks into CF7's own `wpcf7_load_js` and `wpcf7_load_css` filters to conditionally disable asset loading. On pages without a form, it also dequeues module scripts (reCAPTCHA, Turnstile, Stripe, SWV).

### Detection

The plugin checks for CF7 forms in:

- `[contact-form-7]` shortcode in post content
- `[contact-form]` shortcode (legacy alias)
- `contact-form-7/contact-form-selector` Gutenberg block
- CF7 widgets in sidebar areas
- CF7 shortcodes inside text widgets
- CF7 blocks inside block widgets

### Assets Dequeued

When no form is detected, these handles are dequeued:

| Handle | Source |
|--------|--------|
| `contact-form-7` | Main CF7 JS |
| `contact-form-7-html5-fallback` | Datepicker fallback |
| `swv` | Schema-based Validation |
| `wpcf7-recaptcha` | reCAPTCHA v3 handler |
| `google-recaptcha` | Google reCAPTCHA API |
| `cloudflare-turnstile` | Cloudflare Turnstile API |
| `wpcf7-stripe` | Stripe JS |
| `contact-form-7` (style) | Main CF7 CSS |
| `contact-form-7-rtl` (style) | RTL CSS |
| `jquery-ui-smoothness` (style) | jQuery UI theme |
| `wpcf7-stripe` (style) | Stripe CSS |

### Edge Cases

For forms rendered via PHP templates or dynamic shortcodes not in post content:

```php
// Force-load CF7 assets on specific pages
add_filter('cf7ao_has_form', function (?bool $has_form): ?bool {
    if (is_page('custom-form-page')) {
        return true;
    }
    return $has_form;
});
```

## Testing

```bash
composer install
vendor/bin/phpunit
```

## Links

- [Packagist](https://packagist.org/packages/renzojohnson/cf7-asset-optimizer)
- [GitHub](https://github.com/renzojohnson/cf7-asset-optimizer)
- [Issues](https://github.com/renzojohnson/cf7-asset-optimizer/issues)
- [CF7 Issue #1278](https://github.com/rocklobster-in/contact-form-7/issues/1278)
- [Author](https://renzojohnson.com)

## License

MIT License. Copyright (c) 2026 [Renzo Johnson](https://renzojohnson.com).
