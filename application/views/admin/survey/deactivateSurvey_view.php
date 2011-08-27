        <?php if (isset($step1))
        { ?>
            <br /><div class='messagebox ui-corner-all'>
            <div class='header ui-widget-header'><?php echo $clang->gT("Deactivate Survey");  echo "($surveyid)" ; ?></div>
            <div class='warningheader'>
            <?php echo $clang->gT("Warning"); ?><br /><?php echo $clang->gT("READ THIS CAREFULLY BEFORE PROCEEDING"); ?>
            </div>
            <?php echo $clang->gT("In an active survey, a table is created to store all the data-entry records."); ?>
            <p><?php echo $clang->gT("When you deactivate a survey all the data entered in the original table will be moved elsewhere, and when you activate the survey again, the table will be empty. You will not be able to access this data using LimeSurvey any more."); ?></p>
            <p><?php echo $clang->gT("Deactivated survey data can only be accessed by system administrators using a Database data access tool like phpmyadmin. If your survey uses tokens, this table will also be renamed and will only be accessible by system administrators."); ?></p>
            <p><?php echo $clang->gT("Your responses table will be renamed to:"). "{$dbprefix}old_".$surveyid."_{$date}"; ?></p>
            <p><?php echo $clang->gT("Also you should export your responses before deactivating."); ?></p>
            <input type='submit' value='<?php echo $clang->gT("Deactivate Survey"); ?>' onclick="<?php echo get2post(site_url("admin/survey/deactivate/".$surveyid)."?action=deactivate&amp;ok=Y&amp;sid=$surveyid"); ?>" />
            </div><br />
        <?php }
        else
        { ?>
            <br /><div class='messagebox ui-corner-all'>
            <div class='header ui-widget-header'><?php echo $clang->gT("Deactivate Survey"); echo "($surveyid)"; ?></div>
            <div class='successheader'><?php echo $clang->gT("Survey Has Been Deactivated"); ?>
            </div>
            <p>
            <?php echo $clang->gT("The responses table has been renamed to: ")." ".$newtable; ?>
            <?php echo $clang->gT("The responses to this survey are no longer available using LimeSurvey."); ?>
            <p><?php echo $clang->gT("You should note the name of this table in case you need to access this information later."); ?></p>
            <?php if (isset($toldtable) && $toldtable)
            { 
                 echo $clang->gT("The tokens table associated with this survey has been renamed to: ")." $tnewtable";
            } ?>
            <p><?php echo $clang->gT("Note: If you deactivated this survey in error, it is possible to restore this data easily if you do not make any changes to the survey structure. See the LimeSurvey documentation for further details"); ?></p>
            </div><br/>&nbsp;
        <?php } ?>