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
*/

use LimeSurvey\Api\Command\CommandRequest;
use LimeSurvey\Api\Command\V1\SessionKeyCreate;
use LimeSurvey\Api\Command\V1\SessionKeyRelease;

class SessionController extends LSYii_ControllerRest
{
    public function actionKeyPost()
    {
        $request = \Yii::app()->request;
        $requestData = [
            'username' => $request->getPost('username'),
            'password' => $request->getPost('password')
        ];
        $commandResponse = (new SessionKeyCreate)
            ->run(new CommandRequest($requestData));

        $this->renderJson($commandResponse->getData());
    }

    public function actionKeyDelete()
    {
        $requestData = [
            'sessionKey' => $this->getAuthToken()
        ];
        $commandResponse = (new SessionKeyRelease)->run(
            new CommandRequest($requestData)
        );

        $this->renderJson($commandResponse->getData());
    }
}
