<?php

namespace LimeSurvey\Menu;

class Menu implements MenuInterface
{
    protected $isDropDown = false;
    protected $label = "Missing label";
    protected $href = "#";
    protected $menuItems = array();
    protected $iconClass = "";

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
    }

    public function isDropDown()
    {
        return $this->isDropDown; 
    }
    
    public function getLabel()
    {
        return $this->label; 
    }
    
    public function getHref()
    {
        return $this->href; 
    }
    
    public function getMenuItems()
    {
        return $this->menuItems; 
    }
    
    public function getIconClass()
    {
        return $this->iconClass; 
    }
}
