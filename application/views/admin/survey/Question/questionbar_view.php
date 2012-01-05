<?php if (isset($tmp_survlangs)) { ?>
    <div class="langpopup" id="previewquestionpopup"><?php $clang->eT("Please select a language:"); ?><ul>
            <?php foreach ($tmp_survlangs as $tmp_lang)
                { ?>
                <li><a target='_blank' onclick="$('#previewquestion').qtip('hide');" href='<?php echo $this->createUrl("admin/question/preview/surveyid/".$surveyid."/qid/".$qid."/lang/".$tmp_lang); ?>' accesskey='d'><?php echo getLanguageNameFromCode($tmp_lang,false); ?></a></li>
                <?php } ?>
        </ul></div>
    <?php } ?>
<div class='menubar-title ui-widget-header'>
    <strong><?php $clang->eT("Question"); ?></strong> <span class='basic'><?php echo $qrrow['question']; ?> (<?php echo $clang->gT("ID").":".$qid; ?>)</span>
</div>
<div class='menubar-main'>
    <div class='menubar-left'>
        <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/blank.gif' alt='' width='55' height='20' />
        <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' alt='' />
        <?php if(bHasSurveyPermission($surveyid,'surveycontent','read'))
            {
                if (count(Survey::model()->findByPk($surveyid)->additionalLanguages) == 0)
                { ?>
                <a href="#" accesskey='q' onclick="window.open('<?php echo $this->createUrl("admin/question/preview/surveyid/$surveyid/qid/$qid/");?>', '_blank')"
                    title="<?php $clang->eTview("Preview This Question"); ?>">
                    <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/preview.png' alt='<?php $clang->eT("Preview This Question"); ?>' /></a>
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' alt='' />
                <?php } else { ?>
                <a href="#" accesskey='q' id='previewquestion'
                    title="<?php $clang->eTview("Preview This Question"); ?>">
                    <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/preview.png' title='' alt='<?php $clang->eT("Preview This Question"); ?>' /></a>
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' alt=''  />

                <?php }
        } ?>


        <?php  if(bHasSurveyPermission($surveyid,'surveycontent','update'))
            { ?>

            <a href='<?php echo $this->createUrl("admin/question/editquestion/surveyid/".$surveyid."/gid/".$gid."/qid/".$qid); ?>'
                title="<?php $clang->eTview("Edit current question"); ?>">
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/edit.png' alt='<?php $clang->eT("Edit Current Question"); ?>' /></a>
            <?php } ?>




        <?php if ((($qct == 0 && $activated != "Y") || $activated != "Y") && bHasSurveyPermission($surveyid,'surveycontent','delete'))
            {
                if (is_null($condarray))
                { ?>
                <a href='#'
                    onclick="if (confirm('<?php $clang->eT("Deleting this question will also delete any answer options and subquestions it includes. Are you sure you want to continue?","js"); ?>')) { <?php echo get2post($this->createUrl("admin/question/delete/surveyid/$surveyid/gid/$gid/qid/$qid")); ?>}">
                    <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/delete.png' alt='<?php $clang->eT("Delete current question"); ?>'
                        border='0' hspace='0' /></a>
                <?php }
                else
                // TMSW Conditions->Relevance:  not needed - should be allowed to delete questions even if others depend upon it - use separate view to see exceptions

                { ?>
                <a href='<?php echo $this->createUrl('admin/survey/view/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid); ?>'
                    onclick="alert('<?php $clang->eT("It's impossible to delete this question because there is at least one question having a condition on it.","js"); ?>')"
                    title="<?php $clang->eTview("Disabled - Delete current question"); ?>">
                    <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/delete_disabled.png' alt='<?php $clang->eT("Disabled - Delete current question"); ?>' /></a>
                <?php }
            }
            else {
            ?>

            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/blank.gif' alt='' width='40' />

            <?php }



            if(bHasSurveyPermission($surveyid,'surveycontent','export'))
            { ?>
            <a href='<?php echo $this->createUrl("admin/export/question/surveyid/$surveyid/gid/$gid/qid/$qid");?>'
                title="<?php $clang->eTview("Export this question"); ?>" >
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/dumpquestion.png' alt='<?php $clang->eT("Export this question"); ?>' /></a>
            <?php } ?>

        <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' alt='' />




        <?php if(bHasSurveyPermission($surveyid,'surveycontent','create'))
            {
                if ($activated != "Y")
                { ?>
                <a href='<?php echo $this->createUrl("admin/question/copyquestion/surveyid/$surveyid/gid/$gid/qid/$qid");?>'
                    title="<?php $clang->eTview("Copy Current Question"); ?>" >
                    <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/copy.png'  alt='<?php $clang->eT("Copy Current Question"); ?>' /></a>
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' alt='' />
                <?php }
                else
                { ?>
                <a href='#' title="<?php $clang->eTview("Copy Current Question"); ?>"
                    onclick="alert('<?php $clang->eT("You can't copy a question if the survey is active.","js"); ?>')">
                    <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/copy_disabled.png' alt='<?php $clang->eT("Copy Current Question"); ?>' /></a>
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' alt='' />
                <?php }
            }
            else
            { ?>
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/blank.gif' alt='' width='40' />
            <?php }

            if(bHasSurveyPermission($surveyid,'surveycontent','update'))
            { ?>
            <a href='#' onclick="window.open('<?php echo $this->createUrl("admin/conditions/index/subaction/editconditionsform/surveyid/$surveyid/gid/$gid/qid/$qid");?>', '_top')"
                title="<?php $clang->eTview("Set/view conditions for this question"); ?>">
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/conditions.png' alt='<?php $clang->eT("Set conditions for this question"); ?>'  /></a>
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' alt='' />
            <?php }
            else
            { ?>
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/blank.gif' alt='' width='40' />
            <?php }





            if(bHasSurveyPermission($surveyid,'surveycontent','read'))
            {
                if ($qtypes[$qrrow['type']]['subquestions'] >0)
                { ?>
                <a href='<?php echo $this->createUrl('admin/question/subquestions/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid); ?>'
                    title='<?php $clang->eTview("Edit subquestions for this question"); ?>'>
                    <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/subquestions.png' alt='<?php $clang->eT("Edit subquestions for this question"); ?>' /></a>
                <?php }
            }
            else
            { ?>
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/blank.gif' alt='' width='40' />
            <?php }




            if(bHasSurveyPermission($surveyid,'surveycontent','read') && $qtypes[$qrrow['type']]['answerscales'] > 0)
            { ?>
            <a href='<?php echo $this->createUrl('admin/question/answeroptions/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid); ?>'
                title="<?php $clang->eTview("Edit answer options for this question"); ?>">
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/answers.png' alt='<?php $clang->eT("Edit answer options for this question"); ?>' /></a>
            <?php }
            else
            { ?>
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/blank.gif' alt='' width='40' />
            <?php }




            if(bHasSurveyPermission($surveyid,'surveycontent','read') && $qtypes[$qrrow['type']]['hasdefaultvalues'] >0)
            { ?>
            <a href='<?php echo $this->createUrl('admin/question/editdefaultvalues/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid); ?>'
                title="<?php $clang->eTview("Edit default answers for this question"); ?>">
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/defaultanswers.png' alt='<?php $clang->eT("Edit default answers for this question"); ?>' /></a>
            <?php } ?>
    </div>
    <div class='menubar-right'>
        <input type='image' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/minus.gif' title='
            <?php $clang->eT("Hide Details of this Question"); ?>'  alt='<?php $clang->eT("Hide Details of this Question"); ?>' onclick='document.getElementById("questiondetails").style.display="none";' />
        <input type='image' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/plus.gif' title='
            <?php $clang->eT("Show Details of this Question"); ?>'  alt='<?php $clang->eT("Show Details of this Question"); ?>' onclick='document.getElementById("questiondetails").style.display="";' />
        <input type='image' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/close.gif' title='
            <?php $clang->eT("Close this Question"); ?>' alt='<?php $clang->eT("Close this Question"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/survey/view/surveyid/$surveyid/qid/$gid"); ?>', '_top')" />
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
                $junk = array();
                templatereplace($qrrow['question']);
                echo LimeExpressionManager::GetLastPrettyPrintExpression();
        ?></td></tr>
    <tr><td><strong>
            <?php $clang->eT("Help:"); ?></strong></td><td>
            <?php
                if (trim($qrrow['help'])!=''){
                    $junk = array();
                    templatereplace($qrrow['help']);
                    echo LimeExpressionManager::GetLastPrettyPrintExpression();
            } ?>
        </td></tr>
    <?php if ($qrrow['preg'])
        { ?>
        <tr ><td><strong>
                <?php $clang->eT("Validation:"); ?></strong></td><td><?php echo $qrrow['preg']; ?>
            </td></tr>
        <?php } ?>

    <tr><td><strong>
            <?php $clang->eT("Type:"); ?></strong></td><td><?php echo $qtypes[$qrrow['type']]['description']; ?>
        </td></tr>
    <?php if ($qct == 0 && $qtypes[$qrrow['type']]['answerscales'] >0)
        { ?>
        <tr ><td></td><td>
                <span class='statusentryhighlight'>
                    <?php $clang->eT("Warning"); ?>: <a href='<?php echo $this->createUrl("admin/question/answeroptions/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>'><?php $clang->eT("You need to add answer options to this question"); ?>
                        <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/answers_20.png' title='
                            <?php $clang->eT("Edit answer options for this question"); ?>' /></a></span></td></tr>
        <?php }


        if($sqct == 0 && $qtypes[$qrrow['type']]['subquestions'] >0)
        { ?>
        <tr ><td></td><td>
                <span class='statusentryhighlight'>
                    <?php $clang->eT("Warning"); ?>: <a href='<?php echo $this->createUrl("admin/question/subquestions/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>'><?php $clang->eT("You need to add subquestions to this question"); ?>
                        <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/subquestions_20.png' title='
                            <?php $clang->eT("Edit subquestions for this question"); ?>' /></a></span></td></tr>
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
            </td></tr>
        <tr>
            <td><?php $clang->eT("Relevance equation:"); ?></td>
            <td><?php echo $relevance; ?></td>
        </tr>
        <?php }
        // TMSW Conditions->Relevance:  not needed?  Or use relevance output or custom EM query to compute this?

        if (!is_null($condarray))
        { ?>
        <tr>
            <td ><strong>
                    <?php $clang->eT("Other questions having conditions on this question:"); ?>
                </strong></td><td>
                <?php foreach ($condarray[$qid] as $depqid => $depcid)
                    {
                        $listcid=implode("-",$depcid); ?>
                    <a href='#' onclick="window.open('<?php echo $this->createUrl("admin/conditions/markcid/" . $listcid . "/surveyid/$surveyid/qid/$depqid"); ?>','_top')">[QID: <?php echo $depqid; ?>]</a>
                    <?php } ?>
            </td></tr>
        <?php }
        $sCurrentCategory='';
        foreach ($advancedsettings as $aAdvancedSetting)
        {?>
        <tr>
            <td><?php echo $aAdvancedSetting['caption'];?>:</td>
            <td><?php
                    if ($aAdvancedSetting['i18n']==false)  echo $aAdvancedSetting['value']; else echo $aAdvancedSetting[$baselang]['value']?>
            </td>
        </tr>
        <?php
    }?>
            </table>
