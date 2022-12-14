<?php

namespace LimeSurvey\Api\Rest\Renderer;

use LimeSurvey\Api\Command\Response\Response;
use Exception;

interface RestRendererInterface
{
    public function returnResponse(Response $response);
    public function returnException(Exception $exception);
}
