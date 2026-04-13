<?php

namespace LimeSurvey\Libraries\FormExtension\Inputs;

use InvalidArgumentException;

class DefaultBaseRenderer
{
    /**
     * @param RawHtmlInput|BaseInput $input
     */
    public function run($input): string
    {
        switch (true) {
            case $input instanceof RawHtmlInput:
                return $input->getHtml();
            default:
                $c = get_class($input);
                throw new InvalidArgumentException("DefaultBaseRenderer has no support for class $c");
        }
    }

    /**
     * @param ?string $text
     * @return array{0: string, 1: string}
     */
    protected function bakeTooltip($text)
    {
        if ($text) {
            return [sprintf("title='%s'", $text), "data-bs-toggle='tooltip'"];
        } else {
            return ['', ''];
        }
    }
}
