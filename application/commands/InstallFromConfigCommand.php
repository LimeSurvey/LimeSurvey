<?php

/*
 * GititSurvey (tm)
 * Copyright (C) 2011 The GititSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * GititSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */
class InstallFromConfigCommand extends CConsoleCommand
{

    /**
     * If true, output trace.
     * @var boolean
     */
    public $noisy = false;


    /**
     * Imported configuration file
     */
    public $configuration = [];
    public $dbConnectionArray = [];

    /**
     *
     * @var DbConnection
     */
    public $connection;

    /**
     * @param array $args
     * @return int
     */
    public function run($args)
    {
        
        if (isset($args) && isset($args[0])) {
            $readFromConfig = realpath($args[0]);
            $this->configuration = include($readFromConfig);
            $this->dbConnectionArray = $this->configuration['components']['db'];
            
            foreach ($this->configuration as $configKey => $configValue) {
                Yii::app()->params[$configKey] = $configValue;
            }

            Yii::import('application.helpers.common_helper', true);

            $this->setNoisy($args);

            try {
                $this->output('Connecting to database...');
                $this->connection = App()->getDb();
                $this->connection->connectionString = $this->dbConnectionArray['connectionString'];
                $this->connection->username = $this->dbConnectionArray['username'];
                $this->connection->password = $this->dbConnectionArray['password'];
                $this->connection->active = true;
                $this->output('Using connection string ' . $this->connection->connectionString);
            } catch (CDbException $e) {
                $this->output('Could not connect to database: ' . $e->getMessage());
                $this->createDatabase();
            };

            $this->connection->charset = 'utf8';

            switch ($this->connection->driverName) {
                case 'mysql':
                case 'mysqli':
                    $this->connection->createCommand("ALTER DATABASE " . $this->connection->quoteTableName($this->getDBConnectionStringProperty('dbname')) . " DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;")->execute();
                    break;
                case 'pgsql':
                case 'dblib':
                case 'mssql':
                case 'sqlsrv':
                    break;
                default:
                    throw new Exception(sprintf('Unknown database type "%s".', $this->connection->driverName));
            }

            $sFileName = dirname(APPPATH) . '/installer/create-database.php';
            require_once($sFileName);
            try {
                $this->output('Creating tables...');
                populateDatabase($this->connection);
            } catch (Exception $e) {
                $this->output('Could not create GititSurvey tables: ' . $e->getMessage());
                return 1;
            }

            $this->output('Creating admin user...');
            $this->connection->createCommand()->insert(
                $this->connection->tablePrefix . 'users',
                array(
                    'users_name' => $this->configuration['config']['defaultuser'],
                    'password' => password_hash((string) $this->configuration['config']['defaultpass'], PASSWORD_DEFAULT),
                    'full_name' => "",
                    'parent_id' => 0,
                    'lang' => 'auto',
                    'email' => $this->configuration['config']['siteadminemail']
                )
            );
            $this->connection->createCommand()->insert(
                $this->connection->tablePrefix . 'permissions',
                array(
                    'entity' => 'global',
                    'entity_id' => 0,
                    'uid' => 1,
                    'permission' => 'superadmin',
                    'create_p' => 0,
                    'read_p' => 1,
                    'update_p' => 0,
                    'delete_p' => 0,
                    'import_p' => 0,
                    'export_p' => 0
                )
            );
            $this->output('All done!');
            return 0;
        } else {
            // TODO: a valid error process
            echo "You have to set the path to the config file as only parameter\n";
            return 1;
        }
    }

    /**
     * @param string $sProperty
     * @param string $connectionString
     * @return string|null
     */
    public function getDBConnectionStringProperty($sProperty, $connectionString = null)
    {
        if (!isset($connectionString)) {
            $connectionString = $this->dbConnectionArray['connectionString'];
        }
        // Yii doesn't give us a good way to get the database name
        if (preg_match('/' . $sProperty . '=([^;]*)/', (string) $connectionString, $aMatches) == 1) {
            return $aMatches[1];
        }
        return null;
    }

    /**
     * Create database with name?
     * @return void
     */
    protected function createDatabase()
    {
        $this->output('Creating database...');
        App()->configure(array('components' => array('db' => array('autoConnect' => false))));
        
        $dbConnectArray = $this->configuration['components']['db'];

        $connectionString = $dbConnectArray['connectionString'];
        $this->output($connectionString);
        $dbConnectArray['connectionString'] = preg_replace('/dbname=([^;]*)/', '', (string) $connectionString);
        
        $this->connection = App()->getDb();
        $this->connection->connectionString = $dbConnectArray['connectionString'];
        $this->connection->username = $dbConnectArray['username'];
        $this->connection->password = $dbConnectArray['password'];

        $this->connection->setAttributes($dbConnectArray);
        $this->connection->init();
        
        try {
            $this->output('Opening connection...');
            $this->connection->active = true;
        } catch (Exception $e) {
            print_r($dbConnectArray);
            throw new CException("Invalid access data. Check your config.php db access data");
        }

        $sDatabaseName = $this->getDBConnectionStringProperty('dbname', $connectionString);
        try {
            switch ($this->connection->driverName) {
                case 'mysqli':
                case 'mysql':
                    $exists = $this->connection->createCommand(
                        "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$sDatabaseName'"
                    )->queryScalar();
                    if (!$exists) {
                        $this->connection->createCommand("CREATE DATABASE `$sDatabaseName` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")->execute();
                    }
                    break;
                case 'dblib':
                case 'mssql':
                case 'odbc':
                    // TODO: Check if exists
                    $this->connection->createCommand("CREATE DATABASE [$sDatabaseName];")->execute();
                    break;
                case 'pgsql':
                    // TODO: Check if exists
                    $this->connection->createCommand("CREATE DATABASE \"$sDatabaseName\" ENCODING 'UTF8'")->execute();
                    break;
                default:
                    // TODO: Check if exists
                    $this->connection->createCommand("CREATE DATABASE $sDatabaseName")->execute();
                    break;
            }
        } catch (Exception $e) {
            throw new CException('Database could not be created because it either existed or you have no permissions');
        }

        $this->connection->active = false;
        $this->connection->connectionString = $connectionString;
        $this->connection->active = true;
    }

    /**
     * @param string $msg
     * @return void
     */
    public function output($msg)
    {
        if ($this->noisy) {
            echo $msg . PHP_EOL;
        }
    }

    /**
     * Set noisy = true if fifth argument is given.
     * @param array $args
     * @return void
     */
    protected function setNoisy(array $args)
    {
        if (isset($args[1]) && $args[1]) {
            $this->noisy = true;
        }
    }
}
