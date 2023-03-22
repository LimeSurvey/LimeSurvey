<?php

namespace LimeSurvey\Libraries\FormExtension\Input;

use LimeSurvey\Libraries\FormExtension\Renderer\BaseInputRenderer;
use LimeSurvey\Libraries\FormExtension\Renderer\RendererInterface;

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

    /** @var string */
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

    /** @var bool */
    private $checked = false;

    /** @var array */
    private $attributes = [];

    /** @var RendererInterface */
    private $renderer = null;

    public function __construct(array $options)
    {
        if (empty($options['name'])) {
            throw new InvalidArgumentException("Input is missing mandatory name option");
        }

        $this->id = $options['id'] ?? null;
        $this->name = $options['name'] ?? null;
        $this->label = $options['label'] ?? null;
        $this->help = $options['help'] ?? null;
        $this->tooltip = $options['tooltip'] ?? null;
        $this->disabled = $options['disabled'] ?? false;
        $this->checked = $options['checked'] ?? false;
        $this->saveFunction = $options['save'] ?? null;
        $this->loadFunction = $options['load'] ?? null;
        $this->conditionFunction = $options['condition'] ?? null;

        $attributesDefault = [
            'id' => $options['id'] ?? '',
            'name' => $options['name'] ?? '',
            'disabled' => !empty($options['disabled']),
            'title' => $options['tooltip'] ?? '',
        ];
        $this->attributes = array_merge(
            $attributesDefault,
            $options['attributes'] ?? []
        );

        $this->setRenderer(new BaseInputRenderer);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
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

    public function getAttributes(): array
    {
        if (!isset($this->attributes['value'])) {
            $this->attributes['value'] = $this->getValue();
        }
        if (!isset($this->attributes['title'])) {
            $this->attributes['data-toggle'] = 'tooltip';
        }
        return $this->attributes;
    }

    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function setRenderer(RendererInterface $renderer)
    {
        return $this->renderer = $renderer;
    }

    public function render(): string
    {
        return $this->renderer->render($this);
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
