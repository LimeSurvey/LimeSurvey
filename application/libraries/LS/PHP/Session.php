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

/**
 * PHP Session Helper Class
 *
 * Inspection of PHP session, does not implement any abstraction
 * or application logic.
 */
class LS_PHP_Session
{
    /**
     * Is PHP session active?
     *
     * NOTE: PHP 5.4: use session_status();
     *
     * @throws UnexpectedValueException
     * @return bool
     */
    public function isActive()
    {
        $setting = 'session.use_trans_sid';
        $current = ini_get($setting);
        if (FALSE === $current)
        {
            throw new UnexpectedValueException(sprintf('Setting %s does not exists.', $setting));
        }
        $testate = "mix$current$current";
        $old = @ini_set($setting, $testate);
        $peek = @ini_set($setting, $current);
        $result = $peek === $current || $peek === FALSE;
        return $result;
    }

    /**
     * Change to a new session name if different to current.
     *
     * Handles if an existing session is active and write
     * it's changes.
     *
     * Assumes session is using cookies and imports session id
     * for the new session if applicable.
     *
     * Does not start the new session, just does everything
     * that the session with the new name can be started
     * w/o loosing any data.
     *
     * @param string $newName name of the new session
     * @return bool false if there was no change, true if there was
     */
    public function changeTo($newName)
    {
        $isActive = $this->isActive();
        $currentName = session_name();
        $currentId = session_id();

        // if the name is the same, there is nothing to change
        if ($currentName === $newName) return FALSE;

        // active session is comitted
        if ($isActive) session_write_close();

        // as the name changes, the id needs to change, too
        $newId = $this->getRequestSessionId($newName);

        if (NULL === $newId  &&  $currentId !== '')
        {
            $newId = $this->generateSessionId($currentId);
        }

        // set new session name and id
        session_name($newName);
        (NULL === $newId) || session_id($newId);
        
        return TRUE;
    }

    /**
     * Destroy Session Cookie
     *
     * @param string $sessionName (optional) defaults to session_name()
     * @param array $cookieParams (optional) defaults to session_get_cookie_params()
     */
    public function cookieDestroy($sessionName = NULL, array $cookieParams = NULL)
    {
        if (NULL === $sessionName)
            $sessionName = session_name();

        if (NULL === $cookieParams)
        {
            $params = session_get_cookie_params();
        }
        else
        {
            $params = $cookieParams;
        }
        
        $lifetime = array_shift($params);
        $lifetime = time() - 42000;
        array_unshift($params, $sessionName, '', $lifetime);
        call_user_func_array('setcookie', $params);
    }

    /**
     * Generate a new session id string
     *
     * Currently only creating a new one that is not the current one, however, this
     * is not safe.
     *
     * @todo implement php_session_create_id
     * @link http://lxr.php.net/opengrok/xref/PHP_5_3/ext/session/session.c#345
     * @link http://www.php.net/manual/en/session.configuration.php#ini.session.hash-function
     *
     * @param string $currentId
     * @return string
     */
    public function generateSessionId($currentId)
    {
        // lazy style re-generation, this ensures the new
        // id is in the same format like the old, but
        // it's not optimal. See docblock.
        $sessionId = $currentId = (string) $currentId;
        $i = 0;
        while ($sessionId === $currentId && $i++ <10)
            $sessionId = str_shuffle($sessionId);

        if ($sessionId === $currentId)
            throw new InvalidArgumentException(sprintf('Failed. Current "%s" could not resolve not new.', $currentId));

        return $sessionId;
    }

    /**
     * get session id for a session name from the current request.
     *
     * @param string $sessionName
     * @return string|NULL
     */
    public function getRequestSessionId($sessionName)
    {
        $sessionId = NULL;

        if (isset($_COOKIE[$sessionName]))
        {
            $cookieId = $_COOKIE[$sessionName];
            if ($this->isValidId($cookieId))
                $sessionId = $cookieId;
        }

        return $sessionId;
    }
    
    /**
     * validate string session id
     *
     * @param string $sessionId
     * @return bool
     */
    public function isValidId($sessionId)
    {
        $strId = (string) $sessionId;

        if ($strId !== $sessionId) return FALSE;

        return (bool) preg_match('/^[0-9a-zA-Z,-]{22,40}$/', $strId);
    }
}