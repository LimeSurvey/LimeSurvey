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
 * Dump Database
 *
 * @package LimeSurvey
 * @copyright 2011
 * @access public
 */
class Dumpdb extends SurveyCommonAction
{
    /**
     * Dumpdb constructor.
     * @param $controller
     * @param $id
     */
    public function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            die();
        }

        if (!in_array(Yii::app()->db->getDriverName(), array('mysql', 'mysqli'))) {
            die(sprintf(gT('This feature is only available for MySQL databases. Your database type is %s.'), Yii::app()->db->getDriverName()));
        }
        if (Yii::app()->getConfig('demoMode')) {
            Yii::app()->setFlashMessage(gT('This function cannot be executed because demo mode is active.'), 'error');
            $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin"));
        }
    }

    /**
     * Base function
     *
     * This functions receives the request to generate a dump file for the
     * database and does so! Only superadmins are allowed to do this!
     */
    public function index()
    {
        $data = $this->getData();

        $data['topbar']['title'] = gT('Backup entire database');
        $data['topbar']['backLink'] = App()->createUrl('admin/index');

        $this->renderWrappedTemplate('dumpdb', 'dumpdb_view', $data);
    }

    /**
     * Send the headers so that it is shown as a download
     * @param string $sFileName
     */
    private function outputHeaders(string $sFileName)
    {
        header('Content-type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $sFileName);
        header("Cache-Control: no-store, no-cache, must-revalidate");  // Don't store in cache because it is sensitive data
    }

    private function getData()
    {
        Yii::app()->loadHelper("admin/backupdb");
        $dbSize = getDatabaseSize();
        $downloadable = true;
        if ($dbSize > Yii::app()->getConfig('maxDatabaseSizeForDump')) {
            $downloadable = false;
        }
        return [
            'downloadable' => $downloadable,
            'dbSize' => $dbSize,
        ];
    }

    public function outPutDatabase()
    {
        Yii::app()->loadHelper("admin/backupdb");
        $sDbName = _getDbName();
        $sFileName = 'LimeSurvey_' . $sDbName . '_dump_' . dateShift(date('Y-m-d H:i:s'), 'Y-m-d', Yii::app()->getConfig('timeadjust')) . '.sql';
        $this->outputHeaders($sFileName);
        outputDatabase();
        return;
    }
}
