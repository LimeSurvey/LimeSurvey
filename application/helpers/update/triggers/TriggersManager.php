<?php

namespace LimeSurvey\Helpers\Update\triggers;

use DbConnection;


class TriggersManager
{
    /** @var DbConnection */
    public $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    function getAll(): array
    {
        return [
            'lastmodified' => [
                'AnswersTriggerBuilder',
                'GroupL10nsTriggerBuilder',
                'GroupsTriggerBuilder',
                'LanguageSettingsTriggerBuilder',
                'QuestionL10nsTriggerBuilder',
                'QuestionsTriggerBuilder',
                'SurveysTriggerBuilder'
            ]
        ];
    }

    /**
     * @throws \CDbException
     */
    function execute(): void
    {
        $prefix = $this->db->tablePrefix;
        $driverName = $this->db->driverName;

        $triggersQueries = [];
        foreach ($this->getAll() as $fieldName => $triggers) {
            foreach ($triggers as $trigger) {
                $className = "LimeSurvey\\Helpers\\Update\\triggers\\$trigger";
                $triggersQueries = array_merge($triggersQueries,
                    $className::build($driverName, $prefix, $fieldName)
                );
            }
        }

        foreach ($triggersQueries as $trigger) {
            $this->db->createCommand($trigger)->execute();
        }
    }
}
