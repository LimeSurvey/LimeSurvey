<?php

namespace LimeSurvey\Api\Authentication;

use User;
use Yii;

class SessionUtil
{
    /**
     * Initializes Yii PHP Session data for a given username.
     *
     * @param string $username The username
     * @return bool
     */
    public function jumpStartSession($username)
    {
        $oUser = User::model()->findByAttributes(
            array('users_name' => $username)
        );

        if (!$oUser) {
            return false;
        }

        $aUserData = $oUser->attributes;

        /** @var \LSYii_Application */
        $app = Yii::app();

        $session = array(
            'loginID' => intval($aUserData['uid']),
            'user' => $aUserData['users_name'],
            'full_name' => $aUserData['full_name'],
            'htmleditormode' => $aUserData['htmleditormode'],
            'templateeditormode' => $aUserData['templateeditormode'],
            'questionselectormode' => $aUserData['questionselectormode'],
            // When using the REST API, data is transferred using the format
            // YYYY-MM-DD since the browser handles formatting for display.
            // This format is defined as '6' in
            // insurveytranslator_helper.php / getDateFormatData()
            'dateformat' => 6,
            'adminlang' => 'en'
        );
        foreach ($session as $k => $v) {
            $app->session[$k] = $v;
        }
        $app->user->setId($aUserData['uid']);

        return true;
    }
}
