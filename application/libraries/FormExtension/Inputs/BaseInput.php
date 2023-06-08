<?php

namespace LimeSurvey\Libraries\FormExtension\Inputs;

use CHttpRequest;
use CDbConnection;
use InvalidArgumentException;

class BaseInput implements InputInterface
{
    /** @var ?callable */
    private $saveFunction;

    /** @var ?callable */
    private $loadFunction;

    /** @var ?callable */
    private $conditionFunction;

    /** @var ?string */
    private $id;

    /** @var string */
    private $name;

    /** @var ?string */
    private $label;

    /** @var ?string */
    private $help;

    /** @var ?string */
    private $tooltip;

    /** @var bool */
    private $disabled = false;

    public function __construct(array $options)
    {
        if (empty($options['name'])) {
            throw new InvalidArgumentException("Input is missing mandatory name option");
        }

        $this->name = $options['name'];
        $this->id = $options['id'] ?? null;
        $this->label = $options['label'] ?? null;
        $this->help = $options['help'] ?? null;
        $this->tooltip = $options['tooltip'] ?? null;
        $this->disabled = $options['disabled'] ?? false;
        $this->saveFunction = $options['save'] ?? null;
        $this->loadFunction = $options['load'] ?? null;
        $this->conditionFunction = $options['condition'] ?? null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** @return ?string */
    public function getId()
    {
        return $this->id;
    }

    /** @return ?string */
    public function getLabel()
    {
        return $this->label;
    }

    /** @return ?string */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->load();
    }

    /** @return ?string */
    public function getTooltip()
    {
        return $this->tooltip;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function save(CHttpRequest $request, CDbConnection $connection): bool
    {
        $fn = $this->saveFunction;
        if ($fn) {
            return $fn($request, $connection);
        } else {
            return false;
        }
    }

    /** @return mixed */
    public function load()
    {
        $fn = $this->loadFunction;
        if ($fn) {
            return $fn();
        } else {
            return null;
        }
    }
}
