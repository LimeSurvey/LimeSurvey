<br/>
<div class='messagebox ui-corner-all'>
    <div class='header ui-widget-header'><?php echo $clang->gT("Delete survey"); ?>
    </div>

    <?php if (!isset($surveyid) || !$surveyid)
    { ?>
        <font color='red'><strong><?php echo $clang->gT("Error"); ?></strong></font>
        <?php echo $clang->gT("You have not selected a survey to delete"); ?>
        <br /><br />
        <input type='submit' value='<?php echo $clang->gT("Main Admin Screen"); ?>' onclick="window.open('<?php echo site_url("admin"); ?>', '_top')"/>
        </td></tr></table>
        </body></html>
        <?php return;
    }

    if (!isset($deleteok) || !$deleteok)
    { ?>
        <div class='warningheader'><?php echo $clang->gT("Warning"); ?></div><br />
        <strong><?php echo $clang->gT("You are about to delete this survey"); ?> (<?php echo $surveyid; ?>)</strong><br /><br />
        <?php echo $clang->gT("This process will delete this survey, and all related groups, questions answers and conditions."); ?><br /><br />
        <?php echo $clang->gT("We recommend that before you delete this survey you export the entire survey from the main administration screen."); 
    
        if (tableExists("survey_$surveyid"))
        { ?>
            <br /><br /><?php echo $clang->gT("This survey is active and a responses table exists. If you delete this survey, these responses will be deleted. We recommend that you export the responses before deleting this survey."); ?><br /><br />
        <?php }
    
        if (tableExists("tokens_$surveyid"))
        { ?>
            <?php echo $clang->gT("This survey has an associated tokens table. If you delete this survey this tokens table will be deleted. We recommend that you export or backup these tokens before deleting this survey."); ?><br /><br />
        <?php } ?>
    
        <p>
        <input type='submit'  value='<?php echo $clang->gT("Delete survey"); ?>' onclick="<?php echo get2post("$link?action=deletesurvey&amp;sid=$surveyid&amp;deleteok=Y"); ?>" />
        <input type='submit'  value='<?php echo $clang->gT("Cancel"); ?>' onclick="window.open('<?php echo site_url("admin/survey/view/$surveyid"); ?>', '_top')" />
    <?php } 
    else
    { ?>
        <p><?php echo $clang->gT("This survey has been deleted."); ?><br /><br />
        <input type='submit' value='<?php echo $clang->gT("Main Admin Screen"); ?>' onclick="window.open('<?php echo site_url("admin"); ?>', '_top')" />
    <?php } ?>
</div><br />&nbsp;