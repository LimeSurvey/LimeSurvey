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
        $output = '<' . $input->getHtmlTag() . ' ' . $this->renderAttributes($input);
        if ($input->getContent()) {
            $output .= '>' . $input->getContent() . '</' . $input->getHtmlTag() . '>';
        } else {
            $output .= '/>';
        }
        return $output;
    }

    protected function renderAttributes(InputInterface $input)
    {
        $attributes = $input->getAttributes();
        $flags = ['disabled', 'checked'];

        $attributeParts = array_map(function ($key) use ($attributes, $flags) {
            $value = $attributes[$key];
            if (empty($value)) {
                return null;
            }
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            return in_array($key, $flags)
                ? htmlspecialchars($key)
                : htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"'
                ;
        }, array_keys($attributes ));

        return implode(' ', $attributeParts);
    }
}
