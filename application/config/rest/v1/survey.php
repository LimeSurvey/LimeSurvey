<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Survey
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];
$v1Namespace = '\LimeSurvey\Api\Command\V1\\';

/**
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
 *      ),
 *      @OA\Parameter(
 *          parameter="surveySettings",
 *          name="surveySettings[]",
 *          allowReserved=true,
 *          in="query",
 *          description="Survey settings",
 *          required=false,
 *          @OA\Schema(
 *              type="array",
 *              @OA\Items(type="string")
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
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
 *      )
 * )
 */
$rest['v1/survey/$surveyID'] = [
    'GET' => [
        'commandClass' => $v1Namespace . 'SurveyPropertiesGet',
        'auth' => 'session',
        'params' => [
            'surveySettings' => true
        ],
        'bodyParams' => []
    ]
];


/**
 * @OA\Post(
 *      path="/rest/v1/survey",
 *      security={{"bearerAuth":{}}},
 *      summary="Create survey",
 *      description="Create survey",
 *      tags={"Survey"},
 *      @OA\RequestBody(
 *          @OA\JsonContent(
 *              required={
 *                  "surveyTitle",
 *                  "surveyLanguage"
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
 */
$rest['v1/survey'] = [
    'POST' => [
        'commandClass' => $v1Namespace . 'SurveyAdd',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => [
            'surveyTitle' => true,
            'surveyLanguage' => true,
            'format' => true
        ]
    ],
    'DELETE' => [
        'commandClass' => $v1Namespace . 'SurveyDelete',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => []
    ]
];

return $rest;
