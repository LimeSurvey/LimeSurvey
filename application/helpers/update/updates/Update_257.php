<?php

namespace LimeSurvey\Helpers\Update;

use Exception;

class Update_257 extends DatabaseUpdateBase
{
    public function up()
    {
        switch (\Yii::app()->db->driverName) {
            case 'pgsql':
                $sSubstringCommand = 'substr';
                break;
            default:
                $sSubstringCommand = 'substring';
        }
        $this->db->createCommand("UPDATE {{templates}} set folder={$sSubstringCommand}(folder,1,50)")->execute();
        try {
            dropPrimaryKey('templates');
        } catch (\Exception $e) {
        };
        alterColumn('{{templates}}', 'folder', "string(50)", false);
        addPrimaryKey('templates', 'folder');
        dropPrimaryKey('participant_attribute_names_lang');
        alterColumn('{{participant_attribute_names_lang}}', 'lang', "string(20)", false);
        addPrimaryKey('participant_attribute_names_lang', array('attribute_id', 'lang'));
        //Fixes the collation for the complete DB, tables and columns
        if (\Yii::app()->db->driverName == 'mysql') {
            fixMySQLCollations('utf8mb4', 'utf8mb4_unicode_ci');
            // Also apply again fixes from DBVersion 181 again for case sensitive token fields
            upgradeSurveyTables181('utf8mb4_bin');
            upgradeTokenTables181('utf8mb4_bin');
        }
    }
}
