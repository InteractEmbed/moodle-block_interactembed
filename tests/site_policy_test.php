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

use block_interactembed\local\site_policy;

#[\PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations]
/**
 * Tests administrator-defined site policies.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \block_interactembed\local\site_policy
 */
final class site_policy_test extends \advanced_testcase {
    /**
     * Tests source policy choices and forged values.
     */
    public function test_source_policy_is_enforced(): void {
        $this->resetAfterTest();

        set_config('sourcepolicy', 'urlonly', 'block_interactembed');
        $this->assertTrue(site_policy::allows_source('url'));
        $this->assertFalse(site_policy::allows_source('package'));
        $this->assertTrue(site_policy::allows_source('guide'));

        set_config('sourcepolicy', 'packageonly', 'block_interactembed');
        $this->assertFalse(site_policy::allows_source('url'));
        $this->assertTrue(site_policy::allows_source('package'));
        $this->assertTrue(site_policy::allows_source('guide'));

        set_config('sourcepolicy', 'forged', 'block_interactembed');
        $this->assertTrue(site_policy::allows_source('url'));
        $this->assertTrue(site_policy::allows_source('package'));
    }

    /**
     * Tests safe fallbacks before new settings exist.
     */
    public function test_policy_defaults_preserve_existing_features(): void {
        $this->resetAfterTest();
        unset_config('allowembedcode', 'block_interactembed');
        unset_config('forceremoteconsent', 'block_interactembed');

        $this->assertTrue(site_policy::allows_embed_code());
        $this->assertFalse(site_policy::forces_remote_consent());
    }

    /**
     * Tests explicit feature policies.
     */
    public function test_boolean_policies_are_enforced(): void {
        $this->resetAfterTest();
        set_config('allowembedcode', 0, 'block_interactembed');
        set_config('forceremoteconsent', 1, 'block_interactembed');

        $this->assertFalse(site_policy::allows_embed_code());
        $this->assertTrue(site_policy::forces_remote_consent());
    }
}
