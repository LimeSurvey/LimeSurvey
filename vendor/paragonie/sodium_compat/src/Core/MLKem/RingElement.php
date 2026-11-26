<?php

if (class_exists('ParagonIE_Sodium_Core_MLKem_RingElement', false)) {
    return;
}

/**
 * @internal
 * @psalm-suppress PossiblyUnusedMethod
 */
/**
 * @implements ArrayAccess<int, int>
 */
final class ParagonIE_Sodium_Core_MLKem_RingElement extends ParagonIE_Sodium_Core_Object256 implements ArrayAccess
{
    /**
     * @return self
     */
    public static function zero()
    {
        return new self();
    }

    /**
     * @param int[] $coefficients
     *
     * @throws SodiumException
     *
     * @return self
     * @psalm-suppress PossiblyUnusedMethod Covered by internal primitive tests.
     */
    public static function fromArray($coefficients)
    {
        if (count($coefficients) !== 256) {
            throw new SodiumException("ParagonIE_Sodium_Core_MLKem_RingElement::fromArray() expects 256 elements");
        }
        $poly = new self();
        foreach ($coefficients as $index => $coefficient) {
            $poly->{'c' . $index} = $coefficient;
        }
        return $poly;
    }
    #[ReturnTypeWillChange]
    /**
     * @param mixed $offset
     * @return bool
     */
    #[\Override]
    public function offsetExists($offset)
    {
        return $offset >= 0 && $offset < 256;
    }
    #[ReturnTypeWillChange]
    /**
     * @param mixed $offset
     * @return int
     */
    #[\Override]
    public function offsetGet($offset)
    {
        return $this->{'c' . $offset};
    }
    #[ReturnTypeWillChange]
    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    #[\Override]
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            throw new SodiumException('Cannot append to a polynomial');
        }
        $this->{'c' . $offset} = $value;
    }
    #[ReturnTypeWillChange]
    /**
     * @param mixed $offset
     *
     * @return void
     */
    #[\Override]
    public function offsetUnset($offset)
    {
        $this->{'c' . $offset} = 0;
    }
}
