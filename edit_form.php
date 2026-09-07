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
 * Instance configuration form for InteractEmbed.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * InteractEmbed instance edit form.
 */
class block_interactembed_edit_form extends block_edit_form {
    /**
     * Displays the complete configuration form as soon as a block is added.
     *
     * @return bool
     */
    public static function display_form_when_adding(): bool {
        return true;
    }

    /**
     * Adds plugin-specific fields.
     *
     * @param MoodleQuickForm $mform form being built
     */
    protected function specific_definition($mform): void {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $this->add_source_fields($mform);
        $this->add_presentation_fields($mform);
        $this->add_behaviour_fields($mform);
    }

    /**
     * Adds content-source controls.
     *
     * @param MoodleQuickForm $mform form being built
     */
    private function add_source_fields(MoodleQuickForm $mform): void {

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_interactembed'));
        $mform->setType('config_title', PARAM_TEXT);

        $mform->addElement('textarea', 'config_description', get_string('contentdescription', 'block_interactembed'), [
            'rows' => 3,
            'class' => 'w-100',
        ]);
        $mform->setType('config_description', PARAM_TEXT);
        $mform->addHelpButton('config_description', 'contentdescription', 'block_interactembed');

        $types = [];
        if (\block_interactembed\local\site_policy::allows_source('url')) {
            $types['url'] = get_string('sourcetypeurl', 'block_interactembed');
        }
        if (\block_interactembed\local\site_policy::allows_source('package')) {
            $types['package'] = get_string('sourcetypepackage', 'block_interactembed');
        }
        $types['guide'] = get_string('sourcetypeguide', 'block_interactembed');
        $mform->addElement('select', 'config_sourcetype', get_string('sourcetype', 'block_interactembed'), $types);
        $mform->setDefault('config_sourcetype', array_key_first($types));

        $mform->addElement('textarea', 'config_sourceurl', get_string('sourceurl', 'block_interactembed'), [
            'rows' => 4,
            'class' => 'w-100',
        ]);
        $mform->setType('config_sourceurl', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('config_sourceurl', 'sourceurl', 'block_interactembed');
        $mform->hideIf('config_sourceurl', 'config_sourcetype', 'neq', 'url');

        $fileoptions = [
            'accepted_types' => '*',
            'maxbytes' => 0,
            'maxfiles' => -1,
            'subdirs' => true,
        ];
        $mform->addElement(
            'filemanager',
            'config_packagefiles',
            get_string('packagefiles', 'block_interactembed'),
            null,
            $fileoptions
        );
        $mform->addHelpButton('config_packagefiles', 'packagefiles', 'block_interactembed');
        $mform->hideIf('config_packagefiles', 'config_sourcetype', 'neq', 'package');

        $coursecontext = \block_interactembed\local\creator_connector::course_context($this->block->context);
        $creatoroptions = $coursecontext
            ? \block_interactembed\local\creator_connector::options($coursecontext)
            : [];
        if ($creatoroptions) {
            $mform->addElement('select', 'config_packageorigin', get_string('packageorigin', 'block_interactembed'), [
                'upload' => get_string('packageoriginupload', 'block_interactembed'),
                'library' => get_string('packageoriginlibrary', 'block_interactembed'),
            ]);
            $mform->setDefault('config_packageorigin', 'upload');
            $mform->hideIf('config_packageorigin', 'config_sourcetype', 'neq', 'package');
            $mform->addElement(
                'select',
                'config_creatorpublicationid',
                get_string('creatorpublication', 'block_interactembed'),
                $creatoroptions
            );
            $mform->setType('config_creatorpublicationid', PARAM_INT);
            $mform->hideIf('config_creatorpublicationid', 'config_sourcetype', 'neq', 'package');
            $mform->hideIf('config_creatorpublicationid', 'config_packageorigin', 'neq', 'library');
            $mform->hideIf('config_packagefiles', 'config_packageorigin', 'eq', 'library');
        }

        $mform->addElement('text', 'config_entrypoint', get_string('entrypoint', 'block_interactembed'));
        $mform->setType('config_entrypoint', PARAM_PATH);
        $mform->setDefault('config_entrypoint', 'index.html');
        $mform->addHelpButton('config_entrypoint', 'entrypoint', 'block_interactembed');
        $mform->hideIf('config_entrypoint', 'config_sourcetype', 'neq', 'package');
        if ($creatoroptions) {
            $mform->hideIf('config_entrypoint', 'config_packageorigin', 'eq', 'library');
        }
    }

    /**
     * Adds cover and responsive-layout controls.
     *
     * @param MoodleQuickForm $mform form being built
     */
    private function add_presentation_fields(MoodleQuickForm $mform): void {

        $coveroptions = [
            'accepted_types' => ['image'],
            'maxbytes' => 5 * 1024 * 1024,
            'maxfiles' => 1,
            'subdirs' => false,
        ];
        $mform->addElement(
            'filemanager',
            'config_coverfiles',
            get_string('coverimage', 'block_interactembed'),
            null,
            $coveroptions
        );
        $mform->addHelpButton('config_coverfiles', 'coverimage', 'block_interactembed');
        $mform->addElement(
            'static',
            'coverimageguidance',
            '',
            get_string('coverimageguidance', 'block_interactembed')
        );

        $mform->addElement('text', 'config_coveralt', get_string('coveralt', 'block_interactembed'));
        $mform->setType('config_coveralt', PARAM_TEXT);
        $mform->addHelpButton('config_coveralt', 'coveralt', 'block_interactembed');

        $ratios = [
            '16x9' => get_string('ratio16x9', 'block_interactembed'),
            '4x3' => get_string('ratio4x3', 'block_interactembed'),
            '1x1' => get_string('ratio1x1', 'block_interactembed'),
            '3x4' => get_string('ratio3x4', 'block_interactembed'),
            '9x16' => get_string('ratio9x16', 'block_interactembed'),
            'custom' => get_string('ratiocustom', 'block_interactembed'),
        ];
        $mform->addElement('select', 'config_aspectratio', get_string('aspectratio', 'block_interactembed'), $ratios);
        $defaultaspectratio = \block_interactembed\local\site_policy::string_setting(
            'defaultaspectratio',
            '16x9'
        );
        $mform->setDefault(
            'config_aspectratio',
            array_key_exists($defaultaspectratio, $ratios) ? $defaultaspectratio : '16x9'
        );

        $mform->addElement('text', 'config_customheight', get_string('customheight', 'block_interactembed'));
        $mform->setType('config_customheight', PARAM_INT);
        $defaultheight = (int)\block_interactembed\local\site_policy::string_setting('defaultcustomheight', '600');
        $mform->setDefault('config_customheight', max(200, min(4000, $defaultheight)));
        $mform->addRule('config_customheight', null, 'numeric', null, 'client');
        $mform->hideIf('config_customheight', 'config_aspectratio', 'neq', 'custom');

        $displaymodes = [
            'inline' => get_string('displaymodeinline', 'block_interactembed'),
            'card' => get_string('displaymodecard', 'block_interactembed'),
        ];
        $mform->addElement(
            'select',
            'config_displaymode',
            get_string('displaymode', 'block_interactembed'),
            $displaymodes
        );
        $defaultdisplaymode = \block_interactembed\local\site_policy::string_setting(
            'defaultdisplaymode',
            'inline'
        );
        $mform->setDefault('config_displaymode', \block_interactembed\local\configuration_guard::display_mode(
            $defaultdisplaymode
        ));
        $mform->addHelpButton('config_displaymode', 'displaymode', 'block_interactembed');
    }

    /**
     * Adds loading, full-screen, embed-code and sandbox controls.
     *
     * @param MoodleQuickForm $mform form being built
     */
    private function add_behaviour_fields(MoodleQuickForm $mform): void {

        $mform->addElement(
            'advcheckbox',
            'config_loadonrequest',
            get_string('loadonrequest', 'block_interactembed')
        );
        $mform->setDefault(
            'config_loadonrequest',
            \block_interactembed\local\site_policy::bool_setting('defaultloadonrequest', false)
        );
        $mform->addHelpButton('config_loadonrequest', 'loadonrequest', 'block_interactembed');
        $mform->hideIf('config_loadonrequest', 'config_displaymode', 'eq', 'card');
        if (\block_interactembed\local\site_policy::forces_remote_consent()) {
            $mform->addElement(
                'static',
                'remoteconsentnotice',
                '',
                get_string('remoteconsentnotice', 'block_interactembed')
            );
        }

        $mform->addElement('advcheckbox', 'config_showfullscreen', get_string('showfullscreen', 'block_interactembed'));
        $mform->setDefault(
            'config_showfullscreen',
            \block_interactembed\local\site_policy::bool_setting('defaultshowfullscreen', true)
        );

        if (\block_interactembed\local\site_policy::allows_embed_code()) {
            $mform->addElement(
                'advcheckbox',
                'config_showembedcode',
                get_string('showembedcode', 'block_interactembed')
            );
            $mform->setDefault(
                'config_showembedcode',
                \block_interactembed\local\site_policy::bool_setting('defaultshowembedcode', false)
            );
            $mform->addHelpButton('config_showembedcode', 'showembedcode', 'block_interactembed');
        }

        $mform->addElement('advcheckbox', 'config_lazyload', get_string('lazyload', 'block_interactembed'));
        $mform->setDefault(
            'config_lazyload',
            \block_interactembed\local\site_policy::bool_setting('defaultlazyload', true)
        );

        $sandboxprofiles = [
            'strict' => get_string('sandboxstrict', 'block_interactembed'),
            'standard' => get_string('sandboxstandard', 'block_interactembed'),
        ];
        if (has_capability('block/interactembed:trustpackage', $this->block->context)) {
            $sandboxprofiles['trusted'] = get_string('sandboxtrusted', 'block_interactembed');
        }
        $mform->addElement(
            'select',
            'config_sandboxprofile',
            get_string('sandboxprofile', 'block_interactembed'),
            $sandboxprofiles
        );
        $defaultsandbox = \block_interactembed\local\site_policy::string_setting('defaultsandboxprofile', 'standard');
        $mform->setDefault(
            'config_sandboxprofile',
            in_array($defaultsandbox, ['strict', 'standard'], true) ? $defaultsandbox : 'standard'
        );
        $mform->addHelpButton('config_sandboxprofile', 'sandboxprofile', 'block_interactembed');
    }

    /**
     * Prepares package files in the user's draft area.
     *
     * @param stdClass $defaults default form data
     */
    public function set_data($defaults): void {
        $draftitemid = file_get_submitted_draft_itemid('config_packagefiles');
        file_prepare_draft_area(
            $draftitemid,
            $this->block->context->id,
            'block_interactembed',
            'content',
            0,
            ['subdirs' => true, 'maxfiles' => -1]
        );
        $defaults->config_packagefiles = $draftitemid;

        $coverdraftitemid = file_get_submitted_draft_itemid('config_coverfiles');
        file_prepare_draft_area(
            $coverdraftitemid,
            $this->block->context->id,
            'block_interactembed',
            'cover',
            0,
            ['subdirs' => false, 'maxfiles' => 1, 'accepted_types' => ['image']]
        );
        $defaults->config_coverfiles = $coverdraftitemid;
        parent::set_data($defaults);
    }

    /**
     * Validates the source and display settings.
     *
     * @param array $data submitted values
     * @param array $files submitted files
     * @return array field errors
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        return array_merge(
            $errors,
            $this->validate_source($data),
            $this->validate_cover($data),
            $this->validate_custom_height($data)
        );
    }

    /**
     * Validates the selected URL or package source.
     *
     * @param array $data submitted values
     * @return array field errors
     */
    private function validate_source(array $data): array {
        $errors = [];
        $type = $data['config_sourcetype'] ?? 'url';

        if ($type === 'url') {
            try {
                \block_interactembed\local\embed\embed_builder::build((string)($data['config_sourceurl'] ?? ''));
            } catch (\InvalidArgumentException $exception) {
                $errors['config_sourceurl'] = get_string('invalidsource', 'block_interactembed');
            }
        } else if ($type === 'package' && ($data['config_packageorigin'] ?? 'upload') === 'library') {
            try {
                $coursecontext = \block_interactembed\local\creator_connector::course_context($this->block->context);
                if (!$coursecontext) {
                    throw new \moodle_exception('creatorunavailable', 'block_interactembed');
                }
                \block_interactembed\local\creator_connector::snapshot(
                    $coursecontext,
                    (int) ($data['config_creatorpublicationid'] ?? 0)
                );
            } catch (\Throwable $exception) {
                debugging($exception->getMessage(), DEBUG_DEVELOPER);
                $errors['config_creatorpublicationid'] = get_string(
                    'creatorpublicationinvalid',
                    'block_interactembed'
                );
            }
        } else if ($type === 'package' && empty($data['config_packagefiles'])) {
            $errors['config_packagefiles'] = get_string('packagefilesrequired', 'block_interactembed');
        } else if ($type === 'package') {
            try {
                \block_interactembed\local\package\package_importer::validate_draft(
                    (int)$data['config_packagefiles'],
                    (string)($data['config_entrypoint'] ?? 'index.html')
                );
            } catch (\moodle_exception $exception) {
                $errors['config_packagefiles'] = $exception->getMessage();
            }
        }
        return $errors;
    }

    /**
     * Requires alternative text when a cover image is present.
     *
     * @param array $data submitted values
     * @return array field errors
     */
    private function validate_cover(array $data): array {
        $coverdraftitemid = (int)($data['config_coverfiles'] ?? 0);
        if ($coverdraftitemid <= 0) {
            return [];
        }

        $coverinfo = file_get_draft_area_info($coverdraftitemid);
        if (!empty($coverinfo['filecount']) && trim((string)($data['config_coveralt'] ?? '')) === '') {
            return ['config_coveralt' => get_string('coveraltrequired', 'block_interactembed')];
        }
        return [];
    }

    /**
     * Validates the custom-height range when that layout is selected.
     *
     * @param array $data submitted values
     * @return array field errors
     */
    private function validate_custom_height(array $data): array {
        $height = (int)($data['config_customheight'] ?? 600);
        if (($data['config_aspectratio'] ?? '') === 'custom' && ($height < 200 || $height > 4000)) {
            return ['config_customheight' => get_string('customheighterror', 'block_interactembed')];
        }
        return [];
    }
}
