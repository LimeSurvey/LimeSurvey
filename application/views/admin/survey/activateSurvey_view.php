<?php if ((isset($failedcheck) && $failedcheck) || (isset($failedgroupcheck) && $failedgroupcheck)): ?>
<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row welcome survey-action">
        <div class="col-sm-12 content-right">
            <div class="jumbotron message-box message-box-error">
                <h2><?php eT("Activate Survey"); echo " ($surveyid)"; ?></h2>
                <p class="lead text-warning"><strong><?php eT("Error"); ?> !</strong></p>
                <p class="lead text-warning"><strong><?php eT("Survey does not pass consistency check"); ?></strong></p>
                <p>
                    <?php eT("The following problems have been found:"); ?><br />
                    <ul class="list-unstyled">
                        <?php
                        if (isset($failedcheck) && $failedcheck)
                        {
                            foreach ($failedcheck as $fc)
                            { ?>

                            <li> Question qid-<?php echo $fc[0]; ?> ("<a href='<?php echo Yii::app()->getController()->createUrl('admin/questions/sa/view/surveyid/'.$surveyid.'/gid/'.$fc[3].'/qid/'.$fc[0]); ?>'><?php echo $fc[1]; ?></a>")<?php echo $fc[2]; ?></li>
                            <?php }
                        }

                        if (isset($failedgroupcheck) && $failedgroupcheck)
                        {
                            foreach ($failedgroupcheck as $fg)
                            { ?>

                            <li> Group gid-<?php echo $fg[0]; ?> ("<a href='<?php echo Yii::app()->getController()->createUrl('admin/questiongroups/sa/view/surveyid/'.$surveyid.'/gid/'.$fg[0]); ?>'><?php echo flattenText($fg[1]); ?></a>")<?php echo $fg[2]; ?></li>
                            <?php }
                        } ?>
                    </ul>
                    <?php eT("The survey cannot be activated until these problems have been resolved."); ?>
                </p>

                <p>
                    <a class="btn btn-default" href="<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid"); ?>" role="button">
                        <?php eT("Return to survey"); ?>
                    </a>
                </p>
            </div>
</div>
<?php else:?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
<div class='container message-box col-sm-12'>

    <h2 class='col-sm-7 col-sm-offset-2'><?php eT("Warning: Please read this carefully before proceeding!"); ?></h2>

    <p class="lead col-sm-7 col-sm-offset-2">
        <?php eT("You should only activate a survey when you are absolutely certain that your survey setup is finished and will not need changing."); ?>
    </p>
    <p class="col-sm-7 col-sm-offset-2">
        <?php eT("Once a survey is activated you can no longer:"); ?>
    </p>
    <div class='col-sm-6 col-sm-offset-4'>
        <ul class=''>
            <li><?php eT("Add or delete groups"); ?></li>
            <li><?php eT("Add or delete questions"); ?></li>
            <li><?php eT("Add or delete subquestions or change their codes"); ?></li>
        </ul>
    </div>

    <div class="col-sm-7 col-sm-offset-2"><p><strong>
        <?php eT("Additionally the following settings cannot be changed when the survey is active.");?>
    </strong><br><?php eT("Please check these settings now:");?></p></div>

    <?php echo CHtml::form(array("admin/survey/sa/activate/surveyid/{$surveyid}/"), 'post', array('class'=>'form-horizontal')); ?>
        <div class='row'>
            <div class="col-sm-4 col-sm-offset-2">
                <div class='form-group'>
                    <label for='anonymized' class='control-label col-sm-7'>
                        <?php eT("Anonymized responses?"); ?>
                        <script type="text/javascript">
                            <!--
                            function alertPrivacy()
                            {
                                if (document.getElementById('anonymized').value == 'Y')
                                {
                                    alert('<?php eT("Warning"); ?>: <?php eT("If you turn on the -Anonymized responses- option and create a tokens table, LimeSurvey will mark your completed tokens only with a 'Y' instead of date/time to ensure the anonymity of your participants.","js"); ?>');
                                }
                            }
                            //-->
                        </script>
                    </label>

                    <?php /*$this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                        'name' => 'anonymizedazd',
                        'htmlOptions' => array(
                        'id' => 'anonymizedazd',
                        )
                    )); */ ?>

                    <div class='col-sm-5'>
                        <select id='anonymized' class='form-control' name='anonymized' onchange='alertPrivacy();'>
                            <option value='Y'
                            <?php if ($aSurveysettings['anonymized'] == "Y") { ?>
                                selected='selected'
                                <?php } ?>
                                ><?php eT("Yes"); ?></option>
                                <option value='N'
                                <?php if ($aSurveysettings['anonymized'] != "Y") { ?>
                                    selected='selected'
                                    <?php } ?>
                                    ><?php eT("No"); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-sm-4">
                <div class='form-group'>
                    <label for='datestamp' class='control-label col-sm-7'>
                        <?php eT("Date stamp?"); ?>
                    </label>
                    <div class='col-sm-5'>
                        <select id='datestamp' class='form-control' name='datestamp' onchange='alertDateStampAnonymization();'>
                            <option value='Y' <?php if ($aSurveysettings['datestamp'] == "Y"){echo 'selected="selected"';}?>>
                                <?php eT("Yes"); ?>
                            </option>

                            <option value='N' <?php if ($aSurveysettings['datestamp'] != "Y"){echo "selected='selected'";}?>>
                                <?php eT("No"); ?>
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class='row'>
            <div class="col-sm-4 col-sm-offset-2">
                <div class='form-group'>
                    <label for='ipaddr' class='control-label col-sm-7'>
                        <?php eT("Save IP address?"); ?>
                    </label>

                    <div class='col-sm-5'>
                        <select name='ipaddr' id='ipaddr' class='form-control'>
                            <option value='Y' <?php if ($aSurveysettings['ipaddr'] == "Y") {echo "selected='selected'";} ?>>
                                <?php eT("Yes"); ?>
                            </option>
                            <option value='N' <?php if ($aSurveysettings['ipaddr'] != "Y") { echo "selected='selected'";} ?>>
                                <?php eT("No"); ?>
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-sm-4">
                <div class='form-group'>
                    <label class='control-label col-sm-7' for='refurl'><?php eT("Save referrer URL?"); ?></label>
                    <div class='col-sm-5'>
                        <select class='form-control' name='refurl' id='refurl'>
                            <option value='Y' <?php if ($aSurveysettings['refurl'] == "Y"){echo "selected='selected'";} ?>>
                                <?php eT("Yes"); ?>
                            </option>
                            <option value='N' <?php if ($aSurveysettings['refurl'] != "Y") {echo "selected='selected'";} ?>>
                                <?php eT("No"); ?>
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-4 col-sm-offset-2">
            <div class='form-group'>
                <label class='control-label col-sm-7' for='savetimings'><?php eT("Save timings?"); ?></label>
                <div class='col-sm-5'>
                    <select class='form-control' id='savetimings' name='savetimings'>
                        <option value='Y' <?php if (!isset($aSurveysettings['savetimings']) || !$aSurveysettings['savetimings'] || $aSurveysettings['savetimings'] == "Y") { ?> selected='selected' <?php } ?>>
                            <?php eT("Yes"); ?>
                        </option>

                        <option value='N' <?php if (isset($aSurveysettings['savetimings']) && $aSurveysettings['savetimings'] == "N") { ?>  selected='selected' <?php } ?>>
                            <?php eT("No"); ?>
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <p class='col-sm-7 col-sm-offset-2'>
            <?php eT("Please note that once responses have collected with this survey and you want to add or remove groups/questions or change one of the settings above, you will need to deactivate this survey, which will move all data that has already been entered into a separate archived table."); ?><br /><br />
        </p>

        <div class='col-sm-6 col-sm-offset-4'>
            <input type='hidden' name='ok' value='Y' />
            <input id="activateSurvey__basicSettings--proceed" type='submit' class="btn btn-success btn-lg " value="<?php eT("Save & activate survey"); ?>" />
            <a class="btn btn-default btn-lg" href="<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid"); ?>" role="button">
            <?php eT("Cancel"); ?>
            </a>
        </div>

        <div class='col-sm-12'>&nbsp;</div>

    </form>
</div>
</div>
<?php endif;?>
