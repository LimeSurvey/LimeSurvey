<?php

namespace LimeSurvey\Libraries\FormExtension;

use LimeSurvey\Libraries\FormExtension\Renderer\RendererInterface;

class FormExtensionWidget
{
    /**
     * @param Inputs\InputInterface[] $inputs
     * @param RendererInterface $renderer
     */
    public static function render(array $inputs, RendererInterface $renderer): string
    {
        return array_reduce(
            $inputs,
            function ($html, $i) use ($renderer) {
                return $html . $renderer->render($i);
            },
            ''
        );
    }
}
