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

use block_interactembed\privacy\provider;
use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\types\external_location;

#[\PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations]
/**
 * Tests the privacy metadata exposed for external iframe providers.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \block_interactembed\privacy\provider
 */
final class privacy_provider_test extends \basic_testcase {
    /**
     * Confirms that browser data sent to external providers is declared.
     */
    public function test_external_provider_metadata_is_declared(): void {
        $metadata = provider::get_metadata(new collection('block_interactembed'))->get_collection();

        $this->assertCount(1, $metadata);
        $this->assertInstanceOf(external_location::class, $metadata[0]);
        $this->assertSame('externalcontent', $metadata[0]->get_name());
        $this->assertSame('privacy:metadata:externalcontent', $metadata[0]->get_summary());
        $this->assertArrayHasKey('ipaddress', $metadata[0]->get_privacy_fields());
        $this->assertArrayHasKey('useragent', $metadata[0]->get_privacy_fields());
    }
}
