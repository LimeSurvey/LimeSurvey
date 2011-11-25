<?php
    if ((isset($failedcheck) && $failedcheck) || (isset($failedgroupcheck) && $failedgroupcheck))
    { ?>
    <div class='messagebox ui-corner-all'>
        <div class='header ui-widget-header'><?php $clang->eT("Activate Survey"); echo "($surveyid)"; ?></div>
        <div class='warningheader'><?php $clang->eT("Error"); ?><br />
        <?php $clang->eT("Survey does not pass consistency check"); ?></div>
        <p>
        <strong><?php $clang->eT("The following problems have been found:"); ?></strong><br />
        <ul>
            <?php if (isset($failedcheck) && $failedcheck)
                {
                    foreach ($failedcheck as $fc)
                    { ?>
                    <li> Question qid-<?php echo $fc[0]; ?> ("<a href='<?php echo Yii::app()->createUrl('admin/survey/view/'.$surveyid.'/'.$fc[3].'/'.$fc[0]); ?>'><?php echo $fc[1]; ?></a>")<?php echo $fc[2]; ?></li>
                    <?php }
                }
                if (isset($failedgroupcheck) && $failedgroupcheck)
                {
                    foreach ($failedgroupcheck as $fg)
                    { ?>
                    <li> Group gid-<?php echo $fg[0]; ?> ("<a href='<?php echo Yii::app()->createUrl('admin/survey/view/'.$surveyid.'/'.$fg[0]); ?>'><?php echo $fg[1]; ?></a>")<?php echo $fg[2]; ?></li>
                    <?php }
            } ?>
        </ul>
        <?php $clang->eT("The survey cannot be activated until these problems have been resolved."); ?>
    </div><br />&nbsp;


    <?php }
    else
    { ?>

    <br /><div class='messagebox ui-corner-all'>
        <div class='header ui-widget-header'><?php $clang->eT("Activate Survey"); echo "($surveyid)" ;?></div>
        <div class='warningheader'>
            <?php $clang->eT("Warning"); ?><br />
            <?php $clang->eT("READ THIS CAREFULLY BEFORE PROCEEDING"); ?>
        </div>
        <?php $clang->eT("You should only activate a survey when you are absolutely certain that your survey setup is finished and will not need changing."); ?><br /><br />
        <?php $clang->eT("Once a survey is activated you can no longer:"); ?><ul><li><?php $clang->eT("Add or delete groups"); ?></li><li><?php $clang->eT("Add or delete questions"); ?></li><li><?php $clang->eT("Add or delete subquestions or change their codes"); ?></li></ul>
        <div class='warningheader'>
            <?php $clang->eT("The following settings cannot be changed when the survey is active.");?>
        </div>
        <?php $clang->eT("Please check these settings now, then click the button below.");?>
        <form class='form44' action='<?php echo Yii::app()->createUrl("admin/survey/sa/activate/surveyid/".$surveyid); ?>' method='post'>
            <ul>
                <li><label for='anonymized'><?php echo $clang->gT("Anonymized responses?"); ?>

                        <script type="text/javascript"><!--
                            function alertPrivacy()
                            {
                                if (document.getElementById('anonymized').value == 'Y')
                                {
                                    alert('<?php echo $clang->gT("Warning"); ?>: <?php echo $clang->gT("If you turn on the -Anonymized responses- option and create a tokens table, LimeSurvey will mark your completed tokens only with a 'Y' instead of date/time to ensure the anonymity of your participants.","js"); ?>');
                                }
                            }
                            //--></script></label>

                    <select id='anonymized' name='anonymized' onchange='alertPrivacy();'>
                        <option value='Y'
                            <?php if ($aSurveysettings['anonymized'] == "Y") { ?>
                                selected='selected'
                                <?php } ?>
                            ><?php echo $clang->gT("Yes"); ?></option>
                        <option value='N'
                            <?php if ($aSurveysettings['anonymized'] != "Y") { ?>
                                selected='selected'
                                <?php } ?>
                            ><?php echo $clang->gT("No"); ?></option>
                    </select>
                </li>

                <li><label for='datestamp'><?php $clang->eT("Date stamp?"); ?></label>
                    <select id='datestamp' name='datestamp' onchange='alertPrivacy();'>
                        <option value='Y'
                            <?php if ($aSurveysettings['datestamp'] == "Y") { ?>
                                selected='selected'
                                <?php } ?>
                            ><?php $clang->eT("Yes"); ?></option>
                        <option value='N'
                            <?php if ($aSurveysettings['datestamp'] != "Y") { ?>
                                selected='selected'
                                <?php } ?>
                            ><?php $clang->eT("No"); ?></option>
                    </select>
                </li>


                <li><label for='ipaddr'><?php $clang->eT("Save IP address?"); ?></label>

                    <select name='ipaddr' id='ipaddr'>
                        <option value='Y'
                            <?php if ($aSurveysettings['ipaddr'] == "Y") { ?>
                                selected='selected'
                                <?php } ?>
                            ><?php $clang->eT("Yes"); ?></option>
                        <option value='N'
                            <?php if ($aSurveysettings['ipaddr'] != "Y") { ?>
                                selected='selected'
                                <?php } ?>
                            ><?php $clang->eT("No"); ?></option>
                    </select>
                </li>


                <li><label for='refurl'><?php $clang->eT("Save referrer URL?"); ?></label>
                    <select name='refurl' id='refurl'>
                        <option value='Y'
                            <?php if ($aSurveysettings['refurl'] == "Y") { ?>
                                selected='selected'
                                <?php } ?>
                            ><?php $clang->eT("Yes"); ?></option>
                        <option value='N'
                            <?php if ($aSurveysettings['refurl'] != "Y") { ?>
                                selected='selected'
                                <?php } ?>
                            ><?php $clang->eT("No"); ?></option>
                    </select>
                </li>

                <li><label for='savetimings'><?php $clang->eT("Save timings?"); ?></label>
                    <select id='savetimings' name='savetimings'>
                        <option value='Y'
                            <?php if (!isset($aSurveysettings['savetimings']) || !$aSurveysettings['savetimings'] || $aSurveysettings['savetimings'] == "Y") { ?> selected='selected' <?php } ?>
                            ><?php $clang->eT("Yes"); ?></option>
                        <option value='N'
                            <?php if (isset($aSurveysettings['savetimings']) && $aSurveysettings['savetimings'] == "N") { ?>  selected='selected' <?php } ?>
                            ><?php $clang->eT("No"); ?></option>
                    </select>
                </li>
            </ul>

            <?php $clang->eT("Please note that once responses have collected with this survey and you want to add or remove groups/questions or change one of the settings above, you will need to deactivate this survey, which will move all data that has already been entered into a separate archived table."); ?><br /><br />
            <input type='hidden' name='ok' value='Y' />
            <input type='submit' value="<?php $clang->eT("Save / Activate survey"); ?>" />
        </form>
    </div><br />&nbsp;
    <?php } ?>