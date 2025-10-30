<?php

return [
    'access' => ['admin'],
    'root' => '',
    'extensions' => [
        'md'    => 'text/markdown',
        'js'    => 'text/javascript',
        'yml'   => 'application/x-yaml'
    ],
    'mimes' => [
        'directory' => [
            'component'     => 'k-editor-preview-folder',
            'icon'          => 'folder',
            'permission'    => [
                'download' => false
            ]
        ],
        'text/*' => [
            'component' => 'k-editor-preview-text',
            'icon'      => 'file'
        ],
        'font/*' => [
            'component' => 'k-editor-preview-font',
            'icon'      => 'file-font',
        ],
        'application/json' => [
            'component' => 'k-editor-preview-text',
            'icon'      => 'file-code',
        ],
        'application/pdf' => [
            'component' => 'k-editor-preview-object',
            'icon'      => 'file-pdf',
        ],
        "application/x-empty" => [
            'component' => 'k-editor-preview-text',
        ],
        'application/x-yaml' => [
            'component' => 'k-editor-preview-text',
            'language'  => 'yaml'
        ],
        'image/svg+xml' => [
            'components' => [
                'k-editor-preview-default' => 'plain.editor.preview',
                'k-editor-preview-text' => 'plain.editor.editor'
            ],
            'language'  => 'xml'
        ],
        'text/css' => [
            'icon'      => 'file-css'
        ],
        'text/js'    => [
            'icon'      => 'file-javascript'
        ],
        'text/html' => [
            'components' => [
                'k-editor-preview-object' => 'plain.editor.preview',
                'k-editor-preview-text' => 'plain.editor.editor'
            ],
            'icon'      => 'file-html'
        ],
        'text/markdown' => [
            'components' => [
                'k-editor-preview-text' => 'plain.editor.editor',
                'k-editor-preview-markdown' => 'plain.editor.preview'
            ],
            'options'   => [
                'wordWrap' => true
            ],
            'icon'      => 'markdown',
            'language'  => 'markdown'
        ],
        'text/ts'    => [
            'icon'      => 'file-javascript'
        ],
        'text/x-shellscript' => [
            'icon'      => 'file-code',
            'language'  => 'shell'
        ],
        'text/vue' => [
            'icon'      => 'file-vue'
        ],
        'text/x-php' => [
            'icon'      => 'file-php',
            'language'  => 'php'
        ],
        'text/yml' => [
            'icon'      => 'file-yaml'
            //Todo: Blueprint editor
        ]
    ]
];
