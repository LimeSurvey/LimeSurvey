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

use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\V1\SiteSettingsGet;

class SiteSettingsController extends LSYii_ControllerRest
{
    public function actionIndexGet($id)
    {
        $requestData = array(
            'sessionKey' => $this->getAuthToken(),
            'settingName' => $id
        );
        $commandRequest = new Request($requestData);
        $commandResponse = (new SiteSettingsGet())
            ->run($commandRequest);

        $this->renderCommandResponse($commandResponse);
    }
}
