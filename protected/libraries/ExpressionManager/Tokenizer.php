<?php
namespace ls\expressionmanager;

class Tokenizer {
    
    public static $tokens = [
        Token::STRING => [
            '(?<!\\\\)".*?(?<!\\\\)"', // Single quoted string.
            '(?<!\\\\)\'.*?(?<!\\\\)\'' // Double quoted string.
        ],
        Token::WS => '\s+',
        Token::LP => '\(',
        Token::RP => '\)',
        Token::SEPARATOR => ',',
        Token::EQ_OP => ['<=', '<', '>=', '>', '==', '!=', '\ble\b', '\blt\b', '\bge\b', '\bgt\b', '\beq\b', '\bne\b'],
        Token::MULTI_OP => ['\*', '/'],
        Token::ADD_OP => ['\+', '\b-\b'],
        Token::SGQA => '[0-9]+X[0-9]+X[0-9]+[A-Z0-9_]*',
        Token::BOOL => ['true', 'false'],
        Token::UN_OP => '!',
        Token::LOGIC_OP => ['\band\b', '\bor\b', '&&' , '\|\|'],
        Token::NUMBER => ['-?[0-9]+\.?[0-9]*', '-?[0-9]*\.?[0-9]+'],
        Token::APPLY => '\.',
        Token::WORD => '[A-Z][A-Z0-9_]*',
        Token::ASSIGN => '=',
        // We have this here so we cant unit test and check if all tokens have a rule in the tokenizer.
        Token::UNKNOWN => []
    ];


    private static $_regex;
    public function __construct() {

    }

    protected function getRegex() {
        if (!isset(self::$_regex)) {
            $parts = [];
            array_walk_recursive(self::$tokens, function ($value) use (&$parts) {
                $parts[] = $value;
            });
            self::$_regex =  '#(' . implode('|', $parts) . ')#i';
        }
        return self::$_regex;
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

    /**
     * @param $string The content of the token.
     * @return int The type of the token.
     */
    public function classify($string)
    {
        foreach(self::$tokens as $name => $expressions) {
            foreach ((array) $expressions as $regex) {
                $regex = strtr($regex, ['\\b' => '']);
                if (preg_match("#^{$regex}$#i", $string) == 1) {
                    return $name;
                }
            }
        }
        return Token::UNKNOWN;
    }
}