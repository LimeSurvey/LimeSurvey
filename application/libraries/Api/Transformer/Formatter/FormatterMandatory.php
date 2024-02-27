<?php

namespace LimeSurvey\Api\Transformer\Formatter;

/**
 * This class is extending the FormatterYnToBool class in revert mode
 * to be able to translate null value to 'S'.
 * It is only needed for prop of type "mandatory"
 */
class FormatterMandatory extends FormatterYnToBool
{
    private string $name = 'mandatory';

    /**
     * @param bool $revert
     */
    public function __construct($revert = false)
    {
        parent::__construct(!$revert);
        parent::setName($this->name);
    }

    /**
     * @param ?mixed $value
     * @param array $config
     * @param array $options
     * @return ?mixed
     */
    public function format($value, $config, $options = [])
    {
        $this->setClassBasedOnConfig($config);
        if ($this->active) {
            return $this->revert
                ? $this->revert($value)
                : $this->apply($value);
        } else {
            return $value;
        }
    }

    /**
     * if parent revert function returns null, 'S' is returned
     *
     * @param ?mixed $value
     * @return ?mixed
     */
    protected function revert($value)
    {
        $string = parent::revert($value);
        return $string === null ? 'S' : $string;
    }

    /**
     * Checks config for this specific formatter,
     * if so it could adjust class properties based on the config.
     * Returns true if this formatter is configured, false otherwise.
     * @param array $config
     * @return void
     */
    public function setClassBasedOnConfig($config)
    {
        $this->resetClassVariables();
        if (isset($config['formatter'][$this->name])) {
            $this->active = true;
        }
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return void
     */
    private function resetClassVariables()
    {
        $this->active = false;
    }
}
