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
 * @package    Zend_Test
 * @subpackage PHPUnit
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** @see Zend_Dom_Query */
require_once 'Zend/Dom/Query.php';

/**
 * Zend_Dom_Query-based PHPUnit Constraint
 *
 * @uses       PHPUnit_Framework_Constraint
 * @category   Zend
 * @package    Zend_Test
 * @subpackage PHPUnit
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Test_PHPUnit_Constraint_DomQuery37 extends PHPUnit_Framework_Constraint
{
    /**#@+
     * Assertion type constants
     */
    public const ASSERT_QUERY            = 'assertQuery';
    public const ASSERT_CONTENT_CONTAINS = 'assertQueryContentContains';
    public const ASSERT_CONTENT_REGEX    = 'assertQueryContentRegex';
    public const ASSERT_CONTENT_COUNT    = 'assertQueryCount';
    public const ASSERT_CONTENT_COUNT_MIN= 'assertQueryCountMin';
    public const ASSERT_CONTENT_COUNT_MAX= 'assertQueryCountMax';
    /**#@-*/

    /**
     * Current assertion type
     * @var string
     */
    protected $_assertType        = null;

    /**
     * Available assertion types
     * @var array
     */
    protected $_assertTypes       = [
        self::ASSERT_QUERY,
        self::ASSERT_CONTENT_CONTAINS,
        self::ASSERT_CONTENT_REGEX,
        self::ASSERT_CONTENT_COUNT,
        self::ASSERT_CONTENT_COUNT_MIN,
        self::ASSERT_CONTENT_COUNT_MAX,
    ];

    /**
     * Content being matched
     * @var string
     */
    protected $_content           = null;

    /**
     * Whether or not assertion is negated
     * @var bool
     */
    protected $_negate            = false;

    /**
     * CSS selector or XPath path to select against
     * @var string
     */
    protected $_path              = null;

    /**
     * Whether or not to use XPath when querying
     * @var bool
     */
    protected $_useXpath          = false;

    /**
     * XPath namespaces
     * @var array
     */
    protected $_xpathNamespaces = [];

    /**
     * Constructor; setup constraint state
     *
     * @param  string $path CSS selector path
     * @return void
     */
    public function __construct($path)
    {
        $this->_path = $path;
    }

    /**
     * Indicate negative match
     *
     * @param  bool $flag
     * @return void
     */
    public function setNegate($flag = true)
    {
        $this->_negate = $flag;
    }

    /**
     * Whether or not path is a straight XPath expression
     *
     * @param  bool $flag
     * @return Zend_Test_PHPUnit_Constraint_DomQuery37
     */
    public function setUseXpath($flag = true)
    {
        $this->_useXpath = (bool) $flag;
        return $this;
    }

    /**
     * Evaluate an object to see if it fits the constraints
     *
     * @param  string $content Response content to be matched against (haystack)
     * @param  null|string $assertType Assertion type
     * @param  string $match (optional) String to match (needle), may be required depending on assertion type
     * @return bool
     *
     * NOTE:
     * Drastic changes up to PHPUnit 3.5.15 this was:
     *     public function evaluate($other, $assertType = null)
     * In PHPUnit 3.6.0 they changed the interface into this:
     *     public function evaluate($other, $description = '', $returnResult = FALSE)
     * We use the new interface for PHP-strict checking, but emulate the old one
     */
    public function evaluate($content, $assertType = '', $match = FALSE)
    {
        if (strstr($assertType, 'Not')) {
            $this->setNegate(true);
            $assertType = str_replace('Not', '', $assertType);
        }

        if (strstr($assertType, 'Xpath')) {
            $this->setUseXpath(true);
            $assertType = str_replace('Xpath', 'Query', $assertType);
        }

        if (!in_array($assertType, $this->_assertTypes)) {
            require_once 'Zend/Test/PHPUnit/Constraint/Exception.php';
            throw new Zend_Test_PHPUnit_Constraint_Exception(sprintf('Invalid assertion type "%s" provided to %s constraint', $assertType, __CLASS__));
        }

        $this->_assertType = $assertType;

        $method   = $this->_useXpath ? 'queryXpath' : 'query';
        $domQuery = new Zend_Dom_Query($content);
        $domQuery->registerXpathNamespaces($this->_xpathNamespaces);
        $result   = $domQuery->$method($this->_path);

        switch ($assertType) {
            case self::ASSERT_CONTENT_CONTAINS:
                if (!$match) {
                    require_once 'Zend/Test/PHPUnit/Constraint/Exception.php';
                    throw new Zend_Test_PHPUnit_Constraint_Exception('No content provided against which to match');
                }
                $this->_content = $match;
                return ($this->_negate)
                    ? $this->_notMatchContent($result, $match)
                    : $this->_matchContent($result, $match);
            case self::ASSERT_CONTENT_REGEX:
                if (!$match) {
                    require_once 'Zend/Test/PHPUnit/Constraint/Exception.php';
                    throw new Zend_Test_PHPUnit_Constraint_Exception('No pattern provided against which to match');
                }
                $this->_content = $match;
                return ($this->_negate)
                    ? $this->_notRegexContent($result, $match)
                    : $this->_regexContent($result, $match);
            case self::ASSERT_CONTENT_COUNT:
            case self::ASSERT_CONTENT_COUNT_MIN:
            case self::ASSERT_CONTENT_COUNT_MAX:
                if ($match === false) {
                    require_once 'Zend/Test/PHPUnit/Constraint/Exception.php';
                    throw new Zend_Test_PHPUnit_Constraint_Exception('No count provided against which to compare');
                }
                $this->_content = $match;
                return $this->_countContent($result, $match, $assertType);
            case self::ASSERT_QUERY:
            default:
                if ($this->_negate) {
                    return (0 == count($result));
                } else {
                    return (0 != count($result));
                }
        }
    }

    /**
     * Report Failure
     *
     * @see    PHPUnit_Framework_Constraint for implementation details
     * @param  mixed $other CSS selector path
     * @param  string $description Failure description
     * @param  object $cannot_be_used Cannot be used, null
     * @return void
     * @throws PHPUnit_Framework_ExpectationFailedException
     * NOTE:
     * Drastic changes up to PHPUnit 3.5.15 this was:
     *     public function fail($other, $description, $not = false)
     * In PHPUnit 3.6.0 they changed the interface into this:
     *     protected function fail($other, $description, PHPUnit_Framework_ComparisonFailure $comparisonFailure = NULL)
     * We use the new interface for PHP-strict checking
     */
    public function fail($other, $description, ?PHPUnit_Framework_ComparisonFailure $cannot_be_used = NULL)
    {
        require_once 'Zend/Test/PHPUnit/Constraint/Exception.php';
        switch ($this->_assertType) {
            case self::ASSERT_CONTENT_CONTAINS:
                $failure = 'Failed asserting node denoted by %s CONTAINS content "%s"';
                if ($this->_negate) {
                    $failure = 'Failed asserting node DENOTED BY %s DOES NOT CONTAIN content "%s"';
                }
                $failure = sprintf($failure, $other, $this->_content);
                break;
            case self::ASSERT_CONTENT_REGEX:
                $failure = 'Failed asserting node denoted by %s CONTAINS content MATCHING "%s"';
                if ($this->_negate) {
                    $failure = 'Failed asserting node DENOTED BY %s DOES NOT CONTAIN content MATCHING "%s"';
                }
                $failure = sprintf($failure, $other, $this->_content);
                break;
            case self::ASSERT_CONTENT_COUNT:
                $failure = 'Failed asserting node DENOTED BY %s OCCURS EXACTLY %d times';
                if ($this->_negate) {
                    $failure = 'Failed asserting node DENOTED BY %s DOES NOT OCCUR EXACTLY %d times';
                }
                $failure = sprintf($failure, $other, $this->_content);
                break;
            case self::ASSERT_CONTENT_COUNT_MIN:
                $failure = 'Failed asserting node DENOTED BY %s OCCURS AT LEAST %d times';
                $failure = sprintf($failure, $other, $this->_content);
                break;
            case self::ASSERT_CONTENT_COUNT_MAX:
                $failure = 'Failed asserting node DENOTED BY %s OCCURS AT MOST %d times';
                $failure = sprintf($failure, $other, $this->_content);
                break;
            case self::ASSERT_QUERY:
            default:
                $failure = 'Failed asserting node DENOTED BY %s EXISTS';
                if ($this->_negate) {
                    $failure = 'Failed asserting node DENOTED BY %s DOES NOT EXIST';
                }
                $failure = sprintf($failure, $other);
                break;
        }

        if (!empty($description)) {
            $failure = $description . "\n" . $failure;
        }

        throw new Zend_Test_PHPUnit_Constraint_Exception($failure);
    }

    /**
     * Complete implementation
     *
     * @return string
     */
    public function toString()
    {
        return '';
    }

    /**
     * Register XPath namespaces
     *
     * @param   array $xpathNamespaces
     * @return  void
     */
    public function registerXpathNamespaces($xpathNamespaces)
    {
        $this->_xpathNamespaces = $xpathNamespaces;
    }

    /**
     * Check to see if content is matched in selected nodes
     *
     * @param  Zend_Dom_Query_Result $result
     * @param  string $match Content to match
     * @return bool
     */
    protected function _matchContent($result, $match)
    {
        $match = (string) $match;

        if (0 == count($result)) {
            return false;
        }

        foreach ($result as $node) {
            $content = $this->_getNodeContent($node);
            if (strstr($content, $match)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check to see if content is NOT matched in selected nodes
     *
     * @param  Zend_Dom_Query_Result $result
     * @param  string $match
     * @return bool
     */
    protected function _notMatchContent($result, $match)
    {
        if (0 == count($result)) {
            return true;
        }

        foreach ($result as $node) {
            $content = $this->_getNodeContent($node);
            if (strstr($content, $match)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check to see if content is matched by regex in selected nodes
     *
     * @param  Zend_Dom_Query_Result $result
     * @param  string $pattern
     * @return bool
     */
    protected function _regexContent($result, $pattern)
    {
        if (0 == count($result)) {
            return false;
        }

        foreach ($result as $node) {
            $content = $this->_getNodeContent($node);
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check to see if content is NOT matched by regex in selected nodes
     *
     * @param  Zend_Dom_Query_Result $result
     * @param  string $pattern
     * @return bool
     */
    protected function _notRegexContent($result, $pattern)
    {
        if (0 == count($result)) {
            return true;
        }

        foreach ($result as $node) {
            $content = $this->_getNodeContent($node);
            if (preg_match($pattern, $content)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if content count matches criteria
     *
     * @param  Zend_Dom_Query_Result $result
     * @param  int $test Value against which to test
     * @param  string $type assertion type
     * @return boolean
     */
    protected function _countContent($result, $test, $type)
    {
        $count = count($result);

        switch ($type) {
            case self::ASSERT_CONTENT_COUNT:
                return ($this->_negate)
                    ? ($test != $count)
                    : ($test == $count);
            case self::ASSERT_CONTENT_COUNT_MIN:
                return ($count >= $test);
            case self::ASSERT_CONTENT_COUNT_MAX:
                return ($count <= $test);
            default:
                return false;
        }
    }

    /**
     * Get node content, minus node markup tags
     *
     * @param  DOMNode $node
     * @return string
     */
    protected function _getNodeContent(DOMNode $node)
    {
        if ($node instanceof DOMAttr) {
            return $node->value;
        } else {
            $doc     = $node->ownerDocument;
            $content = $doc->saveXML($node);
            $tag     = $node->nodeName;
            $regex   = '|</?' . $tag . '[^>]*>|';
            return preg_replace($regex, '', $content);
        }
    }
}
