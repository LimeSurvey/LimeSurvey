<?php

namespace LimeSurvey;

use CHttpSession;
use ArrayAccess;

/**
 * Session Data
 *
 * Simple array access wrapper of CHttpSession to allow
 * mocking of session data in unit tests.
 *
 * Instead of accessing session data via App()->session['value'] it is
 * accessed via an injected SessionData: $sessionData['value'].
 * When unit testing SessionData is initialised in mock mode,
 * so data exists only in memory and is not passed to CHttpSession.
 */
class SessionData implements ArrayAccess
{
    private ?CHttpSession $yiiSession;
    public $mockContainer = [];

    public function __construct(
        ?CHttpSession $yiiSession = null
    ) {
        $this->yiiSession = $yiiSession;
    }

    private function &getContainer()
    {
        if (isset($this->yiiSession)) {
            return $this->yiiSession;
        }
        return $this->mockContainer;
    }

    public function offsetSet($offset, $value): void
    {
        $container = &$this->getContainer();
        if (is_null($offset)) {
            $container[] = $value;
        } else {
            $container[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool
    {
        $container = $this->getContainer();
        return isset($container[$offset]);
    }

    public function offsetUnset($offset): void
    {
        $container = &$this->getContainer();
        unset($container[$offset]);
    }

    public function offsetGet($offset)
    {
        $container = $this->getContainer();
        return isset($container[$offset])
            ? $container[$offset] : null;
    }
}
