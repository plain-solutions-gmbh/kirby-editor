<?php

use Kirby\Toolkit\A;

return [
    'editor' => function ($kirby) {

      if (in_array(
        $kirby->user()?->role()->name(),
        A::wrap($kirby->option('plain.editor.access'))
      )) {

        return [
          'label' => 'Editor',
          'icon' => 'folder-structure',
          'menu' => true,
          'link' => 'editor',
          'views' => [
            [            
              'pattern' => 'editor',
              'action'  => fn() => ['component' => 'k-editor-view']
            ]
          ]
        ];
      }

      return [];
    }
];

?>