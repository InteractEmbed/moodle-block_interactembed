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
 * Tests file-area routing and clean same-Moodle embed markup.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \block_interactembed\local\content_factory
 */
final class file_serving_test extends \advanced_testcase {
    /**
     * Confirms package and cover requests remain in their respective file areas.
     */
    public function test_file_area_is_preserved(): void {
        global $CFG;

        $this->resetAfterTest();
        require_once($CFG->dirroot . '/blocks/interactembed/lib.php');
        $context = \context_system::instance();
        $fs = get_file_storage();

        foreach (['content' => 'index.html', 'cover' => 'cover.svg'] as $filearea => $filename) {
            $fs->create_file_from_string([
                'component' => 'block_interactembed',
                'filearea' => $filearea,
                'contextid' => $context->id,
                'itemid' => 0,
                'filepath' => '/',
                'filename' => $filename,
            ], $filearea);
        }

        $package = block_interactembed_get_file_for_area($context, 'content', [0, 'index.html']);
        $cover = block_interactembed_get_file_for_area($context, 'cover', [0, 'cover.svg']);

        $this->assertInstanceOf(\stored_file::class, $package);
        $this->assertSame('content', $package->get_content());
        $this->assertInstanceOf(\stored_file::class, $cover);
        $this->assertSame('cover', $cover->get_content());
        $this->assertNull(block_interactembed_get_file_for_area($context, 'cover', [1, 'cover.svg']));
        $this->assertNull(block_interactembed_get_file_for_area($context, 'cover', [0]));
    }

    /**
     * Confirms the optional iframe code contains only public iframe attributes.
     */
    public function test_embed_code_omits_internal_presentation_attributes(): void {
        global $PAGE;

        $this->resetAfterTest();
        $this->setAdminUser();
        $systemcontext = \context_system::instance();
        $record = $this->getDataGenerator()->create_block('interactembed', [
            'parentcontextid' => $systemcontext->id,
            'pagetypepattern' => 'site-index',
        ]);
        $context = \context_block::instance($record->id);
        $config = (object)[
            'title' => 'Reusable guide',
            'description' => 'Internal description',
            'coveralt' => 'Internal cover text',
            'sourcetype' => 'guide',
            'aspectratio' => '16x9',
            'customheight' => 600,
            'displaymode' => 'inline',
            'showfullscreen' => 1,
            'lazyload' => 1,
            'sandboxprofile' => 'standard',
        ];
        get_file_storage()->create_file_from_string([
            'component' => 'block_interactembed',
            'filearea' => 'cover',
            'contextid' => $context->id,
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'cover.svg',
        ], '<svg xmlns="http://www.w3.org/2000/svg"/>');

        $PAGE->set_context($context);
        $data = content_factory::from_instance($record, $context, $config, true)
            ->export_for_template($PAGE->get_renderer('core'));

        $this->assertStringContainsString('<iframe', $data['embedcode']);
        $this->assertStringContainsString('/blocks/interactembed/embed.php', $data['embedcode']);
        $this->assertMatchesRegularExpression('/[?&amp;]nonce=[a-f0-9]{32}/', $data['embedcode']);
        $this->assertMatchesRegularExpression('/data-interactembed-nonce="[a-f0-9]{32}"/', $data['embedcode']);
        $this->assertStringContainsString('embed-resizer.js?v=2026090202', $data['embedcode']);
        $this->assertStringNotContainsString('allow-popups-to-escape-sandbox', $data['sandbox']);
        $this->assertSame('Internal description', $data['description']);
        $this->assertTrue($data['hasdescription']);
        $this->assertStringContainsString('/pluginfile.php/', $data['coverurl']);
        $this->assertTrue($data['hascover']);
        $this->assertSame('Internal cover text', $data['coveralt']);
        $this->assertStringNotContainsString('description=', $data['embedcode']);
        $this->assertStringNotContainsString('hasdescription=', $data['embedcode']);
        $this->assertStringNotContainsString('coverurl=', $data['embedcode']);
        $this->assertStringNotContainsString('hascover=', $data['embedcode']);
        $this->assertStringNotContainsString('coveralt=', $data['embedcode']);
    }
}
