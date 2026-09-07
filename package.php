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
 * Serves isolated HTML5 package files through a short-lived signed path.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_DEBUG_DISPLAY', true);
define('NO_MOODLE_COOKIES', true);

require_once(__DIR__ . '/../../config.php');

$relativepath = get_file_argument();
if (!$relativepath || $relativepath[0] !== '/') {
    send_file_not_found();
}

$args = explode('/', ltrim($relativepath, '/'));
if (count($args) < 3) {
    send_file_not_found();
}

$contextidpart = (string)array_shift($args);
$contextid = (int)$contextidpart;
$accesskey = (string)array_shift($args);
$filename = (string)array_pop($args);
if ($contextid <= 0 || $contextidpart !== (string)$contextid || $filename === '') {
    send_file_not_found();
}

$context = context::instance_by_id($contextid, IGNORE_MISSING);
if (!$context instanceof context_block) {
    send_file_not_found();
}

$instance = $DB->get_record('block_instances', [
    'id' => $context->instanceid,
    'blockname' => 'interactembed',
]);
if (!$instance) {
    send_file_not_found();
}

$config = unserialize_object(base64_decode($instance->configdata ?? ''));
$revision = (int)($config->revision ?? 0);
if (
    ($config->sourcetype ?? '') !== 'package' ||
        !\block_interactembed\local\package\package_access_key::validate(
            $accesskey,
            $context->id,
            $revision
        )
) {
    send_file_not_found();
}

$filepath = empty($args) ? '/' : '/' . implode('/', $args) . '/';
$file = get_file_storage()->get_file(
    $context->id,
    'block_interactembed',
    'content',
    0,
    $filepath,
    $filename
);
if (!$file || $file->is_directory()) {
    send_file_not_found();
}

$requestmethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
if (!in_array($requestmethod, ['GET', 'HEAD', 'OPTIONS'], true)) {
    header('Allow: GET, HEAD, OPTIONS');
    http_response_code(405);
    exit;
}

$profile = (string)($config->sandboxprofile ?? 'standard');
\block_interactembed\local\package\package_response_policy::apply($filename, $profile, true);
if ($requestmethod === 'OPTIONS') {
    header('Allow: GET, HEAD, OPTIONS');
    http_response_code(204);
    exit;
}

send_stored_file($file, 0, 0, false);
