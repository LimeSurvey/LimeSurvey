<?php if (isset($step1))
    { ?>
    <br /><div class='messagebox ui-corner-all'>
        <div class='header ui-widget-header'><?php eT("Stop this survey");  echo "($surveyid)" ; ?></div>
        <div class='warningheader'>
            <?php eT("Warning"); ?><br /><?php eT("READ THIS CAREFULLY BEFORE PROCEEDING"); ?>
        </div>
        <p><?php eT("There are two ways to stop a survey. Please read carefully about the two options below and choose the right one for you."); ?></p>
        <table id='deactivation'><tr><th width='50%'><?php eT("Expiration"); ?></th><th><?php eT("Deactivation"); ?></th></tr>
            <tr><td><ul>
                        <li><?php eT("No responses are lost.");?></li>
                        <li><?php eT("No participant information lost.");?></li>
                        <li><?php eT("Ability to change of questions, groups and parameters is still limited.");?></li>
                        <li><?php eT("An expired survey is not accessible to participants (they only see a message that the survey has expired).");?></li>
                        <li><?php eT("It's still possible to perform statistics on responses using LimeSurvey.");?></li>
                    </ul>
                </td>
                <td>
                    <ul>
                        <li><?php eT("All responses are not accessible anymore with LimeSurvey.");?> <?php echo gT("Your response table will be renamed to:")." {$dbprefix}old_".$surveyid."_{$date}"; ?></li>
                        <li><?php eT("All participant information is lost.");?></li>
                        <li><?php eT("A deactivated survey is not accessible to participants (only a message appears that they are not permitted to see this survey).");?></li>
                        <li><?php eT("All questions, groups and parameters are editable again.");?></li>
                        <li><a title='<?php eT("Export survey results") ?>' href='<?php echo $this->createUrl('admin/export/sa/exportresults/surveyid/'.$surveyid) ?>'>
                            <?php eT("You should export your responses before deactivating.");?>
                        </li>
                    </ul>
                </td>
            </tr><tr>
                <td>
                    <?php echo CHtml::form(array("admin/survey/sa/expire/surveyid/{$surveyid}/"), 'post'); ?>
                        <p><input type='submit' value='<?php eT("Expire survey"); ?>'/></p>
                    </form>
                </td>
                <td>
                    <?php echo CHtml::form(array("admin/survey/sa/deactivate/surveyid/{$surveyid}/"), 'post'); ?>
                        <p><input type='submit' value='<?php eT("Deactivate survey"); ?>'/></p>
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
        <div class='header ui-widget-header'><?php eT("Deactivate Survey"); echo "($surveyid)"; ?></div>
        <div class='successheader'><?php eT("Survey Has Been Deactivated"); ?>
        </div>
        <p>
        <?php eT("The responses to this survey are no longer available using LimeSurvey."); ?></p>
        <p>
        <?php echo gT("The responses table has been renamed to: ")." <b>".$sNewSurveyTableName; ?></b><br>
        <?php if (isset($toldtable) && $toldtable)
            {
                echo gT("The tokens table associated with this survey has been renamed to: ")." $tnewtable<br>";
        } ?>

        <?php if (isset($sNewTimingsTableName)) echo gT("The response timings table has been renamed to: ")." ".$sNewTimingsTableName; ?><br>
        </p>
        <?php eT("You should note the name(s) of the table(s) in case you need to access this information later."); ?><br>
        <p><?php eT("Note: If you deactivated this survey in error, it is possible to restore this data easily if you do not make any changes to the survey structure. See the LimeSurvey documentation for further details"); ?></p>
    </div><br/>&nbsp;
    <?php } ?>