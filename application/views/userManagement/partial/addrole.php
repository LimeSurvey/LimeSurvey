<?php
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => gT('Edit user roles')]
);

?>

<?php $form = $this->beginWidget('TbActiveForm', array(
    'id' => 'UserManagement--modalform',
    'action' => App()->createUrl('userManagement/SaveRole'),
    'enableAjaxValidation'=>false,
    'enableClientValidation'=>false,
));?>

<div class="modal-body selector--edit-role-container">
    <div class="container form">
        <input type="hidden" name="userid" value="<?=$oUser->uid?>" />
        <div class="row">
            <?php
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text' => gT("Note: Adding role(s) to a user will overwrite any individual user permissions!"),
                'type' => 'info',
                'htmlOptions' => ['class' => 'col-12']
            ]);
            ?>
        </div>
        <div class="mb-3">
            <label for="roleselector"><?=gT("Select role(s):")?></label>
            <?php $this->widget('yiiwheels.widgets.select2.WhSelect2',
                [
                    'asDropDownList' => true,
                            'htmlOptions' => array(
                                'style' => 'width:100%;',
                                'multiple' => true
                            ),
                    'data' => $aPossibleRoles,
                    'value' => $aCurrentRoles,
                    'name' => 'roleselector[]',
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
