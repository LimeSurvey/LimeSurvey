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
            <?php echo CHtml::form(array("admin/questions/sa/import"), 'post', array('id'=>'importquestion', 'class'=>'', 'name'=>'importquestion', 'enctype'=>'multipart/form-data','onsubmit'=>"return window.LS.validatefilename(this, '".gT("Please select a file to import!",'js')."');")); ?>
                <div class="form-group">
                    <label class=" control-label" for='the_file'><?php eT("Select question file (*.lsq):");
                    echo '<br>'.sprintf(gT("(Maximum file size: %01.2f MB)"),getMaximumFileUploadSize()/1024/1024);?>
                    </label>
                    <div class="">
                        <input name='the_file' id='the_file' type="file" required="required" accept=".lsq" />
                    </div>
                </div>
                <div class="form-group">
                    <label class=" control-label" for='the_file'><?php eT("Destination question group:"); ?></label>
                    <div class="">
                        <select name='gid' id='gid' class="form-control">
                            <?php echo getGroupList3($groupid, $surveyid); ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class=" control-label" for='translinksfields'><?php eT("Automatically rename question code if already exists?"); ?></label>
                    <div class="">
                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'name' => 'autorename',
                            'id'=>'autorename',
                            'value' => 1,
                            'onLabel'=>gT('On'),
                            'offLabel' => gT('Off')));
                        ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class=" control-label" for='translinksfields'><?php eT("Convert resource links?"); ?></label>
                    <div class="">
                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'name' => 'translinksfields',
                            'id'=>'translinksfields',
                            'value' => 1,
                            'onLabel'=>gT('On'),
                            'offLabel' => gT('Off')));
                        ?>
                    </div>
                </div>
                <p>
                <input type='submit' class="hidden" value='<?php eT("Import question"); ?>' />
                <input type='hidden' name='action' value='importquestion' />
                <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
            </form>
        </div>
    </div>
</div>







