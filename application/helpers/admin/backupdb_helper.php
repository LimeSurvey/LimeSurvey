<?php

    /*
    * LimeSurvey
    * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
    * All rights reserved.
    * License: GNU/GPL License v2 or later, see LICENSE.php
    * LimeSurvey is free software. This version may have been modified pursuant
    * to the GNU General Public License, and as distributed it includes or
    * is derivative of works licensed under the GNU General Public License or
    * other free or open source software licenses.
    * See COPYRIGHT.php for copyright notices and details.
    *
    */


    /**
     * Outputs a full dump of the current LimeSurvey database
     * @param string $sDbName Database Name
     */
function outputDatabase($sDbName = '', $bEchoOutput = true, $sFileName = null)
{
    if ($sDbName == '') {
        $sDbName = _getDbName();
    }
    $bAllowExportAllDb = (bool) Yii::app()->getConfig('allowexportalldb');

    $sOutput = _outputDBDescription($sDbName, $bAllowExportAllDb);
    if ($bEchoOutput) {
        echo $sOutput;
    }

    if (!is_null($sFileName)) {
        $oFile = fopen($sFileName, 'w');
        if ($oFile === false) {
            safeDie('Could not open output file.');
        } else {
            fwrite($oFile, (string) $sOutput);
        }
    } else {
        $oFile = null;
    }
    _outputDBData($bAllowExportAllDb, $bEchoOutput, $sFileName, $oFile);
    if (!is_null($sFileName) && $oFile !== false) {
        fclose($oFile);
    }
}

function _outputDBDescription($sDbName, $bAllowExportAllDb)
{
    $sOutput = '--' . "\n";
    $sOutput .= '-- LimeSurvey Database Dump of `' . $sDbName . '`' . "\n";
    if (!$bAllowExportAllDb) {
        $sOutput = '-- Only prefixed tables with: ' . Yii::app()->db->tablePrefix . "\n";
    }
    $sOutput .= '-- Date of Dump: ' . dateShift(date('d-M-Y'), 'd-M-Y') . "\n";
    $sOutput .= '--' . "\n";
    return $sOutput;
}

function _outputDBData($bAllowExportAllDb, $bEchoOutput, $sFileName, $oFile)
{
    if ($bAllowExportAllDb) {
        $aTables = Yii::app()->db->getSchema()->getTableNames();
    } else {
        $aTables = Yii::app()->db->createCommand(dbSelectTablesLike(addcslashes((string) Yii::app()->db->tablePrefix, '_') . "%"))->queryColumn();
    }
    foreach ($aTables as $sTableName) {
        $oTableData = Yii::app()->db->getSchema()->getTable($sTableName);
        $sOutput = _outputTableDescription($sTableName);
        if ($bEchoOutput) {
            echo $sOutput;
        }
        if (!is_null($sFileName)) {
            fwrite($oFile, (string) $sOutput);
        }
        _outputTableData($sTableName, $oTableData, $bEchoOutput, $sFileName, $oFile);
    }
}

    /**
     * Outputs the table structure in sql format
     */
function _outputTableDescription($sTableName)
{
    $sOutput = "\n" . '-- --------------------------------------------------------' . "\n\n";
    $sOutput .= '--' . "\n";
    $sOutput .= '-- Table structure for table `' . $sTableName . '`' . "\n";
    $sOutput .= '--' . "\n\n";
    $sOutput .= 'DROP TABLE IF EXISTS `' . $sTableName . '`;' . "\n";

    $aCreateTable = Yii::app()->db->createCommand('SHOW CREATE TABLE ' . Yii::app()->db->quoteTableName($sTableName))->queryRow();
    $sOutput .= $aCreateTable['Create Table'] . ';' . "\n\n";
    return $sOutput;
}

    /**
     * Outputs the table data in sql format
     */
function _outputTableData($sTableName, $oTableData, $bEchoOutput, $sFileName, $oFile)
{
    $sOutput = '--' . "\n";
    $sOutput .= '-- Dumping data for table `' . $sTableName . '`' . "\n";
    $sOutput .= '--' . "\n\n";

    $iNbRecords = _countNumberOfEntries($sTableName);
    if ($iNbRecords > 0) {
        $iMaxNbRecords = _getMaxNbRecords();
        $aFieldNames = array_keys($oTableData->columns);

        for ($i = 0; $i < ceil($iNbRecords / $iMaxNbRecords); $i++) {
            $aRecords = Yii::app()->db->createCommand()
            ->select()
            ->from($sTableName)
            ->limit(intval($iMaxNbRecords), ($i != 0 ? ($i * $iMaxNbRecords) : null))
            ->query()->readAll();

            $sOutput .= _outputRecords($sTableName, $aFieldNames, $aRecords);
            if ($bEchoOutput) {
                echo $sOutput;
            }
            if (!is_null($sFileName)) {
                fwrite($oFile, $sOutput);
            }
            $sOutput = '';
        }
        $sOutput .= "\n";
    }
    if ($bEchoOutput) {
        echo $sOutput;
    }
    if (!is_null($sFileName)) {
        fwrite($oFile, $sOutput);
    }
}

function _outputRecords($sTableName, $aFieldNames, $aRecords)
{
    $sLastFieldName = end($aFieldNames);
    $aLastRecord = end($aRecords);
    $i = 0;
    $sOutput = '';
    foreach ($aRecords as $aRecord) {
        if ($i == 0) {
            $sOutput .= 'INSERT INTO `' . $sTableName . "` (";
            foreach ($aFieldNames as $sFieldName) {
                $sOutput .= '`' . $sFieldName . '`,';
            }
            $sOutput = substr($sOutput, 0, -1);
            $sOutput .= ") VALUES\n";
        }
        $sOutput .= '(';
        foreach ($aFieldNames as $sFieldName) {
            if (isset($aRecord[$sFieldName]) && !is_null($aRecord[$sFieldName])) {
                $sOutput .= Yii::app()->db->quoteValue($aRecord[$sFieldName]);
            } else {
                $sOutput .= 'NULL';
            }

            if ($sFieldName != $sLastFieldName) {
                $sOutput .= ', ';
            }
        }
        $i++;
        if ($i == 200 || ($aLastRecord == $aRecord)) {
            $sOutput .= ');' . "\n";
            $i = 0;
        } else {
            $sOutput .= '),' . "\n";
        }
    }
    return $sOutput;
}

function _countNumberOfEntries($sTableName)
{
    $aNumRows = Yii::app()->db->createCommand('SELECT COUNT(*) FROM ' . Yii::app()->db->quoteTableName($sTableName))->queryRow();
    $iNumRows = $aNumRows['COUNT(*)'];
    return $iNumRows;
}

function _getMaxNbRecords()
{
    $iMaxRecords = (int) Yii::app()->getConfig('maxdumpdbrecords');
    if ($iMaxRecords < 1) {
        $iMaxRecords = 2500;
        return $iMaxRecords; // default
    }
    return $iMaxRecords;
}


    /**
     * Get the database name
     */
function _getDbName()
{
    // Yii doesn't give us a good way to get the database name
    preg_match('/dbname=([^;]*)/', (string) Yii::app()->db->getSchema()->getDbConnection()->connectionString, $aMatches);
    $sDbName = $aMatches[1];

    return $sDbName;
}

/**
 * Get database size in MB
 */
function getDatabaseSize()
{
    $dbName = _getDbName();

    // Run the query using Yii's DB component
    $result = Yii::app()->db->createCommand("
        SELECT 
            table_schema AS `Database`, 
            ROUND(SUM(data_length) / 1024 / 1024, 2) AS `Size (MB)`
        FROM 
            information_schema.tables 
        WHERE 
            table_schema = :dbName
        GROUP BY 
            table_schema;
    ")->bindValue(':dbName', $dbName)->queryRow();

    if ($result) {
        // echo "Database: " . $result['Database'] . "<br>";
        return $result['Size (MB)']; // Size in MB
    } else {
        return null;
    }
}
