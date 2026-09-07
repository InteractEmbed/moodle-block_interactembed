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

#[\PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations]
/**
 * Tests course backup and restore of block configuration and package files.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \block_interactembed
 * @covers    \block_interactembed\local\package\package_importer
 */
final class backup_restore_test extends \advanced_testcase {
    /**
     * Tests that a rejected replacement leaves the working package untouched.
     */
    public function test_failed_package_replacement_is_rolled_back(): void {
        global $DB, $USER;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $block = $this->create_course_block($course);
        $this->save_package($block);

        $pathname = make_request_directory() . '/unsafe-replacement.zip';
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($pathname, \ZipArchive::CREATE | \ZipArchive::OVERWRITE));
        $this->assertTrue($zip->addFromString('../outside.html', '<h1>Unsafe</h1>'));
        $this->assertTrue($zip->close());

        $itemid = file_get_unused_draft_itemid();
        get_file_storage()->create_file_from_pathname([
            'contextid' => \context_user::instance($USER->id)->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $itemid,
            'filepath' => '/',
            'filename' => 'unsafe-replacement.zip',
        ], $pathname);

        try {
            $block->instance_config_save((object)[
                'title' => 'Broken replacement',
                'description' => '',
                'coverfiles' => 0,
                'coveralt' => '',
                'sourcetype' => 'package',
                'packagefiles' => $itemid,
                'entrypoint' => 'index.html',
                'aspectratio' => '16x9',
                'customheight' => 600,
                'displaymode' => 'inline',
                'loadonrequest' => 0,
                'showfullscreen' => 1,
                'showembedcode' => 0,
                'lazyload' => 1,
                'sandboxprofile' => 'standard',
            ]);
            $this->fail('The unsafe replacement must be rejected.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('packageunsafearchive', $exception->errorcode);
        }

        $record = $DB->get_record('block_instances', ['id' => $block->instance->id], '*', MUST_EXIST);
        $config = unserialize(base64_decode($record->configdata));
        $this->assertSame('Restored package', $config->title);

        $fs = get_file_storage();
        $this->assertTrue($fs->file_exists(
            $block->context->id,
            'block_interactembed',
            'content',
            0,
            '/',
            'index.html'
        ));
        $this->assertTrue($fs->file_exists(
            $block->context->id,
            'block_interactembed',
            'content',
            0,
            '/assets/',
            'message.txt'
        ));
        $this->assertTrue($fs->file_exists(
            $block->context->id,
            'block_interactembed',
            'cover',
            0,
            '/',
            'cover.svg'
        ));
        $this->assertFalse($fs->file_exists(
            $block->context->id,
            'block_interactembed',
            'content',
            0,
            '/',
            'unsafe-replacement.zip'
        ));
    }

    /**
     * Tests that an HTML5 package and its configuration survive course restore.
     */
    public function test_package_backup_and_restore(): void {
        global $CFG, $DB, $USER;

        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        $course = $this->getDataGenerator()->create_course();
        $block = $this->create_course_block($course);
        $this->save_package($block);

        $fs = get_file_storage();
        $this->assertTrue($fs->file_exists(
            $block->context->id,
            'block_interactembed',
            'content',
            0,
            '/',
            'index.html'
        ));
        $this->assertTrue($fs->file_exists(
            $block->context->id,
            'block_interactembed',
            'cover',
            0,
            '/',
            'cover.svg'
        ));

        $CFG->backup_file_logger_level = \backup::LOG_NONE;
        $backup = new \backup_controller(
            \backup::TYPE_1COURSE,
            $course->id,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_IMPORT,
            $USER->id
        );
        $backupid = $backup->get_backupid();
        $backup->execute_plan();
        $backup->destroy();

        $restoredcourse = $this->getDataGenerator()->create_course();
        $restore = new \restore_controller(
            $backupid,
            $restoredcourse->id,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            $USER->id,
            \backup::TARGET_CURRENT_ADDING
        );
        $this->assertTrue($restore->execute_precheck());
        $restore->execute_plan();
        $restore->destroy();

        $coursecontext = \context_course::instance($restoredcourse->id);
        $record = $DB->get_record('block_instances', [
            'blockname' => 'interactembed',
            'parentcontextid' => $coursecontext->id,
        ], '*', MUST_EXIST);
        $config = unserialize(base64_decode($record->configdata));
        $this->assertSame('Restored package', $config->title);
        $this->assertSame('package', $config->sourcetype);
        $this->assertSame('card', $config->displaymode);
        $this->assertSame('Complete the short activity.', $config->description);
        $this->assertSame('Abstract learning shapes', $config->coveralt);

        $restoredcontext = \context_block::instance($record->id);
        $this->assertTrue($fs->file_exists(
            $restoredcontext->id,
            'block_interactembed',
            'content',
            0,
            '/',
            'index.html'
        ));
        $this->assertTrue($fs->file_exists(
            $restoredcontext->id,
            'block_interactembed',
            'content',
            0,
            '/assets/',
            'message.txt'
        ));
        $this->assertTrue($fs->file_exists(
            $restoredcontext->id,
            'block_interactembed',
            'cover',
            0,
            '/',
            'cover.svg'
        ));
    }

    /**
     * Creates and loads an InteractEmbed block in a course.
     *
     * @param \stdClass $course test course
     * @return \block_interactembed
     */
    private function create_course_block(\stdClass $course): \block_interactembed {
        $page = $this->course_page($course);
        $page->blocks->add_block_at_end_of_default_region('interactembed');

        $page = $this->course_page($course);
        $blocks = $page->blocks->get_blocks_for_region($page->blocks->get_default_region());
        return end($blocks);
    }

    /**
     * Creates a course page suitable for block management.
     *
     * @param \stdClass $course test course
     * @return \moodle_page
     */
    private function course_page(\stdClass $course): \moodle_page {
        $page = new \moodle_page();
        $page->set_context(\context_course::instance($course->id));
        $page->set_course($course);
        $page->set_pagelayout('standard');
        $page->set_pagetype('course-view');
        $page->blocks->load_blocks();
        return $page;
    }

    /**
     * Saves representative unpacked package files through the block API.
     *
     * @param \block_interactembed $block block instance
     */
    private function save_package(\block_interactembed $block): void {
        global $USER;

        $itemid = file_get_unused_draft_itemid();
        $usercontext = \context_user::instance($USER->id);
        $fs = get_file_storage();
        $fs->create_file_from_string([
            'component' => 'user',
            'filearea' => 'draft',
            'contextid' => $usercontext->id,
            'itemid' => $itemid,
            'filepath' => '/',
            'filename' => 'index.html',
        ], '<!doctype html><title>Backup fixture</title>');
        $fs->create_file_from_string([
            'component' => 'user',
            'filearea' => 'draft',
            'contextid' => $usercontext->id,
            'itemid' => $itemid,
            'filepath' => '/assets/',
            'filename' => 'message.txt',
        ], 'Restored asset');

        $coveritemid = file_get_unused_draft_itemid();
        $fs->create_file_from_string([
            'component' => 'user',
            'filearea' => 'draft',
            'contextid' => $usercontext->id,
            'itemid' => $coveritemid,
            'filepath' => '/',
            'filename' => 'cover.svg',
        ], '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"></svg>');

        $block->instance_config_save((object)[
            'title' => 'Restored package',
            'description' => 'Complete the short activity.',
            'coverfiles' => $coveritemid,
            'coveralt' => 'Abstract learning shapes',
            'sourcetype' => 'package',
            'packagefiles' => $itemid,
            'entrypoint' => 'index.html',
            'aspectratio' => '16x9',
            'customheight' => 600,
            'displaymode' => 'card',
            'loadonrequest' => 0,
            'showfullscreen' => 1,
            'showembedcode' => 0,
            'lazyload' => 1,
            'sandboxprofile' => 'standard',
        ]);
    }
}
