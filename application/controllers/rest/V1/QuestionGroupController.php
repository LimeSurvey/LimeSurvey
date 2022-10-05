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
     *      path="/rest/v1/questionGroup",
     *      security={{"bearerAuth":{}}},
     *      summary="Create question group",
     *      description="Create question group",
     *      tags={"Question Group"},
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              required={
     *                  "survey_id",
     *                  "group_name",
     *                  "description"
     *              },
     *              @OA\Property(
     *                  property="survey_id",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="group_name",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="description",
     *                  type="string"
     *              ),
     *              example={
     *                  "survey_id": "1",
     *                  "group_name": "Test Group",
     *                  "description" : "This is a test group"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Success"
     *      )
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
     * @OA\Get(
     *      path="/rest/v1/questionGroup",
     *      security={{"bearerAuth":{}}},
     *      summary="Get question group list",
     *      description="Get question group list",
     *      tags={"Question Group"},
     *      @OA\Parameter(
     *          parameter="query_survey_id",
     *          name="survey_id",
     *          description="Survey id",
     *          @OA\Schema(
     *              type="string"
     *          ),
     *          in="query",
     *          required=true
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/question_group_list"
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad request"
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Not found"
     *      )
     * )
     *
     * @OA\Get(
     *      path="/rest/v1/questionGroup/{id}",
     *      security={{"bearerAuth":{}}},
     *      summary="Get question group by id",
     *      description="Get question group by id",
     *      tags={"Question Group"},
     *      @OA\Parameter(
     *          description="Question group id",
     *          in="path",
     *          name="id",
     *          required=true,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/question_group_detail"
     *          )
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Bad request"
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Not found"
     *     )
     * )
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
     * @OA\Put(
     *     path="/rest/v1/questionGroup/{id}",
     *     security={{"bearerAuth":{}}},
     *     summary="Update question group by id",
     *     description="Update question group by id",
     *     tags={"Question Group"},
     *     @OA\Parameter(
     *         description="Question group id",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *              required={
     *                  "language",
     *                  "questionData"
     *              },
     *              @OA\Property(
     *                  property="language",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="questionData",
     *                  type="object",
     *                  required={},
     *                  @OA\Property(
     *                      property="group_order",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="randomization_group",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="grelevance",
     *                      type="string"
     *                  )
     *              ),
     *              example={
     *                  "group_name": "Question group Two 2",
     *                  "description": "Question group Two 2 Desc",
     *                  "language": "en",
     *                  "questiongroupl10ns": {
     *                      "en": {
     *                          "group_name": "Question group 2 Two",
     *                          "description": ""
     *                      }
     *                  },
     *                  "order": "1",
     *                  "randomization_group": "",
     *                  "grelevance": "1"
     *              }
     *          )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="group_name",
     *                  type="bool"
     *              ),
     *              @OA\Property(
     *                  property="description",
     *                  type="bool"
     *              ),
     *              @OA\Property(
     *                  property="language",
     *                  type="bool"
     *              ),
     *              @OA\Property(
     *                  property="questiongroupl10ns",
     *                  type="object",
     *                  @OA\Property(
     *                      property="en",
     *                      type="object",
     *                      @OA\Property(
     *                          property="group_name",
     *                          type="string",
     *                      ),
     *                      @OA\Property(
     *                          property="description",
     *                          type="string"
     *                      )
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="order",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="randomization_group",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="grelevance",
     *                  type="string"
     *              ),
     *              example={
     *                  "questiongroupl10ns": {
     *                      "en": {
     *                          "language": true,
     *                          "group_name": true,
     *                          "description": true
     *                      }
     *                  },
     *                  "group_order": true,
     *                  "randomization_group": true,
     *                  "grelevance": true
     *              }
     *          )
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Bad request"
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     ),
     *      @OA\Response(
     *          response="404",
     *          description="Not found"
     *     )
     * )
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
     * Delete question group by id.
     *
     * @OA\Delete(
     *      path="/rest/v1/questionGroup/{id}",
     *      security={{"bearerAuth":{}}},
     *      summary="Delete question group by id",
     *      description="Delete question group by id",
     *      tags={"Question Group"},
     *      @OA\Parameter(
     *          description="Question group id",
     *          in="path",
     *          name="id",
     *          required=true,
     *          @OA\Schema(type="string"),
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Success"
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad request"
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Not found"
     *     )
     * )
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
