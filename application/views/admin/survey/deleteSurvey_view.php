<br />
<div class='messagebox ui-corner-all'>
    <div class='header ui-widget-header'><?php $clang->eT("Delete survey"); ?>
    </div>

    <?php if (empty($surveyid) || Survey::model()->findByPk($surveyid) === null)
    { ?>
        <font color='red'><strong><?php $clang->eT("Error"); ?></strong></font>
        <?php $clang->eT("You have not selected a survey to delete"); ?>
        <br /><br />
        <input type='submit' value='<?php $clang->eT("Main Admin Screen"); ?>' onclick="window.open('<?php echo $this->createUrl('admin/index'); ?>', '_top')"/>
        </td></tr></table>
        </body></html>
        <?php return;
    }
	else
	{ ?>
        <div class='warningheader'><?php $clang->eT("Warning"); ?></div><br />
        <strong><?php $clang->eT("You are about to delete this survey"); ?> (<?php echo $surveyid; ?>)</strong><br /><br />
        <?php $clang->eT("This process will delete this survey, and all related groups, questions answers and conditions."); ?><br /><br />
        <?php $clang->eT("It will also delete any resources/files that have been uploaded for this survey."); ?><br /><br />
        <?php $clang->eT("We recommend that before you delete this survey you export the entire survey from the main administration screen.");

        if (tableExists("{{survey_{$surveyid}}}"))
        { ?>
            <br /><br /><?php $clang->eT("This survey is active and a responses table exists. If you delete this survey, these responses (and files) will be deleted. We recommend that you export the responses before deleting this survey."); ?><br /><br />
        <?php }

        if (tableExists("{{tokens_{$surveyid}}}"))
        { ?>
            <?php $clang->eT("This survey has an associated tokens table. If you delete this survey this tokens table will be deleted. We recommend that you export or backup these tokens before deleting this survey."); ?><br /><br />
        <?php } ?>

        <?php echo CHtml::beginForm($this->createUrl("admin/survey/sa/delete/surveyid/{$surveyid}"), 'post');?>
        <input type='hidden' name='delete' value='yes'>
        <input type='submit' value='<?php $clang->eT("Delete survey"); ?>'>
        <input type='button' value='<?php $clang->eT("Cancel"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid"); ?>', '_top')" />
        <?php
        echo CHtml::endForm();
    } ?>
</div><br />&nbsp;