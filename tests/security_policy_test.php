<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace block_interactembed;

use block_interactembed\local\message_nonce;
use block_interactembed\local\package\package_response_policy;
use block_interactembed\local\security\sandbox_policy;

#[\PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations]
/**
 * Tests sandbox, signed-resource and message-channel security policy.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \block_interactembed\local\message_nonce
 * @covers    \block_interactembed\local\package\package_response_policy
 * @covers    \block_interactembed\local\security\sandbox_policy
 */
final class security_policy_test extends \basic_testcase {
    /**
     * Package profiles never let ordinary popups escape their sandbox.
     */
    public function test_sandbox_profiles_apply_least_privilege(): void {
        $strict = sandbox_policy::tokens('strict');
        $standard = sandbox_policy::tokens('standard');
        $trusted = sandbox_policy::tokens('trusted');

        $this->assertStringContainsString('allow-scripts', $strict);
        $this->assertStringContainsString('allow-popups', $standard);
        $this->assertStringContainsString('allow-downloads', $standard);
        $this->assertStringNotContainsString('allow-same-origin', $strict);
        $this->assertStringNotContainsString('allow-same-origin', $standard);
        $this->assertStringContainsString('allow-same-origin', $trusted);
        $this->assertStringNotContainsString('allow-popups-to-escape-sandbox', $strict);
        $this->assertStringNotContainsString('allow-popups-to-escape-sandbox', $standard);
        $this->assertStringNotContainsString('allow-popups-to-escape-sandbox', $trusted);
        $this->assertSame($standard, sandbox_policy::tokens('forged'));
    }

    /**
     * Signed package resources explicitly support opaque sandbox origins without cookies.
     */
    public function test_signed_resource_headers_are_cors_and_sniffing_safe(): void {
        $headers = package_response_policy::headers('project.json', 'standard', true);

        $this->assertSame('*', $headers['Access-Control-Allow-Origin']);
        $this->assertSame('GET, HEAD, OPTIONS', $headers['Access-Control-Allow-Methods']);
        $this->assertSame('cross-origin', $headers['Cross-Origin-Resource-Policy']);
        $this->assertSame('nosniff', $headers['X-Content-Type-Options']);
        $this->assertSame('no-referrer', $headers['Referrer-Policy']);
        $this->assertArrayNotHasKey('Content-Security-Policy', $headers);
    }

    /**
     * HTML documents receive matching HTTP sandbox and frame-ancestor policy.
     */
    public function test_html_headers_preserve_the_opaque_origin_sandbox(): void {
        $headers = package_response_policy::headers('INDEX.HTML', 'standard', true);
        $csp = $headers['Content-Security-Policy'];

        $this->assertStringContainsString('sandbox allow-scripts', $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringNotContainsString('allow-same-origin', $csp);
        $this->assertStringNotContainsString('allow-popups-to-escape-sandbox', $csp);
    }

    /**
     * Authenticated pluginfile responses do not opt into cross-origin reads.
     */
    public function test_unsigned_pluginfile_headers_do_not_enable_cors(): void {
        $headers = package_response_policy::headers('index.html', 'strict', false);

        $this->assertArrayNotHasKey('Access-Control-Allow-Origin', $headers);
        $this->assertArrayNotHasKey('Cross-Origin-Resource-Policy', $headers);
        $this->assertSame('nosniff', $headers['X-Content-Type-Options']);
        $this->assertSame('no-referrer', $headers['Referrer-Policy']);
    }

    /**
     * Resize channel nonces have one strict, unpredictable public representation.
     */
    public function test_message_nonce_shape_is_strict(): void {
        $first = message_nonce::create();
        $second = message_nonce::create();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $first);
        $this->assertNotSame($first, $second);
        $this->assertTrue(message_nonce::is_valid($first));
        $this->assertFalse(message_nonce::is_valid(strtoupper($first)));
        $this->assertFalse(message_nonce::is_valid($first . '0'));
        $this->assertFalse(message_nonce::is_valid(''));
    }
}
