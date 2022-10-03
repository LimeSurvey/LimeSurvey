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

/**
 * Question Controller
 * 
 */
class QuestionController extends LSYii_ControllerRest
{
    /**
     * Create question.
     *
     * @OA\Post(
     *     path="/rest/v1/question",
     *     summary="Create Question",
     *     description="Create Question",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="survey_id",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="group_id",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                     property="description",
     *                     type="string"
     *                 ),
     *                 example={
     *                      "survey_id": "1", 
     *                      "group_id": "1", 
     *                      "name": "country", 
     *                      "description" : "In what country do you live?"
     *                  }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *          response="200", 
     *          description="Success"
     *     ),
     *     @OA\Response(
     *          response="400", 
     *          description="Bad Request"
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
            'groupID' => isset($data['group_id']) ? $data['group_id'] : '',
            'groupTitle' => isset($data['name']) ? $data['name'] : '',
            'groupDescription' => isset($data['description']) ? $data['description'] : ''
        ];
        $commandResponse = (new QuestionImport())
            ->run(new Request($requestData));

        $this->renderCommandResponse($commandResponse);
    }

    /**
     * Get array of questions or one specific question.
     * 
     * @OA\Get(
     *     path="/rest/v1/question",
     *     summary="Get question list",
     *     description="Get question list",
     *     @OA\Response(
     *          response="200", 
     *          description="Success",
     *          @OA\JsonContent(
     *             ref="#/components/schemas/question_list"
     *          )
     *     ),
     *      @OA\Response(
     *          response="400", 
     *          description="Bad Request"
     *     ),
     *     @OA\Response(
     *          response="401", 
     *          description="Unauthorized"
     *     ),
     *      @OA\Response(
     *          response="404", 
     *          description="Not Found"
     *     )
     * )
     * 
     * @OA\Get(
     *      path="/rest/v1/question/{id}",
     *      summary="Get question by ID",
     *      description="Get question by ID",
     *      @OA\Parameter(
     *          description="Question ID",
     *          in="path",
     *          name="id",
     *          required=true,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/question_detail"
     *          )
     *     ),
     *     @OA\Response(
     *          response="400", 
     *          description="Bad Request",
     *          @OA\JsonContent(
     *             ref="#/components/schemas/question_status_error_invalid_question_id"
     *          )
     *     ),
     *     @OA\Response(
     *          response="401", 
     *          description="Unauthorized"
     *     ),
     *      @OA\Response(
     *          response="404", 
     *          description="Not Found"
     *     )
     * )
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
            $commandResponse = (new QuestionPropertiesGet())
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
            $commandResponse = (new QuestionList())
                ->run(new Request($requestData));

            $this->renderCommandResponse($commandResponse);
        }
    }

    /**
     * Update question properties.
     *
     * @OA\Put(
     *     path="/rest/v1/question/{id}",
     *     summary="Update question by id",
     *     description="Update question by id",
     *     @OA\Parameter(
     *         description="Question ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *              @OA\Property(
     *                  property="id",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="phone",
     *                  oneOf={
     *                      @OA\Schema(type="string"),
     *                      @OA\Schema(type="integer"),
     *                  }
     *              ),
     *              example={
     *                  "id": "a3fb6", 
     *                  "name": "Jessica Smith", 
     *                  "phone": 12345678
     *              }
     *          )
     *     ),
     *     @OA\Response(
     *          response="200", 
     *          description="Success"
     *     )
     * )
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
        $commandResponse = (new QuestionPropertiesSet())
            ->run(new Request($requestData));

        $this->renderCommandResponse($commandResponse);
    }

    /**
     * Delete question by question id.
     *
     * @OA\Delete(
     *     path="/rest/v1/question/{id}",
     *     summary="Delete question by ID",
     *     description="Delete question by ID",
     *     @OA\Parameter(
     *         description="Question ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Response(
     *          response="200", 
     *          description="Success"
     *     )
     * )
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
        $commandResponse = (new QuestionDelete())
            ->run(new Request($requestData));

        $this->renderCommandResponse($commandResponse);
    }
}
