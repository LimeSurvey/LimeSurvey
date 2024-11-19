<?php

namespace LimeSurvey\Libraries\FormExtension\Inputs;

class RawHtmlInput
{
    private $html;

    public function __construct(string $html)
    {
        $this->html = $html;
    }

    public function getHtml(): string
    {
        return $this->html;
    }
}
