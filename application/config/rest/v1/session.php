<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Session
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];
$v1Namespace = '\LimeSurvey\Api\Command\V1\\';

/**
 * @OA\Post(
 *      path="/rest/v1/session",
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
 *      path="/rest/v1/session",
 *      security={{"bearerAuth":{}}},
 *      summary="Delete session",
 *      description="Delete session",
 *      tags={"Session"},
 *      @OA\Response(
 *          response="200",
 *          description="Success - session was deleted"
 *      )
 * )
 */
$rest['v1/session'] = [
    'POST' => [
        'commandClass' => $v1Namespace . 'SessionKeyCreate',
        'params' => [
            'username' => true,
            'password' => true
        ],
        'bodyParams' => []
    ],
    'DELETE' => [
        'commandClass' => $v1Namespace . 'SessionKeyRelease',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => []
    ]
];

return $rest;
