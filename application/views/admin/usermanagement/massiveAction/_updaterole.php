<?php
    $aPermissiontemplates = Permissiontemplates::model()->findAll();
?>

<div class="modal-header">
    <h3>
        <?php eT("Apply role to user");?>
    </h3>
</div>
<div class="modal-body selector--edit-role-container">
    <div class="container-center form">        
        <div class="row">
            <div class="col-xs-12 alert alert-info">
                <?=gT("Careful: Applying a role to the user will overwrite any individual permissions given to the user!")?>
            </div>
        </div>
        <div class="form-group">
            <label for="roleselector"><?=gT("Select role to apply to users")?></label>
            <select class="form-control select post-value" name="roleselector" id="roleselector" multiple>
                <?php foreach($aPermissiontemplates as $oPermissiontemplate) {
                    echo "<option value='".$oPermissiontemplate->ptid."'>".$oPermissiontemplate->name."</option>";
                } ?>
            </select>
        </div>
    </div>
</div>