# Change log

## 0.9.0-beta34 — 2026-09-07

- Validated uploaded HTML5 packages in draft storage before replacing the active Files API area, so an unsafe replacement cannot remove the previous working package on PostgreSQL.
- Retained the post-move package validation as a second line of defence.
- Reordered the English language strings to satisfy Moodle CodeSniffer with zero warnings.
- Removed the legacy Bootstrap `.sr-only` and `.ml-2` aliases while retaining their Moodle 4.5-compatible `.visually-hidden` and `.ms-2` equivalents.

## 0.9.0-beta33 — 2026-09-02

- Hardened the same-Moodle resize channel with a per-render nonce, exact message schema, expected origin and iframe-source binding.
- Added explicit CORS for signed, cookie-free, read-only package resources so sandboxed HTML5 applications can fetch relative JSON and assets without granting same-origin access.
- Applied `nosniff`, a no-referrer policy and an HTTP CSP sandbox to HTML package documents, including direct signed openings.
- Removed `allow-popups-to-escape-sandbox`; the Standard package profile remains isolated, while `allow-same-origin` stays restricted to the explicit Trusted profile and its dedicated `RISK_XSS` capability.

## 0.9.0-beta32 — 2026-09-02

- Added concise cover-image format, dimension, weight and crop guidance directly below the file picker.
- Kept the guidance aligned with the InteractEmbed Activity in English, French and Spanish AMOS packs.

## 0.9.0-beta31 — 2026-09-01

- Contained keyboard focus inside the full-screen fallback so Tab and Shift+Tab cannot reach controls hidden behind the player.
- Exposed the fallback as a labelled modal dialog to assistive technology and removed those temporary semantics immediately on exit.
- Extended the permanent headless regression test to cover forward and reverse focus wrapping, Escape exit, focus restoration and multiple independent players.

## 0.9.0-beta30 — 2026-09-01

- Completed the French and Spanish interface translations for the optional InteractEmbed Library package origin.
- Added permanent regression tests proving that a publication snapshot becomes an independent block-owned package and that a forged canonical hash cannot replace the working package.
- Confirmed 51 tests and 258 assertions separately on Moodle 4.5.13, Moodle 5.0.9, IOMAD/Moodle 5.1.6 and Moodle 5.2.1+; Moodle CodeSniffer reports no error or warning.

## 0.9.0-beta29 — 2026-09-01

- Fixed the add/edit form path so an independent package copied from the InteractEmbed Library is retained after activation instead of being removed by the non-package cleanup branch.
- Confirmed the regression through a real IOMAD 4.5 Creator publication and block instance.

## 0.9.0-beta28 — 2026-09-01

- Added an optional InteractEmbed Library origin inside the existing HTML5 package source.
- Copies Creator publications through a temporary block-owned area, revalidates paths, limits, entry point and canonical hash, then activates an independent local copy.
- Preserved rendering, duplication and backup/restore when the Creator is absent.
- Passed the existing Moodle 4.5 suite with 49 tests and 238 assertions; Moodle CodeSniffer remains clean.

## 0.9.0-beta27 — 2026-09-01

- Hardened HTML5 package entry points and every unpacked path against absolute paths, traversal, ambiguous dot segments, repeated separators, null bytes and non-HTML start files.
- Added a dedicated `block/interactembed:trustpackage` capability for the security-sensitive Trusted package profile.
- Made package revisions strictly increasing even when several saves occur within the same second.
- Prevented one player from changing the full-screen state or stealing focus from another InteractEmbed block on the same page.
- Added cross-version PHPUnit coverage metadata compatible with PHPUnit 9 and 11, bringing the suite to 49 tests and 238 assertions.

## 0.9.0-beta26 — 2026-09-01

- Rejected configured package start files that are not `.html` or `.htm` during draft validation, before an unusable block can be saved.
- Wrapped package, cover and configuration replacement in a delegated Moodle database transaction so a rejected archive leaves the previous working revision intact.
- Added regression tests for non-HTML entry points and failed-package rollback, bringing the Moodle 4.5 suite to 45 tests and 208 assertions.

## 0.9.0-beta25 — 2026-09-01

- Moved the add-time configuration-form declaration to the Moodle `block_edit_form` subclass, where Moodle 4.5 through 5.2 actually reads it.
- Ensured that adding InteractEmbed opens its required configuration form before an incomplete block instance can be saved.
- Added a permanent PHPUnit regression test and corrected the nine prepared Behat scenarios for Moodle's dynamic add-block form and public-home settings.

## 0.9.0-beta24 — 2026-09-01

- Removed deprecated PHPUnit doc-comment metadata from the test suite while retaining Moodle 4.5 compatibility.
- Replaced the cross-version data provider with seven explicit smart-provider tests, preserving all 42 tests and 197 assertions.
- Verified the exact suite without PHPUnit deprecation notices on Moodle 5.2 and with PHPUnit 9 on Moodle/Iomad 4.5.

## 0.9.0-beta23 — 2026-09-01

- Aligned the low-height desktop minimum height with the responsive maximum-height calculation after the beta 22 live measurement showed the older minimum still taking precedence.
- Added a regression assertion requiring the same exact height formula in both desktop constraints.

## 0.9.0-beta22 — 2026-09-01

- Removed the final document-level scrollbar found during the live beta 21 review by making the player's maximum height use the exact responsive outer padding.
- Kept the slide viewport itself fully fitted on both the welcome screen and the denser workflow screens.
- Added a regression assertion for the shared padding calculation.

## 0.9.0-beta21 — 2026-09-01

- Corrected the guide's responsive breakpoint after a live Moodle 5.0.8 review showed that a 741-pixel course block still switched to the tall mobile layout inside a 16:9 iframe.
- Kept the fitted two-column presentation down to 641 pixels so ordinary Moodle block widths remain visible at a glance without document-level iframe scrolling.
- Retained the readable scrolling layout at 640 pixels and below for phones and genuinely narrow containers.

## 0.9.0-beta20 — 2026-09-01

- Rebuilt the same-Moodle resize AMD module from its source with Moodle's standard toolchain.
- Added the missing source map and restored strict source/build parity required by Moodle Plugin CI.
- Added a pre-flight guard requiring both compiled JavaScript and source maps for every AMD source module.

## 0.9.0-beta19 — 2026-09-01

- Improved the bundled guide's height-aware fitting after real Moodle review: short desktop slides now grow modestly to use available space, while dense slides still shrink enough to avoid internal scrolling.
- Increased the minimum size of answer explanations and guided-step text for better readability in 16:9 and full-screen displays.
- Kept mobile layouts scrollable so narrow screens never trade readability for forced fitting.

## 0.9.0-beta18 — 2026-09-01

- Expanded the bundled trilingual microcourse from 13 to 17 screens with focused guidance for player appearance, loading controls, site administration and troubleshooting.
- Documented the mutually exclusive ZIP and unpacked-file upload methods directly in the course and all three written guides.
- Replaced fixed slide indexes in the course score with automatic discovery so future guide additions cannot silently break scoring.
- Added height-aware desktop fitting so each guide screen remains fully visible between its fixed header and navigation without internal scrolling.
- Preserved the guide's isolated sandbox and its no-external-connection design.

## 0.9.0-beta17 — 2026-08-31

- Split access checks, player-data construction, package validation and form validation into focused helpers without changing product behaviour.
- Added PHP Mess Detector as an informational CI diagnostic and expanded the future GitHub matrix to cover each advertised Moodle branch at its minimum and maximum supported PHP versions.
- Kept all Moodle-mandated callback signatures intact while reducing maintainability complexity in security-sensitive code.

## 0.9.0-beta16 — 2026-08-31

- Replaced the non-existent core `i/fullscreen` icon with Moodle's supported `e/fullscreen` icon.
- Added permanent rendering assertions so the full-screen control cannot silently regress to a missing core icon.
- Verified click-to-load, the bundled HTML5 guide, full-screen fallback and exit in Safari on an iPhone 17 simulator running iOS 26.5.

## 0.9.0-beta15 — 2026-08-31

- Fixed cover-image delivery so Moodle resolves `cover` requests from the cover file area instead of the HTML5 package area.
- Added a regression-tested file-area resolver for package and cover files.
- Removed internal presentation values that were accidentally emitted as non-standard attributes in the optional copyable iframe code.
- Restored description and cover metadata in the rendered player while keeping those values out of copied iframe markup.
- Hardened archive validation against ambiguous dot and repeated-separator paths, and added MIME-sniffing protection to standard file delivery.

## 0.9.0-beta14 — 2026-08-31

- Expanded Behat acceptance coverage from six to nine scenarios.
- Added explicit scenarios for two independent blocks on one page, pasted iframe conversion and the optional same-Moodle embed-code tool.
- Removed two Moodle ESLint promise warnings from the accessible full-screen label update.
- Kept all product behaviour unchanged while strengthening the evidence required before a stable-release decision.

## 0.9.0-beta13 — 2026-08-31

- Expanded browser acceptance coverage from three to six scenarios: creation, editing, HTML5 ZIP upload, anonymous site-home display, administrative defaults and enforced site policy.
- Added a minimal self-contained HTML5 test package to exercise upload, extraction and iframe delivery together.
- Corrected two previously undefined Gherkin selector steps and verified that all 67 steps resolve in a Moodle 5.2 Behat dry run.
- Aligned the prepared GitHub Actions workflow with Moodle HQ's conditional MariaDB/PostgreSQL services and managed Chrome/Selenium execution.

## 0.9.0-beta12 — 2026-08-31

- Prevented the built-in guide language selector from overflowing the player header at common 16:9 viewport sizes.
- Kept the candidate release in beta while the final pre-1.0 acceptance review continues.

## 0.9.0-beta11 — 2026-08-31

- Added an optional accessible introduction displayed before the interactive player.
- Added an optional 5 MB cover image with required alternative text for compact-card and click-to-load displays.
- Added the complete trilingual interactive guide as a built-in, optional content source.
- Added a distinctive InteractEmbed icon and reused the visual identity in the included guide.
- Extended file serving, duplication, backup and restore to include cover images.

## 0.9.0-beta10 — 2026-08-31

- Added an in-page full-screen fallback when the browser refuses the native Fullscreen API or blocks pop-up windows.
- Added an accessible pressed state, translated exit label, Escape-key exit and focus restoration for the full-screen control.
- Added permanent tests for administrative defaults and course backup/restore with HTML5 package files.
- Extended template accessibility assertions and completed the browser recipe at 320, 375, 768 and 1024 pixels in English, French and Spanish.
- Normalised the English plugin title according to Moodle language-string guidance.

## 0.9.0-beta9 — 2026-08-30

- Exposed the standard block settings page under Site administration > Plugins > Blocks > InteractEmbed.
- Added administrator defaults for new blocks without changing existing block configuration.
- Added site policies for allowed source types, embed-code authoring and mandatory visitor interaction before remote content loads.
- Enforced sensitive policies during saving and rendering, not only by hiding form fields.
- Organised global settings into Defaults, Site policies and HTML5 package limits.

## 0.9.0-beta8 — 2026-08-30

- Added an administrator-defined allow-list for generic embed domains while keeping recognised providers automatic.
- Changed new installations to reject unrecognised generic URLs by default; upgraded sites retain their previous setting.
- Added an optional click-to-load privacy and performance mode that creates no external iframe request before consent.
- Added a compact card display mode for narrow block regions; it expands the full player after a click.
- Added server-side display-mode normalisation, domain-boundary and privacy-sensitive template tests, and trilingual interface text.

## 0.9.0-beta7 — 2026-08-30

- Added a per-block “Show embed code” setting, disabled by default.
- Kept the embed code restricted to users who can edit the block, even when the setting is enabled.
- Added the new setting and help text in English, French and Spanish.

## 0.9.0-beta6 — 2026-08-30

- Replaced a non-existent legacy-style exception class with PHP's standard `InvalidArgumentException`, so invalid or hostile URLs produce normal form validation on Moodle 4.5 through 5.2.
- Added a PHPUnit privacy-provider test for external iframe metadata.
- Reached zero errors and zero warnings with Moodle CodeSniffer 3.7.0.
- Passed 23 PHPUnit tests and 58 assertions on an isolated Iomad/Moodle 4.5.13 environment.

## 0.9.0-beta5 — 2026-08-30

- Fixed package signing on standard Moodle installations that do not define the obsolete optional `passwordsaltmain` setting.
- Switched signed package paths to Moodle's standard private site identifier API, compatible with Moodle 4.5 through 5.2.
- Added a cookie-free signed package endpoint so relative CSS, JavaScript, images and media remain available inside the isolated sandbox.

## 0.9.0-beta4 — 2026-08-30

- Added short-lived signed package paths so relative CSS, JavaScript, images and media load inside the isolated Standard sandbox without exposing the Moodle session.
- Kept ordinary direct package URLs protected by the original Moodle context rules.
- Updated the same-Moodle Embed resizer asset version.

## 0.9.0-beta3 — 2026-08-30

- Enforced the administrator-only Trusted package profile on the server, not only in the form.
- Limited anonymous system-context players and package files to blocks attached to the site home page.
- Updated the same-Moodle Embed resizer asset version.

## 0.9.0-beta2 — 2026-08-30

- Added a per-instance revision to HTML5 package URLs whenever an editor saves the block.
- Changed package file delivery to require browser revalidation, preventing stale HTML, CSS, JavaScript and sandbox policies after an update.
- Updated the same-Moodle Embed resizer asset version.

## 0.9.0-beta1 — 2026-08-29

- Initial development version.
- Added responsive remote-content and HTML5 package players.
- Added safe iframe URL extraction and initial provider converters.
- Added ZIP validation, same-site embed player, full-screen mode and EN/FR/ES language packs.
- Added an upgrade step that removes the early development build’s unintended authenticated-user Dashboard permission.
