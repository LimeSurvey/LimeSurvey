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
 * @package    Zend_Ldap
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Zend_Ldap_Collection_Iterator_Default is the default collection iterator implementation
 * using ext/ldap
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Ldap_Collection_Iterator_Default implements Iterator, Countable
{
    public const ATTRIBUTE_TO_LOWER  = 1;
    public const ATTRIBUTE_TO_UPPER  = 2;
    public const ATTRIBUTE_NATIVE    = 3;

    /**
     * LDAP Connection
     *
     * @var Zend_Ldap
     */
    protected $_ldap = null;

    /**
     * Result identifier resource
     *
     * @var resource
     */
    protected $_resultId = null;

    /**
     * Current result entry identifier
     *
     * @var resource
     */
    protected $_current = null;

    /**
     * Number of items in query result
     *
     * @var integer
     */
    protected $_itemCount = -1;

    /**
     * The method that will be applied to the attribute's names.
     *
     * @var  integer|callback
     */
    protected $_attributeNameTreatment = self::ATTRIBUTE_TO_LOWER;

    /**
     * This array holds a list of resources and sorting-values.
     *
     * Each result is represented by an array containing the keys <var>resource</var>
     * which holds a resource of a result-item and the key <var>sortValue</var>
     * which holds the value by which the array will be sorted.
     *
     * The resources will be filled on creating the instance and the sorting values
     * on sorting.
     *
     * @var array
     */
    protected $_entries = [];

    /**
     * The function to sort the entries by
     *
     * @var callable
     */
    protected $_sortFunction;

    /**
     * Constructor.
     *
     * @param  Zend_Ldap $ldap
     * @param  resource  $resultId
     * @return void
     */
    public function __construct(Zend_Ldap $ldap, $resultId)
    {
        $this->setSortFunction('strnatcasecmp');
        $this->_ldap = $ldap;
        $this->_resultId = $resultId;
        $this->_itemCount = @ldap_count_entries($ldap->getResource(), $resultId);
        if ($this->_itemCount === false) {
            /**
             * @see Zend_Ldap_Exception
             */
            require_once 'Zend/Ldap/Exception.php';
            throw new Zend_Ldap_Exception($this->_ldap, 'counting entries');
        }

        $identifier = ldap_first_entry(
            $ldap->getResource(),
            $resultId
        );

        while (false !== $identifier) {
            $this->_entries[] = [
                'resource' => $identifier,
                'sortValue' => '',
            ];

            $identifier = ldap_next_entry(
                $ldap->getResource(),
                $identifier
            );
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Closes the current result set
     *
     * @return bool
     */
    public function close()
    {
        $isClosed = false;
        if ($this->_isResult($this->_resultId)) {
             $isClosed = @ldap_free_result($this->_resultId);
             $this->_resultId = null;
             $this->_current = null;
        }
        return $isClosed;
    }

    /**
     * Gets the current LDAP connection.
     *
     * @return Zend_Ldap
     */
    public function getLdap()
    {
        return $this->_ldap;
    }

    /**
     * Sets the attribute name treatment.
     *
     * Can either be one of the following constants
     * - Zend_Ldap_Collection_Iterator_Default::ATTRIBUTE_TO_LOWER
     * - Zend_Ldap_Collection_Iterator_Default::ATTRIBUTE_TO_UPPER
     * - Zend_Ldap_Collection_Iterator_Default::ATTRIBUTE_NATIVE
     * or a valid callback accepting the attribute's name as it's only
     * argument and returning the new attribute's name.
     *
     * @param  integer|callback $attributeNameTreatment
     * @return $this
     */
    public function setAttributeNameTreatment($attributeNameTreatment)
    {
        if (is_callable($attributeNameTreatment)) {
            if (is_string($attributeNameTreatment) && !function_exists($attributeNameTreatment)) {
                $this->_attributeNameTreatment = self::ATTRIBUTE_TO_LOWER;
            } else if (is_array($attributeNameTreatment) &&
                    !method_exists($attributeNameTreatment[0], $attributeNameTreatment[1])) {
                $this->_attributeNameTreatment = self::ATTRIBUTE_TO_LOWER;
            } else {
                $this->_attributeNameTreatment = $attributeNameTreatment;
            }
        } else {
            $attributeNameTreatment = (int)$attributeNameTreatment;
            switch ($attributeNameTreatment) {
                case self::ATTRIBUTE_TO_LOWER:
                case self::ATTRIBUTE_TO_UPPER:
                case self::ATTRIBUTE_NATIVE:
                    $this->_attributeNameTreatment = $attributeNameTreatment;
                    break;
                default:
                    $this->_attributeNameTreatment = self::ATTRIBUTE_TO_LOWER;
                    break;
            }
        }
        return $this;
    }

    /**
     * Returns the currently set attribute name treatment
     *
     * @return integer|callback
     */
    public function getAttributeNameTreatment()
    {
        return $this->_attributeNameTreatment;
    }

    /**
     * Returns the number of items in current result
     * Implements Countable
     *
     * @return int
     */
    public function count(): int
    {
        return $this->_itemCount;
    }

    /**
     * Return the current result item
     * Implements Iterator
     *
     * @return array|null
     * @throws Zend_Ldap_Exception
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        if (!$this->_isResultEntry($this->_current)) {
            $this->rewind();
        }
        if (!$this->_isResultEntry($this->_current)) {
            return null;
        }

        $entry = ['dn' => $this->key()];
        $name = @ldap_first_attribute($this->_ldap->getResource(), $this->_current);
        while ($name) {
            $data = @ldap_get_values_len($this->_ldap->getResource(), $this->_current, $name);
            unset($data['count']);

            switch($this->_attributeNameTreatment) {
                case self::ATTRIBUTE_TO_LOWER:
                    $attrName = strtolower($name);
                    break;
                case self::ATTRIBUTE_TO_UPPER:
                    $attrName = strtoupper($name);
                    break;
                case self::ATTRIBUTE_NATIVE:
                    $attrName = $name;
                    break;
                default:
                    $attrName = call_user_func($this->_attributeNameTreatment, $name);
                    break;
            }
            $entry[$attrName] = $data;
            $name = @ldap_next_attribute($this->_ldap->getResource(), $this->_current);
        }
        ksort($entry, SORT_LOCALE_STRING);
        return $entry;
    }

    /**
     * Return the result item key
     * Implements Iterator
     *
     * @return string|null
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        if (!$this->_isResultEntry($this->_current)) {
            $this->rewind();
        }
        if ($this->_isResultEntry($this->_current)) {
            $currentDn = @ldap_get_dn($this->_ldap->getResource(), $this->_current);
            if ($currentDn === false) {
                /** @see Zend_Ldap_Exception */
                require_once 'Zend/Ldap/Exception.php';
                throw new Zend_Ldap_Exception($this->_ldap, 'getting dn');
            }
            return $currentDn;
        } else {
            return null;
        }
    }

    /**
     * Move forward to next result item
     *
     * @see Iterator
     *
     * @return void
     */
    public function next(): void
    {
        next($this->_entries);
        $nextEntry = current($this->_entries);
        $this->_current = isset($nextEntry['resource']) ? $nextEntry['resource'] : null;
    }

    /**
     * Rewind the Iterator to the first result item
     *
     * @see Iterator
     *
     * @return void
     */
    public function rewind(): void
    {
        reset($this->_entries);
        $nextEntry = current($this->_entries);
        $this->_current = isset($nextEntry['resource']) ? $nextEntry['resource'] : null;
    }

    /**
     * Check if there is a current result item
     * after calls to rewind() or next()
     * Implements Iterator
     *
     * @return boolean
     */
    public function valid(): bool
    {
        return ($this->_isResultEntry($this->_current));
    }

    /**
     * @param $resource
     *
     * @return bool
     */
    protected function _isResult($resource)
    {
        if (PHP_VERSION_ID < 80100) {
            return is_resource($resource);
        }

        return $resource instanceof \LDAP\Result;
    }

    /**
     * @param $resource
     *
     * @return bool
     */
    protected function _isResultEntry($resource)
    {
        if (PHP_VERSION_ID < 80100) {
            return is_resource($resource);
        }

        return $resource instanceof \LDAP\ResultEntry;
    }

    /**
     * Set a sorting-algorithm for this iterator
     *
     * The callable has to accept two parameters that will be compared.
     *
     * @param callable $_sortFunction The algorithm to be used for sorting
     * @return self Provides a fluent interface
     */
    public function setSortFunction($_sortFunction)
    {
        $this->_sortFunction = $_sortFunction;

        return $this;
    }

    /**
     * Sort the iterator
     *
     * Sorting is done using the set sortFunction which is by default strnatcasecmp.
     *
     * The attribute is determined by lowercasing everything.
     *
     * The sort-value will be the first value of the attribute.
     *
     * @param string $sortAttribute The attribute to sort by. If not given the
     *                              value set via setSortAttribute is used.
     * @return void
     */
    public function sort($sortAttribute)
    {
        foreach ($this->_entries as $key => $entry) {
            $attributes = ldap_get_attributes(
                $this->_ldap->getResource(),
                $entry['resource']
            );

            $attributes = array_change_key_case($attributes, CASE_LOWER);

            if (isset($attributes[$sortAttribute][0])) {
                $this->_entries[$key]['sortValue'] =
                    $attributes[$sortAttribute][0];
            }
        }

        $sortFunction = $this->_sortFunction;
        $sorted = usort($this->_entries, function($a, $b) use ($sortFunction) {
            return $sortFunction($a['sortValue'], $b['sortValue']);
        });

        if (! $sorted) {
            throw new Zend_Ldap_Exception($this, 'sorting result-set');
        }
    }
}
