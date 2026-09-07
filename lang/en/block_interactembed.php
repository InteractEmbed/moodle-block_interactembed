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
 * English strings for InteractEmbed.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['alloweddomains'] = 'Additional allowed domains';
$string['alloweddomains_desc'] = 'One domain per line, for example content.example.org. The domain and its subdomains will be accepted. Recognised providers do not need to be listed.';
$string['allowembedcode'] = 'Allow embed code';
$string['allowembedcode_desc'] = 'Allow block editors to expose a copyable iframe code for reuse within the same Moodle site. Disabling this setting hides and enforces the option for every block.';
$string['allowgenericurls'] = 'Allow generic URLs';
$string['allowgenericurls_desc'] = 'Allow editors to embed any valid HTTP or HTTPS web address that is not a recognised provider or listed below. Enable this only for trusted editors. The remote site must still permit iframe display.';
$string['aspectratio'] = 'Display format';
$string['configtitle'] = 'Block title';
$string['contentdescription'] = 'Introduction or accessible description';
$string['contentdescription_help'] = 'Optional plain-text instructions, learning objective or alternative description displayed before the player.';
$string['contentunavailable'] = 'This interactive content is currently unavailable.';
$string['copyembedcode'] = 'Copy embed code';
$string['coveralt'] = 'Cover image description';
$string['coveralt_help'] = 'Briefly describe the meaningful content of the cover image for people using assistive technology.';
$string['coveraltrequired'] = 'Describe the cover image, or remove the image.';
$string['coverimage'] = 'Cover image';
$string['coverimage_help'] = 'Optional image displayed in compact-card or click-to-load mode. Maximum size: 5 MB.';
$string['coverimageguidance'] = 'Recommended: WebP under 500 KB. Suggested dimensions: 1600 × 900 px (16:9), 1600 × 1200 px (4:3), 1200 × 1200 px (1:1), 1200 × 1600 px (3:4), or 900 × 1600 px (9:16). Maximum: 5 MB. Keep important content centred because the image may be cropped.';
$string['creatorpublication'] = 'Library publication';
$string['creatorpublicationinvalid'] = 'The selected Library publication is unavailable or not permitted in this course.';
$string['creatorpublicationnumber'] = 'Publication {$a}';
$string['creatorselectpublication'] = 'Choose a publication…';
$string['creatorunavailable'] = 'The InteractEmbed Library is unavailable or incompatible.';
$string['customheight'] = 'Custom height (pixels)';
$string['customheighterror'] = 'Enter a height between 200 and 4000 pixels.';
$string['defaultaspectratio'] = 'Default display format';
$string['defaultaspectratio_desc'] = 'Initial format selected when a new InteractEmbed block is created.';
$string['defaultcustomheight'] = 'Default custom height';
$string['defaultcustomheight_desc'] = 'Initial custom height in pixels. Block editors can change it when Custom height is selected.';
$string['defaultdisplaymode'] = 'Default display mode';
$string['defaultdisplaymode_desc'] = 'Initial display mode selected for new blocks.';
$string['defaultlazyload'] = 'Enable deferred loading by default';
$string['defaultlazyload_desc'] = 'Initial value of the deferred-loading option for new blocks.';
$string['defaultloadonrequest'] = 'Require a click by default';
$string['defaultloadonrequest_desc'] = 'Initial value of the click-to-load option for new blocks.';
$string['defaultsandboxprofile'] = 'Default security profile';
$string['defaultsandboxprofile_desc'] = 'Initial security profile for new blocks. The administrator-only Trusted package profile must always be selected explicitly in a block.';
$string['defaultsheading'] = 'Defaults for new blocks';
$string['defaultsheading_desc'] = 'These values initialise newly created blocks. They do not change existing blocks and remain editable unless a site policy below overrides them.';
$string['defaultshowembedcode'] = 'Show embed code by default';
$string['defaultshowembedcode_desc'] = 'Initial value of the embed-code option for new blocks. The site policy below can prevent blocks from displaying the copy tool.';
$string['defaultshowfullscreen'] = 'Show full-screen button by default';
$string['defaultshowfullscreen_desc'] = 'Initial value of the full-screen option for new blocks.';
$string['displaymode'] = 'Display mode';
$string['displaymode_help'] = 'Inline displays the player in the block. Compact card initially displays a lightweight launch panel and expands the player after a click.';
$string['displaymodecard'] = 'Compact card, then open the player';
$string['displaymodeinline'] = 'Inline player';
$string['embedcode'] = 'Embed code';
$string['embedcodecopied'] = 'Embed code copied.';
$string['embedcodedesc'] = 'Use this code in another HTML-capable area of this Moodle site. Access permissions remain unchanged.';
$string['embedtitle'] = 'Embedded interactive content';
$string['entrypoint'] = 'Start file';
$string['entrypoint_help'] = 'Path to the HTML file that starts the package, for example index.html or presentation/index.html.';
$string['exitfullscreen'] = 'Exit full screen';
$string['forceremoteconsent'] = 'Always require a click for remote content';
$string['forceremoteconsent_desc'] = 'Force every web-address or iframe source to wait for visitor interaction before loading. This site policy overrides the corresponding block setting.';
$string['fullscreen'] = 'View full screen';
$string['interactembed:addinstance'] = 'Add a new InteractEmbed block';
$string['interactembed:myaddinstance'] = 'Add a new InteractEmbed block to the Dashboard';
$string['interactembed:trustpackage'] = 'Use the trusted HTML5 package profile';
$string['invalidsource'] = 'Enter a valid HTTP or HTTPS address, or paste a valid iframe.';
$string['lazyload'] = 'Load content only when it approaches the screen';
$string['limitsheading'] = 'HTML5 package limits';
$string['limitsheading_desc'] = 'Safety limits applied when ZIP packages are inspected and extracted.';
$string['loadcontent'] = 'Load interactive content';
$string['loadcontentdesc'] = 'The content will load after you select the button. External content may connect to another service.';
$string['loadonrequest'] = 'Require a click before loading the content';
$string['loadonrequest_help'] = 'Prevents the iframe from loading until the visitor selects the load button. This can improve initial performance and privacy. Compact card mode always uses this behaviour.';
$string['maxarchivebytes'] = 'Maximum extracted archive size';
$string['maxarchivebytes_desc'] = 'Maximum total uncompressed size, in bytes, accepted from one ZIP archive.';
$string['maxarchivefiles'] = 'Maximum archive files';
$string['maxarchivefiles_desc'] = 'Maximum number of entries accepted in one ZIP archive.';
$string['notconfigured'] = 'Configure this block to select interactive content.';
$string['openexternal'] = 'Open content in a new window';
$string['packageentryinvalid'] = 'The package start file must be a safe relative .html or .htm path.';
$string['packageentrymissing'] = 'The configured start file was not found in this package.';
$string['packageentrynothtml'] = 'The configured start file must be an HTML file ending in .html or .htm.';
$string['packagefiles'] = 'HTML5 package files';
$string['packagefiles_help'] = 'Upload either one ZIP archive or the unpacked files for an HTML5 project, preserving its folders. The package must contain an HTML start file.';
$string['packagefilesrequired'] = 'Add at least one package file.';
$string['packageinvalidarchive'] = 'The ZIP archive is invalid or cannot be read.';
$string['packageonlyonearchive'] = 'Upload one ZIP archive by itself, or upload unpacked files without a ZIP archive.';
$string['packageorigin'] = 'Package origin';
$string['packageoriginlibrary'] = 'InteractEmbed Library';
$string['packageoriginupload'] = 'My files';
$string['packageunsafearchive'] = 'The ZIP archive contains an unsafe path, a nested archive, too many files, or too much extracted data.';
$string['packageunsafefile'] = 'The package contains an unsafe file path: {$a}';
$string['pluginname'] = 'InteractEmbed — Interactive content block';
$string['policiesheading'] = 'Site policies';
$string['policiesheading_desc'] = 'These settings can restrict or enforce sensitive features across every InteractEmbed block, including existing blocks.';
$string['privacy:metadata'] = 'The InteractEmbed block does not store personal data.';
$string['privacy:metadata:externalcontent'] = 'When configured external content is displayed, the visitor’s browser connects directly to that provider. InteractEmbed does not add Moodle profile or grade data to the request.';
$string['privacy:metadata:externalcontent:ipaddress'] = 'The external provider receives the IP address used for the browser connection.';
$string['privacy:metadata:externalcontent:useragent'] = 'The external provider receives the browser user-agent information.';
$string['ratio16x9'] = 'Widescreen (16:9)';
$string['ratio1x1'] = 'Square (1:1)';
$string['ratio3x4'] = 'Portrait (3:4)';
$string['ratio4x3'] = 'Classic (4:3)';
$string['ratio9x16'] = 'Vertical / mobile (9:16)';
$string['ratiocustom'] = 'Custom height';
$string['remoteconsentnotice'] = 'The administrator requires visitor interaction before all remote content is loaded.';
$string['sandboxprofile'] = 'Content security profile';
$string['sandboxprofile_help'] = 'Strict is recommended for unknown content. Standard supports most trusted presentations. Trusted also permits same-origin access, so active package code can interact with this Moodle site and its browser session. Reserve it for audited HTML5 files you control.';
$string['sandboxstandard'] = 'Standard';
$string['sandboxstrict'] = 'Strict';
$string['sandboxtrusted'] = 'Trusted package';
$string['showembedcode'] = 'Show embed code';
$string['showembedcode_help'] = 'Displays a copyable embed code to users who can edit this block. Students and visitors never see this option.';
$string['showfullscreen'] = 'Show full-screen button';
$string['sourcenotallowed'] = 'This content source is disabled by the InteractEmbed site policy.';
$string['sourcepolicy'] = 'Allowed content sources';
$string['sourcepolicy_desc'] = 'Choose which source types may be configured and displayed. Restricting a type also makes existing blocks using that type unavailable until the policy is changed.';
$string['sourcepolicyboth'] = 'Web addresses, iframes and HTML5 packages';
$string['sourcepolicypackageonly'] = 'HTML5 packages only';
$string['sourcepolicyurlonly'] = 'Web addresses and iframes only';
$string['sourcetype'] = 'Content source';
$string['sourcetypeguide'] = 'Included interactive guide';
$string['sourcetypepackage'] = 'HTML5 package';
$string['sourcetypeurl'] = 'Web address or iframe';
$string['sourceurl'] = 'Web address or iframe code';
$string['sourceurl_help'] = 'Paste an HTTP/HTTPS address or iframe code. Recognised services are converted to their official embed address.';
