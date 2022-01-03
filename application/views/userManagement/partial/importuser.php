<?php
/**
 * Subview: Userimport form 
 * 
 * @package UserManagement
 * @author LimeSurvey GmbH <info@limesurvey.org>
 * @license GPL3.0
 */
?>

<?php
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => gT('Import users')]
);
?>

<?=TbHtml::formTb(
    null,
    App()->createUrl('userManagement/importUsers',['importFormat' => $importFormat]),
    'post',
    ["id"=>"UserManagement--modalform--import", 'enctype'=>'multipart/form-data']
)?>

<div class="modal-body">
    <div class="container-center">
        <div class="row">
            <div class="col-sm-12 well">
                <?=$note?>
            </div>
        </div>
        <div class="row ls-space margin top-5 bottom-5 hidden" id="UserManagement--errors">
        </div>
        <div class="row ls-space margin top-5 bottom-5" >
            <input type="checkbox" name="overwrite" value="overwrite"> <?= eT("Overwrite existing users")?>
        </div>
        <hr>
        
        <div class="row ls-space margin top-5 bottom-15">
            <label for="the_file"><?=gT('Select '.$importFormat.' file')?></label>
            <input type="file" accept="<?=$allowFile?>" name="the_file" id="the_file" class="form control" required/>
        </div>
        <div class="row ls-space margin top-5">
            <hr class="ls-space margin top-5 bottom-10"/>
        </div>
    </div>
</div>

<div class="modal-footer modal-footer-buttons">
    <button class="btn btn-cancel" id="exitForm"><?=gT('Cancel')?></button>
    <button class="btn btn-success " id="submitForm"><?=gT('Import')?></button>
</div>
</form>
