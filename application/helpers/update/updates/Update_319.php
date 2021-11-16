<?php

namespace LimeSurvey\Helpers\Update;

class Update_319 extends DatabaseUpdateBase
{
    public function up()
    {

            $this->db->createCommand()->update('{{settings_global}}', array('stg_value' => 319), "stg_name='DBVersion'");

            $table = \Yii::app()->db->schema->getTable('{{surveys_groups}}');
        if (isset($table->columns['order'])) {
            $this->db->createCommand()->renameColumn('{{surveys_groups}}', 'order', 'sortorder');
        }

            $table = \Yii::app()->db->schema->getTable('{{templates}}');
        if (isset($table->columns['extends_template_name'])) {
            $this->db->createCommand()->renameColumn('{{templates}}', 'extends_template_name', 'extends');
        }
    }
}
