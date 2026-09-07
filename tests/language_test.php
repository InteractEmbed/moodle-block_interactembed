<?php
// This file is part of Moodle - http://moodle.org/
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

#[\PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations]
/**
 * Ensures the distributable English language file exposes essential keys.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversNothing
 */
final class language_test extends \basic_testcase {
    /**
     * Tests essential English interface strings.
     */
    public function test_english_language_file_contains_required_keys(): void {
        global $CFG;

        $string = [];
        include($CFG->dirroot . '/blocks/interactembed/lang/en/block_interactembed.php');

        $requiredkeys = [
            'pluginname',
            'interactembed:addinstance',
            'interactembed:myaddinstance',
            'interactembed:trustpackage',
            'sourcetypeguide',
            'sourcetypepackage',
            'sourcetypeurl',
            'contentdescription',
            'coverimage',
            'coverimageguidance',
            'fullscreen',
            'packageentryinvalid',
            'packageunsafefile',
            'packageoriginlibrary',
            'creatorpublication',
            'creatorpublicationinvalid',
        ];
        foreach ($requiredkeys as $key) {
            $this->assertArrayHasKey($key, $string);
            $this->assertIsString($string[$key]);
            $this->assertNotSame('', trim($string[$key]));
        }
    }
}
