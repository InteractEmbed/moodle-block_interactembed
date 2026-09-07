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

use block_interactembed\local\content_factory;

#[\PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations]
/**
 * Tests the optional bundled interactive guide source.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \block_interactembed\local\content_factory
 */
final class guide_source_test extends \advanced_testcase {
    /**
     * Tests that the bundled course covers the complete workflow in all three languages.
     */
    public function test_guide_course_is_complete_and_trilingual(): void {
        $coursepath = dirname(__DIR__) . '/guide/course-data.js';
        $apppath = dirname(__DIR__) . '/guide/app.js';
        $stylepath = dirname(__DIR__) . '/guide/styles.css';
        $coursedata = file_get_contents($coursepath);
        $appdata = file_get_contents($apppath);
        $styledata = file_get_contents($stylepath);

        $this->assertNotFalse($coursedata);
        $this->assertNotFalse($appdata);
        $this->assertNotFalse($styledata);
        $this->assertStringContainsString('Dépannage express', $coursedata);
        $this->assertStringContainsString('Quick troubleshooting', $coursedata);
        $this->assertStringContainsString('Solución rápida de problemas', $coursedata);
        $this->assertStringContainsString('Administration du site', $coursedata);
        $this->assertStringContainsString('Site administration', $coursedata);
        $this->assertStringContainsString('Administración del sitio', $coursedata);
        $this->assertStringContainsString('jamais les deux à la fois', $coursedata);
        $this->assertStringContainsString('never both at once', $coursedata);
        $this->assertStringContainsString('nunca ambos a la vez', $coursedata);
        $this->assertStringNotContainsString('const scoredSlides', $appdata);
        $this->assertStringContainsString("slide.type === 'challenge'", $appdata);
        $this->assertStringContainsString("matchMedia('(max-width: 640px)')", $appdata);
        $this->assertStringContainsString('@media (max-width: 640px)', $styledata);
        $this->assertStringContainsString('(min-width: 641px)', $styledata);
        $this->assertStringContainsString('--course-shell-padding', $styledata);
        $this->assertStringContainsString(
            'calc(100vh - var(--course-shell-padding) - var(--course-shell-padding))',
            $styledata
        );
        $this->assertSame(
            2,
            substr_count(
                $styledata,
                'calc(100vh - var(--course-shell-padding) - var(--course-shell-padding))'
            )
        );
    }

    /**
     * Tests that the guide uses a local, language-aware URL.
     */
    public function test_guide_source_is_local_and_language_aware(): void {
        global $DB, $PAGE;

        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $page = new \moodle_page();
        $page->set_context(\context_course::instance($course->id));
        $page->set_course($course);
        $page->set_pagelayout('standard');
        $page->set_pagetype('course-view');
        $page->blocks->load_blocks();
        $page->blocks->add_block_at_end_of_default_region('interactembed');

        $record = $DB->get_record('block_instances', [
            'blockname' => 'interactembed',
            'parentcontextid' => \context_course::instance($course->id)->id,
        ], '*', MUST_EXIST);
        $context = \context_block::instance($record->id);
        $PAGE->set_context($context);
        $view = content_factory::from_instance($record, $context, (object)[
            'title' => 'InteractEmbed guide',
            'sourcetype' => 'guide',
            'aspectratio' => '16x9',
            'displaymode' => 'inline',
            'sandboxprofile' => 'standard',
            'lazyload' => 1,
            'showfullscreen' => 1,
        ]);
        $data = $view->export_for_template($PAGE->get_renderer('core'));

        $this->assertSame('interactembedguide', $data['provider']);
        $this->assertStringContainsString('/blocks/interactembed/guide/index.html', $data['iframeurl']);
        $this->assertStringContainsString('lang=', $data['iframeurl']);
        $this->assertSame($data['iframeurl'], $data['externalurl']);
        $this->assertFalse($data['waitforinteraction']);
    }
}
