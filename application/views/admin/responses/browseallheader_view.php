<script type='text/javascript'>
    var strdeleteconfirm='<?php $clang->eT('Do you really want to delete this response?', 'js'); ?>';
    var strDeleteAllConfirm='<?php $clang->eT('Do you really want to delete all marked responses?', 'js'); ?>';
    var noFilesSelectedForDeletion = '<?php $clang->eT('Please select at least one file for deletion', 'js'); ?>';
    var noFilesSelectedForDnld = '<?php $clang->eT('Please select at least one file for download', 'js'); ?>';
</script>
<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php $clang->eT("Data view control"); ?></strong></div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <?php if (!isset($_POST['sql']))
                { ?>
                <a href='<?php echo $this->createUrl("admin/responses/sa/browse/surveyid/$surveyid", array('start' =>0,'limit'=>$limit)); ?>'>
                    <img src='<?php echo $sImageURL; ?>databegin.png' alt='<?php $clang->eT("Show start..."); ?>' /></a>
                <a href='<?php echo $this->createUrl("admin/responses/sa/browse/surveyid/$surveyid", array('start' =>$last,'limit'=>$limit)); ?>'>
                    <img src='<?php echo $sImageURL; ?>databack.png' alt='<?php $clang->eT("Show previous.."); ?>' /></a>
                <img src='<?php echo $sImageURL; ?>blank.gif' width='13' height='20' alt='' />

                <a href='<?php echo $this->createUrl("admin/responses/sa/browse/surveyid/$surveyid", array('start' =>$next,'limit'=>$limit)); ?>'>
                    <img src='<?php echo $sImageURL; ?>dataforward.png' alt='<?php $clang->eT("Show next.."); ?>' /></a>
                <a href='<?php echo $this->createUrl("admin/responses/sa/browse/surveyid/$surveyid", array('start' =>$end,'limit'=>$limit)); ?>'>
                    <img src='<?php echo $sImageURL; ?>dataend.png' alt='<?php $clang->eT("Show last.."); ?>' /></a>
                <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />
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
                    <img src='<?php echo $sImageURL; ?>blank.gif' width='31' height='20' alt='' />
                    <?php $clang->eT("Records displayed:"); ?><input type='text' size='4' value='<?php echo $dtcount2; ?>' name='limit' id='limit' />
                    &nbsp;&nbsp; <?php $clang->eT("Starting from:"); ?><input type='text' size='4' value='<?php echo $start; ?>' name='start' id='start' />
                    &nbsp;&nbsp; <input type='submit' value='<?php $clang->eT("Show"); ?>' />
                    &nbsp;&nbsp; <?php $clang->eT("Display:"); ?>
                    <?php echo CHtml::dropDownList('completionstate',$sCompletionStateValue,
                        array('all'=> $clang->gT("All responses"),
                        'complete'=> $clang->gT("Completed responses only"),
                        'incomplete'=> $clang->gT("Incomplete responses only"))
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
        <th><?php $clang->eT('Actions'); ?></th>
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
<img id='imgDeleteMarkedResponses' src='<?php echo $sImageURL; ?>token_delete.png' alt='<?php $clang->eT('Delete marked responses'); ?>' />
<?php } ?>
<?php if ($bHasFileUploadQuestion) { ?>
<img id='imgDownloadMarkedFiles' src='<?php echo $sImageURL; ?>down_all.png' alt='<?php $clang->eT('Download marked files'); ?>' />
<?php } ?>
</td>
    </tr>
</tfoot>
