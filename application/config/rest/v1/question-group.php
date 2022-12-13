<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Question Group
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];
$v1Namespace = '\LimeSurvey\Api\Command\V1\\';

/**
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
 *      ),
 *      @OA\Parameter(
 *          parameter="groupSettings",
 *          name="groupSettings[]",
 *          allowReserved=true,
 *          in="query",
 *          description="Group settings",
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
 *          @OA\Schema(type="string")
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Success",
 *          @OA\JsonContent(
 *              ref="#/components/schemas/question_group_detail"
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
 */
$rest['v1/questionGroup/$groupID'] = [
    'GET' => [
        'commandClass' => $v1Namespace . 'QuestionGroupPropertiesGet',
        'auth' => 'session',
        'params' => [
            'language' => true
        ],
        'bodyParams' => []
    ],
    'PUT' => [
        'commandClass' => $v1Namespace . 'QuestionGroupPropertiesSet',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => [
            'group_name' => true,
            'description' => true,
            'language' => true,
            'questiongroupl10ns' => true,
            'group_order' => true,
            'randomization_group' => true,
            'grelevance' => true
        ]
    ],
    'DELETE' => [
        'commandClass' => $v1Namespace . 'QuestionGroupDelete',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => []
    ]
];

/**
 * @OA\Post(
 *      path="/rest/v1/questionGroup",
 *      security={{"bearerAuth":{}}},
 *      summary="Create question group",
 *      description="Create question group",
 *      tags={"Question Group"},
 *      @OA\RequestBody(
 *          @OA\JsonContent(
 *              required={
 *                  "surveyID",
 *                  "groupTitle",
 *                  "groupDescription"
 *              },
 *              @OA\Property(
 *                  property="surveyID",
 *                  type="string"
 *              ),
 *              @OA\Property(
 *                  property="groupTitle",
 *                  type="string"
 *              ),
 *              @OA\Property(
 *                  property="groupDescription",
 *                  type="string"
 *              ),
 *              example={
 *                  "surveyID": "1",
 *                  "groupTitle": "Test Group",
 *                  "groupDescription" : "This is a test group"
 *              }
 *          )
 *      ),
 *      @OA\Response(
 *          response="200",
 *          description="Success"
 *      )
 * )
 *
 * @OA\Get(
 *      path="/rest/v1/questionGroup",
 *      security={{"bearerAuth":{}}},
 *      summary="Get question group list",
 *      description="Get question group list",
 *      tags={"Question Group"},
 *      @OA\Parameter(
 *          parameter="surveyID",
 *          name="surveyID",
 *          required=true,
 *          in="query",
 *          description="Survey id",
 *          @OA\Schema(
 *              type="string"
 *          ),
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
 */
$rest['v1/questionGroup'] = [
    'POST' => [
        'commandClass' => $v1Namespace . 'QuestionGroupAdd',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => [
            'surveyID' => true,
            'groupTitle' => true,
            'groupDescription' => true
        ]
    ],
    'GET' => [
        'commandClass' => $v1Namespace . 'QuestionGroupList',
        'auth' => 'session',
        'params' => [
            'surveyID' => true,
            'language' => true
        ],
        'bodyParams' => []
    ]
];



return $rest;
