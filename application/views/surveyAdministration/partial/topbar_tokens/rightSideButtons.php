<?php

if (!empty($showDelButton) && ($hasSurveySettingsUpdatePermission || $hasTokensDeletePermission)) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'delete-token-table',
            'id' => 'delete-token-table',
            'text' => gT('Delete participants list'),
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

?>

<div id="tokenBounceModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php eT('Bounce processing');?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Here will come the result of the ajax request -->
                <p class='modal-body-text'>

                </p>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    <?php eT("Cancel");?>
                </button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
