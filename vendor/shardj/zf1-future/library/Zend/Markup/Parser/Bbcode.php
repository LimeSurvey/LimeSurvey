<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Markup
 * @subpackage Parser
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Markup_TokenList
 */
require_once 'Zend/Markup/TokenList.php';

/**
 * @see Zend_Markup_Parser_ParserInterface
 */
require_once 'Zend/Markup/Parser/ParserInterface.php';

/**
 * @category   Zend
 * @package    Zend_Markup
 * @subpackage Parser
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Markup_Parser_Bbcode implements Zend_Markup_Parser_ParserInterface
{
    public const NEWLINE   = "[newline\0]";

    // there is a parsing difference between the default tags and single tags
    public const TYPE_DEFAULT = 'default';
    public const TYPE_SINGLE  = 'single';

    public const NAME_CHARSET = '^\[\]=\s';

    public const STATE_SCAN       = 0;
    public const STATE_SCANATTRS  = 1;
    public const STATE_PARSEVALUE = 2;

    /**
     * Token tree
     *
     * @var Zend_Markup_TokenList
     */
    protected $_tree;

    /**
     * Current token
     *
     * @var Zend_Markup_Token
     */
    protected $_current;

    /**
     * Source to tokenize
     *
     * @var string
     */
    protected $_value = '';

    /**
     * Length of the value
     *
     * @var int
     */
    protected $_valueLen = 0;

    /**
     * Current pointer
     *
     * @var int
     */
    protected $_pointer = 0;

    /**
     * The buffer
     *
     * @var string
     */
    protected $_buffer = '';

    /**
     * Temporary tag storage
     *
     * @var array
     */
    protected $_temp;

    /**
     * Stoppers that we are searching for
     *
     * @var array
     */
    protected $_searchedStoppers = [];

    /**
     * Tag information
     *
     * @var array
     */
    protected $_tags = [
        'Zend_Markup_Root' => [
            'type'     => self::TYPE_DEFAULT,
            'stoppers' => [],
        ],
        '*' => [
            'type'     => self::TYPE_DEFAULT,
            'stoppers' => [self::NEWLINE, '[/*]', '[/]'],
        ],
        'hr' => [
            'type'     => self::TYPE_SINGLE,
            'stoppers' => [],
        ],
        'code' => [
            'type'         => self::TYPE_DEFAULT,
            'stoppers'     => ['[/code]', '[/]'],
            'parse_inside' => false
        ]
    ];

    /**
     * Token array
     *
     * @var array
     */
    protected $_tokens = [];

    /**
     * State
     *
     * @var int
     */
    protected $_state = self::STATE_SCAN;


    /**
     * Prepare the parsing of a bbcode string, the real parsing is done in {@link _parse()}
     *
     * @param  string $value
     * @return Zend_Markup_TokenList
     */
    public function parse($value)
    {
        if (!is_string($value)) {
            /**
             * @see Zend_Markup_Parser_Exception
             */
            require_once 'Zend/Markup/Parser/Exception.php';
            throw new Zend_Markup_Parser_Exception('Value to parse should be a string.');
        }

        if (empty($value)) {
            /**
             * @see Zend_Markup_Parser_Exception
             */
            require_once 'Zend/Markup/Parser/Exception.php';
            throw new Zend_Markup_Parser_Exception('Value to parse cannot be left empty.');
        }

        $this->_value = str_replace(["\r\n", "\r", "\n"], self::NEWLINE, $value);

        // variable initialization for tokenizer
        $this->_valueLen         = strlen($this->_value);
        $this->_pointer          = 0;
        $this->_buffer           = '';
        $this->_temp             = [];
        $this->_state            = self::STATE_SCAN;
        $this->_tokens           = [];

        $this->_tokenize();

        // variable initialization for treebuilder
        $this->_searchedStoppers = [];
        $this->_tree             = new Zend_Markup_TokenList();
        $this->_current          = new Zend_Markup_Token(
            '',
            Zend_Markup_Token::TYPE_NONE,
            'Zend_Markup_Root'
        );

        $this->_tree->addChild($this->_current);

        $this->_createTree();

        return $this->_tree;
    }

    /**
     * Tokenize
     *
     * @param string $input
     *
     * @return void
     */
    protected function _tokenize()
    {
        $attribute = '';

        while ($this->_pointer < $this->_valueLen) {
            switch ($this->_state) {
                case self::STATE_SCAN:
                    $matches = [];
                    $regex   = '#\G(?<text>[^\[]*)(?<open>\[(?<name>[' . self::NAME_CHARSET . ']+)?)?#';
                    preg_match($regex, $this->_value, $matches, 0, $this->_pointer);

                    $this->_pointer += strlen($matches[0]);

                    if (!empty($matches['text'])) {
                        $this->_buffer .= $matches['text'];
                    }

                    if (!isset($matches['open'])) {
                        // great, no tag, we are ending the string
                        break;
                    }
                    if (!isset($matches['name'])) {
                        $this->_buffer .= $matches['open'];
                        break;
                    }

                    $this->_temp = [
                        'tag'        => '[' . $matches['name'],
                        'name'       => $matches['name'],
                        'attributes' => []
                    ];

                    if ($this->_pointer >= $this->_valueLen) {
                        // damn, no tag
                        $this->_buffer .= $this->_temp['tag'];
                        break 2;
                    }

                    if ($this->_value[$this->_pointer] == '=') {
                        $this->_pointer++;

                        $this->_temp['tag'] .= '=';
                        $this->_state        = self::STATE_PARSEVALUE;
                        $attribute           = $this->_temp['name'];
                    } else {
                        $this->_state = self::STATE_SCANATTRS;
                    }
                    break;
                case self::STATE_SCANATTRS:
                    $matches = [];
                    $regex   = '#\G((?<end>\s*\])|\s+(?<attribute>[' . self::NAME_CHARSET . ']+)(?<eq>=?))#';
                    if (!preg_match($regex, $this->_value, $matches, 0, $this->_pointer)) {
                        break 2;
                    }

                    $this->_pointer += strlen($matches[0]);

                    if (!empty($matches['end'])) {
                        if (!empty($this->_buffer)) {
                            $this->_tokens[] = [
                                'tag' => $this->_buffer,
                                'type' => Zend_Markup_Token::TYPE_NONE
                            ];
                            $this->_buffer = '';
                        }
                        $this->_temp['tag'] .= $matches['end'];
                        $this->_temp['type'] = Zend_Markup_Token::TYPE_TAG;

                        $this->_tokens[] = $this->_temp;
                        $this->_temp     = [];

                        $this->_state = self::STATE_SCAN;
                    } else {
                        // attribute name
                        $attribute = $matches['attribute'];

                        $this->_temp['tag'] .= $matches[0];

                        $this->_temp['attributes'][$attribute] = '';

                        if (empty($matches['eq'])) {
                            $this->_state = self::STATE_SCANATTRS;
                        } else {
                            $this->_state = self::STATE_PARSEVALUE;
                        }
                    }
                    break;
                case self::STATE_PARSEVALUE:
                    $matches = [];
                    $regex   = '#\G((?<quote>"|\')(?<valuequote>.*?)\\2|(?<value>[^\]\s]+))#';
                    if (!preg_match($regex, $this->_value, $matches, 0, $this->_pointer)) {
                        $this->_state = self::STATE_SCANATTRS;
                        break;
                    }

                    $this->_pointer += strlen($matches[0]);

                    if (!empty($matches['quote'])) {
                        $this->_temp['attributes'][$attribute] = $matches['valuequote'];
                    } else {
                        $this->_temp['attributes'][$attribute] = $matches['value'];
                    }
                    $this->_temp['tag'] .= $matches[0];

                    $this->_state = self::STATE_SCANATTRS;
                    break;
            }
        }

        if (!empty($this->_buffer)) {
            $this->_tokens[] = [
                'tag'  => $this->_buffer,
                'type' => Zend_Markup_Token::TYPE_NONE
            ];
        }
    }

    /**
     * Parse the token array into a tree
     *
     * @param array $tokens
     *
     * @return void
     */
    public function _createTree()
    {
        foreach ($this->_tokens as $token) {
            // first we want to know if this tag is a stopper, or at least a searched one
            if ($this->_isStopper($token['tag'])) {
                // find the stopper
                $oldItems = [];

                while (!in_array($token['tag'], $this->_tags[$this->_current->getName()]['stoppers'])) {
                    $oldItems[]     = clone $this->_current;
                    $this->_current = $this->_current->getParent();
                }

                // we found the stopper, so stop the tag
                $this->_current->setStopper($token['tag']);
                $this->_removeFromSearchedStoppers($this->_current);
                $this->_current = $this->_current->getParent();

                // add the old items again if there are any
                if (!empty($oldItems)) {
                    foreach (array_reverse($oldItems) as $item) {
                        /* @var Zend_Markup_Token $token */
                        $this->_current->addChild($item);
                        $item->setParent($this->_current);
                        $this->_current = $item;
                    }
                }
            } else {
                if ($token['type'] == Zend_Markup_Token::TYPE_TAG) {
                    if ($token['tag'] == self::NEWLINE) {
                        // this is a newline tag, add it as a token
                        $this->_current->addChild(new Zend_Markup_Token(
                            "\n",
                            Zend_Markup_Token::TYPE_NONE,
                            '',
                            [],
                            $this->_current
                        ));
                    } elseif (isset($token['name']) && ($token['name'][0] == '/')) {
                        // this is a stopper, add it as a empty token
                        $this->_current->addChild(new Zend_Markup_Token(
                            $token['tag'],
                            Zend_Markup_Token::TYPE_NONE,
                            '',
                            [],
                            $this->_current
                        ));
                    } elseif (isset($this->_tags[$this->_current->getName()]['parse_inside'])
                        && !$this->_tags[$this->_current->getName()]['parse_inside']
                    ) {
                        $this->_current->addChild(new Zend_Markup_Token(
                            $token['tag'],
                            Zend_Markup_Token::TYPE_NONE,
                            '',
                            [],
                            $this->_current
                        ));
                    } else {
                        // add the tag
                        $child = new Zend_Markup_Token(
                            $token['tag'],
                            $token['type'],
                            $token['name'],
                            $token['attributes'],
                            $this->_current
                        );
                        $this->_current->addChild($child);

                        // add stoppers for this tag, if its has stoppers
                        if ($this->_getType($token['name']) == self::TYPE_DEFAULT) {
                            $this->_current = $child;

                            $this->_addToSearchedStoppers($this->_current);
                        }
                    }
                } else {
                    // no tag, just add it as a simple token
                    $this->_current->addChild(new Zend_Markup_Token(
                        $token['tag'],
                        Zend_Markup_Token::TYPE_NONE,
                        '',
                        [],
                        $this->_current
                    ));
                }
            }
        }
    }

    /**
     * Check if there is a tag declaration, and if it isnt there, add it
     *
     * @param string $name
     *
     * @return void
     */
    protected function _checkTagDeclaration($name)
    {
        if (!isset($this->_tags[$name])) {
            $this->_tags[$name] = [
                'type'     => self::TYPE_DEFAULT,
                'stoppers' => [
                    '[/' . $name . ']',
                    '[/]'
                ]
            ];
        }
    }
    /**
     * Check the tag's type
     *
     * @param  string $name
     * @return string
     */
    protected function _getType($name)
    {
        $this->_checkTagDeclaration($name);

        return $this->_tags[$name]['type'];
    }

    /**
     * Check if the tag is a stopper
     *
     * @param  string $tag
     * @return bool
     */
    protected function _isStopper($tag)
    {
        $this->_checkTagDeclaration($this->_current->getName());

        if (!empty($this->_searchedStoppers[$tag])) {
            return true;
        }

        return false;
    }

    /**
     * Add to searched stoppers
     *
     * @param  Zend_Markup_Token $token
     * @return void
     */
    protected function _addToSearchedStoppers(Zend_Markup_Token $token)
    {
        $this->_checkTagDeclaration($token->getName());

        foreach ($this->_tags[$token->getName()]['stoppers'] as $stopper) {
            if (!isset($this->_searchedStoppers[$stopper])) {
                $this->_searchedStoppers[$stopper] = 0;
            }
            ++$this->_searchedStoppers[$stopper];
        }
    }

    /**
     * Remove from searched stoppers
     *
     * @param  Zend_Markup_Token $token
     * @return void
     */
    protected function _removeFromSearchedStoppers(Zend_Markup_Token $token)
    {
        $this->_checkTagDeclaration($token->getName());

        foreach ($this->_tags[$token->getName()]['stoppers'] as $stopper) {
            --$this->_searchedStoppers[$stopper];
        }
    }

}
