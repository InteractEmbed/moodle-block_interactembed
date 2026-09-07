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

use block_interactembed\local\package\package_access_key;

#[\PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations]
/**
 * Tests signed access paths for isolated package resources.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \block_interactembed\local\package\package_access_key
 */
final class package_access_key_test extends \basic_testcase {
    /**
     * Tests scope, expiry and tamper protection.
     */
    public function test_key_is_scoped_and_short_lived(): void {
        $time = 1788120000;
        $key = package_access_key::create(42, 1234, $time);

        $this->assertTrue(package_access_key::is_candidate($key));
        $this->assertTrue(package_access_key::validate($key, 42, 1234, $time));
        $this->assertTrue(package_access_key::validate($key, 42, 1234, $time + 3600));
        $this->assertFalse(package_access_key::validate($key, 43, 1234, $time));
        $this->assertFalse(package_access_key::validate($key, 42, 1235, $time));
        $this->assertFalse(package_access_key::validate($key, 42, 1234, $time + 7200));
        $this->assertFalse(package_access_key::validate($key . '0', 42, 1234, $time));
    }
}
