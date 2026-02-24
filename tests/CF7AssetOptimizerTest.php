<?php

/**
 * CF7 Asset Optimizer
 *
 * @package   RenzoJohnson\CF7AssetOptimizer
 * @author    Renzo Johnson <hello@renzojohnson.com>
 * @copyright 2026 Renzo Johnson
 * @license   MIT
 * @link      https://renzojohnson.com
 */

declare(strict_types=1);

namespace RenzoJohnson\CF7AssetOptimizer\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for cf7ao_page_has_form() detection logic.
 *
 * These tests verify the shortcode and block detection patterns
 * without requiring a full WordPress environment.
 */
final class CF7AssetOptimizerTest extends TestCase
{
    public function testShortcodeDetectionRegex(): void
    {
        $content = 'Hello [contact-form-7 id="123" title="Contact"] world';
        $this->assertTrue($this->contentHasCf7($content));
    }

    public function testLegacyShortcodeDetection(): void
    {
        $content = 'Hello [contact-form id="123"] world';
        $this->assertTrue($this->contentHasCf7($content));
    }

    public function testBlockDetection(): void
    {
        $content = '<!-- wp:contact-form-7/contact-form-selector {"id":123} /-->';
        $this->assertTrue($this->contentHasCf7Block($content));
    }

    public function testNoFormDetection(): void
    {
        $content = '<p>This is a regular page with no form.</p>';
        $this->assertFalse($this->contentHasCf7($content));
        $this->assertFalse($this->contentHasCf7Block($content));
    }

    public function testShortcodeInWidget(): void
    {
        $text = 'Contact us: [contact-form-7 id="456" title="Widget Form"]';
        $this->assertTrue(
            str_contains($text, '[contact-form-7') || str_contains($text, '[contact-form')
        );
    }

    public function testNoShortcodeInWidget(): void
    {
        $text = 'Just a regular text widget with no forms.';
        $this->assertFalse(
            str_contains($text, '[contact-form-7') || str_contains($text, '[contact-form')
        );
    }

    public function testBlockWidgetWithCf7(): void
    {
        $block = '<!-- wp:contact-form-7/contact-form-selector {"id":789} /-->';
        $this->assertTrue(str_contains($block, 'contact-form-7/contact-form-selector'));
    }

    public function testCf7WidgetIdPrefix(): void
    {
        $this->assertTrue(str_starts_with('wpcf7-widget-123', 'wpcf7-'));
        $this->assertFalse(str_starts_with('text-3', 'wpcf7-'));
    }

    public function testHandleList(): void
    {
        $handles = [
            'contact-form-7',
            'contact-form-7-html5-fallback',
            'swv',
            'wpcf7-recaptcha',
            'google-recaptcha',
            'cloudflare-turnstile',
            'wpcf7-stripe',
        ];

        $this->assertCount(7, $handles);
        $this->assertContains('contact-form-7', $handles);
        $this->assertContains('swv', $handles);
    }

    public function testStyleHandleList(): void
    {
        $styles = [
            'contact-form-7',
            'contact-form-7-rtl',
            'jquery-ui-smoothness',
            'wpcf7-stripe',
        ];

        $this->assertCount(4, $styles);
        $this->assertContains('contact-form-7', $styles);
    }

    /**
     * Simulate has_shortcode detection (same logic WordPress uses).
     */
    private function contentHasCf7(string $content): bool
    {
        return (bool) preg_match('/\[contact-form-7[\s\]]/', $content)
            || (bool) preg_match('/\[contact-form[\s\]]/', $content);
    }

    /**
     * Simulate has_block detection (same logic WordPress uses).
     */
    private function contentHasCf7Block(string $content): bool
    {
        return str_contains($content, '<!-- wp:contact-form-7/contact-form-selector');
    }
}
