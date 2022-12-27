<?php

namespace LimeSurvey\Api\Rest;

use LimeSurvey\Api\Command\Response\Response;
use Exception;

interface RestRendererInterface
{
    public function returnResponse(Response $response);
    public function returnException(Exception $exception);
}
