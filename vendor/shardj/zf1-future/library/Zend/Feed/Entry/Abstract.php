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
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @see Zend_Feed
 */
require_once 'Zend/Feed.php';

/**
 * @see Zend_Feed_Element
 */
require_once 'Zend/Feed/Element.php';

/** @see Zend_Xml_Security */
require_once 'Zend/Xml/Security.php';

/**
 * Zend_Feed_Entry_Abstract represents a single entry in an Atom or RSS
 * feed.
 *
 * @category   Zend
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Feed_Entry_Abstract extends Zend_Feed_Element
{
    /**
     * Root XML element for entries. Subclasses must define this to a
     * non-null value.
     *
     * @var string
     */
    protected $_rootElement;

    /**
     * Root namespace for entries. Subclasses may define this to a
     * non-null value.
     *
     * @var string
     */
    protected $_rootNamespace = null;


    /**
     * Zend_Feed_Entry_Abstract constructor
     *
     * The Zend_Feed_Entry_Abstract constructor takes the URI of the feed the entry
     * is part of, and optionally an XML construct (usually a
     * SimpleXMLElement, but it can be an XML string or a DOMNode as
     * well) that contains the contents of the entry.
     *
     * @param  string $uri
     * @param  SimpleXMLElement|DOMNode|string  $element
     * @return void
     * @throws Zend_Feed_Exception
     */
    public function __construct($uri = null, $element = null)
    {
        if (!($element instanceof DOMElement)) {
            if ($element) {
                // Load the feed as an XML DOMDocument object
                $doc = new DOMDocument();
                $doc = @Zend_Xml_Security::scan($element, $doc);

                if (!$doc) {
                    $err = error_get_last();
                    $phpErrormsg = isset($err) ? $err['message'] : null;
                    // prevent the class to generate an undefined variable notice (ZF-2590)
                    if (!isset($phpErrormsg)) {
                        if (function_exists('xdebug_is_enabled')) {
                            $phpErrormsg = '(error message not available, when XDebug is running)';
                        } else {
                            $phpErrormsg = '(error message not available)';
                        }
                    }

                    /**
                     * @see Zend_Feed_Exception
                     */
                    require_once 'Zend/Feed/Exception.php';
                    throw new Zend_Feed_Exception("DOMDocument cannot parse XML: $phpErrormsg");
                }

                $element = $doc->getElementsByTagName($this->_rootElement)->item(0);
                if (!$element) {
                    /**
                     * @see Zend_Feed_Exception
                     */
                    require_once 'Zend/Feed/Exception.php';
                    throw new Zend_Feed_Exception('No root <' . $this->_rootElement . '> element found, cannot parse feed.');
                }
            } else {
                $doc = new DOMDocument('1.0', 'utf-8');
                if ($this->_rootNamespace !== null) {
                    $element = $doc->createElementNS(Zend_Feed::lookupNamespace($this->_rootNamespace), $this->_rootElement);
                } else {
                    $element = $doc->createElement($this->_rootElement);
                }
            }
        }

        parent::__construct($element);
    }

}
