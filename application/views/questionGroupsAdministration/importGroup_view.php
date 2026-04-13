<?php
/**
 * Import a group view
 *
 * @var QuestionGroupsAdministrationController $this
 * @var int $surveyid
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('importQuestionGroup');
?>
<div id='edit-survey-text-element' class='side-body'>
    <div class="pagetitle h1"><?php eT("Import question group"); ?></div>
    <div class="row">
        <div class="col-6">
            <!-- form -->
            <?php echo CHtml::form(array("questionGroupsAdministration/import"), 'post', array(
                'id'=>'importgroup',
                'name'=>'importgroup',
                'class'=>'form30 ',
                'enctype'=>'multipart/form-data',
                'onsubmit'=>'return window.LS.validatefilename(this,"'.gT('Please select a file to import!','js').'");'
            )); ?>

                <!-- Select question group file -->
                <div class="mb-3">
                    <label for='the_file' class=" form-label"><?php eT("Select question group file (*.lsg):");
                    echo '<br>'.sprintf(gT("(Maximum file size: %01.2f MB)"),getMaximumFileUploadSize()/1024/1024);
                    ?></label>
                        <div class="">
                           <input id='the_file' class="form-control" name="the_file" type="file" accept='.lsg' />
                        </div>
                </div>

                <!-- Convert resource links -->
                <div class="mb-3">
                    <label for='translinksfields' class="form-label"><?php eT("Convert resource links?"); ?></label>
                    <div>
                        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                            'name'          => 'translinksfields',
                            'checkedOption' => 1,
                            'selectOptions' => [
                                '1' => gT('On'),
                                '0' => gT('Off'),
                            ],
                        ]); ?>
                    </div>
                </div>

                <input type='submit' class="d-none" value='<?php eT("Import question group"); ?>' />
                <input type='hidden' name='action' value='importgroup' />
                <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
            </form>
        </div>
    </div>
</div>
