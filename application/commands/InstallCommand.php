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
        public $connection;

        public function run($sArgument)
        {
            if (!isset($sArgument) || !isset($sArgument[0]) || !isset($sArgument[1]) || !isset($sArgument[2]) || !isset($sArgument[3])) die('You have to set admin/password/full name and email address on the command line like this: php starter.php adminname mypassword fullname emailaddress');
            Yii::import('application.helpers.common_helper', true);
            $aConfig=Yii::app()->getComponents(false);
            $bDatabaseExists=true;
            try
            {
                $this->connection=new CDbConnection($aConfig['db']['connectionString'],$aConfig['db']['username'],$aConfig['db']['password']);
                $this->connection->active=true;
            }
            catch(Exception $e){
                $bDatabaseExists=false;
                $sConnectionString=preg_replace('/dbname=([^;]*)/', '', $aConfig['db']['connectionString']);
                try
                {
                    $this->connection=new CDbConnection($sConnectionString, $aConfig['db']['username'], $aConfig['db']['password']);
                    $this->connection->active=true;
                }
                catch(Exception $e){
                    echo "Invalid access data. Check your config.php db access data"; die();
                }

            };

            $sDatabaseType = substr($aConfig['db']['connectionString'],0,strpos($aConfig['db']['connectionString'],':'));
            $sDatabaseName= $this->getDBConnectionStringProperty('dbname');

            if (!$bDatabaseExists)
            {

                $createDb = true; // We are thinking positive
                switch ($sDatabaseType)
                {
                    case 'mysqli':
                    case 'mysql':
                    try
                    {
                        $this->connection->createCommand("CREATE DATABASE `$sDatabaseName` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci")->execute();
                    }
                    catch(Exception $e)
                    {
                        $createDb=false;
                    }
                    break;
                    case 'mssql':
                    case 'odbc':
                    try
                    {
                        $this->connection->createCommand("CREATE DATABASE [$sDatabaseName];")->execute();
                    }
                    catch(Exception $e)
                    {
                        $createDb=false;
                    }
                    break;
                    case 'postgres':
                    try
                    {
                        $this->connection->createCommand("CREATE DATABASE \"$sDatabaseName\" ENCODING 'UTF8'")->execute();
                    }
                    catch (Exception $e)
                    {
                        $createdb = false;
                    }
                    break;
                    default:
                    try
                    {
                        $this->connection->createCommand("CREATE DATABASE $sDatabaseName")->execute();
                    }
                    catch(Exception $e)
                    {
                        $createDb=false;
                    }
                    break;        
                }
                if (!$createDb)
                {
                    echo 'Database could not be created because it either existed or you have no permissions'; die();
                }
                else
                {
                    $this->connection=new CDbConnection($aConfig['db']['connectionString'],$aConfig['db']['username'],$aConfig['db']['password']);
                    $this->connection->active=true;

                }
            }

            $this->connection->charset = 'utf8';
            switch ($sDatabaseType) {
                case 'mysql':
                case 'mysqli':
                    $this->connection->createCommand("ALTER DATABASE ". $this->connection->quoteTableName($sDatabaseName) ." DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;")->execute();
                    $sql_file = 'mysql';
                    break;
                case 'pgsql':
                    if (version_compare($this->connection->getServerVersion(),'9','>=')) {
                        $this->connection->createCommand("ALTER DATABASE ". $this->connection->quoteTableName($sDatabaseName) ." SET bytea_output='escape';")->execute();
                    }
                    $sql_file = 'pgsql';
                    break;
                case 'mssql':
                    $sql_file = 'mssql';
                    break;
                default:
                    throw new Exception(sprintf('Unkown database type "%s".', $sDatabaseType));
            }
            $this->_executeSQLFile(dirname(Yii::app()->basePath).'/installer/sql/create-'.$sql_file.'.sql', $aConfig['db']['tablePrefix']);        
            $this->connection->createCommand()->insert($aConfig['db']['tablePrefix'].'users', array(
            'users_name'=>$sArgument[0],
            'password'=>hash('sha256',$sArgument[1]),
            'full_name'=>$sArgument[2],
            'parent_id'=>0,
            'lang'=>'auto',
            'email'=>$sArgument[3],
            'create_survey'=>1,
            'participant_panel'=>1,
            'create_user'=>1,
            'delete_user'=>1,
            'superadmin'=>1,
            'configurator'=>1,
            'manage_template'=>1,
            'manage_label'=>1
            ));

        }

        function _executeSQLFile($sFileName, $sDatabasePrefix)
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
                        $sCommand = str_replace('prefix_', $sDatabasePrefix, $sCommand); // Table prefixes

                        try {
                            $this->connection->createCommand($sCommand)->execute();
                        } catch(Exception $e) {
                            $aMessages[] = "Executing: ".$sCommand." failed! Reason: ".$e;
                            var_dump($e); die();
                        }

                        $sCommand = '';
                    } else {
                        $sCommand .= $sLine;
                    }
                }
            }
            return $aMessages;


        }

        function getDBConnectionStringProperty($sProperty)
        {
            $aConfig=Yii::app()->getComponents(false);
            // Yii doesn't give us a good way to get the database name
            preg_match('/'.$sProperty.'=([^;]*)/', $aConfig['db']['connectionString'], $aMatches);
            if ( count($aMatches) === 0 ) {
                return null;
            }
            return $aMatches[1];
        }

    }
?>