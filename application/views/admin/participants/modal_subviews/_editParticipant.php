<?php

if ($editType === 'add') {
    $modalTitle = gT('Add participant');
    $buttonTitle = gT('Add');
} else {
    $modalTitle = gT('Edit participant');
    $buttonTitle = gT('Save');
}

Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => $modalTitle, 'modalTitleId' => 'participant_edit_modal']
);
?>
<div class="modal-body edit-participant-modal-body ">
    <?php
    $form = $this->beginWidget(
        'yiistrap_fork.widgets.TbActiveForm',
        [
            'id'          => 'editPartcipantActiveForm',
            'action'      => ['admin/participants/sa/editParticipant'],
            'htmlOptions' => ['class' => 'form'], // for inset effect
        ]
    );
    ?>
    <input type="hidden" name="oper" value="<?php echo $editType; ?>"/>
    <?= $form->hiddenField($model, 'participant_id') ?>
    <fieldset>
        <?php
        echo "<legend>" . gT("Basic attributes") . "</legend>";
        $baseControlGroupHtmlOptions = [
            'labelOptions' => ['class' => ''],
            'class'        => '',
            'required'     => 'required'
        ];
        ?>
        <div class='mb-3'>
            <label id="Participant_firstname_label" class='form-label ' for="Participant_firstname">
                <?php eT('First name:'); ?>
            </label>
            <div class=''>
                <?= $form->textField($model, 'firstname', ['id' => 'Participant_firstname', 'aria-labelledby' => 'Participant_firstname_label']) ?>
            </div>
            <label id="Participant_lastname_label" class='form-label ' for="Participant_lastname">
                <?php eT('Last name:'); ?>
            </label>
            <div class=''>
                <?= $form->textField($model, 'lastname', ['id' => 'Participant_lastname', 'aria-labelledby' => 'Participant_lastname_label']) ?>
            </div>
        </div>
        <div class='mb-3'>
            <label id="Participant_email_label" class='form-label ' for="Participant_email">
                <?php eT('Email:'); ?>
            </label>
            <div class=''>
                <?= $form->textField($model, 'email', ['id' => 'Participant_email', 'aria-labelledby' => 'Participant_email_label']) ?>
            </div>
        </div>
        <div class='mb-3'>
            <label id="Participant_language_label" class='form-label ' for="Participant_language"><?php eT("Language:"); ?></label>
            <div class=''>
                <?= $form->dropDownList(
                    $model,
                    'language',
                    $model->languageOptions,
                    [
                        'id'              => 'Participant_language',
                        'aria-labelledby' => 'Participant_language_label',
                        'empty'           => gT('Select language...'),
                        'class'           => 'form-select'
                    ]
                ); ?>
            </div>
        </div>
        <div class='mb-3'>
            <label class='form-label '><?php eT("Participant is on blocklist:"); ?></label>
            <div>
                <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                    'model'         => $model,
                    'ariaLabel'     => gT("Participant is on blocklist"),
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
            <?php ?>
            <div class='mb-3'>
                <label id="owner_uid_label" class='form-label' for="owner_uid"><?php eT("Owner:"); ?></label>
                <?php
                // When we add a new user, owner is default to current user
                $selected = ($editType == 'add') ? Yii::app()->user->id : $model->owner_uid;
                $listUsers = CHtml::listData($users, 'uid', 'full_name');
                echo CHtml::dropDownList(
                    'Participant[owner_uid]',
                    $selected,
                    $listUsers,
                    ['id' => 'owner_uid', 'class' => 'form-select', 'aria-labelledby' => 'owner_uid_label']
                );
                ?>
            </div>
        <?php endif; ?>
    </fieldset>

    <?php if (count($extraAttributes) > 0): ?>
        <fieldset>
            <legend><?php eT("Custom attributes"); ?></legend>
            <?php foreach ($extraAttributes as $attribute): ?>
                <?php switch ($attribute['attribute_type']):
                    case 'TB': // Text box
                        $this->renderPartial('/admin/participants/modal_subviews/attributes/textbox', $attribute);
                        break;
                    case 'DD': // Drop down
                        $this->renderPartial('/admin/participants/modal_subviews/attributes/dropdown', $attribute);
                        break;
                    case 'DP': // Date
                        $this->renderPartial('/admin/participants/modal_subviews/attributes/date', $attribute);
                        break;
                endswitch; ?>
            <?php endforeach; ?>
        </fieldset>
    <?php endif; ?>
    <?php
    $this->endWidget();
    ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT('Cancel') ?></button>
    <button type="button" class="btn btn-primary action_save_modal_editParticipant">
        <?php echo $buttonTitle; ?>
    </button>
</div>