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
     * @var array Data used for rendering views
     */
    protected array $data = [];

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

        $this->data = $this->getData();
    }

    /**
     * Base function
     *
     * This functions receives the request to generate a dump file for the
     * database and does so! Only superadmins are allowed to do this!
     */
    public function index()
    {
        $this->data['topbar']['title'] = gT('Backup entire database');
        $this->data['topbar']['backLink'] = App()->createUrl('admin/index');

        $event = new PluginEvent('beforeRenderDbDumpView');
        App()->getPluginManager()->dispatchEvent($event);
        $htmlContent = $event->get('html');

        // Use the existing renderWrappedTemplate method
        $this->renderWrappedTemplate('dumpdb', 'dumpdb_view', array_merge($this->data, ['htmlContent' => $htmlContent]));
    }

    /**
     * Send the headers so that it is shown as a download
     * @param string $sFileName
     */
    private function outputHeaders(string $sFileName)
    {
        header('Content-type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $sFileName);
        header("Cache-Control: no-store, no-cache, must-revalidate"); // Don't store in cache because it is sensitive data
    }

    private function getData()
    {
        if ($this->data === []) {
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
        return $this->data;
    }

    public function outPutDatabase()
    {
        // Check if it's a POST request
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(405, gT("Invalid action"));
        }

        // Check if user has necessary permissions
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }

        if ($this->data['downloadable'] === false) {
            throw new CHttpException(403, gT("The database is too large to be downloaded. Please consider exporting it manually using your database client."));
        }

        Yii::app()->loadHelper("admin/backupdb");
        $sDbName = _getDbName();
        $sFileName = 'LimeSurvey_' . $sDbName . '_dump_' . dateShift(date('Y-m-d H:i:s'), 'Y-m-d') . '.sql';
        $this->outputHeaders($sFileName);
        outputDatabase();
        return;
    }
}
