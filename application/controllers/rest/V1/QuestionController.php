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
     *      path="/rest/v1/question",
     *      security={{"bearerAuth":{}}},
     *      summary="Create question",
     *      description="Create question",
     *      tags={"Question"},
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              required={
     *                  "surveyID",
     *                  "groupID",
     *                  "newQuestionTitle",
     *                  "newQuestion"
     *              },
     *              @OA\Property(
     *                  property="surveyID",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="groupID",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="importData",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="importDataType",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="newQuestionTitle",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="newQuestion",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="newQuestionHelp",
     *                  type="string"
     *              ),
     *              example={
     *                  "surveyID": "1",
     *                  "groupID": "1",
     *                  "newQuestionTitle": "In what country do you live?",
     *                  "newQuestion": "In what country do you live?",
     *                  "newQuestionHelp": "In what country do you live?"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *      response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Schema(
     *                  type="string",
     *                  example=123
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad request",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/question_status_error_invalid_question_id"
     *          )
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized"
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
            'surveyID' => isset($data['surveyID']) ? $data['surveyID'] : '',
            'groupID' => isset($data['groupID']) ? $data['groupID'] : '',
            'importData' => isset($data['importData']) ? $data['importData'] : '',
            'importDataType' => isset($data['importDataType']) ? $data['importDataType'] : '',
            'newQuestionTitle' => isset($data['newQuestionTitle']) ? $data['newQuestionTitle'] : '',
            'newQuestion' => isset($data['newQuestion']) ? $data['newQuestion'] : '',
            'newQuestionHelp' => isset($data['newQuestionHelp']) ? $data['newQuestionHelp'] : ''
        ];
        $commandResponse = (new QuestionImport())
            ->run(new Request($requestData));

        $this->renderCommandResponse($commandResponse);
    }

    /**
     * Get array of questions or one specific question.
     *
     * @OA\Get(
     *      path="/rest/v1/question",
     *      security={{"bearerAuth":{}}},
     *      summary="Get question list",
     *      description="Get question list",
     *      tags={"Question"},
     *      @OA\Parameter(
     *          parameter="questionSettings",
     *          name="questionSettings",
     *          in="query",
     *          description="Question settings",
     *          required=false,
     *          @OA\Schema(
     *              type="array",
     *              @OA\Items(type="string")
     *          )
     *      ),
     *      @OA\Parameter(
     *          parameter="language",
     *          name="language",
     *          in="query",
     *          description="Language",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/question_list"
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
     *      path="/rest/v1/question/{id}",
     *      security={{"bearerAuth":{}}},
     *      summary="Get question by id",
     *      description="Get question by id",
     *      tags={"Question"},
     *      @OA\Parameter(
     *          description="Question id",
     *          in="path",
     *          name="id",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          parameter="surveyID",
     *          name="surveyID",
     *          in="query",
     *          description="Survey id",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          parameter="groupID",
     *          name="groupID",
     *          in="query",
     *          description="Group id",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          parameter="language",
     *          name="language",
     *          in="query",
     *          description="Language",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/question_detail"
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
                'questionSettings' => $request->getParam('questionSettings'),
                'language' => $request->getParam('language')
            ];
            $commandResponse = (new QuestionPropertiesGet())
                ->run(new Request($requestData));

            $this->renderCommandResponse($commandResponse);
        } else {
            $request = Yii::app()->request;
            $requestData = [
                'sessionKey' => $this->getAuthToken(),
                'surveyID' => $request->getParam('surveyID'),
                'groupID' => $request->getParam('groupID'),
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
     *     security={{"bearerAuth":{}}},
     *     summary="Update question by id",
     *     description="Update question by id",
     *     tags={"Question"},
     *     @OA\Parameter(
     *         description="Question id",
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
     *                  "language": "en",
     *                  "questionData": {
     *                      "group_order": "1",
     *                      "randomization_group": "1",
     *                      "grelevance": "1"
     *                  }
     *              }
     *          )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="group_order",
     *                      type="bool"
     *                  ),
     *                  @OA\Property(
     *                      property="randomization_group",
     *                      type="bool"
     *                  ),
     *                  @OA\Property(
     *                      property="grelevance",
     *                      type="bool"
     *                  ),
     *                  example={
     *                      "group_order": true,
     *                      "randomization_group": true,
     *                      "grelevance": true
     *                  }
     *              )
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
     *      path="/rest/v1/question/{id}",
     *      security={{"bearerAuth":{}}},
     *      summary="Delete question by id",
     *      description="Delete question by id",
     *      tags={"Question"},
     *      @OA\Parameter(
     *          description="Question id",
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
