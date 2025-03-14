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
 * @see Zend_Feed_Element
 */
require_once 'Zend/Feed/Element.php';

/** @see Zend_Xml_Security */
require_once 'Zend/Xml/Security.php';

/**
 * The Zend_Feed_Abstract class is an abstract class representing feeds.
 *
 * Zend_Feed_Abstract implements two core PHP 5 interfaces: ArrayAccess and
 * Iterator. In both cases the collection being treated as an array is
 * considered to be the entry collection, such that iterating over the
 * feed takes you through each of the feed.s entries.
 *
 * @category   Zend
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Feed_Abstract extends Zend_Feed_Element implements Iterator, Countable
{
    /**
     * Current index on the collection of feed entries for the
     * Iterator implementation.
     *
     * @var integer
     */
    protected $_entryIndex = 0;

    /**
     * Cache of feed entries.
     *
     * @var array
     */
    protected $_entries;

    /**
     * Feed constructor
     *
     * The Zend_Feed_Abstract constructor takes the URI of a feed or a
     * feed represented as a string and loads it as XML.
     *
     * @param  string $uri The full URI of the feed to load, or NULL if not retrieved via HTTP or as an array.
     * @param  string $string The feed as a string, or NULL if retrieved via HTTP or as an array.
     * @param  Zend_Feed_Builder_Interface $builder The feed as a builder instance or NULL if retrieved as a string or via HTTP.
     * @return void
     * @throws Zend_Feed_Exception If loading the feed failed.
     */
    public function __construct($uri = null, $string = null, ?Zend_Feed_Builder_Interface $builder = null)
    {
        if ($uri !== null) {
            // Retrieve the feed via HTTP
            $client = Zend_Feed::getHttpClient();
            $client->setUri($uri);
            $response = $client->request('GET');

            if ($response->getStatus() !== 200) {
                /**
                 * @see Zend_Feed_Exception
                 */
                require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('Feed failed to load, got response code ' . $response->getStatus() . '; request: ' . $client->getLastRequest() . "\nresponse: " . $response->asString());
            }

            $this->_element = $this->_importFeedFromString($response->getBody());
            $this->__wakeup();
        } elseif ($string !== null) {
            // Retrieve the feed from $string
            $this->_element = $string;
            $this->__wakeup();
        } else {
            // Generate the feed from the array
            $header = $builder->getHeader();
            $this->_element = new DOMDocument('1.0', $header['charset']);
            $root = $this->_mapFeedHeaders($header);
            $this->_mapFeedEntries($root, $builder->getEntries());
            $this->_element = $root;
            $this->_buildEntryCache();
        }
    }


    /**
     * Load the feed as an XML DOMDocument object
     *
     * @return void
     * @throws Zend_Feed_Exception
     */
    public function __wakeup()
    {
        $doc = new DOMDocument;
        $doc = @Zend_Xml_Security::scan($this->_element, $doc);

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

        $this->_element = $doc;
    }


    /**
     * Prepare for serialiation
     *
     * @return array
     */
    public function __sleep()
    {
        $this->_element = $this->saveXml();

        return ['_element'];
    }


    /**
     * Cache the individual feed elements so they don't need to be
     * searched for on every operation.
     *
     * @return void
     */
    protected function _buildEntryCache()
    {
        $this->_entries = [];
        foreach ($this->_element->childNodes as $child) {
            if ($child->localName == $this->_entryElementName) {
                $this->_entries[] = $child;
            }
        }
    }


    /**
     * Get the number of entries in this feed object.
     *
     * @return integer Entry count.
     */
    public function count(): int
    {
        return count($this->_entries);
    }


    /**
     * Required by the Iterator interface.
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->_entryIndex = 0;
    }


    /**
     * Required by the Iterator interface.
     *
     * @return mixed The current row, or null if no rows.
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return new $this->_entryClassName(
            null,
            $this->_entries[$this->_entryIndex]);
    }


    /**
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->_entryIndex;
    }


    /**
     * @return void
     */
    public function next(): void
    {
        ++$this->_entryIndex;
    }


    /**
     * Required by the Iterator interface.
     *
     * @return boolean Whether the iteration is valid
     */
    public function valid(): bool
    {
        return 0 <= $this->_entryIndex && $this->_entryIndex < $this->count();
    }

    /**
     * Generate the header of the feed when working in write mode
     *
     * @param  array $array the data to use
     * @return DOMElement root node
     */
    abstract protected function _mapFeedHeaders($array);

    /**
     * Generate the entries of the feed when working in write mode
     *
     * @param  DOMElement $root the root node to use
     * @param  array $array the data to use
     * @return DOMElement root node
     */
    abstract protected function _mapFeedEntries(DOMElement $root, $array);

    /**
     * Send feed to a http client with the correct header
     *
     * @throws Zend_Feed_Exception if headers have already been sent
     * @return void
     */
    abstract public function send();

    /**
     * Import a feed from a string
     *
     * Protects against XXE attack vectors.
     *
     * @param  string $feed
     * @return string
     * @throws Zend_Feed_Exception on detection of an XXE vector
     */
    protected function _importFeedFromString($feed)
    {
        if (trim($feed) == '') {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Remote feed being imported'
            . ' is an Empty string or comes from an empty HTTP response');
        }
        $doc = new DOMDocument;
        $doc = Zend_Xml_Security::scan($feed, $doc);

        if (!$doc) {
            // prevent the class to generate an undefined variable notice (ZF-2590)
            // Build error message
            $error = libxml_get_last_error();
            if ($error && $error->message) {
                $errormsg = "DOMDocument cannot parse XML: {$error->message}";
            } else {
                $errormsg = "DOMDocument cannot parse XML";
            }


            /**
             * @see Zend_Feed_Exception
             */
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception($errormsg);
        }

        return $doc->saveXML($doc->documentElement);
    }
}
