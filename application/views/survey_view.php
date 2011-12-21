    <?php $surveyinfo = getSurveyInfo($surveyid); ?>
    <script type='text/javascript'>
        var sReplaceTextConfirmation='<?php echo $clang->gT("This will replace the existing text. Continue?","js"); ?>'
    </script>

    <div class='header ui-widget-header'>
        <?php echo $clang->gT("Edit email templates"); ?>
    </div>
    <form class='form30newtabs' id='emailtemplates' action='<?php echo Yii::app()->createUrl('admin/emailtemplates/sa/update/action/updateemailtemplates/surveyid/'.$surveyid); ?>' method='post'>
        <div id='tabs'><ul>

        <?php foreach ($grplangs as $grouplang): ?>
            <li><a href='#tab-<?php echo $grouplang; ?>'><?php echo getLanguageNameFromCode($grouplang,false); ?>
            <?php if ($grouplang == GetBaseLanguageFromSurveyID($surveyid)): ?>
                <?php echo ' ('.$clang->gT("Base language").')'; ?>
            <?php endif; ?>
            </a></li>
        <?php endforeach; ?>
        </ul>
    <?php foreach ($grplangs as $key => $grouplang): ?>
        <?php // this one is created to get the right default texts fo each language
        $bplang = $bplangs[$key];
        $esrow = $attrib[$key];
        $aDefaultTexts = $defaulttexts[$key];
        if ($ishtml == true)
        {
            $aDefaultTexts['admin_detailed_notification']=$aDefaultTexts['admin_detailed_notification_css'].conditional_nl2br($aDefaultTexts['admin_detailed_notification'],$ishtml);
        }
        ?>

        <div id='tab-<?php echo $grouplang; ?>'>
            <div class='tabsinner' id='tabsinner-<?php echo $grouplang; ?>'>
                <ul>
                    <li><a href='#tab-<?php echo $grouplang; ?>-invitation'><?php echo $clang->gT("Invitation"); ?></a></li>
                    <li><a href='#tab-<?php echo $grouplang; ?>-reminder'><?php echo $clang->gT("Reminder"); ?></a></li>
                    <li><a href='#tab-<?php echo $grouplang; ?>-confirmation'><?php echo$clang->gT("Confirmation"); ?></a></li>
                    <li><a href='#tab-<?php echo $grouplang; ?>-registration'><?php echo $clang->gT("Registration"); ?></a></li>
                    <li><a href='#tab-<?php echo $grouplang; ?>-admin-confirmation'><?php echo $clang->gT("Basic admin notification"); ?></a></li>
                    <li><a href='#tab-<?php echo $grouplang; ?>-admin-responses'><?php echo $clang->gT("Detailed admin notification"); ?></a></li>
                </ul>

                <div id='tab-<?php echo $grouplang; ?>-admin-confirmation'>
                    <ul>
                        <li><label for='email_admin_notification_subj_<?php echo $grouplang; ?>'><?php echo $clang->gT("Admin confirmation email subject:"); ?></label>
                        <input type='text' size='80' name='email_admin_notification_subj_<?php echo $grouplang; ?>' id='email_admin_notification_subj_<?php echo $grouplang; ?>' value="<?php echo $esrow->email_admin_notification_subj; ?>" />
                        <input type='hidden' name='email_admin_notification_subj_default_<?php echo $grouplang; ?>' id='email_admin_notification_subj_default_<?php echo $grouplang; ?>' value='<?php echo $aDefaultTexts['admin_notification_subject']; ?>' />
                        <input type='button' value='<?php echo $clang->gT("Use default"); ?>' onclick='javascript: fillin("email_admin_notification_subj_<?php echo $grouplang; ?>","email_admin_notification_subj_default_<?php echo $grouplang; ?>")' />
                        </li>
                        <li><label for='email_admin_notification_<?php echo $grouplang; ?>'><?php echo $clang->gT("Admin confirmation email body:"); ?></label>
                        <textarea cols='80' rows='20' name='email_admin_notification_<?php echo $grouplang; ?>' id='email_admin_notification_<?php echo $grouplang; ?>'><?php echo htmlspecialchars($esrow->email_admin_notification); ?></textarea>
                        <?php echo getEditor("email-admin-notification","email_admin_notification_$grouplang", "[".$clang->gT("Admin notification email:", "js")."](".$grouplang.")",$surveyid,'','','editemailtemplates'); ?>
                        <input type='hidden' name='email_admin_notification_default_<?php echo $grouplang; ?>' id='email_admin_notification_default_<?php echo $grouplang; ?>' value='<?php echo htmlspecialchars(conditional_nl2br($aDefaultTexts['admin_notification'],$ishtml),ENT_QUOTES); ?>' />
                        <input type='button' value='<?php echo $clang->gT("Use default"); ?>' onclick='javascript: fillin("email_admin_notification_<?php echo $grouplang; ?>","email_admin_notification_default_<?php echo $grouplang; ?>")' />
                        </li>
                    </ul>
                </div>

                <div id='tab-<?php echo $grouplang; ?>-admin-responses'>
                    <ul>
                        <li><label for='email_admin_responses_subj_<?php echo $grouplang; ?>'><?php echo $clang->gT("Detailed admin notification subject:"); ?></label>
                        <input type='text' size='80' name='email_admin_responses_subj_<?php echo $grouplang; ?>' id='email_admin_responses_subj_<?php echo $grouplang; ?>' value="<?php echo $esrow->email_admin_responses_subj; ?>" />
                        <input type='hidden' name='email_admin_responses_subj_default_<?php echo $grouplang; ?>' id='email_admin_responses_subj_default_<?php echo $grouplang; ?>' value='$aDefaultTexts['admin_detailed_notification_subject']' />
                        <input type='button' value='<?php echo $clang->gT("Use default"); ?>' onclick='javascript: fillin("email_admin_responses_subj_<?php echo $grouplang; ?>","email_admin_responses_subj_default_<?php echo $grouplang; ?>")' />
                        </li>
                        <li><label for='email_admin_responses_<?php echo $grouplang; ?>'><?php echo $clang->gT("Detailed admin notification email:"); ?></label>
                        <textarea cols='80' rows='20' name='email_admin_responses_<?php echo $grouplang; ?>' id='email_admin_responses_<?php echo $grouplang; ?>'><?php echo htmlspecialchars($esrow->email_admin_responses); ?></textarea>
                        <?php echo getEditor("email-admin-resp","email_admin_responses_$grouplang", "[".$clang->gT("Invitation email:", "js")."](".$grouplang.")",$surveyid,'','','editemailtemplates'); ?>
                        <input type='hidden' name='email_admin_responses_default_<?php echo $grouplang; ?>' id='email_admin_responses_default_<?php echo $grouplang; ?>' value='<?php echo htmlspecialchars($aDefaultTexts['admin_detailed_notification'],ENT_QUOTES); ?>' />
                        <input type='button' value='<?php echo $clang->gT("Use default"); ?>' onclick='javascript: fillin("email_admin_responses_<?php echo $grouplang; ?>","email_admin_responses_default_<?php echo $grouplang; ?>")' />
                        </li>
                    </ul>
                </div>

                <div id='tab-<?php echo $grouplang; ?>-invitation'>
                    <ul>
                        <li><label for='email_invite_subj_<?php echo $grouplang; ?>'><?php echo $clang->gT("Invitation email subject:"); ?></label>
                        <input type='text' size='80' name='email_invite_subj_<?php echo $grouplang; ?>' id='email_invite_subj_<?php echo $grouplang; ?>' value="<?php echo $esrow->surveyls_email_invite_subj; ?>" />
                        <input type='hidden' name='email_invite_subj_default_<?php echo $grouplang; ?>' id='email_invite_subj_default_<?php echo $grouplang; ?>' value='<?php echo $aDefaultTexts['invitation_subject'] ?>' />
                        <input type='button' value='<?php echo $clang->gT("Use default"); ?>' onclick='javascript: fillin("email_invite_subj_<?php echo $grouplang; ?>","email_invite_subj_default_<?php echo $grouplang; ?>")' />
                        </li>
                        <li><label for='email_invite_<?php echo $grouplang; ?>'><?php echo $clang->gT("Invitation email:"); ?></label>
                        <textarea cols='80' rows='20' name='email_invite_<?php echo $esrow->surveyls_language; ?>' id='email_invite_<?php echo $grouplang; ?>'><?php echo htmlspecialchars($esrow->surveyls_email_invite); ?></textarea>
                        <?php echo getEditor("email-inv","email_invite_$grouplang", "[".$clang->gT("Invitation email:", "js")."](".$grouplang.")",$surveyid,'','','editemailtemplates'); ?>
                        <input type='hidden' name='email_invite_default_<?php echo $esrow->surveyls_language; ?>' id='email_invite_default_<?php echo $grouplang; ?>' value='<?php echo htmlspecialchars(conditional_nl2br($aDefaultTexts['invitation'],$ishtml),ENT_QUOTES); ?>' />
                        <input type='button' value='<?php echo $clang->gT("Use default"); ?>' onclick='javascript: fillin("email_invite_<?php echo $grouplang; ?>","email_invite_default_<?php echo $grouplang; ?>")' />
                        </li>
                    </ul>
                </div>

                <div id='tab-<?php echo $grouplang; ?>-reminder'>
                    <ul>
                        <li><label for='email_remind_subj_<?php echo $grouplang; ?>'><?php echo $clang->gT("Reminder email subject:"); ?></label>
                        <input type='text' size='80' name='email_remind_subj_<?php echo $esrow->surveyls_language; ?>' id='email_remind_subj_<?php echo $grouplang; ?>' value="<?php echo $esrow->surveyls_email_remind_subj; ?>" />
                        <input type='hidden' name='email_remind_subj_default_<?php echo $esrow->surveyls_language; ?>' id='email_remind_subj_default_<?php echo $grouplang; ?>' value='<?php echo $aDefaultTexts['reminder_subject'] ?>' />
                        <input type='button' value='<?php echo $clang->gT("Use default"); ?>' onclick='javascript: fillin("email_remind_subj_<?php echo $grouplang; ?>","email_remind_subj_default_<?php echo $grouplang; ?>")' />
                        </li>
                        <li><label for='email_remind_<?php echo $grouplang; ?>'><?php echo $clang->gT("Email reminder:"); ?></label>
                        <textarea cols='80' rows='20' name='email_remind_<?php echo $esrow->surveyls_language; ?>' id='email_remind_<?php echo $grouplang; ?>'><?php echo htmlspecialchars($esrow->surveyls_email_remind); ?></textarea>
                        <?php echo getEditor("email-rem","email_remind_$grouplang", "[".$clang->gT("Email reminder:", "js")."](".$grouplang.")",$surveyid,'','','editemailtemplates'); ?>
                        <input type='hidden' name='email_remind_default_<?php echo $esrow->surveyls_language; ?>' id='email_remind_default_<?php echo $grouplang; ?>' value='<?php echo htmlspecialchars(conditional_nl2br($aDefaultTexts['reminder'],$ishtml),ENT_QUOTES); ?>' />
                        <input type='button' value='<?php echo $clang->gT("Use default"); ?>' onclick='javascript: fillin("email_remind_<?php echo $grouplang; ?>","email_remind_default_<?php echo $grouplang; ?>")' />
                        </li>
                    </ul>
                </div>

                <div id='tab-<?php echo $grouplang; ?>-confirmation'>
                    <ul>
                    <?php if($surveyinfo['sendconfirmation'] == 'N'): ?>
                        <li><span class='warningtext'><?php echo $clang->gT("No confirmation emails will be sent. To send emails see Survey properties."); ?></span></li>
                    <?php endif; ?>
                        <li><label for='email_confirm_subj_<?php echo $grouplang; ?>'><?php echo $clang->gT("Confirmation email subject:"); ?></label>
                        <input type='text' size='80' name='email_confirm_subj_<?php echo $esrow->surveyls_language; ?>' id='email_confirm_subj_<?php echo $grouplang; ?>' value="<?php echo $esrow->surveyls_email_confirm_subj; ?>" />
                        <input type='hidden' name='email_confirm_subj_default_<?php echo $esrow->surveyls_language; ?>' id='email_confirm_subj_default_<?php echo $grouplang; ?>' value='<?php echo $aDefaultTexts['confirmation_subject'] ?>' />
                        <input type='button' value='<?php echo $clang->gT("Use default"); ?>' onclick='javascript: fillin("email_confirm_subj_<?php echo $grouplang; ?>","email_confirm_subj_default_<?php echo $grouplang; ?>")' />
                        </li>
                        <li><label for='email_confirm_<?php echo $grouplang; ?>'><?php echo $clang->gT("Confirmation email:"); ?></label>
                        <textarea cols='80' rows='20' name='email_confirm_<?php echo $esrow->surveyls_language; ?>' id='email_confirm_<?php echo $grouplang; ?>'><?php echo htmlspecialchars($esrow->surveyls_email_confirm); ?></textarea>
                        <?php echo getEditor("email-conf","email_confirm_$grouplang", "[".$clang->gT("Confirmation email", "js")."](".$grouplang.")",$surveyid,'','','editemailtemplates'); ?>
                        <input type='hidden' name='email_confirm_default_<?php echo $esrow->surveyls_language; ?>' id='email_confirm_default_<?php echo $grouplang; ?>' value='<?php echo htmlspecialchars(conditional_nl2br($aDefaultTexts['confirmation'],$ishtml),ENT_QUOTES); ?>' />
                        <input type='button' value='<?php echo $clang->gT("Use default"); ?>' onclick='javascript: fillin("email_confirm_<?php echo $grouplang; ?>","email_confirm_default_<?php echo $grouplang; ?>")' />
                        </li>
                    </ul>
                </div>

                <div id='tab-<?php echo $grouplang; ?>-registration'>
                    <ul>
                        <li><label for='email_register_subj_<?php echo $grouplang; ?>'><?php echo $clang->gT("Public registration email subject:"); ?></label>
                        <input type='text' size='80' name='email_register_subj_<?php echo $esrow->surveyls_language; ?>' id='email_register_subj_<?php echo $grouplang; ?>' value="<?php echo $esrow->surveyls_email_register_subj; ?>" />
                        <input type='hidden' name='email_register_subj_default_<?php echo $esrow->surveyls_language; ?>' id='email_register_subj_default_<?php echo $grouplang; ?>' value='<?php echo $aDefaultTexts['registration_subject'] ?>' />
                        <input type='button' value='<?php echo $clang->gT("Use default"); ?>' onclick='javascript: fillin("email_register_subj_<?php echo $grouplang; ?>","email_register_subj_default_<?php echo $grouplang; ?>")' />
                        </li>
                        <li><label for='email_register_<?php echo $grouplang; ?>'><?php echo $clang->gT("Public registration email:"); ?></label>
                        <textarea cols='80' rows='20' name='email_register_<?php echo $grouplang; ?>' id='email_register_<?php echo $grouplang; ?>'><?php echo htmlspecialchars($esrow->surveyls_email_register); ?></textarea>
                        <?php echo getEditor("email-reg","email_register_$grouplang", "[".$clang->gT("Public registration email:", "js")."](".$grouplang.")",$surveyid,'','','editemailtemplates'); ?>
                        <input type='hidden' name='email_register_default_<?php echo $esrow->surveyls_language; ?>' id='email_register_default_<?php echo $grouplang; ?>' value='<?php echo htmlspecialchars(conditional_nl2br($aDefaultTexts['registration'],$ishtml),ENT_QUOTES); ?>' />
                        <input type='button' value='<?php echo $clang->gT("Use default"); ?>' onclick='javascript: fillin("email_register_<?php echo $grouplang; ?>","email_register_default_<?php echo $grouplang; ?>")' />
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    <p>
        <input type='submit' class='standardbtn' value='<?php echo $clang->gT("Save"); ?>' />
        <input type='hidden' name='action' value='tokens' />
        <input type='hidden' name='language' value="<?php echo $esrow->surveyls_language; ?>" />
    </p>
    </form>