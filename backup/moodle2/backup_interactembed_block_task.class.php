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
 * Backup task for InteractEmbed.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_interactembed_block_task extends backup_block_task {
    /**
     * Defines plugin-specific backup settings.
     */
    protected function define_my_settings(): void {
    }

    /**
     * Defines plugin-specific backup steps.
     */
    protected function define_my_steps(): void {
    }

    /**
     * Returns the file areas to back up.
     *
     * @return array file areas to back up
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
     * Encodes links found in configuration content.
     *
     * @param string $content content
     * @return string
     */
    public static function encode_content_links($content): string {
        return $content;
    }
}
