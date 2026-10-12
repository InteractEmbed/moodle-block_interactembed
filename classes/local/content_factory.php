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

use block_interactembed\local\embed\embed_builder;
use block_interactembed\local\package\entrypoint_locator;
use block_interactembed\local\package\package_access_key;
use block_interactembed\local\security\sandbox_policy;
use block_interactembed\output\content;
use context_block;
use html_writer;
use moodle_url;
use stdClass;

/**
 * Creates safe player data shared by the block and embedded page.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class content_factory {
    /** Display ratio to CSS aspect-ratio mapping. */
    private const RATIOS = [
        '16x9' => '16 / 9',
        '4x3' => '4 / 3',
        '1x1' => '1 / 1',
        '3x4' => '3 / 4',
        '9x16' => '9 / 16',
    ];

    /**
     * Builds template data for a block instance.
     *
     * @param stdClass $instance block instance record
     * @param context_block $context block context
     * @param stdClass $config decoded instance configuration
     * @param bool $showembedtools whether editor-only embed tools are shown
     * @return content
     */
    public static function from_instance(
        stdClass $instance,
        context_block $context,
        stdClass $config,
        bool $showembedtools = false
    ): content {
        $sourcetype = $config->sourcetype ?? 'url';
        if (!site_policy::allows_source($sourcetype)) {
            throw new \moodle_exception('sourcenotallowed', 'block_interactembed');
        }

        $siteorigin = self::site_origin();
        $source = self::source_data($sourcetype, $context, $config, $siteorigin);
        $display = self::display_data($sourcetype, $config, $source['origin'], $siteorigin);
        $presentation = self::presentation_data($context, $config);
        $domid = 'interactembed-' . (int)$instance->id;
        $embedurl = new moodle_url('/blocks/interactembed/embed.php', ['id' => (int)$instance->id]);

        return new content([
            'domid' => $domid,
            'iframeurl' => $source['iframeurl'],
            'title' => $presentation['title'],
            'description' => $presentation['description'],
            'hasdescription' => $presentation['description'] !== '',
            'provider' => $source['provider'],
            'expectedorigin' => $source['origin'],
            'sandbox' => $display['sandbox'],
            'allow' => 'fullscreen; autoplay; clipboard-write; encrypted-media; picture-in-picture',
            'lazy' => !empty($config->lazyload),
            'showfullscreen' => !empty($config->showfullscreen),
            'waitforinteraction' => $display['waitforinteraction'],
            'iscard' => $display['iscard'],
            'iscustomheight' => $display['custom'],
            'customheight' => $display['height'],
            'ratio' => $display['ratio'],
            'coverurl' => $presentation['coverurl'],
            'hascover' => $presentation['coverurl'] !== '',
            'coveralt' => $presentation['coveralt'],
            'frameid' => $domid . '-frame',
            'showembedtools' => $showembedtools,
            'embedcode' => self::embed_code($embedurl, $presentation['title'], $display['height'], (int)$instance->id),
            'embedinputid' => $domid . '-embedcode',
            'externalurl' => $sourcetype === 'package' ? $embedurl->out(false) : $source['iframeurl'],
        ]);
    }

    /**
     * Returns the canonical Moodle origin.
     *
     * @return string scheme, host and optional port
     */
    private static function site_origin(): string {
        $siteparts = parse_url((new moodle_url('/'))->out(false));
        return strtolower($siteparts['scheme']) . '://' . strtolower($siteparts['host']) .
            (isset($siteparts['port']) ? ':' . (int)$siteparts['port'] : '');
    }

    /**
     * Resolves the selected source into player-safe values.
     *
     * @param string $sourcetype source type
     * @param context_block $context block context
     * @param stdClass $config decoded instance configuration
     * @param string $siteorigin Moodle site origin
     * @return array{iframeurl: string, origin: string, provider: string}
     */
    private static function source_data(
        string $sourcetype,
        context_block $context,
        stdClass $config,
        string $siteorigin
    ): array {
        if ($sourcetype === 'guide') {
            $language = substr(current_language(), 0, 2);
            if (!in_array($language, ['en', 'es', 'fr'], true)) {
                $language = 'en';
            }
            return [
                'iframeurl' => (new moodle_url('/blocks/interactembed/guide/index.html', [
                    'lang' => $language,
                ]))->out(false),
                'origin' => $siteorigin,
                'provider' => 'interactembedguide',
            ];
        }

        if ($sourcetype === 'package') {
            return self::package_source_data($context, $config, $siteorigin);
        }

        $result = embed_builder::build((string)($config->sourceurl ?? ''));
        return [
            'iframeurl' => $result->get_url(),
            'origin' => $result->get_origin(),
            'provider' => $result->get_provider(),
        ];
    }

    /**
     * Builds a signed URL for an HTML5 package entry point.
     *
     * @param context_block $context block context
     * @param stdClass $config decoded instance configuration
     * @param string $siteorigin Moodle site origin
     * @return array{iframeurl: string, origin: string, provider: string}
     */
    private static function package_source_data(context_block $context, stdClass $config, string $siteorigin): array {
        $entrypoint = entrypoint_locator::locate($context, (string)($config->entrypoint ?? 'index.html'));
        $revision = (int)($config->revision ?? 0);
        $accesskey = package_access_key::create($context->id, $revision);
        $packagepath = trim($entrypoint['filepath'], '/');
        $pathparts = $packagepath === '' ? [] : explode('/', $packagepath);
        $pathparts[] = $entrypoint['filename'];
        $encodedpath = implode('/', array_map('rawurlencode', $pathparts));
        $packageurl = (new moodle_url('/blocks/interactembed/package.php'))->out(false);

        return [
            'iframeurl' => $packageurl . '/' . $context->id . '/' . rawurlencode($accesskey) .
                '/' . $encodedpath . '?rev=' . $revision,
            'origin' => $siteorigin,
            'provider' => 'package',
        ];
    }

    /**
     * Normalises aspect ratio, loading mode and iframe sandbox.
     *
     * @param string $sourcetype source type
     * @param stdClass $config decoded instance configuration
     * @param string $origin selected source origin
     * @param string $siteorigin Moodle site origin
     * @return array{ratio: string, custom: bool, height: int, iscard: bool, waitforinteraction: bool, sandbox: string}
     */
    private static function display_data(
        string $sourcetype,
        stdClass $config,
        string $origin,
        string $siteorigin
    ): array {
        $ratio = array_key_exists($config->aspectratio ?? '', self::RATIOS)
            ? $config->aspectratio
            : '16x9';
        $custom = ($config->aspectratio ?? '') === 'custom';
        $height = max(200, min(4000, (int)($config->customheight ?? 600)));
        $displaymode = configuration_guard::display_mode((string)($config->displaymode ?? 'inline'));
        $iscard = $displaymode === 'card';
        $waitforinteraction = $iscard || !empty($config->loadonrequest) ||
            ($sourcetype === 'url' && site_policy::forces_remote_consent());

        return [
            'ratio' => self::RATIOS[$ratio],
            'custom' => $custom,
            'height' => $height,
            'iscard' => $iscard,
            'waitforinteraction' => $waitforinteraction,
            'sandbox' => self::sandbox($sourcetype, $config, $origin, $siteorigin),
        ];
    }

    /**
     * Normalises the iframe sandbox for the selected source.
     *
     * @param string $sourcetype source type
     * @param stdClass $config decoded instance configuration
     * @param string $origin selected source origin
     * @param string $siteorigin Moodle site origin
     * @return string sandbox attribute
     */
    private static function sandbox(
        string $sourcetype,
        stdClass $config,
        string $origin,
        string $siteorigin
    ): string {
        $profile = in_array($config->sandboxprofile ?? '', ['strict', 'standard', 'trusted'], true)
            ? (string)$config->sandboxprofile
            : 'standard';
        if ($sourcetype !== 'package' && $profile === 'trusted') {
            // The trusted-package profile has no additional meaning for remote providers.
            $profile = 'standard';
        }
        $sandbox = sandbox_policy::tokens($profile);
        if ($sourcetype !== 'package' && $profile === 'standard' && $origin !== $siteorigin) {
            // This preserves provider cookies and storage without granting access to the Moodle origin.
            $sandbox .= ' allow-same-origin';
        }
        return $sandbox;
    }

    /**
     * Prepares accessible title, description and cover data.
     *
     * @param context_block $context block context
     * @param stdClass $config decoded instance configuration
     * @return array{title: string, description: string, coverurl: string, coveralt: string}
     */
    private static function presentation_data(context_block $context, stdClass $config): array {
        $title = !empty($config->title)
            ? format_string($config->title, true, ['context' => $context])
            : get_string('embedtitle', 'block_interactembed');
        $description = trim((string)($config->description ?? ''));
        $coverurl = '';
        $coverfiles = get_file_storage()->get_area_files(
            $context->id,
            'block_interactembed',
            'cover',
            0,
            'id ASC',
            false
        );
        if ($coverfiles) {
            $coverfile = reset($coverfiles);
            $coverurl = moodle_url::make_pluginfile_url(
                $context->id,
                'block_interactembed',
                'cover',
                0,
                $coverfile->get_filepath(),
                $coverfile->get_filename()
            )->out(false);
        }
        $coveralt = trim((string)($config->coveralt ?? ''));
        if ($coverurl !== '' && $coveralt === '') {
            // Preserve an accessible fallback for instances created before cover alternative text was required.
            $coveralt = $title;
        }

        return [
            'title' => $title,
            'description' => $description,
            'coverurl' => $coverurl,
            'coveralt' => $coveralt,
        ];
    }

    /**
     * Builds the optional same-Moodle iframe snippet.
     *
     * @param moodle_url $embedurl player URL
     * @param string $title accessible iframe title
     * @param int $height iframe height
     * @param int $instanceid block instance id
     * @return string copyable iframe; Moodle loads the resizer through AMD
     */
    private static function embed_code(moodle_url $embedurl, string $title, int $height, int $instanceid): string {
        $nonce = message_nonce::create();
        $embedurl = clone $embedurl;
        $embedurl->param('nonce', $nonce);
        $embedcode = html_writer::tag('iframe', '', [
            'src' => $embedurl->out(false),
            'title' => $title,
            'width' => '100%',
            'height' => (string)$height,
            'loading' => 'lazy',
            'allowfullscreen' => 'allowfullscreen',
            'data-interactembed-id' => (string)$instanceid,
            'data-interactembed-nonce' => $nonce,
            'style' => 'border:0;display:block;width:100%;',
        ]);
        return $embedcode;
    }
}
