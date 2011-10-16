<?php
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * $Id$
 */

require_once 'LS/LS.php';

/**
 *
 * Limesurvey CI session ($_SESSION based)
 *
 * Non-autostart variant
 *
 * @todo remove bind_userdata() from code-base (after this class has been accepted)
 * @todo remove LSCI_Session_Debug seam for production
 * @since 2.0
 */
class LSCI_Session extends LSCI_Session_Debug // LSCI_Session_Base
{
    /**
     * session status.
     *
     * set at instantiation as track flag for lazy-start
     *
     * @var int 0: undefined, 1: none, 2: active
     */
    private $status = 0;

    /**
     * constructor (CI Interface)
     *
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        $this->config = $this->importConfig($params);
        $this->status = 1 + (int) LS_PHP_Session::isActive();
    }

    /**
     * ping
     *
     * event on read/write of session data, start
     * session if not active.
     */
    private function ping()
    {
        if (2 == $this->status) return;

        if (LS_PHP_Session::isActive())
        {
            $this->status = 2; // active
            $this->touch();
        }
        else
        {
            $this->start();
        }
    }

    /**
     * start session
     */
    private function start()
    {
        $config = $this->config;

        if ($config['sess_cookie_name'])
        {
            session_name($config['sess_cookie_name']);
        }

        $this->set_cookie_params();
        $this->status = 2; // active
        session_start();
        $this->touch();
    }

    /**
     * touch session
     *
     * update last active time (and check against configuration, invalidate if necessary)
     */
    private function touch()
    {
        $CI =& get_instance();
        $config = $this->config;
        $expire = $this->getExpiration();

        // Check session expiration, ip, and agent
        $now = time();
        $destroy = FALSE;

        // Initialize store on session
        if (!isset($_SESSION['__CI']))
        {
            $_SESSION = array('__CI' => array()) + $_SESSION;
        }
        
        $store =& $_SESSION['__CI'];
        
        if (isset($store['last_activity']) && ($store['last_activity'] + $expire) < $now)
        {
            // Expired - destroy
            $destroy = TRUE;
        }
        else if ($config['sess_match_ip'] == TRUE && isset($store['ip_address'])
                 && $store['ip_address'] != $CI->input->ip_address())
        {
            // IP doesn't match - destroy
            $destroy = TRUE;
        }
        else if ($config['sess_match_useragent'] == TRUE && isset($store['user_agent'])
                 && $store['user_agent'] != trim(substr($CI->input->user_agent(), 0, 50)))
        {
            // Agent doesn't match - destroy
            $destroy = TRUE;
        }

        // Destroy expired or invalid session
        if ($destroy)
        {
            // Clear old session and start new
            $this->sess_destroy();
            $this->start();
        }

        // Set activity time
        $store['last_activity'] = $now;

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
        
        // Store session name
        $store['name'] = session_name();
    }

    private function set_cookie_params()
    {
        $config = $this->config;

        // Set expiration, path, and domain
        $expire = $config['sess_expire_on_close'] ? 0 : $this->getExpiration();
        $path = $config['cookie_path'] ? $config['cookie_path'] : '/';
        $domain = $config['cookie_domain'] ? $config['cookie_domain'] : '';

        session_set_cookie_params($expire, $path, $domain);
    }

    /**
     * Fetch a specific item in session data (CI Interface)
     *
     * @param string $item
     * @param mixed $default (optional)
     * @return mixed item value or default value if it not set.
     */
    public function userdata($item, $default = FALSE)
    {
        if ($this->status == 1) $this->ping();

        return isset($_SESSION[$item]) ? $_SESSION[$item] : $default;
    }

    /**
     * Add or change item(s) in session data (CI Interface)
     *
     * @param string|array $newdata name of the item or key/value pair(s) of the data
     * @param string|void $newval (optional) value of item or not-set for array mode.
     */
    public function set_userdata($newdata = array(), $newval = NULL)
    {
        if ($this->status == 1) $this->ping();

        if (is_string($newdata))
        {
            $newdata = array($newdata => $newval);
        }

        foreach($newdata as $key => $val)
        {
            $_SESSION[$key] = $val;
        }
    }
    
    /**
     * Delete item(s) in session data (CI Interface)
     *
     */
    public function unset_userdata($newdata = array())
    {
        if ($this->status == 1) $this->ping();

        if (is_string($newdata))
        {
            $newdata = array($newdata => '');
        }

        foreach($newdata as $key => $void)
        {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destroy the current session (CI Interface)
     *
     */
    public function sess_destroy()
    {
        // Cleanup session
        $_SESSION = array();
        LS_PHP_Session::cookieDestroy();
        session_destroy();
        $this->status = 1;
    }
}

/**
 * Base Session class
 */
abstract class LSCI_Session_Base
{
    /**
     * configuration values
     *
     * @var array
     */
    protected $config;

    protected function importConfig(array $params)
    {
        $keys = array('sess_encrypt_cookie', 'sess_expiration', 'sess_expire_on_close', 'sess_match_ip', 'sess_match_useragent', 'sess_cookie_name', 'cookie_path', 'cookie_domain', 'cookie_secure', 'sess_time_to_update', 'time_reference', 'cookie_prefix', 'encryption_key');
        $CI =& get_instance();
        foreach($keys as $key)
        {
            if (!isset($params[$key]))
                $params[$key] = $CI->config->item($key)
            ;
        }
        return $params;
    }

    /**
     * session expiration in seconds
     *
     * @return int
     */
    protected function getExpiration()
    {
        $config = $this->config;
        $expire = 7200;
        if ($config['sess_expiration'] !== FALSE)
        {
            // Default to 2 years if expiration is "0"
            $expire = ($config['sess_expiration'] == 0) ? (60*60*24*365*2) : $config['sess_expiration'];
        }
        return $expire;
    }
}

/**
 * Debug Session seam
 *
 */
class LSCI_Session_Debug extends LSCI_Session_Base
{
    /**
     * Give backtrace and specific exception message when undefined
     * function is called.
     *
     * @throws Exception
     */
    public function __call($func, $params)
    {
        echo 'Debug Session Handler. Please report this exception (see below stacktrace) to limsurvey-dev/mot2.';
        echo '<pre>', debug_print_backtrace(), '</pre>';
        throw new Exception(sprintf('LSCI_Session: Undefined "%s()" (Params: %s)', $func, print_r($params, true)));
    }

    /**
     * Re-bind $_SESSION to userdata (Native Driver Interface)
     *
     * void for LSCI_Session, kept for Session Driver in-placement
     */
    public function bind_userdata() {}
}