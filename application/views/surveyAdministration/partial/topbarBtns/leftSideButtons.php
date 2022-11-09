<?php

if (Permission::model()->hasGlobalPermission('surveys', 'create')) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'ls-question-tools-button',
            'id' => 'ls-question-tools-button',
            'text' => gT('Create survey'),
            'icon' => 'fa fa-plus-circle',
            'link' => $this->createUrl("surveyAdministration/newSurvey"),
            'htmlOptions' => [
                'class' => 'btn btn-primary tab-dependent-button',
                'data-tab' => '#surveys'
            ],
        ]
    );
}

if (Permission::model()->hasGlobalPermission('surveysgroups', 'create')) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'ls-question-tools-button',
            'id' => 'ls-question-tools-button',
            'text' => gT('Create survey group'),
            'icon' => 'fa fa-plus-circle',
            'link' => $this->createUrl("admin/surveysgroups/sa/create"),
            'htmlOptions' => [
                'class' => 'btn btn-primary tab-dependent-button d-none',
                'data-tab' => '#surveygroups'
            ],
        ]
    );
}
