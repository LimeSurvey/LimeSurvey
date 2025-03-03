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
 * @subpackage Calendar
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata_Entry
 */
require_once 'Zend/Gdata/App/Extension/Link.php';

/**
 * @see Zend_Gdata_Entry
 */
require_once 'Zend/Gdata/Calendar/Extension/WebContent.php';


/**
 * Specialized Link class for use with Calendar. Enables use of gCal extension elements.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Calendar
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Calendar_Extension_Link extends Zend_Gdata_App_Extension_Link
{

    protected $_webContent = null;

    /**
     * Constructs a new Zend_Gdata_Calendar_Extension_Link object.
     * @see Zend_Gdata_App_Extension_Link#__construct
     * @param Zend_Gdata_Calendar_Extension_Webcontent $webContent
     */
    public function __construct($href = null, $rel = null, $type = null,
            $hrefLang = null, $title = null, $length = null, $webContent = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Calendar::$namespaces);
        parent::__construct($href, $rel, $type, $hrefLang, $title, $length);
        $this->_webContent = $webContent;
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
        if ($this->_webContent != null) {
            $element->appendChild($this->_webContent->getDOM($element->ownerDocument));
        }
        return $element;
    }

    /**
     * Creates individual Entry objects of the appropriate type and
     * stores them as members of this entry based upon DOM data.
     *
     * @param DOMNode $child The DOMNode to process
     */
    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('gCal') . ':' . 'webContent':
            $webContent = new Zend_Gdata_Calendar_Extension_WebContent();
            $webContent->transferFromDOM($child);
            $this->_webContent = $webContent;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    /**
     * Get the value for this element's WebContent attribute.
     *
     * @return Zend_Gdata_Calendar_Extension_Webcontent The WebContent value
     */
    public function getWebContent()
    {
        return $this->_webContent;
    }

    /**
     * Set the value for this element's WebContent attribute.
     *
     * @param Zend_Gdata_Calendar_Extension_WebContent $value The desired value for this attribute.
     * @return $this
     */
    public function setWebContent($value)
    {
        $this->_webContent = $value;
        return $this;
    }


}

