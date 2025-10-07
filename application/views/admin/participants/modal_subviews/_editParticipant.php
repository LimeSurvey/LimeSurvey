<?php
if ($editType == 'add'){
    $modalTitle = gT('Add participant');
    $buttonTitle = gT('Add');
}else{
    $modalTitle = gT('Edit participant');
    $buttonTitle = gT('Save');
}

Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => $modalTitle]
);
?>
<div class="modal-body edit-participant-modal-body ">
<?php
    $form = $this->beginWidget(
        'yiistrap_fork.widgets.TbActiveForm',
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
        <div class='mb-3'>
            <label class='form-label '>
                <?php eT('First name:'); ?>
            </label>
            <div class=''>
                <?=$form->textField($model, 'firstname')?>
            </div>
            <label class='form-label '>
                <?php eT('Last name:'); ?>
            </label>
            <div class=''>
                <?=$form->textField($model, 'lastname')?>
            </div>
        </div>
        <div class='mb-3'>
            <label class='form-label '>
                <?php eT('Email:'); ?>
            </label>
            <div class='0'>
                <?=$form->textField($model, 'email')?>
            </div>
        </div>
        <div class='mb-3'>
            <label class='form-label '><?php eT("Language:"); ?></label>
            <div class=''>
                <?= $form->dropDownList(
                        $model,
                        'language',
                        $model->languageOptions,
                        [
                            'empty' => gT('Select language...'),
                            'class' => 'form-select'
                        ]
                    ); ?>
            </div>
        </div>
        <div class='mb-3'>
            <label class='form-label '><?php eT("Participant is on blocklist:"); ?></label>
            <div>
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'model'         => $model,
                    'attribute'     => 'blacklisted',
                    'checkedOption' => $model->blacklisted ?? 'N',
                    'selectOptions' => [
                        'Y' => gT('Yes'),
                        'N' => gT('No'),
                    ]
                ]); ?>
            </div>
        </div>

        <!-- Change owner -->
        <?php if ($model->isOwnerOrSuperAdmin()): ?>
        <?php  ?>
            <div class='mb-3'>
                <label class='form-label '><?php eT("Owner:"); ?></label>
                <?php
                    // When we add a new user, owner is default to current user
                    $selected = ($editType == 'add') ? Yii::app()->user->id : $model->owner_uid;
                    $listUsers = CHtml::listData($users,'uid','full_name');
                    echo CHtml::dropDownList('Participant[owner_uid]',$selected,$listUsers,array('id'=>'owner_uid','class'=>'form-select'));
                ?>
            </div>
        <?php endif; ?>

    <?php if (count($extraAttributes) > 0): ?>
        <legend><?php eT("Custom attributes"); ?></legend>
        <?php $i = 0; foreach($extraAttributes as $attribute): $i++; ?>

            <!-- Two inputs on each row -->
            <?php if ($i % 2 == 0): ?>
                <div class='mb-3'>
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

            <!-- Close mb-3 div -->
            <?php if ($i % 2 == 0): ?>
                </div>
            <?php endif; ?>

        <?php endforeach; ?>
    <?php endif; ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT('Cancel') ?></button>
    <button role="button" type="button" class="btn btn-primary action_save_modal_editParticipant">
        <?php echo $buttonTitle; ?>
    </button>
</div>

<?php
$this->endWidget();
?>