<?php
    /*
    * LimeSurvey (tm)
    * Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
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
         *
         * @var CDbConnection
         */
        public $connection;

        public function run($sArgument)
        {
            if (!isset($sArgument) || !isset($sArgument[0]) || !isset($sArgument[1]) || !isset($sArgument[2]) || !isset($sArgument[3])) die('You have to set admin/password/full name and email address on the command line like this: php starter.php adminname mypassword fullname emailaddress');
            Yii::import('application.helpers.common_helper', true);
            
            try
            {
                $this->connection = App()->getDb();
                $this->connection->active=true;
            }
            catch(CDbException $e){
                $this->createDatabase();
            };

            $this->connection->charset = 'utf8';
            switch ($this->connection->driverName) {
                case 'mysql':
                case 'mysqli':
                    $this->connection->createCommand("ALTER DATABASE ". $this->connection->quoteTableName($this->getDBConnectionStringProperty('dbname')) ." DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;")->execute();
                    $sql_file = 'mysql';
                    break;
                case 'pgsql':
                    if (version_compare($this->connection->getServerVersion(),'9','>=')) {
                        $this->connection->createCommand("ALTER DATABASE ". $this->connection->quoteTableName($this->getDBConnectionStringProperty('dbname')) ." SET bytea_output='escape';")->execute();
                    }
                    $sql_file = 'pgsql';
                    break;
                case 'dblib': 
                case 'mssql':
                case 'sqlsrv':
                    $sql_file = 'mssql';
                    break;
                default:
                    throw new Exception(sprintf('Unknown database type "%s".', $this->connection->driverName));
            }
            $this->_executeSQLFile(dirname(Yii::app()->basePath).'/installer/sql/create-'.$sql_file.'.sql');
            $this->connection->createCommand()->insert($this->connection->tablePrefix.'users', array(
            'users_name'=>$sArgument[0],
            'password'=>hash('sha256',$sArgument[1]),
            'full_name'=>$sArgument[2],
            'parent_id'=>0,
            'lang'=>'auto',
            'email'=>$sArgument[3]
            ));
            $this->connection->createCommand()->insert($this->connection->tablePrefix.'permissions', array(
            'entity'=>'global',
            'entity_id'=>0,
            'uid'=>1,
            'permission'=>'superadmin',
            'create_p'=>0,
            'read_p'=>1,
            'update_p'=>0,
            'delete_p'=>0,
            'import_p'=>0,
            'export_p'=>0
            ));
        }

        function _executeSQLFile($sFileName)
        {
            echo   $sFileName;
            $aMessages = array();
            $sCommand = '';

            if (!is_readable($sFileName)) {
                return false;
            } else {
                $aLines = file($sFileName);
            }
            foreach ($aLines as $sLine) {
                $sLine = rtrim($sLine);
                $iLineLength = strlen($sLine);

                if ($iLineLength && $sLine[0] != '#' && substr($sLine,0,2) != '--') {
                    if (substr($sLine, $iLineLength-1, 1) == ';') {
                        $line = substr($sLine, 0, $iLineLength-1);
                        $sCommand .= $sLine;
                        $sCommand = str_replace('prefix_', $this->connection->tablePrefix, $sCommand); // Table prefixes

                        try {
                            $this->connection->createCommand($sCommand)->execute();
                        } catch(Exception $e) {
                            $aMessages[] = "Executing: ".$sCommand." failed! Reason: ".$e;
                        }

                        $sCommand = '';
                    } else {
                        $sCommand .= $sLine;
                    }
                }
            }
            return $aMessages;


        }

        function getDBConnectionStringProperty($sProperty, $connectionString = null)
        {
            if (!isset($connectionString))
            {
                $connectionString = $this->connection->connectionString;
            }
            // Yii doesn't give us a good way to get the database name
            if ( preg_match('/'.$sProperty.'=([^;]*)/', $connectionString, $aMatches) == 1 ) {
                return $aMatches[1];
            }
        }


        protected function createDatabase()
        {
            App()->configure(array('components'=>array('db'=>array('autoConnect'=>false)))) ;
            $this->connection=App()->db;
            App()->configure(array('components'=>array('db'=>array('autoConnect'=>true)))) ;
            $connectionString = $this->connection->connectionString;
            $this->connection->connectionString = preg_replace('/dbname=([^;]*)/', '', $connectionString);
            try {
                $this->connection->active=true;
            }
            catch(Exception $e){
                throw new CException("Invalid access data. Check your config.php db access data");
            }

            $sDatabaseName= $this->getDBConnectionStringProperty('dbname', $connectionString);
            try {
                switch ($this->connection->driverName)
                {
                    case 'mysqli':
                    case 'mysql':
                        $this->connection->createCommand("CREATE DATABASE `$sDatabaseName` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci")->execute();
                        break;
                    case 'dblib':
                    case 'mssql':
                    case 'odbc':
                        $this->connection->createCommand("CREATE DATABASE [$sDatabaseName];")->execute();
                        break;
                    case 'postgres':
                        $this->connection->createCommand("CREATE DATABASE \"$sDatabaseName\" ENCODING 'UTF8'")->execute();
                        break;
                    default:
                        $this->connection->createCommand("CREATE DATABASE $sDatabaseName")->execute();
                        break;
                }
            }
            catch (Exception $e)
            {
                throw new CException('Database could not be created because it either existed or you have no permissions');
            }
            
            $this->connection->active = false;
            $this->connection->connectionString = $connectionString;
            $this->connection->active = true;
        }

    }
?>