<?php

namespace LimeSurvey\Menu;

class MenuItem implements MenuItemInterface
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
