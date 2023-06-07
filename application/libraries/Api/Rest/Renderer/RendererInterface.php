<?php

namespace LimeSurvey\Api\Rest\Renderer;

use LimeSurvey\Api\Command\Response\Response;
use Exception;

interface RendererInterface
{
    /**
     * @return void
     */
    public function returnResponse(Response $response);

    /**
     * @return void
     */
    public function returnException(Exception $exception);
}
