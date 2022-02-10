<?php if (isset($step1)): ?>
    <div class='side-body <?php echo getSideBodyClass(false); ?>'>
        <div class="row welcome survey-action">
            <div class="col-sm-12 content-right">
                <div class="jumbotron message-box message-box-error">
                    <h2>
                        <?php eT("Stop this survey");  echo "<em>($surveyid)</em>" ; ?>
                    </h2>
                    <p class="lead text-warning">
                        <?php eT("Attention: Please read the following carefully before stopping your survey."); ?>
                    </p>
                    <p>
                        <?php eT("There are two ways to stop a survey. Please read the Expiration and Deactivation points below before proceeding."); ?>
                    </p>
                    <table id='deactivation' class="text-left">
                        <tr>
                            <th width='50%'>
                                <?php eT("Expiration"); ?>
                            </th>
                            <th>
                                <?php eT("Deactivation"); ?>
                            </th>
                        </tr>
                        <tr>
                            <td>
                                <ul>
                                    <li><?php eT("No responses are lost.");?></li>
                                    <li><?php eT("No participant information is lost.");?></li>
                                    <li><?php eT("The ability to change questions, groups and parameters is limited.");?></li>
                                    <li><?php eT("An expired survey cannot be accessed by participants.  A message will be displayed stating that the survey has expired.");?></li>
                                    <li><?php eT("It is still possible to perform statistical analysis on responses.");?></li>
                                </ul>
                            </td>
                            <td>
                                <ul>
                                    <li><?php printf(gT('Responses are no longer accessible. Your response table will be renamed to: %s_old_%d_%s'), $dbprefix, $surveyid, $date); ?></li>
                                    <li><?php eT("All participant information will be lost.");?></li>
                                    <li><?php eT("A deactivated survey cannot be accessed by participants.  A message will be displayed stating that the survey has been closed.");?></li>
                                    <li><?php eT("Questions, groups and parameters can be edited again.");?></li>
                                    <li><a title='<?php eT("Export survey results") ?>' href='<?php echo $this->createUrl('admin/export/sa/exportresults/surveyid/'.$surveyid) ?>'>
                                        <?php eT("We highly recommend that you export your responses before deactivating your survey.");?>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo CHtml::form(array("surveyAdministration/expire/surveyid/{$surveyid}/"), 'post'); ?>
                                <p><input class="btn btn-default"  type='submit' value='<?php eT("Expire survey"); ?>'/></p>
                                </form>
                            </td>
                            <td>
                                <?php echo CHtml::form(array("surveyAdministration/deactivate/surveyid/{$surveyid}/"), 'post'); ?>
                                <p><input  class="btn btn-default" type='submit' value='<?php eT("Deactivate survey"); ?>'/></p>
                                <input type='hidden' value='Y' name='ok' />
                                </form>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="jumbotron message-box" style="border: none;">
                    <a href="<?php echo Yii::app()->createUrl('surveyAdministration/view/surveyid/'.$surveyid);?>" class="btn btn-danger btn-lg">
                        <?php eT('Cancel');?>
                    </a>
                </div>
            </div>
        </div>
    </div>

<?php elseif (isset($nostep)): ?>
    <div class='side-body <?php echo getSideBodyClass(false); ?>'>
    </div>

<?php else: ?>
    <div class='side-body <?php echo getSideBodyClass(false); ?>'>
        <div class="row welcome survey-action">
            <div class="col-sm-12 content-right">
                <div class="jumbotron message-box">
                    <h2>
                        <?php eT("Deactivate Survey");
                        echo " <em>($surveyid)</em>"; ?>
                    </h2>
                    <p class="lead text-success">
                        <strong>
                            <?php eT("The survey was deactivated."); ?>
                        </strong>
                    </p>
                    <p class="text-warning">
                        <?php eT("The responses to this survey are no longer available using LimeSurvey."); ?>
                    </p>
                    <p>
                        <?php printf(gT("The responses table has been renamed to: %s"), "<b>" . $sNewSurveyTableName . "</b>"); ?>
                    </p>
                    <p>
                        <?php if (isset($toldtable) && $toldtable) {
                            printf(gT("The participant table associated with this survey has been renamed to: %s "), $tnewtable);
                        }?>
                    </p>
                    <p>
                        <?php if (isset($sNewTimingsTableName)) {
                            printf(gT("The response timings table has been renamed to: %s"), $sNewTimingsTableName); 
                        }?>
                    </p>
                    <p>
                    <?php eT("You should note the name(s) of the table(s) in case you need to access this information later."); ?><br>
                    <p><?php eT("Note: If you deactivated this survey in error, it is possible to restore this data easily if you do not make any changes to the survey structure. See the LimeSurvey documentation for further details"); ?>
                    </p>
                    <p>
                    <a href="<?php echo Yii::app()->createUrl('surveyAdministration/view/surveyid/' . $surveyid);?>" class="btn btn-default btn-lg">
                            <?php eT('Close');?>
                    </a>
                </p>
            </div>
        </div>
    </div>
<?php endif; ?>
</div>
</div>
