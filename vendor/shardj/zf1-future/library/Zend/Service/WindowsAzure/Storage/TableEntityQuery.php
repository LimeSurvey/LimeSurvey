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
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_Storage_TableEntityQuery
{
    /**
     * From
     *
     * @var string
     */
	protected $_from  = '';

	/**
	 * Where
	 *
	 * @var array
	 */
	protected $_where = [];

	/**
	 * Order by
	 *
	 * @var array
	 */
	protected $_orderBy = [];

	/**
	 * Top
	 *
	 * @var int
	 */
	protected $_top = null;

	/**
	 * Partition key
	 *
	 * @var string
	 */
	protected $_partitionKey = null;

	/**
	 * Row key
	 *
	 * @var string
	 */
	protected $_rowKey = null;

	/**
	 * Select clause
	 *
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function select()
	{
		return $this;
	}

	/**
	 * From clause
	 *
	 * @param string $name Table name to select entities from
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function from($name)
	{
		$this->_from = $name;
		return $this;
	}

	/**
	 * Specify partition key
	 *
	 * @param string $value Partition key to query for
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function wherePartitionKey($value = null)
	{
	    $this->_partitionKey = $value;
	    return $this;
	}

	/**
	 * Specify row key
	 *
	 * @param string $value Row key to query for
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function whereRowKey($value = null)
	{
	    $this->_rowKey = $value;
	    return $this;
	}

	/**
	 * Add where clause
	 *
	 * @param string       $condition   Condition, can contain question mark(s) (?) for parameter insertion.
	 * @param string|array $value       Value(s) to insert in question mark (?) parameters.
	 * @param string       $cond        Condition for the clause (and/or/not)
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function where($condition, $value = null, $cond = '')
	{
	    $condition = $this->_replaceOperators($condition);

	    if (!is_null($value)) {
	        $condition = $this->_quoteInto($condition, $value);
	    }

		if (count($this->_where) === 0) {
			$cond = '';
		} else if ($cond !== '') {
			$cond = ' ' . strtolower(trim($cond)) . ' ';
		}

		$this->_where[] = $cond . $condition;
		return $this;
	}

	/**
	 * Add where clause with AND condition
	 *
	 * @param string       $condition   Condition, can contain question mark(s) (?) for parameter insertion.
	 * @param string|array $value       Value(s) to insert in question mark (?) parameters.
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function andWhere($condition, $value = null)
	{
		return $this->where($condition, $value, 'and');
	}

	/**
	 * Add where clause with OR condition
	 *
	 * @param string       $condition   Condition, can contain question mark(s) (?) for parameter insertion.
	 * @param string|array $value       Value(s) to insert in question mark (?) parameters.
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function orWhere($condition, $value = null)
	{
		return $this->where($condition, $value, 'or');
	}

	/**
	 * OrderBy clause
	 *
	 * @param string $column    Column to sort by
	 * @param string $direction Direction to sort (asc/desc)
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function orderBy($column, $direction = 'asc')
	{
		$this->_orderBy[] = $column . ' ' . $direction;
		return $this;
	}

	/**
	 * Top clause
	 *
	 * @param int $top  Top to fetch
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
    public function top($top = null)
    {
        $this->_top  = (int)$top;
        return $this;
    }

    /**
     * Assembles the query string
     *
     * @param boolean $urlEncode Apply URL encoding to the query string
     * @return string
     */
	public function assembleQueryString($urlEncode = false)
	{
		$query = [];
		if (count($this->_where) != 0) {
		    $filter = implode('', $this->_where);
			$query[] = '$filter=' . ($urlEncode ? self::encodeQuery($filter) : $filter);
		}

		if (count($this->_orderBy) != 0) {
		    $orderBy = implode(',', $this->_orderBy);
			$query[] = '$orderby=' . ($urlEncode ? self::encodeQuery($orderBy) : $orderBy);
		}

		if (!is_null($this->_top)) {
			$query[] = '$top=' . $this->_top;
		}

		if (count($query) != 0) {
			return '?' . implode('&', $query);
		}

		return '';
	}

	/**
	 * Assemble from
	 *
	 * @param boolean $includeParentheses Include parentheses? ()
	 * @return string
	 */
	public function assembleFrom($includeParentheses = true)
	{
	    $identifier = '';
	    if ($includeParentheses) {
	        $identifier .= '(';

	        if (!is_null($this->_partitionKey)) {
	            $identifier .= 'PartitionKey=\'' . self::encodeQuery($this->_partitionKey) . '\'';
	        }

	        if (!is_null($this->_partitionKey) && !is_null($this->_rowKey)) {
	            $identifier .= ', ';
	        }

	        if (!is_null($this->_rowKey)) {
	            $identifier .= 'RowKey=\'' . self::encodeQuery($this->_rowKey) . '\'';
	        }

	        $identifier .= ')';
	    }
		return $this->_from . $identifier;
	}

	/**
	 * Assemble full query
	 *
	 * @return string
	 */
	public function assembleQuery()
	{
		$assembledQuery = $this->assembleFrom();

		$queryString = $this->assembleQueryString();
		if ($queryString !== '') {
			$assembledQuery .= $queryString;
		}

		return $assembledQuery;
	}

	/**
	 * Quotes a variable into a condition
	 *
	 * @param string       $text   Condition, can contain question mark(s) (?) for parameter insertion.
	 * @param string|array $value  Value(s) to insert in question mark (?) parameters.
	 * @return string
	 */
	protected function _quoteInto($text, $value = null)
	{
		if (!is_array($value)) {
	        $text = str_replace('?', '\'' . addslashes($value) . '\'', $text);
	    } else {
	        $i = 0;
	        while(strpos($text, '?') !== false) {
	            if (is_numeric($value[$i])) {
	                $text = substr_replace($text, $value[$i++], strpos($text, '?'), 1);
	            } else {
	                $text = substr_replace($text, '\'' . addslashes($value[$i++]) . '\'', strpos($text, '?'), 1);
	            }
	        }
	    }
	    return $text;
	}

	/**
	 * Replace operators
	 *
	 * @param string $text
	 * @return string
	 */
	protected function _replaceOperators($text)
	{
        return str_replace(
            ['==', '>', '<', '>=', '<=', '!=', '&&', '||', '!'],
            ['eq', 'gt', 'lt', 'ge', 'le', 'ne', 'and', 'or', 'not'],
            $text);
	}

	/**
	 * urlencode a query
	 *
	 * @param string $query Query to encode
	 * @return string Encoded query
	 */
	public static function encodeQuery($query)
	{
        return str_replace(
            ['/', '?', ':', '@', '&', '=', '+', ',', '$', '{', '}', ' '],
            ['%2F', '%3F', '%3A', '%40', '%26', '%3D', '%2B', '%2C', '%24', '%7B', '%7D', '%20'],
            $query);
	}

	/**
	 * __toString overload
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->assembleQuery();
	}
}
