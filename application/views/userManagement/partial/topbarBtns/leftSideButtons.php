<?php

if (Permission::model()->hasGlobalPermission('users', 'create')) {
    $userHtmlOptions = [
        'class' => 'btn btn-primary UserManagement--action--openmodal',
        'data-bs-toggle' => 'modal',
        'data-href' => $this->createUrl("userManagement/addEditUser")
    ];
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'add-user-button',
            'id' => 'add-user-button',
            'text' => gT('Add user'),
            'icon' => 'ri-user-add-line',
            'htmlOptions' => $userHtmlOptions,
        ]
    );

//dropdown for import with two buttons (csv and json)
    $dropdownItemsImp = $this->renderPartial('/userManagement/partial/topbarBtns/dropDownItemsImport', [], true);
    ?>

    <?php
    $this->widget('ext.ButtonWidget.ButtonWidget', [
        'name' => 'import-users-button',
        'id' => 'import-users-button',
        'text' => gT('Import'),
        'icon' => 'ri-download-2-fill',
        'isDropDown' => true,
        'dropDownContent' => $dropdownItemsImp,
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]); ?>

    <?php

    $dropdownItems = $this->renderPartial('/userManagement/partial/topbarBtns/dropDownItemsExport', [], true);

    ?>

    <?php
    $this->widget('ext.ButtonWidget.ButtonWidget', [
        'name' => 'export-users-button',
        'id' => 'export-users-button',
        'text' => gT('Export'),
        'icon' => 'ri-upload-2-fill',
        'isDropDown' => true,
        'dropDownContent' => $dropdownItems,
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]); ?>

    <?php
}
