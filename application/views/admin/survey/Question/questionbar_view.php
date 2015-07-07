<?php
$aReplacementData=array();
?>
<div class='menubar-title ui-widget-header'>
    <strong><?php eT("Question"); ?></strong> <span class='basic'><?php echo ellipsize(FlattenText($qrrow['question']),200); ?> (<?php echo gT("ID").":".$qid; ?>)</span>
</div>
<div class='menubar-main'>
    <div class='menubar-left'>
        <img id='separator16' src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />
        <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read'))
            {
            ?>
                <a accesskey='q' id='questionpreviewlink' ' href="<?php echo $this->createUrl("survey/index/action/previewquestion/sid/" . $surveyid . "/gid/" . $gid . "/qid/" . $qid); ?>" target="_blank">
                    <img src='<?php echo $sImageURL; ?>preview.png' alt='<?php eT("Preview this question"); ?>' /></a>
                <?php if (count($languagelist) > 1)
                { ?>
                <div class="popuptip" rel="questionpreviewlink"><?php eT("Preview this question in:"); ?>
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
                <img src='<?php echo $sImageURL; ?>edit.png' alt='<?php eT("Edit Current Question"); ?>' /></a>
            <?php } ?>

        <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read'))
            { ?>
            <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt=''  />
            <a target='_blank' href="<?php echo $this->createUrl("admin/expressions/sa/survey_logic_file/sid/{$surveyid}/gid/{$gid}/qid/{$qid}/"); ?>">
                <img src='<?php echo $sImageURL; ?>quality_assurance.png' alt='<?php eT("Check survey logic for current question"); ?>' /></a>
            <?php } ?>
        <?php if ($activated != "Y")
            {?>
            <a href='#'
                onclick="if (confirm('<?php eT("Deleting this question will also delete any answer options and subquestions it includes. Are you sure you want to continue?","js"); ?>')) { <?php echo convertGETtoPOST($this->createUrl("admin/questions/sa/delete/surveyid/$surveyid/gid/$gid/qid/$qid")); ?>}">
                <img style='<?php echo (Permission::model()->hasSurveyPermission($surveyid,'surveycontent','delete')?'':'visibility: hidden;');?>' src='<?php echo $sImageURL; ?>delete.png' alt='<?php eT("Delete current question"); ?>'/></a>
            <?php }
            else
            { ?>
            <a href='<?php echo $this->createUrl('admin/survey/sa/view/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid); ?>'
                onclick="alert('<?php eT("You can't delete this question because the survey is currently active.","js"); ?>')">
                <img src='<?php echo $sImageURL; ?>delete_disabled.png' alt='<?php eT("Disabled - Delete current question"); ?>' /></a>
            <?php }



            if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','export'))
            { ?>
            <a href='<?php echo $this->createUrl("admin/export/sa/question/surveyid/$surveyid/gid/$gid/qid/$qid");?>'>
                <img src='<?php echo $sImageURL; ?>dumpquestion.png' alt='<?php eT("Export this question"); ?>' /></a>
            <?php } ?>

        <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />




        <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','create'))
            {
                if ($activated != "Y")
                { ?>
                <a href='<?php echo $this->createUrl("admin/questions/sa/copyquestion/surveyid/$surveyid/gid/$gid/qid/$qid");?>'>
                    <img src='<?php echo $sImageURL; ?>copy.png'  alt='<?php eT("Copy Current Question"); ?>' /></a>
                <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />
                <?php }
                else
                { ?>
                <a href='#' onclick="alert('<?php eT("You can't copy a question if the survey is active.","js"); ?>')">
                    <img src='<?php echo $sImageURL; ?>copy_disabled.png' alt='<?php eT("Copy Current Question"); ?>' /></a>
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
                <img src='<?php echo $sImageURL; ?>conditions.png' alt='<?php eT("Set conditions for this question"); ?>'  /></a>
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
                    <img src='<?php echo $sImageURL; ?><?php if ($qtypes[$qrrow['type']]['subquestions']==1){?>subquestions.png<?php } else {?>subquestions2d.png<?php } ?>' alt='<?php eT("Edit subquestions for this question"); ?>' /></a>
                <?php }
            }
            else
            { ?>
            <img src='<?php echo $sImageURL; ?>blank.gif' alt='' height="<?php echo $iIconSize;?>" width='40' />
            <?php }




            if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read') && $qtypes[$qrrow['type']]['answerscales'] > 0)
            { ?>
            <a href='<?php echo $this->createUrl('admin/questions/sa/answeroptions/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid); ?>'>
                <img src='<?php echo $sImageURL; ?>answers.png' alt='<?php eT("Edit answer options for this question"); ?>' /></a>
            <?php }
            else
            { ?>
            <img src='<?php echo $sImageURL; ?>blank.gif' alt='' height="<?php echo $iIconSize;?>" width='40' />
            <?php }




            if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read') && $qtypes[$qrrow['type']]['hasdefaultvalues'] >0)
            { ?>
            <a href='<?php echo $this->createUrl('admin/questions/sa/editdefaultvalues/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid); ?>'>
                <img src='<?php echo $sImageURL; ?>defaultanswers.png' alt='<?php eT("Edit default answers for this question"); ?>' /></a>
            <?php } ?>
    </div>
    <div class='menubar-right'>
        <input type='image' src='<?php echo $sImageURL; ?>minimize.png'
            title='<?php eT("Hide details of this question"); ?>'  alt='<?php eT("Hide details of this question"); ?>' onclick='document.getElementById("questiondetails").style.display="none";' />
        <input type='image' src='<?php echo $sImageURL; ?>maximize.png' title='<?php eT("Show details of this question"); ?>'  alt='<?php eT("Show Details of this Question"); ?>' onclick='document.getElementById("questiondetails").style.display="";' />
        <input type='image' src='<?php echo $sImageURL; ?>close.png' title='<?php eT("Close this question"); ?>' alt='<?php eT("Close this question"); ?>'
            onclick="window.open('<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid/gid/$gid"); ?>','_top');" />
    </div>
</div>
</div>
<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>


<table  id='questiondetails' <?php echo $qshowstyle; ?>><tr><td><strong>
            <?php eT("Code:"); ?></strong></td>
        <td><?php echo $qrrow['title']; ?>
            <?php if ($qrrow['type'] != "X")
                {
                    if ($qrrow['mandatory'] == "Y") { ?>
                    : (<i><?php eT("Mandatory Question"); ?></i>)
                    <?php }
                    else { ?>
                    : (<i><?php eT("Optional Question"); ?></i>)
                    <?php }
            } ?>
        </td></tr>
    <tr><td><strong>
            <?php eT("Question:"); ?></strong></td><td>
            <?php
                templatereplace($qrrow['question'],array(),$aReplacementData,'Unspecified', false ,$qid);
                echo viewHelper::stripTagsEM(LimeExpressionManager::GetLastPrettyPrintExpression());
        ?></td></tr>
    <tr><td><strong>
            <?php eT("Help:"); ?></strong></td><td>
            <?php
                if (trim($qrrow['help'])!=''){
                    templatereplace($qrrow['help'],array(),$aReplacementData,'Unspecified', false ,$qid);
                    echo viewHelper::stripTagsEM(LimeExpressionManager::GetLastPrettyPrintExpression());
            } ?>
        </td></tr>
    <?php if ($qrrow['preg'])
        { ?>
        <tr ><td><strong>
                <?php eT("Validation:"); ?></strong></td><td><?php echo htmlspecialchars($qrrow['preg']); ?>
            </td></tr>
        <?php } ?>

    <tr><td><strong>
            <?php eT("Type:"); ?></strong></td><td><?php echo $qtypes[$qrrow['type']]['description']; ?>
        </td></tr>

        <?php foreach($aWarnings as $aWarning)
        { ?>
        <tr>
            <td class='text-error'><?php eT("Warning:"); ?></td>
            <td>
            <?php echo CHtml::link($aWarning['text'].CHtml::image($sImageURL.$aWarning['img']),$aWarning['url'],array("title"=>$aWarning['help'])) ?>
            </td>
        </tr>
        <?php } ?>

        <?php if ($qrrow['type'] == "M" or $qrrow['type'] == "P")
        { ?>
        <tr>
            <td><strong>
                <?php eT("Option 'Other':"); ?></strong></td>
            <td>
                <?php if ($qrrow['other'] == "Y") { ?>
                    <?php eT("Yes"); ?>
                    <?php } else
                    { ?>
                    <?php eT("No"); ?>

                    <?php } ?>
            </td></tr>
        <?php }
        if (isset($qrrow['mandatory']) and ($qrrow['type'] != "X") and ($qrrow['type'] != "|"))
        { ?>
        <tr>
            <td><strong>
                <?php eT("Mandatory:"); ?></strong></td>
            <td>
                <?php if ($qrrow['mandatory'] == "Y") { ?>
                    <?php eT("Yes"); ?>
                    <?php } else
                    { ?>
                    <?php eT("No"); ?>

                    <?php } ?>
            </td>
        </tr>
        <?php } ?>
    <?php if (trim($qrrow['relevance']) != '') { ?>
        <tr>
            <td><?php eT("Relevance equation:"); ?></td>
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
