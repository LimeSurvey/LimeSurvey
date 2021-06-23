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
    <div class="container-center form">
        <input type="hidden" name="userid" value="<?=$oUser->uid?>" />
        <div class="row">
            <div class="col-xs-12 alert alert-info">
                <?=gT("Note: Adding role(s) to a user will overwrite any individual user permissions!")?>
            </div>
        </div>
        <div class="form-group">
            <label for="roleselector"><?=gT("Select role(s):")?></label>
            <select name="roleselector[]" id="roleselector"  class="form-control select" style="width:150px;" multiple>
                <?php
                foreach ($aPossibleRoles as $key => $name) {
                    echo sprintf(
                        "<option value='%s' %s> %s </option>",
                        $key,
                        in_array($key, $aCurrentRoles) ? 'selected' : '',
                        $name
                    );
                }
                ?>
            </select>
        </div>        

    </div>
</div>

<div class="modal-footer modal-footer-buttons">
    <button class="btn btn-cancel " id="exitForm"><?=gT('Cancel')?></button>
    <button class="btn btn-success " id="submitForm"><?=gT('Save')?></button>
</div>
<?php $this->endWidget(); ?>
