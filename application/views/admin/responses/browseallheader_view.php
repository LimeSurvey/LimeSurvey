<script type='text/javascript'>
    var strdeleteconfirm='<?php eT('Do you really want to delete this response?', 'js'); ?>';
    var strDeleteAllConfirm='<?php eT('Do you really want to delete all marked responses?', 'js'); ?>';
    var noFilesSelectedForDeletion = '<?php eT('Please select at least one file for deletion', 'js'); ?>';
    var noFilesSelectedForDnld = '<?php eT('Please select at least one file for download', 'js'); ?>';
</script>
<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php eT("Data view control"); ?></strong></div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <?php if (!isset($_POST['sql']))
                { ?>
                <a href='<?php echo $this->createUrl("admin/responses/sa/browse/surveyid/$surveyid", array('start' =>0,'limit'=>$limit)); ?>'>
                    <span class="icon-databegin text-success" title='<?php eT("Show start..."); ?>'></span>
                </a>
                <a href='<?php echo $this->createUrl("admin/responses/sa/browse/surveyid/$surveyid", array('start' =>$last,'limit'=>$limit)); ?>'>
                    <span class="icon-databack text-success" title="<?php eT("Show previous.."); ?>"></span>
                </a>

                <a href='<?php echo $this->createUrl("admin/responses/sa/browse/surveyid/$surveyid", array('start' =>$next,'limit'=>$limit)); ?>'>
                    <span class="icon-dataforward text-success" title="<?php eT("Show next.."); ?>"></span>
                </a>
                <a href='<?php echo $this->createUrl("admin/responses/sa/browse/surveyid/$surveyid", array('start' =>$end,'limit'=>$limit)); ?>'>
                    <span class="icon-dataend text-success" title="<?php eT("Show last.."); ?>"></span>
                </a>

                <?php
                }
                $selectshow = '';
                $selectinc = '';
                $selecthide = '';

                if (incompleteAnsFilterState() == "incomplete")
                {
                    $selectinc = "selected='selected'";
                }
                elseif (incompleteAnsFilterState() == "complete")
                {
                    $selecthide = "selected='selected'";
                }
                else
                {
                    $selectshow = "selected='selected'";
                }
            ?>
            <?php echo CHtml::form(array("admin/responses/sa/browse/surveyid/{$surveyid}/"), 'post', array('id'=>'browseresults')); ?>

                    <?php eT("Records displayed:"); ?><input type='text' size='4' value='<?php echo $dtcount2; ?>' name='limit' id='limit' />
                    &nbsp;&nbsp; <?php eT("Starting from:"); ?><input type='text' size='4' value='<?php echo $start; ?>' name='start' id='start' />
                    &nbsp;&nbsp; <input type='submit' value='<?php eT("Show"); ?>' />
                    &nbsp;&nbsp; <?php eT("Display:"); ?>
                    <?php echo CHtml::dropDownList('completionstate',$sCompletionStateValue,
                        array('all'=> gT("All responses",'unescaped'),
                        'complete'=> gT("Completed responses only",'unescaped'),
                        'incomplete'=> gT("Incomplete responses only",'unescaped'))
                    ); ?>
                <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
                <input type='hidden' name='action' value='browse' />
                <input type='hidden' name='subaction' value='all' />
            </form></div>
    </div>
</div>

<?php
echo CHtml::form(array("admin/responses/sa/browse/surveyid/{$surveyid}/"), 'post', array('id'=>'resulttableform')); ?>
<!-- DATA TABLE -->
<?php if ($fncount < 10) { ?>
    <table class='browsetable' style='width:100%'>
    <?php } else { ?>
    <table class='browsetable'>
    <?php } ?>

<thead>
    <tr>
        <th><input type='checkbox' id='selectall'></th>
        <th><?php eT('Actions'); ?></th>
        <?php
            foreach ($fnames as $fn)
            {
                if (!isset($currentgroup))
                {
                    $currentgroup = $fn[0];
                    $gbc = "odd";
                }
                if ($currentgroup != $fn[0])
                {
                    $currentgroup = $fn[0];
                    if ($gbc == "odd")
                    {
                        $gbc = "even";
                    }
                    else
                    {
                        $gbc = "odd";
                    }
                }
            ?>
            <th class='<?php echo $gbc; ?>'>
                <?php if(isset($fn['code'])){ ?>
                    <strong class="qcode">[<?php echo $fn['code']; ?>]</strong>
                <?php }?>
                <span class="questiontext"><?php echo $fn[1]; ?></span>
            </th>
            <?php } ?>
    </tr>
</thead>
<tfoot>
    <tr>
        <td colspan=<?php echo $fncount + 2; ?>>
<?php if (Permission::model()->hasSurveyPermission($iSurveyId, 'responses', 'delete')) { ?>
<span id='imgDeleteMarkedResponses' class="fa fa-trash text-warning" title="<?php eT('Delete marked responses'); ?>"></span>
<?php } ?>
<?php if ($bHasFileUploadQuestion) { ?>
<span id='imgDownloadMarkedFiles' class="fa fa-download-alt text-success" title="<?php eT('Download marked files'); ?>"></span>
<?php } ?>
</td>
    </tr>
</tfoot>
