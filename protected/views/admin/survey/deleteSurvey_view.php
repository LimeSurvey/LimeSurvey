<br />
<div class='messagebox ui-corner-all'>
    <div class='header ui-widget-header'><?php eT("Delete survey"); ?>
    </div>

    <?php if (empty($surveyid) || Survey::model()->findByPk($surveyid) === null)
    { ?>
        <font color='red'><strong><?php eT("Error"); ?></strong></font>
        <?php eT("You have not selected a survey to delete"); ?>
        <br /><br />
        <input type='submit' value='<?php eT("Main Admin Screen"); ?>' onclick="window.open('<?php echo $this->createUrl('admin/index'); ?>', '_top')"/>
        </td></tr></table>
        </body></html>
        <?php return;
    }
    else
    { ?>
        <div class='warningheader'><?php eT("Warning"); ?></div><br />
        <strong><?php eT("You are about to delete this survey"); ?> (<?php echo $surveyid; ?>)</strong><br /><br />
        <?php eT("This process will delete this survey, and all related groups, questions answers and conditions."); ?><br /><br />
        <?php eT("It will also delete any resources/files that have been uploaded for this survey."); ?><br /><br />
        <?php eT("We recommend that before you delete this survey you export the entire survey from the main administration screen.");

        if (tableExists("{{survey_{$surveyid}}}"))
        { ?>
            <br /><br /><?php eT("This survey is active and a responses table exists. If you delete this survey, these responses (and files) will be deleted. We recommend that you export the responses before deleting this survey."); ?><br /><br />
            <?php }

        if (tableExists("{{tokens_{$surveyid}}}"))
        { ?>
            <?php eT("This survey has an associated tokens table. If you delete this survey this tokens table will be deleted. We recommend that you export or backup these tokens before deleting this survey."); ?><br /><br />
            <?php }

        echo CHtml::beginForm($this->createUrl("admin/survey/sa/delete/surveyid/{$surveyid}"), 'post');?>
        <input type='hidden' name='delete' value='yes'>
        <input type='submit' value='<?php eT("Delete survey"); ?>'>
        <input type='button' value='<?php eT("Cancel"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid"); ?>', '_top')" />
        <?php
        echo CHtml::endForm();
    } ?>
</div><br />&nbsp;