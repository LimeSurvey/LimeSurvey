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
        $this->error = null;
        $this->tokenizer = new Tokenizer();
        $tokens = $this->tokenizer->tokenize($string);
        $stack = new Stack();
        $result = $this->parseExpression($tokens, $stack) && $tokens->end();
        $color = !$result ? '#ff0000' : '#00ff00';
        echo "<pre style='background-color: $color;'>";
        echo "$string\n";
        if (!$result) {
            $got = isset($this->error['token']) ? $this->error['token']->type: 'NULL';
            echo "Error in expression, expected {$this->error['expected']} got {$got}\n";
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
        if ($result) {
            return $stack->pop();
        }
    }



    /**
     * Rule: EXPR --> EQ_EXPR
     * @param array $tokens
     * @param array $stack
     */
    protected function parseExpression(TokenStream $tokens, Stack $stack) {
//        echo "Parsing {$tokens->getRest()}\n";
        return $this->parseLogicExpression($tokens, $stack);
    }

    /**
     * Rule: LOGIC_EXPR --> EQ_EXPR (LOGIC_OP EQ_EXPR)*
     * @param TokenStream $tokens
     * @param array $stack
     */
    protected function parseLogicExpression(TokenStream $tokens, Stack $stack)
    {
        $result = $this->parseEqExpression($tokens, $stack);
        if ($result) {
            while ($result) {
                $result = (
                        $tokens->begin() && $stack->begin()
                        && $this->parseToken('LOGIC_OP', $tokens, $stack)
                        && $this->parseEqExpression($tokens, $stack)
                        && $tokens->commit() && $stack->commit()
                    )
                    || $tokens->rollback() || $stack->rollback();
                if ($result) {
                    // Combine.
                    $operand2 = $stack->pop();
                    $operator = $stack->pop();
                    $operand1 = $stack->pop();
                    $stack->push([$operator, $operand1, $operand2]);
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
    protected function parseEqExpression(TokenStream $tokens, Stack $stack) {
        $result = $this->parseAddExpression($tokens, $stack);
        if ($result) {
            while ($result) {
                $result = (
                    $tokens->begin() && $stack->begin()
                    && $this->parseToken('EQ_OP', $tokens, $stack)
//                    && (var_dump($tokens->getIndex()) || var_dump($tokens->getItems())|| true)

                    && $this->parseAddExpression($tokens, $stack)
                    && $tokens->commit() && $stack->commit()
                )
                || $tokens->rollback() || $stack->rollback();
                if ($result) {
                    // Combine.
                    $operand2 = $stack->pop();
                    $operator = $stack->pop();
                    $operand1 = $stack->pop();
                    $stack->push([$operator, $operand1, $operand2]);
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
    protected function parseAddExpression(TokenStream $tokens, Stack $stack) {
        $result = $this->parseMultiExpression($tokens, $stack);
        if ($result) {
            while ($result) {
                $result = (
                    $tokens->begin() && $stack->begin()
                    && $this->parseToken('ADD_OP', $tokens, $stack)
                    && $this->parseMultiExpression($tokens, $stack)
                    && $tokens->commit() && $stack->commit()
                )
                || $tokens->rollback() || $stack->rollback();
                if ($result) {
                    // Combine.
                    $operand2 = $stack->pop();
                    $operator = $stack->pop();
                    $operand1 = $stack->pop();
                    $stack->push([$operator, $operand1, $operand2]);
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
    protected function parseMultiExpression(TokenStream $tokens, Stack $stack) {
        $result = $this->parsePrimary($tokens, $stack);
        if ($result) {
            while ($result) {
                $result = (
                    $tokens->begin() && $stack->begin()
                    && $this->parseToken('MULTI_OP', $tokens, $stack)
                    && $this->parsePrimary($tokens, $stack)
                    && $tokens->commit() && $stack->commit()
                )
                || $tokens->rollback() || $stack->rollback();
                if ($result) {
                    // Combine.
                    $operand2 = $stack->pop();
                    $operator = $stack->pop();
                    $operand1 = $stack->pop();
                    $stack->push([$operator, $operand1, $operand2]);
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
    protected function parsePrimary(TokenStream $tokens, Stack $stack)
    {
        return (
            $tokens->begin() && $stack->begin()
            && $this->consumeToken('LP', $tokens, $stack)
            && $this->parseExpression($tokens, $stack)
            && $this->consumeToken('RP', $tokens, $stack)
            && $tokens->commit() && $stack->commit()
        )
        || $tokens->rollback() || $stack->rollback()
        || $this->parseValue($tokens, $stack)
        || $this->parseUnaryExpression($tokens, $stack)
        || $this->parseFunc($tokens, $stack)
        || $this->parseName($tokens, $stack)
        || $this->parseUnaryExpression($tokens, $stack, 'ADD_OP');
    }

    /**
     * This parses an unary expression and puts the result on the stack.
     * The type argument allows using it for + and - as well (they can be used as binary and unary ops).
     * Rule: UN_OP EXPR
     *
     * @param TokenStream $tokens
     * @param Stack $stack
     */
    protected function parseUnaryExpression(TokenStream $tokens, Stack $stack, $type = 'UN_OP') {
        if (($tokens->begin() && $stack->begin()
            && $this->parseToken($type, $tokens, $stack)
            && $this->parseExpression($tokens, $stack)
            && $tokens->commit() && $stack->commit()
        )
        || $tokens->rollback() || $stack->rollback()) {
            $operand = $stack->pop();
            $operator = $stack->pop();
            $stack->push([$operator, $operand]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Rule: FUNC --> WORD LPAREN LIST RPAREN
     * @param array $tokens
     * @param array $stack
     */
    protected function parseFunc(TokenStream $tokens, Stack $stack)
    {
        echo "Parsing function .. {$tokens->getRest()}\n";
        ;
        if ((
            $tokens->begin() && $stack->begin()
            && $this->parseToken('WORD', $tokens, $stack, 'FUNC')
            && $this->consumeToken('LP', $tokens, $stack)
            && $this->parseList($tokens, $stack)
            && $this->consumeToken('RP', $tokens, $stack)
            && $tokens->commit() && $stack->commit()
        )
        || $tokens->rollback() || $stack->rollback()) {
            $operands = $stack->pop();
            $operator = $stack->pop();
            $stack->push([$operator, $operands]);
            return true;
        } else {
            return false;
        }

    }


    /**
     * Rule: LIST --> E | EXPR (LIST_SEPARATOR EXPR)*
     * @param array $tokens
     * @param array $stack
     */
    protected function parseList(TokenStream $tokens, Stack $stack) {
        $result = $this->parseExpression($tokens, $stack);
        if ($result) {
            // List must be an array.
            $stack->push([$stack->pop()]);
            while ($result) {
                $result = (
                    $tokens->begin() && $stack->begin()
                    && $this->consumeToken('LISTSEPARATOR', $tokens, $stack)
                    && $this->parseExpression($tokens, $stack)
                    && $tokens->commit() && $stack->commit()
                )
                || $tokens->rollback() || $stack->rollback();
                if ($result) {
                    // Combine.
                    $operand = $stack->pop();
                    $operands = $stack->pop();
                    // Push new operand onto operands.
                    array_push($operands, $operand);
                    // Push operands onto stack.
                    $stack->push($operands);
                }
            }
        } else {
            // List must be an array.
            $stack->push([]);
        }
        // Always return true, empty list is valid.
        return true;

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
    protected function parseToken($type, TokenStream $tokens, Stack $stack, $context = null) {
        while($this->consumeToken('WS', $tokens, $stack)) {}
        if (!$tokens->end() && $tokens->peek()->type == $type) {

            $token = $tokens->next();
            if (isset($context)) {
                $token->context = $context;
            }
            $stack->push($token);
            return true;
        } else {
            $this->error($type, $tokens, $stack);
            return false;
        }
    }

    protected function error($type, TokenStream $tokens, Stack $stack) {
        // Stores the deepest error.
        if ($type != 'WS' && (!isset($this->error['index']) || $tokens->getIndex() > $this->error['index'])) {
            $this->error = [
                'expected' => $type,
                'stack' => $stack,
                'token' => $tokens->end() ? null : $tokens->peek(),
                'index' => $tokens->getIndex()
            ];
        }
    }
    protected function consumeToken($type, TokenStream $tokens, Stack $stack) {
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
    protected function parseValue(TokenStream $tokens, Stack $stack) {
        return $this->parseToken('STRING', $tokens, $stack, 'LITERAL')
            || $this->parseToken('BOOL', $tokens, $stack, 'LITERAL')
            || $this->parseToken('NUMBER', $tokens, $stack, 'LITERAL');
    }

    /**
     * Rule: NAME --> SGQA (APPLY WORD)? | WORD (APPLY WORD)?
     * @param array $tokens
     * @param array $stack
     */
    protected function parseName(TokenStream $tokens, Stack $stack) {
//        echo "<span style='background-color: blue;>Parsing name.</span>";
        return (
            $tokens->begin() && $stack->begin()
            && $this->parseToken('SGQA', $tokens, $stack, 'VARIABLE')
            && $this->parseApply($tokens, $stack)
            && $tokens->commit() && $stack->commit()
        )
        || $tokens->rollback() || $stack->rollback()
        || (
            $tokens->begin() && $stack->begin()
            && $this->parseToken('WORD', $tokens, $stack, 'VARIABLE')
            && $this->parseApply($tokens, $stack)
            && $tokens->commit() && $stack->commit()
        )
        || $tokens->rollback() || $stack->rollback();

    }

    /**
     * Parse optional apply rule.
     * Rule: (APPLY WORD)?
     * @param array $tokens
     * @param array $stack
     */
    protected function parseApply(TokenStream $tokens, Stack $stack)
    {
        if ((
            $tokens->begin() && $stack->begin()
            && $this->consumeToken('APPLY', $tokens, $stack)
            && $this->parseToken('WORD', $tokens, $stack, 'FUNC')
            && $tokens->commit() && $stack->commit()
        )
        || $tokens->rollback() || $stack->rollback()
        ) {
            // Basically this is a function operator.
            $operator = $stack->pop();
            $operand = $stack->pop();
            $stack->push([$operator, [$operand]]);

        }
        // Always return true since this is an optional rule.
        return true;
    }
}