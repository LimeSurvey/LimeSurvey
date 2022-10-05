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
use LimeSurvey\Api\Command\V1\SiteSettingsGet;

/**
 * Site Settings Controller
 *
 *
 * @OA\Get(
 *      path="/rest/v1/siteSettings",
 *      security={{"bearerAuth":{}}},
 *      summary="Get site settings",
 *      description="Get site settings",
 *      tags={"Site Settings"},
 *      @OA\Parameter(
 *          description="Setting id",
 *          in="path",
 *          name="id",
 *          required=true,
 *          @OA\Schema(type="string")
 *     ),
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
 *      )
 * )
 *
 */
class SiteSettingsController extends LSYii_ControllerRest
{
    /**
     *  Handle get request
     *
     * @param string $id
     * @return void
     */
    public function actionIndexGet(string $id)
    {
        $requestData = array(
            'sessionKey' => $this->getAuthToken(),
            'settingName' => $id
        );
        $commandRequest = new Request($requestData);
        $commandResponse = (new SiteSettingsGet())
            ->run($commandRequest);

        $this->renderCommandResponse($commandResponse);
    }
}
