<?php
//vdd(ls\models\Template::getTemplateList());
/* @var Controller $this*/
$menu = [[ // Left side
    [
        'title' => gT('Import template'),
        'url' => ['templates/import'],
        'icon' => 'import'
    ],
    [
        'title' => gT('Export template'),
        'url' => ['templates/export', 'name' => $template['name']],
        'icon' => 'export'
    ],
    [
        'title' => gT('Copy template'),
        'url' => ['templates/copy', 'name' => $template['name']],
        'icon' => 'duplicate'
    ],
], [ // Right side
    [
        'label' => gT('ls\models\Template') . ': ' . $template['name'],
//        'url' => ['templates/index'],
        'items' => array_map(function($template) {
            return [
                'url' => App()->createUrl('templates/index', ['name' => $template]),
                'label' => $template
            ];

        }, \ls\models\Template::getTemplateList()),

    ],
    [
        'label' => gT('Screen') . ': ' . $screen['name'],
//        'url' => ['templates/index'],
        'items' => array_map(function($screen) {
            return [
                'url' => App()->createUrl('templates/index', ['screen' => $screen['id']]),
                'label' => $screen['name']
            ];

        }, $screens),

    ],
    ]];
    
return $menu;