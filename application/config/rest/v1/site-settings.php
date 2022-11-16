<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Site Settings
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$rest = [];
$v1Namespace = '\LimeSurvey\Api\Command\V1\\';

/**
 * @OA\Get(
 *      path="/rest/v1/siteSettings/{id}",
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
 */
$rest['v1/siteSettings/$settingName'] = [
    'GET' => [
        'commandClass' => $v1Namespace . 'SiteSettingsGet',
        'auth' => 'session',
        'params' => [],
        'bodyParams' => []
    ]
];

return $rest;
