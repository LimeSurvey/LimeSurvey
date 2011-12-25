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
        <?php if (!isset($_POST['sql']))
        { ?>
            <a href='<?php echo $this->createUrl("admin/browse/surveyid/$surveyid/sa/all/start/0/limit/$limit"); ?>' title='<?php $clang->eTview("Show start..."); ?>' >
                <img name='DataBegin' align='left' src='<?php echo $imageurl; ?>/databegin.png' alt='<?php $clang->eT("Show start..."); ?>' /></a>
            <a href='<?php echo $this->createUrl("admin/browse/surveyid/$surveyid/sa/all/start/$last/limit/$limit"); ?>' title='<?php $clang->eTview("Show previous.."); ?>' >
                <img name='DataBack' align='left'  src='<?php echo $imageurl; ?>/databack.png' alt='<?php $clang->eT("Show previous.."); ?>' /></a>
            <img src='<?php echo $imageurl; ?>/blank.gif' width='13' height='20' border='0' hspace='0' align='left' alt='' />

            <a href='<?php echo $this->createUrl("admin/browse/surveyid/$surveyid/sa/all/start/$next/limit/$limit"); ?>' title='<?php $clang->eT("Show next..."); ?>' >
                <img name='DataForward' align='left' src='<?php echo $imageurl; ?>/dataforward.png' alt='<?php $clang->eT("Show next.."); ?>' /></a>
            <a href='<?php echo $this->createUrl("admin/browse/surveyid/$surveyid/sa/all/start/$end/limit/$limit"); ?>' title='<?php $clang->eT("Show last..."); ?>' >
                <img name='DataEnd' align='left' src='<?php echo $imageurl; ?>/dataend.png' alt='<?php $clang->eT("Show last.."); ?>' /></a>
            <img src='<?php echo $imageurl; ?>/seperator.gif' border='0' hspace='0' align='left' alt='' />
        <?php
        }
        $selectshow = '';
        $selectinc = '';
        $selecthide = '';

        if (incompleteAnsFilterstate() == "inc")
        {
            $selectinc = "selected='selected'";
        }
        elseif (incompleteAnsFilterstate() == "filter")
        {
            $selecthide = "selected='selected'";
        }
        else
        {
            $selectshow = "selected='selected'";
        }
        ?>
        <form action='<?php echo $this->createUrl("admin/browse/surveyid/$surveyid/sa/all/"); ?>' id='browseresults' method='post'><font size='1' face='verdana'>
            <img src='<?php echo $imageurl; ?>/blank.gif' width='31' height='20' border='0' hspace='0' align='right' alt='' />
            <?php $clang->eT("Records displayed:"); ?><input type='text' size='4' value='<?php echo $dtcount2; ?>' name='limit' id='limit' />
            &nbsp;&nbsp; <?php $clang->eT("Starting from:"); ?><input type='text' size='4' value='<?php echo $start; ?>' name='start' id='start' />
            &nbsp;&nbsp; <input type='submit' value='<?php $clang->eT("Show"); ?>' />
            &nbsp;&nbsp; <?php $clang->eT("Display:"); ?> <select name='filterinc' onchange='javascript:submit();'>
                <option value='show'<?php echo $selectshow; ?>><?php $clang->eT("All responses"); ?></option>
                <option value='filter'<?php echo $selecthide; ?>><?php $clang->eT("Completed responses only"); ?></option>
                <option value='incomplete'<?php echo $selectinc; ?>><?php $clang->eT("Incomplete responses only"); ?></option>
            </select>

            </font>
            <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
            <input type='hidden' name='action' value='browse' />
            <input type='hidden' name='subaction' value='all' />

            <?php if (isset($_POST['sql']))
            { ?>
                <input type='hidden' name='sql' value='<?php echo html_escape($_POST['sql']); ?>' />
<?php } ?>
        </form></div>
</div>

<form action='<?php echo $this->createUrl("admin/browse/surveyid/$surveyid/sa/all"); ?>' id='resulttableform' method='post'>

    <!-- DATA TABLE -->
        <?php if ($fncount < 10) { ?>
            <table class='browsetable' width='100%'>
        <?php } else { ?>
            <table class='browsetable'>
        <?php } ?>

            <thead>
                <tr valign='top'>
                    <th><input type='checkbox' id='selectall'></th>
                    <th><?php $clang->eT('Actions'); ?></th>
                    <?php
                    foreach ($fnames as $fn)
                    {
                        if (!isset($currentgroup))
                        {
                            $currentgroup = $fn[1];
                            $gbc = "odd";
                        }
                        if ($currentgroup != $fn[1])
                        {
                            $currentgroup = $fn[1];
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
                        <strong><?php echo FlattenText(strip_javascript($fn[1]), true); ?></strong>
                    </th>
                    <?php } ?>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan=<?php echo $fncount + 2; ?>>
                        <?php if (bHasSurveyPermission($iSurveyId, 'responses', 'delete')) { ?>
                            <img id='imgDeleteMarkedResponses' src='<?php echo Yii::app()->getConfig("imageurl"); ?>/token_delete.png' alt='<?php $clang->eT('Delete marked responses'); ?>' />
                        <?php } ?>
                        <?php if (bHasFileUploadQuestion($iSurveyId)) { ?>
                            <img id='imgDownloadMarkedFiles' src='<?php echo Yii::app()->getConfig("imageurl"); ?>/down_all.png' alt='<?php $clang->eT('Download marked files'); ?>' />
                        <?php } ?>
                    </td>
                </tr>
            </tfoot>
