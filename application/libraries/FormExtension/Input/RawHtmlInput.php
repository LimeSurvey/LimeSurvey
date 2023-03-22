<?php

namespace LimeSurvey\Libraries\FormExtension\Input;

class RawHtmlInput extends BaseInput
{
    private $html;

    public function setHtml($html)
    {
        $this->html = $html;
    }

    public function getHtml(): string
    {
        return $this->html;
    }
}
