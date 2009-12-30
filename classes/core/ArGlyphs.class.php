<?php
/**
 * ----------------------------------------------------------------------
 *  
 * Copyright (C) 2009 by Khaled Al-Shamaa.
 *  
 * http://www.ar-php.org
 *  
 * ----------------------------------------------------------------------
 *  
 * LICENSE
 *
 * This program is open source product; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public License (LGPL)
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 *  
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *  
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/lgpl.txt>.
 *  
 * ----------------------------------------------------------------------
 *  
 * Class Name: Arabic Glyphs is a simple class to render Arabic text
 *  
 * Filename:   ArGlyphs.class.php
 *  
 * Original    Author(s): Khaled Al-Sham'aa <khaled.alshamaa@gmail.com>
 *  
 * Purpose:    This class takes Arabic text (encoded in Windows-1256 character 
 *             set) as input and performs Arabic glyph joining on it and outputs 
 *             a UTF-8 hexadecimals stream that is no longer logically arranged 
 *             but in a visual order which gives readable results when formatted 
 *             with a simple Unicode rendering just like GD and UFPDF libraries 
 *             that does not handle basic connecting glyphs of Arabic language 
 *             yet but simply outputs all stand alone glyphs in left-to-right 
 *             order.
 *              
 * ----------------------------------------------------------------------
 *  
 * Arabic Glyphs is class to render Arabic text
 *
 * PHP class to render Arabic text by performs Arabic glyph joining on it,
 * then output a UTF-8 hexadecimals stream gives readable results on PHP
 * libraries supports UTF-8.
 *
 * Example:
 * <code>
 *   include('./Arabic.php');
 *   $Arabic = new Arabic('ArGlyphs');
 *
 *   $text = $Arabic->utf8Glyphs($text);
 *      
 *   imagettftext($im, 20, 0, 200, 100, $black, $font, $text);
 * </code>
 *
 * @category  Text 
 * @package   Arabic
 * @author    Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
 * @copyright 2009 Khaled Al-Shamaa
 *    
 * @license   LGPL <http://www.gnu.org/licenses/lgpl.txt>
 * @link      http://www.ar-php.org 
 */

// New in PHP V5.3: Namespaces
// namespace Arabic/ArGlyphs;

/**
 * This PHP class render Arabic text by performs Arabic glyph joining on it
 *  
 * @category  Text 
 * @package   Arabic
 * @author    Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
 * @copyright 2009 Khaled Al-Shamaa
 *    
 * @license   LGPL <http://www.gnu.org/licenses/lgpl.txt>
 * @link      http://www.ar-php.org 
 */ 
class ArGlyphs
{
    protected $_glyphs = null;
    protected $_hex = null;
    protected $_prevLink;
    protected $_nextLink;
    
    /**
     * Loads initialize values
     */         
    public function __construct()
    {
        $this->_prevLink = '¡¿ºÜÆÈÊËÌÍÎÓÔÕÖØÙÚÛÝÞßáãäåí';
        $this->_nextLink = 'ÜÂÃÄÅÇÆÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÝÞßáãäåæìí';
        $this->vowel = 'ðñòóõöøú';

        /*
         $this->_glyphs['ð']  = array('FE70','FE71');
         $this->_glyphs['ñ']  = array('FE72','FE72');
         $this->_glyphs['ò']  = array('FE74','FE74');
         $this->_glyphs['ó']  = array('FE76','FE77');
         $this->_glyphs['õ']  = array('FE78','FE79');
         $this->_glyphs['ö']  = array('FE7A','FE7B');
         $this->_glyphs['ø']  = array('FE7C','FE7D');
         $this->_glyphs['ú']  = array('FE7E','FE7E');
         */
        $this->_glyphs = 'ðñòóõöøú';
        $this->_hex = '064B064B064B064B064C064C064C064C064D064D064D064D064E064E064E064E064F064F064F064F065006500650065006510651065106510652065206520652';

        $this->_glyphs .= 'ÁÂÃÄÅÆÇÈ';
        $this->_hex .= 'FE80FE80FE80FE80FE81FE82FE81FE82FE83FE84FE83FE84FE85FE86FE85FE86FE87FE88FE87FE88FE89FE8AFE8BFE8CFE8DFE8EFE8DFE8EFE8FFE90FE91FE92';

        $this->_glyphs .= 'ÉÊËÌÍÎÏÐ';
        $this->_hex .= 'FE93FE94FE93FE94FE95FE96FE97FE98FE99FE9AFE9BFE9CFE9DFE9EFE9FFEA0FEA1FEA2FEA3FEA4FEA5FEA6FEA7FEA8FEA9FEAAFEA9FEAAFEABFEACFEABFEAC';

        $this->_glyphs .= 'ÑÒÓÔÕÖØÙ';
        $this->_hex .= 'FEADFEAEFEADFEAEFEAFFEB0FEAFFEB0FEB1FEB2FEB3FEB4FEB5FEB6FEB7FEB8FEB9FEBAFEBBFEBCFEBDFEBEFEBFFEC0FEC1FEC2FEC3FEC4FEC5FEC6FEC7FEC8';

        $this->_glyphs .= 'ÚÛÝÞßáãä';
        $this->_hex .= 'FEC9FECAFECBFECCFECDFECEFECFFED0FED1FED2FED3FED4FED5FED6FED7FED8FED9FEDAFEDBFEDCFEDDFEDEFEDFFEE0FEE1FEE2FEE3FEE4FEE5FEE6FEE7FEE8';

        $this->_glyphs .= 'åæìíÜ¡¿º';
        $this->_hex .= 'FEE9FEEAFEEBFEECFEEDFEEEFEEDFEEEFEEFFEF0FEEFFEF0FEF1FEF2FEF3FEF40640064006400640060C060C060C060C061F061F061F061F061B061B061B061B';

        $this->_glyphs .= 'áÂáÃáÅáÇ';
        $this->_hex .= 'FEF5FEF6FEF5FEF6FEF7FEF8FEF7FEF8FEF9FEFAFEF9FEFAFEFBFEFCFEFBFEFC';
    }
    
    /**
     * Get glyphs
     * 
     * @param string  $char Char
     * @param integer $type Type
     * 
     * @return string
     */                                  
    protected function _getGlyphs($char, $type)
    {
        $pos = strpos($this->_glyphs, $char);
        
        if ($pos > 48) {
            $pos = ($pos-48)/2 + 48;
        }
        
        $pos = $pos*16 + $type*4;
        
        return substr($this->_hex, $pos, 4);
    }
    
    /**
     * Convert Arabic Windows-1256 charset string into glyph joining in UTF-8 
     * hexadecimals stream
     *      
     * @param string $str Arabic string in Windows-1256 charset
     *      
     * @return string Arabic glyph joining in UTF-8 hexadecimals stream
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    protected function _preConvert($str)
    {
        $crntChar = null;
        $prevChar = null;
        $nextChar = null;
        $output = '';
        
        $chars = preg_split('//', $str);
        $max = count($chars);
        
        for ($i = $max - 1; $i >= 0; $i--) {
            $crntChar = $chars[$i];
            if ($i > 0) {
                $prevChar = $chars[$i - 1];
            } else {
                $prevChar = null;
            }
            
            if ($prevChar && strpos($this->vowel, $prevChar) !== false) {
                $prevChar = $chars[$i - 2];
                if ($prevChar && strpos($this->vowel, $prevChar) !== false) {
                    $prevChar = $chars[$i - 3];
                }
            }
            
            $Reversed = false;
            $flip_arr = ')]>}';
            $ReversedChr = '([<{';
            
            if ($crntChar && strpos($flip_arr, $crntChar) !== false) {
                $crntChar = substr($ReversedChr, strpos($flip_arr, $crntChar), 1);
                $Reversed = true;
            } else {
                $Reversed = false;
            }
            
            if ($crntChar && (strpos($ReversedChr, $crntChar) !== false) && !$Reversed) {
                $crntChar = substr($flip_arr, strpos($ReversedChr, $crntChar), 1);
            }
            
            if ($crntChar && strpos($this->vowel, $crntChar) !== false) {
                if ((strpos($this->_nextLink, $chars[$i + 1]) !== false)  && (strpos($this->_prevLink, $prevChar) !== false)) {
                    $output .= '&#x' . $this->_getGlyphs($crntChar, 1) . ';';
                } else {
                    $output .= '&#x' . $this->_getGlyphs($crntChar, 0) . ';';
                }
                continue;
            }
            
            if (isset($chars[$i + 1]) && in_array($chars[$i + 1], array('Â', 'Ã', 'Å', 'Ç')) && $crntChar == 'á') {
                continue;
            }
            
            if (ord($crntChar) < 128) {
                $output .= $crntChar;
                $nextChar = $crntChar;
                continue;
            }
            
            $form = 0;
            
            if (in_array($crntChar, array('Â', 'Ã', 'Å', 'Ç')) && $prevChar == 'á') {
                if (strpos($this->_prevLink, $chars[$i - 2]) !== false) {
                    $form++;
                }
                
                $output .= '&#x' . $this->_getGlyphs($prevChar . $crntChar, $form) . ';';
                $nextChar = $prevChar;
                continue;
            }
            
            if ($prevChar && strpos($this->_prevLink, $prevChar) !== false) {
                $form++;
            }
            if ($nextChar && strpos($this->_nextLink, $nextChar) !== false) {
                $form += 2;
            }
            
            $output .= '&#x' . $this->_getGlyphs($crntChar, $form) . ';';
            $nextChar = $crntChar;
        }
        
        $output = $this->_decodeEntities($output, $exclude = array('&'));
        return $output;
    }
    
    /**
     * Regression analysis calculate roughly the max number of character fit in 
     * one A4 page line for a given font size.
     *      
     * @param integer $font Font size
     *      
     * @return integer Maximum number of characters per line
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    public function a4_max_chars($font)
    {
        $x = 381.6 - 31.57 * $font + 1.182 * pow($font, 2) - 0.02052 * pow($font, 3) + 0.0001342 * pow($font, 4);
        return floor($x - 2);
    }
    
    /**
     * Calculate the lines number of given Arabic text and font size that will 
     * fit in A4 page size
     *      
     * @param string  $str          Arabic string you would like to split it into lines
     * @param integer $font         Font size
     * @param string  $inputCharset (optional) Input charset [utf-8|windows-1256|iso-8859-6]
     *                              default value is NULL (use set input charset)       
     * @param object  $main         Main Ar-PHP object to access charset converter options
     *                    
     * @return integer Number of lines for a given Arabic string in A4 page size
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    public function a4_lines($str, $font, $inputCharset = null, $main = null)
    {
        if ($main) {
            if ($inputCharset == null) $inputCharset = $main->getInputCharset();
            $str = $main->coreConvert($str, $inputCharset, 'windows-1256');
        }

        $str = str_replace(array("\r\n", "\n", "\r"), "\n", $str);
        
        $lines = 0;
        $chars = 0;
        $words = split(' ', $str);
        $w_count = count($words);
        $max_chars = $this->a4_max_chars($font);
        
        for ($i = 0; $i < $w_count; $i++) {
            $w_len = strlen($words[$i]) + 1;
            
            if ($chars + $w_len < $max_chars) {
                if (preg_match("/\n/i", $words[$i])) {
                    $words_nl = split("\n", $words[$i]);
                    
                    $nl_num = count($words_nl) - 1;
                    for ($j = 1; $j < $nl_num; $j++) {
                        $lines++;
                    }
                    
                    $chars = strlen($words_nl[$nl_num]) + 1;
                } else {
                    $chars += $w_len;
                }
            } else {
                $lines++;
                $chars = $w_len;
            }
        }
        $lines++;
        
        return $lines;
    }
    
    /**
     * Convert Arabic Windows-1256 charset string into glyph joining in UTF-8 
     * hexadecimals stream (take care of whole the document including English 
     * sections as well as numbers and arcs etc...)
     *                    
     * @param string  $str          Arabic string in Windows-1256 charset
     * @param integer $max_chars    Max number of chars you can fit in one line
     * @param boolean $hindo        If true use Hindo digits else use Arabic digits
     * @param string  $inputCharset (optional) Input charset [utf-8|windows-1256|iso-8859-6]
     *                              default value is NULL (use set input charset)       
     * @param object  $main         Main Ar-PHP object to access charset converter options
     *                    
     * @return string Arabic glyph joining in UTF-8 hexadecimals stream (take
     *                care of whole document including English sections as well
     *                as numbers and arcs etc...)
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    public function utf8Glyphs($str, $max_chars = 50, $hindo = true, $inputCharset = null, $main = null)
    {
        if ($main) {
            if ($inputCharset == null) $inputCharset = $main->getInputCharset();
            $str = $main->coreConvert($str, $inputCharset, 'windows-1256');
        }

        $str = str_replace(array("\r\n", "\n", "\r"), "\n", $str);
        
        $lines = array();
        $words = split(' ', $str);
        $w_count = count($words);
        $c_chars = 0;
        $c_words = array();
        
        $english = array();
        $en_index = -1;
        
        for ($i = 0; $i < $w_count; $i++) {
            if (preg_match("/^[a-z\d\\/\@\#\$\%\^\&\*\(\)\_\~\"\'\[\]\{\}\;\,\|]*([\.\:\+\=\-\!¡¿]?)$/i", $words[$i], $matches)) {
                if ($matches[1]) $words[$i] = $matches[1].substr($words[$i], 0, -1);
                $words[$i] = strrev($words[$i]);
                array_push($english, $words[$i]);
                if ($en_index == -1) {
                    $en_index = $i;
                }
            } elseif ($en_index != -1) {
                $en_count = count($english);
                
                for ($j = 0; $j < $en_count; $j++) {
                    $words[$en_index + $j] = $english[$en_count - 1 - $j];
                }
                
                $en_index = -1;
                $english = array();
            }
            
            $en_count = count($english);
            
            for ($j = 0; $j < $en_count; $j++) {
                $words[$en_index + $j] = $english[$en_count - 1 - $j];
            }
        }
        
        for ($i = 0; $i < $w_count; $i++) {
            $w_len = strlen($words[$i]) + 1;
            
            
            if ($c_chars + $w_len < $max_chars) {
                if (preg_match("/\n/i", $words[$i])) {
                    $words_nl = split("\n", $words[$i]);
                    
                    array_push($c_words, $words_nl[0]);
                    array_push($lines, implode(' ', $c_words));
                    
                    $nl_num = count($words_nl) - 1;
                    for ($j = 1; $j < $nl_num; $j++) {
                        array_push($lines, $words_nl[$j]);
                    }
                    
                    $c_words = array($words_nl[$nl_num]);
                    $c_chars = strlen($words_nl[$nl_num]) + 1;
                } else {
                    array_push($c_words, $words[$i]);
                    $c_chars += $w_len;
                }
            } else {
                array_push($lines, implode(' ', $c_words));
                $c_words = array($words[$i]);
                $c_chars = $w_len;
            }
        }
        array_push($lines, implode(' ', $c_words));
        
        $max_line = count($lines);
        $output = '';
        for ($j = $max_line - 1; $j >= 0; $j--) {
            $output .= $lines[$j] . "\n";
        }
        
        $output = rtrim($output);
        
        $output = $this->_preConvert($output);
        if ($hindo) {
            $Nums = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
            $arNums = array('Ù ', 'Ù¡', 'Ù¢', 'Ù£', 'Ù¤', 'Ù¥', 'Ù¦', 'Ù§', 'Ù¨', 'Ù©');
            $output = str_replace($Nums, $arNums, $output);
        }
        
        return $output;
    }
    
    /**
     * Decode all HTML entities (including numerical ones) to regular UTF-8 bytes. 
     * Double-escaped entities will only be decoded once ("&amp;lt;" becomes "&lt;", not "<").
     *                   
     * @param string $text    The text to decode entities in.
     * @param array  $exclude An array of characters which should not be decoded.
     *                        For example, array('<', '&', '"'). This affects
     *                        both named and numerical entities.
     *                        
     * @return string           
     */
    protected function _decodeEntities($text, $exclude = array())
    {
        static $table;
        
        // We store named entities in a table for quick processing.
        if (!isset($table)) {
            // Get all named HTML entities.
            $table = array_flip(get_html_translation_table(HTML_ENTITIES));
            
            // PHP gives us ISO-8859-1 data, we need UTF-8.
            $table = array_map('utf8_encode', $table);
            
            // Add apostrophe (XML)
            $table['&apos;'] = "'";
        }
        $newtable = array_diff($table, $exclude);
        
        // Use a regexp to select all entities in one pass, to avoid decoding 
        // double-escaped entities twice.
        return preg_replace('/&(#x?)?([A-Za-z0-9]+);/e', '$this
          ->_decodeEntities2("$1", "$2", "$0", $newtable, $exclude)', $text);
    }
    
    /**
     * Helper function for _decodeEntities
     * 
     * @param string $prefix    Prefix      
     * @param string $codepoint Codepoint         
     * @param string $original  Original        
     * @param array  &$table    Store named entities in a table      
     * @param array  &$exclude  An array of characters which should not be decoded
     * 
     * @return string                  
     */
    protected function _decodeEntities2($prefix, $codepoint, $original, &$table, &$exclude)
    {
        // Named entity
        if (!$prefix) {
            if (isset($table[$original])) {
                return $table[$original];
            } else {
                return $original;
            }
        }
        
        // Hexadecimal numerical entity
        if ($prefix == '#x') {
            $codepoint = base_convert($codepoint, 16, 10);
        }
        
        // Encode codepoint as UTF-8 bytes
        if ($codepoint < 0x80) {
            $str = chr($codepoint);
        } elseif ($codepoint < 0x800) {
            $str = chr(0xC0 | ($codepoint >> 6)) . chr(0x80 | ($codepoint & 0x3F));
        } elseif ($codepoint < 0x10000) {
            $str = chr(0xE0 | ($codepoint >> 12)) . chr(0x80 | (($codepoint >> 6) & 0x3F)) . chr(0x80 | ($codepoint & 0x3F));
        } elseif ($codepoint < 0x200000) {
            $str = chr(0xF0 | ($codepoint >> 18)) . chr(0x80 | (($codepoint >> 12) & 0x3F)) . chr(0x80 | (($codepoint >> 6) & 0x3F)) . chr(0x80 | ($codepoint & 0x3F));
        }
        
        // Check for excluded characters
        if (in_array($str, $exclude)) {
            return $original;
        } else {
            return $str;
        }
    }
}
?>
