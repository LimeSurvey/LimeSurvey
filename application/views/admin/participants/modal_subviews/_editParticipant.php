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
    <?=$form->hiddenField($model, 'participant_id')?>
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
                <?=$form->textField($model, 'firstname')?>
            </div>
            <label class='control-label '>
                <?php eT('Last name:'); ?>
            </label>
            <div class=''>
                <?=$form->textField($model, 'lastname')?>
            </div>
        </div>
        <div class='form-group'>
            <label class='control-label '>
                <?php eT('E-mail:'); ?>
            </label>
            <div class='0'>
                <?=$form->textField($model, 'email')?>
            </div>
        </div>
        <div class='form-group'>
            <label class='control-label '><?php eT("Language:"); ?></label>
            <div class=''>
                <?=$form->dropDownList($model, 'language', $model->languageOptions, ['empty' => gT('Select language...')])?>
            </div>
        </div>
        <div class='form-group'>
            <label class='control-label '><?php eT("Blacklist user:"); ?></label>
            <div class=''>
                &nbsp;
                <?php $this->widget(
                'yiiwheels.widgets.switch.WhSwitch',
                array(
                    'attribute' => 'blacklisted',
                    'model' => $model,
                    'htmlOptions'=>array(
                        'class'=>'bootstrap-switch',
                        'value' => 'Y'
                    ),
                    'onLabel'=>gT('Yes'),
                    'offLabel'=>gT('No'),
                    'onColor'=> 'warning',
                    'offColor'=> 'primary'
                )
            );
            ?>
            </div>
        </div>

        <!-- Change owner -->
        <?php if ($model->isOwnerOrSuperAdmin()): ?>
        <?php  ?>
            <div class='form-group'>
                <label class='control-label '><?php eT("Owner:"); ?></label>
                <div class=''>
                <?php
                    // When we add a new user, owner is default to current user
                    $selected = ($editType == 'add') ? Yii::app()->user->id : $model->owner_uid;
                    $listUsers = CHtml::listData($users,'uid','full_name');
                    echo CHtml::dropDownList('Participant[owner_uid]',$selected,$listUsers,array('id'=>'owner_uid','class'=>'form-control'));
                ?>
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
<script>
$('#editPartcipantActiveForm .bootstrap-switch').bootstrapSwitch();
</script>
<?php
$this->endWidget();
?>
