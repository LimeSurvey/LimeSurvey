<?php
/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
* $Id:
*
*/

  include_once("login_check.php");  //Login Check dies also if the script is started directly

    $js_admin_includes[]='scripts/emailtemplates.js';
    if(isset($surveyid) && getEmailFormat($surveyid) == 'html')
    {
        $ishtml=true;
    }
    else
    {
        $ishtml=false;
    }
    $grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    array_unshift($grplangs,$baselang);

    $sHTMLOutput = PrepareEditorScript();
    // Inject necessary strings for Javascript functions
    $sHTMLOutput .= "<script type='text/javascript'>
                          var sReplaceTextConfirmation='".$clang->gT("This will replace the existing text. Continue?","js")."'
                       </script>\n";
    $sHTMLOutput .="<div class='header ui-widget-header'>\n".$clang->gT("Edit email templates")."</div>\n"
    . "<form class='form30newtabs' id='emailtemplates' action='$scriptname' method='post'>\n"
    . "<div id='tabs'><ul>";
    $surveyinfo=getSurveyInfo($surveyid);

    foreach ($grplangs as $grouplang)
    {
        $sHTMLOutput.="<li><a href='#tab-{$grouplang}'>".getLanguageNameFromCode($grouplang,false);
        if ($grouplang==GetBaseLanguageFromSurveyID($surveyid)) {$sHTMLOutput .= ' ('.$clang->gT("Base language").')';}
        $sHTMLOutput.="</a></li>";
    }
    $sHTMLOutput.="</ul>";
    foreach ($grplangs as $grouplang)
    {
        // this one is created to get the right default texts fo each language
        $bplang = new limesurvey_lang($grouplang);
        $esquery = "SELECT * FROM ".db_table_name("surveys_languagesettings")." WHERE surveyls_survey_id=$surveyid and surveyls_language='$grouplang'";
        $esresult = db_execute_assoc($esquery);
        $esrow = $esresult->FetchRow();
        $aDefaultTexts=aTemplateDefaultTexts($bplang);
        if ($ishtml==true){
            $aDefaultTexts['admin_detailed_notification']=$aDefaultTexts['admin_detailed_notification_css'].conditional_nl2br($aDefaultTexts['admin_detailed_notification'],$ishtml);
        }

        $sHTMLOutput .= "<div id='tab-{$grouplang}'>";
        $sHTMLOutput .= "<div class='tabsinner' id='tabsinner-{$grouplang}'>"
        ."<ul>"
        ."<li><a href='#tab-{$grouplang}-invitation'>".$clang->gT("Invitation")."</a></li>"
        ."<li><a href='#tab-{$grouplang}-reminder'>".$clang->gT("Reminder")."</a></li>"
        ."<li><a href='#tab-{$grouplang}-confirmation'>".$clang->gT("Confirmation")."</a></li>"
        ."<li><a href='#tab-{$grouplang}-registration'>".$clang->gT("Registration")."</a></li>"
        ."<li><a href='#tab-{$grouplang}-admin-confirmation'>".$clang->gT("Basic admin notification")."</a></li>"
        ."<li><a href='#tab-{$grouplang}-admin-responses'>".$clang->gT("Detailed admin notification")."</a></li>"
        ."</ul>"

        ."<div id='tab-{$grouplang}-admin-confirmation'>";
        $sHTMLOutput .= "<ul><li><label for='email_admin_notification_subj_{$grouplang}'>".$clang->gT("Admin confirmation email subject:")."</label>\n"
        . "<input type='text' size='80' name='email_admin_notification_subj_{$grouplang}' id='email_admin_notification_subj_{$grouplang}' value=\"{$esrow['email_admin_notification_subj']}\" />\n"
        . "<input type='hidden' name='email_admin_notification_subj_default_{$grouplang}' id='email_admin_notification_subj_default_{$grouplang}' value='".$aDefaultTexts['admin_notification_subject']."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_admin_notification_subj_{$grouplang}\",\"email_admin_notification_subj_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .= "<li><label for='email_admin_notification_{$grouplang}'>".$clang->gT("Admin confirmation email body:")."</label>\n"
        . "<textarea cols='80' rows='20' name='email_admin_notification_{$grouplang}' id='email_admin_notification_{$grouplang}'>".htmlspecialchars($esrow['email_admin_notification'])."</textarea>\n"
        . getEditor("email-admin-conf","email_admin_notification_{$grouplang}", "[".$clang->gT("Invitation email:", "js")."](".$grouplang.")",$surveyid,'','',$action)
        . "<input type='hidden' name='email_admin_notification_default_{$grouplang}' id='email_admin_notification_default_{$grouplang}' value='".htmlspecialchars(conditional_nl2br($aDefaultTexts['admin_notification'],$ishtml),ENT_QUOTES)."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_admin_notification_{$grouplang}\",\"email_admin_notification_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .="</ul></div>"

        ."<div id='tab-{$grouplang}-admin-responses'>";
        $sHTMLOutput .= "<ul><li><label for='email_admin_responses_subj_{$grouplang}'>".$clang->gT("Detailed admin notification subject:")."</label>\n"
        . "<input type='text' size='80' name='email_admin_responses_subj_{$grouplang}' id='email_admin_responses_subj_{$grouplang}' value=\"{$esrow['email_admin_responses_subj']}\" />\n"
        . "<input type='hidden' name='email_admin_responses_subj_default_{$grouplang}' id='email_admin_responses_subj_default_{$grouplang}' value='{$aDefaultTexts['admin_detailed_notification_subject']}' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_admin_responses_subj_{$grouplang}\",\"email_admin_responses_subj_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .= "<li><label for='email_admin_responses_{$grouplang}'>".$clang->gT("Detailed admin notification email:")."</label>\n"
        . "<textarea cols='80' rows='20' name='email_admin_responses_{$grouplang}' id='email_admin_responses_{$grouplang}'>".htmlspecialchars($esrow['email_admin_responses'])."</textarea>\n"
        . getEditor("email-admin-resp","email_admin_responses_{$grouplang}", "[".$clang->gT("Invitation email:", "js")."](".$grouplang.")",$surveyid,'','',$action)
        . "<input type='hidden' name='email_admin_responses_default_{$grouplang}' id='email_admin_responses_default_{$grouplang}' value='".htmlspecialchars($aDefaultTexts['admin_detailed_notification'],ENT_QUOTES)."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_admin_responses_{$grouplang}\",\"email_admin_responses_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .="</ul></div>"

        ."<div id='tab-{$grouplang}-invitation'>";
        $sHTMLOutput .= "<ul><li><label for='email_invite_subj_{$grouplang}'>".$clang->gT("Invitation email subject:")."</label>\n"
        . "<input type='text' size='80' name='email_invite_subj_{$grouplang}' id='email_invite_subj_{$grouplang}' value=\"{$esrow['surveyls_email_invite_subj']}\" />\n"
        . "<input type='hidden' name='email_invite_subj_default_{$grouplang}' id='email_invite_subj_default_{$grouplang}' value='{$aDefaultTexts['invitation_subject']}' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_invite_subj_{$grouplang}\",\"email_invite_subj_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .= "<li><label for='email_invite_{$grouplang}'>".$clang->gT("Invitation email:")."</label>\n"
        . "<textarea cols='80' rows='20' name='email_invite_".$esrow['surveyls_language']."' id='email_invite_{$grouplang}'>".htmlspecialchars($esrow['surveyls_email_invite'])."</textarea>\n"
        . getEditor("email-inv","email_invite_{$grouplang}", "[".$clang->gT("Invitation email:", "js")."](".$grouplang.")",$surveyid,'','',$action)
        . "<input type='hidden' name='email_invite_default_".$esrow['surveyls_language']."' id='email_invite_default_{$grouplang}' value='".htmlspecialchars(conditional_nl2br($aDefaultTexts['invitation'],$ishtml),ENT_QUOTES)."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_invite_{$grouplang}\",\"email_invite_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .="</ul></div>"

        ."<div id='tab-{$grouplang}-reminder'>";
        $sHTMLOutput .= "<ul><li><label for='email_remind_subj_{$grouplang}'>".$clang->gT("Reminder email subject:")."</label>\n"
        . "<input type='text' size='80' name='email_remind_subj_".$esrow['surveyls_language']."' id='email_remind_subj_{$grouplang}' value=\"{$esrow['surveyls_email_remind_subj']}\" />\n"
        . "<input type='hidden' name='email_remind_subj_default_".$esrow['surveyls_language']."' id='email_remind_subj_default_{$grouplang}' value='{$aDefaultTexts['reminder_subject']}' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_remind_subj_{$grouplang}\",\"email_remind_subj_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .= "<li><label for='email_remind_{$grouplang}'>".$clang->gT("Email reminder:")."</label>\n"
        . "<textarea cols='80' rows='20' name='email_remind_".$esrow['surveyls_language']."' id='email_remind_{$grouplang}'>".htmlspecialchars($esrow['surveyls_email_remind'])."</textarea>\n"
        . getEditor("email-rem","email_remind_{$grouplang}", "[".$clang->gT("Email reminder:", "js")."](".$grouplang.")",$surveyid,'','',$action)
        . "<input type='hidden' name='email_remind_default_".$esrow['surveyls_language']."' id='email_remind_default_{$grouplang}' value='".htmlspecialchars(conditional_nl2br($aDefaultTexts['reminder'],$ishtml),ENT_QUOTES)."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_remind_{$grouplang}\",\"email_remind_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .="</ul></div>"

        ."<div id='tab-{$grouplang}-confirmation'>";
        $sHTMLOutput .= "<ul><li><label for='email_confirm_subj_{$grouplang}'>".$clang->gT("Confirmation email subject:")."</label>\n"
        . "<input type='text' size='80' name='email_confirm_subj_".$esrow['surveyls_language']."' id='email_confirm_subj_{$grouplang}' value=\"{$esrow['surveyls_email_confirm_subj']}\" />\n"
        . "<input type='hidden' name='email_confirm_subj_default_".$esrow['surveyls_language']."' id='email_confirm_subj_default_{$grouplang}' value='{$aDefaultTexts['confirmation_subject']}' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_confirm_subj_{$grouplang}\",\"email_confirm_subj_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .= "<li><label for='email_confirm_{$grouplang}'>".$clang->gT("Confirmation email:")."</label>\n"
        . "<textarea cols='80' rows='20' name='email_confirm_".$esrow['surveyls_language']."' id='email_confirm_{$grouplang}'>".htmlspecialchars($esrow['surveyls_email_confirm'])."</textarea>\n"
        . getEditor("email-conf","email_confirm_{$grouplang}", "[".$clang->gT("Confirmation email", "js")."](".$grouplang.")",$surveyid,'','',$action)
        . "<input type='hidden' name='email_confirm_default_".$esrow['surveyls_language']."' id='email_confirm_default_{$grouplang}' value='".htmlspecialchars(conditional_nl2br($aDefaultTexts['confirmation'],$ishtml),ENT_QUOTES)."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript: fillin(\"email_confirm_{$grouplang}\",\"email_confirm_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .="</ul></div>"

        ."<div id='tab-{$grouplang}-registration'>";
        $sHTMLOutput .= "<ul><li><label for='email_register_subj_{$grouplang}'>".$clang->gT("Public registration email subject:")."</label>\n"
        . "<input type='text' size='80' name='email_register_subj_".$esrow['surveyls_language']."' id='email_register_subj_{$grouplang}' value=\"{$esrow['surveyls_email_register_subj']}\" />\n"
        . "<input type='hidden' name='email_register_subj_default_".$esrow['surveyls_language']."' id='email_register_subj_default_{$grouplang}' value='{$aDefaultTexts['registration_subject']}' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript:  fillin(\"email_register_subj_{$grouplang}\",\"email_register_subj_default_{$grouplang}\")' />\n"
        . "\t</li>\n";
        $sHTMLOutput .= "<li><label for='email_register_{$grouplang}'>".$clang->gT("Public registration email:")."</label>\n"
        . "<textarea cols='80' rows='20' name='email_register_{$grouplang}' id='email_register_{$grouplang}'>".htmlspecialchars($esrow['surveyls_email_register'])."</textarea>\n"
        . getEditor("email-reg","email_register_{$grouplang}", "[".$clang->gT("Public registration email:", "js")."](".$grouplang.")",$surveyid,'','',$action)
        . "<input type='hidden' name='email_register_default_".$esrow['surveyls_language']."' id='email_register_default_{$grouplang}' value='".htmlspecialchars(conditional_nl2br($aDefaultTexts['registration'],$ishtml),ENT_QUOTES)."' />\n"
        . "<input type='button' value='".$clang->gT("Use default")."' onclick='javascript:  fillin(\"email_register_{$grouplang}\",\"email_register_default_{$grouplang}\")' />\n"
        . "\t</li></ul>";
        $sHTMLOutput .="</div>" // tab

        ."</div>" // tabinner
        ."</div>"; // language tab
    }
    $sHTMLOutput .= '</div>';
    $sHTMLOutput .= "\t<p><input type='submit' class='standardbtn' value='".$clang->gT("Save")."' />\n"
    . "\t<input type='hidden' name='action' value='tokens' />\n"
    . "\t<input type='hidden' name='action' value='updateemailtemplates' />\n"
    . "\t<input type='hidden' name='sid' value=\"{$surveyid}\" />\n"
    . "\t<input type='hidden' name='language' value=\"{$esrow['surveyls_language']}\" />\n"
    . "</form>";

?>
