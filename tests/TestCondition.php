<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;

class TestCondition extends TestCase
{
    public function assertFieldConditions(string $criteriaCondition, string $pattern, array $placeholders)
    {
        $separators = [
            ['`', '`'],
            ['[', ']'], // MSSQL
            ['"', '"']  // PostgreSQL
        ];
        foreach ($separators as $sep) {
            $condition = $pattern;
            foreach ($placeholders as $key => $fieldname) {
                $placeholder = "[{$key}]";
                $condition = str_replace($placeholder, $sep[0] . $fieldname . $sep[1], $condition);
            }
            if (strpos($criteriaCondition, $condition) !== false) {
                return $this->assertTrue(true);
            }
        }

        return $this->assertTrue(false, "Unexpected condition: {$criteriaCondition}");
    }
}
