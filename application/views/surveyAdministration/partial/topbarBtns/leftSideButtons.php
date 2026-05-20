<?php
/**
 * Use this button to quickly add 1000 dummy surveys for testing purposes.
 * It will create surveys with the title "Bulk Survey X" where X is a number from 1 to 1000.
 * The surveys will be created in the default survey group and with the default language of the application.
 *
if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'btn-create-test-surveys',
            'id' => 'btn-create-test-surveys',
            'text' => gT('Create test surveys'),
            'icon' => 'ri-test-tube-line',
            'link' => $this->createUrl("surveyAdministration/createTestSurveys"),
            'htmlOptions' => [
                'class' => 'btn btn-warning tab-dependent-button',
                'data-tab' => '#surveys',
            ],
        ]
    );
}
*/

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
