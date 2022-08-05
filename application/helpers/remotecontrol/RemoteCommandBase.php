<?php

namespace LimeSurvey\Helpers\RemoteControl;

use CDbCriteria;
use Session;
use User;
use Yii;

/**
 * Base class for all remote commands.
 */
abstract class RemoteCommandBase
{
    abstract public function run();

    protected function checkSessionKey($sessionKey)
    {
        $sessionKey = (string) $sessionKey;
        $criteria = new CDbCriteria();
        $criteria->condition = 'expire < ' . time();
        Session::model()->deleteAll($criteria);
        $oResult = Session::model()->findByPk($sessionKey);

        if (is_null($oResult)) {
            return false;
        } else {
            $this->jumpStartSession($oResult->data);
            return true;
        }
    }

    /**
     * @param string $username
     */
    protected function jumpStartSession($username): void
    {
        $userData = User::model()->findByAttributes(array('users_name' => (string) $username))->attributes;

        $session = [
            'loginID'              => intval($userData['uid']),
            'user'                 => $userData['users_name'],
            'full_name'            => $userData['full_name'],
            'htmleditormode'       => $userData['htmleditormode'],
            'templateeditormode'   => $userData['templateeditormode'],
            'questionselectormode' => $userData['questionselectormode'],
            'dateformat'           => $userData['dateformat'],
            'adminlang'            => 'en'
        ];
        foreach ($session as $k => $v) {
            Yii::app()->session[$k] = $v;
        }
        Yii::app()->user->setId($userData['uid']);
    }
}
