<?php

namespace LimeSurvey\Helpers\Update;

/**
 * @SuppressWarnings(PHPMD)
 */
class Update_400 extends DatabaseUpdateBase
{
    public function up()
    {
        if ($this->db->driverName == 'mysql') {
            $this->db->createCommand(
                "ALTER DATABASE `" . getDBConnectionStringProperty(
                    'dbname'
                ) . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
            );
        }

        // Question table
        /* l10ns question table */
        if ($this->db->schema->getTable('{{question_l10ns}}')) {
            $this->db->createCommand()->dropTable('{{question_l10ns}}');
        }
        $this->db->createCommand()->createTable(
            '{{question_l10ns}}',
            array(
                'id' => "pk",
                'qid' => "integer NOT NULL",
                'question' => "mediumtext NOT NULL",
                'help' => "mediumtext",
                'language' => "string(20) NOT NULL"
            ),
            $this->options
        );
        $this->db->createCommand()->createIndex(
            '{{idx1_question_l10ns}}',
            '{{question_l10ns}}',
            ['qid', 'language'],
            true
        );
        $this->db->createCommand(
            "INSERT INTO {{question_l10ns}} (qid, question, help, language) select qid, question, help, language from {{questions}}"
        )->execute();
        /* questions by rename/insert */
        if ($this->db->schema->getTable('{{questions_update400}}')) {
            $this->db->createCommand()->dropTable('{{questions_update400}}');
        }
        $this->db->createCommand()->renameTable('{{questions}}', '{{questions_update400}}');
        $this->db->createCommand()->createTable(
            '{{questions}}',
            array(
                'qid' => "pk",
                'parent_qid' => "integer NOT NULL DEFAULT '0'",
                'sid' => "integer NOT NULL DEFAULT '0'",
                'gid' => "integer NOT NULL DEFAULT '0'",
                'type' => "string(30) NOT NULL DEFAULT 'T'",
                'title' => "string(20) NOT NULL DEFAULT ''",
                'preg' => "text",
                'other' => "string(1) NOT NULL DEFAULT 'N'",
                'mandatory' => "string(1) NULL",
                //'encrypted' =>  "string(1) NULL DEFAULT 'N'", DB version 406
                'question_order' => "integer NOT NULL",
                'scale_id' => "integer NOT NULL DEFAULT '0'",
                'same_default' => "integer NOT NULL DEFAULT '0'",
                'relevance' => "text",
                'modulename' => "string(255) NULL"
            ),
            $this->options
        );
        switchMSSQLIdentityInsert('questions', true); // Untested
        $this->db->createCommand(
            "INSERT INTO {{questions}}
            (qid, parent_qid, sid, gid, type, title, preg, other, mandatory, question_order, scale_id, same_default, relevance, modulename)
            SELECT qid, parent_qid, {{questions_update400}}.sid, gid, type, title, COALESCE(preg,''), other, COALESCE(mandatory,''), question_order, scale_id, same_default, COALESCE(relevance,''), COALESCE(modulename,'')
            FROM {{questions_update400}}
            INNER JOIN {{surveys}} ON {{questions_update400}}.sid = {{surveys}}.sid AND {{questions_update400}}.language = {{surveys}}.language
            "
        )->execute();

        switchMSSQLIdentityInsert('questions', false); // Untested
        $this->db->createCommand()->dropTable('{{questions_update400}}'); // Drop the table before create index for pgsql
        $this->db->createCommand()->createIndex('{{idx1_questions}}', '{{questions}}', 'sid', false);
        $this->db->createCommand()->createIndex('{{idx2_questions}}', '{{questions}}', 'gid', false);
        $this->db->createCommand()->createIndex('{{idx3_questions}}', '{{questions}}', 'type', false);
        $this->db->createCommand()->createIndex('{{idx4_questions}}', '{{questions}}', 'title', false);
        $this->db->createCommand()->createIndex('{{idx5_questions}}', '{{questions}}', 'parent_qid', false);

        // Groups table
        if ($this->db->schema->getTable('{{group_l10ns}}')) {
            $this->db->createCommand()->dropTable('{{group_l10ns}}');
        }

        $this->db->createCommand()->createTable(
            '{{group_l10ns}}',
            array(
                'id' => "pk",
                'gid' => "integer NOT NULL",
                'group_name' => "text NOT NULL",
                'description' => "mediumtext",
                'language' => "string(20) NOT NULL"
            ),
            $this->options
        );
        $this->db->createCommand()->createIndex('{{idx1_group_l10ns}}', '{{group_l10ns}}', ['gid', 'language'], true);
        $quotedGroups = $this->db->quoteTableName('{{groups}}');
        $this->db->createCommand(
            sprintf(
                "INSERT INTO {{group_l10ns}} (gid, group_name, description, language) SELECT gid, group_name, description, language FROM %s",
                $quotedGroups
            )
        )->execute();
        if ($this->db->schema->getTable('{{groups_update400}}')) {
            $this->db->createCommand()->dropTable('{{groups_update400}}');
        }
        $this->db->createCommand()->renameTable('{{groups}}', '{{groups_update400}}');
        $this->db->createCommand()->createTable(
            '{{groups}}',
            array(
                'gid' => "pk",
                'sid' => "integer NOT NULL DEFAULT '0'",
                'group_order' => "integer NOT NULL DEFAULT '0'",
                'randomization_group' => "string(20) NOT NULL DEFAULT ''",
                'grelevance' => "text NULL"
            ),
            $this->options
        );
        switchMSSQLIdentityInsert('groups', true); // Untested
        $this->db->createCommand(
            "INSERT INTO " . $quotedGroups . "
            (gid, sid, group_order, randomization_group, grelevance)
            SELECT gid, {{groups_update400}}.sid, group_order, randomization_group, COALESCE(grelevance,'')
            FROM {{groups_update400}}
            INNER JOIN {{surveys}} ON {{groups_update400}}.sid = {{surveys}}.sid AND {{groups_update400}}.language = {{surveys}}.language
            "
        )->execute();

        switchMSSQLIdentityInsert('groups', false); // Untested
        $this->db->createCommand()->dropTable('{{groups_update400}}'); // Drop the table before create index for pgsql
        $this->db->createCommand()->createIndex('{{idx1_groups}}', '{{groups}}', 'sid', false);

        // Answers table
        if ($this->db->schema->getTable('{{answer_l10ns}}')) {
            $this->db->createCommand()->dropTable('{{answer_l10ns}}');
        }

        $this->db->createCommand()->createTable(
            '{{answer_l10ns}}',
            array(
                'id' => "pk",
                'aid' => "integer NOT NULL",
                'answer' => "mediumtext NOT NULL",
                'language' => "string(20) NOT NULL"
            ),
            $this->options
        );
        $this->db->createCommand()->createIndex('{{idx1_answer_l10ns}}', '{{answer_l10ns}}', ['aid', 'language'], true);

        /* Renaming old without pk answers */
        if ($this->db->schema->getTable('{{answers_update400}}')) {
            $this->db->createCommand()->dropTable('{{answers_update400}}');
        }

        $this->db->createCommand()->renameTable('{{answers}}', '{{answers_update400}}');
        /* Create new answers with pk and copy answers_update400 Grouping by unique part */
        $this->db->createCommand()->createTable(
            '{{answers}}',
            [
                'aid' => 'pk',
                'qid' => 'integer NOT NULL',
                'code' => 'string(5) NOT NULL',
                'sortorder' => 'integer NOT NULL',
                'assessment_value' => 'integer NOT NULL DEFAULT 0',
                'scale_id' => 'integer NOT NULL DEFAULT 0'
            ],
            $this->options
        );
        $this->db->createCommand()->createIndex(
            'answer_update400_idx_10',
            '{{answers_update400}}',
            ['qid', 'code', 'scale_id']
        );
        /* No pk in insert */
        $this->db->createCommand(
            "INSERT INTO {{answers}}
            (qid, code, sortorder, assessment_value, scale_id)
            SELECT {{answers_update400}}.qid, {{answers_update400}}.code, {{answers_update400}}.sortorder, {{answers_update400}}.assessment_value, {{answers_update400}}.scale_id
            FROM {{answers_update400}}
            INNER JOIN {{questions}} ON {{answers_update400}}.qid = {{questions}}.qid
            INNER JOIN {{surveys}} ON {{questions}}.sid = {{surveys}}.sid AND {{surveys}}.language = {{answers_update400}}.language
            "
        )->execute();

        /* no pk in insert, get aid by INNER join */
        $this->db->createCommand(
            "INSERT INTO {{answer_l10ns}}
            (aid, answer, language)
            SELECT {{answers}}.aid, {{answers_update400}}.answer, {{answers_update400}}.language
            FROM {{answers_update400}}
            INNER JOIN {{answers}}
            ON {{answers_update400}}.qid = {{answers}}.qid AND {{answers_update400}}.code = {{answers}}.code AND {{answers_update400}}.scale_id = {{answers}}.scale_id
            "
        )->execute();

        $this->db->createCommand()->dropTable('{{answers_update400}}');
        $this->db->createCommand()->createIndex('{{answers_idx}}', '{{answers}}', ['qid', 'code', 'scale_id'], true);
        $this->db->createCommand()->createIndex('{{answers_idx2}}', '{{answers}}', 'sortorder', false);

        // Apply integrity fix before starting label set update.
        // List of label set ids which contain code duplicates.
        $lids = $this->db->createCommand(
            "SELECT {{labels}}.lid AS lid
            FROM {{labels}}
            GROUP BY {{labels}}.lid, {{labels}}.language
            HAVING COUNT(DISTINCT({{labels}}.code)) < COUNT({{labels}}.id)"
        )->queryAll();

        foreach ($lids as $lid) {
            regenerateLabelCodes400($lid['lid']);
        }

        // Labels table
        if ($this->db->schema->getTable('{{label_l10ns}}')) {
            $this->db->createCommand()->dropTable('{{label_l10ns}}');
        }
        if ($this->db->schema->getTable('{{labels_update400}}')) {
            $this->db->createCommand()->dropTable('{{labels_update400}}');
        }

        $this->db->createCommand()->renameTable('{{labels}}', '{{labels_update400}}');
        $this->db->createCommand()->createTable(
            '{{labels}}',
            [
                'id' => "pk",
                'lid' => 'integer NOT NULL',
                'code' => 'string(5) NOT NULL',
                'sortorder' => 'integer NOT NULL',
                'assessment_value' => 'integer NOT NULL DEFAULT 0'
            ],
            $this->options
        );
        /* The previous id is broken and can not be used, create a new one */
        /* we can groub by lid and code, adding min(sortorder), min(assessment_value) if they are different (this fix different value for deifferent language) */
        $this->db->createCommand(
            "INSERT INTO {{labels}}
            (lid, code, sortorder, assessment_value)
            SELECT lid, SUBSTRING(code,1,5), min(sortorder), min(assessment_value)
            FROM {{labels_update400}}
            GROUP BY lid, code"
        )->execute();

        $this->db->createCommand()->createTable(
            '{{label_l10ns}}',
            array(
                'id' => "pk",
                'label_id' => "integer NOT NULL",
                'title' => "text",
                'language' => "string(20) NOT NULL DEFAULT 'en'"
            ),
            $this->options
        );

        $this->db->createCommand()->createIndex(
            '{{idx1_label_l10ns}}',
            '{{label_l10ns}}',
            ['label_id', 'language'],
            true
        );

        // Remove invalid labels, otherwise update will fail because of index duplicates in the next query
        $this->db->createCommand("delete from {{labels_update400}} WHERE code=''")->execute();
        $this->db->createCommand(
            "INSERT INTO {{label_l10ns}}
            (label_id, title, language)
            SELECT {{labels}}.id ,{{labels_update400}}.title,{{labels_update400}}.language
            FROM {{labels_update400}}
            INNER JOIN {{labels}} ON {{labels_update400}}.lid = {{labels}}.lid AND {{labels_update400}}.code = {{labels}}.code 
            "
        )->execute();

        $this->db->createCommand()->dropTable('{{labels_update400}}');

        // Extend language field on labelsets
        \alterColumn('{{labelsets}}', 'languages', "string(255)", false);

        // Extend question type field length
        \alterColumn('{{questions}}', 'type', 'string(30)', false, 'T');

        // Drop autoincrement on timings table primary key
        upgradeSurveyTimings350();

        $this->db->createCommand()->update('{{settings_global}}', array('stg_value' => 400), "stg_name='DBVersion'");
    }
}
