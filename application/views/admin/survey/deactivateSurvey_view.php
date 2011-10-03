<?php if (isset($step1))
    { ?>
    <br /><div class='messagebox ui-corner-all'>
        <div class='header ui-widget-header'><?php $clang->eT("Stop this survey");  echo "($surveyid)" ; ?></div>
        <div class='warningheader'>
            <?php $clang->eT("Warning"); ?><br /><?php $clang->eT("READ THIS CAREFULLY BEFORE PROCEEDING"); ?>
        </div>
        <p><?php $clang->eT("There are two ways to stop a survey. Please read carefully on the two options below and choose the right one for you."); ?></p>
        <table><tr><th width='50%'><?php $clang->eT("Expiration"); ?></th><th><?php $clang->eT("Deactivation"); ?></th></tr>
            <tr><td><ul>
                        <li><?php $clang->eT("No responses are lost.");?></li>
                        <li><?php $clang->eT("No participant information lost.");?></li>
                        <li><?php $clang->eT("Ability to change of questions, groups and parameters is still limited.");?></li>
                        <li><?php $clang->eT("An expired survey is not accessible to participants (they only see a message that the survey has expired).");?></li>
                        <li><?php $clang->eT("It's still possible to perform statistics on responses using LimeSurvey.");?></li>
                    </ul>
                </td>
                <td>
                    <ul>
                        <li><?php $clang->eT("All responses are not accessible anymore with LimeSurvey).");?> <?php echo $clang->gT("Your response table will be renamed to:")." {$dbprefix}old_".$surveyid."_{$date}"; ?></li>
                        <li><?php $clang->eT("All participant information is lost.");?></li>
                        <li><?php $clang->eT("A deactivated survey is not accessible to participants (only a message appears that they are not permitted to see this survey).");?></li>
                        <li><?php $clang->eT("All questions, groups and parameters are editable again.");?></li>
                        <li><?php $clang->eT("Also you should export your responses before deactivating.");?></li>
                    </ul>
                </td>
                </tr><tr>
                <td>
                    <form method="post" action="<?php echo site_url("admin/survey/expire/".$surveyid);?>">
                      <input type='submit' value='<?php $clang->eT("Expire survey"); ?>'/>
                    </form>
                </td>
                <td>
                    <form method="post" action="<?php echo site_url("admin/survey/deactivate/".$surveyid);?>">
                      <input type='submit' value='<?php $clang->eT("Deactivate survey"); ?>' onclick="<?php echo get2post(site_url("admin/survey/deactivate/".$surveyid)."?action=deactivate&amp;ok=Y&amp;sid=$surveyid"); ?>" />
                      <input type='hidden' value='Y' name='ok' />
                    </form>
                </td>
            </tr>
        </table>
    </div><br />
    <?php }
    else
    { ?>
    <br /><div class='messagebox ui-corner-all'>
        <div class='header ui-widget-header'><?php $clang->eT("Deactivate Survey"); echo "($surveyid)"; ?></div>
        <div class='successheader'><?php $clang->eT("Survey Has Been Deactivated"); ?>
        </div>
        <p>
        <?php $clang->eT("The responses table has been renamed to: ")." ".$newtable; ?>
        <?php $clang->eT("The responses to this survey are no longer available using LimeSurvey."); ?>
        <p><?php $clang->eT("You should note the name of this table in case you need to access this information later."); ?></p>
        <?php if (isset($toldtable) && $toldtable)
            {
                $clang->eT("The tokens table associated with this survey has been renamed to: ")." $tnewtable";
        } ?>
        <p><?php $clang->eT("Note: If you deactivated this survey in error, it is possible to restore this data easily if you do not make any changes to the survey structure. See the LimeSurvey documentation for further details"); ?></p>
    </div><br/>&nbsp;
    <?php } ?>