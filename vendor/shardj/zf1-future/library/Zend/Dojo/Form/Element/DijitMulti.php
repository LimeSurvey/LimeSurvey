<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Dojo
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Dojo_Form_Element_Dijit */
require_once 'Zend/Dojo/Form/Element/Dijit.php';

/**
 * CheckBox dijit
 *
 * Note: this would be easier with mixins or traits...
 *
 * @uses       Zend_Dojo_Form_Element_Dijit
 * @package    Zend_Dojo
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
abstract class Zend_Dojo_Form_Element_DijitMulti extends Zend_Dojo_Form_Element_Dijit
{
    /**
     * Array of options for multi-item
     * @var array
     */
    public $options = [];

    /**
     * Flag: autoregister inArray validator?
     * @var bool
     */
    protected $_registerInArrayValidator = true;

    /**
     * Separator to use between options; defaults to '<br />'.
     * @var string
     */
    protected $_separator = '<br />';

    /**
     * Which values are translated already?
     * @var array
     */
    protected $_translated = [];

    /**
     * Retrieve separator
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->_separator;
    }

    /**
     * Set separator
     *
     * @param mixed $separator
     * @return self
     */
    public function setSeparator($separator)
    {
        $this->_separator = $separator;
        return $this;
    }

    /**
     * Retrieve options array
     *
     * @return array
     */
    protected function _getMultiOptions()
    {
        if (null === $this->options || !is_array($this->options)) {
            $this->options = [];
        }

        return $this->options;
    }

    /**
     * Add an option
     *
     * @param  string $option
     * @param  string $value
     * @return Zend_Dojo_Form_Element_DijitMulti
     */
    public function addMultiOption($option, $value = '')
    {
        $option  = (string) $option;
        $this->_getMultiOptions();
        if (!$this->_translateOption($option, $value)) {
            $this->options[$option] = $value;
        }

        return $this;
    }

    /**
     * Add many options at once
     *
     * @param  array $options
     * @return Zend_Dojo_Form_Element_DijitMulti
     */
    public function addMultiOptions(array $options)
    {
        foreach ($options as $option => $value) {
            if (is_array($value)
                && array_key_exists('key', $value)
                && array_key_exists('value', $value)
            ) {
                $this->addMultiOption($value['key'], $value['value']);
            } else {
                $this->addMultiOption($option, $value);
            }
        }
        return $this;
    }

    /**
     * Set all options at once (overwrites)
     *
     * @param  array $options
     * @return Zend_Dojo_Form_Element_DijitMulti
     */
    public function setMultiOptions(array $options)
    {
        $this->clearMultiOptions();
        return $this->addMultiOptions($options);
    }

    /**
     * Retrieve single multi option
     *
     * @param  string $option
     * @return mixed
     */
    public function getMultiOption($option)
    {
        $option  = (string) $option;
        $this->_getMultiOptions();
        if (isset($this->options[$option])) {
            $this->_translateOption($option, $this->options[$option]);
            return $this->options[$option];
        }

        return null;
    }

    /**
     * Retrieve options
     *
     * @return array
     */
    public function getMultiOptions()
    {
        $this->_getMultiOptions();
        foreach ($this->options as $option => $value) {
            $this->_translateOption($option, $value);
        }
        return $this->options;
    }

    /**
     * Remove a single multi option
     *
     * @param  string $option
     * @return bool
     */
    public function removeMultiOption($option)
    {
        $option  = (string) $option;
        $this->_getMultiOptions();
        if (isset($this->options[$option])) {
            unset($this->options[$option]);
            if (isset($this->_translated[$option])) {
                unset($this->_translated[$option]);
            }
            return true;
        }

        return false;
    }

    /**
     * Clear all options
     *
     * @return Zend_Dojo_Form_Element_DijitMulti
     */
    public function clearMultiOptions()
    {
        $this->options = [];
        $this->_translated = [];
        return $this;
    }

    /**
     * Set flag indicating whether or not to auto-register inArray validator
     *
     * @param  bool $flag
     * @return Zend_Dojo_Form_Element_DijitMulti
     */
    public function setRegisterInArrayValidator($flag)
    {
        $this->_registerInArrayValidator = (bool) $flag;
        return $this;
    }

    /**
     * Get status of auto-register inArray validator flag
     *
     * @return bool
     */
    public function registerInArrayValidator()
    {
        return $this->_registerInArrayValidator;
    }

    /**
     * Is the value provided valid?
     *
     * Autoregisters InArray validator if necessary.
     *
     * @param  string $value
     * @param  mixed $context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        if ($this->registerInArrayValidator()) {
            if (!$this->getValidator('InArray')) {
                $options = $this->getMultiOptions();
                $this->addValidator(
                    'InArray',
                    true,
                    [array_keys($options)]
                );
            }
        }
        return parent::isValid($value, $context);
    }

    /**
     * Translate an option
     *
     * @param  string $option
     * @param  string $value
     * @return bool
     */
    protected function _translateOption($option, $value)
    {
        if (!isset($this->_translated[$option])) {
            $this->options[$option] = $this->_translateValue($value);
            if ($this->options[$option] === $value) {
                return false;
            }
            $this->_translated[$option] = true;
            return true;
        }

        return false;
    }

    /**
     * Translate a value
     *
     * @param  array|string $value
     * @return array|string
     */
    protected function _translateValue($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->_translateValue($val);
            }
            return $value;
        } else {
            if (null !== ($translator = $this->getTranslator())) {
                return $translator->translate($value);
            }

            return $value;
        }
    }
}
