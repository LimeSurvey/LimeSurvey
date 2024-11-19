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
            'name' => 'ls-question-tools-button',
            'id' => 'ls-question-tools-button',
            'text' => gT('Add user'),
            'icon' => 'ri-user-add-line',
            'htmlOptions' => $userHtmlOptions,
        ]
    );

    $dummyUserHtmlOptions = [
        'class' => 'btn btn-secondary UserManagement--action--openmodal',
        'data-bs-toggle' => 'modal',
        'data-href' => $this->createUrl("userManagement/addDummyUser")
    ];
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'ls-question-tools-button',
            'id' => 'ls-question-tools-button',
            'text' => gT('Add dummy user'),
            'icon' => 'ri-user-add-line',
            'htmlOptions' => $dummyUserHtmlOptions,
        ]
    );

//dropdown for import with two buttons (csv and json)
    $dropdownItemsImp = $this->renderPartial('/userManagement/partial/topbarBtns/dropDownItemsImport', [], true);
    ?>

    <?php
    $this->widget('ext.ButtonWidget.ButtonWidget', [
        'name' => 'ls-create-token-button',
        'id' => 'ls-create-token-button',
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
        'name' => 'ls-create-token-button',
        'id' => 'ls-create-token-button',
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
