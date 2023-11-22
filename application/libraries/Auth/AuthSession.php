<?php

namespace LimeSurvey\Auth;

use User;

class AuthSession
{
    /**
     * @param string $username The username
     * @return bool
     */
    public function initSessionByUsername($username)
    {
        $oUser = \User::model()->findByAttributes(array('users_name' => $username));

        if (!$oUser) {
            return false;
        }

        $this->initSession($oUser);

        return true;
    }

    /**
     * @param string $uid The user id
     * @return bool
     */
    public function initSessionByUid($uid)
    {
        $oUser = \User::model()->findByAttributes(array('uid' => $uid));

        if (!$oUser) {
            return false;
        }

        $this->initSession($oUser);

        return true;
    }

    /**
     * Fills the session with necessary user info on the fly
     *
     * @param string $username The username
     * @return bool
     */
    public function initSession(User $oUser)
    {
        $aUserData = $oUser->attributes;

        /** @var \LSYii_Application */
        $app = \Yii::app();

        $session = array(
            'loginID' => intval($aUserData['uid']),
            'user' => $aUserData['users_name'],
            'full_name' => $aUserData['full_name'],
            'htmleditormode' => $aUserData['htmleditormode'],
            'templateeditormode' => $aUserData['templateeditormode'],
            'questionselectormode' => $aUserData['questionselectormode'],
            'dateformat' => $aUserData['dateformat'],
            'adminlang' => 'en'
        );
        foreach ($session as $k => $v) {
            $app->session[$k] = $v;
        }
        $app->user->setId($aUserData['uid']);

        return true;
    }
}
