<div class="row">
<?php

App()->clientScript->registerScriptFile('scripts/admin/templates.js');
echo \TbHtml::tag('div', [
    'class' => 'col-md-3',
    'style' => 'position: fixed; padding-left: 0px; bottom: 0px; top: 50px;'
], $this->widget(\FileManagerWidget::class, [
    'id' => 'fileBrowser',
    'key' => $template['name'],
    'context' => 'template',
    'htmlOptions' => [
        'style' => 'position: absolute; top: 0px; bottom: 0px; left: 0px; right: 0px;',
    ],
    'clientOptions' => [
        'defaultView' => 'list',
        'resizable' => false,
        'ui' => ['toolbar', 'path', 'stat'],
        'uiOptions' => [
            'cwd' => [
                'listView' => [
                    'columns' => ['date', 'size'],
                ],
                'oldSchool' => true
            ]
        ],
        'handlers' => [
            // Set height after init. Needed to correctly calculate height from fixed parent.
            'init' => new CJavaScriptExpression('function() { $("#fileBrowser").css("height", "").trigger("resize"); }')
        ]
    ],
    'disabledCommands' => [
        'archive',
        'download',
        'quicklook',
        'open',
        'edit'
    ],
    // Open the file in editor.
    'callback' => new \CJavaScriptExpression('loadFile')
], true));

echo \CHtml::openTag('div', ['class' => 'col-md-offset-3 col-md-9', 'style' => 'padding: 0px;']);

    /**********************************************************************************************************************/
    /* BUTTON TOOLBAR                                                                                                     */
    /**********************************************************************************************************************/

    $right = [];
    $left = [];
    foreach([
                [640, 480],
                [800, 600],
                [1024, 768],
                ["100%", 768]
            ] as list($width, $height)) {
         $left[] = [
            'label' => "$width x $height",
            'url' => '#',
            // Use unobtrusive javascript for resizing.
            'data-height' => $height,
            'data-width' => $width,
            'data-target' => '#preview'
        ];
    }
    $right[] = [
        'label' => gT('Template') . ': ' . $template['name'],
        'items' => array_map(function($directory) use ($screen) {
            return [
                'url' => App()->createUrl('templates/index', ['name' => basename($directory), 'screen' => $screen['id']]),
                'label' => basename($directory)
            ];

        }, \ls\models\Template::getTemplateList()),

    ];

    $right[] = [
        'label' => gT('Screen') . ': ' . $screen['name'],
        'items' => array_map(function($screen) use ($template) {
            return [
                'url' => App()->createUrl('templates/index', ['screen' => $screen['id'], 'name' => $template['name']]),
                'label' => $screen['name']
            ];

        }, $screens),
    ];
    $right[] = [
        'title' => gT('Import template'),
        'url' => ['templates/import'],
        'icon' => 'import'
    ];
    $right[] = [
        'title' => gT('Export template'),
        'url' => ['templates/export', 'name' => $template['name']],
        'icon' => 'export'
    ];
    $right[] = [
        'title' => gT('Copy template'),
        'url' => ['templates/copy', 'name' => $template['name']],
        'icon' => 'duplicate'
    ];
    $right[] = [
        'label' => gT('Save changes'),
        'color' => \TbHtml::BUTTON_COLOR_PRIMARY,
        'icon' => 'save',
        'url' => '#',
//        'linkOptions' => [
            'id' => 'save',
//        ]
    ];

    class_exists(TbNavbar::class);
    $this->widget(\TbNavBar::class, [
        'brandLabel' => false,
        'display' => TbHtml::NAVBAR_DISPLAY_STATICTOP,

        'fluid' => true,
        'items' => [
            [
                'class' => TbNav::class,
                'items' => $left
            ],
            [
                'class' => TbNav::class,
                'htmlOptions' => [
                    'class' => 'navbar-right'
                ],
                'items' => $right
            ]
        ]
    ]);
    //    echo \TbHtml::buttonGroup($buttons, [
    //        'class' => 'pull-right',
    //        'style' => 'margin-top: 15px; margin-bottom: 15px;'
    //    ]);
    /**********************************************************************************************************************/
    /* EDITOR  WINDOW                                                                                                     */
    /**********************************************************************************************************************/
    echo \CHtml::textArea('editor', '', [
        'style' => 'height: 350px; width: 100%',
        'class' => 'ace',
        'id' => 'editor'
    ]);


    /**********************************************************************************************************************/
    /* PREVIEW WINDOW                                                                                                     */
    /**********************************************************************************************************************/
    echo \CHtml::tag('iframe', [
        'id' => 'preview',
        'src' => App()->createUrl('templates/preview', [
            'name' => $template['name'],
            'page' => $screen['id'],
        ]),
        'class' => 'col-md-12',
        'style' => 'height: 768px; display: block; margin: auto; width: 1024px; float: none;'
    ], 'No iframe support.');
echo \CHtml::closeTag('div');



App()->clientScript->registerScript('refresher', "$(document).on('saveFile', function(e, textarea) { $('#preview').attr('src', $('#preview').attr('src')); });");
?>
</div>

<script>


</script>