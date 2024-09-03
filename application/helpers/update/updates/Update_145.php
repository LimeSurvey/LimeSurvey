<?php

namespace LimeSurvey\Helpers\Update;

use Exception;

/**
 * @SuppressWarnings(PHPMD)
 */
class Update_145 extends DatabaseUpdateBase
{
    public function up()
    {
        addColumn('{{surveys}}', 'savetimings', "string(1) NULL DEFAULT 'N'");
        addColumn('{{surveys}}', 'showXquestions', "string(1) NULL DEFAULT 'Y'");
        addColumn('{{surveys}}', 'showgroupinfo', "string(1) NULL DEFAULT 'B'");
        addColumn('{{surveys}}', 'shownoanswer', "string(1) NULL DEFAULT 'Y'");
        addColumn('{{surveys}}', 'showqnumcode', "string(1) NULL DEFAULT 'X'");
        addColumn('{{surveys}}', 'bouncetime', 'integer');
        addColumn('{{surveys}}', 'bounceprocessing', "string(1) NULL DEFAULT 'N'");
        addColumn('{{surveys}}', 'bounceaccounttype', "string(4)");
        addColumn('{{surveys}}', 'bounceaccounthost', "string(200)");
        addColumn('{{surveys}}', 'bounceaccountpass', "string(100)");
        addColumn('{{surveys}}', 'bounceaccountencryption', "string(3)");
        addColumn('{{surveys}}', 'bounceaccountuser', "string(200)");
        addColumn('{{surveys}}', 'showwelcome', "string(1) DEFAULT 'Y'");
        addColumn('{{surveys}}', 'showprogress', "string(1) DEFAULT 'Y'");
        addColumn('{{surveys}}', 'allowjumps', "string(1) DEFAULT 'N'");
        addColumn('{{surveys}}', 'navigationdelay', "integer DEFAULT 0");
        addColumn('{{surveys}}', 'nokeyboard', "string(1) DEFAULT 'N'");
        addColumn('{{surveys}}', 'alloweditaftercompletion', "string(1) DEFAULT 'N'");


        $aFields = array(
            'sid' => "integer NOT NULL",
            'uid' => "integer NOT NULL",
            'permission' => 'string(20) NOT NULL',
            'create_p' => "integer NOT NULL DEFAULT 0",
            'read_p' => "integer NOT NULL DEFAULT 0",
            'update_p' => "integer NOT NULL DEFAULT 0",
            'delete_p' => "integer NOT NULL DEFAULT 0",
            'import_p' => "integer NOT NULL DEFAULT 0",
            'export_p' => "integer NOT NULL DEFAULT 0"
        );
        $this->db->createCommand()->createTable('{{survey_permissions}}', $aFields);
        addPrimaryKey('survey_permissions', array('sid', 'uid', 'permission'));

        upgradeSurveyPermissions145();

        // drop the old survey rights table
        $this->db->createCommand()->dropTable('{{surveys_rights}}');

        // Add new fields for email templates
        addColumn('{{surveys_languagesettings}}', 'email_admin_notification_subj', "string");
        addColumn('{{surveys_languagesettings}}', 'email_admin_responses_subj', "string");
        addColumn('{{surveys_languagesettings}}', 'email_admin_notification', "text");
        addColumn('{{surveys_languagesettings}}', 'email_admin_responses', "text");

        //Add index to questions table to speed up subquestions
        $this->db->createCommand()->createIndex('parent_qid_idx', '{{questions}}', 'parent_qid');

        addColumn('{{surveys}}', 'emailnotificationto', "text");

        upgradeSurveys145();
        dropColumn('{{surveys}}', 'notification');
        \alterColumn('{{conditions}}', 'method', "string(5)", false, '');

        $this->db->createCommand()->renameColumn('{{surveys}}', 'private', 'anonymized');
        $this->db->createCommand()->update('{{surveys}}', array('anonymized' => 'N'), "anonymized is NULL");
        \alterColumn('{{surveys}}', 'anonymized', "string(1)", false, 'N');

        //now we clean up things that were not properly set in previous DB upgrades
        $this->db->createCommand()->update('{{answers}}', array('answer' => ''), "answer is NULL");
        $this->db->createCommand()->update('{{assessments}}', array('scope' => ''), "scope is NULL");
        $this->db->createCommand()->update('{{assessments}}', array('name' => ''), "name is NULL");
        $this->db->createCommand()->update('{{assessments}}', array('message' => ''), "message is NULL");
        $this->db->createCommand()->update('{{assessments}}', array('minimum' => ''), "minimum is NULL");
        $this->db->createCommand()->update('{{assessments}}', array('maximum' => ''), "maximum is NULL");
        $this->db->createCommand()->update('{{groups}}', array('group_name' => ''), "group_name is NULL");
        $this->db->createCommand()->update('{{labels}}', array('code' => ''), "code is NULL");
        $this->db->createCommand()->update('{{labelsets}}', array('label_name' => ''), "label_name is NULL");
        $this->db->createCommand()->update('{{questions}}', array('type' => 'T'), "type is NULL");
        $this->db->createCommand()->update('{{questions}}', array('title' => ''), "title is NULL");
        $this->db->createCommand()->update('{{questions}}', array('question' => ''), "question is NULL");
        $this->db->createCommand()->update('{{questions}}', array('other' => 'N'), "other is NULL");

        \alterColumn('{{answers}}', 'answer', "text", false);
        \alterColumn('{{answers}}', 'assessment_value', 'integer', false, '0');
        \alterColumn('{{assessments}}', 'scope', "string(5)", false, '');
        \alterColumn('{{assessments}}', 'name', "text", false);
        \alterColumn('{{assessments}}', 'message', "text", false);
        \alterColumn('{{assessments}}', 'minimum', "string(50)", false, '');
        \alterColumn('{{assessments}}', 'maximum', "string(50)", false, '');
        // change the primary index to include language
        if (
            \Yii::app(
            )->db->driverName == 'mysql'
        ) { // special treatment for mysql because this needs to be in one step since an AUTOINC field is involved
            modifyPrimaryKey('assessments', array('id', 'language'));
        } else {
            dropPrimaryKey('assessments');
            addPrimaryKey('assessments', array('id', 'language'));
        }


        \alterColumn('{{conditions}}', 'cfieldname', "string(50)", false, '');
        dropPrimaryKey('defaultvalues');
        \alterColumn('{{defaultvalues}}', 'specialtype', "string(20)", false, '');
        addPrimaryKey('defaultvalues', array('qid', 'specialtype', 'language', 'scale_id', 'sqid'));

        \alterColumn('{{groups}}', 'group_name', "string(100)", false, '');
        \alterColumn('{{labels}}', 'code', "string(5)", false, '');
        dropPrimaryKey('labels');
        \alterColumn('{{labels}}', 'language', "string(20)", false, 'en');
        addPrimaryKey('labels', array('lid', 'sortorder', 'language'));
        \alterColumn('{{labelsets}}', 'label_name', "string(100)", false, '');
        \alterColumn('{{questions}}', 'parent_qid', 'integer', false, '0');
        \alterColumn('{{questions}}', 'title', "string(20)", false, '');
        \alterColumn('{{questions}}', 'question', "text", false);
        try {
            setTransactionBookmark();
            $this->db->createCommand()->dropIndex('questions_idx4', '{{questions}}');
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        }

        \alterColumn('{{questions}}', 'type', "string(1)", false, 'T');
        try {
            $this->db->createCommand()->createIndex('questions_idx4', '{{questions}}', 'type');
        } catch (\Exception $e) {
        };
        \alterColumn('{{questions}}', 'other', "string(1)", false, 'N');
        \alterColumn('{{questions}}', 'mandatory', "string(1)");
        \alterColumn('{{question_attributes}}', 'attribute', "string(50)");
        \alterColumn('{{quota}}', 'qlimit', 'integer');

        $this->db->createCommand()->update('{{saved_control}}', array('identifier' => ''), "identifier is NULL");
        \alterColumn('{{saved_control}}', 'identifier', "text", false);
        $this->db->createCommand()->update('{{saved_control}}', array('access_code' => ''), "access_code is NULL");
        \alterColumn('{{saved_control}}', 'access_code', "text", false);
        \alterColumn('{{saved_control}}', 'email', "string(320)");
        $this->db->createCommand()->update('{{saved_control}}', array('ip' => ''), "ip is NULL");
        \alterColumn('{{saved_control}}', 'ip', "text", false);
        $this->db->createCommand()->update('{{saved_control}}', array('saved_thisstep' => ''), "saved_thisstep is NULL");
        \alterColumn('{{saved_control}}', 'saved_thisstep', "text", false);
        $this->db->createCommand()->update('{{saved_control}}', array('status' => ''), "status is NULL");
        \alterColumn('{{saved_control}}', 'status', "string(1)", false, '');
        $this->db->createCommand()->update(
            '{{saved_control}}',
            array('saved_date' => '1980-01-01 00:00:00'),
            "saved_date is NULL"
        );
        \alterColumn('{{saved_control}}', 'saved_date', "datetime", false);
        $this->db->createCommand()->update('{{settings_global}}', array('stg_value' => ''), "stg_value is NULL");
        \alterColumn('{{settings_global}}', 'stg_value', "string", false, '');

        \alterColumn('{{surveys}}', 'admin', "string(50)");
        $this->db->createCommand()->update('{{surveys}}', array('active' => 'N'), "active is NULL");

        \alterColumn('{{surveys}}', 'active', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'startdate', "datetime");
        \alterColumn('{{surveys}}', 'adminemail', "string(320)");
        \alterColumn('{{surveys}}', 'anonymized', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'faxto', "string(20)");
        \alterColumn('{{surveys}}', 'format', "string(1)");
        \alterColumn('{{surveys}}', 'language', "string(50)");
        \alterColumn('{{surveys}}', 'additional_languages', "string");
        \alterColumn('{{surveys}}', 'printanswers', "string(1)", true, 'N');
        \alterColumn('{{surveys}}', 'publicstatistics', "string(1)", true, 'N');
        \alterColumn('{{surveys}}', 'publicgraphs', "string(1)", true, 'N');
        \alterColumn('{{surveys}}', 'assessments', "string(1)", true, 'N');
        \alterColumn('{{surveys}}', 'usetokens', "string(1)", true, 'N');
        \alterColumn('{{surveys}}', 'bounce_email', "string(320)");
        \alterColumn('{{surveys}}', 'tokenlength', 'integer', true, 15);

        $this->db->createCommand()->update(
            '{{surveys_languagesettings}}',
            array('surveyls_title' => ''),
            "surveyls_title is NULL"
        );
        \alterColumn('{{surveys_languagesettings}}', 'surveyls_title', "string(200)", false);
        \alterColumn('{{surveys_languagesettings}}', 'surveyls_endtext', "text");
        \alterColumn('{{surveys_languagesettings}}', 'surveyls_url', "string");
        \alterColumn('{{surveys_languagesettings}}', 'surveyls_urldescription', "string");
        \alterColumn('{{surveys_languagesettings}}', 'surveyls_email_invite_subj', "string");
        \alterColumn('{{surveys_languagesettings}}', 'surveyls_email_remind_subj', "string");
        \alterColumn('{{surveys_languagesettings}}', 'surveyls_email_register_subj', "string");
        \alterColumn('{{surveys_languagesettings}}', 'surveyls_email_confirm_subj', "string");
        \alterColumn('{{surveys_languagesettings}}', 'surveyls_dateformat', 'integer', false, 1);

        $this->db->createCommand()->update('{{users}}', array('users_name' => ''), "users_name is NULL");
        $this->db->createCommand()->update('{{users}}', array('full_name' => ''), "full_name is NULL");
        \alterColumn('{{users}}', 'users_name', "string(64)", false, '');
        \alterColumn('{{users}}', 'full_name', "string(50)", false);
        \alterColumn('{{users}}', 'lang', "string(20)");
        \alterColumn('{{users}}', 'email', "string(320)");
        \alterColumn('{{users}}', 'superadmin', 'integer', false, 0);
        \alterColumn('{{users}}', 'htmleditormode', "string(7)", true, 'default');
        \alterColumn('{{users}}', 'dateformat', 'integer', false, 1);
        try {
            setTransactionBookmark();
            $this->db->createCommand()->dropIndex('email', '{{users}}');
        } catch (\Exception $e) {
            // do nothing
            rollBackToTransactionBookmark();
        }

        $this->db->createCommand()->update('{{user_groups}}', array('name' => ''), "name is NULL");
        $this->db->createCommand()->update('{{user_groups}}', array('description' => ''), "description is NULL");
        \alterColumn('{{user_groups}}', 'name', "string(20)", false);
        \alterColumn('{{user_groups}}', 'description', "text", false);

        try {
            $this->db->createCommand()->dropIndex('user_in_groups_idx1', '{{user_in_groups}}');
        } catch (\Exception $e) {
        }
        try {
            addPrimaryKey('user_in_groups', array('ugid', 'uid'));
        } catch (\Exception $e) {
        }

        addColumn('{{surveys_languagesettings}}', 'surveyls_numberformat', "integer NOT NULL DEFAULT 0");

        $this->db->createCommand()->createTable(
            '{{failed_login_attempts}}',
            array(
                'id' => "pk",
                'ip' => 'string(37) NOT NULL',
                'last_attempt' => 'string(20) NOT NULL',
                'number_attempts' => "integer NOT NULL"
            )
        );
        upgradeTokens145();
    }
}
