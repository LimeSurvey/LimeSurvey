<?php

namespace LimeSurvey\Menu;

class Menu implements MenuInterface
{
    /**
     * If true, render this menu as a dropdown.
     * @var boolean
     */
    protected $isDropDown = false;

    /**
     * @var string
     */
    protected $label = "Missing label";

    /**
     * @var string
     */
    protected $href = "#";

    /**
     * @var MenuItem[]
     */
    protected $menuItems = [];

    /**
     * Font-awesome icon class.
     * @var string
     */
    protected $iconClass = "";

    /**
     * @var string
     */
    protected $onClick = "";

    /**
     * @var string
     */
    protected $tooltip = "";

    /**
     * @param array $options - Options for either dropdown menu or plain link
     * @return void
     */
    public function __construct($options)
    {
        if (isset($options['isDropDown'])) {
            $this->isDropDown = $options['isDropDown'];
        }

        if (isset($options['label'])) {
            $this->label = $options['label'];
        }

        if (isset($options['href'])) {
            $this->href = $options['href'];
        }

        if (isset($options['menuItems'])) {
            $this->menuItems = $options['menuItems'];
        }

        if (isset($options['iconClass'])) {
            $this->iconClass = $options['iconClass'];
        }

        if (isset($options['onClick'])) {
            $this->onClick = $options['onClick'];
        }

        if (isset($options['tooltip'])) {
            $this->tooltip = $options['tooltip'];
        }
    }

    /**
     * @return boolean
     */
    public function isDropDown()
    {
        return $this->isDropDown;
    }
    
    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
    
    /**
     * @return string
     */
    public function getHref()
    {
        return $this->href;
    }
    
    /**
     * @return MenuItem[]
     */
    public function getMenuItems()
    {
        return $this->menuItems;
    }
    
    /**
     * @return string
     */
    public function getIconClass()
    {
        return $this->iconClass;
    }

    /**
     * @return string
     */
    public function getOnClick()
    {
        return $this->onClick;
    }

    /**
     * @return string
     */
    public function getTooltip()
    {
        return $this->tooltip;
    }
}
