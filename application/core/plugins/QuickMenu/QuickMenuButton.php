<?php

/**
 * Local exception
 */
class QuickMenuException extends CException {}

/**
 * Small class for buttons. Basically just an
 * array wrapper with some default values.
 *
 * Implements ArrayAccess so core code can
 * use it as an array.
 *
 * @todo Put this in core?
 */
class QuickMenuButton implements ArrayAccess {
    /**
     * @var string - href in anchor
     */
    public $href;

    /**
     * @var string - String in tooltip. Empty string means no tooltip
     */
    public $tooltip = '';

    /**
     * @var string - Class with glyphicon
     */
    public $iconClass;

    /**
     * @var string - Button name
     */
    public $name;

    /**
     * @var int - Order sorting
     */
    public $order = 0;

    /**
     * @var bool - Whether or not to open link in new tab
     */
    public $openInNewTab = false;

    /**
     * @var bool - Whether or not to show button only when survey is active
     */
    public $showOnlyWhenSurveyIsActivated = false;

    /**
     * @var bool - Whether or not to show button only when survey is non-active
     */
    public $showOnlyWhenSurveyIsDeactivated = false;

    /**
     * @var array<string> - Tuple with permission category and right, e.g. array("survey", "read")
     * Null means available for all.
     */
    public $neededPermission = null;

    /**
     * $options is an array of settings for the button
     *
     * @param array<string, mixed> $options
     */
    public function __construct($options) {
        $this->href = $options['href'];
        $this->tooltip = $options['tooltip'];
        $this->iconClass = $options['iconClass'];
        $this->name = $options['name'];

        if (isset($options['openInNewTab'])) {
            $this->openInNewTab = $options['openInNewTab'];
        }

        if (isset($options['showOnlyWhenSurveyIsActivated']))
        {
            $this->showOnlyWhenSurveyIsActivated = $options['showOnlyWhenSurveyIsActivated'];
        }

        if (isset($options['showOnlyWhenSurveyIsDeactivated']))
        {
            $this->showOnlyWhenSurveyIsDeactivated = $options['showOnlyWhenSurveyIsDeactivated'];
        }

        if (isset($options['neededPermission']))
        {
          if (count($options['neededPermission']) !== 2)
          {
              throw new InvalidArgumentException("Option neededPermission must have length 2 (permission category and right)");
          }

          $this->neededPermission = $options['neededPermission'];
        }
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function offsetExists($offset)
    {
        throw new QuickMenuException("Can't check if offset exists for QuickMenuButton");
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {
        throw new QuickMenuException("Can't set offset for QuickMenuButton");
    }

    public function offsetUnset($offset)
    {
        throw new QuickMenuException("Can't unset offset for QuickMenuButton");
    }
}

