<?php

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Session
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];
$v1Namespace = '\LimeSurvey\Api\Command\V2\\';

/**
 * @OA\Post(
 *      path="/rest/v2/session",
 *      summary="Create session token",
 *      description="Create session token",
 *      tags={"Session"},
 *      @OA\RequestBody(
 *          @OA\MediaType(
 *              mediaType="multipart/form-data",
 *              @OA\Schema(
 *                  required={
 *                      "username",
 *                      "password"
 *                  },
 *                  @OA\Property(
 *                      property="username",
 *                      type="string",
 *                      description="Username"
 *                  ),
 *                  @OA\Property(
 *                      property="password",
 *                      type="string",
 *                      description="Password"
 *                  )
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *      response="200",
 *          description="Success - returns session token",
 *          @OA\JsonContent(
 *              @OA\Schema(
 *                  type="string",
 *                  example="jMFVh92ZL4SN2~mMr7Aam_kThUgDXuu8"
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response="400",
 *          description="Bad request"
 *      ),
 *      @OA\Response(
 *          response="401",
 *          description="Unauthorized - Invalid user name or password"
 *      )
 * )
 *
 * @OA\Delete(
 *      path="/rest/v2/session",
 *      security={{"bearerAuth":{}}},
 *      summary="Delete session",
 *      description="Delete session",
 *      tags={"Session"},
 *      @OA\Response(
 *          response="200",
 *          description="Success - session was deleted"
 *      )
 * )
 *
 */
$rest['v2/session'] = [
    'POST' => [
        'description' => 'Generate new authentication token',
        'commandClass' => $v1Namespace . 'SessionKeyCreate',
        'params' => [
            'username' => ['src' => 'form'],
            'password' => ['src' => 'form']
        ],
        'bodyParams' => [],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success - returns string access token for use in "Authorization: bearer $token" header',
                'schema' => Schema::string()->example('%7&!T%EYd@PnDB49MRfwQ!KjX48J^3x6rDhyB6DK')
            ]
        ]
    ],
    'DELETE' => [
        'description' => 'Destroy currently used authentication token',
        'commandClass' => $v1Namespace . 'SessionKeyRelease',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => [],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
            ],
            'unauthorized' => [
                'code' => 403,
                'description' => 'Unauthorized'
            ]
        ]
    ]
];

return $rest;
