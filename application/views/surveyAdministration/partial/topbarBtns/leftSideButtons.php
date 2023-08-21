<?php

if (Permission::model()->hasGlobalPermission('surveysgroups', 'create')) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'ls-question-tools-button',
            'id' => 'ls-question-tools-button',
            'text' => gT('Create survey group'),
            'icon' => 'ri-add-circle-fill',
            'link' => $this->createUrl("admin/surveysgroups/sa/create"),
            'htmlOptions' => [
                'class' => 'btn btn-primary tab-dependent-button d-none',
                'data-tab' => '#surveygroups'
            ],
        ]
    );
}
