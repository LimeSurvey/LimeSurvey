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
 * @package    Zend_Gdata
 * @subpackage Media
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata_App_Extension
 */
require_once 'Zend/Gdata/App/Extension.php';

/**
 * Represents the media:hash element
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Media
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Media_Extension_MediaHash extends Zend_Gdata_Extension
{

    protected $_rootElement = 'hash';
    protected $_rootNamespace = 'media';
    protected $_algo = null;

    /**
     * Constructs a new MediaHash element
     *
     * @param string $text
     * @param string $algo
     */
    public function __construct($text = null, $algo = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Media::$namespaces);
        parent::__construct();
        $this->_text = $text;
        $this->_algo = $algo;
    }

    /**
     * Retrieves a DOMElement which corresponds to this element and all
     * child properties.  This is used to build an entry back into a DOM
     * and eventually XML text for sending to the server upon updates, or
     * for application storage/persistence.
     *
     * @param DOMDocument $doc The DOMDocument used to construct DOMElements
     * @return DOMElement The DOMElement representing this element and all
     * child properties.
     */
    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_algo !== null) {
            $element->setAttribute('algo', $this->_algo);
        }
        return $element;
    }

    /**
     * Given a DOMNode representing an attribute, tries to map the data into
     * instance members.  If no mapping is defined, the name and value are
     * stored in an array.
     *
     * @param DOMNode $attribute The DOMNode attribute needed to be handled
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'algo':
            $this->_algo = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    /**
     * @return string The algo
     */
    public function getAlgo()
    {
        return $this->_algo;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setAlgo($value)
    {
        $this->_algo = $value;
        return $this;
    }

}
