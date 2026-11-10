<?php

/*
* LimeSurvey
* Copyright (C) 2007-2026 The LimeSurvey Project Team
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Evaluates the "Plural-Forms" header of a gettext catalog, e.g.
 * "nplurals=3; plural=(n == 1) ? 0 : ((n % 10 >= 2 && n % 10 <= 4) ? 1 : 2);"
 *
 * The plural expression is a C expression. It is parsed into a tree once and
 * evaluated without eval(), as the header comes from a translation file.
 */
class LSGettextPluralForms
{
    /** @var array<string,int> Binary operators and their precedence, higher binds tighter */
    private const BINARY_OPERATORS = [
        '||' => 1,
        '&&' => 2,
        '==' => 3,
        '!=' => 3,
        '<' => 4,
        '>' => 4,
        '<=' => 4,
        '>=' => 4,
        '+' => 5,
        '-' => 5,
        '*' => 6,
        '/' => 6,
        '%' => 6,
    ];

    /** @var array<string,array|null> Parsed expression trees by header, null if the header is invalid */
    private static array $parsedHeaders = [];

    /** @var string[] Tokens of the expression currently being parsed */
    private array $tokens = [];

    /** @var int Position of the current token */
    private int $position = 0;

    /**
     * Returns the index of the plural form to use for $count
     *
     * Falls back to the English rule (one form for 1, another for everything else)
     * if the header has no valid plural expression.
     *
     * @param string $header The catalog header, or only its Plural-Forms value
     * @param int $count
     * @return int
     */
    public static function getFormIndex(string $header, int $count): int
    {
        if (!array_key_exists($header, self::$parsedHeaders)) {
            self::$parsedHeaders[$header] = self::parseHeader($header);
        }
        $tree = self::$parsedHeaders[$header];
        if ($tree === null) {
            return $count === 1 ? 0 : 1;
        }
        return max(0, self::evaluate($tree, $count));
    }

    /**
     * Extracts and parses the plural expression of a catalog header
     *
     * @param string $header
     * @return array|null The expression tree, or null if there is no valid expression
     */
    private static function parseHeader(string $header): ?array
    {
        if (!preg_match('/plural\s*=\s*([^;\n]+)/', $header, $matches)) {
            return null;
        }
        try {
            return (new self())->parse($matches[1]);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     * Parses a plural expression into a tree
     *
     * @param string $expression
     * @return array
     * @throws InvalidArgumentException if the expression is invalid
     */
    private function parse(string $expression): array
    {
        if (!preg_match_all('/\s*(\d+|n|\|\||&&|[=!<>]=|[-+*\/%<>!?:()])/A', $expression, $matches)
            || implode('', $matches[0]) !== rtrim($expression)
        ) {
            throw new InvalidArgumentException('Invalid plural expression');
        }
        $this->tokens = $matches[1];
        $this->position = 0;
        $tree = $this->parseTernary();
        if ($this->position !== count($this->tokens)) {
            throw new InvalidArgumentException('Invalid plural expression');
        }
        return $tree;
    }

    /**
     * Parses "condition ? then : else", the operator with the lowest precedence
     *
     * @return array
     * @throws InvalidArgumentException
     */
    private function parseTernary(): array
    {
        $condition = $this->parseBinary(1);
        if ($this->peek() !== '?') {
            return $condition;
        }
        $this->position++;
        $then = $this->parseTernary();
        $this->expect(':');
        $else = $this->parseTernary();
        return ['?', $condition, $then, $else];
    }

    /**
     * Parses binary operators of at least the given precedence (precedence climbing)
     *
     * @param int $minPrecedence
     * @return array
     * @throws InvalidArgumentException
     */
    private function parseBinary(int $minPrecedence): array
    {
        $left = $this->parseUnary();
        while (($operator = $this->peek()) !== null && (self::BINARY_OPERATORS[$operator] ?? 0) >= $minPrecedence) {
            $this->position++;
            $right = $this->parseBinary(self::BINARY_OPERATORS[$operator] + 1);
            $left = [$operator, $left, $right];
        }
        return $left;
    }

    /**
     * Parses "!x", "-x", a number, n or a parenthesized expression
     *
     * @return array
     * @throws InvalidArgumentException
     */
    private function parseUnary(): array
    {
        $token = $this->peek();
        $this->position++;
        if ($token === '!') {
            return ['!', $this->parseUnary()];
        }
        if ($token === '-') {
            return ['negate', $this->parseUnary()];
        }
        if ($token === '(') {
            $tree = $this->parseTernary();
            $this->expect(')');
            return $tree;
        }
        if ($token === 'n') {
            return ['n'];
        }
        if ($token !== null && ctype_digit($token)) {
            return ['number', (int)$token];
        }
        throw new InvalidArgumentException('Invalid plural expression');
    }

    /**
     * Returns the current token without consuming it
     *
     * @return string|null Null at the end of the expression
     */
    private function peek(): ?string
    {
        return $this->tokens[$this->position] ?? null;
    }

    /**
     * Consumes the current token, which must be $token
     *
     * @param string $token
     * @return void
     * @throws InvalidArgumentException
     */
    private function expect(string $token): void
    {
        if ($this->peek() !== $token) {
            throw new InvalidArgumentException('Invalid plural expression');
        }
        $this->position++;
    }

    /**
     * Evaluates an expression tree for n = $count, with C semantics (booleans are 0 or 1)
     *
     * @param array $tree
     * @param int $count
     * @return int
     */
    private static function evaluate(array $tree, int $count): int
    {
        switch ($tree[0]) {
            case 'n':
                return $count;
            case 'number':
                return $tree[1];
            case '!':
                return (int)!self::evaluate($tree[1], $count);
            case 'negate':
                return -self::evaluate($tree[1], $count);
            case '?':
                return self::evaluate($tree[1], $count)
                    ? self::evaluate($tree[2], $count)
                    : self::evaluate($tree[3], $count);
            case '&&':
                return (int)(self::evaluate($tree[1], $count) && self::evaluate($tree[2], $count));
            case '||':
                return (int)(self::evaluate($tree[1], $count) || self::evaluate($tree[2], $count));
        }
        $left = self::evaluate($tree[1], $count);
        $right = self::evaluate($tree[2], $count);
        switch ($tree[0]) {
            case '==':
                return (int)($left === $right);
            case '!=':
                return (int)($left !== $right);
            case '<':
                return (int)($left < $right);
            case '>':
                return (int)($left > $right);
            case '<=':
                return (int)($left <= $right);
            case '>=':
                return (int)($left >= $right);
            case '+':
                return $left + $right;
            case '-':
                return $left - $right;
            case '*':
                return $left * $right;
            case '/':
                return $right === 0 ? 0 : intdiv($left, $right);
            default:
                return $right === 0 ? 0 : $left % $right;
        }
    }
}
