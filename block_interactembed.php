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
 * InteractEmbed block.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Displays secure responsive external or packaged HTML5 content.
 */
class block_interactembed extends block_base {
    /**
     * Initialises the block.
     */
    public function init(): void {
        $this->title = get_string('pluginname', 'block_interactembed');
    }

    /**
     * Allows multiple instances on a page.
     *
     * @return bool
     */
    public function instance_allow_multiple(): bool {
        return true;
    }

    /**
     * Exposes the plugin's global settings in Site administration.
     *
     * @return bool
     */
    public function has_config(): bool {
        return true;
    }

    /**
     * Makes the block available in all standard page formats.
     *
     * @return array
     */
    public function applicable_formats(): array {
        return ['all' => true];
    }

    /**
     * Applies the configured title.
     */
    public function specialization(): void {
        if (!empty($this->config->title)) {
            $this->title = format_string($this->config->title, true, ['context' => $this->context]);
        }
    }

    /**
     * Builds the block content.
     *
     * @return stdClass
     */
    public function get_content(): stdClass {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        if (empty($this->instance) || empty($this->config->sourcetype)) {
            if ($this->user_can_edit()) {
                $this->content->text = html_writer::div(
                    get_string('notconfigured', 'block_interactembed'),
                    'alert alert-info mb-0'
                );
            }
            return $this->content;
        }

        try {
            $view = \block_interactembed\local\content_factory::from_instance(
                $this->instance,
                $this->context,
                $this->config,
                $this->user_can_edit() &&
                    !empty($this->config->showembedcode) &&
                    \block_interactembed\local\site_policy::allows_embed_code()
            );
            $this->content->text = $this->page->get_renderer('core')->render_from_template(
                'block_interactembed/content',
                $view->export_for_template($this->page->get_renderer('core'))
            );
            $this->page->requires->js_call_amd('block_interactembed/player', 'init', [$view->get_dom_id()]);
        } catch (\Throwable $exception) {
            debugging($exception->getMessage(), DEBUG_DEVELOPER);
            $this->content->text = html_writer::div(
                get_string('contentunavailable', 'block_interactembed'),
                'alert alert-warning mb-0'
            );
        }

        return $this->content;
    }

    /**
     * Saves instance configuration and moves package files out of draft storage.
     *
     * @param stdClass $data submitted form data
     * @param bool $nolongerused legacy argument
     */
    public function instance_config_save($data, $nolongerused = false) {
        global $DB;

        $transaction = $DB->start_delegated_transaction();
        try {
            $this->save_instance_config_and_files($data, $nolongerused);
            $transaction->allow_commit();
        } catch (\Throwable $exception) {
            // File records and block configuration must remain on the previous
            // working revision if package inspection or extraction fails.
            $transaction->rollback($exception);
        }
    }

    /**
     * Saves configuration and files inside the caller's delegated transaction.
     *
     * @param stdClass $data submitted form data
     * @param bool $nolongerused legacy argument
     */
    private function save_instance_config_and_files($data, $nolongerused): void {
        $config = clone $data;
        $draftitemid = (int)($data->packagefiles ?? 0);
        $coverdraftitemid = (int)($data->coverfiles ?? 0);
        $packageorigin = (string)($data->packageorigin ?? 'upload');
        $creatorpublicationid = (int)($data->creatorpublicationid ?? 0);

        // The trusted profile grants same-origin access to active package code. Hiding the
        // option in the form is not sufficient because submitted values can be forged.
        $config->sandboxprofile = \block_interactembed\local\configuration_guard::sandbox_profile(
            (string)($config->sandboxprofile ?? 'standard'),
            has_capability('block/interactembed:trustpackage', $this->context)
        );
        $config->displaymode = \block_interactembed\local\configuration_guard::display_mode(
            (string)($config->displaymode ?? 'inline')
        );
        $sourcetype = (string)($config->sourcetype ?? 'url');
        if (!\block_interactembed\local\site_policy::allows_source($sourcetype)) {
            throw new moodle_exception('sourcenotallowed', 'block_interactembed');
        }
        if (!\block_interactembed\local\site_policy::allows_embed_code()) {
            $config->showembedcode = 0;
        }
        if ($sourcetype === 'url' && \block_interactembed\local\site_policy::forces_remote_consent()) {
            $config->loadonrequest = 1;
        }

        if ($sourcetype === 'package' && $packageorigin === 'library') {
            $coursecontext = \block_interactembed\local\creator_connector::course_context($this->context);
            if (!$coursecontext) {
                throw new moodle_exception('creatorunavailable', 'block_interactembed');
            }
            $snapshot = \block_interactembed\local\creator_connector::snapshot(
                $coursecontext,
                $creatorpublicationid
            );
            \block_interactembed\local\package\package_importer::import_stored_files(
                $this->context,
                $snapshot->files,
                $snapshot->entrypoint,
                $snapshot->packagehash
            );
            $config->entrypoint = $snapshot->entrypoint;
        } else {
            file_save_draft_area_files(
                $draftitemid,
                $this->context->id,
                'block_interactembed',
                'content',
                0,
                ['subdirs' => true, 'maxfiles' => -1]
            );
        }
        file_save_draft_area_files(
            $coverdraftitemid,
            $this->context->id,
            'block_interactembed',
            'cover',
            0,
            [
                'subdirs' => false,
                'maxfiles' => 1,
                'maxbytes' => 5 * 1024 * 1024,
                'accepted_types' => ['image'],
            ]
        );

        if ($sourcetype === 'package') {
            if ($packageorigin !== 'library') {
                \block_interactembed\local\package\package_importer::expand_saved_archive($this->context);
            }
        } else {
            get_file_storage()->delete_area_files($this->context->id, 'block_interactembed', 'content', 0);
        }

        unset($config->packagefiles);
        unset($config->coverfiles);
        unset($config->packageorigin);
        unset($config->creatorpublicationid);
        // Bust cached package entry pages and assets whenever the editor saves this instance.
        $config->revision = \block_interactembed\local\configuration_guard::next_revision(
            (int)($this->config->revision ?? 0)
        );
        parent::instance_config_save($config, $nolongerused);
    }

    /**
     * Deletes all files owned by the block instance.
     *
     * @return bool
     */
    public function instance_delete(): bool {
        get_file_storage()->delete_area_files($this->context->id, 'block_interactembed');
        return true;
    }

    /**
     * Copies all files when Moodle duplicates the block.
     *
     * @param int $fromid source block instance id
     * @return bool
     */
    public function instance_copy($fromid): bool {
        $sourcecontext = context_block::instance($fromid);
        $fs = get_file_storage();
        foreach (['content', 'cover'] as $filearea) {
            $files = $fs->get_area_files(
                $sourcecontext->id,
                'block_interactembed',
                $filearea,
                0,
                'id ASC',
                false
            );
            foreach ($files as $file) {
                $fs->create_file_from_storedfile(['contextid' => $this->context->id], $file);
            }
        }
        return true;
    }
}
