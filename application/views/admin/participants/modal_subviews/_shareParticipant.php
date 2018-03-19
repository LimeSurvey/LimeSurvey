<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="participant_edit_modal"><?php eT('Share participant(s)'); ?></h4>
</div>
<div class="modal-body ">
<?php
    $form = $this->beginWidget(
        'bootstrap.widgets.TbActiveForm',
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
        <div class="col-md-6">
            <div class='form-group'>
                <label class='control-label'>
                    <?php eT("User with whom the participants are to be shared:"); ?>
                </label>

                <div class='col-sm-12'>
                    <select class='form-control' id='shareuser' name='shareuser'>
                        <option value=''><?php eT('Share with all users'); ?></option>
                        <?php foreach ($users as $user): ?>
                            <option value='<?php echo $user->uid; ?>'>
                                <?php echo $user->full_name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class='col-sm-4'></div>
            </div>
        </div>

        <div class="col-md-6">
            <div class='form-group'>
                <label class='control-label text-left'>
                    <?php eT("Other users may edit this participant"); ?>
                </label>
                <div class='col-sm-12'>
                    <input name='can_edit' type='checkbox' data-size='small' data-on-color='primary' data-off-color='warning' data-off-text='<?php eT('No'); ?>' data-on-text='<?php eT('Yes'); ?>' class='ls-bootstrap-switch ls-space margin left-15' />
                </div>
            </div>
        </div>
    </div>

</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT('Close') ?></button>
    <button type="button" class="btn btn-primary action_save_modal_shareparticipant"><?php eT("Share")?></button>
</div>
<?php $this->endWidget(); ?>
