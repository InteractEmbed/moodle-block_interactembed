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

namespace block_interactembed\local;

use context;
use context_block;
use context_coursecat;
use context_system;
use context_user;
use core_course_category;

/**
 * Applies the parent page's Moodle access rules to players and files.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class access_manager {
    /**
     * Requires access to the block instance's parent context.
     *
     * @param context_block $blockcontext block context
     * @param \stdClass $instance block instance record
     */
    public static function require_access(context_block $blockcontext, \stdClass $instance): void {
        global $DB;

        $parentcontext = context::instance_by_id((int)$instance->parentcontextid, MUST_EXIST);
        self::require_visible_instance_access($blockcontext, $parentcontext, $instance);

        $coursecontext = $blockcontext->get_course_context(false);
        if ($coursecontext) {
            $course = $DB->get_record('course', ['id' => $coursecontext->instanceid], '*', MUST_EXIST);
            require_course_login($course);
            return;
        }

        if ($parentcontext instanceof context_user) {
            self::require_user_context_access($parentcontext);
            return;
        }

        if ($parentcontext instanceof context_coursecat) {
            self::require_category_context_access($parentcontext);
            return;
        }

        if ($parentcontext instanceof context_system) {
            self::require_system_context_access($instance);
            return;
        }

        self::require_login_when_forced();
    }

    /**
     * Protects hidden block instances from ordinary viewers.
     *
     * @param context_block $blockcontext block context
     * @param context $parentcontext parent context
     * @param \stdClass $instance block instance record
     */
    private static function require_visible_instance_access(
        context_block $blockcontext,
        context $parentcontext,
        \stdClass $instance
    ): void {
        global $USER;

        if (!isset($instance->visible) || !empty($instance->visible)) {
            return;
        }

        $canedit = has_capability('moodle/block:edit', $blockcontext);
        if ($parentcontext instanceof context_user && (int)$USER->id === (int)$parentcontext->instanceid) {
            $canedit = $canedit || has_capability('moodle/my:manageblocks', $parentcontext);
        }
        if (!$canedit) {
            throw new \moodle_exception('contentunavailable', 'block_interactembed');
        }
    }

    /**
     * Applies access rules for dashboard and profile contexts.
     *
     * @param context_user $parentcontext user context
     */
    private static function require_user_context_access(context_user $parentcontext): void {
        global $USER;

        require_login();
        if (
            (int)$USER->id !== (int)$parentcontext->instanceid &&
                !has_capability('moodle/user:viewdetails', $parentcontext)
        ) {
            throw new \required_capability_exception(
                $parentcontext,
                'moodle/user:viewdetails',
                'nopermissions',
                ''
            );
        }
    }

    /**
     * Applies access rules for course-category contexts.
     *
     * @param context_coursecat $parentcontext category context
     */
    private static function require_category_context_access(context_coursecat $parentcontext): void {
        if (!core_course_category::get($parentcontext->instanceid, IGNORE_MISSING)) {
            throw new \moodle_exception('cannotviewcategory');
        }
        self::require_login_when_forced();
    }

    /**
     * Applies access rules for system-context blocks.
     *
     * @param \stdClass $instance block instance record
     */
    private static function require_system_context_access(\stdClass $instance): void {
        global $CFG;

        // Only an instance explicitly attached to the site home page can inherit anonymous access.
        $publicsitehome = ($instance->pagetypepattern ?? '') === 'site-index';
        if (!empty($CFG->forcelogin) || !$publicsitehome) {
            require_login();
        }
    }

    /**
     * Requires a session when the site-wide force-login policy is enabled.
     */
    private static function require_login_when_forced(): void {
        global $CFG;

        if (!empty($CFG->forcelogin)) {
            require_login();
        }
    }
}
