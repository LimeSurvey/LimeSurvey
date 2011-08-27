<?php if ((isset($failedcheck) && $failedcheck) || (isset($failedgroupcheck) && $failedgroupcheck))
            { ?>
                <div class='messagebox ui-corner-all'>
                <div class='header ui-widget-header'><?php echo $clang->gT("Activate Survey"); echo "($surveyid)"; ?></div>
                <div class='warningheader'><?php echo $clang->gT("Error"); ?><br />
                <?php echo $clang->gT("Survey does not pass consistency check"); ?></div>
                <p>
                <strong><?php echo $clang->gT("The following problems have been found:"); ?></strong><br />
                <ul>
                <?php if (isset($failedcheck) && $failedcheck)
                {
                    foreach ($failedcheck as $fc)
                    { ?>
                        <li> Question qid-<?php echo $fc[0]; ?> ("<a href='<?php echo site_url('admin/survey/view/'.$surveyid.'/'.$fc[3].'/'.$fc[0]); ?>'><?php echo $fc[1]; ?></a>")<?php echo $fc[2]; ?></li>
                    <?php }
                }
                if (isset($failedgroupcheck) && $failedgroupcheck)
                {
                    foreach ($failedgroupcheck as $fg)
                    { ?>
                        <li> Group gid-<?php echo $fg[0]; ?> ("<a href='<?php echo site_url('admin/survey/view/'.$surveyid.'/'.$fg[0]); ?>'><?php echo $fg[1]; ?></a>")<?php echo $fg[2]; ?></li>
                    <?php }
                } ?>
                </ul>
                <?php echo $clang->gT("The survey cannot be activated until these problems have been resolved."); ?>
                </div><br />&nbsp;
        
                
            <?php }
            else
            { ?>
                        
                <br /><div class='messagebox ui-corner-all'>
                <div class='header ui-widget-header'><?php echo $clang->gT("Activate Survey"); echo "($surveyid)" ;?></div>
                <div class='warningheader'>
                <?php echo $clang->gT("Warning"); ?><br />
                <?php echo $clang->gT("READ THIS CAREFULLY BEFORE PROCEEDING"); ?>
                </div>
                <?php echo $clang->gT("You should only activate a survey when you are absolutely certain that your survey setup is finished and will not need changing."); ?><br /><br />
                <?php echo $clang->gT("Once a survey is activated you can no longer:"); ?><ul><li><?php echo $clang->gT("Add or delete groups"); ?></li><li><?php echo $clang->gT("Add or delete questions"); ?></li><li><?php echo $clang->gT("Add or delete subquestions or change their codes"); ?></li></ul>
                <?php echo $clang->gT("However you can still:"); ?><ul><li><?php echo $clang->gT("Edit your questions code/title/text and advanced options"); ?></li><li><?php echo $clang->gT("Edit your group names or descriptions"); ?></li><li><?php echo $clang->gT("Add, remove or edit answer options"); ?></li><li><?php echo $clang->gT("Change survey name or description"); ?></li></ul>
                <?php echo $clang->gT("Once data has been entered into this survey, if you want to add or remove groups or questions, you will need to deactivate this survey, which will move all data that has already been entered into a separate archived table."); ?><br /><br />
                <input type='submit' value="<?php echo $clang->gT("Activate Survey"); ?>" onclick="<?php echo get2post(site_url("admin/survey/activate/".$surveyid)."?action=activate&amp;ok=Y&amp;sid={$surveyid}"); ?>" />
                </div><br />&nbsp;
            <?php } ?>