<?php echo PrepareEditorScript(true, $this); ?>
<div class='header ui-widget-header'>
    <?php eT("Send email invitations"); ?>
</div>
<div>
    <?php if ($thissurvey[$baselang]['active'] != 'Y') { ?>
        <div class='messagebox ui-corner-all'><div class='warningheader'><?php eT('Warning!'); ?></div><?php eT("This survey is not yet activated and so your participants won't be able to fill out the survey."); ?></div>
    <?php } ?>
    <div id='tabs'>
        <ul>
            <?php
                foreach ($surveylangs as $language)
                {
                    echo '<li><a href="#' . $language . '">' . getLanguageNameFromCode($language, false);
                    if ($language == $baselang)
                    {
                        echo "(" . gT("Base language") . ")";
                    }
                    echo "</a></li>";
                }
            ?>
        </ul>
        <?php echo CHtml::form(array("admin/tokens/sa/email/surveyid/{$surveyid}"), 'post', array('id'=>'sendinvitation', 'name'=>'sendinvitation', 'class'=>'form30')); ?>
            <?php foreach ($surveylangs as $language) {

                    $fieldsarray["{ADMINNAME}"] = $thissurvey[$baselang]['adminname'];
                    $fieldsarray["{ADMINEMAIL}"] = $thissurvey[$baselang]['adminemail'];
                    $fieldsarray["{SURVEYNAME}"] = $thissurvey[$language]['name'];
                    $fieldsarray["{SURVEYDESCRIPTION}"] = $thissurvey[$language]['description'];
                    $fieldsarray["{EXPIRY}"] = $thissurvey[$baselang]["expiry"];

                    $subject = Replacefields($thissurvey[$language]['email_invite_subj'], $fieldsarray, false);
                    $textarea = Replacefields($thissurvey[$language]['email_invite'], $fieldsarray, false);
                    if ($ishtml !== true)
                    {
                        $textarea = str_replace(array('<x>', '</x>'), array(''), $textarea);
                    }
                ?>
                <div id="<?php echo $language; ?>">

                    <ul>
                        <li><label for='from_<?php echo $language; ?>'><?php eT("From"); ?>:</label>
                            <?php echo CHtml::textField("from_{$language}",$thissurvey[$baselang]['adminname']." <".$thissurvey[$baselang]['adminemail'].">",array('size'=>50)); ?>
                        </li>
                        <li><label for='subject_<?php echo $language; ?>'><?php eT("Subject"); ?>:</label>
                            <?php echo CHtml::textField("subject_{$language}",$subject,array('size'=>83)); ?>
                        </li>

                        <li><label for='message_<?php echo $language; ?>'><?php eT("Message"); ?>:</label>
                            <div class="htmleditor">
                                <?php echo CHtml::textArea("message_{$language}",$textarea,array('cols'=>80,'rows'=>20)); ?>
                                <?php echo getEditor("email-inv", "message_$language", "[" . gT("Invitation email:", "js") . "](" . $language . ")", $surveyid, '', '', "tokens"); ?>
                            </div>
                        </li>
                    </ul>
                </div>
            <?php } ?>

            <?php
            if (count($tokenids)>0)
            { ?>
                <p>
                    <label><?php eT("Send invitation email to token ID(s):"); ?></label>
                <?php echo short_implode(", ", "-", (array) $tokenids); ?></p>
            <?php } ?>
            <p>
                <label for='bypassbademails'><?php eT("Bypass token with failing email addresses"); ?>:</label>
                <?php echo CHtml::dropDownList('bypassbademails', 'Y',array("Y"=>gT("Yes"),"N"=>gT("No"))); ?>
            </p>
            <p>
                <?php
                    echo CHtml::submitButton(gT("Send Invitations"));
                    echo CHtml::hiddenField('ok','absolutely');
                    echo CHtml::hiddenField('subaction','invite');
                    if (!empty($tokenids))
                        echo CHtml::hiddenField('tokenids',implode('|', (array) $tokenids));
                ?>
            </p>
        </form>
    </div>
</div>
