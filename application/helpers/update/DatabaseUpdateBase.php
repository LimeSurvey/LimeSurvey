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

    /**
     * @param CDbConnection $connection
     */
    public function __construct(CDbConnection $connection)
    {
        $this->db = $connection;
        // Database version is part of class and file name, e.g. Update_123.
        $this->newVersion = $this->getVersionFromClass($this);
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

    /**
     * @param object $that
     * @return int
     */
    private function getVersionFromClass($that)
    {
        $nameParts = explode(
            '_',
            (new \ReflectionClass($that))->getShortName()
        );
        if (count($nameParts) !== 2) {
            throw new Exception('Expected exactly two name parts');
        }
        return (int) $nameParts[1];
    }

    abstract public function up();
}
