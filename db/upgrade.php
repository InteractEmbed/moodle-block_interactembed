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
 * Upgrade steps for InteractEmbed.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Performs InteractEmbed upgrades.
 *
 * @param int $oldversion previously installed version
 * @return bool
 */
function xmldb_block_interactembed_upgrade(int $oldversion): bool {
    global $DB;

    if ($oldversion < 2026082902) {
        $systemcontext = context_system::instance();
        $userroles = $DB->get_records('role', ['archetype' => 'user']);
        foreach ($userroles as $role) {
            $DB->delete_records('role_capabilities', [
                'contextid' => $systemcontext->id,
                'roleid' => $role->id,
                'capability' => 'block/interactembed:myaddinstance',
                'permission' => CAP_ALLOW,
            ]);
        }

        upgrade_block_savepoint(true, 2026082902, 'interactembed');
    }

    if ($oldversion < 2026083000) {
        upgrade_block_savepoint(true, 2026083000, 'interactembed');
    }

    if ($oldversion < 2026083001) {
        upgrade_block_savepoint(true, 2026083001, 'interactembed');
    }

    if ($oldversion < 2026083002) {
        upgrade_block_savepoint(true, 2026083002, 'interactembed');
    }

    if ($oldversion < 2026083003) {
        upgrade_block_savepoint(true, 2026083003, 'interactembed');
    }

    if ($oldversion < 2026083006) {
        // Preserve the previous permissive behaviour for upgraded sites. New
        // installations use the safer disabled-by-default setting.
        if (get_config('block_interactembed', 'allowgenericurls') === false) {
            set_config('allowgenericurls', 1, 'block_interactembed');
        }
        upgrade_block_savepoint(true, 2026083006, 'interactembed');
    }

    if ($oldversion < 2026083101) {
        upgrade_block_savepoint(true, 2026083101, 'interactembed');
    }

    if ($oldversion < 2026090110) {
        upgrade_block_savepoint(true, 2026090110, 'interactembed');
    }

    if ($oldversion < 2026090111) {
        // Adds the optional InteractEmbed Library import bridge without schema changes.
        upgrade_block_savepoint(true, 2026090111, 'interactembed');
    }

    if ($oldversion < 2026090112) {
        // Keeps Creator publications after their independent local copy is activated.
        upgrade_block_savepoint(true, 2026090112, 'interactembed');
    }

    return true;
}
