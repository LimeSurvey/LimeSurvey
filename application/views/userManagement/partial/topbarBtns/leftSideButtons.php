<?php

if (Permission::model()->hasGlobalPermission('users', 'create')) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'ls-question-tools-button',
            'id' => 'ls-question-tools-button',
            'text' => gT('Add user'),
            'icon' => 'fa fa-plus-circle',
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary UserManagement--action--openmodal',
                'data-bs-toggle' => 'modal',
                'data-href' => $this->createUrl("userManagement/addEditUser")
            ],
        ]
    );

    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'ls-question-tools-button',
            'id' => 'ls-question-tools-button',
            'text' => gT('Add dummy user'),
            'icon' => 'fa fa-plus-square text-success',
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary UserManagement--action--openmodal',
                'data-bs-toggle' => 'modal',
                'data-href' => $this->createUrl("userManagement/addDummyUser")
            ],
        ]
    );

//dropdown for import with two buttons (csv and json)
    $dropdownItemsImp = $this->renderPartial('/userManagement/partial/topbarBtns/dropDownItemsImport', [], true);
    ?>
<div class="d-inline-flex">
    <?php
    $this->widget('ext.ButtonWidget.ButtonWidget', [
        'name' => 'ls-create-token-button',
        'id' => 'ls-create-token-button',
        'text' => gT('Import'),
        'icon' => 'icon-import text-success',
        'isDropDown' => true,
        'dropDownContent' => $dropdownItemsImp,
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]); ?>
</div>
    <?php

    $dropdownItems = $this->renderPartial('/userManagement/partial/topbarBtns/dropDownItemsExport', [], true);

    ?>
<div class="d-inline-flex">
    <?php
    $this->widget('ext.ButtonWidget.ButtonWidget', [
        'name' => 'ls-create-token-button',
        'id' => 'ls-create-token-button',
        'text' => gT('Export'),
        'icon' => 'fa fa-upload',
        'isDropDown' => true,
        'dropDownContent' => $dropdownItems,
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]); ?>
    </div>
<?php
}
