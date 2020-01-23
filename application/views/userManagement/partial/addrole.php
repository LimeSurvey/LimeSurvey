<div class="modal-header">
    <h3>
        <?php eT("Edit user roles");?>
    </h3>
</div>
<div class="modal-body selector--edit-role-container">
    <div class="container-center form">     
    <?php $form = $this->beginWidget('TbActiveForm', array(
            'id' => 'UserManagement--modalform',
            'action' => App()->createUrl('userManagement/SaveRole'),
            'enableAjaxValidation'=>false,
            'enableClientValidation'=>false,
        ));?>
        <input type="hidden" name="userid" value="<?=$oUser->uid?>" />
        <div class="row">
            <div class="col-xs-12 alert alert-info">
                <?=gT("Note: Adding role(s) to a user will overwrite any individual user permissions!")?>
            </div>
        </div>
        <div class="form-group">
            <label for="roleselector"><?=gT("Select role(s):")?></label>
            <select name="roleselector[]" id="roleselector" class="form-control select" multiple>
                <?php foreach($aPossibleRoles as $key => $name) {
                    echo sprintf(
                        "<option value='%s' %s> %s </option>", 
                        $key,
                        in_array($key, $aCurrentRoles) ? 'selected' : '',
                        $name
                    );
                } ?>
            </select>
        </div>        
        <div class="row ls-space margin top-15">
            <hr />
        </div>
        <div class="row ls-space margin top-5">
            <button class="btn btn-success col-sm-3 col-xs-5 col-xs-offset-1" id="submitForm"><?=gT('Save')?></button>
            <button class="btn btn-error col-sm-3 col-xs-5 col-xs-offset-1" id="exitForm"><?=gT('Cancel')?></button>
        </div>
    <?php $this->endWidget(); ?>
    </div>
</div>
