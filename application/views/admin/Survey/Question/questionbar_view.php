<div class='menubar-title ui-widget-header'>
            <strong><?php echo $clang->gT("Question"); ?></strong> <span class='basic'><?php echo $qrrow['question']; ?> (<?php echo $clang->gT("ID").":".$qid; ?>)</span>
            </div>
            <div class='menubar-main'>
            <div class='menubar-left'>
            <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='55' height='20' />
            <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt='' />
            <?php if(bHasSurveyPermission($surveyid,'surveycontent','read'))
            {
                if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
                { ?>
                    <a href="#" accesskey='q' onclick="window.open('<?php echo site_url("admin/question/preview/$surveyid/$qid/");?>', '_blank')"
                    title="<?php echo $clang->gTview("Preview This Question"); ?>">
                    <img src='<?php echo $this->config->item('imageurl'); ?>/preview.png' alt='<?php echo $clang->gT("Preview This Question"); ?>' name='previewquestionimg' /></a>
                    <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt='' />
                <?php } else { ?>
                    <a href="#" accesskey='q' id='previewquestion'
                    title="<?php echo $clang->gTview("Preview This Question"); ?>">
                    <img src='<?php echo $this->config->item('imageurl'); ?>/preview.png' title='' alt='<?php echo $clang->gT("Preview This Question"); ?>' name='previewquestionimg' /></a>
                    <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt=''  />
    
                <?php }
            } ?>
    
               
           <?php  if(bHasSurveyPermission($surveyid,'surveycontent','update'))
            { ?>
                
                <a href='<?php echo site_url("admin/question/editquestion/".$surveyid."/".$gid."/".$qid); ?>'
                 title="<?php echo $clang->gTview("Edit current question"); ?>">
                <img src='<?php echo $this->config->item('imageurl'); ?>/edit.png' alt='<?php echo $clang->gT("Edit Current Question"); ?>' name='EditQuestion' /></a> 
            <?php } ?>
    
    
            
    
            <?php if ((($qct == 0 && $activated != "Y") || $activated != "Y") && bHasSurveyPermission($surveyid,'surveycontent','delete'))
            {
                if (is_null($condarray))
                { ?>
                    <a href='#'
    				onclick="if (confirm('<?php echo $clang->gT("Deleting this question will also delete any answer options and subquestions it includes. Are you sure you want to continue?","js"); ?>')) {<?php echo get2post(site_url('admin/question/delete')."?action=delquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid"); ?>}">
    				<img src='<?php echo $this->config->item('imageurl'); ?>/delete.png' name='DeleteWholeQuestion' alt='<?php echo $clang->gT("Delete current question"); ?>'
    				border='0' hspace='0' /></a>
                <?php }
                else
                { ?>
                    <a href='$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid'
    				onclick="alert('<?php echo $clang->gT("It's impossible to delete this question because there is at least one question having a condition on it.","js"); ?>')"
    				title="<?php echo $clang->gTview("Disabled - Delete current question"); ?>">
    				<img src='<?php echo $this->config->item('imageurl'); ?>/delete_disabled.png' name='DeleteWholeQuestion' alt='<?php echo $clang->gT("Disabled - Delete current question"); ?>' /></a>
                <?php }
            }
            else {
                ?>
                
                <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='40' />
                
                <?php } 
    
    
    
            if(bHasSurveyPermission($surveyid,'surveycontent','export'))
            { ?>
                <a href='<?php echo site_url("admin/export/question/$surveyid/$gid/$qid");?>'
                 title="<?php echo $clang->gTview("Export this question"); ?>" >
                <img src='<?php echo $this->config->item('imageurl'); ?>/dumpquestion.png' alt='<?php echo $clang->gT("Export this question"); ?>' name='ExportQuestion' /></a>
            <?php } ?>
    
            <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt='' />
    
    
            
    
            <?php if(bHasSurveyPermission($surveyid,'surveycontent','create'))
            {
                if ($activated != "Y")
                { ?>
                    <a href='$scriptname?action=copyquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'
                     title="<?php echo $clang->gTview("Copy Current Question"); ?>" >
                    <img src='<?php echo $this->config->item('imageurl'); ?>/copy.png'  alt='<?php echo $clang->gT("Copy Current Question"); ?>' name='CopyQuestion' /></a>
                    <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt='' />
                <?php }
                else
                { ?>
                    <a href='#' title="<?php echo $clang->gTview("Copy Current Question"); ?>"
                    onclick="alert('<?php echo $clang->gT("You can't copy a question if the survey is active.","js"); ?>')">
                    <img src='<?php echo $this->config->item('imageurl'); ?>/copy_disabled.png' alt='<?php echo $clang->gT("Copy Current Question"); ?>' name='CopyQuestion' /></a>
                    <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt='' />
                <?php }
            }
            else
            { ?>
                <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='40' />
            <?php } 
            
            if(bHasSurveyPermission($surveyid,'surveycontent','update'))
            { ?>
                <a href='#' onclick="window.open('<?php echo site_url("admin/conditions/editconditionsform/$surveyid/$gid/$qid");?>', '_top')"
                 title="<?php echo $clang->gTview("Set/view conditions for this question"); ?>">
                <img src='<?php echo $this->config->item('imageurl'); ?>/conditions.png' alt='<?php echo $clang->gT("Set conditions for this question"); ?>'  name='SetQuestionConditions' /></a>
                <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' alt='' />
            <?php }
            else
            { ?>
                <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='40' />
            <?php } 
    
    
            
    
            
            if(bHasSurveyPermission($surveyid,'surveycontent','read'))
            {
                if ($qtypes[$qrrow['type']]['subquestions'] >0)
                { ?>
                    <a href='<?php echo site_url('admin/question/subquestions/'.$surveyid.'/'.$gid.'/'.$qid); ?>'
                    title='<?php echo $clang->gTview("Edit subquestions for this question"); ?>'>
                    <img src='<?php echo $this->config->item('imageurl'); ?>/subquestions.png' alt='<?php echo $clang->gT("Edit subquestions for this question"); ?>' name='EditSubquestions' /></a>
                <?php }
            } 
            else
            { ?>
                <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='40' />
            <?php }
    
    
           
    
            if(bHasSurveyPermission($surveyid,'surveycontent','read') && $qtypes[$qrrow['type']]['answerscales'] >0)
            { ?>
                <a href='<?php echo site_url('admin/question/answeroptions/'.$surveyid.'/'.$gid.'/'.$qid); ?>'
                title="<?php echo $clang->gTview("Edit answer options for this question"); ?>">
                <img src='<?php echo $this->config->item('imageurl'); ?>/answers.png' alt='<?php echo $clang->gT("Edit answer options for this question"); ?>' name='EdtAnswerOptions' /></a>
            <?php }
            else
            { ?>
                <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' alt='' width='40' />
            <?php }
    
    

    
            if(bHasSurveyPermission($surveyid,'surveycontent','read') && $qtypes[$qrrow['type']]['hasdefaultvalues'] >0)
            { ?>
                <a href='$scriptname?action=editdefaultvalues&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'
                title="<?php echo $clang->gTview("Edit default answers for this question"); ?>">
                <img src='<?php echo $this->config->item('imageurl'); ?>/defaultanswers.png' alt='<?php echo $clang->gT("Edit default answers for this question"); ?>' name='EdtAnswerOptions' /></a> 
            <?php } ?>
            </div>
            <div class='menubar-right'>
            <input type='image' src='<?php echo $this->config->item('imageurl'); ?>/minus.gif' title='
            <?php echo $clang->gT("Hide Details of this Question"); ?>'  alt='<?php echo $clang->gT("Hide Details of this Question"); ?>' name='MinimiseQuestionWindow'
            onclick='document.getElementById("questiondetails").style.display="none";' />
            <input type='image' src='<?php echo $this->config->item('imageurl'); ?>/plus.gif' title='
            <?php echo $clang->gT("Show Details of this Question"); ?>'  alt='<?php echo $clang->gT("Show Details of this Question"); ?>' name='MaximiseQuestionWindow'
            onclick='document.getElementById("questiondetails").style.display="";' />
            <input type='image' src='<?php echo $this->config->item('imageurl'); ?>/close.gif' title='
            <?php echo $clang->gT("Close this Question"); ?>' alt='<?php echo $clang->gT("Close this Question"); ?>' name='CloseQuestionWindow'
            onclick="window.open('<?php echo site_url("admin/survey/view/".$surveyid."/".$gid); ?>', '_top')" />
            </div>
            </div>
            </div>
            <p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>
    
            
            <table  id='questiondetails' <?php echo $qshowstyle; ?>><tr><td width='20%' align='right'><strong>
            <?php echo $clang->gT("Code:"); ?></strong></td>
            <td align='left'><?php echo $qrrow['title']; ?>
            <?php if ($qrrow['type'] != "X")
            {
                if ($qrrow['mandatory'] == "Y") { ?>
                : (<i><?php echo $clang->gT("Mandatory Question"); ?></i>)
                <?php }
                else { ?>
                : (<i><?php echo $clang->gT("Optional Question"); ?></i>)
                <?php }
            } ?>
            </td></tr>
            <tr><td align='right' valign='top'><strong>
            <?php echo $clang->gT("Question:"); ?></strong></td><td align='left'><?php echo $qrrow['question']; ?></td></tr>
            <tr><td align='right' valign='top'><strong>
            <?php echo $clang->gT("Help:"); ?></strong></td><td align='left'>
            <?php if (trim($qrrow['help'])!=''){ echo $qrrow['help'];} ?>
            </td></tr>
            <?php if ($qrrow['preg'])
            { ?>
                <tr ><td align='right' valign='top'><strong>
                <?php echo $clang->gT("Validation:"); ?></strong></td><td align='left'><?php echo $qrrow['preg']; ?>
                </td></tr>
            <?php } ?>
            
            <tr><td align='right' valign='top'><strong>
            <?php echo $clang->gT("Type:"); ?></strong></td><td align='left'><?php echo $qtypes[$qrrow['type']]['description']; ?>
            </td></tr>
            <?php if ($qct == 0 && $qtypes[$qrrow['type']]['answerscales'] >0)
            { ?>
                <tr ><td></td><td align='left'>
                <span class='statusentryhighlight'>
                <?php echo $clang->gT("Warning"); ?>: <a href='$scriptname?sid={$surveyid}&amp;gid={$gid}&amp;qid={$qid}&amp;action=editansweroptions'><?php echo $clang->gT("You need to add answer options to this question"); ?>
                <img src='<?php echo $this->config->item('imageurl'); ?>/answers_20.png' title='
                <?php echo $clang->gT("Edit answer options for this question"); ?>' name='EditThisQuestionAnswers'/></span></td></tr>
            <?php } 
    
    
            if($sqct == 0 && $qtypes[$qrrow['type']]['subquestions'] >0)
            { ?>
               <tr ><td></td><td align='left'>
                <span class='statusentryhighlight'>
                <?php echo $clang->gT("Warning"); ?>: <a href='$scriptname?sid={$surveyid}&amp;gid={$gid}&amp;qid={$qid}&amp;action=editsubquestions'><?php echo $clang->gT("You need to add subquestions to this question"); ?>
                <img src='<?php echo $this->config->item('imageurl'); ?>/subquestions_20.png' title='
                <?php echo $clang->gT("Edit subquestions for this question"); ?>' name='EditThisQuestionAnswers' /></span></td></tr>
            <?php }
    
            if ($qrrow['type'] == "M" or $qrrow['type'] == "P")
            { ?>
                <tr>
                <td align='right' valign='top'><strong>
                <?php echo $clang->gT("Option 'Other':"); ?></strong></td>
                <td align='left'>
                <?php if ($qrrow['other'] == "Y") { ?>
                <?php echo $clang->gT("Yes"); ?> 
                <?php } else
                { ?>
                    <?php echo $clang->gT("No"); ?>
                    
                <?php } ?>
                </td></tr>
            <?php } 
            if (isset($qrrow['mandatory']) and ($qrrow['type'] != "X") and ($qrrow['type'] != "|"))
            { ?>
                <tr>
                <td align='right' valign='top'><strong>
                <?php echo $clang->gT("Mandatory:"); ?></strong></td>
                <td align='left'>
                <?php if ($qrrow['mandatory'] == "Y") { ?>
                <?php echo $clang->gT("Yes"); ?> 
                <?php } else
                { ?>
                    <?php echo $clang->gT("No"); ?>
                    
                <?php } ?>
                </td></tr>
            <?php } 
            if (!is_null($condarray))
            { ?>
                <tr>
                <td align='right' valign='top'><strong>
                <?php echo $clang->gT("Other questions having conditions on this question:"); ?>
                </strong></td><td align='left' valign='bottom'>
                <?php foreach ($condarray[$qid] as $depqid => $depcid)
                { 
                    $listcid=implode("-",$depcid); ?>
                     <a href='#' onclick="window.open('admin.php?sid=<?php echo $surveyid; ?>&amp;qid=<?php echo $depqid; ?>&amp;action=conditions&amp;markcid=<?php echo $listcid; ?>','_top')">[QID: <?php echo $depqid; ?>]</a>
                <?php } ?>
                </td></tr>
            <?php } ?>
            </table>