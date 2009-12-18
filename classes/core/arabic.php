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
 * Class Name: PHP and Arabic Language
 *  
 * Filename:   Arabic.php
 *  
 * Original    Author(s): Khaled Al-Sham'aa <khaled.alshamaa@gmail.com>
 *  
 * Purpose:    Set of PHP classes developed to enhance Arabic web 
 *             applications by providing set of tools includes stem-based searching, 
 *             translitiration, soundex, Hijri calendar, charset detection and
 *             converter, spell numbers, keyboard language, Muslim prayer time, 
 *             auto-summarization, and more...
 *              
 * ----------------------------------------------------------------------
 *
 * @desc   Set of PHP classes developed to enhance Arabic web
 *         applications by providing set of tools includes stem-based searching, 
 *         translitiration, soundex, Hijri calendar, charset detection and
 *         converter, spell numbers, keyboard language, Muslim prayer time, 
 *         auto-summarization, and more...
 * @category  Text 
 * @package   Arabic
 * @author    Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
 * @copyright 2009 Khaled Al-Shamaa
 *    
 * @license   LGPL <http://www.gnu.org/licenses/lgpl.txt>
 * @version   2.5.2 released in Sep 16, 2009
 * @link      http://www.ar-php.org
 */

// New in PHP V5.3: Namespaces
// namespace Arabic;

//error_reporting(E_STRICT);
$use_exception = false;
$use_autoload = false;

/**
 * Error handler function
 * 
 * @param int    $errno   The level of the error raised
 * @param string $errstr  The error message
 * @param string $errfile The filename that the error was raised in
 * @param int    $errline The line number the error was raised at
 * 
 * @return boolean FALSE      
 */ 
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    if ($errfile == __FILE__ || file_exists(dirname(__FILE__).'/'.basename($errfile))) {
        $msg  = '<b>Arabic Class Exception:</b> ';
        $msg .= $errstr;
        $msg .= " in <b>$errfile</b>";
        $msg .= " on line <b>$errline</b><br />";

        throw new ArabicException($msg, $errno);
    }
    
    // If the function returns false then the normal error handler continues
    return false;
}

if ($use_exception) { 
    set_error_handler('myErrorHandler'); 
}

/**
 * Core PHP and Arabic language class
 *  
 * @category  Text 
 * @package   Arabic
 * @author    Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
 * @copyright 2009 Khaled Al-Shamaa
 *    
 * @license   LGPL <http://www.gnu.org/licenses/lgpl.txt>
 * @link      http://www.ar-php.org
 */  
class Arabic
{
    protected $_inputCharset = 'utf-8';
    protected $_outputCharset = 'utf-8';
    protected $_path;
    
    public $myObject;
    public $myClass;

    /**
     * Load selected library/sub class you would like to use its functionality
     *          
     * @param string $library [ArAutoSummarize|ArCharsetC|ArCharsetD|ArDate|
     *               ArGender|ArGlyphs|ArIdentifier|ArKeySwap|ArMktime|ArNumbers|
     *               ArQuery|ArSoundex|ArStrToTime|ArTransliteration|ArWordTag|
     *               EnTransliteration|Salat|ArCompressStr|ArStandard|ArStemmer]
     *                    
     * @desc Load selected library/sub class you would like to use its functionality 
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    public function __construct($library)
    {
        if($library) $this->load($library);
    }
    
    public function load($library)
    {
        global $use_autoload;

        $this->myClass = $library;

        if (!$use_autoload) {
            $this->_path = strtr(__FILE__, '\\', '/');
            $this->_path = substr($this->_path, 0, strrpos($this->_path, '/'));

            include_once $this->_path.'/'.$this->myClass.'.class.php';
        }

        $this->myObject = new $library();
        $this->{$library} = &$this->myObject;
    }
    
    /**
     * The magic method __call() allows to capture invocation of non existing methods. 
     * That way __call() can be used to implement user defined method handling that 
     * depends on the name of the actual method being called.
     * 
     * @param string $methodName Method name
     * @param array  $arguments  Array of arguments
     * 
     * @return The value returned from the __call() method will be returned to 
     *         the caller of the method.
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */                                  
    public function __call($methodName, $arguments)
    {
        // Create an instance of the ReflectionMethod class
        $method = new ReflectionMethod($this->myClass, $methodName);
        
        $params = array();
        $parameters = $method->getParameters();

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $value = array_shift($arguments);
            if (is_null($value) && $parameter->isDefaultValueAvailable()) $value = $parameter->getDefaultValue(); 
            if ($name == 'main') $value = $this;
            $params[$name] = $value;
        }
        
        return call_user_func_array(array(&$this->myObject, $methodName), $params);
    }

    /**
     * Garbage collection, release child objects directly
     *          
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    public function __destruct() 
    {
        $this->_inputCharset = null;
        $this->_outputCharset = null;
        $this->_path = null;
        $this->myObject = null;
        $this->myClass = null;
    }

    /**
     * Set charset used in class input Arabic strings
     *          
     * @param string $charset Input charset [utf-8|windows-1256|iso-8859-6]
     *      
     * @return TRUE if success, or FALSE if fail
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    public function setInputCharset($charset)
    {
        $flag = true;
        
        $charset = strtolower($charset);
        
        if (in_array($charset, array('utf-8', 'windows-1256', 'iso-8859-6'))) {
            $this->_inputCharset = $charset;
        } else {
            $flag = false;
        }
        
        return $flag;
    }
    
    /**
     * Set charset used in class output Arabic strings
     *          
     * @param string $charset Output charset [utf-8|windows-1256|iso-8859-6]
     *      
     * @return boolean TRUE if success, or FALSE if fail
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    public function setOutputCharset($charset)
    {
        $flag = true;
        
        $charset = strtolower($charset);
        
        if (in_array($charset, array('utf-8', 'windows-1256', 'iso-8859-6'))) {
            $this->_outputCharset = $charset;
        } else {
            $flag = false;
        }
        
        return $flag;
    }

    /**
     * Get the charset used in the input Arabic strings
     *      
     * @return string return current setting for class input Arabic charset
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    public function getInputCharset()
    {
        return $this->_inputCharset;
    }
    
    /**
     * Get the charset used in the output Arabic strings
     *         
     * @return string return current setting for class output Arabic charset
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    public function getOutputCharset()
    {
        return $this->_outputCharset;
    }
    
    /**
     * Convert Arabic string from one charset to another
     *          
     * @param string $str           Original Arabic string that you wouldliketo convert
     * @param string $inputCharset  Input charset
     * @param string $outputCharset Output charset
     *      
     * @return string Converted Arabic string in defined charset
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    public function coreConvert($str, $inputCharset, $outputCharset)
    {
        if ($inputCharset != $outputCharset) {
            if ($inputCharset == 'windows-1256') $inputCharset = 'cp1256';
            if ($outputCharset == 'windows-1256') $outputCharset = 'cp1256';
            $conv_str = iconv($inputCharset, "$outputCharset//TRANSLIT", $str);

            if($conv_str == '' && $str != '') {
                include_once($this->_path.'/ArCharsetC.class.php');
                $c = ArCharsetC::singleton();
                
                if ($inputCharset == 'cp1256') {
                    $conv_str = $c->win2utf($str);
                } else {
                    $conv_str = $c->utf2win($str);
                }
            }
        } else {
            $conv_str = $str;
        }
        
        return $conv_str;
    }

    /**
     * Convert Arabic string from one format to another
     *          
     * @param string $str           Arabic string in the format set by setInputCharset
     * @param string $inputCharset  (optional) Input charset [utf-8|windows-1256|iso-8859-6]
     *                              default value is NULL (use set input charset)       
     * @param string $outputCharset (optional) Output charset [utf-8|windows-1256|iso-8859-6]
     *                              default value is NULL (use set output charset)
     *                                  
     * @return string Arabic string in the format set by method setOutputCharset
     * @author Khaled Al-Shamaa <khaled.alshamaa@gmail.com>
     */
    public function convert($str, $inputCharset = null, $outputCharset = null)
    {
        if ($inputCharset == null) $inputCharset = $this->_inputCharset;
        if ($outputCharset == null) $outputCharset = $this->_outputCharset;
        
        $str = $this->coreConvert($str, $inputCharset, $outputCharset);

        return $str;
    }
}

/**
 * Arabic Exception class defined by extending the built-in Exception class.
 *  
 * @category  Text 
 * @package   Arabic
 * @author    Khaled Al-Shamaa <khaled@ar-php.org>
 * @copyright 2009 Khaled Al-Shamaa
 *    
 * @license   LGPL <http://www.gnu.org/licenses/lgpl.txt>
 * @link      http://www.ar-php.org
 */  
class ArabicException extends Exception
{
    /**
     * Make sure everything is assigned properly
     * 
     * @param string $message Exception message
     * @param int    $code    User defined exception code            
     */         
    public function __construct($message, $code=0)
    {
        parent::__construct($message, $code);
    }
}
?>