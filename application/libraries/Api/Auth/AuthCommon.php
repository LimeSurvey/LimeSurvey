<?php

namespace LimeSurvey\Api\Auth;

use LSUserIdentity;
use PluginEvent;

class AuthCommon
{
    /**
     * Get identity from username and password
     *
     * @param string $sUsername username
     * @param string $sPassword password
     * @param string $sPlugin plugin to be used
     * @return LSUserIdentity
     */
    public function getIdentity($sUsername, $sPassword, $sPlugin = 'Authdb')
    {
        $identity = new LSUserIdentity($sUsername, $sPassword);
        $identity->setPlugin($sPlugin);
        $event = new PluginEvent('remoteControlLogin');
        $event->set('identity', $identity);
        $event->set('plugin', $sPlugin);
        $event->set('username', $sUsername);
        $event->set('password', $sPassword);
        App()->getPluginManager()->dispatchEvent($event, array($sPlugin));
        return $identity;
    }

    /**
     * Verify username and password
     *
     * @param string $sUsername username
     * @param string $sPassword password
     * @param string $sPlugin plugin to be used
     * @return bool|string
     */
    public function verifyUsernameAndPassword($sUsername, $sPassword, $sPlugin = 'Authdb')
    {
        $identity = $this->getIdentity(
            $sUsername,
            $sPassword,
            $sPlugin
        );
        return !$identity->authenticate();
    }
}
