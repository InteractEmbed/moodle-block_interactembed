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
 * File serving callbacks for InteractEmbed.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Serves files belonging to an HTML5 package or its cover image.
 *
 * @param stdClass $course current course
 * @param stdClass $birecordorcm block instance record
 * @param context $context file context
 * @param string $filearea file area
 * @param array $args item id and path components
 * @param bool $forcedownload requested download mode
 * @param array $options serving options
 */
function block_interactembed_pluginfile(
    $course,
    $birecordorcm,
    $context,
    $filearea,
    $args,
    $forcedownload,
    array $options = []
): void {
    if (
        !$context instanceof context_block || !in_array($filearea, ['content', 'cover'], true) ||
            ($birecordorcm->blockname ?? '') !== 'interactembed'
    ) {
        send_file_not_found();
    }

    \block_interactembed\local\access_manager::require_access($context, $birecordorcm);

    $file = block_interactembed_get_file_for_area($context, $filearea, $args);
    if (!$file || $file->is_directory()) {
        send_file_not_found();
    }

    $config = unserialize_object(base64_decode($birecordorcm->configdata ?? ''));
    $profile = (string)($config->sandboxprofile ?? 'standard');
    \block_interactembed\local\package\package_response_policy::apply(
        $file->get_filename(),
        $profile,
        false
    );
    $extension = strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION));
    if ($filearea === 'content' && in_array($extension, ['html', 'htm'], true)) {
        $forcedownload = false;
    }

    \core\session\manager::write_close();
    // Package files can be replaced without their relative URLs changing. Revalidate them so
    // browsers do not retain stale scripts, styles, or an obsolete HTML sandbox policy.
    send_stored_file($file, 0, 0, $forcedownload, $options);
}

/**
 * Resolves one stored package or cover file from standard pluginfile arguments.
 *
 * Keeping this lookup separate makes the file-area boundary testable and prevents
 * cover requests from accidentally being resolved against the package area.
 *
 * @param context $context block context
 * @param string $filearea validated file area
 * @param array $args item id and path components
 * @return stored_file|null matching file, or null for invalid arguments
 */
function block_interactembed_get_file_for_area(context $context, string $filearea, array $args): ?stored_file {
    $itemid = (int)array_shift($args);
    if ($itemid !== 0 || empty($args)) {
        return null;
    }

    $filename = array_pop($args);
    $filepath = empty($args) ? '/' : '/' . implode('/', $args) . '/';
    return get_file_storage()->get_file(
        $context->id,
        'block_interactembed',
        $filearea,
        0,
        $filepath,
        $filename
    );
}
