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

use LimeSurvey\Api\Rest\Endpoint\EndpointFactory;
use LimeSurvey\DI;

// phpcs:ignore
class RestController extends LSYii_Controller
{
    /**
     * Runs the named action.
     *
     * Run REST controller actions with beforeControllerAction
     * and afterControllerAction events.
     *
     * @param string $actionID action ID
     * @return void
     * @throws \Exception
     */
    public function run($actionID)
    {
        Yii::app()->loadConfig('rest');
        $action = new CInlineAction($this, 'index');
        if (Yii::app()->beforeControllerAction($this, $action)) {
            $endpointFactory = DI::getContainer()
                ->get(EndpointFactory::class);

            ($endpointFactory)->create(
                Yii::app()->request
            )->run();

            Yii::app()->afterControllerAction($this, $action);
        }
    }
}
