<?php

namespace LimeSurvey\Auth;

class AuthCommon
{
    /**
     * Login with username and password
     *
     * @param string $sUsername username
     * @param string $sPassword password
     * @param string $sPlugin plugin to be used
     * @return \LsUserIdentity
     */
    public function getIdentity($sUsername, $sPassword, $sPlugin = 'Authdb')
    {
        /* @var $identity LSUserIdentity */
        $identity = new \LSUserIdentity($sUsername, $sPassword);
        $identity->setPlugin($sPlugin);
        $event = new \PluginEvent('remoteControlLogin');
        $event->set('identity', $identity);
        $event->set('plugin', $sPlugin);
        $event->set('username', $sUsername);
        $event->set('password', $sPassword);
        App()->getPluginManager()->dispatchEvent($event, array($sPlugin));
        return $identity;
    }
}
