<strong><?php echo $clang->gT("Question group"); ?></strong>&nbsp;
            <span class='basic'><?php echo $grow['group_name']; ?> (<?php echo $clang->gT("ID"); ?>:<?php echo $gid; ?>)</span>
            </div>
            <div class='menubar-main'>
            <div class='menubar-left'>
    
            <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='54' height='20'  />
    
            <?php if(bHasSurveyPermission($surveyid,'surveycontent','update'))
            { ?>
                 <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt=''  />
                 <a href="#" onclick="window.open('$scriptname?action=previewgroup&amp;sid=$surveyid&amp;gid=$gid','_blank')"
                 title="<?php echo $clang->gTview("Preview current question group"); ?>">
                 <img src='<?php echo $this->config->item('imageurl'); ?>/preview.png' alt='<?php echo $clang->gT("Preview current question group"); ?>' name='PreviewGroup' /></a>
            <?php }
            else{ ?>
                 <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt=''  />
            <?php } ?>
    
    
    
            <?php if(bHasSurveyPermission($surveyid,'surveycontent','update'))
            { ?>
                 <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt=''  />
                <a href="#" onclick="window.open('<?php echo site_url('admin/questiongroup/edit/'.$surveyid.'/'.$gid); ?>','_top')"
                 title="<?php echo $clang->gTview("Edit current question group"); ?>">
                <img src='<?php echo $this->config->item('imageurl'); ?>/edit.png' alt='<?php echo $clang->gT("Edit current question group"); ?>' name='EditGroup' /></a>
            <?php } ?>
    
    
            <?php 
            if (bHasSurveyPermission($surveyid,'surveycontent','delete'))
            {
                if ((($sumcount4 == 0 && $activated != "Y") || $activated != "Y"))
                {
                    if (is_null($condarray))
                    { ?>
                        
                        <a href='#' onclick="if (confirm('<?php echo $clang->gT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js"); ?>')) {<?php echo get2post(site_url('admin/questiongroup/delete')."?action=delgroup&amp;sid=$surveyid&amp;gid=$gid"); ?>}"
                         title="<?php echo $clang->gTview("Delete current question group"); ?>">
                        <img src='<?php echo $this->config->item('imageurl'); ?>/delete.png' alt='<?php echo $clang->gT("Delete current question group"); ?>' name='DeleteWholeGroup' title=''  /></a>
                        
                    <?php }
                    else
                    { ?>
                        <a href='$scriptname?sid=$surveyid&amp;gid=$gid' onclick="alert('<?php echo $clang->gT("Impossible to delete this group because there is at least one question having a condition on its content","js"); ?>')"
                         title="<?php echo $clang->gTview("Delete current question group"); ?>">
                        <img src='<?php echo $this->config->item('imageurl'); ?>/delete_disabled.png' alt='<?php echo $clang->gT("Delete current question group"); ?>' name='DeleteWholeGroup' /></a>
                    <?php }
                }
                else
                { ?>
                    "<img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='40' />
                <?php }
            }
    
    
            
    
            if(bHasSurveyPermission($surveyid,'surveycontent','export'))
            { ?>
    
                <a href='$scriptname?action=exportstructureGroup&amp;sid=$surveyid&amp;gid=$gid' title="<?php echo $clang->gTview("Export this question group"); ?>" >
                <img src='<?php echo $this->config->item('imageurl'); ?>/dumpgroup.png' title='' alt='<?php echo $clang->gT("Export this question group"); ?>' name='ExportGroup'  /></a>
            <?php }
    
    
            
    
            if(bHasSurveyPermission($surveyid,'surveycontent','update'))
            { ?>
                <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt='' />
                <?php if($activated!="Y" && getQuestionSum($surveyid, $gid)>1)
                { ?>
    
                      <a href='$scriptname?action=orderquestions&amp;sid=$surveyid&amp;gid=$gid' title="<?php echo $clang->gTview("Change Question Order"); ?>" >
                      <img src='<?php echo $this->config->item('imageurl'); ?>/reorder.png' alt='<?php echo $clang->gT("Change Question Order"); ?>' name='updatequestionorder' /></a>
                <?php }
                else
                { ?>
                    <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='40' />
                <?php }
            } ?>
    
            </div>
            <div class='menubar-right'>
            <span class="boxcaption"><?php echo $clang->gT("Questions"); ?>:</span><select class="listboxquestions" name='qid' 
            onchange="window.open(this.options[this.selectedIndex].value, '_top')">
            <?php echo getQuestions($surveyid,$gid,$qid); ?>
            </select>
    
    
             
            
            <span class='arrow-wrapper'>
            <?php if ($QidPrev != "")
            { ?>
              
                <a href='<?php site_url("admin/survey/view/".$surveyid."/".$gid."/".$QidPrev); ?>'>
                <img src='<?php echo $this->config->item('imageurl'); ?>/previous_20.png' title='' alt='<?php echo $clang->gT("Previous question"); ?>'
                name='questiongroupprevious'/></a>
            <?php }
            else
            { ?>
              
                <img src='<?php echo $this->config->item('imageurl'); ?>/previous_disabled_20.png' title='' alt='<?php echo $clang->gT("No previous question"); ?>'
                name='noquestionprevious' />
            <?php } ?>
    
    
            
            <?php if ($QidNext != "")
            { ?>
              
                <a href='<?php site_url("admin/survey/view/".$surveyid."/".$gid."/".$QidNext); ?>'>
                <img src='<?php echo $this->config->item('imageurl'); ?>/next_20.png' title='' alt='<?php echo $clang->gT("Next question"); ?>'
                name='questiongroupnext'<?php echo $clang->gT("Next question"); ?>/> </a>
            <?php }
            else
            { ?>
              
                <img src='<?php echo $this->config->item('imageurl'); ?>/next_disabled_20.png' title='' alt='<?php echo $clang->gT("No next question"); ?>'
                name='noquestionnext' />
            <?php } ?>
            </span>
    
    
    
            
    
            <?php if ($activated == "Y")
            { ?>
                <a href='#'>
                <img src='<?php echo $this->config->item('imageurl'); ?>/add_disabled.png' title='' alt='<?php echo $clang->gT("Disabled").' - '.$clang->gT("This survey is currently active."); ?>' 
                name='AddNewQuestion' onclick="window.open('', '_top')" /></a>
            <?php }
            elseif(bHasSurveyPermission($surveyid,'surveycontent','create'))
            { ?>
                <a href='<?php echo site_url("admin/question/newquestion/".$surveyid."/".$gid); ?>'
                title="<?php echo $clang->gTview("Add New Question to Group"); ?>" >
                <img src='<?php echo $this->config->item('imageurl'); ?>/add.png' title='' alt='<?php echo $clang->gT("Add New Question to Group"); ?>'
                name='AddNewQuestion' onclick="window.open('', '_top')" /></a>
            <?php } ?>
    
    
            
    
            <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt=''  />
    
            <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' width='18' alt='' />
            <input id='MinimizeGroupWindow' type='image' src='<?php echo $this->config->item('imageurl'); ?>/minus.gif' title='
            <?php echo $clang->gT("Hide Details of this Group"); ?>' alt='<?php echo $clang->gT("Hide Details of this Group"); ?>' name='MinimizeGroupWindow' />
            <input type='image' id='MaximizeGroupWindow' src='<?php echo $this->config->item('imageurl'); ?>/plus.gif' title='
            <?php echo $clang->gT("Show Details of this Group"); ?>' alt='<?php echo $clang->gT("Show Details of this Group"); ?>' name='MaximizeGroupWindow' />
            <?php if (!$qid)
            { ?>
                <input type='image' src='<?php echo $this->config->item('imageurl'); ?>/close.gif' title='
                <?php echo $clang->gT("Close this Group"); ?>' alt='<?php echo $clang->gT("Close this Group"); ?>'  name='CloseSurveyWindow'
                onclick="window.open('<?php echo site_url("admin/survey/view/".$surveyid); ?>', '_top')" />
            <?php }
            else 
            { ?>
                <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='18' />
            <?php } ?>
            </div></div>
            </div>
            
    
            
    
            <table id='groupdetails' <?php echo $gshowstyle; ?> ><tr ><td width='20%' align='right'><strong>
            <?php echo $clang->gT("Title"); ?>:</strong></td>
            <td align='left'>
            <?php echo $grow['group_name']; ?> (<?php echo $grow['gid']; ?>)</td></tr>
            <tr><td valign='top' align='right'><strong>
            <?php echo $clang->gT("Description:"); ?></strong></td><td align='left'>
            <?php if (trim($grow['description'])!='') { echo $grow['description'];} ?>
            </td></tr>
    
            <?php if (!is_null($condarray))
            { ?>
                <tr><td align='right'><strong>
                <?php echo $clang->gT("Questions with conditions to this group"); ?>:</strong></td>
                <td valign='bottom' align='left'>
                <?php foreach ($condarray[$gid] as $depgid => $deprow)
                {
                    foreach ($deprow['conditions'] as $depqid => $depcid)
                    { ?>
                        
                        $listcid=implode("-",$depcid);
                        <a href='#' onclick="window.open('admin.php?sid=<?php echo $surveyid; ?>&amp;gid=<?php echo $depgid; ?>&amp;qid=<?php echo $depqid; ?>&amp;action=conditions&amp;markcid=<?php echo implode("-",$depcid); ?>','_top')">[QID: <?php echo $depqid; ?>]</a>
                    <?php }
                } ?>
                </td></tr>
            <?php } ?>