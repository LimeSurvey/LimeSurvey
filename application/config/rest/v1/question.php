<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Question
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];
$v1Namespace = '\LimeSurvey\Api\Command\V1\\';

/**
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
 *              ref="#/components/schemas/response_status"
 *          )
 *      ),
 *      @OA\Response(
 *          response="401",
 *          description="Unauthorized"
 *      )
 * )
 *
 * @OA\Get(
 *      path="/rest/v1/question",
 *      security={{"bearerAuth":{}}},
 *      summary="Get question list",
 *      description="Get question list",
 *      tags={"Question"},
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
 *          required=true,
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
 */
$rest['v1/question'] = [
    'POST' => [
        'commandClass' => $v1Namespace . 'QuestionImport',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => [
            'surveyID' => true,
            'groupID' => true,
            'importData' => true,
            'importDataType' => true,
            'newQuestionTitle' => true,
            'newQuestion' => true,
            'newQuestionHelp' => true
        ]
    ],
    'GET' => [
        'commandClass' => $v1Namespace . 'QuestionGroupList',
        'auth' => 'session',
        'params' => [
            'surveyID' => true,
            'groupID' => true,
            'language' => true,
        ],
        'bodyParams' => []
    ]
];

/**
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
 *          parameter="language",
 *          name="language",
 *          in="query",
 *          description="Language",
 *          required=true,
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
 */
$rest['v1/question/$questionID'] = [
    'GET' => [
        [
            'commandClass' => $v1Namespace . 'QuestionPropertiesGet',
            'auth' => 'session',
            'params' => [
                'questionSettings' => true,
                'language' => true
            ],
            'bodyParams' => []
        ]
    ],
    'PUT' => [
        [
            'commandClass' => $v1Namespace . 'QuestionPropertiesSet',
            'auth' => 'session',
            'params' => [],
            'bodyParams' => [
                'language' => true,
                'questionData' => true
            ]
        ]
    ],
    'DELETE' => [
        [
            'commandClass' => $v1Namespace . 'QuestionDelete',
            'auth' => 'session',
            'params' => [],
            'bodyParams' => []
        ]
    ]
];


return $rest;
