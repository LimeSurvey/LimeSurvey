<?php

/**
 * This class contains a fields' details.
 * It replaces some of the arrays with informal structure.
 * @property-read string $name
 */
class ResponseField extends CComponent
{
    protected $_name;
    protected $_numerical = false;

    public function __construct($name) {
        $this->_name = $name;
    }

    /**
     * @return string The name of this field in sgqa format.
     * Example: 13455X123X12other
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * @return string The EM code for this field.
     * Example: q1_other
     */

    public function getCode() {
        return $this->_name;
    }

    public function isNumerical() {
        return $this->_numerical;
    }

    public function getJavascriptName() {
        return "java{$this->_name}";
    }

}