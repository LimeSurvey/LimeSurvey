<?php
$aReplacementData=array();
?>
<div class='menubar-title ui-widget-header'>
    <strong><?php $clang->eT("Question"); ?></strong> <span class='basic'><?php echo ellipsize(FlattenText($qrrow['question']),200); ?> (<?php echo $clang->gT("ID").":".$qid; ?>)</span>
</div>
<div class='menubar-main'>
    <div class='menubar-left'>
        <img id='separator16' src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />
        <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read'))
            {
            ?>
                <a accesskey='q' id='questionpreviewlink' ' href="<?php echo $this->createUrl("survey/index/action/previewquestion/sid/" . $surveyid . "/gid/" . $gid . "/qid/" . $qid); ?>" target="_blank">
                    <img src='<?php echo $sImageURL; ?>preview.png' alt='<?php $clang->eT("Preview this question"); ?>' /></a>
                <?php if (count($languagelist) > 1)
                { ?>
                <div class="popuptip" rel="questionpreviewlink"><?php $clang->eT("Preview this question in:"); ?>
                    <ul>
                    <?php foreach ($languagelist as $tmp_lang){ ?>
                        <li><a target='_blank' href='<?php echo $this->createUrl("survey/index/action/previewquestion/sid/" . $surveyid . "/gid/" . $gid . "/qid/" . $qid . "/lang/" . $tmp_lang); ?>' ><?php echo getLanguageNameFromCode($tmp_lang,false); ?></a></li>
                    <?php } ?>
                    </ul>
                </div>
                <?php } ?>
                <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />
        <?php } ?>


        <?php  if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update'))
            { ?>

            <a href='<?php echo $this->createUrl("admin/questions/sa/editquestion/surveyid/".$surveyid."/gid/".$gid."/qid/".$qid); ?>'>
                <img src='<?php echo $sImageURL; ?>edit.png' alt='<?php $clang->eT("Edit Current Question"); ?>' /></a>
            <?php } ?>

        <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read'))
            { ?>
            <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt=''  />
            <a target='_blank' href="<?php echo $this->createUrl("admin/expressions/sa/survey_logic_file/sid/{$surveyid}/gid/{$gid}/qid/{$qid}/"); ?>">
                <img src='<?php echo $sImageURL; ?>quality_assurance.png' alt='<?php $clang->eT("Check survey logic for current question"); ?>' /></a>
            <?php } ?>
        <?php if ($activated != "Y")
            {?>
            <a href='#'
                onclick="if (confirm('<?php $clang->eT("Deleting this question will also delete any answer options and subquestions it includes. Are you sure you want to continue?","js"); ?>')) { <?php echo convertGETtoPOST($this->createUrl("admin/questions/sa/delete/surveyid/$surveyid/gid/$gid/qid/$qid")); ?>}">
                <img style='<?php echo (Permission::model()->hasSurveyPermission($surveyid,'surveycontent','delete')?'':'visibility: hidden;');?>' src='<?php echo $sImageURL; ?>delete.png' alt='<?php $clang->eT("Delete current question"); ?>'/></a>
            <?php }
            else
            { ?>
            <a href='<?php echo $this->createUrl('admin/survey/sa/view/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid); ?>'
                onclick="alert('<?php $clang->eT("You can't delete this question because the survey is currently active.","js"); ?>')">
                <img src='<?php echo $sImageURL; ?>delete_disabled.png' alt='<?php $clang->eT("Disabled - Delete current question"); ?>' /></a>
            <?php }



            if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','export'))
            { ?>
            <a href='<?php echo $this->createUrl("admin/export/sa/question/surveyid/$surveyid/gid/$gid/qid/$qid");?>'>
                <img src='<?php echo $sImageURL; ?>dumpquestion.png' alt='<?php $clang->eT("Export this question"); ?>' /></a>
            <?php } ?>

        <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />




        <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','create'))
            {
                if ($activated != "Y")
                { ?>
                <a href='<?php echo $this->createUrl("admin/questions/sa/copyquestion/surveyid/$surveyid/gid/$gid/qid/$qid");?>'>
                    <img src='<?php echo $sImageURL; ?>copy.png'  alt='<?php $clang->eT("Copy Current Question"); ?>' /></a>
                <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />
                <?php }
                else
                { ?>
                <a href='#' onclick="alert('<?php $clang->eT("You can't copy a question if the survey is active.","js"); ?>')">
                    <img src='<?php echo $sImageURL; ?>copy_disabled.png' alt='<?php $clang->eT("Copy Current Question"); ?>' /></a>
                <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />
                <?php }
            }
            else
            { ?>
            <img src='<?php echo $sImageURL; ?>blank.gif' alt='' height="<?php echo $iIconSize;?>" width='40' />
            <?php }

            if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update'))
            { ?>
            <a href="<?php echo $this->createUrl("admin/conditions/sa/index/subaction/editconditionsform/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>">
                <img src='<?php echo $sImageURL; ?>conditions.png' alt='<?php $clang->eT("Set conditions for this question"); ?>'  /></a>
            <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />
            <?php }
            else
            { ?>
            <img src='<?php echo $sImageURL; ?>blank.gif' alt='' height="<?php echo $iIconSize;?>" width='40' />
            <?php }





            if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read'))
            {
                if ($qtypes[$qrrow['type']]['subquestions'] >0)
                { ?>
                <a href='<?php echo $this->createUrl('admin/questions/sa/subquestions/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid); ?>'>
                    <img src='<?php echo $sImageURL; ?><?php if ($qtypes[$qrrow['type']]['subquestions']==1){?>subquestions.png<?php } else {?>subquestions2d.png<?php } ?>' alt='<?php $clang->eT("Edit subquestions for this question"); ?>' /></a>
                <?php }
            }
            else
            { ?>
            <img src='<?php echo $sImageURL; ?>blank.gif' alt='' height="<?php echo $iIconSize;?>" width='40' />
            <?php }




            if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read') && $qtypes[$qrrow['type']]['answerscales'] > 0)
            { ?>
            <a href='<?php echo $this->createUrl('admin/questions/sa/answeroptions/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid); ?>'>
                <img src='<?php echo $sImageURL; ?>answers.png' alt='<?php $clang->eT("Edit answer options for this question"); ?>' /></a>
            <?php }
            else
            { ?>
            <img src='<?php echo $sImageURL; ?>blank.gif' alt='' height="<?php echo $iIconSize;?>" width='40' />
            <?php }




            if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read') && $qtypes[$qrrow['type']]['hasdefaultvalues'] >0)
            { ?>
            <a href='<?php echo $this->createUrl('admin/questions/sa/editdefaultvalues/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid); ?>'>
                <img src='<?php echo $sImageURL; ?>defaultanswers.png' alt='<?php $clang->eT("Edit default answers for this question"); ?>' /></a>
            <?php } ?>
    </div>
    <div class='menubar-right'>
        <input type='image' src='<?php echo $sImageURL; ?>minimize.png'
            title='<?php $clang->eT("Hide details of this question"); ?>'  alt='<?php $clang->eT("Hide details of this question"); ?>' onclick='document.getElementById("questiondetails").style.display="none";' />
        <input type='image' src='<?php echo $sImageURL; ?>maximize.png' title='<?php $clang->eT("Show details of this question"); ?>'  alt='<?php $clang->eT("Show Details of this Question"); ?>' onclick='document.getElementById("questiondetails").style.display="";' />
        <input type='image' src='<?php echo $sImageURL; ?>close.png' title='<?php $clang->eT("Close this question"); ?>' alt='<?php $clang->eT("Close this question"); ?>'
            onclick="window.open('<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid/gid/$gid"); ?>','_top');" />
    </div>
</div>
</div>
<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>


<table  id='questiondetails' <?php echo $qshowstyle; ?>><tr><td><strong>
            <?php $clang->eT("Code:"); ?></strong></td>
        <td><?php echo $qrrow['title']; ?>
            <?php if ($qrrow['type'] != "X")
                {
                    if ($qrrow['mandatory'] == "Y") { ?>
                    : (<i><?php $clang->eT("Mandatory Question"); ?></i>)
                    <?php }
                    else { ?>
                    : (<i><?php $clang->eT("Optional Question"); ?></i>)
                    <?php }
            } ?>
        </td></tr>
    <tr><td><strong>
            <?php $clang->eT("Question:"); ?></strong></td><td>
            <?php
                templatereplace(FlattenText($qrrow['question']),array(),$aReplacementData,'Unspecified', false ,$qid);
                echo LimeExpressionManager::GetLastPrettyPrintExpression();
        ?></td></tr>
    <tr><td><strong>
            <?php $clang->eT("Help:"); ?></strong></td><td>
            <?php
                if (trim($qrrow['help'])!=''){
                    templatereplace(FlattenText($qrrow['help']),array(),$aReplacementData,'Unspecified', false ,$qid);
                    echo LimeExpressionManager::GetLastPrettyPrintExpression();
            } ?>
        </td></tr>
    <?php if ($qrrow['preg'])
        { ?>
        <tr ><td><strong>
                <?php $clang->eT("Validation:"); ?></strong></td><td><?php echo htmlspecialchars($qrrow['preg']); ?>
            </td></tr>
        <?php } ?>

    <tr><td><strong>
            <?php $clang->eT("Type:"); ?></strong></td><td><?php echo $qtypes[$qrrow['type']]['description']; ?>
        </td></tr>
    <?php if ($qct == 0 && $qtypes[$qrrow['type']]['answerscales'] >0)
        { ?>
        <tr ><td></td><td>
                <span class='statusentryhighlight'>
                    <?php $clang->eT("Warning"); ?>: <a href='<?php echo $this->createUrl("admin/questions/sa/answeroptions/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>'><?php $clang->eT("You need to add answer options to this question"); ?>
                        <img src='<?php echo $sImageURL; ?>answers_20.png' title='<?php $clang->eT("Edit answer options for this question"); ?>' /></a></span></td></tr>
        <?php }


        if($sqct == 0 && $qtypes[$qrrow['type']]['subquestions'] >0)
        { ?>
        <tr ><td></td><td>
                <span class='statusentryhighlight'>
                    <?php $clang->eT("Warning"); ?>: <a href='<?php echo $this->createUrl("admin/questions/sa/subquestions/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>'><?php $clang->eT("You need to add subquestions to this question"); ?>
                        <img src='<?php echo $sImageURL; ?><?php if ($qtypes[$qrrow['type']]['subquestions']==1){?>subquestions_20<?php } else {?>subquestions2d_20<?php } ?>.png' title='<?php $clang->eT("Edit subquestions for this question"); ?>' /></a></span></td></tr>
        <?php }

        if ($qrrow['type'] == "M" or $qrrow['type'] == "P")
        { ?>
        <tr>
            <td><strong>
                <?php $clang->eT("Option 'Other':"); ?></strong></td>
            <td>
                <?php if ($qrrow['other'] == "Y") { ?>
                    <?php $clang->eT("Yes"); ?>
                    <?php } else
                    { ?>
                    <?php $clang->eT("No"); ?>

                    <?php } ?>
            </td></tr>
        <?php }
        if (isset($qrrow['mandatory']) and ($qrrow['type'] != "X") and ($qrrow['type'] != "|"))
        { ?>
        <tr>
            <td><strong>
                <?php $clang->eT("Mandatory:"); ?></strong></td>
            <td>
                <?php if ($qrrow['mandatory'] == "Y") { ?>
                    <?php $clang->eT("Yes"); ?>
                    <?php } else
                    { ?>
                    <?php $clang->eT("No"); ?>

                    <?php } ?>
            </td>
        </tr>
        <?php } ?>
    <?php if (trim($qrrow['relevance']) != '') { ?>
        <tr>
            <td><?php $clang->eT("Relevance equation:"); ?></td>
            <td>
                <?php
                    LimeExpressionManager::ProcessString("{" . $qrrow['relevance'] . "}", $qid);    // tests Relevance equation so can pretty-print it
                    echo LimeExpressionManager::GetLastPrettyPrintExpression();
                ?>
            </td>
        </tr>
        <?php } ?>
    <?php
        $sCurrentCategory='';
        foreach ($advancedsettings as $aAdvancedSetting)
        { ?>
        <tr>
            <td><?php echo $aAdvancedSetting['caption'];?>:</td>
            <td><?php
                    if ($aAdvancedSetting['i18n']==false)  echo htmlspecialchars($aAdvancedSetting['value']); else echo htmlspecialchars($aAdvancedSetting[$baselang]['value'])?>
            </td>
        </tr>
        <?php } ?>
            </table>
