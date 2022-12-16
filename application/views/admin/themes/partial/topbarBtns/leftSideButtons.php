<?php

/** @var bool $isExport */
/** @var string $templatename */
/** @var bool $isExtend */
/** @var bool $isRename only possible for extended survey themes */
/** @var bool $isDelete only possible for extended survey themes */

if ($isExport) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'button-export',
            'id' => 'button-export',
            'text' => gT('Export'),
            'icon' => 'icon-export text-success',
            'link' => $this->createUrl('admin/themes/sa/templatezip/templatename/' . $templatename),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
                'role' => 'button'
            ],
        ]
    );
}

if ($isExtend) {
    if (is_writable(App()->getConfig('userthemerootdir'))) {
        //extend
        $text1 = gT("Please enter the name for the new theme:");
        $text2 = gT("extends_") . "$templatename";
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'button-export',
                'id' => 'button-extend-' . $templatename,
                'text' => gT('Extend'),
                'icon' => 'icon-copy text-success',
                'link' => '',
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary',
                    'onclick' => "javascript: copyprompt('$text1', '$text2', '<?php echo $templatename; ?>', 'copy')",
                    'role' => 'button',
                ],
            ]
        );
    } /*else {
        //copy
    } */
}
