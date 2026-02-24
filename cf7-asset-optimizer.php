<?php

/**
 * CF7 Asset Optimizer
 *
 * @package   RenzoJohnson\CF7AssetOptimizer
 * @author    Renzo Johnson <hello@renzojohnson.com>
 * @copyright 2026 Renzo Johnson
 * @license   MIT
 * @link      https://renzojohnson.com
 *
 * @wordpress-plugin
 * Plugin Name: CF7 Asset Optimizer
 * Plugin URI:  https://github.com/renzojohnson/cf7-asset-optimizer
 * Description: Only loads Contact Form 7 scripts and styles on pages that contain a form. Fixes performance issue #1278.
 * Version:     1.0.0
 * Requires at least: 6.4
 * Requires PHP: 8.4
 * Author:      Renzo Johnson
 * Author URI:  https://renzojohnson.com
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: cf7-asset-optimizer
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check whether the current page contains a Contact Form 7 form.
 *
 * Detects:
 * - [contact-form-7] shortcode
 * - [contact-form] shortcode (legacy alias)
 * - contact-form-7/contact-form-selector Gutenberg block
 * - CF7 forms inside widget areas
 *
 * Filterable via 'cf7ao_has_form' for edge cases (forms in templates, dynamic rendering).
 */
function cf7ao_page_has_form(): bool
{
    global $post;

    $override = apply_filters('cf7ao_has_form', null);
    if (is_bool($override)) {
        return $override;
    }

    if (!is_singular() || $post === null) {
        return true;
    }

    $content = $post->post_content;

    if (has_shortcode($content, 'contact-form-7')) {
        return true;
    }

    if (has_shortcode($content, 'contact-form')) {
        return true;
    }

    if (has_block('contact-form-7/contact-form-selector', $post)) {
        return true;
    }

    $sidebars = wp_get_sidebars_widgets();
    if (is_array($sidebars)) {
        foreach ($sidebars as $sidebar_id => $widgets) {
            if ($sidebar_id === 'wp_inactive_widgets' || !is_array($widgets)) {
                continue;
            }
            foreach ($widgets as $widget_id) {
                if (str_starts_with($widget_id, 'wpcf7-')) {
                    return true;
                }
                if (str_starts_with($widget_id, 'text-')) {
                    $number = (int) substr($widget_id, 5);
                    $text_widgets = get_option('widget_text', []);
                    if (isset($text_widgets[$number]['text'])) {
                        $text = $text_widgets[$number]['text'];
                        if (
                            str_contains($text, '[contact-form-7') ||
                            str_contains($text, '[contact-form')
                        ) {
                            return true;
                        }
                    }
                }
                if (str_starts_with($widget_id, 'block-')) {
                    $number = (int) substr($widget_id, 6);
                    $block_widgets = get_option('widget_block', []);
                    if (isset($block_widgets[$number]['content'])) {
                        $block_content = $block_widgets[$number]['content'];
                        if (
                            str_contains($block_content, 'contact-form-7/contact-form-selector') ||
                            str_contains($block_content, '[contact-form-7') ||
                            str_contains($block_content, '[contact-form')
                        ) {
                            return true;
                        }
                    }
                }
            }
        }
    }

    return false;
}

add_action('wp', function (): void {
    if (cf7ao_page_has_form()) {
        return;
    }

    add_filter('wpcf7_load_js', '__return_false');

    add_filter('wpcf7_load_css', '__return_false');

    add_action('wp_enqueue_scripts', function (): void {
        wp_dequeue_script('wpcf7-recaptcha');
        wp_dequeue_script('google-recaptcha');
        wp_dequeue_script('cloudflare-turnstile');
        wp_dequeue_script('wpcf7-stripe');
        wp_dequeue_style('wpcf7-stripe');
        wp_dequeue_script('swv');
        wp_dequeue_script('contact-form-7');
        wp_dequeue_script('contact-form-7-html5-fallback');
        wp_dequeue_style('contact-form-7');
        wp_dequeue_style('contact-form-7-rtl');
        wp_dequeue_style('jquery-ui-smoothness');
    }, 99);
});
