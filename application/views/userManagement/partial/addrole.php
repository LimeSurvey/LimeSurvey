<?php
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => gT('Edit user roles')]
);

?>

<?php $form = $this->beginWidget('TbActiveForm', array(
    'id' => 'UserManagement--modalform',
    'action' => App()->createUrl('userManagement/SaveRole'),
    'enableAjaxValidation' => false,
    'enableClientValidation' => false,
));?>

<div class="modal-body selector--edit-role-container">
    <div class="form">
        <div class="row ls-space margin top-5 bottom-5 d-none" id="UserManagement--errors" role="alert" aria-live="assertive" aria-atomic="true" tabindex="-1">
        </div>
        <input type="hidden" name="userid" value="<?= $oUser->uid ?>"/>
        <?php
        if ($hasIndividualPermissions) {
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text'        => gT("This user has individual permissions. If you assign a role, all of them will be removed and replaced by the permissions of the role."),
                'type'        => 'warning',
                'htmlOptions' => ['class' => 'col-12']
            ]);
        }
        if (!empty($aLockedRoles)) {
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text'        => sprintf(
                    gT("This user also has the following roles, which you are not allowed to change: %s"),
                    CHtml::encode(implode(', ', $aLockedRoles))
                ),
                'type'        => 'warning',
                'htmlOptions' => ['class' => 'col-12']
            ]);
        }
        ?>
        <div class="mb-3">
            <label for="roleselector"><?= gT("Select role(s):") ?></label>
            <?php $this->widget(
                'yiiwheels.widgets.select2.WhSelect2',
                [
                    'asDropDownList' => true,
                    'htmlOptions'    => [
                        'style'    => 'width:100%;',
                        'multiple' => true
                    ],
                    'data'           => $aPossibleRoles,
                    'value'          => $aCurrentRoles,
                    'name'           => 'roleselector[]',
                ]
            ); ?>
        </div>
    </div>
</div>

<div class="modal-footer modal-footer-buttons">
     <button class="btn btn-cancel" id="exitForm" data-bs-dismiss="modal">
         <?=gT('Cancel')?>
     </button>
    <button class="btn btn-primary" id="submitForm">
        <?=gT('Save')?>
    </button>
</div>
<?php $this->endWidget(); ?>
