<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="participant_edit_modal"><?php eT('List active surveys'); ?></h4>
</div>
<div class="modal-body ">
<?php
    $form = $this->beginWidget(
        'bootstrap.widgets.TbActiveForm',
        array(
            'id' => 'participantSurveysActiveForm',
            'action' => array('admin/participants/sa/editValueParticipantPanel'),
            'htmlOptions' => array('class' => ''), // for inset effect
        )
    );
?>
    <input type="hidden" name="actionTarget" value="addParticipantToSurvey" />
    <input type="hidden" name="Participant[participant_id]" value="<?php echo $model->participant_id; ?>" />
    <div class="container-fluid">
    <?php
        $this->widget('bootstrap.widgets.TbGridView', array(
            'id' => 'list_participant_surveys',
            'itemsCssClass' => 'table table-striped items',
            'dataProvider' => $surveymodel->search(),
            'columns' => $surveymodel->columns,
            'htmlOptions' => array('class'=> 'table-responsive'),
            'itemsCssClass' => 'table table-responsive table-striped',
            'afterAjaxUpdate' => 'bindButtonsInsideSurveys',
            'ajaxType' => 'POST',
            'summaryText'   => gT('Displaying {count} survey(s).'),
            'emptyText' => gT("This participant is not active in any survey")
            
        ));
    ?>
    <p>&nbsp;</p>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT('Close') ?></button>
</div>
<?php
$this->endWidget();
?>
