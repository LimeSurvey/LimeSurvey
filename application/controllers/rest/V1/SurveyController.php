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

/**
 * Survey Controller
 */
class SurveyController extends LSYii_ControllerRest
{
    /**
     * Handle post request
     *
     * @OA\Post(
     *      path="/rest/v1/survey",
     *      security={{"bearerAuth":{}}},
     *      summary="Create survey",
     *      description="Create survey",
     *      tags={"Survey"},
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              required={
     *                  "group_name",
     *                  "description"
     *              },
     *              @OA\Property(
     *                  property="surveyID",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="surveyTitle",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="surveyLanguage",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="format",
     *                  type="string"
     *              ),
     *              example={
     *                  "surveyID": "1",
     *                  "surveyTitle": "Test Survey",
     *                  "surveyLanguage": "en",
     *                  "format": null
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Success - returns survey id",
     *          @OA\JsonContent(
     *              type="integer",
     *              example=263491
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
     * @return void
     */
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
        $commandResponse = (new SurveyAdd())
            ->run($commandRequest);

        $this->renderCommandResponse($commandResponse);
    }

    /**
     * Handle get request
     *
     * @OA\Get(
     *      path="/rest/v1/survey/{id}",
     *      security={{"bearerAuth":{}}},
     *      summary="Get survey by id",
     *      description="Get survey by id",
     *      tags={"Survey"},
     *      @OA\Parameter(
     *          description="Survey id",
     *          in="path",
     *          name="id",
     *          required=true,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Success"
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
     * @param string $id
     * @return void
     */
    public function actionIndexGet(string $id)
    {
        $request = Yii::app()->request;
        $requestData = array(
            'sessionKey' => $this->getAuthToken(),
            'surveyID' => $id,
            'surveySettings' => $request->getParam('settings')
        );
        $commandRequest = new Request($requestData);
        $commandResponse = (new SurveyPropertiesGet())
            ->run($commandRequest);

        $this->renderCommandResponse($commandResponse);
    }

    /**
     * Handle delete request
     *
     * @OA\Delete(
     *      path="/rest/v1/survey/{id}",
     *      security={{"bearerAuth":{}}},
     *      summary="Delete survey by id",
     *      description="Delete survey by id",
     *      tags={"Survey"},
     *      @OA\Parameter(
     *          description="Survey id",
     *          in="path",
     *          name="id",
     *          required=true,
     *          @OA\Schema(type="string")
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
     * @param string $id
     * @return void
     */
    public function actionIndexDelete(string $id)
    {
        $requestData = array(
            'sessionKey' => $this->getAuthToken(),
            'surveyID' => $id
        );
        $commandRequest = new Request($requestData);
        $commandResponse = (new SurveyDelete())
            ->run($commandRequest);

        $this->renderCommandResponse($commandResponse);
    }
}
