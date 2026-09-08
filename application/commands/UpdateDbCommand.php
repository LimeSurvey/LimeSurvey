<?php

    /*
 * @author Denis Chenu <denis@sondages.pro>
 * @license GPL v3
 * @version 0.1
 *
 * Usage: application/commands/console.php updatedb [pathToConfig.php]
 *
 * If a path to a custom config.php is given as first argument, its 'components.db'
 * connection is used instead of the default application/config/config.php one,
 * similar to InstallFromConfigCommand.
 *
 * Copyright (C) 2017 LimeSurvey Team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 */
class UpdateDBCommand extends CConsoleCommand
{
    /**
     * Configuration loaded from the custom config.php given on the command line, if any.
     * @var array
     */
    public $configuration = [];

    /**
     * @var CDbConnection
     */
    public $connection;

    /**
     * Performs a database schema upgrade when the configured target version is greater than the current installed version.
     *
     * @param array|null $args Optional command-line arguments. $args[0], if set, is the path to a custom
     * config.php whose database connection should be used instead of the default one.
     * @throws CException If the current database version is not found (application appears uninstalled) or if the upgrade process fails and requires manual intervention.
     */
    public function run($args = null)
    {
        $usingCustomConfig = isset($args) && isset($args[0]) && $args[0];
        if ($usingCustomConfig) {
            $this->useCustomConfig($args[0]);
        }

        $newDbVersion = (int) Yii::app()->getConfig('dbversionnumber');
        $currentDbVersion = $usingCustomConfig
            ? $this->getCurrentDbVersion()
            : intval(Yii::app()->getConfig('DBVersion'));

        if (!$currentDbVersion) {
            throw new CException("Database version was not found, LimeSurvey is not correctly installed.");
        }

        if ($newDbVersion > $currentDbVersion) {
            echo "Update " . Yii::app()->db->connectionString . " with prefix :";
            echo Yii::app()->db->tablePrefix . " from {$currentDbVersion} to {$newDbVersion}\n";
            Yii::import('application.helpers.common_helper', true);
            Yii::import('application.helpers.update.update_helper', true);
            Yii::import('application.helpers.update.updatedb_helper', true);

            $result = db_upgrade_all($currentDbVersion);
            if ($result) {
                echo "Database has been successfully upgraded to version $newDbVersion \n";
                return 0;
            } else {
                throw new CException("Please fix this error in your database and try again");
            }
        } else {
            echo "no need update : DB is uptodate\n";
            return 0;
        }
    }

    /**
     * Switches the application's database connection to the one defined in a custom config.php file,
     * the same way InstallFromConfigCommand does, instead of the default application/config/config.php one.
     *
     * @param string $configPath Path to a config.php file returning an array with a 'components.db' section.
     * @return void
     * @throws CException If the config file cannot be found.
     */
    protected function useCustomConfig($configPath)
    {
        $readFromConfig = realpath($configPath);
        if ($readFromConfig === false) {
            throw new CException("Config file not found: $configPath");
        }

        $this->configuration = include($readFromConfig);
        $dbConnectionArray = $this->configuration['components']['db'];

        foreach ($this->configuration as $configKey => $configValue) {
            Yii::app()->params[$configKey] = $configValue;
        }

        // Prevent CDbConnection::init() from auto-connecting with whatever connection is
        // currently configured (the default application/config/config.php one) before we
        // get a chance to point it at the custom config below.
        Yii::app()->configure(array('components' => array('db' => array('autoConnect' => false))));

        $this->connection = Yii::app()->getDb();
        $this->connection->active = false;
        $this->connection->connectionString = $dbConnectionArray['connectionString'];
        $this->connection->username = $dbConnectionArray['username'];
        $this->connection->password = $dbConnectionArray['password'];
        if (isset($dbConnectionArray['tablePrefix'])) {
            $this->connection->tablePrefix = $dbConnectionArray['tablePrefix'];
        }
        $this->connection->active = true;
        echo "Using connection string " . $this->connection->connectionString . "\n";
    }

    /**
     * Reads the current database version directly from settings_global rather than the application
     * config cached at bootstrap, which was read from the default database and would be stale once
     * a custom config.php has switched the connection to a different database.
     *
     * @return int
     */
    protected function getCurrentDbVersion()
    {
        $row = Yii::app()->db->createCommand()
            ->select('stg_value')
            ->from('{{settings_global}}')
            ->where('stg_name=:stg_name', array(':stg_name' => 'DBVersion'))
            ->queryRow();
        return $row ? intval($row['stg_value']) : 0;
    }
}
