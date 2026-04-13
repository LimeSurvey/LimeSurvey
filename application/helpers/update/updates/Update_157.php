<?php

namespace LimeSurvey\Helpers\Update;

use Exception;

/**
 * @SuppressWarnings(PHPMD)
 */
class Update_157 extends DatabaseUpdateBase
{
    public function up()
    {
        // MySQL DB corrections
        try {
            setTransactionBookmark();
            $this->db->createCommand()->dropIndex('questions_idx4', '{{questions}}');
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        }

        \alterColumn('{{answers}}', 'assessment_value', 'integer', false, '0');
        dropPrimaryKey('answers');
        \alterColumn('{{answers}}', 'scale_id', 'integer', false, '0');
        addPrimaryKey('answers', array('qid', 'code', 'language', 'scale_id'));
        \alterColumn('{{conditions}}', 'method', "string(5)", false, '');
        \alterColumn('{{participants}}', 'owner_uid', 'integer', false);
        \alterColumn('{{participant_attribute_names}}', 'visible', 'string(5)', false);
        \alterColumn('{{questions}}', 'type', "string(1)", false, 'T');
        \alterColumn('{{questions}}', 'other', "string(1)", false, 'N');
        \alterColumn('{{questions}}', 'mandatory', "string(1)");
        \alterColumn('{{questions}}', 'scale_id', 'integer', false, '0');
        \alterColumn('{{questions}}', 'parent_qid', 'integer', false, '0');

        \alterColumn('{{questions}}', 'same_default', 'integer', false, '0');
        \alterColumn('{{quota}}', 'qlimit', 'integer');
        \alterColumn('{{quota}}', 'action', 'integer');
        \alterColumn('{{quota}}', 'active', 'integer', false, '1');
        \alterColumn('{{quota}}', 'autoload_url', 'integer', false, '0');
        \alterColumn('{{saved_control}}', 'status', "string(1)", false, '');
        try {
            setTransactionBookmark();
            \alterColumn('{{sessions}}', 'id', "string(32)", false);
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        }
        \alterColumn('{{surveys}}', 'active', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'anonymized', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'format', "string(1)");
        \alterColumn('{{surveys}}', 'savetimings', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'datestamp', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'usecookie', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'allowregister', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'allowsave', "string(1)", false, 'Y');
        \alterColumn('{{surveys}}', 'autonumber_start', 'integer', false, '0');
        \alterColumn('{{surveys}}', 'autoredirect', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'allowprev', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'printanswers', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'ipaddr', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'refurl', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'publicstatistics', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'publicgraphs', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'listpublic', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'htmlemail', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'sendconfirmation', "string(1)", false, 'Y');
        \alterColumn('{{surveys}}', 'tokenanswerspersistence', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'assessments', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'usecaptcha', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'usetokens', "string(1)", false, 'N');
        \alterColumn('{{surveys}}', 'tokenlength', 'integer', false, '15');
        \alterColumn('{{surveys}}', 'showxquestions', "string(1)", true, 'Y');
        \alterColumn('{{surveys}}', 'showgroupinfo', "string(1) ", true, 'B');
        \alterColumn('{{surveys}}', 'shownoanswer', "string(1) ", true, 'Y');
        \alterColumn('{{surveys}}', 'showqnumcode', "string(1) ", true, 'X');
        \alterColumn('{{surveys}}', 'bouncetime', 'integer');
        \alterColumn('{{surveys}}', 'showwelcome', "string(1)", true, 'Y');
        \alterColumn('{{surveys}}', 'showprogress', "string(1)", true, 'Y');
        \alterColumn('{{surveys}}', 'allowjumps', "string(1)", true, 'N');
        \alterColumn('{{surveys}}', 'navigationdelay', 'integer', false, '0');
        \alterColumn('{{surveys}}', 'nokeyboard', "string(1)", true, 'N');
        \alterColumn('{{surveys}}', 'alloweditaftercompletion', "string(1)", true, 'N');
        \alterColumn('{{surveys}}', 'googleanalyticsstyle', "string(1)");

        \alterColumn('{{surveys_languagesettings}}', 'surveyls_dateformat', 'integer', false, 1);
        try {
            setTransactionBookmark();
            \alterColumn('{{survey_permissions}}', 'sid', "integer", false);
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        }
        try {
            setTransactionBookmark();
            \alterColumn('{{survey_permissions}}', 'uid', "integer", false);
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        }
        \alterColumn('{{survey_permissions}}', 'create_p', 'integer', false, '0');
        \alterColumn('{{survey_permissions}}', 'read_p', 'integer', false, '0');
        \alterColumn('{{survey_permissions}}', 'update_p', 'integer', false, '0');
        \alterColumn('{{survey_permissions}}', 'delete_p', 'integer', false, '0');
        \alterColumn('{{survey_permissions}}', 'import_p', 'integer', false, '0');
        \alterColumn('{{survey_permissions}}', 'export_p', 'integer', false, '0');

        \alterColumn('{{survey_url_parameters}}', 'targetqid', 'integer');
        \alterColumn('{{survey_url_parameters}}', 'targetsqid', 'integer');

        \alterColumn('{{templates_rights}}', 'use', 'integer', false);

        \alterColumn('{{users}}', 'create_survey', 'integer', false, '0');
        \alterColumn('{{users}}', 'create_user', 'integer', false, '0');
        \alterColumn('{{users}}', 'participant_panel', 'integer', false, '0');
        \alterColumn('{{users}}', 'delete_user', 'integer', false, '0');
        \alterColumn('{{users}}', 'superadmin', 'integer', false, '0');
        \alterColumn('{{users}}', 'configurator', 'integer', false, '0');
        \alterColumn('{{users}}', 'manage_template', 'integer', false, '0');
        \alterColumn('{{users}}', 'manage_label', 'integer', false, '0');
        \alterColumn('{{users}}', 'dateformat', 'integer', false, 1);
        \alterColumn('{{users}}', 'participant_panel', 'integer', false, '0');
        \alterColumn('{{users}}', 'parent_id', 'integer', false);
        try {
            setTransactionBookmark();
            \alterColumn('{{surveys_languagesettings}}', 'surveyls_survey_id', "integer", false);
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        }
        \alterColumn('{{user_groups}}', 'owner_id', "integer", false);
        dropPrimaryKey('user_in_groups');
        \alterColumn('{{user_in_groups}}', 'ugid', "integer", false);
        \alterColumn('{{user_in_groups}}', 'uid', "integer", false);

        // Additional corrections for Postgres
        try {
            setTransactionBookmark();
            $this->db->createCommand()->createIndex('questions_idx3', '{{questions}}', 'gid');
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        };
        try {
            setTransactionBookmark();
            $this->db->createCommand()->createIndex('conditions_idx3', '{{conditions}}', 'cqid');
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        };
        try {
            setTransactionBookmark();
            $this->db->createCommand()->createIndex('questions_idx4', '{{questions}}', 'type');
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        };
        try {
            setTransactionBookmark();
            $this->db->createCommand()->dropIndex('user_in_groups_idx1', '{{user_in_groups}}');
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        };
        try {
            setTransactionBookmark();
            $this->db->createCommand()->dropIndex('{{user_name_key}}', '{{users}}');
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        };
        try {
            setTransactionBookmark();
            $this->db->createCommand()->createIndex('users_name', '{{users}}', 'users_name', true);
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        };
        try {
            setTransactionBookmark();
            addPrimaryKey('user_in_groups', array('ugid', 'uid'));
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        };

        \alterColumn('{{participant_attribute}}', 'value', "string(50)", false);
        try {
            setTransactionBookmark();
            \alterColumn('{{participant_attribute_names}}', 'attribute_type', "string(4)", false);
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        };
        try {
            setTransactionBookmark();
            dropColumn('{{participant_attribute_names_lang}}', 'id');
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        };
        try {
            setTransactionBookmark();
            addPrimaryKey('participant_attribute_names_lang', array('attribute_id', 'lang'));
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        };
        try {
            setTransactionBookmark();
            $this->db->createCommand()->renameColumn('{{participant_shares}}', 'shared_uid', 'share_uid');
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        };
        \alterColumn('{{participant_shares}}', 'date_added', "datetime", false);
        \alterColumn('{{participants}}', 'firstname', "string(40)");
        \alterColumn('{{participants}}', 'lastname', "string(40)");
        \alterColumn('{{participants}}', 'email', "string(80)");
        \alterColumn('{{participants}}', 'language', "string(40)");
        \alterColumn('{{quota_languagesettings}}', 'quotals_name', "string");
        try {
            setTransactionBookmark();
            \alterColumn('{{survey_permissions}}', 'sid', 'integer', false);
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        };
        try {
            setTransactionBookmark();
            \alterColumn('{{survey_permissions}}', 'uid', 'integer', false);
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        };
        \alterColumn('{{users}}', 'htmleditormode', "string(7)", true, 'default');

        // Sometimes the survey_links table was deleted before this step, if so
        // we recreate it (copied from line 663)
        if (!tableExists('{survey_links}')) {
            $this->db->createCommand()->createTable(
                '{{survey_links}}',
                array(
                    'participant_id' => 'string(50) NOT NULL',
                    'token_id' => 'integer NOT NULL',
                    'survey_id' => 'integer NOT NULL',
                    'date_created' => 'datetime NOT NULL'
                )
            );
            addPrimaryKey('survey_links', array('participant_id', 'token_id', 'survey_id'));
        }
        \alterColumn('{{survey_links}}', 'date_created', "datetime", true);
        \alterColumn('{{saved_control}}', 'identifier', "text", false);
        \alterColumn('{{saved_control}}', 'email', "string(320)");
        \alterColumn('{{surveys}}', 'adminemail', "string(320)");
        \alterColumn('{{surveys}}', 'bounce_email', "string(320)");
        \alterColumn('{{users}}', 'email', "string(320)");

        try {
            setTransactionBookmark();
            $this->db->createCommand()->dropIndex('assessments_idx', '{{assessments}}');
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        };
        try {
            setTransactionBookmark();
            $this->db->createCommand()->createIndex('assessments_idx3', '{{assessments}}', 'gid');
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        };

        try {
            setTransactionBookmark();
            $this->db->createCommand()->dropIndex('ixcode', '{{labels}}');
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        };
        try {
            setTransactionBookmark();
            $this->db->createCommand()->dropIndex('{{labels_ixcode_idx}}', '{{labels}}');
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        };
        try {
            setTransactionBookmark();
            $this->db->createCommand()->createIndex('labels_code_idx', '{{labels}}', 'code');
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        };

        if (\Yii::app()->db->driverName == 'pgsql') {
            try {
                setTransactionBookmark();
                $this->db->createCommand("ALTER TABLE ONLY {{user_groups}} ADD PRIMARY KEY (ugid); ")->execute();
            } catch (\Exception $e) {
                rollBackToTransactionBookmark();
            };
            try {
                setTransactionBookmark();
                $this->db->createCommand("ALTER TABLE ONLY {{users}} ADD PRIMARY KEY (uid); ")->execute();
            } catch (\Exception $e) {
                rollBackToTransactionBookmark();
            };
        }

        // Additional corrections for MSSQL
        \alterColumn('{{answers}}', 'answer', "text", false);
        \alterColumn('{{assessments}}', 'name', "text", false);
        \alterColumn('{{assessments}}', 'message', "text", false);
        \alterColumn('{{defaultvalues}}', 'defaultvalue', "text");
        \alterColumn('{{expression_errors}}', 'eqn', "text");
        \alterColumn('{{expression_errors}}', 'prettyprint', "text");
        \alterColumn('{{groups}}', 'description', "text");
        \alterColumn('{{groups}}', 'grelevance', "text");
        \alterColumn('{{labels}}', 'title', "text");
        \alterColumn('{{question_attributes}}', 'value', "text");
        \alterColumn('{{questions}}', 'preg', "text");
        \alterColumn('{{questions}}', 'help', "text");
        \alterColumn('{{questions}}', 'relevance', "text");
        \alterColumn('{{questions}}', 'question', "text", false);
        \alterColumn('{{quota_languagesettings}}', 'quotals_quota_id', "integer", false);
        \alterColumn('{{quota_languagesettings}}', 'quotals_message', "text", false);
        \alterColumn('{{saved_control}}', 'refurl', "text");
        \alterColumn('{{saved_control}}', 'access_code', "text", false);
        \alterColumn('{{saved_control}}', 'ip', "text", false);
        \alterColumn('{{saved_control}}', 'saved_thisstep', "text", false);
        \alterColumn('{{saved_control}}', 'saved_date', "datetime", false);
        \alterColumn('{{surveys}}', 'attributedescriptions', "text");
        \alterColumn('{{surveys}}', 'emailresponseto', "text");
        \alterColumn('{{surveys}}', 'emailnotificationto', "text");

        \alterColumn('{{surveys_languagesettings}}', 'surveyls_description', "text");
        \alterColumn('{{surveys_languagesettings}}', 'surveyls_welcometext', "text");
        \alterColumn('{{surveys_languagesettings}}', 'surveyls_email_invite', "text");
        \alterColumn('{{surveys_languagesettings}}', 'surveyls_email_remind', "text");
        \alterColumn('{{surveys_languagesettings}}', 'surveyls_email_register', "text");
        \alterColumn('{{surveys_languagesettings}}', 'surveyls_email_confirm', "text");
        \alterColumn('{{surveys_languagesettings}}', 'surveyls_attributecaptions', "text");
        \alterColumn('{{surveys_languagesettings}}', 'email_admin_notification', "text");
        \alterColumn('{{surveys_languagesettings}}', 'email_admin_responses', "text");
        \alterColumn('{{surveys_languagesettings}}', 'surveyls_endtext', "text");
        \alterColumn('{{user_groups}}', 'description', "text", false);

        \alterColumn('{{conditions}}', 'value', 'string', false, '');
        \alterColumn('{{participant_shares}}', 'can_edit', "string(5)", false);

        \alterColumn('{{users}}', 'password', "binary", false);
        dropColumn('{{users}}', 'one_time_pw');
        addColumn('{{users}}', 'one_time_pw', 'binary');

        $this->db->createCommand()->update(
            '{{question_attributes}}',
            array('value' => '1'),
            "attribute = 'random_order' and value = '2'"
        );
    }
}
