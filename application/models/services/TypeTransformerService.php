<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Api\Transformer\Registry\Registry;
use LimeSurvey\Api\Transformer\Formatter\{
    FormatterLongText
};

class TypeTransformerService
{
    protected Registry $registry;

    protected array $mapping;

    protected FormatterLongText $formatterLongText;

    /**
     * Constructor
     *
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry,
        FormatterLongText $formatterLongText
    ) {
        $this->registry = $registry;
        $this->formatterLongText = $formatterLongText;
        $this->mapping = [
            \Question::QT_T_LONG_FREE_TEXT => $this->formatterLongText,
        ];
    }

    /**
     * Converts an input from a source type to a destination type
     *
     * @param string $from the source type
     * @param string $to the destination type
     * @param mixed $input
     * @return mixed
     *
     */
    public function convert(string $from, string $to, $input)
    {
        if ($from === $to) {
            return $input;
        } elseif (isset($this->mapping[$to])) {
            return $this->mapping[$to]->format($input, [], [
                'type' => $from
            ]);
        } else {
            return null;
        }
    }
}
