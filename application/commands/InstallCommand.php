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
     * Installs LimeSurvey: creates the database (if needed), tables, admin user and permissions.
     *
     * @param array $args Expected order: [0] admin username, [1] admin password, [2] admin full name, [3] admin email, [4] optional verbose flag.
     * @return int Returns 0 on success, 1 if required arguments are missing or table creation fails.
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
     * Extracts the value of a property (e.g. dbname) from a PDO-style connection string.
     *
     * @param string $sProperty Name of the property to extract, e.g. 'dbname'.
     * @param string|null $connectionString Connection string to parse; defaults to the current connection's string.
     * @return string|null The property value, or null if not found.
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
     * Creates the database specified in the connection string, then reconnects to it.
     *
     * @return void
     * @throws CException If the connection cannot be opened or the database could not be created.
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
     * Prints a message to stdout, but only when $noisy is true.
     *
     * @param string $msg The message to print.
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


    /**
     * Sets the connection charset to utf8mb4 for the supported database drivers.
     *
     * @return void
     * @throws Exception If the database driver is not supported.
     */
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

    /**
     * Inserts the initial admin user record.
     *
     * @param array $data Same order as run()'s $args: [0] username, [1] password, [2] full name, [3] email.
     * @return void
     */
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

    /**
     * Grants the initial admin user (uid=1) the superadmin permission.
     *
     * @return void
     */
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
