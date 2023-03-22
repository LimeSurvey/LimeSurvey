<?php

namespace LimeSurvey\Libraries\FormExtension\Input;

use LimeSurvey\Libraries\FormExtension\Renderer\RendererInterface;

interface InputInterface
{
    public function getName();
    public function getValue();
    public function getHelp();
    public function getLabel();
    public function setAttributes($attributes);
    public function setAttribute($key, $value);
    public function getAttributes();
    public function setRenderer(RendererInterface $renderer);
    public function render(): string;
}
