    <div class='header ui-widget-header'><?php eT("Stop this survey");  echo "($survey->sid)" ; ?></div>
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
                    <li><?php eT("All responses are not accessible anymore with LimeSurvey.");?> <?php echo gT("Your response table will be renamed to:")." {$survey->dbConnection->tablePrefix}old_survey_{$survey->sid}_" . date('Ymd'); ?></li>
                    <li><?php eT("All participant information is lost.");?></li>
                    <li><?php eT("A deactivated survey is not accessible to participants (only a message appears that they are not permitted to see this survey).");?></li>
                    <li><?php eT("All questions, groups and parameters are editable again.");?></li>
                    <li><a title='<?php eT("Export survey results") ?>' href='<?php echo App()->createUrl("admin/export", ["sa" => "exportresults", "surveyid" => $survey->sid]); ?>'>
                            <?php eT("You should export your responses before deactivating.");?>
                    </li>
                </ul>
            </td>
        </tr><tr>
            <td>
                <?php echo CHtml::form(["admin/survey", "sa" => "expire", "surveyid" => $survey->sid], 'post'); ?>
                <p><input type='submit' value='<?php eT("Expire survey"); ?>'/></p>
                </form>
            </td>
            <td>
                <?php echo CHtml::form(["surveys/deactivate", "id" => $survey->sid], 'post'); ?>
                <p><input type='submit' value='<?php eT("Deactivate survey"); ?>'/></p>
                <input type='hidden' value='Y' name='ok' />
                </form>
            </td>
        </tr>
    </table>
</div><br />
