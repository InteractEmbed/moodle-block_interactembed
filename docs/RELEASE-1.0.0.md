# InteractEmbed 1.0.0

InteractEmbed 1.0.0 is the first stable release of Michel Cardinal's interactive-content block for Moodle.

## Compatibility

- Moodle 4.5, 5.0, 5.1 and 5.2.
- PHP versions supported by the selected Moodle release; the plugin source remains PHP 8.1 compatible.
- MariaDB and PostgreSQL are covered by the public continuous-integration matrix.

## Highlights

- Responsive external embeds and validated self-contained HTML5 packages in Moodle block regions.
- Smart conversion for recognised sharing URLs and an administrator-controlled allow-list for other domains.
- Inline, compact-card and privacy-aware click-to-load modes.
- Accessible full-screen fallback, keyboard focus management and multiple independent players per page.
- A complete local interactive guide in English, French and Spanish.
- Site-wide defaults and enforceable source, privacy and package-limit policies.
- Optional same-Moodle embed code without bypassing Moodle permissions.
- Course backup and restore of block configuration, cover images and HTML5 content.
- Optional InteractEmbed Creator API v1 publication selection. Publications are copied into an immutable, autonomous block-owned snapshot.

## Security and privacy

HTML5 packages are path-validated and activated atomically. Active package documents use an iframe sandbox and Content Security Policy. Relative assets are delivered through short-lived signed, read-only paths that do not require Moodle session cookies. Cross-window resize messages are bound to the expected origin, iframe source and a per-render nonce.

InteractEmbed stores no learner profile or tracking table. Remote providers receive ordinary browser connection information only when their content is loaded. Administrators may require a visitor action before any external connection occurs.

## Languages

The distributable archive contains Moodle's required English source strings. Complete French and Spanish translations are prepared for AMOS, while the included interactive guide is natively trilingual.

## Upgrade

Replace the existing `blocks/interactembed` directory with the 1.0.0 archive contents and complete Moodle's standard plugin upgrade. Existing block settings, packages and cover images are preserved.

SHA-256 for the published archive is provided with the GitHub release and in the release workspace.
