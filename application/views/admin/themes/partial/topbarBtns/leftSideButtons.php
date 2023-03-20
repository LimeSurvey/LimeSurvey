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
            'icon' => 'ri-upload-2-fill',
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
                'icon' => 'icon-copy',
                'link' => '',
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary',
                    'onclick' => "javascript: copyprompt('$text1', '$text2', '$templatename', 'copy')",
                    'role' => 'button',
                ],
            ]
        );
    }
}

if (is_template_editable($templatename)) {
    if (Permission::model()->hasGlobalPermission('templates', 'update')) {
        $text1 = gT("Rename this theme to:");
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'button-rename-theme',
                'id' => 'button-rename-theme',
                'text' => gT('Rename'),
                'icon' => 'ri-pencil-fill',
                'link' => '',
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary',
                    'onclick' => "javascript: copyprompt('$text1', '$templatename', '$templatename', 'rename')",
                    'role' => 'button',
                ],
            ]
        );
    }
}

if (Permission::model()->hasGlobalPermission('templates', 'delete')) {
    $dataPost = json_encode(['templatename' => $templatename]);
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'button-delete',
            'id' => 'button-delete',
            'text' => gT('Delete'),
            'icon' => 'ri-delete-bin-fill',
            'link' => Yii::app()->getController()->createUrl('admin/themes/sa/delete/'),
            'htmlOptions' => [
                'class' => 'btn btn-danger selector--ConfirmModal',
                'data-post' => $dataPost,
                'data-text' => gT('Are you sure you want to delete this theme?'),
                'data-button-no' => gT('Cancel'),
                'data-button-yes' => gT('Delete'),
                'data-button-type' => 'btn-danger',
            ],
        ]
    );
}
