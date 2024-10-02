<?php

namespace LimeSurvey\Helpers\Update;

/**
 * @SuppressWarnings(PHPMD)
 */
class Update_407 extends DatabaseUpdateBase
{
    public function up()
    {
        // defaultvalues
        if (\Yii::app()->db->schema->getTable('{{defaultvalue_l10ns}}')) {
            $this->db->createCommand()->dropTable('{{defaultvalue_l10ns}}');
        }
        $this->db->createCommand()->createTable(
            '{{defaultvalue_l10ns}}',
            array(
                'id' => "pk",
                'dvid' => "integer NOT NULL DEFAULT '0'",
                'language' => "string(20) NOT NULL",
                'defaultvalue' => "text",
            ),
            $this->options
        );
        $this->db->createCommand()->createIndex(
            '{{idx1_defaultvalue_l10ns}}',
            '{{defaultvalue_l10ns}}',
            ['dvid', 'language'],
            true
        );
        if (\Yii::app()->db->schema->getTable('{{defaultvalues_update407}}')) {
            $this->db->createCommand()->dropTable('{{defaultvalues_update407}}');
        }
        $this->db->createCommand()->renameTable('{{defaultvalues}}', '{{defaultvalues_update407}}');
        $this->db->createCommand()->createIndex(
            'defaultvalues_update407_idx_10',
            '{{defaultvalues_update407}}',
            ['qid', 'scale_id', 'sqid', 'specialtype', 'language']
        );
        $this->db->createCommand()->createTable(
            '{{defaultvalues}}',
            [
                'dvid' => "pk",
                'qid' => "integer NOT NULL DEFAULT '0'",
                'scale_id' => "integer NOT NULL DEFAULT '0'",
                'sqid' => "integer NOT NULL DEFAULT '0'",
                'specialtype' => "string(20) NOT NULL DEFAULT ''",
            ],
            $this->options
        );
        /* Get only survey->language */
        $this->db->createCommand(
            "INSERT INTO {{defaultvalues}} (qid, sqid, scale_id, specialtype)
            SELECT qid, sqid, scale_id, specialtype
            FROM {{defaultvalues_update407}}
            GROUP BY qid, sqid, scale_id, specialtype
            "
        )->execute();

        $this->db->createCommand()->createIndex(
            '{{idx1_defaultvalue}}',
            '{{defaultvalues}}',
            ['qid', 'scale_id', 'sqid', 'specialtype'],
            false
        );

        $this->db->createCommand(
            "INSERT INTO {{defaultvalue_l10ns}} (dvid, language, defaultvalue)
            SELECT {{defaultvalues}}.dvid, {{defaultvalues_update407}}.language, {{defaultvalues_update407}}.defaultvalue
            FROM {{defaultvalues}}
            INNER JOIN {{defaultvalues_update407}}
            ON {{defaultvalues}}.qid = {{defaultvalues_update407}}.qid AND {{defaultvalues}}.sqid = {{defaultvalues_update407}}.sqid AND {{defaultvalues}}.scale_id = {{defaultvalues_update407}}.scale_id AND {{defaultvalues}}.specialtype = {{defaultvalues_update407}}.specialtype
            "
        )->execute();

        $this->db->createCommand()->dropTable('{{defaultvalues_update407}}');
    }
}
