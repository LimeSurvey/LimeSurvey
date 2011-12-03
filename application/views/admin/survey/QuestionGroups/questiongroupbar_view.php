<strong><?php echo $clang->gT("Question group"); ?></strong>&nbsp;
<span class='basic'><?php echo $grow['group_name']; ?> (<?php echo $clang->gT("ID"); ?>:<?php echo $gid; ?>)</span>
</div>
<div class='menubar-main'>
    <div class='menubar-left'>

        <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/blank.gif' alt='' width='54' height='20'  />

        <?php if(bHasSurveyPermission($surveyid,'surveycontent','update'))
            { ?>
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' alt=''  />
            <a href="#" onclick="window.open('<?php echo $this->createUrl("survey/sa/previewgroup/surveyid/$surveyid/gid/$gid/");?>','_blank')"
                title="<?php echo $clang->gTview("Preview current question group"); ?>">
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/preview.png' alt='<?php echo $clang->gT("Preview current question group"); ?>' name='PreviewGroup' width="40" height="40"/></a>
            <?php }
            else{ ?>
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' alt=''  />
            <?php } ?>

        <?php if(bHasSurveyPermission($surveyid,'surveycontent','update'))
            { ?>
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' alt=''  />
            <a href="#" onclick="window.open('<?php echo $this->createUrl('admin/questiongroup/sa/edit/surveyid/'.$surveyid.'/gid/'.$gid); ?>','_top')"
                title="<?php echo $clang->gTview("Edit current question group"); ?>">
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/edit.png' alt='<?php echo $clang->gT("Edit current question group"); ?>' name='EditGroup' width="40" height="40"/></a>
            <?php } ?>


        <?php
            if (bHasSurveyPermission($surveyid,'surveycontent','delete'))
            {
                if ((($sumcount4 == 0 && $activated != "Y") || $activated != "Y"))
                {
                    if (is_null($condarray))
                    { ?>

                    <a href='#' onclick="if (confirm('<?php echo $clang->gT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js"); ?>')) { <?php echo $this->createUrl("admin/questiongroup/sa/delete/surveyid/$surveyid/gid/$gid"); ?>}"
                        title="<?php echo $clang->gTview("Delete current question group"); ?>">
                        <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/delete.png' alt='<?php echo $clang->gT("Delete current question group"); ?>' name='DeleteWholeGroup' title='' width="40" height="40"/></a>

                    <?php }
                    else
                    // TMSW Conditions->Relevance:  Should be allowed to delete group even if there are conditions/relevance, since separate view will show exceptions

                    { ?>
                    <a href='<?php echo $this->createUrl("admin/questiongroup/sa/view/surveyid/$surveyid/gid/$gid"); ?>' onclick="alert('<?php echo $clang->gT("Impossible to delete this group because there is at least one question having a condition on its content","js"); ?>')"
                        title="<?php echo $clang->gTview("Delete current question group"); ?>">
                        <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/delete_disabled.png' alt='<?php echo $clang->gT("Delete current question group"); ?>' name='DeleteWholeGroup' width="40" height="40"/></a>
                    <?php }
                }
                else
                { ?>
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/blank.gif' alt='' width='40' />
                <?php }
            }
            if(bHasSurveyPermission($surveyid,'surveycontent','export'))
            { ?>

            <a href='<?php echo $this->createUrl("admin/export/sa/group/surveyid/$surveyid/gid/$gid");?>' title="<?php echo $clang->gTview("Export this question group"); ?>" >
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/dumpgroup.png' title='' alt='<?php echo $clang->gT("Export this question group"); ?>' name='ExportGroup' width="40" height="40"/></a>
            <?php } ?>
    </div>
    <div class='menubar-right'>
        <label for="questionid"><?php echo $clang->gT("Questions:"); ?></label> <select class="listboxquestions" name='questionid' id='questionid'
            onchange="window.open(this.options[this.selectedIndex].value, '_top')">

            <?php echo getQuestions($surveyid,$gid,$qid); ?>
        </select>




        <span class='arrow-wrapper'>
            <?php if ($QidPrev != "")
                { ?>

                <a href='<?php echo $this->createUrl("admin/survey/sa/view/surveyid/".$surveyid."/gid/".$gid."/qid/".$QidPrev); ?>'>
                    <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/previous_20.png' title='' alt='<?php echo $clang->gT("Previous question"); ?>'
                        name='questionprevious' width="20" height="20"/></a>
                <?php }
                else
                { ?>

                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/previous_disabled_20.png' title='' alt='<?php echo $clang->gT("No previous question"); ?>'
                    name='noquestionprevious' width="20" height="20"/>
                <?php } ?>



            <?php if ($QidNext != "")
                { ?>

                <a href='<?php echo $this->createUrl("admin/survey/sa/view/surveyid/".$surveyid."/gid/".$gid."/qid/".$QidNext); ?>'>
                    <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/next_20.png' title='' alt='<?php echo $clang->gT("Next question"); ?>'
                    name='questionnext' width="20" height="20"/> </a>
                <?php }
                else
                { ?>

                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/next_disabled_20.png' title='' alt='<?php echo $clang->gT("No next question"); ?>'
                    name='noquestionnext' width="20" height="20"/>
                <?php } ?>
        </span>

        <?php if ($activated == "Y")
            { ?>
            <a href='#'>
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/add_disabled.png' title='' alt='<?php echo $clang->gT("Disabled").' - '.$clang->gT("This survey is currently active."); ?>'
                    name='AddNewQuestion' onclick="window.open('', '_top')" width="40" height="40"/></a>
            <?php }
            elseif(bHasSurveyPermission($surveyid,'surveycontent','create'))
            { ?>
            <a href='<?php echo $this->createUrl("admin/question/sa/addquestion/surveyid/".$surveyid."/gid/".$gid); ?>'
                title="<?php echo $clang->gTview("Add new question to group"); ?>" >
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/add.png' title='' alt='<?php echo $clang->gT("Add New Question to Group"); ?>'
                    name='AddNewQuestion' onclick="window.open('', '_top')" width="40" height="40"/></a>
            <?php } ?>

        <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' alt=''  />

        <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/blank.gif' width='18' alt='' />
        <input id='MinimizeGroupWindow' type='image' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/minus.gif' title='<?php echo $clang->gT("Hide Details of this Group"); ?>' alt='<?php echo $clang->gT("Hide details of thisgroup"); ?>' name='MinimizeGroupWindow' />
        <input type='image' id='MaximizeGroupWindow' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/plus.gif' title='<?php echo $clang->gT("Show Details of this Group"); ?>' alt='<?php echo $clang->gT("Show details of this group"); ?>' name='MaximizeGroupWindow' />
        <?php if (!$qid)
            { ?>
            <input type='image' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/close.gif' title='<?php echo $clang->gT("Close this Group"); ?>' alt='<?php echo $clang->gT("Close this Group"); ?>'  name='CloseSurveyWindow'
                onclick="window.open('<?php echo $this->createUrl("admin/survey/sa/view/surveyid/".$surveyid); ?>', '_top')" />
            <?php }
            else
            { ?>
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/blank.gif' alt='' width='18' />
            <?php } ?>
    </div></div>
</div>




<table id='groupdetails' <?php echo $gshowstyle; ?> >
<tr ><td width='20%' align='right'><strong>
            <?php echo $clang->gT("Title"); ?>:</strong></td>
    <td align='left'>
        <?php echo $grow['group_name']; ?> (<?php echo $grow['gid']; ?>)</td>
</tr>
<tr><td valign='top' align='right'><strong>
        <?php echo $clang->gT("Description:"); ?></strong>
    </td>
    <td align='left'>
        <?php if (trim($grow['description'])!='') {
                templatereplace($grow['description']);
                echo LimeExpressionManager::GetLastPrettyPrintExpression();
        } ?>
    </td>
</tr>
<?php
    if (trim($grow['randomization_group'])!='')
    {?>
    <tr>
        <td><?php echo $clang->gT("Randomization group:"); ?></td><td><?php echo $grow['randomization_group'];?></td>
    </tr>
    <?php
    }
    // TMSW Conditions->Relevance:  Use relevance equation or different EM query to show dependencies
    if (!is_null($condarray))
    { ?>
    <tr><td align='right'><strong>
                <?php echo $clang->gT("Questions with conditions to this group"); ?>:</strong></td>
        <td valign='bottom' align='left'>
            <?php foreach ($condarray[$gid] as $depgid => $deprow)
                {
                    foreach ($deprow['conditions'] as $depqid => $depcid)
                    {

                        $listcid=implode("-",$depcid);?>
                    <a href='#' onclick="window.open('<?php echo $this->createUrl("admin/conditions/markcid/" . implode("-",$depcid) . "/surveyid/$surveyid/gid/$depgid/qid/$depqid"); ?>','_top')">[QID: <?php echo $depqid; ?>]</a>
                    <?php }
            } ?>
        </td></tr>
    <?php } ?>