<?php

namespace LimeSurvey\Menu;

class MenuButton extends Menu implements MenuButtonInterface
{

    /**
     * @var string
     */
    protected $buttonId;

    /**
     * @var string
     */
    protected $buttonClass = "btn btn-primary";

    /**
     * @var bool
     */
    protected $openInNewTab = false;

    /**
     * @param array $options - Options for either dropdown menu or plain link
     * @return void
     */
    public function __construct($options)
    {
        parent::__construct($options);

        $this->isDropDown = false;

        if (isset($options['buttonId'])) {
            $this->buttonId = $options['buttonId'];
        }
        if (isset($options['buttonClass'])) {
            $this->buttonClass = $options['buttonClass'];
        }
        if (isset($options['openInNewTab'])) {
            $this->openInNewTab = $options['openInNewTab'];
        }
    }

    /**
     * Returns the label string.
     * if a iconClass is set it will be put before the label
     * @return string
     */
    public function getLabel()
    {
        $label = $this->label;
        if ($this->iconClass !== '') {
            $label = "<i class='" . $this->iconClass . "'></i>&nbsp;" . $this->label;
        }

        return $label;
    }

    public function getButtonId()
    {
        return $this->buttonId;
    }

    /**
     * @return string
     */
    public function getButtonClass()
    {
        return $this->buttonClass;
    }

    /**
     * @return bool
     */
    public function getOpenInNewTab()
    {
        return $this->openInNewTab;
    }

    /**
     * @return bool
     */
    public function isButton()
    {
        return true;
    }
}
