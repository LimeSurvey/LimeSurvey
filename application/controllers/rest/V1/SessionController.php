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
use LimeSurvey\Api\Command\V1\SessionKeyCreate;
use LimeSurvey\Api\Command\V1\SessionKeyRelease;

/**
 * Session Controller
 */
class SessionController extends LSYii_ControllerRest
{
    /**
     * Handle post request
     *
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
     *          description="Unauthorized"
     *      )
     * )
     *
     * @return void
     */
    public function actionIndexPost()
    {
        $request = Yii::app()->request;
        $requestData = [
            'username' => $request->getPost('username'),
            'password' => $request->getPost('password')
        ];
        $commandResponse = (new SessionKeyCreate())
            ->run(new Request($requestData));

        $this->renderCommandResponse($commandResponse);
    }

    /**
     * Handle delete request
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
     * @return void
     */
    public function actionIndexDelete()
    {
        $requestData = [
            'sessionKey' => $this->getAuthToken()
        ];
        $commandResponse = (new SessionKeyRelease())->run(
            new Request($requestData)
        );

        $this->renderCommandResponse($commandResponse);
    }
}
