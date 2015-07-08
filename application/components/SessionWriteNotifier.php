<?php

class SessionWriteNotifier implements ArrayAccess {
    protected $session;

    public function __construct(&$reference = null) {
        if (!isset($reference)) {
            $this->session = &$_SESSION;
        } else {
            $this->session = $reference;
        }
    }
    public function offsetExists($offset)
    {
        return isset($this->session[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        if (!isset($this->session[$offset])) {
            $this->session[$offset] = [];
        }

        if (is_array($this->session[$offset])) {
            return new SessionWriteNotifier($this->session[$offset]);
        }
        return $this->session[$offset];
        // TODO: Implement offsetGet() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        //get the trace
        $trace = debug_backtrace();

        // Get the class that is asking for who awoke it
        if (!isset($trace[1]['class']) || $trace[1]['class'] != 'CHttpSession') {
            throw new \Exception('Writing to session, are we?');
        } else {
            $this->session[$offset] = $value;
        }
        // TODO: Implement offsetSet() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        //get the trace
        $trace = debug_backtrace();

        // Get the class that is asking for who awoke it
        $class = $trace[1]['class'];
        if (!isset($trace[1]['class']) || $trace[1]['class'] != 'CHttpSession') {
            throw new \Exception('Writing to session, are we?');
        } else {
            unset($this->session[$offset]);
        }

        // TODO: Implement offsetUnset() method.
    }

}