<?php

namespace LimeSurvey\Api\Rest\Renderer;

use LimeSurvey\Api\Command\Response\Response;

interface RestRendererInterface
{
    public function returnResponse(Response $response);
    public function returnException(Response $response);
}
