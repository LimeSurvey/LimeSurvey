<?php
namespace ls\expressionmanager;

class Tokenizer {
    public $tokens = [
        'STRING' => [
            '(?<!\\\\)".*?(?<!\\\\)"', // Single quoted string.
            '(?<!\\\\)\'.*?(?<!\\\\)\'' // Double quoted string.
        ],
        'WS' => '\s+',
        'LP' => '\(',
        'RP' => '\)',
        'LISTSEPARATOR' => ',',
        'EQ_OP' => ['<=', '<', '>=', '>', '==', '!=', '\ble\b', '\blt\b', '\bge\b', '\bgt\b', '\beq\b', '\bne\b'],
        'MULTI_OP' => ['\*', '/'],
        'ADD_OP' => ['\+', '\b-\b'],
        'SGQA' => '[0-9]+X[0-9]+X[0-9]+[A-Z0-9_]',
        'BOOL' => ['true', 'false'],
        'UN_OP' => '!',
        'LOGIC_OP' => ['\band\b', '\bor\b', '&&' , '\|\|'],
        'APPLY' => ['\.'],
        'WORD' => '[A-Z][A-Z0-9_]*',
        'NUMBER' => '-?[0-9]+\.?[0-9]*',


    ];


    private $_regex;
    public function __construct() {

    }

    protected function getRegex() {
        if (!isset($this->_regex)) {
            $parts = [];
            array_walk_recursive($this->tokens, function($value) use (&$parts) {
                $parts[] = $value;
            });
            $this->_regex =  '#(' . implode('|', $parts) . ')#i';
        }
        return $this->_regex;
    }
    public function tokenize($string) {
        $regex = $this->getRegex();
        $parts = preg_split($regex, $string, 0, PREG_SPLIT_DELIM_CAPTURE + PREG_SPLIT_NO_EMPTY);
        $tokens = [];
        foreach($parts as $part) {
            $tokens[] = new Token($part, $this->classify($part));
        }
        return new TokenStream($tokens);
    }

    public function classify($string) {
        foreach($this->tokens as $name => $regexes) {
            foreach(is_array($regexes) ? $regexes : [$regexes] as $regex) {
                if (preg_match("#^{$regex}$#i", $string) == 1) {
                    return $name;
                }
            }
        }
        return "UNKNOWN";
    }
}