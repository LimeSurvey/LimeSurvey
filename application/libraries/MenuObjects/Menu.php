<?php

namespace LimeSurvey\Menu;

class Menu implements ExtendedMenuInterface
{
    /**
     * @var string
     */
    protected $id = "";

    /**
     * If true, render this menu as a dropdown.
     * @var bool
     */
    protected $isDropDown = false;

    /**
     * If true, render this menu as a dropdown button.
     * @var bool
     */
    protected $isDropDownButton = false;

    /**
     * @var string
     */
    protected $dropDownButtonClass = "btn btn-primary";

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
     * Added option because we split the menu into two sections for LS6
     * If true, render this menu in the middle section. False would render it in the right section
     * @var bool
     */
    protected $isInMiddleSection = true;

    /**
     * If true, render this menu before the main menu.
     * @var bool
     */
    protected $isPrepended = false;

    /**
     * @param array $options - Options for either dropdown menu or plain link
     * @return void
     */
    public function __construct($options)
    {
        if (isset($options['id'])) {
            $this->id = $options['id'];
        }

        if (isset($options['isDropDown'])) {
            $this->isDropDown = $options['isDropDown'];
        }

        if (isset($options['isDropDownButton'])) {
            $this->isDropDownButton = $options['isDropDownButton'];
        }

        if (isset($options['dropDownButtonClass'])) {
            $this->dropDownButtonClass = $options['dropDownButtonClass'];
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

        if (isset($options['isInMiddleSection'])) {
            $this->isInMiddleSection = $options['isInMiddleSection'];
        }

        if (isset($options['isPrepended'])) {
            $this->isPrepended = $options['isPrepended'];
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isDropDown()
    {
        return $this->isDropDown;
    }

    /**
     * @return bool
     */
    public function isDropDownButton()
    {
        return $this->isDropDownButton;
    }

    /**
     * @return string
     */
    public function getDropDownButtonClass()
    {
        return $this->dropDownButtonClass;
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

    /**
     * @return bool
     */
    public function isInMiddleSection()
    {
        return $this->isInMiddleSection;
    }

    /**
     * @return bool
     */
    public function isPrepended()
    {
        return $this->isPrepended;
    }

    /**
     * @return bool
     */
    public function isButton()
    {
        return false;
    }
}
