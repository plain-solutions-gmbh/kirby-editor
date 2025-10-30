# Kirby Editor

<img src="./.github/screenshot.png" title="kirby editor" width="450" />

## Overview

Kirby Editor is a fully functional built-in file explorer for Kirby CMS.

**Features**

⭐️ Easy access to the editor via the Kirby menu bar.<br/>
⭐️ Full access to your project folder. You can also set a custom start folder.<br/>
⭐️ Preview for several file types (Markdown, PDF, HTML, Media & Fonts).<br/>
⭐️ A filetree field included to select files and folders from your project folder.<br/>
⭐️ Useful features for organizing files and folders (create, delete, move, rename, download).<br/>
⭐️ Edit text files just like you do in VSCode.<br/>
⭐️ Prevent overwriting if the file on the server is newer.<br/>
⭐️ Smart caching feature for fast file switching.<br/>

**Upcoming features**

🤩 Graphical blueprint editor extension!<br/>
⭐️ Extended permissions<br/>
⭐️ Popup on right click

## Important security notice

Before using this plugin, please note the following:<br/>
Changes to files or folders made with this tool may affect the functionality of Kirby.

As a first security measure, access to the editor is restricted to administrators by default. It is recommended to remove the `admin` [role](https://getkirby.com/docs/guide/users/roles) from users without technical knowledge.
Using the `plain.editor.access` option, you can define which user roles are allowed to access the editor.

⚠️ **The developer of this plugin accepts no liability for any damage or data loss.**


## Installation

**Manually**

[Download](https://github.com/plain-solutions-gmbh/kirby-editor) and copy the plugin into your plugin folder: `/site/plugins/`

**With Composer**

`composer require plain/kirby-editor`

## Options

`plain.editor.access`<br/>
A list of roles that have access to the editor. (Default: `admin`)<br/>


`plain.editor.root`<br/>
A relative path to the start folder. (Default: kirby root)
`

`plain.editor.extensions`<br/>
Redefines mime type by its file extension


`plain.editor.mimes.(mime/type)`<br/>
An array of options for the given mime type. Wildcards allowed `text/*`
| Key        | Description                                                                                                           | Default                  |
| ---------- | --------------------------------------------------------------------------------------------------------------------- | ------------------------ |
| icon       | Icon that appears in file tree or folder preview                                                                      | \`file\` or \`folder\`   |
| component  | A preview component to open the file                                                                                  | k-editor-preview-default |
| components | Array of selectable previews.\`\['preview-component' => 'name-or-translation-key']\`                                  | (none)                   |
| language   | Sets the \[language]\(https\://github.com/microsoft/monaco-editor/tree/main/src/basic-languages) for the text editor. | `text`                   |
| options    | Props that are given to the preview component.                                                                         |                          |


## License

This plugin is free to use and is published under the MIT license. If you use this plugin for commercial purposes or wish to show your appreciation, [support me with a donation](https://www.paypal.com/donate/?hosted_button_id=YBXZWG7E6GMZQ).