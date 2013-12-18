<strong><?php $clang->eT("Question group"); ?></strong>&nbsp;
<span class='basic'><?php echo $grow['group_name']; ?> (<?php $clang->eT("ID"); ?>:<?php echo $gid; ?>)</span>
</div>
<div class='menubar-main'>
    <div class='menubar-left'>


        <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update'))
            { ?>
            <img id='separator4' src='<?php echo $imageurl; ?>separator.gif' class='separator' alt=''  />
            <a id="grouppreviewlink" href="<?php echo $this->createUrl("survey/index/action/previewgroup/sid/$surveyid/gid/$gid/"); ?>" target="_blank">
                <img src='<?php echo $imageurl; ?>preview.png' alt='<?php $clang->eT("Preview current question group"); ?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a>
                <?php if (count($languagelist) > 1)
                { ?>
                <div class="popuptip" rel="grouppreviewlink"><?php $clang->eT("Preview this question group in:"); ?>
                    <ul>
                    <?php foreach ($languagelist as $tmp_lang){ ?>
                        <li><a target="_blank" href="<?php echo $this->createUrl("survey/index/action/previewgroup/sid/{$surveyid}/gid/{$gid}/lang/" . $tmp_lang); ?>" ><?php echo getLanguageNameFromCode($tmp_lang,false); ?></a></li>
                    <?php } ?>
                    </ul>
                </div>
                <?php } ?>
            <?php }
            else{ ?>
            <img src='<?php echo $imageurl; ?>separator.gif' class='separator' alt=''  />
            <?php } ?>

        <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update'))
            { ?>
            <img id='separator5' src='<?php echo $imageurl; ?>separator.gif' class='separator' alt=''  />
            <a href="<?php echo $this->createUrl('admin/questiongroups/sa/edit/surveyid/'.$surveyid.'/gid/'.$gid); ?>">
                <img src='<?php echo $imageurl; ?>edit.png' alt='<?php $clang->eT("Edit current question group"); ?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a>
            <?php } ?>

        <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read'))
            { ?>
            <img id='separator6' src='<?php echo $imageurl; ?>separator.gif' class='separator' alt=''  />
            <a  target='_blank' href="<?php echo $this->createUrl("admin/expressions/sa/survey_logic_file/sid/{$surveyid}/gid/{$gid}/"); ?>">
            <img src='<?php echo $imageurl; ?>quality_assurance.png' alt='<?php $clang->eT("Check survey logic for current question group"); ?>' /></a>
            <?php } ?>

        <?php
            if (Permission::model()->hasSurveyPermission($surveyid,'surveycontent','delete'))
            {
                if ((($sumcount4 == 0 && $activated != "Y") || $activated != "Y"))
                {
                    if (is_null($condarray))
                    { ?>

                    <a href='#' onclick="if (confirm('<?php $clang->eT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js"); ?>')) { window.open('<?php echo $this->createUrl("admin/questiongroups/sa/delete/surveyid/$surveyid/gid/$gid"); ?>','_top'); }">
                        <img src='<?php echo $imageurl; ?>delete.png' alt='<?php $clang->eT("Delete current question group"); ?>' title='' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a>

                    <?php }
                    else
                    // TMSW Condition->Relevance:  Should be allowed to delete group even if there are conditions/relevance, since separate view will show exceptions

                    { ?>
                    <a href='<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid/gid/$gid"); ?>' onclick="alert('<?php $clang->eT("Impossible to delete this group because there is at least one question having a condition on its content","js"); ?>'); return false;">
                        <img src='<?php echo $imageurl; ?>delete_disabled.png' alt='<?php $clang->eT("Delete current question group"); ?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a>
                    <?php }
                }
                else
                { ?><img src='<?php echo $imageurl; ?>blank.gif' alt='' height="<?php echo $iIconSize;?>"  width="<?php echo $iIconSize;?>" /><?php }
            }
            if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','export'))
            { ?>

            <a href='<?php echo $this->createUrl("admin/export/sa/group/surveyid/$surveyid/gid/$gid");?>'>
                <img src='<?php echo $imageurl; ?>dumpgroup.png' title='' alt='<?php $clang->eT("Export this question group"); ?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>"/></a>
            <?php } ?>
    </div>
    <div class='menubar-right'>
        <label for="questionid"><?php $clang->eT("Questions:"); ?></label> <select class="listboxquestions" name='questionid' id='questionid'
            onchange="window.open(this.options[this.selectedIndex].value, '_top')">

            <?php echo getQuestions($surveyid,$gid,$qid); ?>
        </select>




        <span class='arrow-wrapper'>
            <?php if ($QidPrev != "")
                { ?>

                <a href='<?php echo $this->createUrl("admin/survey/sa/view/surveyid/".$surveyid."/gid/".$gid."/qid/".$QidPrev); ?>'>
                    <img src='<?php echo $imageurl; ?>previous_20.png' title='' alt='<?php $clang->eT("Previous question"); ?>'/></a>
                <?php }
                else
                { ?>

                <img src='<?php echo $imageurl; ?>previous_disabled_20.png' title='' alt='<?php $clang->eT("No previous question"); ?>'/>
                <?php } ?>



            <?php if ($QidNext != "")
                { ?>

                <a href='<?php echo $this->createUrl("admin/survey/sa/view/surveyid/".$surveyid."/gid/".$gid."/qid/".$QidNext); ?>'>
                <img src='<?php echo $imageurl; ?>next_20.png' title='' alt='<?php $clang->eT("Next question"); ?>'/> </a>
                <?php }
                else
                { ?>

                <img src='<?php echo $imageurl; ?>next_disabled_20.png' title='' alt='<?php $clang->eT("No next question"); ?>'/>
                <?php } ?>
        </span>

        <?php if ($activated == "Y")
            { ?>
            <a href='#'>
                <img src='<?php echo $imageurl; ?>add_disabled.png' title='' alt='<?php echo $clang->gT("Disabled").' - '.$clang->gT("This survey is currently active."); ?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>" /></a>
            <?php }
            elseif(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','create'))
            { ?>
            <a href='<?php echo $this->createUrl("admin/questions/sa/addquestion/surveyid/".$surveyid."/gid/".$gid); ?>'>
                <img src='<?php echo $imageurl; ?>add.png' title='' alt='<?php $clang->eT("Add new question to group"); ?>' width="<?php echo $iIconSize;?>" height="<?php echo $iIconSize;?>" /></a>
            <?php } ?>

        <img id='separator12' src='<?php echo $imageurl; ?>separator.gif' class='separator' alt=''  />

        <input id='MinimizeGroupWindow' type='image' src='<?php echo $imageurl; ?>minimize.png' title='<?php $clang->eT("Hide details of this group"); ?>' alt='<?php $clang->eT("Hide details of this group"); ?>' />
        <input type='image' id='MaximizeGroupWindow' src='<?php echo $imageurl; ?>maximize.png' title='<?php $clang->eT("Show details of this group"); ?>' alt='<?php $clang->eT("Show details of this group"); ?>' />
        <input type='image' src='<?php echo $imageurl; ?>close.png' title='<?php $clang->eT("Close this group"); ?>' alt='<?php $clang->eT("Close this group"); ?>'
            onclick="window.open('<?php echo $this->createUrl("admin/survey/sa/view/surveyid/".$surveyid); ?>','_top');"
            <?php if (!$qid){?>
                style='visibility:hidden;'
                <?php } ?>
            >
    </div></div>
</div>




<table id='groupdetails' <?php echo $gshowstyle; ?> >
<tr ><td ><strong>
            <?php $clang->eT("Title"); ?>:</strong></td>
    <td>
        <?php echo $grow['group_name']; ?> (<?php echo $grow['gid']; ?>)</td>
</tr>
<tr>
    <td><strong>
        <?php $clang->eT("Description:"); ?></strong>
    </td>
    <td>
        <?php if (trim($grow['description'])!='') {
                templatereplace($grow['description']);
                echo LimeExpressionManager::GetLastPrettyPrintExpression();
        } ?>
    </td>
</tr>
<?php if (trim($grow['grelevance'])!='') { ?>
    <tr>
        <td><strong>
            <?php $clang->eT("Relevance:"); ?></strong>
        </td>
        <td>
            <?php
                templatereplace('{' . $grow['grelevance'] . '}');
                echo LimeExpressionManager::GetLastPrettyPrintExpression();
            ?>
        </td>
    </tr>
    <?php } ?>
<?php
    if (trim($grow['randomization_group'])!='')
    {?>
    <tr>
        <td><?php $clang->eT("Randomization group:"); ?></td><td><?php echo $grow['randomization_group'];?></td>
    </tr>
    <?php
    }
    // TMSW Condition->Relevance:  Use relevance equation or different EM query to show dependencies
    if (!is_null($condarray))
    { ?>
    <tr><td><strong>
                <?php $clang->eT("Questions with conditions to this group"); ?>:</strong></td>
        <td>
            <?php foreach ($condarray[$gid] as $depgid => $deprow)
                {
                    foreach ($deprow['conditions'] as $depqid => $depcid)
                    {

                        $listcid=implode("-",$depcid);?>
                    <a href='<?php echo $this->createUrl("admin/conditions/sa/index/subaction/conditions/surveyid/$surveyid/gid/$depgid/qid/$depqid",array('markcid'=>implode("-",$depcid))); ?>'>[QID: <?php echo $depqid; ?>]</a>
                    <?php }
            } ?>
        </td></tr>
    <?php } ?>
