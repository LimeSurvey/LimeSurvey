<?php

if (class_exists('ParagonIE_Sodium_Core_Keccak', false)) {
    return;
}

if (PHP_INT_SIZE === 4) {
    final class ParagonIE_Sodium_Core_Keccak extends ParagonIE_Sodium_Core32_Keccak
    {
    }
} else {
    final class ParagonIE_Sodium_Core_Keccak extends ParagonIE_Sodium_Core_Keccak64
    {
    }
}
