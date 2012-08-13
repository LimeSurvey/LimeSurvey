<?php

/** This file is part of KCFinder project
  *
  *      @desc Input class for GET, POST and COOKIE requests
  *   @package KCFinder
  *   @version 2.51
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010, 2011 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

class input {

  /** Filtered $_GET array
    * @var array */
    public $get;

  /** Filtered $_POST array
    * @var array */
    public $post;

  /** Filtered $_COOKIE array
    * @var array */
    public $cookie;

  /** magic_quetes_gpc ini setting flag
    * @var bool */
    protected $magic_quotes_gpc;

  /** magic_quetes_sybase ini setting flag
    * @var bool */
    protected $magic_quotes_sybase;

    public function __construct() {
        $this->magic_quotes_gpc = function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc();
        $this->magic_quotes_sybase = ini_get('magic_quotes_sybase');
        $this->magic_quotes_sybase = $this->magic_quotes_sybase
            ? !in_array(strtolower(trim($this->magic_quotes_sybase)),
                array('off', 'no', 'false'))
            : false;
        $_GET = $this->filter($_GET);
        $_POST = $this->filter($_POST);
        $_COOKIE = $this->filter($_COOKIE);
        $this->get = &$_GET;
        $this->post = &$_POST;
        $this->cookie = &$_COOKIE;
    }

  /** Magic method to get non-public properties like public.
    * @param string $property
    * @return mixed */

    public function __get($property) {
        return property_exists($this, $property) ? $this->$property : null;
    }

  /** Filter the given subject. If magic_quotes_gpc and/or magic_quotes_sybase
    * ini settings are turned on, the method will remove backslashes from some
    * escaped characters. If the subject is an array, elements with non-
    * alphanumeric keys will be removed
    * @param mixed $subject
    * @return mixed */

    public function filter($subject) {
        if ($this->magic_quotes_gpc) {
            if (is_array($subject)) {
                foreach ($subject as $key => $val)
                    if (!preg_match('/^[a-z\d_]+$/si', $key))
                        unset($subject[$key]);
                    else
                        $subject[$key] = $this->filter($val);
            } elseif (is_scalar($subject))
                $subject = $this->magic_quotes_sybase
                    ? str_replace("\\'", "'", $subject)
                    : stripslashes($subject);

        }

        return $subject;
    }
}

?>