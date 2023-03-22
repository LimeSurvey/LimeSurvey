<?php

namespace LimeSurvey\Libraries\FormExtension\Renderer;

use LimeSurvey\Libraries\FormExtension\Input\InputInterface;

class BaseInputRenderer implements RendererInterface
{
    /**
     * @param InputInterface $input
     */
    public function render(InputInterface $input): string
    {
        return '<input ' . $this->renderAttributes($input) . ' />';
    }

    public function renderAttributes(InputInterface $input)
    {
        $flags = ['disabled', 'checked'];

        $attributeParts = array_map(function ($key, $value) use ($flags) {
            return in_array($key, $flags)
                ? htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"'
                : htmlspecialchars($key)
                ;
        }, $input->getAttributes());

        return implode(' ', $attributeParts);
    }
}
