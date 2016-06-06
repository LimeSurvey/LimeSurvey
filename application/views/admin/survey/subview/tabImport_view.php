<?php
/**
 * Import survey
 */
?>
<!-- tab import survey -->
    <!-- import form -->
    <?php echo CHtml::form(array('admin/survey/sa/copy'), 'post', array('id'=>'importsurvey', 'name'=>'importsurvey', 'class'=>'form-horizontal', 'enctype'=>'multipart/form-data', 'onsubmit'=>'return validatefilename(this,"'. gT('Please select a file to import!', 'js').'");')); ?>
        <div class="row">

            <!-- Select file -->
            <div class='form-group '>
                <label class='control-label col-sm-3' for='the_file'>
                    <?php printf(gT("Select survey structure file (*.lss, *.txt) or survey archive (*.lsa) (maximum file size: %01.2f MB)"),getMaximumFileUploadSize()/1024/1024); ?>
                </label>
                <div class='col-sm-4'>
                    <input id='the_file' name="the_file" type="file" accept='.lss,.lsa,.tsv,.txt'/>
                </div>
            </div>

            <!-- Convert resource links and INSERTANS fields? -->
            <div class='form-group'>
                <label class='control-label col-sm-3' for='translinksfields'><?php  eT("Convert resource links and expression fields?"); ?> </label>
                <div class='col-sm-4'>
                <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                    'name' => 'translinksfields',
                    'value'=> "1",
                    'onLabel'=>gT('On'),
                    'offLabel'=>gT('Off')
                    ));
                ?>
                </div>
            </div>

            <!-- Submit -->
            <div class='form-group'>
                <div class='col-sm-4 col-sm-offset-3'>
                    <input type='submit' class="btn btn-default" value='<?php  eT("Import survey"); ?>' />
                </div>
            </div>

            <?php if (isset($surveyid)) echo '<input type="hidden" name="sid" value="'.$surveyid.'" />'; ?>
            <input type='hidden' name='action' value='importsurvey' />
        </div>
    </form>

    <div id='pleaseselectfile-popup' class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php eT("No file selected"); ?></h4>
                </div>
                <div class="modal-body">
                    <p><?php eT("Please select a file to import!"); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT("Close");?></button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
