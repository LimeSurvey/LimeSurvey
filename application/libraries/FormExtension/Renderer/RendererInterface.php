<?php

namespace LimeSurvey\Libraries\FormExtension\Renderer;

use LimeSurvey\Libraries\FormExtension\Input\InputInterface;

interface RendererInterface
{
    public function render(InputInterface $input): string;
}
