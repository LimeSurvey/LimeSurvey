<?php

namespace LimeSurvey\Api\Command;

use LimeSurvey\Api\Command\{
    Request\Request,
    Response\Response
};

interface CommandInterface
{
    /**
     * @param Request $request
     * @return Response
     */
    public function run(Request $request);
}
