<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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

abstract class LSYii_ControllerRest extends LSYii_Controller
{
    public function run($actionID)
    {
        if ($actionID == null)
            $actionID = $this->defaultAction;
        $request = Yii::app()->request;
        $requestMethod = $request->getRequestType();
        $actionID = $actionID . ucfirst(strtolower($requestMethod));

        try {
            parent::run($actionID);
        } catch (Exception $e) {
            $this->displayException($e);
        }
    }

    /**
     * Return data to browser as JSON and end application.
     * @param array $data
     */
    protected function renderJSON($data)
    {
        header('Content-type: application/json');
        echo CJSON::encode($data);

        foreach (Yii::app()->log->routes as $route) {
            if ($route instanceof CWebLogRoute) {
                $route->enabled = false; // disable any weblogroutes
            }
        }
        Yii::app()->end();
    }

    public function displayError($code, $message, $file, $line)
    {
        $error = [];
        $error['code'] = $code;
        $error['message'] = $message;
        if (YII_DEBUG) {
            $error['file'] = $file;
            $error['line'] = $line;
            $error['stacktrace'] = debug_backtrace();
        }

        $this->renderJson(['error' => $error]);
    }

    public function displayException($exception)
    {
        $error = [];
        $error['code'] = get_class($exception);
        $error['message'] = $exception->getMessage();
        if (YII_DEBUG) {
            $error['file'] = $exception->getFile();
            $error['line'] = $exception->getLine();
            $error['stacktrace'] = $exception->getTraceAsString();
        }

        $this->renderJson(['error' => $error]);
    }
}
