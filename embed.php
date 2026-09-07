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
 * Same-site embedded player page for InteractEmbed.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Access is checked below against the block's parent context, including the public site home page.
// phpcs:ignore moodle.Files.RequireLogin.Missing
require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$nonce = optional_param('nonce', '', PARAM_ALPHANUM);
if (!\block_interactembed\local\message_nonce::is_valid($nonce)) {
    $nonce = '';
}
$instance = $DB->get_record('block_instances', ['id' => $id, 'blockname' => 'interactembed'], '*', MUST_EXIST);
$context = context_block::instance($instance->id, MUST_EXIST);
\block_interactembed\local\access_manager::require_access($context, $instance);

$config = unserialize_object(base64_decode($instance->configdata ?? ''));
if (empty($config->sourcetype)) {
    throw new moodle_exception('notconfigured', 'block_interactembed');
}

$url = new moodle_url('/blocks/interactembed/embed.php', ['id' => $id]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('embedded');
$PAGE->set_pagetype('block-interactembed-embedded');
$PAGE->set_title(format_string($config->title ?? get_string('pluginname', 'block_interactembed')));
$PAGE->set_heading($PAGE->title);
$PAGE->set_cacheable(false);
header("Content-Security-Policy: frame-ancestors 'self'", false);
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');

$view = \block_interactembed\local\content_factory::from_instance($instance, $context, $config, false);
$PAGE->requires->js_call_amd('block_interactembed/player', 'init', [$view->get_dom_id()]);
$PAGE->requires->js_call_amd('block_interactembed/embed_child', 'init', [$id, $nonce]);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_interactembed/content', $view->export_for_template($OUTPUT));
echo $OUTPUT->footer();
