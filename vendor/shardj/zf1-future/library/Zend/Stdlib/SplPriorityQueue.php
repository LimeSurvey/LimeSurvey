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
 * @package    Zend_Stdlib
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * Serializable version of SplPriorityQueue
 *
 * Also, provides predictable heap order for datums added with the same priority
 * (i.e., they will be emitted in the same order they are enqueued).
 *
 * @category   Zend
 * @package    Zend_Stdlib
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Stdlib_SplPriorityQueue extends SplPriorityQueue implements Serializable
{
    /**
     * @var int Seed used to ensure queue order for items of the same priority
     */
    protected $serial = PHP_INT_MAX;

    /**
     * Insert a value with a given priority
     *
     * Utilizes {@var $serial} to ensure that values of equal priority are
     * emitted in the same order in which they are inserted.
     *
     * @param  mixed $datum
     * @param  mixed $priority
     * @return void
     */
    public function insert($datum, $priority)
    {
        // If using the native PHP SplPriorityQueue implementation, we need to
        // hack around it to ensure that items registered at the same priority
        // return in the order registered. In the userland version, this is not
        // necessary.
        if (!is_array($priority)) {
            $priority = [$priority, $this->serial--];
        }
        parent::insert($datum, $priority);
    }

    /**
     * Serialize to an array
     *
     * Array will be priority => data pairs
     *
     * @return array
     */
    public function toArray()
    {
        $this->setExtractFlags(self::EXTR_BOTH);
        $array = [];
        while ($this->valid()) {
            $array[] = $this->current();
            $this->next();
        }
        $this->setExtractFlags(self::EXTR_DATA);

        // Iterating through a priority queue removes items
        foreach ($array as $item) {
            $this->insert($item['data'], $item['priority']);
        }

        // Return only the data
        $return = [];
        foreach ($array as $item) {
            $return[] = $item['data'];
        }

        return $return;
    }

    /**
     * Serialize
     *
     * @return string
     */
    public function serialize(): ?string
    {
        return serialize($this->__serialize());
    }

    public function __serialize(): array
    {
        $data = [];
        $this->setExtractFlags(self::EXTR_BOTH);
        while ($this->valid()) {
            $data[] = $this->current();
            $this->next();
        }
        $this->setExtractFlags(self::EXTR_DATA);

        // Iterating through a priority queue removes items
        foreach ($data as $item) {
            $this->insert($item['data'], $item['priority']);
        }

        return $data;
    }

    /**
     * Deserialize
     *
     * @param  string $data
     * @return void
     */
    public function unserialize($data): void
    {
        $this->__unserialize(unserialize($data));
    }

    public function __unserialize(array $data): void
    {
        foreach ($data as $item) {
            $this->insert($item['data'], $item['priority']);
        }
    }
}
