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

/**
 * Tests administrative defaults and locked policies in the block form.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_interactembed;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/blocks/edit_form.php');
require_once($CFG->dirroot . '/blocks/interactembed/edit_form.php');

#[\PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations]
/**
 * Tests administrative defaults and locked policies in the block form.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \block_interactembed
 * @covers    \block_interactembed_edit_form
 */
final class admin_defaults_test extends \advanced_testcase {
    /**
     * Verifies that Moodle opens the required configuration form while adding the block.
     */
    public function test_configuration_form_is_displayed_when_adding(): void {
        $this->assertTrue(\block_interactembed_edit_form::display_form_when_adding());
    }

    /**
     * Verifies that site defaults are applied to a newly added block.
     */
    public function test_site_defaults_are_applied_to_new_block(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        set_config('defaultaspectratio', '4x3', 'block_interactembed');
        set_config('defaultcustomheight', '777', 'block_interactembed');
        set_config('defaultdisplaymode', 'card', 'block_interactembed');
        set_config('defaultloadonrequest', '1', 'block_interactembed');
        set_config('defaultshowfullscreen', '0', 'block_interactembed');
        set_config('defaultshowembedcode', '1', 'block_interactembed');
        set_config('defaultlazyload', '0', 'block_interactembed');
        set_config('defaultsandboxprofile', 'strict', 'block_interactembed');
        set_config('sourcepolicy', 'both', 'block_interactembed');
        set_config('allowembedcode', '1', 'block_interactembed');

        $quickform = $this->quick_form($this->make_form());
        $defaults = $quickform->_defaultValues;

        $this->assertSame('url', $defaults['config_sourcetype']);
        $this->assertSame('index.html', $defaults['config_entrypoint']);
        $this->assertSame('4x3', $defaults['config_aspectratio']);
        $this->assertSame(777, $defaults['config_customheight']);
        $this->assertSame('card', $defaults['config_displaymode']);
        $this->assertTrue($defaults['config_loadonrequest']);
        $this->assertFalse($defaults['config_showfullscreen']);
        $this->assertTrue($defaults['config_showembedcode']);
        $this->assertFalse($defaults['config_lazyload']);
        $this->assertSame('strict', $defaults['config_sandboxprofile']);
    }

    /**
     * Verifies that locked policies remove forbidden choices from the form.
     */
    public function test_site_policy_locks_are_reflected_in_form(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        set_config('sourcepolicy', 'packageonly', 'block_interactembed');
        set_config('allowembedcode', '0', 'block_interactembed');
        set_config('forceremoteconsent', '1', 'block_interactembed');

        $form = $this->make_form();
        $quickform = $this->quick_form($form);
        $defaults = $quickform->_defaultValues;

        $this->assertSame('package', $defaults['config_sourcetype']);
        $this->assertFalse($quickform->elementExists('config_showembedcode'));
        $this->assertTrue($quickform->elementExists('remoteconsentnotice'));
    }

    /**
     * Creates the add-block form in a representative course page context.
     *
     * @return \block_interactembed_edit_form
     */
    private function make_form(): \block_interactembed_edit_form {
        global $PAGE;

        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $PAGE->set_url('/course/view.php', ['id' => $course->id]);
        $PAGE->set_course($course);
        $PAGE->set_context($context);
        $PAGE->set_pagetype('course-view');

        $block = block_instance('interactembed');
        $block->page = $PAGE;
        $block->context = $context;
        $block->instance = (object)[
            'parentcontextid' => $context->id,
            'id' => null,
        ];

        return new \block_interactembed_edit_form(null, [
            'block' => $block,
            'page' => $PAGE,
        ]);
    }

    /**
     * Gets the MoodleQuickForm object from a Moodle form.
     *
     * @param \block_interactembed_edit_form $form form under test
     * @return \MoodleQuickForm
     */
    private function quick_form(\block_interactembed_edit_form $form): \MoodleQuickForm {
        $property = new \ReflectionProperty(\moodleform::class, '_form');
        return $property->getValue($form);
    }
}
