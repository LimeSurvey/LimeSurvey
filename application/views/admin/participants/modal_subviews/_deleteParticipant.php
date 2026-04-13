<?php
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => gT('Delete participant')]
);
?>

<div class="modal-body ">
<?php
    $form = $this->beginWidget(
        'yiistrap_fork.widgets.TbActiveForm',
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
    <select name='selectedoption' class="form-select" >
        <option value="po" selected><?php eT("Delete only from the central panel") ?></option>
        <option value="ptt"><?php eT("Delete from the central panel and associated surveys") ?></option>
        <option value="ptta"><?php eT("Delete from central panel, associated surveys and all associated responses") ?></option>
    </select>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT('Cancel') ?></button>
    <button type="button" class="btn btn-danger action_save_modal_deleteParticipant" data-bs-dismiss="modal">
        &nbsp;
        <?php eT('Delete'); ?>
    </button>
</div>
<?php
$this->endWidget();
?>
