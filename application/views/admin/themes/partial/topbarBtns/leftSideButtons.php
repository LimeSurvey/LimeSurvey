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
            'text' => gT('Save and close'),
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

}
