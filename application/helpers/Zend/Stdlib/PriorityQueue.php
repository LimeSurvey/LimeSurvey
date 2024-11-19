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

require_once 'Zend/Stdlib/SplPriorityQueue.php';

/**
 * Re-usable, serializable priority queue implementation
 *
 * SplPriorityQueue acts as a heap; on iteration, each item is removed from the
 * queue. If you wish to re-use such a queue, you need to clone it first. This
 * makes for some interesting issues if you wish to delete items from the queue,
 * or, as already stated, iterate over it multiple times.
 *
 * This class aggregates items for the queue itself, but also composes an
 * "inner" iterator in the form of an SplPriorityQueue object for performing
 * the actual iteration.
 *
 * @category   Zend
 * @package    Zend_Stdlib
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Stdlib_PriorityQueue implements Countable, IteratorAggregate, Serializable
{
    const EXTR_DATA     = 0x00000001;
    const EXTR_PRIORITY = 0x00000002;
    const EXTR_BOTH     = 0x00000003;

    /**
     * Inner queue class to use for iteration
     * @var string
     */
    protected $queueClass = 'Zend_Stdlib_SplPriorityQueue';

    /**
     * Actual items aggregated in the priority queue. Each item is an array
     * with keys "data" and "priority".
     * @var array
     */
    protected $items      = array();

    /**
     * Inner queue object
     * @var SplPriorityQueue
     */
    protected $queue;

    /**
     * Insert an item into the queue
     *
     * Priority defaults to 1 (low priority) if none provided.
     *
     * @param  mixed $data
     * @param  int $priority
     * @return Zend_Stdlib_PriorityQueue
     */
    public function insert($data, $priority = 1)
    {
        $priority = (int) $priority;
        $this->items[] = array(
            'data'     => $data,
            'priority' => $priority,
        );
        $this->getQueue()->insert($data, $priority);
        return $this;
    }

    /**
     * Remove an item from the queue
     *
     * This is different than {@link extract()}; its purpose is to dequeue an
     * item.
     *
     * This operation is potentially expensive, as it requires
     * re-initialization and re-population of the inner queue.
     *
     * Note: this removes the first item matching the provided item found. If
     * the same item has been added multiple times, it will not remove other
     * instances.
     *
     * @param  mixed $datum
     * @return boolean False if the item was not found, true otherwise.
     */
    public function remove($datum)
    {
        $found = false;
        foreach ($this->items as $key => $item) {
            if ($item['data'] === $datum) {
                $found = true;
                break;
            }
        }
        if ($found) {
            unset($this->items[$key]);
            $this->queue = null;
            $queue = $this->getQueue();
            foreach ($this->items as $item) {
                $queue->insert($item['data'], $item['priority']);
            }
            return true;
        }
        return false;
    }

    /**
     * Is the queue empty?
     *
     * @return bool
     */
    public function isEmpty()
    {
        return (0 === $this->count());
    }

    /**
     * How many items are in the queue?
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Peek at the top node in the queue, based on priority.
     *
     * @return mixed
     */
    public function top()
    {
        return $this->getIterator()->top();
    }

    /**
     * Extract a node from the inner queue and sift up
     *
     * @return mixed
     */
    public function extract()
    {
        return $this->getQueue()->extract();
    }

    /**
     * Retrieve the inner iterator
     *
     * SplPriorityQueue acts as a heap, which typically implies that as items
     * are iterated, they are also removed. This does not work for situations
     * where the queue may be iterated multiple times. As such, this class
     * aggregates the values, and also injects an SplPriorityQueue. This method
     * retrieves the inner queue object, and clones it for purposes of
     * iteration.
     *
     * @return SplPriorityQueue
     */
    public function getIterator()
    {
        $queue = $this->getQueue();
        return clone $queue;
    }

    /**
     * Serialize the data structure
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->items);
    }

    /**
     * Unserialize a string into a Zend_Stdlib_PriorityQueue object
     *
     * Serialization format is compatible with {@link Zend_Stdlib_SplPriorityQueue}
     *
     * @param  string $data
     * @return void
     */
    public function unserialize($data)
    {
        foreach (unserialize($data) as $item) {
            $this->insert($item['data'], $item['priority']);
        }
    }

    /**
     * Serialize to an array
     *
     * By default, returns only the item data, and in the order registered (not
     * sorted). You may provide one of the EXTR_* flags as an argument, allowing
     * the ability to return priorities or both data and priority.
     *
     * @param  int $flag
     * @return array
     */
    public function toArray($flag = self::EXTR_DATA)
    {
        switch ($flag) {
            case self::EXTR_BOTH:
                return $this->items;
            case self::EXTR_PRIORITY:
                return array_map(array($this, 'returnPriority'), $this->items);
            case self::EXTR_DATA:
            default:
                return array_map(array($this, 'returnData'), $this->items);
        }
    }

    /**
     * Specify the internal queue class
     *
     * Please see {@link getIterator()} for details on the necessity of an
     * internal queue class. The class provided should extend SplPriorityQueue.
     *
     * @param  string $class
     * @return Zend_Stdlib_PriorityQueue
     */
    public function setInternalQueueClass($class)
    {
        $this->queueClass = (string) $class;
        return $this;
    }

    /**
     * Does the queue contain the given datum?
     *
     * @param  mixed $datum
     * @return bool
     */
    public function contains($datum)
    {
        foreach ($this->items as $item) {
            if ($item['data'] === $datum) {
                return true;
            }
        }
        return false;
    }

    /**
     * Does the queue have an item with the given priority?
     *
     * @param  int $priority
     * @return bool
     */
    public function hasPriority($priority)
    {
        foreach ($this->items as $item) {
            if ($item['priority'] === $priority) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the inner priority queue instance
     *
     * @return Zend_Stdlib_SplPriorityQueue
     */
    protected function getQueue()
    {
        if (null === $this->queue) {
            $this->queue = new $this->queueClass();
            if (!$this->queue instanceof SplPriorityQueue) {
                throw new DomainException(sprintf(
                    'Zend_Stdlib_PriorityQueue expects an internal queue of type SplPriorityQueue; received "%s"',
                    get_class($this->queue)
                ));
            }
        }
        return $this->queue;
    }

    /**
     * Return priority from an internal item
     *
     * Used as a lambda in toArray().
     *
     * @param  array $item
     * @return mixed
     */
    public function returnPriority($item)
    {
        return $item['priority'];
    }

    /**
     * Return data from an internal item
     *
     * Used as a lambda in toArray().
     *
     * @param  array $item
     * @return mixed
     */
    public function returnData($item)
    {
        return $item['data'];
    }
}
