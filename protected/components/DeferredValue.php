<?php
namespace ls\components;

/**
 * This class wraps a Closure and returns the closures' result when serialized to string.
 * Class ls\components\DeferredValue
 */
class DeferredValue
{
    private $closure;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    public function __toString()
    {
        $result = call_user_func($this->closure);

        return "" . $result;
    }
}