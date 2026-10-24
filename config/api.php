<?php
use Kirby\Data\Json;
use Plain\Editor\Tree;
use Plain\Editor\Item;

return [
    'routes' => function ($kirby) {
        return [
            [
                'pattern' => 'plain/editor/tree',
                'action' => function() {
                    return Tree::factory(...get())->toArray();
                }
            ],
            [
                'pattern'   => 'plain/editor/save',
                'method'    => 'POST',
                'action'    => function() {

                    try {

                        $fileObj = Item::factory([
                            'path' => get('path', ''),
                            'onlydir' => false
                        ]);

                        return $fileObj->save(
                            get('content'),
                            get('modified'),
                            get('force')
                        );

                    } catch (\Throwable $th) {
                        return [
                            'status' => 'error',
                            'message' => $th->getMessage()
                        ];
                    };
                }
            ],
            [
                'pattern' => 'plain/editor/get/(:any)',
                'action' => function($method)  {
                    
                    $fileObj = Item::factory([
                        'path' => get('path', ''), 
                        'current' => get('current'), 
                        'onlydir' => get('onlydir', false)
                    ]);
                    $params = JSON::decode(get('params') ?? '') ?? [];
                    $result = $fileObj->$method(...$params);

                    //Return new item array
                    if ($result instanceof Item) {
                        return $result->toArray();
                    }

                    //Operation successfull
                    if (is_bool($result)) {
                        return ($result) ? $fileObj->toArray() : false;
                    }

                    //Return results
                    if (is_array($result)) {
                        return $result;
                    }

                    return ['result' => $result];

                }
            ]
        ];
    }    
]

?>