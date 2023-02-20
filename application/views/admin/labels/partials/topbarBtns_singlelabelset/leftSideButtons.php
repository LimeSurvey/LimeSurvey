<?php

/** @var bool $hasUpdatePermission */
/** @var bool $hasDeletePermission*/
/** @var int $lid*/

//edit label set button
if ($hasUpdatePermission) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'create-import-button',
            'id' => 'edit-button',
            'text' => gT('Edit label set'),
            'icon' => 'ri-pencil-fill',
            'link' => $this->createUrl("admin/labels/sa/editlabelset/lid/" . $lid),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
                'role' => 'button'
            ],
        ]
    );
}

//export this label set
$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'export-button',
        'id' => 'export-button',
        'text' => gT('Export this label set'),
        'icon' => 'ri-upload-2-fill',
        'link' => $this->createUrl("admin/export/sa/dumplabel/lid/$lid"),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
            'role' => 'button'
        ],
    ]
);

if ($hasDeletePermission) {
//delete this label set
    $dataPost = json_encode(['lid' => $lid]);
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'delete-button',
            'id' => 'create-import-button',
            'text' => gT('Delete'),
            'icon' => 'ri-delete-bin-fill',
            'htmlOptions' => [
                'class' => 'btn btn-danger',
                'data-bs-toggle' => 'modal',
                'data-post-url' =>  $this->createUrl('admin/labels/sa/delete/'),
                'data-post-datas' => $dataPost,
                'data-btnclass' => 'btn-danger',
                'data-btntext' => gt('Delete'),
                'data-title' => gt('Delete label set'),
                'data-bs-target' => '#confirmation-modal',
                'data-message' => gT("Do you really want to delete this label set?", "js")
            ],
        ]
    );
}
