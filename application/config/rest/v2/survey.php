<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Survey
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];
$v2Namespace = '\LimeSurvey\Api\Command\V2\\';

/**
 * @OA\Get(
 *      path="/rest/v2/survey",
 *      security={{"bearerAuth":{}}},
 *      summary="Get survey list",
 *      description="Get survey list",
 *      tags={"Survey"},
 *      @OA\Parameter(
 *          parameter="page",
 *          name="page",
 *          allowReserved=true,
 *          in="query",
 *          description="Page",
 *          required=false,
 *          @OA\Schema(
 *              type="number"
 *          )
 *      ),
 *      @OA\Parameter(
 *          parameter="limit",
 *          name="limit",
 *          allowReserved=true,
 *          in="query",
 *          description="Limit",
 *          required=false,
 *          @OA\Schema(
 *              type="number"
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
$rest['v2/survey'] = [
    'GET' => [
        'commandClass' => $v2Namespace . 'SurveyList',
        'auth' => 'session',
        'params' => [
            'page' => true,
            'limit' => true
        ],
        'bodyParams' => []
    ]
];

/**
 * @OA\Get(
 *      path="/rest/v2/survey-detail/{id}",
 *      security={{"bearerAuth":{}}},
 *      summary="Get survey by id",
 *      description="Get survey by id",
 *      tags={"Survey"},
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
$rest['v2/survey-detail/$surveyId'] = [
    'GET' => [
        'commandClass' => $v2Namespace . 'SurveyDetail',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => []
    ]
];

/**
 * @OA\Patch(
 *      path="/rest/v2/survey/{id}",
 *      security={{"bearerAuth":{}}},
 *      summary="Patch survey by id",
 *      description="Patch survey by id",
 *      tags={"Survey"},
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
$rest['v2/survey/$surveyId'] = [
    'PATCH' => [
        'commandClass' => $v2Namespace . 'SurveyPatch',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => []
    ]
];

return $rest;
