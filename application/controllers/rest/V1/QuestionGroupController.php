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
use LimeSurvey\Api\Command\V1\QuestionGroupAdd;
use LimeSurvey\Api\Command\V1\QuestionGroupList;
use LimeSurvey\Api\Command\V1\QuestionGroupPropertiesGet;
use LimeSurvey\Api\Command\V1\QuestionGroupPropertiesSet;
use LimeSurvey\Api\Command\V1\QuestionGroupDelete;

/**
 */
class QuestionGroupController extends LSYii_ControllerRest
{
    /**
     * Create question group.
     *
     * @OA\Post(
     *     path="/rest/v1/questionGroup",
     *     summary="Create Question Group",
     *     description="Create Question Group",
     *     @OA\Response(
     *          response="200",
     *          description="Success"
     *     )
     * )
     *
     * @return void
     */
    public function actionIndexPost()
    {
        $request = Yii::app()->request;
        $data    = $request->getRestParams();
        $requestData = [
            'sessionKey' => $this->getAuthToken(),
            'surveyID' => isset($data['survey_id']) ? $data['survey_id'] : '',
            'groupTitle' => isset($data['name']) ? $data['name'] : '',
            'groupDescription' => isset($data['description']) ? $data['description'] : ''
        ];
        $commandResponse = (new QuestionGroupAdd())
            ->run(new Request($requestData));

        $this->renderCommandResponse($commandResponse);
    }

    /**
     * Get array of question groups or one specific question group.
     *
     * @param string $id Question Group Id
     * @return void
     */
    public function actionIndexGet($id = null)
    {
        if ($id == null) {
            $request = Yii::app()->request;
            $requestData = [
                'sessionKey' => $this->getAuthToken(),
                'surveyID' => $request->getParam('survey_id'),
                'language' => $request->getParam('language')
            ];
            $commandResponse = (new QuestionGroupList())
                ->run(new Request($requestData));

            $this->renderCommandResponse($commandResponse);
        } else {
            $request = Yii::app()->request;
            $requestData = [
                'sessionKey' => $this->getAuthToken(),
                'groupID' => $id,
                'groupSettings' => $request->getParam('settings'),
                'language' => $request->getParam('language')
            ];
            $commandResponse = (new QuestionGroupPropertiesGet())
                ->run(new Request($requestData));

            $this->renderCommandResponse($commandResponse);
        }
    }

    /**
     * Update question group properties.
     *
     * @param string $id Question Group Id
     * @return void
     */
    public function actionIndexPut($id)
    {
        $request = Yii::app()->request;
        $data    = $request->getRestParams();

        $requestData = [
            'sessionKey' => $this->getAuthToken(),
            'groupID' => $id,
            'groupData' => array(
                'group_name' => isset($data['group_name']) ? $data['group_name'] : '',
                'description' => isset($data['description']) ? $data['description'] : '',
                'language' => isset($data['language']) ? $data['language'] : '',
                'questiongroupl10ns' => isset($data['questiongroupl10ns']) ? $data['questiongroupl10ns'] : '',
                'group_order' => isset($data['group_order']) ? $data['group_order'] : '',
                'randomization_group' => isset($data['randomization_group']) ? $data['randomization_group'] : '',
                'grelevance' => isset($data['grelevance']) ? $data['grelevance'] : ''
            )
        ];
        $commandResponse = (new QuestionGroupPropertiesSet())
            ->run(new Request($requestData));

        $this->renderCommandResponse($commandResponse);
    }

    /**
     * Delete question groups by question group id.
     *
     * @param string $id Question Group Id
     * @return void
     */
    public function actionIndexDelete($id)
    {
        $requestData = [
            'sessionKey' => $this->getAuthToken(),
            'groupID' => $id
        ];
        $commandResponse = (new QuestionGroupDelete())
            ->run(new Request($requestData));

        $this->renderCommandResponse($commandResponse);
    }
}
