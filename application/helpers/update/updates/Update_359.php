<?php

namespace LimeSurvey\Helpers\Update;

class Update_359 extends DatabaseUpdateBase
{
    public function up()
    {
        alterColumn('{{notifications}}', 'message', "text", false);
        alterColumn('{{settings_user}}', 'stg_value', "text", true);
        alterColumn('{{surveys_languagesettings}}', 'surveyls_description', "text", true);
        alterColumn('{{surveys_languagesettings}}', 'surveyls_welcometext', "text", true);
        alterColumn('{{surveys_languagesettings}}', 'surveyls_endtext', "text", true);
        alterColumn('{{surveys_languagesettings}}', 'surveyls_policy_notice', "text", true);
        alterColumn('{{surveys_languagesettings}}', 'surveyls_policy_error', "text", true);
        alterColumn('{{surveys_languagesettings}}', 'surveyls_url', "text", true);
        alterColumn('{{surveys_languagesettings}}', 'surveyls_email_invite', "text", true);
        alterColumn('{{surveys_languagesettings}}', 'surveyls_email_remind', "text", true);
        alterColumn('{{surveys_languagesettings}}', 'surveyls_email_register', "text", true);
        alterColumn('{{surveys_languagesettings}}', 'surveyls_email_confirm', "text", true);
        alterColumn('{{surveys_languagesettings}}', 'surveyls_attributecaptions', "text", true);
        alterColumn('{{surveys_languagesettings}}', 'email_admin_notification', "text", true);
        alterColumn('{{surveys_languagesettings}}', 'email_admin_responses', "text", true);
        alterColumn('{{surveys_languagesettings}}', 'surveyls_numberformat', "integer", false, '0');
        alterColumn('{{user_groups}}', 'description', "text", false);
    }
}
