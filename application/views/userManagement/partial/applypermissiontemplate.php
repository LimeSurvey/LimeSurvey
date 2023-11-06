<?php
    //todo REFACTORING this modal view is nerver rendered (even not partial), could we remove it??
?>

<div class="modal-header">
    <h3>
        <?php eT("Edit permissions");?>
    </h3>
</div>
<div class="modal-body">
    <div class="container-center">        
        <?=TbHtml::formTb(null, App()->createUrl('admin/usermanagement', ['sa' => 'saveUserPermissions']), 'post', ["id"=>"UserManagement--modalform"])?>
            <input type='hidden' name='userid' value='<?php echo $oUser->uid;?>' />
            <div class="row ls-space margin top-5">
                <div class="col-sm-12">
                    Assign permission level to user:
                </div>
            </div>
            <div class="row form-group ls-space margin top-5 bottom-5">
                <label class="control-label">
                    Permission level: 
                </label>
                <select class="form-control" name="permissionclass" id="smk--selector--permissionclass">
                    <option value="surveymanager">Survey manager</option>
                    <option value="classmanager">Group manager</option>
                </select>
            </div>
            <div class="row form-group ls-space margin top-5 bottom-5" id="smk--selector--surveypermission" style="display:none;">
                <label class="control-label">
                    Umfragen zur Berechtigung auswählen: 
                </label>
                <select class="form-control" name="entity_ids[]" multiple="multiple" id="smk--selector--entity-ids">
                    <?php foreach($aMySurveys as $oSurvey) {
                        echo "<option value='".$oSurvey->sid."'>".$oSurvey->currentLanguageSettings->surveyls_title."</option>";
                    } ?>
                </select>
            </div>
            <div class="row ls-space margin top-25">
                <button class="btn btn-success col-sm-3 col-xs-5 col-xs-offset-1" id="submitForm"><?=gT('Save')?></button>
                <button class="btn btn-error col-sm-3 col-xs-5 col-xs-offset-1" id="exitForm"><?=gT('Cancel')?></button>
            </div>
        </form>
    </div>
</div>
