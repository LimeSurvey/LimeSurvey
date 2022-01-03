<?php

namespace LimeSurvey\Helpers\Update;

/**
 * @SuppressWarnings(PHPMD)
 */
class Update_148 extends DatabaseUpdateBase
{
    public function up()
    {
        addColumn('{{users}}', 'participant_panel', "integer NOT NULL default 0");

        $this->db->createCommand()->createTable(
            '{{participants}}',
            array(
                'participant_id' => 'string(50) NOT NULL',
                'firstname' => 'string(40) default NULL',
                'lastname' => 'string(40) default NULL',
                'email' => 'string(80) default NULL',
                'language' => 'string(40) default NULL',
                'blacklisted' => 'string(1) NOT NULL',
                'owner_uid' => "integer NOT NULL"
            )
        );
        addPrimaryKey('participants', array('participant_id'));

        $this->db->createCommand()->createTable(
            '{{participant_attribute}}',
            array(
                'participant_id' => 'string(50) NOT NULL',
                'attribute_id' => "integer NOT NULL",
                'value' => 'string(50) NOT NULL'
            )
        );
        addPrimaryKey('participant_attribute', array('participant_id', 'attribute_id'));

        $this->db->createCommand()->createTable(
            '{{participant_attribute_names}}',
            array(
                'attribute_id' => 'autoincrement',
                'attribute_type' => 'string(4) NOT NULL',
                'visible' => 'string(5) NOT NULL',
                'PRIMARY KEY (attribute_id,attribute_type)'
            )
        );

        $this->db->createCommand()->createTable(
            '{{participant_attribute_names_lang}}',
            array(
                'attribute_id' => 'integer NOT NULL',
                'attribute_name' => 'string(30) NOT NULL',
                'lang' => 'string(20) NOT NULL'
            )
        );
        addPrimaryKey('participant_attribute_names_lang', array('attribute_id', 'lang'));

        $this->db->createCommand()->createTable(
            '{{participant_attribute_values}}',
            array(
                'attribute_id' => 'integer NOT NULL',
                'value_id' => 'pk',
                'value' => 'string(20) NOT NULL'
            )
        );

        $this->db->createCommand()->createTable(
            '{{participant_shares}}',
            array(
                'participant_id' => 'string(50) NOT NULL',
                'share_uid' => 'integer NOT NULL',
                'date_added' => 'datetime NOT NULL',
                'can_edit' => 'string(5) NOT NULL'
            )
        );
        addPrimaryKey('participant_shares', array('participant_id', 'share_uid'));

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
        // Add language field to question_attributes table
        addColumn('{{question_attributes}}', 'language', "string(20)");
        upgradeQuestionAttributes148();
        fixSubquestions();
    }
}
