<div class="row">
<?php

App()->clientScript->registerScriptFile('scripts/admin/templates.js');
echo \CHtml::tag('div', [
    'class' => 'col-md-6',
    'style' => 'height: 400px;'
], $this->widget(\FileManagerWidget::class, [
    'key' => $template['name'],
    'context' => 'template',
    'disabledCommands' => [
        'archive',
        'download',
        'view',
        'quicklook',
        'open',
        'edit'
    ],
    // Open the file in editor.
    'callback' => new \CJavaScriptExpression('loadFile')
], true));
echo \CHtml::openTag('div', ['class' => 'col-md-6', 'style' => 'height: 400px;']);
    echo \CHtml::textArea('editor', '', [
        'style' => 'height: 350px; width: 100%',
        'class' => 'ace',
        'id' => 'editor'
    ]);

$buttons = [];
foreach([
    [640, 480],
    [800, 600],
    [1024, 768],
    ["100%", 768]
] as list($width, $height)) {
    $buttons[] = [
        'label' => "$width x $height",
        'class' => 'resize',
        'data-height' => $height,
        'data-width' => $width,
    ];
}

$buttons[] = [
    'label' => gT('Save changes'),
    'color' => \TbHtml::BUTTON_COLOR_PRIMARY,
    'id' => 'save'
];
echo \TbHtml::buttonGroup($buttons, [
    'style' => 'position:absolute; bottom: 0px; right: 15px;'
]);
echo \CHtml::closeTag('div');



App()->clientScript->registerScript('refresher', "$(document).on('saveFile', function(e, textarea) { $('#preview').attr('src', $('#preview').attr('src')); });");
?>
</div>
<div class="row" style="margin-top: 15px;">
    <?php
    echo \CHtml::tag('iframe', [
        'id' => 'preview',
        'src' => App()->createUrl('templates/preview', [
            'name' => $template['name'],
            'page' => $screen['id'],
        ]),
        'class' => 'col-md-12',
        'style' => 'height: 500px;'
    ], 'No iframe support.');

    ?>
</div>
<script>


</script>