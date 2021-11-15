<?php

namespace LimeSurvey\Helpers\Update;

/**
 * Base class for database migration, inspired by Yii.
 * See more info in README file in same folder.
 */
abstract class DatabaseUpdateBase
{
    /** @var CDbConnection */
    private $db;

    /** @var int */
    private $newVersion;

    /**
     * @param CDbConnection $connection
     */
    public function __construct(CDbConnection $connection, $newVersion)
    {
        $this->db = $connection;
        $this->newVersion = $newVersion;
    }

    /**
     * Runs up() wrapped in a transaction.
     * Returns true at success; otherwise the exception object
     *
     * @return true|Throwable
     */
    public function safeUp()
    {
        $transaction = $this->db->beginTransaction();
        try {
            $this->up();
            $this->updateVersion();
            $transaction->commit();
            return true;
        } catch (Throwable $e) {
            $transaction->rollback();
            return $e;
        }
    }

    /**
     * Sets DBVersion in settings global to $this->newVersion
     * Last thing that happens before transaction commit in every update.
     */
    private function updateVersion()
    {
        $this->db
            ->createCommand()
            ->update('{{settings_global}}', ['stg_value' => $this->newVersion], "stg_name='DBVersion'");
    }

    abstract public function up();
}
