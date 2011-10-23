<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2010, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 2.0
 * @filesource
 */

/**
 * Encapsulate access to session data.
 *
 * Allows notifier being send.
 */
class Session_Data implements ArrayAccess
{
    /**
     * @var callback
     */
    private $notify;

    public function setNotify($callback)
    {
        $this->notify = $callback;
    }
    
    private function notifyCall()
    {
        call_user_func($this->notify);
    }

    public function offsetExists($offset)
    {
        if ($this->notify) $this->notifyCall();
        return isset($_SESSION[$offset]);
    }
    public function offsetGet($offset)
    {
        if ($this->notify) $this->notifyCall();
        return $_SESSION[$offset];
    }
    public function offsetSet($offset, $value)
    {
        if ($this->notify) $this->notifyCall();
        $_SESSION[$offset] = $value;
    }
    public function offsetUnset($offset)
    {
        if ($this->notify) $this->notifyCall();
        unset($_SESSION[$offset]);
    }
    public function clear()
    {
        $_SESSION = array();
    }
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
        throw new RuntimeException('Clone is not allowed.');
    }
    public function __wakeup()
    {
        trigger_error('Unserializing is not allowed.', E_USER_ERROR);
        throw new RuntimeException('Unserializing is not allowed.');
    }
}

/**
 * Native PHP session management driver
 *
 * This is the driver that uses the native PHP $_SESSION array through the Session driver library.
 *
 * debug version of it due to session problems with Limesurvey pre 2.0 code
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Sessions
 * @author		Darren Hill (DChill)
 * @author		mot
 */
class Session_Native_Debug extends SessionDriver {

    /**
     * Autostart session?
     * @var bool
     */
    private $autoStart = false;

    /**
     * session name
     *
     * @var string
     */
    private $name;
    
    /**
     * session variables
     *
     * @var Session_Data
     */
    private $data;
    
    /**
     * session configuration
     *
     * @var array
     */
    private $config;
    
    /**
     * constructor
     */
    public function __construct()
    {
    }
    
    /**
     * read configuration from params and
     * global ci configuration files.
     */
    private function readConfig()
    {
        $config = array();
        $items = array('sess_cookie_name', 'sess_expire_on_close', 'sess_expiration', 'sess_match_ip',
                       'sess_match_useragent', 'cookie_prefix', 'cookie_path', 'cookie_domain');
        $params = $this->parent->params;
        $global = get_instance()->config;
        
        foreach($items as $key)
        {
            $config[$key] = isset($params[$key]) ? $params[$key] : $global->item($key);
        }
        
        // Default to 2 years if expiration is "0"
        if ($config['sess_expiration'] !== FALSE && $config['sess_expiration'] == 0)
        {
            $config['sess_expiration'] = 60*60*24*365*2;
        }
        return $config;
    }
    
    private function getSessionNameFromConfig(array $config)
    {
        $name = NULL;
    	
		if ($config['sess_cookie_name'])
		{
			$name = $config['sess_cookie_name'];
			if ($config['cookie_prefix'])
			{
				// Prepend cookie prefix
				$name = $config['cookie_prefix'].$name;
			}
		}
		return $name;
    }
    
    /**
     * @return array 0:expire, 1:path, 2:domain
     */
    private function getCookieParamsFromConfig(array $config)
    {
		$expire = 7200;
		$path = '/';
		$domain = '';
		
		if ($config['sess_expiration'] !== FALSE)
		{
		    // set session expiration
			$expire = $config['sess_expiration'];
		}
		
		if ($config['sess_expire_on_close'])
		{
		    // session expires on close
		    $expire = 0;
		}
		
		if ($config['cookie_path'])
		{
			// Use specified path
			$path = $config['cookie_path'];
		}
		if ($config['cookie_domain'])
		{
			// Use specified domain
			$domain = $config['cookie_domain'];
		}

		return array($expire, $path, $domain);
    }
    
    /**
     * Check session expiration, ip, and agent
     */
    private function isInvalid()
    {
        $now = time();
        $config = $this->config;
        $CI =& get_instance();
        $invalid = FALSE;
        
        $store = isset($this->data['__CI']) ? $this->data['__CI'] : array();
        
        if (isset($store['last_activity']) && ($store['last_activity'] + $config['sess_expiration']) < $now)
        {
        	// Expired - destroy
        	$invalid = TRUE;
        }
        else if ($config['sess_match_ip'] == TRUE && isset($store['ip_address']) &&
        $store['ip_address'] != $CI->input->ip_address())
        {
        	// IP doesn't match - destroy
        	$invalid = TRUE;
        }
        else if ($config['sess_match_useragent'] == TRUE && isset($store['user_agent']) &&
        $store['user_agent'] != trim(substr($CI->input->user_agent(), 0, 50)))
        {
        	// Agent doesn't match - destroy
        	$invalid = TRUE;
        }
        
        return $invalid;
    }
    
    private function updateAccess()
    {
        $CI =& get_instance();
        $config = $this->config;
        
        $store = isset($this->data['__CI']) ? $this->data['__CI'] : array();
        
        // Set activity time
        $store['last_activity'] = time();
        
        // Set matching values as required
        if ($config['sess_match_ip'] == TRUE && !isset($store['ip_address']))
        {
            // Store user IP address
            $store['ip_address'] = $CI->input->ip_address();
        }
        if ($config['sess_match_useragent'] == TRUE && !isset($store['user_agent']))
        {
            // Store user agent string
            $store['user_agent'] = trim(substr($CI->input->user_agent(), 0, 50));
        }
        
        $this->data['__CI'] = $store;
    }
	/**
	 * Initialize session driver object
	 *
	 * @access  protected
	 * @return	void
	 */
	protected function initialize()
	{
        if ($this->autoStart)
        {
            $this->data = &$_SESSION;
		    $this->session_start();
        } else {
            $this->data = new Session_Data();
            $this->data->setNotify(array($this, 'firstAccess'));
        }
	}
	
	public function firstAccess()
	{
		// $this->data->setNotify(NULL);
	    $this->data = &$_SESSION;
	    $CI =& get_instance();
	    var_dump($CI); die;
	    $CI->session->bind_userdata();
	    $this->session_start();
	}
	
	public function session_start()
	{
		// Get config parameters
		$this->config = $config = $this->readConfig();
		
		if ($name = $this->getSessionNameFromConfig($config))
		{
			// Set session name, if specified
			$this->name = $name;
			session_name($name);
		}
		$this->name = session_name();
		
		// Set expiration, path, and domain
		list($lifetime, $path, $domain) = $this->getCookieParamsFromConfig($config);
		session_set_cookie_params($lifetime, $path, $domain);

		// Start session
		session_start();

		// Check session expiration, ip, and agent
		if($this->isInvalid())
		{
			// Clear old session and start new
			$this->sess_destroy();
			session_start();
		}
		
		// Set activity and access for session validity checks
		$this->updateAccess();
	}

	/**
	 * Save the session data
	 *
	 * @access  public
	 * @return  void
	 */
	public function sess_save()
	{
		// Nothing to do - changes to $_SESSION are automatically saved
	}

	/**
	 * Destroy the current session
	 *
	 * @access  public
	 * @return  void
	 */
	public function sess_destroy()
	{
	    // check if this session is the currently active PHP session
	    $name = session_name();
	    if ($this->name != $name)
	    {
	        throw new Exception(sprintf('Attempt to destroy session "%s", while active PHP session is "%s"', $this->name, $name));
	    }
	    
		// Cleanup session data
		if ($this->data instanceOf Session_Data)
		{
		    $this->data->clear();
		}
		else
		{
		    $_SESSION = array();
		}
		
		$name = session_name();
		if (isset($_COOKIE[$name]))
		{
			// Clear session cookie
			$params = session_get_cookie_params();
			array_unshift($params, $name, '');
			$params['lifetime'] = time() - 42000;
			call_user_func_array('setcookie', $params);
			unset($_COOKIE[$name]);
		}
		session_destroy();
	}

	/**
	 * Regenerate the current session
	 *
	 * Regenerate the session id
	 *
	 * @access  public
	 * @param   boolean	Destroy session data flag (default: false)
	 * @return  void
	 */
	public function sess_regenerate($destroy = false)
	{
		// Just regenerate id, passing destroy flag
		session_regenerate_id($destroy);
	}

	/**
	 * Get a reference to user data array
	 *
	 * @access  public
	 * @return  array	Reference to userdata
	 */
	public function &get_userdata()
	{
		return $this->data;
	}
}
// END Session_Native Class


/* End of file Session_native.php */
/* Location: ./system/libraries/Session/Session.php */
?>
