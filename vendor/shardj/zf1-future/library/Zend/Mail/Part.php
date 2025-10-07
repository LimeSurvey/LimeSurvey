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
 * @package    Zend_Mail
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @see Zend_Mime_Decode
 */
require_once 'Zend/Mime/Decode.php';

/**
 * @see Zend_Mail_Header_HeaderName
 */
require_once 'Zend/Mail/Header/HeaderName.php';

/**
 * @see Zend_Mail_Header_HeaderValue
 */
require_once 'Zend/Mail/Header/HeaderValue.php';

/**
 * @see Zend_Mail_Part_Interface
 */
require_once 'Zend/Mail/Part/Interface.php';


/**
 * @category   Zend
 * @package    Zend_Mail
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Mail_Part implements RecursiveIterator, Zend_Mail_Part_Interface
{
    /**
     * headers of part as array
     * @var null|array
     */
    protected $_headers;

    /**
     * raw part body
     * @var null|string
     */
    protected $_content;

    /**
     * toplines as fetched with headers
     * @var string
     */
    protected $_topLines = '';

    /**
     * parts of multipart message
     * @var array
     */
    protected $_parts = [];

    /**
     * count of parts of a multipart message
     * @var null|int
     */
    protected $_countParts;

    /**
     * current position of iterator
     * @var int
     */
    protected $_iterationPos = 1;

    /**
     * mail handler, if late fetch is active
     * @var null|Zend_Mail_Storage_Abstract
     */
    protected $_mail;

    /**
     * message number for mail handler
     * @var int
     */
    protected $_messageNum = 0;

    /**
     * Class to use when creating message parts
     * @var string
     */
    protected $_partClass;

    /**
     * Public constructor
     *
     * Zend_Mail_Part supports different sources for content. The possible params are:
     * - handler    a instance of Zend_Mail_Storage_Abstract for late fetch
     * - id         number of message for handler
     * - raw        raw content with header and body as string
     * - headers    headers as array (name => value) or string, if a content part is found it's used as toplines
     * - noToplines ignore content found after headers in param 'headers'
     * - content    content as string
     *
     * @param   array $params  full message with or without headers
     * @throws  Zend_Mail_Exception
     */
    public function __construct(array $params)
    {
        if (isset($params['handler'])) {
            if (!$params['handler'] instanceof Zend_Mail_Storage_Abstract) {
                /**
                 * @see Zend_Mail_Exception
                 */
                require_once 'Zend/Mail/Exception.php';
                throw new Zend_Mail_Exception('handler is not a valid mail handler');
            }
            if (!isset($params['id'])) {
                /**
                 * @see Zend_Mail_Exception
                 */
                require_once 'Zend/Mail/Exception.php';
                throw new Zend_Mail_Exception('need a message id with a handler');
            }

            $this->_mail       = $params['handler'];
            $this->_messageNum = $params['id'];
        }

        if (isset($params['partclass'])) {
            $this->setPartClass($params['partclass']);
        }

        if (isset($params['raw'])) {
            Zend_Mime_Decode::splitMessage($params['raw'], $this->_headers, $this->_content, "\r\n");
        } else if (isset($params['headers'])) {
            if (is_array($params['headers'])) {
                $this->_headers = $params['headers'];
                $this->_validateHeaders($this->_headers);
            } else {
                if (!empty($params['noToplines'])) {
                    Zend_Mime_Decode::splitMessage($params['headers'], $this->_headers, $null, "\r\n");
                } else {
                    Zend_Mime_Decode::splitMessage($params['headers'], $this->_headers, $this->_topLines, "\r\n");
                }
            }

            if (isset($params['content'])) {
                $this->_content = $params['content'];
            }
        }
    }

    /**
     * Set name pf class used to encapsulate message parts
     * @param string $class
     * @return Zend_Mail_Part
     */
    public function setPartClass($class)
    {
        if ( !class_exists($class) ) {
            /**
             * @see Zend_Mail_Exception
             */
            require_once 'Zend/Mail/Exception.php';
            throw new Zend_Mail_Exception("Class '{$class}' does not exist");
        }
        if ( !is_subclass_of($class, 'Zend_Mail_Part_Interface') ) {
            /**
             * @see Zend_Mail_Exception
             */
            require_once 'Zend/Mail/Exception.php';
            throw new Zend_Mail_Exception("Class '{$class}' must implement Zend_Mail_Part_Interface");
        }

        $this->_partClass = $class;
        return $this;
    }

    /**
     * Retrieve the class name used to encapsulate message parts
     * @return string
     */
    public function getPartClass()
    {
        if ( !$this->_partClass ) {
            $this->_partClass = __CLASS__;
        }
        return $this->_partClass;
    }

    /**
     * Check if part is a multipart message
     *
     * @return bool if part is multipart
     */
    public function isMultipart()
    {
        try {
            return stripos($this->contentType, 'multipart/') === 0;
        } catch(Zend_Mail_Exception $e) {
            return false;
        }
    }


    /**
     * Body of part
     *
     * If part is multipart the raw content of this part with all sub parts is returned
     *
     * @return string body
     * @throws Zend_Mail_Exception
     */
    public function getContent()
    {
        if ($this->_content !== null) {
            return $this->_content;
        }

        if ($this->_mail) {
            return $this->_mail->getRawContent($this->_messageNum);
        } else {
            /**
             * @see Zend_Mail_Exception
             */
            require_once 'Zend/Mail/Exception.php';
            throw new Zend_Mail_Exception('no content');
        }
    }

    /**
     * Return size of part
     *
     * Quite simple implemented currently (not decoding). Handle with care.
     *
     * @return int size
     */
    public function getSize() {
        return strlen($this->getContent());
    }


    /**
     * Cache content and split in parts if multipart
     *
     * @return null
     * @throws Zend_Mail_Exception
     */
    protected function _cacheContent()
    {
        // caching content if we can't fetch parts
        if ($this->_content === null && $this->_mail) {
            $this->_content = $this->_mail->getRawContent($this->_messageNum);
        }

        if (!$this->isMultipart()) {
            return;
        }

        // split content in parts
        $boundary = $this->getHeaderField('content-type', 'boundary');
        if (!$boundary) {
            /**
             * @see Zend_Mail_Exception
             */
            require_once 'Zend/Mail/Exception.php';
            throw new Zend_Mail_Exception('no boundary found in content type to split message');
        }
        $parts = Zend_Mime_Decode::splitMessageStruct($this->_content, $boundary);
        if ($parts === null) {
            return;
        }
        $partClass = $this->getPartClass();
        $counter = 1;
        foreach ($parts as $part) {
            $this->_parts[$counter++] = new $partClass(['headers' => $part['header'], 'content' => $part['body']]);
        }
    }

    /**
     * Get part of multipart message
     *
     * @param  int $num number of part starting with 1 for first part
     * @return Zend_Mail_Part wanted part
     * @throws Zend_Mail_Exception
     */
    public function getPart($num)
    {
        if (isset($this->_parts[$num])) {
            return $this->_parts[$num];
        }

        if (!$this->_mail && $this->_content === null) {
            /**
             * @see Zend_Mail_Exception
             */
            require_once 'Zend/Mail/Exception.php';
            throw new Zend_Mail_Exception('part not found');
        }

        if ($this->_mail && $this->_mail->hasFetchPart) {
            // TODO: fetch part
            // return
        }

        $this->_cacheContent();

        if (!isset($this->_parts[$num])) {
            /**
             * @see Zend_Mail_Exception
             */
            require_once 'Zend/Mail/Exception.php';
            throw new Zend_Mail_Exception('part not found');
        }

        return $this->_parts[$num];
    }

    /**
     * Count parts of a multipart part
     *
     * @return int number of sub-parts
     */
    public function countParts()
    {
        if ($this->_countParts) {
            return $this->_countParts;
        }

        $this->_countParts = count($this->_parts);
        if ($this->_countParts) {
            return $this->_countParts;
        }

        if ($this->_mail && $this->_mail->hasFetchPart) {
            // TODO: fetch part
            // return
        }

        $this->_cacheContent();

        $this->_countParts = count($this->_parts);
        return $this->_countParts;
    }


    /**
     * Get all headers
     *
     * The returned headers are as saved internally. All names are lowercased. The value is a string or an array
     * if a header with the same name occurs more than once.
     *
     * @return array headers as array(name => value)
     */
    public function getHeaders()
    {
        if ($this->_headers === null) {
            if (!$this->_mail) {
                $this->_headers = [];
            } else {
                $part = $this->_mail->getRawHeader($this->_messageNum);
                Zend_Mime_Decode::splitMessage($part, $this->_headers, $null);
            }
        }

        return $this->_headers;
    }

    /**
     * Get a header in specificed format
     *
     * Internally headers that occur more than once are saved as array, all other as string. If $format
     * is set to string implode is used to concat the values (with Zend_Mime::LINEEND as delim).
     *
     * @param  string $name   name of header, matches case-insensitive, but camel-case is replaced with dashes
     * @param  string $format change type of return value to 'string' or 'array'
     * @return string|array value of header in wanted or internal format
     * @throws Zend_Mail_Exception
     */
    public function getHeader($name, $format = null)
    {
        if ($this->_headers === null) {
            $this->getHeaders();
        }

        $lowerName = strtolower($name);

        if ($this->headerExists($name) == false) {
            $lowerName = strtolower(preg_replace('%([a-z])([A-Z])%', '\1-\2', $name));
            if($this->headerExists($lowerName) == false) {
                /**
                 * @see Zend_Mail_Exception
                 */
                require_once 'Zend/Mail/Exception.php';
                throw new Zend_Mail_Exception("no Header with Name $name or $lowerName found");
            }
        }
        $name = $lowerName;

        $header = $this->_headers[$name];

        switch ($format) {
            case 'string':
                if (is_array($header)) {
                    $header = implode(Zend_Mime::LINEEND, $header);
                }
                break;
            case 'array':
                $header = (array)$header;
            default:
                // do nothing
        }

        return $header;
    }

    /**
     * Check wheater the Mail part has a specific header.
     *
     * @param  string $name
     * @return boolean
     */
    public function headerExists($name)
    {
        $name = strtolower($name);
        if(isset($this->_headers[$name])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get a specific field from a header like content type or all fields as array
     *
     * If the header occurs more than once, only the value from the first header
     * is returned.
     *
     * Throws a Zend_Mail_Exception if the requested header does not exist. If
     * the specific header field does not exist, returns null.
     *
     * @param  string $name       name of header, like in getHeader()
     * @param  string $wantedPart the wanted part, default is first, if null an array with all parts is returned
     * @param  string $firstName  key name for the first part
     * @return string|array wanted part or all parts as array($firstName => firstPart, partname => value)
     * @throws Zend_Exception, Zend_Mail_Exception
     */
    public function getHeaderField($name, $wantedPart = 0, $firstName = 0) {
        return Zend_Mime_Decode::splitHeaderField(current($this->getHeader($name, 'array')), $wantedPart, $firstName);
    }


    /**
     * Getter for mail headers - name is matched in lowercase
     *
     * This getter is short for Zend_Mail_Part::getHeader($name, 'string')
     *
     * @see Zend_Mail_Part::getHeader()
     *
     * @param  string $name header name
     * @return string value of header
     * @throws Zend_Mail_Exception
     */
    public function __get($name)
    {
        return $this->getHeader($name, 'string');
    }

    /**
     * Isset magic method proxy to hasHeader
     *
     * This method is short syntax for Zend_Mail_Part::hasHeader($name);
     *
     * @param  string $name
     * @return bool
     * @see Zend_Mail_Part::hasHeader
     */
    public function __isset($name)
    {
        return $this->headerExists($name);
    }

    /**
     * magic method to get content of part
     *
     * @return string content
     */
    public function __toString()
    {
        return $this->getContent();
    }

    /**
     * implements RecursiveIterator::hasChildren()
     *
     * @return bool current element has children/is multipart
     */
    public function hasChildren(): bool
    {
        $current = $this->current();
        return $current && $current instanceof Zend_Mail_Part && $current->isMultipart();
    }

    /**
     * implements RecursiveIterator::getChildren()
     *
     * @return Zend_Mail_Part same as self::current()
     */
    public function getChildren(): ?\RecursiveIterator
    {
        return $this->current();
    }

    /**
     * implements Iterator::valid()
     *
     * @return bool check if there's a current element
     */
    public function valid(): bool
    {
        if ($this->_countParts === null) {
            $this->countParts();
        }
        return $this->_iterationPos && $this->_iterationPos <= $this->_countParts;
    }

    /**
     * implements Iterator::next()
     *
     * @return null
     */
    public function next(): void
    {
        ++$this->_iterationPos;
    }

    /**
     * implements Iterator::key()
     *
     * @return int key/number of current part
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->_iterationPos;
    }

    /**
     * implements Iterator::current()
     *
     * @return Zend_Mail_Part current part
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->getPart($this->_iterationPos);
    }

    /**
     * implements Iterator::rewind()
     *
     * @return null
     */
    public function rewind(): void
    {
        $this->countParts();
        $this->_iterationPos = 1;
    }

    /**
     * Ensure headers do not contain invalid characters
     *
     * @param array $headers
     * @param bool $assertNames
     */
    protected function _validateHeaders(array $headers, $assertNames = true)
    {
        foreach ($headers as $name => $value) {
            if ($assertNames) {
                Zend_Mail_Header_HeaderName::assertValid($name);
            }

            if (is_array($value)) {
                $this->_validateHeaders($value, false);
                continue;
            }

            Zend_Mail_Header_HeaderValue::assertValid($value);
        }
    }
}
