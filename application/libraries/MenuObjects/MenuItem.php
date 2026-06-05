<?php

namespace LimeSurvey\Menu;

use LimeSurvey\Libraries\MenuObjects\ExtendedMenuItemInterface;

class MenuItem implements ExtendedMenuItemInterface
{
    /** @var boolean */
    protected $isDivider = false;
    /** @var boolean */
    protected $isSmallText = false;
    /** @var string */
    protected $href = "#";
    /** @var string */
    protected $label = "Missing label";
    /** @var string */
    protected $iconClass = "";
    /** @var string */
    protected $id = null;
    /** @var string */
    protected $itemClass = "";

    //make it possible to open a modal via the item click

    protected $isModal = false;
    protected $modalId = null;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        if (isset($options['isDivider'])) {
            $this->isDivider = $options['isDivider'];
        }

        if (isset($options['isSmallText'])) {
            $this->isSmallText = $options['isSmallText'];
        }

        if (isset($options['label'])) {
            $this->label = $options['label'];
        }

        if (isset($options['href'])) {
            $this->href = $options['href'];
        }

        if (isset($options['iconClass'])) {
            $this->iconClass = $options['iconClass'];
        }

        if (isset($options['id'])) {
            $this->id = $options['id'];
        }

        if (isset($options['isModal'])) {
            $this->isModal = $options['isModal'];
        }

        if (isset($options['modalId'])) {
            $this->modalId = $options['modalId'];
        }

        if (isset($options['itemClass'])) {
            $this->itemClass = $options['itemClass'];
        }
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
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
    public function getIconClass()
    {
        return $this->iconClass;
    }

    public function getModalId(){
        return $this->modalId;
    }

    /**
     * @return boolean
     */
    public function isDivider()
    {
        return $this->isDivider;
    }

    /**
     * @return boolean
     */
    public function isSmallText()
    {
        return $this->isSmallText;
    }

    public function isModal()
    {
        return $this->isModal;
    }

    public function getItemClass(){
        return $this->itemClass;
    }

    /**
     * Used by array_unique
     *
     * @return string
     */
    public function __toString()
    {
        return $this->href;
    }
}
