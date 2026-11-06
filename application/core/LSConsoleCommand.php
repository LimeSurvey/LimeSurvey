<?php

/*
* LimeSurvey
* Copyright (C) 2007-2026 The LimeSurvey Project Team
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Base class for console commands that can work on another installation than the
 * one in application/config/config.php by passing the path of its config.php.
 */
abstract class LSConsoleCommand extends CConsoleCommand
{
    /**
     * Points the database connection of the application at the database of the given config.php.
     *
     * @param string $configPath Path of the config.php of the installation to work on
     * @return void
     * @throws CException If the config file cannot be found or is not a LimeSurvey config.php
     */
    protected function useCustomConfig($configPath)
    {
        $readFromConfig = realpath($configPath);
        if ($readFromConfig === false) {
            throw new CException("Config file not found: $configPath");
        }
        // Checked before the include: any other PHP file would run before being rejected
        if (basename($readFromConfig) !== 'config.php') {
            throw new CException("Not a LimeSurvey config.php (the file must be named config.php): $configPath");
        }
        $configuration = include($readFromConfig);
        if (!is_array($configuration) || empty($configuration['components']['db']['connectionString'])) {
            throw new CException("Not a LimeSurvey config.php (no database connection in it): $configPath");
        }
        $dbConnectionArray = $configuration['components']['db'];
        foreach ($configuration as $configKey => $configValue) {
            Yii::app()->params[$configKey] = $configValue;
        }
        // Prevent CDbConnection::init() from auto-connecting with the default connection
        // before it is pointed at the custom config below
        Yii::app()->configure(array('components' => array('db' => array('autoConnect' => false))));
        $connection = Yii::app()->getDb();
        $connection->active = false;
        $connection->connectionString = $dbConnectionArray['connectionString'];
        $connection->username = $dbConnectionArray['username'];
        $connection->password = $dbConnectionArray['password'];
        // An empty string, not null: Yii only replaces the {{table}} placeholders when a prefix is set
        $connection->tablePrefix = $dbConnectionArray['tablePrefix'] ?? '';
        $connection->active = true;
        // The console application already read settings_global of the default installation at
        // startup: forget the cached table schemas and the model metadata, which still carry the
        // default installation's table prefix
        $connection->getSchema()->refresh();
        SettingGlobal::model()->refreshMetaData();
        echo "Using connection string " . $connection->connectionString . "\n";
    }
}
