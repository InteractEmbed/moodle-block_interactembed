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

/**
 * Restore task for InteractEmbed.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_interactembed_block_task extends restore_block_task {
    /**
     * Defines plugin-specific restore settings.
     */
    protected function define_my_settings(): void {
    }

    /**
     * Defines plugin-specific restore steps.
     */
    protected function define_my_steps(): void {
    }

    /**
     * Returns the file areas to restore.
     *
     * @return array restored file areas
     */
    public function get_fileareas(): array {
        return ['content', 'cover'];
    }

    /**
     * Returns configuration attributes that contain encoded links.
     *
     * @return array encoded config attributes
     */
    public function get_configdata_encoded_attributes(): array {
        return [];
    }

    /**
     * Returns restore content decoders.
     *
     * @return array decode contents
     */
    public static function define_decode_contents(): array {
        return [];
    }

    /**
     * Returns restore link decoding rules.
     *
     * @return array decode rules
     */
    public static function define_decode_rules(): array {
        return [];
    }
}
