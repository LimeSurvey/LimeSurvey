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
use LimeSurvey\Api\Rest\RestEndpointFactory;
use LimeSurvey\Api\Command\Response\Status\StatusAbstract;
use LimeSurvey\Api\Rest\Response\RestResponseV1;

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
        Yii::app()->loadConfig('rest');
        $request = Yii::app()->request;
        $endpoint = (new RestEndpointFactory())->create(
            $request
        );
        $renderer = $endpoint->getResponseRenderer();

        try {
            $commandResponse = $endpoint->runCommand();
            $renderer->returnResponse($commandResponse);
        } catch (Exception $e) {
            $renderer->returnException($e);
        }
    }
}
