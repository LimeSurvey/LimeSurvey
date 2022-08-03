<?php
Yii::app()->getController()->renderPartial(
'/layouts/partial_modals/modal_header',
['modalTitle' => gT('Share participant(s)')]
);
?>

<div class="modal-body ">
<?php
    $form = $this->beginWidget(
        'yiistrap_fork.widgets.TbActiveForm',
        array(
            'id' => 'shareParticipantActiveForm',
            'action' => array('admin/participants/sa/shareParticipants'),
            'htmlOptions' => array('class' => 'form '), // for inset effect
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
    <div class="row">
        <div class="col-lg-6">
            <div class='mb-3'>
                <label class='form-label'>
                    <?php eT("User with whom the participants are to be shared:"); ?>
                </label>

                <div class='col-12'>
                    <select class='form-select' id='shareuser' name='shareuser'>
                        <option value=''><?php eT('Share with all users'); ?></option>
                        <?php foreach ($users as $user): ?>
                            <option value='<?php echo $user->uid; ?>'>
                                <?php echo $user->full_name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class='col-md-4'></div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class='mb-3'>
                <label class='form-label text-start'>
                    <?php eT("Other users may edit this participant"); ?>
                </label>
                <div class='col-12'>
                    <?php App()->getController()->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => "can_edit",
                        'checkedOption' => "0",
                        'selectOptions' => [
                            '1' => gT('Yes'),
                            '0' => gT('No'),
                        ]
                    ]); ?>
                </div>
            </div>
        </div>
    </div>

</div>
<div class="modal-footer">
    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT('Cancel') ?></button>
    <button role="button" type="button" class="btn btn-primary action_save_modal_shareparticipant">
        <?php eT("Share")?>
    </button>
</div>
<?php $this->endWidget(); ?>
