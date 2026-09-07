# InteractEmbed

InteractEmbed is an original Moodle block by Michel Cardinal for displaying responsive interactive web content and HTML5 packages outside the activity-module model.

## Main features

- Moodle block instances on course pages, the site home page, dashboards and other block regions.
- Safe extraction of the `src` from pasted iframe markup.
- Smart URL conversion for YouTube, Google Workspace, Canva and Genially, plus direct support for Padlet and Wayground URLs.
- Responsive 16:9, 4:3, 1:1, 3:4, 9:16 and custom-height displays.
- Inline and compact-card display modes for both wide and narrow block regions.
- Optional click-to-load mode that avoids contacting external providers before visitor interaction.
- Standard Site administration page with defaults for new blocks and enforceable site policies.
- HTML5 projects uploaded as unpacked files or one validated ZIP archive.
- A complete trilingual interactive guide available as an optional built-in source.
- Optional accessible introductory text and a custom cover image for card or click-to-load displays.
- Sandboxed package and remote-content rendering.
- Full-screen display with an accessible in-page fallback, Escape-key exit and focus restoration.
- A same-Moodle embed page and copyable iframe code, without bypassing Moodle access controls.
- English source strings in the distributable plugin, with complete French and Spanish translations prepared for Moodle AMOS language packs.

InteractEmbed intentionally does not implement grades, activity completion or course-module tracking.

The included guide is selected from the same **Content source** menu as a web address or HTML5 package. It is never added automatically and makes no external connection.

The guide itself remains fully trilingual. Moodle requires Marketplace plugin archives to ship only `lang/en`; the prepared French and Spanish interface translations are retained separately in the release workspace for submission to AMOS after plugin approval and are excluded from the distribution ZIP.

## Requirements

- Moodle 4.5 through 5.2.
- PHP version supported by the selected Moodle release. The source remains PHP 8.1 compatible.

## Installation

Copy this directory to `blocks/interactembed` under the Moodle public application root, then visit Site administration > Notifications or run the Moodle CLI upgrade.

On Moodle 5.1 and later installations using the new public directory layout, the destination is normally `public/blocks/interactembed`.

## User guides

- [English](docs/GUIDE.en.md)
- [Français](docs/GUIDE.fr.md)
- [Español](docs/GUIDE.es.md)

## Site administration

Administrators can configure InteractEmbed under **Site administration > Plugins > Blocks > InteractEmbed**. The page contains defaults for new blocks, site-wide source and privacy policies, generic-domain controls, and HTML5 package limits. Defaults do not rewrite existing blocks. Site policies are enforced when content is saved and rendered.

## Security

Only trusted roles should receive `block/interactembed:addinstance`, because editors choose active third-party content. Recognised providers work automatically; administrators can allow specific additional domains or explicitly permit all valid generic URLs. HTML5 packages are isolated with iframe sandboxing and an HTTP Content Security Policy. Relative package assets use short-lived signed paths scoped to one block context and package revision. Those read-only, cookie-free responses permit cross-origin reads without credentials so applications in an opaque sandbox origin can load their own JSON and assets without exposing the Moodle session. HTML documents also receive `nosniff` and no-referrer response policies. The Standard profile never grants same-origin access. The optional Trusted profile requires the dedicated `block/interactembed:trustpackage` capability, marked `RISK_XSS`, and is intended only for fully trusted packages.

External providers receive the visitor's normal browser connection details and may set cookies according to their own policies. InteractEmbed does not send Moodle names, emails or grades to those providers.

## Licence

GNU GPL v3 or later.

## Development and support

The source repository is prepared for Moodle Plugin CI across Moodle 4.5, 5.0,
5.1 and 5.2 with MariaDB and PostgreSQL coverage.

- [Source repository](https://github.com/InteractEmbed/moodle-block_interactembed)
- [Issue tracker](https://github.com/InteractEmbed/moodle-block_interactembed/issues)
- [Private vulnerability reporting](https://github.com/InteractEmbed/moodle-block_interactembed/security/advisories/new)

See `CONTRIBUTING.md` for contribution expectations and `SECURITY.md` for the
security-reporting policy.
