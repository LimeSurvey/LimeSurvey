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
use LimeSurvey\Api\Command\V1\SurveyAdd;
use LimeSurvey\Api\Command\V1\SurveyPropertiesGet;
use LimeSurvey\Api\Command\V1\SurveyDelete;

class SurveyController extends LSYii_ControllerRest
{
    public function actionIndexPost()
    {
        $request = Yii::app()->request;
        $data = $request->getRestParams();
        $commandRequest = new Request(array(
            'sessionKey' => $this->getAuthToken(),
            'surveyID' => isset($data['surveyID']) ? $data['surveyID'] : '',
            'surveyTitle' => isset($data['surveyTitle']) ? $data['surveyTitle'] : '',
            'surveyLanguage' => isset($data['surveyLanguage']) ? $data['surveyLanguage'] : '',
            'format' => isset($data['format']) ? $data['format'] : null
        ));
        $commandResponse = (new SurveyAdd)
            ->run($commandRequest);

        $this->renderCommandResponse($commandResponse);
    }

    public function actionIndexGet($id)
    {
        $request = Yii::app()->request;
        $requestData = array(
            'sessionKey' => $this->getAuthToken(),
            'surveyID' => $id,
            'surveySettings' => $request->getParam('settings')
        );
        $commandRequest = new Request($requestData);
        $commandResponse = (new SurveyPropertiesGet)
            ->run($commandRequest);

        $this->renderCommandResponse($commandResponse);
    }

    public function actionIndexDelete($id)
    {
        $requestData = array(
            'sessionKey' => $this->getAuthToken(),
            'surveyID' => $id
        );
        $commandRequest = new Request($requestData);
        $commandResponse = (new SurveyDelete)
            ->run($commandRequest);

        $this->renderCommandResponse($commandResponse);
    }
}
