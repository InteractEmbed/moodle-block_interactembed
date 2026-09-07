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
 * Administrative settings for InteractEmbed.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
        'block_interactembed/defaultsheading',
        get_string('defaultsheading', 'block_interactembed'),
        get_string('defaultsheading_desc', 'block_interactembed')
    ));
    $settings->add(new admin_setting_configselect(
        'block_interactembed/defaultaspectratio',
        get_string('defaultaspectratio', 'block_interactembed'),
        get_string('defaultaspectratio_desc', 'block_interactembed'),
        '16x9',
        [
            '16x9' => get_string('ratio16x9', 'block_interactembed'),
            '4x3' => get_string('ratio4x3', 'block_interactembed'),
            '1x1' => get_string('ratio1x1', 'block_interactembed'),
            '3x4' => get_string('ratio3x4', 'block_interactembed'),
            '9x16' => get_string('ratio9x16', 'block_interactembed'),
            'custom' => get_string('ratiocustom', 'block_interactembed'),
        ]
    ));
    $settings->add(new admin_setting_configtext(
        'block_interactembed/defaultcustomheight',
        get_string('defaultcustomheight', 'block_interactembed'),
        get_string('defaultcustomheight_desc', 'block_interactembed'),
        600,
        PARAM_INT
    ));
    $settings->add(new admin_setting_configselect(
        'block_interactembed/defaultdisplaymode',
        get_string('defaultdisplaymode', 'block_interactembed'),
        get_string('defaultdisplaymode_desc', 'block_interactembed'),
        'inline',
        [
            'inline' => get_string('displaymodeinline', 'block_interactembed'),
            'card' => get_string('displaymodecard', 'block_interactembed'),
        ]
    ));
    $settings->add(new admin_setting_configcheckbox(
        'block_interactembed/defaultloadonrequest',
        get_string('defaultloadonrequest', 'block_interactembed'),
        get_string('defaultloadonrequest_desc', 'block_interactembed'),
        0
    ));
    $settings->add(new admin_setting_configcheckbox(
        'block_interactembed/defaultshowfullscreen',
        get_string('defaultshowfullscreen', 'block_interactembed'),
        get_string('defaultshowfullscreen_desc', 'block_interactembed'),
        1
    ));
    $settings->add(new admin_setting_configcheckbox(
        'block_interactembed/defaultshowembedcode',
        get_string('defaultshowembedcode', 'block_interactembed'),
        get_string('defaultshowembedcode_desc', 'block_interactembed'),
        0
    ));
    $settings->add(new admin_setting_configcheckbox(
        'block_interactembed/defaultlazyload',
        get_string('defaultlazyload', 'block_interactembed'),
        get_string('defaultlazyload_desc', 'block_interactembed'),
        1
    ));
    $settings->add(new admin_setting_configselect(
        'block_interactembed/defaultsandboxprofile',
        get_string('defaultsandboxprofile', 'block_interactembed'),
        get_string('defaultsandboxprofile_desc', 'block_interactembed'),
        'standard',
        [
            'strict' => get_string('sandboxstrict', 'block_interactembed'),
            'standard' => get_string('sandboxstandard', 'block_interactembed'),
        ]
    ));

    $settings->add(new admin_setting_heading(
        'block_interactembed/policiesheading',
        get_string('policiesheading', 'block_interactembed'),
        get_string('policiesheading_desc', 'block_interactembed')
    ));
    $settings->add(new admin_setting_configselect(
        'block_interactembed/sourcepolicy',
        get_string('sourcepolicy', 'block_interactembed'),
        get_string('sourcepolicy_desc', 'block_interactembed'),
        'both',
        [
            'both' => get_string('sourcepolicyboth', 'block_interactembed'),
            'urlonly' => get_string('sourcepolicyurlonly', 'block_interactembed'),
            'packageonly' => get_string('sourcepolicypackageonly', 'block_interactembed'),
        ]
    ));
    $settings->add(new admin_setting_configcheckbox(
        'block_interactembed/allowembedcode',
        get_string('allowembedcode', 'block_interactembed'),
        get_string('allowembedcode_desc', 'block_interactembed'),
        1
    ));
    $settings->add(new admin_setting_configcheckbox(
        'block_interactembed/forceremoteconsent',
        get_string('forceremoteconsent', 'block_interactembed'),
        get_string('forceremoteconsent_desc', 'block_interactembed'),
        0
    ));
    $settings->add(new admin_setting_configcheckbox(
        'block_interactembed/allowgenericurls',
        get_string('allowgenericurls', 'block_interactembed'),
        get_string('allowgenericurls_desc', 'block_interactembed'),
        0
    ));
    $settings->add(new admin_setting_configtextarea(
        'block_interactembed/alloweddomains',
        get_string('alloweddomains', 'block_interactembed'),
        get_string('alloweddomains_desc', 'block_interactembed'),
        '',
        PARAM_RAW_TRIMMED
    ));

    $settings->add(new admin_setting_heading(
        'block_interactembed/limitsheading',
        get_string('limitsheading', 'block_interactembed'),
        get_string('limitsheading_desc', 'block_interactembed')
    ));
    $settings->add(new admin_setting_configtext(
        'block_interactembed/maxarchivefiles',
        get_string('maxarchivefiles', 'block_interactembed'),
        get_string('maxarchivefiles_desc', 'block_interactembed'),
        5000,
        PARAM_INT
    ));
    $settings->add(new admin_setting_configtext(
        'block_interactembed/maxarchivebytes',
        get_string('maxarchivebytes', 'block_interactembed'),
        get_string('maxarchivebytes_desc', 'block_interactembed'),
        209715200,
        PARAM_INT
    ));
}
