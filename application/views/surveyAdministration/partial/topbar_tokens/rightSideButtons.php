<?php

if ($tokenexists) {
    if (!empty($showDelButton) && ($hasSurveySettingsUpdatePermission || $hasTokensDeletePermission)) {
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'delete-token-table',
                'id' => 'delete-token-table',
                'text' => gT('Delete participant list'),
                'icon' => '',
                'link' => Yii::App()->createUrl("admin/tokens/sa/kill/surveyid/$oSurvey->sid"),
                'htmlOptions' => [
                    'class' => 'btn btn-danger',
                    'role' => 'button'
                ],
            ]
        );
    }

    // Include the default buttons
    $this->renderPartial('/surveyAdministration/partial/topbar/surveyTopbarRight_view', get_defined_vars());

    if (!empty($showDownloadButton)) {
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'export-button',
                'id' => 'export-button',
                'text' => gT('Download CSV file'),
                'icon' => 'ri-download-fill',
                'htmlOptions' => [
                    'class' => 'btn btn-primary',
                    'role' => 'button',
                    'data-submit-form' => 1,
                ],
            ]
        );
    }

    if (!empty($showSendInvitationButton)) {
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'send-invitation-button',
                'id' => 'send-invitation-button',
                'text' => gT('Send invitations'),
                'icon' => 'ri-mail-send-fill',
                'htmlOptions' => [
                    'class' => 'btn btn-primary',
                    'role' => 'button'
                ],
            ]
        );
    }

    if (!empty($showSendReminderButton)) {
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'send-reminders-button',
                'id' => 'send-reminders-button',
                'text' => gT('Send reminders'),
                'icon' => 'ri-mail-send-fill',
                'htmlOptions' => [
                    'class' => 'btn btn-primary',
                    'role' => 'button'
                ],
            ]
        );
    }
}
