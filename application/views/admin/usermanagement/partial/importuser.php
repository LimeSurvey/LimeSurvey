<?php
/**
 * Subview: Userimport form csv
 * 
 * @package UserManagement
 * @author Markus Flür <markus.fluer@limesurvey.org>
 * @license GPL3.0
 */
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalTitle-importuser"><?=gT('Import users')?></h4>
</div>
<div class="modal-body">
    <div class="container-center">
    <?=TbHtml::formTb(
        null, 
        App()->createUrl('admin/usermanagement/sa/importcsv'), 
        'post', 
        ["id"=>"UserManagement--modalform--import", 'enctype'=>'multipart/form-data']
    )?>
        <div class="row">
            <div class="col-sm-12 well">
                <?=sprintf(gT("Please make sure that your CSV contains the columns '%s' as well as '%s' and '%s' OR '%s'"), '<b>email</b>','<b>firstname</b>','<b>surname</b>','<b>full_name</b>')?>
            </div>
        </div>
        <div class="row ls-space margin top-5 bottom-5 hidden" id="UserManagement--errors">
        </div>
        <div class="row ls-space margin top-5 bottom-15">
            <label for="the_file"><?=gT('Select CSV file')?></label>
            <input type="file" name="the_file" id="the_file" class="form control"/>
        </div>
        <div class="row ls-space margin top-5">
            <hr class="ls-space margin top-5 bottom-10"/>
        </div>
        <div class="row ls-space margin top-5">
            <button class="btn btn-success col-sm-3 col-xs-5 col-sm-offset-2 col-xs-offset-1" id="submitForm"><?=gT('Save')?></button>
            <button class="btn btn-error col-sm-3 col-xs-5 col-xs-offset-1" id="exitForm"><?=gT('Cancel')?></button>
        </div>
    </form>
    </div>
</div>