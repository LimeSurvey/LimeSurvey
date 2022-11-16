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

use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Rest\EndpointFactory;
use LimeSurvey\Api\Command\Response\Status\StatusAbstract;

// phpcs:ignore
class RestController extends LSYii_Controller
{
    /**
     * Run REST controller action.
     *
     * @param string $actionId
     * @return void
     */
    public function run($actionId = null)
    {
        try {
            Yii::app()->loadConfig('rest');

            $request = Yii::app()->request;

            $endpoint = (new EndpointFactory())->create(
                $request
            );
            $commandResponse = $endpoint->runCommand();

            $this->renderCommandResponse($commandResponse);
        } catch (Exception $e) {
            $this->displayException($e);
        }
    }

    /**
     * Return data to browser as JSON with the correct HTTP response code.
     *
     * @param Response $response
     * @return void
     */
    protected function renderCommandResponse(Response $response)
    {
        $this->renderJSON(
            $response->getData(),
            $this->getHttpResponseCode($response->getStatus())
        );
    }

    /**
     * Return data to browser as JSON and end application.
     *
     * @param array $data
     * @param int $responseCode
     * @return void
     */
    protected function renderJSON($data, $responseCode = 200)
    {
        http_response_code($responseCode);
        header('Content-type: application/json');
        echo CJSON::encode($data);

        foreach (Yii::app()->log->routes as $route) {
            if ($route instanceof CWebLogRoute) {
                $route->enabled = false; // disable any weblogroutes
            }
        }
        Yii::app()->end();
    }

    /**
     * Get HTTP response code from command response status.
     *
     * @param StatusAbstract $status
     * @return void
     */
    protected function getHttpResponseCode(StatusAbstract $status)
    {
        $httpCode = 200;
        switch ($status->getCode()) {
            case 'success':
                $httpCode = 200;
                break;
            case 'success_created':
                $httpCode = 201;
                break;
            case 'error':
                $httpCode = 400;
                break;
            case 'error_unauthorised':
                $httpCode = 401;
                break;
            case 'error_bad_request':
                $httpCode = 400;
                break;
            case 'error_not_found':
                $httpCode = 404;
                break;
        }
        return $httpCode;
    }

    /**
     * Return error as JSON.
     *
     * @param string $code
     * @param string $message
     * @param string $file
     * @param string $line
     * @return void
     */
    protected function displayError($code, $message, $file, $line)
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

    /**
     * Return Exception as JSON.
     *
     * @param Exception $exception
     * @return void
     */
    protected function displayException($exception)
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
