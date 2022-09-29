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

use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\Status\StatusAbstract;

// phpcs:ignore
abstract class LSYii_ControllerRest extends LSYii_Controller
{
    /**
     * Run REST controller action.
     *
     * @param string $actionID
     * @return void
     */
    public function run($actionID)
    {
        if ($actionID == null) {
            $actionID = $this->defaultAction;
        }
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
     * Return data to browser as JSON with the correct hHTTPttp response code.
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

    /**
     * Get auth token.
     *
     * Attempts to read from 'authToken' GET parameter and falls back to authorisation bearer token.
     *
     * @return string|null
     */
    public function getAuthToken()
    {
        $token = Yii::app()->request->getParam('authToken');
        if (!$token) {
            $token = $this->getAuthBearerToken();
        }
        return $token;
    }

    /**
     * Get auth bearer token.
     *
     * Attempts to read bearer token from authorisation header.
     *
     * @return string|null
     */
    protected function getAuthBearerToken()
    {
        $headers = $this->getAllHeaders();

        $token = null;
        if (
            isset($headers['Authorization'])
            && strpos(
                $headers['Authorization'],
                'Bearer '
            ) === 0
        ) {
            $token = substr($headers['Authorization'], 7);
        }

        return $token;
    }

    /**
     * Get all HTTP header key/values as an associative array for the current request.
     *
     * @source https://github.com/ralouphie/getallheaders ralouphie/getallheader
     * @return string[string] The HTTP header key/value pairs.
     */
    protected function getAllHeaders()
    {
        $headers = array();

        $copy_server = array(
            'CONTENT_TYPE'   => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5'    => 'Content-Md5',
        );

        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $key = substr($key, 5);
                if (!isset($copy_server[$key]) || !isset($_SERVER[$key])) {
                    $key = str_replace(
                        ' ',
                        '-',
                        ucwords(
                            strtolower(
                                str_replace('_', ' ', $key)
                            )
                        )
                    );
                    $headers[$key] = $value;
                }
            } elseif (isset($copy_server[$key])) {
                $headers[$copy_server[$key]] = $value;
            }
        }

        if (!isset($headers['Authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $basic_pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
                $headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }

        return $headers;
    }
}
