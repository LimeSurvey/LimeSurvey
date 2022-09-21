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

use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\V1\QuestionList;
use LimeSurvey\Api\Command\V1\QuestionImport;
use LimeSurvey\Api\Command\V1\QuestionPropertiesGet;
use LimeSurvey\Api\Command\V1\QuestionPropertiesSet;
use LimeSurvey\Api\Command\V1\QuestionDelete;

class QuestionController extends LSYii_ControllerRest
{
    /** 
     * Create question.
     * 
     * @param string $id Question Id
     * @return void
     */
    public function actionIndexPost()
    {
        $request = Yii::app()->request;
        $data    = $request->getRestParams();
        $requestData = [
            'sessionKey' => $this->getAuthToken(),
            'surveyID' => isset($data['survey_id']) ? $data['survey_id'] : '',
            'groupID' => isset($data['group_id']) ? $data['group_id'] : '',
            'groupTitle' => isset($data['name']) ? $data['name'] : '',
            'groupDescription' => isset($data['description']) ? $data['description'] : ''
        ];
        $commandResponse = (new QuestionImport)
            ->run(new Request($requestData));

        $this->renderCommandResponse($commandResponse);
    }

    /** 
     * Get array of questions or one specific question.
     * 
     * @param string $id Question Id
     * @return void
     */
    public function actionIndexGet($id = null)
    {
        if ($id !== null) {
            $request = Yii::app()->request;
            $requestData = [
                'sessionKey' => $this->getAuthToken(),
                'questionID' => $id,
                'questionSettings' => $request->getParam('settings'),
                'language' => $request->getParam('language')
            ];
            $commandResponse = (new QuestionPropertiesGet)
                ->run(new Request($requestData));

            $this->renderCommandResponse($commandResponse);
        } else {
            $request = Yii::app()->request;
            $requestData = [
                'sessionKey' => $this->getAuthToken(),
                'surveyID' => $request->getParam('survey_id'),
                'groupID' => $request->getParam('groupId'),
                'language' => $request->getParam('language')
            ];
            $commandResponse = (new QuestionList)
                ->run(new Request($requestData));

            $this->renderCommandResponse($commandResponse);
        }
    }

    /** 
     * Update question properties.
     * 
     * @param string $id Question Id
     * @return void
     */
    public function actionIndexPut($id)
    {
        $request = Yii::app()->request;
        $data    = $request->getRestParams();

        $requestData = [
            'sessionKey' => $this->getAuthToken(),
            'questionID' => $id,
            'language' => isset($data['language']) ? $data['language'] : '',
            'questionData' => isset($data['questionData']) ? $data['questionData'] : array()
        ];
        $commandResponse = (new QuestionPropertiesSet)
            ->run(new Request($requestData));

        $this->renderCommandResponse($commandResponse);
    }

    /** 
     * Delete question by question id.
     * 
     * @param string $id Question Id 
     * @return void
     */
    public function actionIndexDelete($id)
    {
        $requestData = [
            'sessionKey' => $this->getAuthToken(),
            'questionID' => $id
        ];
        $commandResponse = (new QuestionDelete)
            ->run(new Request($requestData));

        $this->renderCommandResponse($commandResponse);
    }
}
