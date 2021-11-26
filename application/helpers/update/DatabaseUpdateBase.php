<?php

namespace LimeSurvey\Helpers\Update;

use CDbConnection;
use Exception;
use Throwable;

/**
 * Base class for database migration, inspired by Yii.
 * See more info in README file in same folder.
 */
abstract class DatabaseUpdateBase
{
    /** @var CDbConnection */
    protected $db;

    /** @var int */
    protected $newVersion;

    /** @var string */
    protected $options;

    /**
     * @param CDbConnection $connection
     * @param string $options Specific database options like ENGINE=INNODB etc
     */
    public function __construct(CDbConnection $connection, $options)
    {
        $this->db = $connection;
        // Database version is part of class and file name, e.g. Update_123.
        $this->newVersion = $this->getVersion();
        $this->options = $options;
    }

    /**
     * Runs up() wrapped in a transaction.
     * Will rollback transaction and re-throw exception at failure.
     *
     * @return void
     * @throws Throwable
     */
    public function safeUp()
    {
        $transaction = $this->db->beginTransaction();
        try {
            $this->up();
            $this->updateVersion();
            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollback();
            throw $e;
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
     * Get db version number based on class name, e.g. 123 for Update_123
     *
     * @return int
     */
    public function getVersion()
    {
        $nameParts = explode(
            '_',
            (new \ReflectionClass($this))->getShortName()
        );
        if (count($nameParts) !== 2) {
            throw new Exception('Expected exactly two name parts');
        }
        if ($nameParts[0] !== 'Update') {
            throw new Exception('Update file MUST be named Update_x for a DBVersion number x');
        }
        return (int) $nameParts[1];
    }

    /**
     * This is the function that must be implemented by the child classes.
     */
    abstract public function up();
}
