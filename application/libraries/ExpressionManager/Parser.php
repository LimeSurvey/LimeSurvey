<?php
namespace ls\expressionmanager;
/**
 * LimeSurvey
 * Copyright (C) 2007-2013 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */
/**
 *
 * @author Sam Mousa (sammousa)
 *
 * This is a clean version of em_core_helper; dealing only with actual parsing.
 * This parser only creates an ABSTRACT SYNTAX TREE.
 * It does not:
 * - Evaluate the tree.
 * - Check if variable names are valid (ie it checks syntax not semantics).
 * - Provide detailed error analysis.
 */

class Parser{

    /**
     * @var Tokenizer;
     */
    public $tokenizer;

    protected $error;

    public function parse($string) {
        // First tokenize it.
        $this->tokenizer = new Tokenizer();
        $tokens = $this->tokenizer->tokenize($string);
        $stack = [];
        while($this->parseExpression($tokens, $stack)) {}
        $color = isset($this->error['token']) ? '#ff0000' : '#00ff00';
        echo "<pre style='background-color: $color;'>";
        echo "$string\n";
        if (isset($this->error['token'])) {
            echo "Error in expression, expected {$this->error['expected']} got {$this->error['token']->type}\n";
        }
        $parts = [];
        foreach($tokens->getItems() as $token) {
            if ($token === $this->error['token']) {
                $parts[] = "<span style='background-color: yellow'>{$token->value}({$token->type})</span>";
            } elseif ($token->type != 'WS') {
                $parts[] = "{$token->value}({$token->type})";
            }
        }
        echo implode(' ', $parts) . "\n";
        echo '</pre>';
        return $stack;
    }



    /**
     * Rule: EXPR --> EQ_EXPR
     * @param array $tokens
     * @param array $stack
     */
    protected function parseExpression(TokenStream $tokens, array &$stack) {
        return $this->parseLogicExpression($tokens, $stack);
    }

    /**
     * Rule: LOGIC_EXPR --> EQ_EXPR (LOGIC_OP EQ_EXPR)*
     * @param TokenStream $tokens
     * @param array $stack
     */
    protected function parseLogicExpression(TokenStream $tokens, array &$stack)
    {
        $result = $this->parseEqExpression($tokens, $stack);
        if ($result) {
            while ($result) {
                $result = (
                        $tokens->begin()
                        && $this->parseToken('LOGIC_OP', $tokens, $stack)
                        && $this->parseEqExpression($tokens, $stack)
                        && $tokens->commit()
                    )
                    || $tokens->rollback();
                if ($result) {
                    // Combine.
                    $operand2 = array_pop($stack);
                    $operator = array_pop($stack);
                    $operand1 = array_pop($stack);
                    array_push($stack, [$operator, $operand1, $operand2]);
                }
            }
            return true;
        } else {
            return false;
        }

    }
    /**
     * Rule: EQ_EXPR --> ADD_EXPR (EQ_OP ADD_EXPR)*
     * @param array $tokens
     * @param array $stack
     */
    protected function parseEqExpression(TokenStream $tokens, array &$stack) {
        $result = $this->parseAddExpression($tokens, $stack);
        if ($result) {
            while ($result) {
                $result = (
                    $tokens->begin()
                    && $this->parseToken('EQ_OP', $tokens, $stack)
                    && (var_dump($tokens->getIndex()) || var_dump($tokens->getItems())|| true)

                    && $this->parseAddExpression($tokens, $stack)
                    && $tokens->commit()
                )
                || $tokens->rollback();
                if ($result) {
                    // Combine.
                    $operand2 = array_pop($stack);
                    $operator = array_pop($stack);
                    $operand1 = array_pop($stack);
                    array_push($stack, [$operator, $operand1, $operand2]);
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Rule: ADD_EXPR --> MULTI_EXPR (ADD_OP MULTI_EXPR)*
     * @param array $tokens
     * @param array $stack
     */
    protected function parseAddExpression(TokenStream $tokens, array &$stack) {
        $result = $this->parseMultiExpression($tokens, $stack);
        if ($result) {
            while ($result) {
                $result = (
                    $tokens->begin()
                    && $this->parseToken('ADD_OP', $tokens, $stack)
                    && $this->parseMultiExpression($tokens, $stack)
                    && $tokens->commit()
                )
                || $tokens->rollback();
                if ($result) {
                    // Combine.
                    $operand2 = array_pop($stack);
                    $operator = array_pop($stack);
                    $operand1 = array_pop($stack);
                    array_push($stack, [$operator, $operand1, $operand2]);
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Rule: MULTI_EXPR --> PRIMARY (MULTI_OP PRIMARY)*
     * @param array $tokens
     * @param array $stack
     */
    protected function parseMultiExpression(TokenStream $tokens, array &$stack) {
        $result = $this->parsePrimary($tokens, $stack);
        if ($result) {
            while ($result) {
                $result = (
                    $tokens->begin()
                    && $this->parseToken('MULTI_OP', $tokens, $stack)
                    && $this->parsePrimary($tokens, $stack)
                    && $tokens->commit()
                )
                || $tokens->rollback();
                if ($result) {
                    // Combine.
                    $operand2 = array_pop($stack);
                    $operator = array_pop($stack);
                    $operand1 = array_pop($stack);
                    array_push($stack, [$operator, $operand1, $operand2]);
                }
            }
            return true;
        } else {
            return false;
        }

    }

    /**
     * Rule: PRIMARY --> LPAREN EXPR RPAREN | VALUE | UN_OP EXPR | FUNC | NAME
     * @param array $tokens
     * @param array $stack
     */
    protected function parsePrimary(TokenStream $tokens, array &$stack)
    {
        return (
            $tokens->begin()
            && $this->consumeToken('LP', $tokens, $stack)
            && $this->parseExpression($tokens, $stack)
            && $this->consumeToken('RP', $tokens, $stack)
            && $tokens->commit()
        )
        || $tokens->rollback()
        || $this->parseValue($tokens, $stack)
        || (
            $tokens->begin()
            && $this->parseToken('UN_OP', $tokens, $stack)
            && $this->parseExpression($tokens, $stack)
            && $tokens->commit()
        )
        || $tokens->rollback()
        || $this->parseFunc($tokens, $stack)
        || $this->parseName($tokens, $stack);

    }


    /**
     * Rule: FUNC --> WORD LPAREN LIST RPAREN
     * @param array $tokens
     * @param array $stack
     */
    protected function parseFunc(TokenStream $tokens, array &$stack)
    {
        return (
            $tokens->begin()
            && $this->parseToken('WORD', $tokens, $stack, 'FUNC')
            && $this->consumeToken('LP', $tokens, $stack)
            && $this->parseList($tokens, $stack)
            && $this->consumeToken('RP', $tokens, $stack)
            && $tokens->commit()
        )
        || $tokens->rollback();
    }


    /**
     * Rule: LIST --> E | EXPR (LIST_SEPARATOR EXPR)*
     * @param array $tokens
     * @param array $stack
     */
    protected function parseList(TokenStream $tokens, array &$stack) {
        $result = $this->parseExpression($tokens, $stack);
        if ($result) {
            // List must be an array.
            array_push($stack, [array_pop($stack)]);
            while ($result) {
                $result = (
                    $tokens->begin()
                    && $this->consumeToken('LISTSEPARATOR', $tokens, $stack)
                    && $this->parseExpression($tokens, $stack)
                    && $tokens->commit()
                )
                || $tokens->rollback();
                if ($result) {
                    // Combine.
                    $operand = array_pop($stack);
                    $operands = array_pop($stack);
                    // Push new operand onto operands.
                    array_push($operands, $operand);
                    // Push operands onto stack.
                    array_push($stack, $operands);
                }
            }
            return true;
        } else {
            return false;
        }

    }

    /**
     * Parse a token from the input and put it on the stack.
     * Optionally set its context.
     * @param $type
     * @param array $tokens
     * @param array $stack
     * @param null $context
     * @return bool
     */
    protected function parseToken($type, TokenStream $tokens, array &$stack, $context = null) {
        while($this->consumeToken('WS', $tokens, $stack)) {}
        if (!$tokens->end() && $tokens->peek()->type == $type) {

            $token = $tokens->next();
            if (isset($context)) {
                $token->context = $context;
            }
            array_push($stack, $token);
            return true;
        } else {
            $this->error($type, $tokens, $stack);
            return false;
        }
    }

    protected function error($type, TokenStream $tokens, array &$stack) {
        // Stores the last error.
        if ($type != 'WS') {
            $this->error = [
                'expected' => $type,
                'stack' => $stack,
                'token' => $tokens->end() ? null : $tokens->peek()
            ];
        }
    }
    protected function consumeToken($type, TokenStream $tokens, array &$stack) {
        // Consume white space if any.
        if ($type != 'WS') {
            $this->consumeToken('WS', $tokens, $stack);
        }
        if (!$tokens->end() && $tokens->peek()->type == $type) {
            $token = $tokens->next();
            return true;
        } else {
            $this->error($type, $tokens, $stack);
            return false;
        }
    }

    /**
     * Rule: VALUE --> BOOL | STRING | NUMBER
     * @param array $tokens
     * @param array $stack
     */
    protected function parseValue(TokenStream $tokens, array &$stack) {
        return $this->parseToken('STRING', $tokens, $stack, 'LITERAL')
            || $this->parseToken('BOOL', $tokens, $stack, 'LITERAL')
            || $this->parseToken('NUMBER', $tokens, $stack, 'LITERAL');
    }

    /**
     * Rule: NAME --> SGQA (APPLY WORD)? | WORD (APPLY WORD)?
     * @param array $tokens
     * @param array $stack
     */
    protected function parseName(TokenStream $tokens, array &$stack) {
//        echo "<span style='background-color: blue;>Parsing name.</span>";
        return (
            $tokens->begin()
            && $this->parseToken('SGQA', $tokens, $stack, 'VARIABLE')
            && $this->parseApply($tokens, $stack)
            && $tokens->commit()
        )
        || $tokens->rollback()
        || (
            $tokens->begin()
            && $this->parseToken('WORD', $tokens, $stack, 'VARIABLE')
            && $this->parseApply($tokens, $stack)
            && $tokens->commit()
        )
        || $tokens->rollback();

    }

    /**
     * Parse optional apply rule.
     * Rule: (APPLY WORD)?
     * @param array $tokens
     * @param array $stack
     */
    protected function parseApply(TokenStream $tokens, array &$stack)
    {
        if ((
            $tokens->begin()
            && $this->consumeToken('APPLY', $tokens, $stack)
            && $this->parseToken('WORD', $tokens, $stack, 'FUNC')
            && $tokens->commit()
        )
        || $tokens->rollback()
        ) {
            // Basically this is a unary operator.
            $operator = array_pop($stack);
            $operand = array_pop($stack);
            array_push($stack, [$operator, $operand]);

        }
        // Always return true since this is an optional rule.
        return true;
    }
}