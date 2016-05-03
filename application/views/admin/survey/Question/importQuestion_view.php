<?php 
/**
 * This view display the page to add a new question to a controller, and to choose its group.
 * TODO : It will have to be merged with other question function such as "edit" or "copy". 
 * 
 */
?>
<div id='edit-question-body' class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3><?php eT("Import a question"); ?></h3>
    <div class="row">
        <div class="col-lg-12">
            <?php echo CHtml::form(array("admin/questions/sa/import"), 'post', array('id'=>'importquestion', 'class'=>'form-horizontal', 'name'=>'importquestion', 'enctype'=>'multipart/form-data','onsubmit'=>"return validatefilename(this, '".gT("Please select a file to import!",'js')."');")); ?>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for='the_file'><?php eT("Select LimeSurvey question file (*.lsq)"); ?>:</label>
                    <div class="col-sm-3">
                        <input name='the_file' class="form-control" id='the_file' type="file" required="required" accept=".lsq,.csv" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for='the_file'><?php eT("Question group:"); ?></label>
                    <div class="col-sm-3">
                        <select name='gid' id='gid' class="form-control">
                            <?php echo getGroupList3($groupid, $surveyid); ?>
                        </select>         
                    </div>           
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for='translinksfields'><?php eT("Convert resource links?"); ?></label>
                    <div class="col-sm-3">
                        <input name='translinksfields' id='translinksfields' type='checkbox' checked='checked'/>
                    </div>
                </div>
                <p>
                <input type='submit' class="hidden" value='<?php eT("Import Question"); ?>' />
                <input type='hidden' name='action' value='importquestion' />
                <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
            </form>            
        </div>
    </div>
</div>







