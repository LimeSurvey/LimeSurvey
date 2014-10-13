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
* Creates the basic token table for a survey
*
* @param mixed $iSurveyID
* @param mixed $aAttributeFields
* @return False if failed , else DB object
*/
function createTokenTable($iSurveyID, $aAttributeFields=array())
{
    Yii::app()->loadHelper('database');
    $fields = array(
    'tid' => 'pk',
    'participant_id' => 'varchar(50)',
    'firstname' => 'varchar(40)',
    'lastname' => 'varchar(40)',
    'email' => 'text',
    'emailstatus' => 'text',
    'token' => 'varchar(35)',
    'language' => 'varchar(25)',
    'blacklisted' => 'varchar(17)',
    'sent' => "varchar(17) DEFAULT 'N'",
    'remindersent' => "varchar(17) DEFAULT 'N'",
    'remindercount' => 'integer DEFAULT 0',
    'completed' => "varchar(17) DEFAULT 'N'",
    'usesleft' => 'integer DEFAULT 1',
    'validfrom' => 'datetime',
    'validuntil' => 'datetime',
    'mpid' => 'integer'
    );
    foreach ($aAttributeFields as $sAttributeField)
    {
        $fields[$sAttributeField]='string';
    }

    if (Yii::app()->db->driverName=='mssql' || Yii::app()->db->driverName=='sqlsrv' || Yii::app()->db->driverName=='dblib')
    {
        $fields = array(
            'tid' => 'pk',
            'participant_id' => 'varchar(50)',
            'firstname' => 'nvarchar(40)',
            'lastname' => 'nvarchar(40)',
            'email' => 'ntext',
            'emailstatus' => 'ntext',
            'token' => 'varchar(35)',
            'language' => 'varchar(25)',
            'blacklisted' => 'varchar(17)',
            'sent' => "varchar(17) DEFAULT 'N'",
            'remindersent' => "varchar(17) DEFAULT 'N'",
            'remindercount' => 'integer DEFAULT 0',
            'completed' => "varchar(17) DEFAULT 'N'",
            'usesleft' => 'integer DEFAULT 1',
            'validfrom' => 'datetime',
            'validuntil' => 'datetime',
            'mpid' => 'integer'
        );
        foreach ($aAttributeFields as $sAttributeField)
        {
            $fields[$sAttributeField]='nvarchar(255)';
        }
    }

    try{
        $sTableName="{{tokens_".intval($iSurveyID)."}}";
        createTable($sTableName, $fields);
        try{
            Yii::app()->db->createCommand()->createIndex("idx_token_token_{$iSurveyID}_".rand(1,50000),"{{tokens_".intval($iSurveyID)."}}",'token');
        } catch(Exception $e) {}
        // create fields for the custom token attributes associated with this survey
        $tokenattributefieldnames = Survey::model()->findByPk($iSurveyID)->tokenAttributes;
        foreach($tokenattributefieldnames as $attrname=>$attrdetails)
        {
            if (isset($fields[$attrname])) continue; // Field was already created
            Yii::app()->db->createCommand(Yii::app()->db->getSchema()->addColumn("{{tokens_".intval($iSurveyID)."}}", $attrname, 'VARCHAR(255)'))->execute();
        }
        Yii::app()->db->schema->getTable($sTableName, true); // Refresh schema cache just in case the table existed in the past
        return true;
    } catch(Exception $e) {
        return false;
    }

}


/**
* Sends email to tokens - invitation and reminders
*
* @param mixed $iSurveyID
* @param array  $aResultTokens
* @param string $sType type of notification invite|remind
* @return array of results
*/
function emailTokens($iSurveyID,$aResultTokens,$sType)
{
	Yii::app()->loadHelper('common');
	$oSurvey = Survey::model()->findByPk($iSurveyID);
	if (getEmailFormat($iSurveyID) == 'html')
		$bHtml = true;
	else
		$bHtml = false;

	$attributes = array_keys(getTokenFieldsAndNames($iSurveyID));
	$oSurveyLocale=SurveyLanguageSetting::model()->findAllByAttributes(array('surveyls_survey_id' => $iSurveyID));
	$oTokens = Token::model($iSurveyID);
	$aSurveyLangs = $oSurvey->additionalLanguages;
	array_unshift($aSurveyLangs, $oSurvey->language);

	//Convert result to associative array to minimize SurveyLocale access attempts
	foreach($oSurveyLocale as $rows)
	{
		$oTempObject=array();
		foreach($rows as $k=>$v)
		{
			$oTempObject[$k] = $v;
		}
		$aSurveyLocaleData[$rows['surveyls_language']]=$oTempObject;
	}

	foreach ($aResultTokens as $aTokenRow)
	{
		//Select language
		$aTokenRow['language'] = trim($aTokenRow['language']);
		$found = array_search($aTokenRow['language'], $aSurveyLangs);
		if ($aTokenRow['language'] == '' || $found == false)
		{
			$aTokenRow['language'] = $oSurvey['language'];
		}
		$sTokenLanguage = $aTokenRow['language'];


		//Build recipient
		$to = array();
		$aEmailaddresses = explode(';', $aTokenRow['email']);
		foreach ($aEmailaddresses as $sEmailaddress)
		{
			$to[] = ($aTokenRow['firstname'] . " " . $aTokenRow['lastname'] . " <{$sEmailaddress}>");
		}


		//Populate attributes
		$fieldsarray["{SURVEYNAME}"] = $aSurveyLocaleData[$sTokenLanguage]['surveyls_title'];
		if ($fieldsarray["{SURVEYNAME}"] == '')
			$fieldsarray["{SURVEYNAME}"] = $aSurveyLocaleData[$oSurvey['language']]['surveyls_title'];

		$fieldsarray["{SURVEYDESCRIPTION}"] = $aSurveyLocaleData[$sTokenLanguage]['surveyls_description'];
		if ($fieldsarray["{SURVEYDESCRIPTION}"] == '')
			$fieldsarray["{SURVEYDESCRIPTION}"] = $aSurveyLocaleData[$oSurvey['language']]['surveyls_description'];

		$fieldsarray["{ADMINNAME}"] = $oSurvey['admin'];
		$fieldsarray["{ADMINEMAIL}"] = $oSurvey['adminemail'];
		$from =  $fieldsarray["{ADMINEMAIL}"];
		if($from ==  '')
			$from = Yii::app()->getConfig('siteadminemail');

		foreach ($attributes as $attributefield)
		{
			$fieldsarray['{' . strtoupper($attributefield) . '}'] = $aTokenRow[$attributefield];
			$fieldsarray['{TOKEN:'.strtoupper($attributefield).'}']=$aTokenRow[$attributefield];
		}

		//create urls
		$fieldsarray["{OPTOUTURL}"] = Yii::app()->getController()->createAbsoluteUrl("/optout/tokens/langcode/" . trim($aTokenRow['language']) . "/surveyid/{$iSurveyID}/token/{$aTokenRow['token']}");
		$fieldsarray["{OPTINURL}"] = Yii::app()->getController()->createAbsoluteUrl("/optin/tokens/langcode/" . trim($aTokenRow['language']) . "/surveyid/{$iSurveyID}/token/{$aTokenRow['token']}");
		$fieldsarray["{SURVEYURL}"] = Yii::app()->getController()->createAbsoluteUrl("/survey/index/sid/{$iSurveyID}/token/{$aTokenRow['token']}/lang/" . trim($aTokenRow['language']) . "/");

		if($bHtml)
		{
			foreach(array('OPTOUT', 'OPTIN', 'SURVEY') as $key)
			{
				$url = $fieldsarray["{{$key}URL}"];
				$fieldsarray["{{$key}URL}"] = "<a href='{$url}'>" . htmlspecialchars($url) . '</a>';
				if ($key == 'SURVEY')
				{
					$barebone_link = $url;
				}
			}
		}

		//mail headers
		$customheaders = array('1' => "X-surveyid: " . $iSurveyID,'2' => "X-tokenid: " . $fieldsarray["{TOKEN}"]);

		global $maildebug;

		//choose appriopriate email message
		if($sType == 'invite')
		{
			$sSubject = $aSurveyLocaleData[$sTokenLanguage]['surveyls_email_invite_subj'];
			$sMessage = $aSurveyLocaleData[$sTokenLanguage]['surveyls_email_invite'];
		}
		else
		{
			$sSubject = $aSurveyLocaleData[$sTokenLanguage]['surveyls_email_remind_subj'];
			$sMessage = $aSurveyLocaleData[$sTokenLanguage]['surveyls_email_remind'];
		}

		$modsubject = Replacefields($sSubject, $fieldsarray);
		$modmessage = Replacefields($sMessage, $fieldsarray);

		if (isset($barebone_link))
		{
			$modsubject = str_replace("@@SURVEYURL@@", $barebone_link, $modsubject);
			$modmessage = str_replace("@@SURVEYURL@@", $barebone_link, $modmessage);
		}




		if (isset($aTokenRow['validfrom']) && trim($aTokenRow['validfrom']) != '' && convertDateTimeFormat($aTokenRow['validfrom'], 'Y-m-d H:i:s', 'U') * 1 > date('U') * 1)
		{
		   $aResult[$aTokenRow['tid']] =  array('name'=>$fieldsarray["{FIRSTNAME}"]." ".$fieldsarray["{LASTNAME}"],
												'email'=>$fieldsarray["{EMAIL}"],
												'status'=>'fail',
												'error'=>'Token not valid yet');

		}
		elseif (isset($aTokenRow['validuntil']) && trim($aTokenRow['validuntil']) != '' && convertDateTimeFormat($aTokenRow['validuntil'], 'Y-m-d H:i:s', 'U') * 1 < date('U') * 1)
		{
		   $aResult[$aTokenRow['tid']] =  array('name'=>$fieldsarray["{FIRSTNAME}"]." ".$fieldsarray["{LASTNAME}"],
												'email'=>$fieldsarray["{EMAIL}"],
												'status'=>'fail',
												'error'=>'Token not valid anymore');

		}
		else
		{
			if (SendEmailMessage($modmessage, $modsubject, $to, $from, Yii::app()->getConfig("sitename"), $bHtml, getBounceEmail($iSurveyID), null, $customheaders))
			{
			   $aResult[$aTokenRow['tid']] =  array('name'=>$fieldsarray["{FIRSTNAME}"]." ".$fieldsarray["{LASTNAME}"],
													'email'=>$fieldsarray["{EMAIL}"],
													'status'=>'OK');

				if($sType == 'invite')
					$oTokens->updateByPk($aTokenRow['tid'], array('sent' => dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"))));

				if($sType == 'remind')
				{
					$iRCount = $oTokens->findByPk($aTokenRow['tid'])->remindercount +1;
					$oTokens->updateByPk($aTokenRow['tid'], array('remindersent' => dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"))));
					$oTokens->updateByPk($aTokenRow['tid'],array('remindercount' => $iRCount));
				 }

			}
			else
			{

			   $aResult[$aTokenRow['tid']] =  array('name'=>$fieldsarray["{FIRSTNAME}"]." ".$fieldsarray["{LASTNAME}"],
													'email'=>$fieldsarray["{EMAIL}"],
													'status'=>'fail',
													'error'=>$maildebug);
			}
		}

		unset($fieldsarray);
	}


	return $aResult;
}



