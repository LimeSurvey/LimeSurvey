<?php
/** @var Survey $oSurvey */

?>
<?php if ((isset($failedcheck) && $failedcheck) || (isset($failedgroupcheck) && $failedgroupcheck)): ?>
<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row welcome survey-action">
        <div class="col-sm-12 content-right">
            <div class="jumbotron message-box message-box-error">
                <h2><?php eT("Activate Survey"); echo " ($surveyid)"; ?></h2>
                <p class="lead text-warning"><strong><?php eT("Error"); ?> !</strong></p>
                <p class="lead text-warning"><strong><?php eT("Survey does not pass consistency check"); ?></strong></p>
                <p>
                    <?php eT("The following problems have been found:"); ?>
                </p>
                <ul class="list-unstyled">
                    <?php
                    if (isset($failedcheck) && $failedcheck)
                    {
                        foreach ($failedcheck as $fc)
                        { ?>

                        <li> Question qid-<?php echo $fc[0]; ?> ("<a href='<?php echo App()->getController()->createUrl('questionAdministration/view/surveyid/'.$surveyid.'/gid/'.$fc[3].'/qid/'.$fc[0]); ?>'><?php echo $fc[1]; ?></a>")<?php echo $fc[2]; ?></li>
                        <?php }
                    }

                    if (isset($failedgroupcheck) && $failedgroupcheck)
                    {
                        foreach ($failedgroupcheck as $fg)
                        { ?>

                        <li> Group gid-<?php echo $fg[0]; ?> ("<a href='<?php echo Yii::app()->getController()->createUrl('questionGroupsAdministration/view/surveyid/'.$surveyid.'/gid/'.$fg[0]); ?>'><?php echo flattenText($fg[1]); ?></a>")<?php echo $fg[2]; ?></li>
                        <?php }
                    } ?>
                </ul>
                <p>
                    <?php eT("The survey cannot be activated until these problems have been resolved."); ?>
                </p>

                <p>
                    <button class="btn btn-default" id="ajaxAllConsistency"><?=gT("Resolve all issues")?></button>
                    <a class="btn btn-default" href="<?php echo $this->createUrl("surveyAdministration/view/surveyid/$surveyid"); ?>" role="button">
                        <?php eT("Return to survey"); ?>
                    </a>
                </p>
            </div>

        <?php 
        App()->getClientScript()->registerScript('FixSolvableErrors', "
        $('#ajaxAllConsistency').on('click', function(e){
            e.preventDefault();
            var items = $('.selector__fixConsistencyProblem').map(function(i,item){
                return function(){
                    return $.ajax({
                        url: $(item).attr('href'),
                        beforeSend: function(){
                            $(item).prop('disabled',true).append('<i class=\"fa fa-spinner fa-pulse\"></i>');
                        },
                        complete: function(jqXHR, status){
                            if(status == 'success')
                                $(item).remove();
                            else 
                                console.log(jqXHR);
                        }
                    });
                };
            });
            var runIteration = function (arrayOfLinks, iterator){
                iterator = iterator || 0;
                if(iterator < arrayOfLinks.length){
                    arrayOfLinks[iterator]().then(function(){
                        iterator++;
                        runIteration(arrayOfLinks, iterator);
                    });
                }
            };
            runIteration(items);
        });
        ", LSYii_ClientScript::POS_POSTSCRIPT);
        ?>
        </div>
    </div>
</div>
<?php else:?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class='container message-box col-sm-12'>
        <div class="row">
            <div class="col-sm-8 col-sm-offset-2">
                <h1><?php eT("Note: Please review your survey carefully before activating"); ?></h1>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-8 col-sm-offset-2">
                <p>
                    <?php eT("Once a survey has been activated you can no longer <strong>add</strong> or <strong>delete</strong> questions, questions groups, or subquestions. You will be <strong>still able to edit</strong> questions, questions groups, or subquestions.", 'unescaped'); ?>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-8 col-sm-offset-2">
                <h3 class="pagetitle"><?php eT("Notification & data management");?></h3>
                <p>
                    <?php eT("Additionally, the following settings cannot be changed once a survey has been activated.");?>
                    <br>
                    <?php eT("Please check these settings now:");?>
                </p>
            </div>
        </div>

        <?php echo CHtml::form(array("surveyAdministration/activate/iSurveyID/{$surveyid}/"), 'post', array('class'=>'form-horizontal')); ?>
            <div class='row'>
                <div class="col-sm-4 col-sm-offset-2">
                    <div class='form-group'>
                        <label for='anonymized' class='control-label col-sm-7'>
                            <?php eT("Anonymized responses"); ?>
                            <i class="fa fa-question-circle text-success" data-toggle="tooltip" title="<?= gT("If enabled, reponses will be anonymized - there will be no way to connect responses and participants."); ?>"></i>
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
                            <?php eT("Date stamp"); ?>
                            <i class="fa fa-question-circle text-success" data-toggle="tooltip" title="<?= gT("If enabled, the submission time of a response will be recorded."); ?>"></i>
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
                            <?php eT("Save IP address"); ?>
                            <i class="fa fa-question-circle text-success" data-toggle="tooltip" title="<?= gT("If enabled, the IP address of the survey respondent will be stored together with the response."); ?>"></i>
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

                <div class="col-sm-4 ">
                    <div class='form-group'>
                        <label for='ipaddr' class='control-label col-sm-7'>
                            <?php eT("Anonymize IP address"); ?>
                            <i class="fa fa-question-circle text-success" data-toggle="tooltip" title="<?= gT("If enabled, the IP address of the respondent is not recorded."); ?>"></i>
                        </label>

                        <div class='col-sm-5'>
                            <select name='ipanonymize' id='ipanonymize' class='form-control'>
                                <option value='Y' <?php if ($aSurveysettings['ipanonymize'] == "Y") {echo "selected='selected'";} ?>>
                                    <?php eT("Yes"); ?>
                                </option>
                                <option value='N' <?php if ($aSurveysettings['ipanonymize'] != "Y") { echo "selected='selected'";} ?>>
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
                        <label class='control-label col-sm-7' for='savetimings'>
                            <?php eT("Save timings"); ?>
                            <i class="fa fa-question-circle text-success" data-toggle="tooltip" title="<?= gT("If enabled, the time spent on each page of the survey by each survey participant is recorded."); ?>"></i>
                        </label>
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

                <div class="col-sm-4">
                    <div class='form-group'>
                        <label class='control-label col-sm-7' for='refurl'>
                            <?php eT("Save referrer URL"); ?>
                            <i class="fa fa-question-circle text-success" data-toggle="tooltip" title="<?= gT("If enabled, the referrer URL will be stored together with the response."); ?>"></i>
                        </label>
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
            
            <!-- Tip -->
            <div class='row'>
                <div class='col-sm-8 col-sm-offset-2'>
                    <div class="alert alert-info">
                        <?php eT("Tip: Please note that you need to <strong>deactivate</strong> a survey if you want to <strong>add</strong> or <strong>delete</strong> groups/questions or <strong>change</strong> any of the settings above. The changes will cause all collected data from respondents to be moved and archived.", 'unescaped'); ?>
                    </div>
                    <br><br>
                </div>
            </div>

            <?php if($oSurvey->getIsDateExpired()):?>
            <div class="row">
                <div class='col-sm-8 col-sm-offset-2'>
                    <div class="alert alert-warning"><?php eT('Note: This survey has a past expiration date configured and is currently not available to participants. Please remember to update/remove the expiration date in the survey settings after activation.')?><div>
                </div>
            </div>
            <?php endif;?>

            <div class='row'>
                <div class='col-sm-6 col-sm-offset-4'>
                    <input type='hidden' name='ok' value='Y' />
                    <input id="activateSurvey__basicSettings--proceed" type='submit' class="btn btn-success btn-lg " value="<?php eT("Save & activate survey"); ?>" />
                    <a class="btn btn-default btn-lg" href="<?php echo $this->createUrl("surveyAdministration/view/", ['surveyid'=> $surveyid]); ?>" role="button">
                    <?php eT("Cancel"); ?>
                    </a>
                </div>
            </div>

            <div class='row'>
                <div class='col-sm-12'>&nbsp;</div>
            </div>

        </form>
    </div>
</div>
<?php endif;?>
