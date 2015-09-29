<?php
namespace ls\expressionmanager;

/**
 * Class TokenStream
 * This class contains an ordered set of tokens.
 * It supports moving through the stream in 2 directions.
 * @package ls\expressionmanager
 */
class TokenStream {
    protected $index = 0;

    protected $restorePoints = [];
    protected $items = [];

    public function __construct(array $items) {
        $this->items = array_values($items);
    }

    public function next()
    {
        return $this->items[$this->index++];
    }

    public function peek() {
        return $this->items[$this->index];
    }

    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        if ($this->inTransaction()) {
            throw new \Exception("Can't remove elements from the stream during a transaction.");
        }
        unset($this->items[$offset]);
        $this->items = array_values($this->items);
        $this->index = 0;
    }


    public function end() {
        return count($this->items) == $this->index;
    }

    /**
     * Creates a restore point by saving the index.
     * @return boolean Always returns true for easy chaining.
     */
    public function begin() {
        array_push($this->restorePoints, $this->index);
        return true;
    }

    /**
     * @return boolean Always returns false for easy chaining.
     */
    public function rollback() {
        $this->index = array_pop($this->restorePoints);
        return false;
    }

    /**
     * @return boolean Always returns true for easy chaining.
     */
    public function commit() {
        array_pop($this->restorePoints);
        return true;
    }

    public function inTransaction() {
        return !empty($this->restorePoints);
    }

    /**
     * @return Token[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }

    public function getRest() {
        $result = '';
        for ($i = $this->index; $i < count($this->items); $i++) {
            $result .= "'{$this->items[$i]->value}'({$this->items[$i]->type}) ";
        }
        return $result;
    }

    public function dump()
    {
        $parts = [];
        foreach ($this->getItems() as $token) {
            if ($token->type != Token::WS) {
                $parts[] = $token->dump();
            }
        }
        return implode(' ', $parts);

    }
}