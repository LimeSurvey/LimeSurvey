<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="participant_edit_modal"><?php echo $model->firstname."&nbsp;".$model->lastname; ?></h4>
</div>
<div class="modal-body form-horizontal">
<?php
    $form = $this->beginWidget(
        'bootstrap.widgets.TbActiveForm',
        array(
            'id' => 'shareParticipantActiveForm',
            'action' => array('admin/participants/sa/shareParticipant'),
            'htmlOptions' => array('class' => 'well form-horizontal'), // for inset effect
        )
    );
?>
    <input type="hidden" name="participant_id" value="<?php echo $model->participant_id; ?>" />
    <?php 
    echo "<p>";
    eT("Share this participant with other Users");
    echo "</p>";
    echo TbHtml::checkBoxControlGroup("can_edit", false, array('label' => gT("Other users may edit this participant"))); ?>
    <p>&nbsp;</p>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT('Close') ?></button>
    <button type="button" class="btn btn-primary action_save_modal_shareparticipant"><?php eT("Share")?></button>
</div>
<?php
$this->endWidget();
?>