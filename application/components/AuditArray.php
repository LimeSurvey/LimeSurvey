<?php

class AuditArray implements ArrayAccess {
    protected $data = [];

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
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
        return $this->data[$offset];
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
//        echo '<pre>';
//        echo "Writing at $offset: ";
////        throw new \Exception();
//        print_r($value);
//        echo '</pre>';
//        @ob_flush();
        $this->data[$offset] = $value;
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
        unset($this->data[$offset]);
//        // Get the class that is asking for who awoke it
//        $class = $trace[1]['class'];
//        if (!isset($trace[1]['class']) || $trace[1]['class'] != 'CHttpSession') {
//            throw new \Exception('Writing to session, are we?');
//        } else {
//
//        }

        // TODO: Implement offsetUnset() method.
    }

}