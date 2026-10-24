<?php

use Kirby\Cms\App;
use Kirby\Data\Data;
use Kirby\Filesystem\F;

F::loadClasses([
    'Plain\Editor\Item' => __DIR__ . '/classes/Item.php',
    'Plain\Editor\Tree' => __DIR__ . '/classes/Tree.php',
]);

App::plugin('plain/editor', [
    'api' => require __DIR__ . '/config/api.php',
    'areas' => require __DIR__ . '/config/areas.php',
    'options' => require __DIR__ . '/config/options.php',
    'translations' => [
        'de' => Data::read(__DIR__ . '/i18n/de.json'),
        'en' => Data::read(__DIR__ . '/i18n/en.json'),
    ],
]);
