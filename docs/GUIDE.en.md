# InteractEmbed guide

## Add web content

1. Turn editing on for a page that supports blocks.
2. Add **InteractEmbed — Interactive Content Block**.
3. Choose **Web address or iframe**.
4. Paste an HTTP/HTTPS address or the iframe code supplied by the service.
5. Select the display format, security profile and viewing options, then save.

InteractEmbed recognises YouTube, Google Docs/Slides/Forms/Sheets, Canva, Genially, Padlet and Wayground. The remote site must permit iframe display.

Select **Require a click before loading the content** when the visitor should decide before a connection is made to the external provider. Select **Compact card** for a lightweight launch panel in narrow block regions; the full player expands after the visitor clicks.

Administrators can allow additional generic domains in the plugin settings. The permissive option for all generic URLs should be limited to sites where every block editor is trusted.

## Present the content

Use **Introduction or accessible description** for instructions, a learning objective or a text alternative before the player. An optional **Cover image** can personalise compact-card and click-to-load displays. Its description is required so the meaningful information remains accessible.

## Use the included guide

Select **Included interactive guide** as the source to display the HTML5 manual bundled with InteractEmbed. The guide initially follows the Moodle language for English, French or Spanish and also provides its own language selector. It is never added automatically and makes no external connection.

## Add an HTML5 package

Choose **HTML5 package**, then upload either one ZIP archive or the unpacked files with their folders. Never keep the ZIP and its unpacked files in Moodle’s file picker at the same time. The simplest method is to upload only the ZIP without using **Unzip**. The start file is normally `index.html`; enter another path when required.

The **Standard** profile isolates the package from Moodle. The administrator-only **Trusted package** profile must be reserved for files you control.

## Reuse the player within the same Moodle

First enable **Show embed code** in the block settings. Expand **Embed code** below the block, copy the code and paste it into a trusted HTML-capable area on the same Moodle. The player keeps the original block's session and access rules. Its accompanying script automatically adjusts the height.

The code never makes a private block public and never bypasses site login or course access.

## Site administration

Open **Site administration > Plugins > Blocks > InteractEmbed** to configure defaults for new blocks, allowed source types, embed-code authoring, mandatory click-to-load behaviour, additional domains and ZIP safety limits.

Defaults initialise only new blocks and remain editable. Site policies are enforced for all blocks. Disabling a source type therefore also makes existing blocks of that type unavailable until the policy is restored. Disabling embed-code authoring hides the copy tool but does not deliberately break iframe codes that were already placed elsewhere in the same Moodle.

## Troubleshoot common problems

- **The package cannot be saved:** keep only the ZIP or only the unpacked files.
- **The start file is missing:** check its case-sensitive full path, for example `presentation/index.html`.
- **A remote source is blank or refused:** check sharing permissions and whether the provider permits iframe display.
- **Content is unavailable:** review the InteractEmbed site policies, context permissions and guest access when the block is on the site home page.
