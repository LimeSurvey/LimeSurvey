<?php
/**
 * This view display the page to add a new question to a controller, and to choose its group.
 * TODO : It will have to be merged with other question function such as "edit" or "copy".
 *
 * @var $this QuestionAdministrationController
 * @var $gid int|null groupID
 * @var $sid int surveyID
 */

?>
<div id='edit-question-body' class='side-body'>
    <div class="pagetitle h1"><?php eT("Import a question"); ?></div>
    <div class="row">
        <div class="col-6">
            <?php echo CHtml::form(
                ["questionAdministration/import"],
                'post',
                [
                    'id'       => 'importquestion',
                    'class'    => '',
                    'name'     => 'importquestion',
                    'enctype'  => 'multipart/form-data',
                    'onsubmit' => "return window.LS.validatefilename(this, '" . gT("Please select a file to import!", 'js') . "');"
                ]
            ); ?>
            <div class="mb-3">
                <label class="form-label" for='the_file'><?php eT("Select question file (*.lsq):");
                    echo '<br>' . sprintf(gT("(Maximum file size: %01.2f MB)"), getMaximumFileUploadSize() / 1024 / 1024); ?>
                </label>
                <input name='the_file' id='the_file' class="form-control" type="file" required="required" accept=".lsq"/>
            </div>
            <div class="mb-3">
                <label class=" form-label" for='gid'><?php eT("Destination question group:"); ?></label>
                <div class="">
                    <select name='gid' id='gid' class="form-select">
                        <?php echo getGroupList3($gid, $sid); ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class=" form-label" for='autorename'><?php eT("Automatically rename question code if already exists?"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'autorename',
                        'checkedOption' => 1,
                        'selectOptions' => [
                            '1' => gT('On'),
                            '0' => gT('Off'),
                        ],
                    ]); ?>
                </div>
            </div>
            <div class="mb-3">
                <label class=" form-label" for='translinksfields'><?php eT("Convert resource links?"); ?></label>
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
            <div class="mb-3">
                <label class=" form-label" for='jumptoquestion'><?php eT("Jump to question after import?"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'jumptoquestion',
                        'id'            => 'jumptoquestion',
                        'checkedOption' => 1,
                        'selectOptions' => [
                            '1' => gT('On'),
                            '0' => gT('Off'),
                        ],
                    ]); ?>
                </div>
            </div>
            <input type='submit' class="d-none" value='<?php eT("Import question"); ?>'/>
            <input type='hidden' name='action' value='importquestion'/>
            <input type='hidden' name='sid' value='<?php echo $sid; ?>'/>
            <?php echo CHtml::endForm(); ?>
        </div>
    </div>
</div>







