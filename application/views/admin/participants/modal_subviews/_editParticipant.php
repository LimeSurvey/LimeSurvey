<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="participant_edit_modal"><?php eT('Edit participant'); ?></h4>
</div>
<div class="modal-body form-horizontal">
<?php
    $form = $this->beginWidget(
        'bootstrap.widgets.TbActiveForm',
        array(
            'id' => 'editPartcipantActiveForm',
            'action' => array('admin/participants/sa/editParticipant'),
            'htmlOptions' => array('class' => 'form-horizontal'), // for inset effect
        )
    );
?>
    <input type="hidden" name="oper" value="<?php echo $editType; ?>" />
    <input type="hidden" name="Participant[participant_id]" value="<?php echo $model->participant_id; ?>" />
    <?php
        echo "<legend>".gT("Basic attributes")."</legend>";
        $baseControlGroupHtmlOptions = array(
             'labelOptions'=> array('class'=> 'col-sm-4'),
             'class' => 'col-sm-8',
             'required' => 'required'
        );
    ?>
        <div class='form-group'>
            <label class='control-label col-sm-2'>
                <?php eT('First name:'); ?>
            </label>
            <div class='col-sm-4'>
                <input class='form-control' name='Participant[firstname]' value='<?php echo $model->firstname; ?>' />
            </div>
            <label class='control-label col-sm-2'>
                <?php eT('Last name:'); ?>
            </label>
            <div class='col-sm-4'>
                <input class='form-control' name='Participant[lastname]' value='<?php echo $model->lastname; ?>' />
            </div>
        </div>
        <div class='form-group'>
            <label class='control-label col-sm-2'>
                <?php eT('E-mail:'); ?>
            </label>
            <div class='col-sm-10'>
                <input class='form-control' name='Participant[email]' value='<?php echo $model->firstname; ?>' />
            </div>
        </div>
        <div class='form-group'>
            <label class='control-label col-sm-4'><?php eT("Should this user be blacklisted?"); ?></label>
            <div class='col-sm-8'>
                &nbsp;
                <input name='Participant[blacklisted]' type='checkbox' value='<?php echo $model->blacklisted == 'Y' ? 1 : 0; ?>' data-size='small' data-on-color='warning' data-off-color='primary' data-off-text='<?php eT('No'); ?>' data-on-text='<?php eT('Yes'); ?>' class='action_changeBlacklistStatus ls-bootstrap-switch' />
            </div>
        </div>
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
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT('Close') ?></button>
    <button type="button" class="btn btn-primary action_save_modal_editParticipant"><?php eT("Save")?></button>
</div>
<?php
$this->endWidget();
?>
