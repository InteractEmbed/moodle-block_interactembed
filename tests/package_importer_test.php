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

use block_interactembed\local\package\package_importer;

#[\PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations]
/**
 * Tests draft-package validation.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \block_interactembed\local\package\package_importer
 */
final class package_importer_test extends \advanced_testcase {
    /**
     * A configured package entry point must be an HTML document.
     */
    public function test_non_html_entrypoint_is_rejected(): void {
        global $USER;

        $this->resetAfterTest();
        $this->setAdminUser();

        $itemid = file_get_unused_draft_itemid();
        get_file_storage()->create_file_from_string([
            'contextid' => \context_user::instance($USER->id)->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $itemid,
            'filepath' => '/',
            'filename' => 'start.js',
        ], 'document.body.textContent = "Not an HTML entry point";');

        try {
            package_importer::validate_draft($itemid, 'start.js');
            $this->fail('A JavaScript entry point must not be accepted.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('packageentryinvalid', $exception->errorcode);
        }
    }

    /**
     * A Creator snapshot becomes an independent block-owned package.
     */
    public function test_stored_snapshot_is_copied_and_staging_is_cleaned(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $context = $this->create_block_context();
        $sourcefiles = $this->create_source_files();
        package_importer::import_stored_files(
            $context,
            $sourcefiles,
            'index.html',
            $this->canonical_hash($sourcefiles)
        );

        $fs = get_file_storage();
        $index = $fs->get_file(
            $context->id,
            'block_interactembed',
            'content',
            0,
            '/',
            'index.html'
        );
        $asset = $fs->get_file(
            $context->id,
            'block_interactembed',
            'content',
            0,
            '/assets/',
            'message.txt'
        );

        $this->assertInstanceOf(\stored_file::class, $index);
        $this->assertSame('<!doctype html><title>Creator snapshot</title>', $index->get_content());
        $this->assertInstanceOf(\stored_file::class, $asset);
        $this->assertSame('Independent asset', $asset->get_content());
        $this->assertNotSame($sourcefiles[0]->get_id(), $index->get_id());
        $this->assertEmpty($fs->get_area_files(
            $context->id,
            'block_interactembed',
            'packageimport',
            false,
            'id ASC',
            false
        ));
        $this->assertInstanceOf(\stored_file::class, $fs->get_file_by_id($sourcefiles[0]->get_id()));
    }

    /**
     * A forged Creator hash is rejected before the working package changes.
     */
    public function test_invalid_snapshot_hash_preserves_working_package(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $context = $this->create_block_context();
        $fs = get_file_storage();
        $fs->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'block_interactembed',
            'filearea' => 'content',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'working.html',
        ], '<!doctype html><title>Working revision</title>');

        try {
            package_importer::import_stored_files(
                $context,
                $this->create_source_files(),
                'index.html',
                str_repeat('0', 64)
            );
            $this->fail('A snapshot with a forged hash must not be imported.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('packageinvalidarchive', $exception->errorcode);
        }

        $this->assertTrue($fs->file_exists(
            $context->id,
            'block_interactembed',
            'content',
            0,
            '/',
            'working.html'
        ));
        $this->assertFalse($fs->file_exists(
            $context->id,
            'block_interactembed',
            'content',
            0,
            '/',
            'index.html'
        ));
        $this->assertEmpty($fs->get_area_files(
            $context->id,
            'block_interactembed',
            'packageimport',
            false,
            'id ASC',
            false
        ));
    }

    /**
     * Creates a course block context for package activation tests.
     *
     * @return \context_block
     */
    private function create_block_context(): \context_block {
        $course = $this->getDataGenerator()->create_course();
        $coursecontext = \context_course::instance($course->id);
        $record = $this->getDataGenerator()->create_block('interactembed', [
            'parentcontextid' => $coursecontext->id,
            'pagetypepattern' => 'course-view-*',
        ]);
        return \context_block::instance($record->id);
    }

    /**
     * Creates a representative immutable publication snapshot.
     *
     * @return \stored_file[]
     */
    private function create_source_files(): array {
        global $USER;

        $itemid = file_get_unused_draft_itemid();
        $context = \context_user::instance($USER->id);
        $fs = get_file_storage();
        $fs->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $itemid,
            'filepath' => '/',
            'filename' => 'index.html',
        ], '<!doctype html><title>Creator snapshot</title>');
        $fs->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $itemid,
            'filepath' => '/assets/',
            'filename' => 'message.txt',
        ], 'Independent asset');
        return array_values($fs->get_area_files(
            $context->id,
            'user',
            'draft',
            $itemid,
            'filepath, filename',
            false
        ));
    }

    /**
     * Implements the public Creator API v1 package-hash contract.
     *
     * @param \stored_file[] $files publication files
     * @return string
     */
    private function canonical_hash(array $files): string {
        $records = [];
        foreach ($files as $file) {
            $path = ltrim($file->get_filepath(), '/') . $file->get_filename();
            $records[$path] = $file;
        }
        ksort($records, SORT_STRING);
        $hash = hash_init('sha256');
        foreach ($records as $path => $file) {
            hash_update($hash, $path . "\0" . $file->get_contenthash() . "\0" . $file->get_filesize() . "\0");
        }
        return hash_final($hash);
    }
}
