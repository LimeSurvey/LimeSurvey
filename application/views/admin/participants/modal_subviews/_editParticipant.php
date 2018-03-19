<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <div class="modal-title h4" id="participant_edit_modal"><?php if ($editType == 'add'): eT('Add participant'); else: eT('Edit participant'); endif; ?></div>
</div>
<div class="modal-body edit-participant-modal-body ">
<?php
    $form = $this->beginWidget(
        'bootstrap.widgets.TbActiveForm',
        array(
            'id' => 'editPartcipantActiveForm',
            'action' => array('admin/participants/sa/editParticipant'),
            'htmlOptions' => array('class' => 'form'), // for inset effect
        )
    );
?>
    <input type="hidden" name="oper" value="<?php echo $editType; ?>" />
    <input type="hidden" name="Participant[participant_id]" value="<?php echo $model->participant_id; ?>" />
    <?php
        echo "<legend>".gT("Basic attributes")."</legend>";
        $baseControlGroupHtmlOptions = array(
             'labelOptions'=> array('class'=> ''),
             'class' => '',
             'required' => 'required'
        );
    ?>
        <div class='form-group'>
            <label class='control-label '>
                <?php eT('First name:'); ?>
            </label>
            <div class=''>
                <input class='form-control' name='Participant[firstname]' value='<?php echo $model->firstname; ?>' />
            </div>
            <label class='control-label '>
                <?php eT('Last name:'); ?>
            </label>
            <div class=''>
                <input class='form-control' name='Participant[lastname]' value='<?php echo $model->lastname; ?>' />
            </div>
        </div>
        <div class='form-group'>
            <label class='control-label '>
                <?php eT('E-mail:'); ?>
            </label>
            <div class='0'>
                <input class='form-control' name='Participant[email]' value='<?php echo $model->email; ?>' />
            </div>
        </div>
        <div class='form-group'>
            <label class='control-label '><?php eT("Blacklist user:"); ?></label>
            <div class=''>
                &nbsp;
                <input name='Participant[blacklisted]' type='checkbox' <?php if ($model->blacklisted == 'Y'): echo ' checked="checked" '; endif; ?> data-size='small' data-on-color='warning' data-off-color='primary' data-off-text='<?php eT('No'); ?>' data-on-text='<?php eT('Yes'); ?>' class='action_changeBlacklistStatus ls-bootstrap-switch' />
            </div>
        </div>

        <!-- Change owner -->
        <?php if ($model->isOwnerOrSuperAdmin()): ?>
            <div class='form-group'>
                <label class='control-label '><?php eT("Owner:"); ?></label>
                <div class=''>
                <select class='form-control' id='owner_uid' name='Participant[owner_uid]'>

                    <?php // When we add a new user, owner is default to current user ?>
                    <?php if ($editType == 'add'): ?>
                        <?php foreach ($users as $user): ?>
                            <option <?php if (Yii::app()->user->id == $user->uid): echo ' selected="selected" '; endif; ?> value='<?php echo $user->uid; ?>'><?php echo $user->full_name; ?></option>
                        <?php endforeach; ?>

                    <?php // When we add a user, owner is set to current owner ?>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <option <?php if ($model->owner_uid == $user->uid): echo ' selected="selected" '; endif; ?> value='<?php echo $user->uid; ?>'><?php echo $user->full_name; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </select>
                </div>
                <div class=''></div>
            </div>
        <?php endif; ?>

    <?php if (count($extraAttributes) > 0): ?>
        <legend><?php eT("Custom attributes"); ?></legend>
        <?php $i = 0; foreach($extraAttributes as $attribute): $i++; ?>

            <!-- Two inputs on each row -->
            <?php if ($i % 2 == 0): ?>
                <div class='form-group'>
            <?php endif; ?>

            <?php switch ($attribute['attribute_type']):

                // Text box
                case 'TB': ?>
                    <?php $this->renderPartial('/admin/participants/modal_subviews/attributes/textbox', $attribute); ?>
                <?php break; ?>

                <!-- Drop down -->
                <?php case 'DD': ?>
                    <?php $this->renderPartial('/admin/participants/modal_subviews/attributes/dropdown', $attribute); ?>
                <?php break; ?>

                <!-- Date -->
                <?php case 'DP': ?>
                    <?php $this->renderPartial('/admin/participants/modal_subviews/attributes/date', $attribute); ?>
                <?php break; ?>

            <?php endswitch; ?>

            <!-- Close form-group div -->
            <?php if ($i % 2 == 0): ?>
                </div>
            <?php endif; ?>

        <?php endforeach; ?>
    <?php endif; ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT('Close') ?></button>
    <button type="button" class="btn btn-primary action_save_modal_editParticipant"><?php eT("Save")?></button>
</div>
<?php
$this->endWidget();
?>
