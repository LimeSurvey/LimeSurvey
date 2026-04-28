<?php

/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

/**
* Sends email to tokens - invitations, reminders, registers, and confirmations
* Seems used only in remote_control : maybe move it to ?
* @param integer $iSurveyID
* @param CActiveRecord[]  $aResultTokens
* @param string $sType type of notification invite|register|remind|confirm
* @param bool $continueOnError Don't stop on first invalid participant
* @return array of results
*/
function emailTokens($iSurveyID, $aResultTokens, $sType, $continueOnError = false)
{
    if (!in_array($sType, ['invite','remind','register','confirm'])) {
        throw new Exception('Invalid email type');
    }
    $oSurvey = Survey::model()->findByPk($iSurveyID);
    $mail = \LimeMailer::getInstance(\LimeMailer::ResetComplete);
    $mail->setSurvey($iSurveyID);
    $mail->emailType = $sType;
    $mail->replaceTokenAttributes = true;
    foreach ($aResultTokens as $index => $aTokenRow) {
        $mail = \LimeMailer::getInstance();
        $mail->setToken($aTokenRow['token']);
        $mail->index = $index;
        $mail->setTypeWithRaw($sType, $aTokenRow['language']);

        if (isset($aTokenRow['validfrom']) && trim((string) $aTokenRow['validfrom']) != '' && convertDateTimeFormat($aTokenRow['validfrom'], 'Y-m-d H:i:s', 'U') * 1 > date('U') * 1) {
            $aResult[$aTokenRow['tid']] = array(
                'name' => $aTokenRow["firstname"] . " " . $aTokenRow["lastname"],
                'email' => $aTokenRow["email"],
                'status' => 'fail',
                'warning' => null,
                'error' => 'Token not valid yet'
            );
            if ($continueOnError) {
                continue;
            } else {
                break 1;
            }
        }
        if (isset($aTokenRow['validuntil']) && trim((string) $aTokenRow['validuntil']) != '' && convertDateTimeFormat($aTokenRow['validuntil'], 'Y-m-d H:i:s', 'U') * 1 < date('U') * 1) {
            $aResult[$aTokenRow['tid']] = array(
                'name' => $aTokenRow["firstname"] . " " . $aTokenRow["lastname"],
                'email' => $aTokenRow["email"],
                'status' => 'fail',
                'warning' => null,
                'error' => 'Token not valid anymore'
            );
            if ($continueOnError) {
                continue;
            } else {
                break 1;
            }
        }
        if ($mail->sendMessage()) {
            $warnings = null;
            $oToken = Token::model($iSurveyID)->findByPk($aTokenRow['tid']);
            if ($sType == 'invite' || $sType == 'register') {
                $oToken->sent = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"));
                if (!$oToken->save(true, ['sent'])) {
                    $warnings = $oToken->getErrors();
                }
            }
            if ($sType == 'remind') {
                $oToken->remindersent = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"));
                $oToken->remindercount++;
                if (!$oToken->save(true, ['remindersent', 'remindercount'])) {
                    $warnings = $oToken->getErrors();
                }
            }
            $aResult[$aTokenRow['tid']] = array(
                'name' => $aTokenRow["firstname"] . " " . $aTokenRow["lastname"],
                'email' => $aTokenRow["email"],
                'status' => 'OK',
                'warning' => $warnings,
                'error' => null
            );
        } else {
            $aResult[$aTokenRow['tid']] = array(
                'name' => $aTokenRow["firstname"] . " " . $aTokenRow["lastname"],
                'email' => $aTokenRow["email"],
                'status' => 'fail',
                'warning' => null,
                'error' => $mail->getError()
            );
        }
    }
    return $aResult;
}
