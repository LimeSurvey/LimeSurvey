<div class="modal-header">
    <h5 class="modal-title" id="participant_edit_modal"><?php eT('List active surveys'); ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body ">
    <?php $form = $this->beginWidget(
        'yiistrap_fork.widgets.TbActiveForm',
        [
            'id'          => 'participantSurveysActiveForm',
            'action'      => ['admin/participants/sa/editValueParticipantPanel'],
            'htmlOptions' => ['class' => ''], // for inset effect
        ]
    ); ?>
    <input type="hidden" name="actionTarget" value="addParticipantToSurvey"/>
    <input type="hidden" name="Participant[participant_id]" value="<?php echo $model->participant_id; ?>"/>
    <div class="container-fluid">
        <?php
        $this->widget('application.extensions.admin.grid.CLSGridView', [
            'id'              => 'list_participant_surveys',
            'dataProvider'    => $surveymodel->search(),
            'columns'         => $surveymodel->columns,
            'htmlOptions'     => ['class' => 'table-responsive grid-view-ls'],
            'lsAfterAjaxUpdate' => ['bindButtonsInsideSurveys()'],
            'ajaxType'        => 'POST',
            'summaryText'     => gT('Displaying {count} survey(s).'),
            'emptyText'       => gT("This participant is not active in any survey")

        ]);
        ?>
        <p>&nbsp;</p>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php eT('Close') ?></button>
    </div>
    <?php $this->endWidget(); ?>
</div>
