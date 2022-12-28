<?php

namespace LimeSurvey\Api\Rest\Renderer;

use LimeSurvey\Api\Command\Response\Response;
use Exception;

interface RendererInterface
{
    public function returnResponse(Response $response);
    public function returnException(Exception $exception);
}
