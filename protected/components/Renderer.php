<?php

namespace ls\components;

use ls\components\SurveySession;

class Renderer
{

    /**
     * @var SurveySession
     */
    protected $session;

    public function __construct(SurveySession $session)
    {
        $this->session = $session;
    }
}