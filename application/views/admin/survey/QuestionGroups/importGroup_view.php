<?php
/**
 * Import a group view
 */
?>

<div id='edit-survey-text-element' class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3><?php eT("Import question group"); ?></h3>
    <div class="row">
        <div class="col-lg-12">
            <!-- form -->
            <?php echo CHtml::form(array("admin/questiongroups/sa/import"), 'post', array('id'=>'importgroup', 'name'=>'importgroup', 'class'=>'form30 form-horizontal', 'enctype'=>'multipart/form-data', 'onsubmit'=>'return validatefilename(this,"'.gT('Please select a file to import!','js').'");')); ?>

                <!-- Select question group file -->
                <div class="form-group">
                    <label for='the_file' class="col-sm-2 control-label"><?php eT("Select question group file (*.lsg):");
                    echo '<br>'.sprintf(gT("(Maximum file size: %01.2f MB)"),getMaximumFileUploadSize()/1024/1024);
                    ?></label>
                        <div class="col-sm-3">
                           <input id='the_file' name="the_file" type="file" accept='.lsg' />
                        </div>
                </div>

                <!-- Convert resource links -->
                <div class="form-group">
                    <label for='translinksfields' class="col-sm-2 control-label"><?php eT("Convert resource links?"); ?></label>
                    <div class="col-sm-10">
                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'name' => 'translinksfields',
                            'id'=>'translinksfields',
                            'value' => 1,
                            'onLabel'=>gT('On'),
                            'offLabel' => gT('Off')));
                        ?>
                    </div>
                </div>

                <input type='submit' class="hidden" value='<?php eT("Import question group"); ?>' />
                <input type='hidden' name='action' value='importgroup' />
                <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
            </form>
        </div>
    </div>
</div>
