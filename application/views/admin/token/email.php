<?php echo PrepareEditorScript(true, $this); ?>
<div class='header ui-widget-header'>
<?php $clang->eT("Send email invitations"); ?></div>
<div><br/>

    <?php if ($thissurvey[$baselang]['active'] != 'Y')
        { ?>
        <div class='messagebox ui-corner-all'><div class='warningheader'><?php $clang->eT('Warning!'); ?></div><?php $clang->eT("This survey is not yet activated and so your participants won't be able to fill out the survey."); ?></div>
        <?php } ?>
    <div id='tabs'>
        <ul>
            <?php
                foreach ($surveylangs as $language)
                {
                    echo '<li><a href="#' . $language . '">' . getLanguageNameFromCode($language, false);
                    if ($language == $baselang)
                    {
                        echo "(" . $clang->gT("Base language") . ")";
                    }
                    echo "</a></li>";
                }
            ?>
        </ul>
        <?php echo CHtml::form(array("admin/tokens/sa/email/surveyid/{$surveyid}"), 'post', array('id'=>'sendinvitation', 'name'=>'sendinvitation', 'class'=>'form30')); ?>
            <?php
                foreach ($surveylangs as $language)
                {
                    //GET SURVEY DETAILS
                    $bplang = new limesurvey_lang($language);

                    if ($ishtml === true)
                    {
                        $aDefaultTexts = templateDefaultTexts($bplang);
                    }
                    else
                    {
                        $aDefaultTexts = templateDefaultTexts($bplang, 'unescaped');
                    }
                    if (!$thissurvey[$language]['email_invite'])
                    {
                        if ($ishtml === true)
                        {
                            $thissurvey[$language]['email_invite'] = HTMLEscape($aDefaultTexts['invitation']);
                        }
                        else
                        {
                            $thissurvey[$language]['email_invite'] = $aDefaultTexts['invitation'];
                        }
                    }
                    if (!$thissurvey[$language]['email_invite_subj'])
                    {
                        $thissurvey[$language]['email_invite_subj'] = $aDefaultTexts['invitation_subject'];
                    }
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
                        <li><label for='from_<?php echo $language; ?>'><?php $clang->eT("From"); ?>:</label>
                            <?php echo CHtml::textField("from_{$language}",$thissurvey[$baselang]['adminname']." <".$thissurvey[$baselang]['adminemail'].">",array('size'=>50)); ?>
                        </li>
                        <li><label for='subject_<?php echo $language; ?>'><?php $clang->eT("Subject"); ?>:</label>
                            <?php echo CHtml::textField("subject_{$language}",$subject,array('size'=>83)); ?>
                        </li>

                        <li><label for='message_<?php echo $language; ?>'><?php $clang->eT("Message"); ?>:</label>
                            <div class="htmleditor">
                                <?php echo CHtml::textArea("message_{$language}",$textarea,array('cols'=>80,'rows'=>20)); ?>
                                <?php echo getEditor("email-inv", "message_$language", "[" . $clang->gT("Invitation email:", "js") . "](" . $language . ")", $surveyid, '', '', "tokens"); ?>
                            </div>
                        </li>
                    </ul></div>
                <?php } ?>

            <?php
            if (count($tokenids)>0)
            { ?>
                <p>
                    <label><?php $clang->eT("Send invitation email to token ID(s):"); ?></label>
                <?php echo short_implode(", ", (array) $tokenids); ?></p>
            <?php } ?>
            <p>
                <label for='bypassbademails'><?php $clang->eT("Bypass token with failing email addresses"); ?>:</label>
                <?php echo CHtml::dropDownList('bypassbademails', 'Y',array("Y"=>gT("Yes"),"N"=>gT("No"))); ?>
            </p>
            <p>
                <input type='submit' value='<?php $clang->eT("Send Invitations"); ?>' />
                <input type='hidden' name='ok' value='absolutely' />
                <input type='hidden' name='subaction' value='email' />
                <?php if (!empty($tokenids)) { ?>
                    <input type='hidden' name='tokenids' value='<?php echo implode('|', (array) $tokenids); ?>' />
                    <?php } ?>
            </p>
        </form>
    </div>
</div>
