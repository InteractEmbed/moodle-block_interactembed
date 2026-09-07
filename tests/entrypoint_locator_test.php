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

use block_interactembed\local\package\entrypoint_locator;

#[\PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations]
/**
 * Tests package entry-point validation.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \block_interactembed\local\package\entrypoint_locator
 */
final class entrypoint_locator_test extends \basic_testcase {
    /**
     * Verifies valid relative HTML paths.
     */
    public function test_normalise_valid_path(): void {
        $this->assertSame('index.html', entrypoint_locator::normalise_path('index.html'));
        $this->assertSame('course/start.htm', entrypoint_locator::normalise_path('course\\start.htm'));
    }

    /**
     * Verifies unsafe and non-HTML paths.
     */
    public function test_normalise_rejects_invalid_paths(): void {
        $paths = [
            '',
            '../index.html',
            './index.html',
            'course/./index.html',
            '/index.html',
            'C:/index.html',
            'course//index.html',
            'manifest.json',
            "index.html\0.js",
        ];

        foreach ($paths as $path) {
            try {
                entrypoint_locator::normalise_path($path);
                $this->fail('An unsafe entry point was accepted: ' . $path);
            } catch (\moodle_exception $exception) {
                $this->assertSame('packageentryinvalid', $exception->errorcode);
            }
        }
    }
}
