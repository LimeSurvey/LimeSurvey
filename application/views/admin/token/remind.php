<?php
/**
 * Send email reminder
 */
?>

<div class="side-body">                           
	<h3><?php eT("Send email reminder"); ?></h3>        
	<div class="row">                             
		<div class="col-lg-12 content-right">
            <?php echo PrepareEditorScript(true, $this); ?>
            
            <?php if ($thissurvey['active'] != 'Y'):?>
                <?php if ($thissurvey[$baselang]['active'] != 'Y'): ?>
                    <div class="jumbotron message-box message-box-error">
                        <h2 class='text-warning'><?php eT('Warning!'); ?></h2>
                        <p class="lead text-warning">
                            <?php eT("This survey is not yet activated and so your participants won't be able to fill out the survey."); ?>    
                        </p>
                    </div>
                <?php endif; ?>                
            <?php endif; ?>
            
            <div id='tabs'>
                <ul>
                    <?php
                    foreach ($surveylangs as $language)
                    {
                        //GET SURVEY DETAILS
                        echo '<li><a href="#' . $language . '">' . getLanguageNameFromCode($language, false);
                        if ($language == $baselang)
                        {
                            echo "(" . gT("Base language") . ")";
                        }
                        echo "</a></li>";
                    }
                    ?>
                </ul>
            
                <?php echo CHtml::form(array("admin/tokens/sa/email/action/remind/surveyid/{$surveyid}"), 'post', array('id'=>'sendreminder', 'class'=>'form30')); ?>
                    <?php
                    foreach ($surveylangs as $language)
                    {
                        $fieldsarray["{ADMINNAME}"] = $thissurvey['adminname'];
                        $fieldsarray["{ADMINEMAIL}"] = $thissurvey['adminemail'];
                        $fieldsarray["{SURVEYNAME}"] = $thissurvey[$language]['name'];
                        $fieldsarray["{SURVEYDESCRIPTION}"] = $thissurvey[$language]['description'];
                        $fieldsarray["{EXPIRY}"] = $thissurvey["expiry"];
            
                        $subject = Replacefields($thissurvey[$language]['email_remind_subj'], $fieldsarray, false);
                        $textarea = Replacefields($thissurvey[$language]['email_remind'], $fieldsarray, false);
                        if ($ishtml !== true)
                        {
                            $textarea = str_replace(array('<x>', '</x>'), array(''), $textarea); // ?????
                        }
                        ?>
                        <div id="<?php echo $language; ?>">
                            <ul class="list-unstyled">
                                <li><label for='from_<?php echo $language; ?>'><?php eT("From"); ?>:</label>
                                    <?php echo CHtml::textField("from_{$language}",$thissurvey[$baselang]['adminname']." <".$thissurvey[$baselang]['adminemail'].">",array('size'=>50)); ?>
                                </li>
                                <li><label for='subject_<?php echo $language; ?>'><?php eT("Subject"); ?>:</label>
                                    <?php echo CHtml::textField("subject_{$language}",$subject,array('size'=>83)); ?>
                                </li>
            
                                <li><label for='message_<?php echo $language; ?>'><?php eT("Message"); ?>:</label>
                                    <div class="htmleditor">
                                        <?php echo CHtml::textArea("message_{$language}",$textarea,array('cols'=>80,'rows'=>20)); ?>
                                        <?php echo getEditor("email-rem", "message_$language", "[" . gT("Reminder Email:", "js") . "](" . $language . ")", $surveyid, '', '', "tokens"); ?>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    <?php } ?>
            
                <ul>
                    <?php if (count($tokenids)>0) { ?>
                        <li>
                            <label><?php eT("Send reminder to token ID(s):"); ?></label>
                            <?php echo short_implode(", ", "-", (array) $tokenids); ?>
                        </li>
                    <?php } ?>
                    <li><label for='bypassbademails'>
                            <?php eT("Bypass token with failing email addresses"); ?>:</label>
                            <?php echo CHtml::dropDownList('bypassbademails', 'Y',array("Y"=>gT("Yes"),"N"=>gT("No"))); ?>
                    </li>
                    <li>
                        <label for='minreminderdelay'><?php eT("Min days between reminders"); ?>:</label>
                        <?php echo CHtml::textField('minreminderdelay'); ?>
                    </li>
                    <li>
                        <label for='maxremindercount'><?php eT("Max reminders"); ?>:</label>
                        <?php echo CHtml::textField('maxremindercount'); ?>
                    </li>
                </ul>
                <p>
                    <?php
                        echo CHtml::submitButton(gT("Send Reminders"), array('class'=>'btn btn-default'));
                        echo CHtml::hiddenField('ok','absolutely');
                        echo CHtml::hiddenField('subaction','remind');
                        if (!empty($tokenids))
                            echo CHtml::hiddenField('tokenids',implode('|', (array) $tokenids));
                    ?>
                </p>
                </form>
            </div>
        <?php } ?>

    <ul>
        <?php if (count($tokenids)>0) { ?>
            <li>
                <label><?php eT("Send reminder to token ID(s):"); ?></label>
                <?php echo short_implode(", ", "-", (array) $tokenids); ?>
            </li>
        <?php } ?>
        <li><label for='bypassbademails'>
                <?php eT("Bypass token with failing email addresses"); ?>:</label>
                <?php echo CHtml::dropDownList('bypassbademails', 'Y',array("Y"=>gT("Yes"),"N"=>gT("No"))); ?>
        </li>
        <li>
            <label for='minreminderdelay'><?php eT("Min days between reminders"); ?>:</label>
            <?php echo CHtml::textField('minreminderdelay'); ?>
        </li>
        <li>
            <label for='maxremindercount'><?php eT("Max reminders"); ?>:</label>
            <?php echo CHtml::textField('maxremindercount'); ?>
        </li>
        <li>
              <?php echo CHtml::label(gT("Bypass date control before sending email."),'bypassdatecontrol', array('title'=>gt("If some tokens have a 'valid from' date set which is in the future, they will not be able to access the survey before that 'valid from' date."),'unescaped')); ?>
              <?php echo CHtml::checkbox('bypassdatecontrol', false); ?>
        </li>
    </ul>
    <p>
        <?php
            echo CHtml::submitButton(gT("Send Reminders"));
            echo CHtml::hiddenField('ok','absolutely');
            echo CHtml::hiddenField('subaction','remind');
            if (!empty($tokenids))
                echo CHtml::hiddenField('tokenids',implode('|', (array) $tokenids));
        ?>
    </p>
    </form>
</div>
