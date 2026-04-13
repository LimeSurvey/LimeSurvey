<?php

/*
 * LimeSurvey (tm)
 * Copyright (C) 2011-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */
class InstallCommand extends CConsoleCommand
{

    /**
     * If true, output trace.
     * @var boolean
     */
    public $noisy = false;

    /**
     *
     * @var CDbConnection
     */
    public $connection;

    /**
     * @param array $args
     * @return int
     * @throws CException
     * @throws Exception
     */
    public function run($args)
    {
        if (isset($args) && isset($args[0]) && isset($args[1]) && isset($args[2]) && isset($args[3])) {
            Yii::import('application.helpers.common_helper', true);

            $this->setNoisy($args);

            try {
                $this->output('Connecting to database...');
                $this->connection = App()->getDb();
                $this->connection->active = true;
                $this->output('Using connection string ' . $this->connection->connectionString);
            } catch (CDbException $e) {
                $this->output('Could not connect to database: ' . $e->getMessage());
                $this->createDatabase();
            };

            $this->prepareCharset();

            $sFileName = dirname(APPPATH) . '/installer/create-database.php';
            require_once($sFileName);
            try {
                $this->output('Creating tables...');
                populateDatabase($this->connection);
            } catch (Exception $e) {
                $this->output('Could not create LimeSurvey tables: ' . $e->getMessage());
                return 1;
            }

            $this->createUser($args);
            $this->createPermissions();

            $this->output('All done!');
            return 0;
        } else {
            // TODO: a valid error process
            echo "You have to set admin/password/full name and email address on the command line like this: php console.php install adminname mypassword fullname emailaddress [verbose]\n";
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
            $connectionString = $this->connection->connectionString;
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
     * @throws CException
     */
    protected function createDatabase()
    {
        $this->output('Creating database...');
        App()->configure(array('components' => array('db' => array('autoConnect' => false))));
        $this->connection = App()->db;

        App()->configure(array('components' => array('db' => array('autoConnect' => true))));
        $connectionString = $this->connection->connectionString;
        $this->output($connectionString);
        $this->connection->connectionString = preg_replace('/dbname=([^;]*)/', '', (string) $connectionString);
        try {
            $this->output('Opening connection...');
            $this->connection->active = true;
        } catch (Exception $e) {
            throw new CException("Invalid access data. Check your config.php db access data");
        }

        if (!empty($this->connection) && $this->connection->driverName == 'mysql') {
            /** @var string */
            $dbEngine = getenv('DBENGINE');
            if (empty($dbEngine)) {
                throw new CException('Environment variable DBENGINE is empty, should be either MyISAM or InnoDB');
            }

            $this->connection
                ->createCommand(new CDbExpression(sprintf('SET default_storage_engine=%s;', $dbEngine)))
                ->execute();
        }

        /** @var string */
        $sDatabaseName = $this->getDBConnectionStringProperty('dbname', $connectionString);

        try {
            switch ($this->connection->driverName) {
                case 'mysqli':
                case 'mysql':
                    $this->connection->createCommand("CREATE DATABASE `$sDatabaseName` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")->execute();
                    break;
                case 'dblib':
                case 'mssql':
                case 'odbc':
                    $this->connection->createCommand("CREATE DATABASE [$sDatabaseName];")->execute();
                    break;
                case 'pgsql':
                    $this->connection->createCommand("CREATE DATABASE \"$sDatabaseName\" ENCODING 'UTF8'")->execute();
                    break;
                default:
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
        if (isset($args[4]) && $args[4]) {
            $this->noisy = true;
        }
    }


    private function prepareCharset()
    {
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
    }

    private function createUser($data)
    {
        $this->output('Creating admin user...');
        $this->connection->createCommand()->insert(
            $this->connection->tablePrefix . 'users',
            array(
                'users_name' => $data[0],
                'password' => password_hash((string) $data[1], PASSWORD_DEFAULT),
                'full_name' => $data[2],
                'parent_id' => 0,
                'lang' => 'auto',
                'email' => $data[3]
            )
        );
    }

    private function createPermissions()
    {
        $this->output('Creating permissions ...');
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
    }
}
