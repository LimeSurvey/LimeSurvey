<?php

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'save-form-button',
        'id' => 'open-add-role-form-button',
        'text' => gT('Add user role'),
        'icon' => 'ri-user-add-line',
        'htmlOptions' => [
            'class' => 'btn btn-primary RoleControl--action--openmodal',
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
        'id' => 'import-roles-button',
        'text' => gT('Import (XML)'),
        'icon' => 'ri-download-2-fill',
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary RoleControl--action--openmodal',
            'data-href' => App()->createUrl("userRole/showImportXML"),
            'data-bs-toggle' => 'modal',
            'title' => gT('Import permission role from XML')
        ],
    ]
);
