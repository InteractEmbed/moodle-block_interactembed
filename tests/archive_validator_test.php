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

use block_interactembed\local\package\archive_validator;

#[\PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations]
/**
 * Tests for ZIP archive security validation.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \block_interactembed\local\package\archive_validator
 */
final class archive_validator_test extends \advanced_testcase {
    /**
     * Tests direct validation of paths from unpacked file collections.
     */
    public function test_unpacked_paths_are_validated(): void {
        archive_validator::validate_path('assets/scripts/app.js');
        $this->addToAssertionCount(1);

        foreach (['../app.js', './app.js', '/app.js', 'C:/app.js', 'assets//app.js', "app.js\0.txt"] as $path) {
            try {
                archive_validator::validate_path($path);
                $this->fail('An unsafe unpacked path was accepted: ' . $path);
            } catch (\moodle_exception $exception) {
                $this->assertSame('packageunsafefile', $exception->errorcode);
            }
        }
    }

    /**
     * Tests that a small ordinary HTML5 archive is accepted.
     */
    public function test_safe_archive_is_accepted(): void {
        $this->resetAfterTest();
        $archive = $this->create_archive('safe.zip', ['index.html' => '<h1>Safe</h1>']);
        archive_validator::validate($archive);
        $this->addToAssertionCount(1);
    }

    /**
     * Tests that parent-directory traversal is rejected.
     */
    public function test_parent_traversal_is_rejected(): void {
        $this->resetAfterTest();
        $archive = $this->create_archive('unsafe.zip', ['../outside.html' => '<h1>Unsafe</h1>']);
        $this->expectException(\moodle_exception::class);
        archive_validator::validate($archive);
    }

    /**
     * Tests that a redundant current-directory segment is rejected.
     */
    public function test_ambiguous_dot_segment_is_rejected(): void {
        $this->resetAfterTest();
        $archive = $this->create_archive('ambiguous.zip', ['assets/./index.html' => '<h1>Unsafe</h1>']);
        $this->expectException(\moodle_exception::class);
        archive_validator::validate($archive);
    }

    /**
     * Tests that a symbolic-link entry is rejected.
     */
    public function test_symbolic_link_is_rejected(): void {
        $this->resetAfterTest();
        $pathname = make_request_directory() . '/symlink.zip';
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($pathname, \ZipArchive::CREATE | \ZipArchive::OVERWRITE));
        $this->assertTrue($zip->addFromString('index.html', '<h1>Safe</h1>'));
        $this->assertTrue($zip->addFromString('linked-file', 'index.html'));
        $this->assertTrue($zip->setExternalAttributesName('linked-file', 3, 0120777 << 16));
        $this->assertTrue($zip->close());

        $archive = get_file_storage()->create_file_from_pathname([
            'contextid' => \context_system::instance()->id,
            'component' => 'block_interactembed',
            'filearea' => 'test',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'symlink.zip',
        ], $pathname);

        $this->expectException(\moodle_exception::class);
        archive_validator::validate($archive);
    }

    /**
     * Creates a ZIP archive in Moodle file storage.
     *
     * @param string $filename archive filename
     * @param array $contents archive paths mapped to contents
     * @return \stored_file
     */
    private function create_archive(string $filename, array $contents): \stored_file {
        $pathname = make_request_directory() . '/' . $filename;
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($pathname, \ZipArchive::CREATE | \ZipArchive::OVERWRITE));
        foreach ($contents as $path => $content) {
            $this->assertTrue($zip->addFromString($path, $content));
        }
        $this->assertTrue($zip->close());

        return get_file_storage()->create_file_from_pathname([
            'contextid' => \context_system::instance()->id,
            'component' => 'block_interactembed',
            'filearea' => 'test',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => $filename,
        ], $pathname);
    }
}
