<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="participant_edit_modal"><?php eT('Delete participant'); ?></h4>
</div>
<div class="modal-body ">
<?php
    $form = $this->beginWidget(
        'bootstrap.widgets.TbActiveForm',
        array(
            'id' => 'deleteParticipantActiveForm',
            'action' => array('admin/participants/sa/editValueParticipantPanel'),
            'htmlOptions' => array('class' => ''), // for inset effect
        )
    );
?>
    <input type="hidden" name="actionTarget" value="deleteParticipant" />
    <input type="hidden" name="participant_id" value="<?php echo $model->participant_id; ?>" />
    <p><?php eT("Please choose one option."); ?></p>
    <select name='selectedoption' class="form-control" >
        <option value="po" selected><?php eT("Delete only from the central panel") ?></option>
        <option value="ptt"><?php eT("Delete from the central panel and associated surveys") ?></option>
        <option value="ptta"><?php eT("Delete from central panel, associated surveys and all associated responses") ?></option>
    </select>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-danger action_save_modal_deleteParticipant" data-dismiss="modal"><span class='fa fa-trash'></span>&nbsp;<?php eT('Delete') ?></button>
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT('Close') ?></button>
</div>
<?php
$this->endWidget();
?>
