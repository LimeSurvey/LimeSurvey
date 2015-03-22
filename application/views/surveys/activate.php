<?php
if ((isset($failedcheck) && $failedcheck) || (isset($failedgroupcheck) && $failedgroupcheck))
{


    ?>
        <div class='header ui-widget-header'><?php eT("Activate Survey"); echo "($survey->sid)"; ?></div>
        <div class='warningheader'><?php eT("Error"); ?><br />
            <?php eT("Survey does not pass consistency check"); ?></div>
        <p>
            <strong><?php eT("The following problems have been found:"); ?></strong><br />
        <ul>
            <?php if (isset($failedcheck) && $failedcheck)
            {
                foreach ($failedcheck as $fc)
                { ?>
                    <li> Question qid-<?php echo $fc[0]; ?> ("<a href='<?php echo Yii::app()->getController()->createUrl('admin/survey/sa/view/surveyid/'.$survey->sid.'/gid/'.$fc[3].'/qid/'.$fc[0]); ?>'><?php echo $fc[1]; ?></a>")<?php echo $fc[2]; ?></li>
                <?php }
            }
            if (isset($failedgroupcheck) && $failedgroupcheck)
            {
                foreach ($failedgroupcheck as $fg)
                { ?>
                    <li> Group gid-<?php echo $fg[0]; ?> ("<a href='<?php echo Yii::app()->getController()->createUrl('admin/survey/sa/view/surveyid/'.$survey->sid.'/gid/'.$fg[0]); ?>'><?php echo $fg[1]; ?></a>")<?php echo $fg[2]; ?></li>
                <?php }
            } ?>
        </ul>
        <?php eT("The survey cannot be activated until these problems have been resolved."); ?>



<?php }
else
{ ?>

    <div class='warningheader'>
        <?php eT("Warning"); ?><br />
        <?php eT("READ THIS CAREFULLY BEFORE PROCEEDING"); ?>
    </div>
    <?php
    eT("You should only activate a survey when you are absolutely certain that your survey setup is finished and will not need changing.");
    echo TbHtml::openTag('ul');
    foreach([
        gT("Once a survey is activated you can no longer:"),
        gT("Add or delete groups"),
        gT("Add or delete questions"),
        gT("Add or delete subquestions or change their codes")
    ]   as $item) {
        echo TbHtml::tag('li', [], $item);
    }
    echo TbHtml::closeTag('ul');
    ?>
    <div class='warningheader'>
        <?php eT("The following settings cannot be changed when the survey is active.");?>
    </div>
    <?php eT("Please check these settings now, then click the button below.");?>
    <?php
    echo TbHtml::beginFormTb('horizontal', ["surveys/activate", 'id' => $survey->sid], 'post');

    echo TbHtml::well('These checkboxes are all ignored in LS3!@!', ['style' => 'background-color: #f2dede;']);

    echo TbHtml::checkBoxListControlGroup('surveyOptions', ['datestamp'], [
        'anonymized' => gT('Anonymized responses?'),
        'datestamp' => gT('Date stamp?'),
        'ip' => gT('Save IP address?'),
        'referer' => gT("Save referrer URL?"),
        'timings' => gT("Save timings?")
    ], ['label' => 'Options']);

    echo TbHtml::well(gT("Please note that once responses have collected with this survey and you want to add or remove groups/questions or change one of the settings above, you will need to deactivate this survey, which will move all data that has already been entered into a separate archived table."));
    echo TbHtml::submitButton(gT("Save / Activate survey"), ['color' => 'primary']);
    echo TbHtml::endForm();
    ?>
</div><br />&nbsp;
<?php } ?>