<?php
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * $Id$
 *
 * LS Library bootstrap file, work in progress.
 *
 * Currently requires require_once to use the library.
 */


# imports
require_once(APPPATH.'third_party/adodb/adodb.inc.php');


/**
 * Installer DB Tasks
 */
class LS_Installer_DbTasks
{
    /**
     *
     * Enter description here ...
     * @var ADONewConnection
     */
    private $connection;

    /**
     * test a database configuration for connection
     *
     * @param array $config
     * @return bool success
     */
    public function testConnection(array $config)
    {
        list($connection, $connect) = $this->connect($config);
        $this->connection = $connection;
        return $connect;
    }
    
    public function validateDatabaseType($databasetype)
    {
        if (!in_array($databasetype, array('mysqli', 'mysql', 'mssql', 'postgres')))
        {
            throw new LS_Exception(sprintf('Invalid database type ("%s").', $databasetype));
        }
    }
    
    private function connect(array $config)
    {
        $vars = array_flip(array('dbname', 'dblocation', 'dbuser', 'dbpwd', 'dbtype'));
        $config = array_intersect_key($config, $vars);
        extract($config);
        $this->validateDatabaseType($dbtype);
        $connection = ADONewConnection($dbtype);
        $connect = $connection->Connect($dblocation, $dbuser, $dbpwd, $dbname);
        return array($connection, $connect);
    }
    
    /**
     * @return ADOConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
    /**
     * split a database location into host and port.
     *
     * @param string $location
     * @param string $defaultPort
     * @return array (host, port)
     */
    public static function getHostParts($location, $defaultPort = null)
    {
        $seperator = ':';
        
        if ( false !== strpos($location, $seperator))
        {
            list($host, $port) = explode($seperator, $location, 2);
        }
        else
        {
            list($host, $port) = array($location, $defaultPort);
        }
        return array($host, $port);
   }
}