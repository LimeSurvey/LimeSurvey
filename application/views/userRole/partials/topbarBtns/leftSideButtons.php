<?php

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'save-form-button',
        'id' => 'save-form-button',
        'text' => gT('Add user role'),
        'icon' => 'fa fa-plus-circle',
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary RoleControl--action--openmodal',
            'data-href' => App()->createUrl("userRole/editRoleModal"),
            'data-bs-toggle' => 'modal',
            'title' => gT('Add a new permission role')
        ],
    ]
);

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'save-form-button',
        'id' => 'save-form-button',
        'text' => gT('Import (XML)'),
        'icon' => 'fa fa-upload',
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary RoleControl--action--openmodal',
            'data-href' => App()->createUrl("userRole/showImportXML"),
            'data-bs-toggle' => 'modal',
            'title' => gT('Import permission role from XML')
        ],
    ]
);
