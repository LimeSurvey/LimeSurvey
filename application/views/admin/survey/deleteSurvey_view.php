<br />
<div class='messagebox ui-corner-all'>
    <div class='header ui-widget-header'><?php echo $clang->gT("Delete survey"); ?>
    </div>

    <?php if (!isset($surveyid) || !$surveyid)
    { ?>
        <font color='red'><strong><?php echo $clang->gT("Error"); ?></strong></font>
        <?php echo $clang->gT("You have not selected a survey to delete"); ?>
        <br /><br />
        <input type='submit' value='<?php echo $clang->gT("Main Admin Screen"); ?>' onclick="window.open('<?php echo Yii::app()->createUrl("admin"); ?>', '_top')"/>
        </td></tr></table>
        </body></html>
        <?php return;
    }
	else
	{ ?>
        <div class='warningheader'><?php echo $clang->gT("Warning"); ?></div><br />
        <strong><?php echo $clang->gT("You are about to delete this survey"); ?> (<?php echo $surveyid; ?>)</strong><br /><br />
        <?php echo $clang->gT("This process will delete this survey, and all related groups, questions answers and conditions."); ?><br /><br />
        <?php echo $clang->gT("It will also delete any resources/files that have been uploaded for this survey."); ?><br /><br />
        <?php echo $clang->gT("We recommend that before you delete this survey you export the entire survey from the main administration screen.");

        if (Yii::app()->db->schema->getTable('{{survey_$surveyid}}'))
        { ?>
            <br /><br /><?php echo $clang->gT("This survey is active and a responses table exists. If you delete this survey, these responses (and files) will be deleted. We recommend that you export the responses before deleting this survey."); ?><br /><br />
        <?php }

        if (Yii::app()->db->schema->getTable('{{tokens_$surveyid}}'))
        { ?>
            <?php echo $clang->gT("This survey has an associated tokens table. If you delete this survey this tokens table will be deleted. We recommend that you export or backup these tokens before deleting this survey."); ?><br /><br />
        <?php } ?>

        <p>
        <input type='submit'  value='<?php echo $clang->gT("Delete survey"); ?>' onclick="window.open('<?php echo Yii::app()->createUrl("admin/survey/delete") . "?action=delete&amp;sid=$surveyid"; ?>', '_top')" />
        <input type='submit'  value='<?php echo $clang->gT("Cancel"); ?>' onclick="window.open('<?php echo Yii::app()->createUrl("admin/survey/view/$surveyid"); ?>', '_top')" />
    <?php } ?>
</div><br />&nbsp;