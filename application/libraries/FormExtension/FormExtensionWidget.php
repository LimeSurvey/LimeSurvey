<?php

namespace LimeSurvey\Libraries\FormExtension;

class FormExtensionWidget
{
    /**
     * @param Inputs\InputInterface[] $inputs
     * @param Inputs\DefaultBaseRenderer $renderer
     */
    public static function render(array $inputs, $renderer): string
    {
        return array_reduce(
            $inputs,
            function ($html, $i) use ($renderer) {
                return $html . $renderer->run($i);
            },
            ''
        );
    }
}
