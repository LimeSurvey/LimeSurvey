<?php

namespace LimeSurvey\Helpers\Update;

use LimeSurvey\Helpers\Update\triggers\AnswersTriggerBuilder;
use LimeSurvey\Helpers\Update\triggers\GroupL10nsTriggerBuilder;
use LimeSurvey\Helpers\Update\triggers\GroupsTriggerBuilder;
use LimeSurvey\Helpers\Update\triggers\LanguageSettingsTriggerBuilder;
use LimeSurvey\Helpers\Update\triggers\QuestionL10nsTriggerBuilder;
use LimeSurvey\Helpers\Update\triggers\QuestionsTriggerBuilder;
use LimeSurvey\Helpers\Update\triggers\SurveysTriggerBuilder;

class Update_634 extends DatabaseUpdateBase
{
    protected string $prefix;

    protected string $fieldName;

    /**
     * @throws \CDbException
     */
    public function up()
    {
        $this->prefix = App()->db->tablePrefix;
        $this->fieldName = 'lastmodified';

        switch ($this->db->driverName) {
            case 'mysql':
            case 'mssql':
            case 'sqlsrv':
                addColumn('{{surveys}}', $this->fieldName, 'datetime DEFAULT current_timestamp');
                break;
            case 'pgsql':
                addColumn('{{surveys}}', $this->fieldName, 'timestamp DEFAULT current_timestamp');
                break;
        }

        foreach ($this->getTriggers() as $trigger) {
            $this->db->createCommand($trigger)->execute();
        }
    }

    private function getTriggers(): array
    {
        $dbType = $this->db->driverName;
        return call_user_func_array('array_merge', [
            AnswersTriggerBuilder::build($dbType, $this->prefix, $this->fieldName),
            GroupL10nsTriggerBuilder::build($dbType, $this->prefix, $this->fieldName),
            GroupsTriggerBuilder::build($dbType, $this->prefix, $this->fieldName),
            LanguageSettingsTriggerBuilder::build($dbType, $this->prefix, $this->fieldName),
            QuestionL10nsTriggerBuilder::build($dbType, $this->prefix, $this->fieldName),
            QuestionsTriggerBuilder::build($dbType, $this->prefix, $this->fieldName),
            SurveysTriggerBuilder::build($dbType, $this->prefix, $this->fieldName),
        ]);
    }
}
