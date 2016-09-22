<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="participant_edit_modal"><?php eT('Share participant(s)'); ?></h4>
</div>
<div class="modal-body form-horizontal">
<?php
    $form = $this->beginWidget(
        'bootstrap.widgets.TbActiveForm',
        array(
            'id' => 'shareParticipantActiveForm',
            'action' => array('admin/participants/sa/shareParticipants'),
            'htmlOptions' => array('class' => 'well form-horizontal'), // for inset effect
        )
    );
?>
    <?php if (isset($participantIds)): ?>
        <?php foreach ($participantIds as $id): ?>
            <input type="hidden" name="participant_id[]" value="<?php echo $id; ?>" />
        <?php endforeach;?>
    <?php else: ?>
        <input type="hidden" name="participant_id" value="<?php echo $model->participant_id; ?>" />
    <?php endif; ?>

    <p>
        <?php eT("User with whom the participants are to be shared"); ?>
    </p>

    <p>
        <select class='form-control' id='shareuser' name='shareuser'>
            <option value=''><?php eT('Share with all users'); ?></option>
            <?php foreach ($users as $user): ?>
                <option value='<?php echo $user->uid; ?>'>
                    <?php echo $user->full_name; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <?php echo TbHtml::checkBoxControlGroup("can_edit", false, array('label' => gT("Other users may edit this participant"))); ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT('Close') ?></button>
    <button type="button" class="btn btn-primary action_save_modal_shareparticipant"><?php eT("Share")?></button>
</div>
<?php $this->endWidget(); ?>
