<?php

namespace LimeSurvey\Helpers\RemoteControl\Commands;

use LimeSurvey\Helpers\RemoteControl\RemoteCommandBase;

/**
 * List the survey groups belonging to a user
 *
 * If user is admin he can get survey groups of every user (parameter sUser) or all survey groups (sUser=null)
 * Else only the survey groups belonging to the user requesting will be shown.
 *
 * Returns array with survey group attributes
 *
 * @access public
 * @param string $sSessionKey Auth credentials
 * @param string|null $sUsername (optional) username to get list of survey groups
 * @return array In case of success the list of survey groups
 */
class ListSurveyGroupsRemoteCommand extends RemoteCommandBase
{
    public function run()
    {
        if ($this->checkSessionKey($sSessionKey)) {
            $oSurveyGroup = new SurveysGroups();
            if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
                $sOwner = Yii::app()->user->getId();
            } elseif ($sUsername != null) {
                $aUserData = User::model()->findByAttributes(['users_name' => (string) $sUsername]);
                if (!isset($aUserData)) {
                    return ['status' => 'Invalid user'];
                } else {
                    $sOwner = $aUserData->attributes['uid'];
                }
            }

            if (empty($sOwner)) {
                $aUserSurveyGroups = $oSurveyGroup->findAll();
            } else {
                $aUserSurveyGroups = $oSurveyGroup->findAllByAttributes(['owner_id' => $sOwner]);
            }
            if (count($aUserSurveyGroups) == 0) {
                return ['status' => 'No survey groups found'];
            }

            foreach ($aUserSurveyGroups as $oSurveyGroup) {
                $aData[] = $oSurveyGroup->attributes;
            }
            return $aData;
        } else {
            return ['status' => self::INVALID_SESSION_KEY];
        }
    }
}
