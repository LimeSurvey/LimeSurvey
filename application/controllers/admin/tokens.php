<?php if ( !defined('BASEPATH')) exit('No direct script access allowed');
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
 * 	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
 */

/**
 * Tokens Controller
 *
 * This controller performs token actions
 *
 * @package		LimeSurvey
 * @subpackage	Backend
 */
class tokens extends Survey_Common_Action
{

    public function run($sa = '')
    {
        switch ($sa)
        {
            case 'addnew' :
            case 'bounceprocessing' :
            case 'bouncesettings' :
            case 'delete' :
            case 'exportdialog' :
            case 'getSearch_json' :
            case 'getTokens_json' :
            case 'editToken' :
            case 'import' :
            case 'importldap' :
            case 'kill' :
            case 'managetokenattributes' :
            case 'tokenify' :
            case 'updatetokenattributes' :
            case 'updatetokenattributedescriptions' :
                $this->route($sa, array('surveyid'));
                break;
            case 'email' :
                $this->route('email', array('surveyid', 'tokenids'));
                break;
            case 'adddummies' :
                $this->route('addDummies', array('surveyid', 'subaction'));
                break;
            case 'browse' :
                $this->route('browse', array('surveyid', 'limit', 'start', 'order', 'searchstring'));
                break;
            case 'edit' :
                $this->route('edit', array('surveyid', 'tokenid'));
                break;
            case 'index' :
            default :
                $this->route('index', array('surveyid'));
                break;
        }
	}

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    function _renderWrappedTemplate($aViewUrls = array(), $aData = array())
    {
        $aData['imageurl'] = Yii::app()->getConfig('imageurl');
        $aData['display']['menu_bars'] = false;
        parent::_renderWrappedTemplate('token', $aViewUrls, $aData);
    }

	/**
	 * Show token index page, handle token database
	 */
    function index($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $clang = $this->getController()->lang;

        if (!bHasSurveyPermission($iSurveyId, 'tokens', 'read'))
        {
            die("no permissions"); // TODO Replace
        }

        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . "admin/tokens.js");

        Yii::app()->loadHelper("surveytranslator");

        //$dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
        $thissurvey = getSurveyInfo($iSurveyId);

        if ($thissurvey === false)
        {
            die($clang->gT("The survey you selected does not exist")); // TODO Replace
        }

        $aData['surveyprivate'] = $thissurvey['anonymized'];

        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        else
        {
            $aData['thissurvey'] = $thissurvey;
            $aData['surveyid'] = $iSurveyId;
            $aData['queries'] = Tokens_dynamic::model($iSurveyId)->summary();

            $this->_renderWrappedTemplate(array('tokenbar', 'tokensummary'), $aData);
        }
    }

    /**
     * tokens::bounceprocessing()
     *
     * @return void
     */
    function bounceprocessing($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $clang = $this->getController()->lang;
        $thissurvey = getSurveyInfo($iSurveyId);

        if ($thissurvey['bounceprocessing'] != 'N' && !($thissurvey['bounceprocessing'] == 'G' && getGlobalSetting('bounceaccounttype') == 'off') && bHasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            $bouncetotal = 0;
            $checktotal = 0;
            if ($thissurvey['bounceprocessing'] == 'G')
            {
                $accounttype = getGlobalSetting('bounceaccounttype');
                $hostname = getGlobalSetting('bounceaccounthost');
                $username = getGlobalSetting('bounceaccountuser');
                $pass = getGlobalSetting('bounceaccountpass');
                $hostencryption = getGlobalSetting('bounceencryption');
            }
            else
            {
                $accounttype = $thissurvey['bounceaccounttype'];
                $hostname = $thissurvey['bounceaccounthost'];
                $username = $thissurvey['bounceaccountuser'];
                $pass = $thissurvey['bounceaccountpass'];
                $hostencryption = $thissurvey['bounceaccountencryption'];
            }

            @list($hostname, $port) = split(':', $hostname);
            if (empty($port))
            {
                if ($accounttype == "IMAP")
                {
                    switch ($hostencryption)
                    {
                        case "Off":
                            $hostname = $hostname . ":143";
                            break;
                        case "SSL":
                            $hostname = $hostname . ":993";
                            break;
                        case "TLS":
                            $hostname = $hostname . ":993";
                            break;
                    }
                }
                else
                {
                    switch ($hostencryption)
                    {
                        case "Off":
                            $hostname = $hostname . ":110";
                            break;
                        case "SSL":
                            $hostname = $hostname . ":995";
                            break;
                        case "TLS":
                            $hostname = $hostname . ":995";
                            break;
                    }
                }
            }

            $flags = "";
            switch ($accounttype)
            {
                case "IMAP":
                    $flags.="/imap";
                    break;
                case "POP":
                    $flags.="/pop3";
                    break;
            }
            switch ($hostencryption) // novalidate-cert to have personal CA , maybe option.
            {
                case "SSL":
                    $flags.="/ssl/novalidate-cert";
                    break;
                case "TLS":
                    $flags.="/tls/novalidate-cert";
                    break;
            }

            if ($mbox = @imap_open('{' . $hostname . $flags . '}INBOX', $username, $pass))
            {
                imap_errors();
                $count = imap_num_msg($mbox);
                $lasthinfo = imap_headerinfo($mbox, $count);
                $datelcu = strtotime($lasthinfo->date);
                $datelastbounce = $datelcu;
                $lastbounce = $thissurvey['bouncetime'];
                while ($datelcu > $lastbounce)
                {
                    @$header = explode("\r\n", imap_body($mbox, $count, FT_PEEK)); // Don't put read
                    foreach ($header as $item)
                    {
                        if (preg_match('/^X-surveyid/', $item))
                        {
                            $iSurveyIdBounce = explode(": ", $item);
                        }
                        if (preg_match('/^X-tokenid/', $item))
                        {
                            $tokenBounce = explode(": ", $item);
                            if ($iSurveyId == $iSurveyIdBounce[1])
                            {
                                $aData = array(
                                    'emailstatus' => 'bounced'
                                );
                                $condn = array('token' => $tokenBounce[1]);

                                $record = Tokens_dynamic::model($iSurveyId)->findByAttributes($condn);
                                foreach ($aData as $k => $v)
                                    $record->$k = $v;
                                $record->save();

                                $readbounce = imap_body($mbox, $count); // Put read
                                if (isset($thissurvey['bounceremove']) && $thissurvey['bounceremove']) // TODO Y or just true, and a imap_delete
                                {
                                    $deletebounce = imap_delete($mbox, $count); // Put delete
                                }
                                $bouncetotal++;
                            }
                        }
                    }
                    $count--;
                    @$lasthinfo = imap_headerinfo($mbox, $count);
                    @$datelc = $lasthinfo->date;
                    $datelcu = strtotime($datelc);
                    $checktotal++;
                    @imap_close($mbox);
                }
                $condn = array('sid' => $iSurveyId);
                $survey = Survey::model()->findByAttributes($condn);
                $survey->bouncetime = $datelistbounce;
                $survey->save();

                if ($bouncetotal > 0)
                {
                    printf($clang->gT("%s messages were scanned out of which %s were marked as bounce by the system."), $checktotal, $bouncetotal);
                }
                else
                {
                    printf($clang->gT("%s messages were scanned, none were marked as bounce by the system."), $checktotal);
                }
            }
            else
            {
                $clang->eT("Please check your settings");
            }
        }
        else
        {
            $clang->eT("We are sorry but you don't have permissions to do this.");
        }

        exit; // if bounceprocessing : javascript : no more todo
    }

    /**
     * Browse Tokens
     */
    function browse($iSurveyId, $limit = 50, $start = 0, $order = false, $searchstring = false)
    {
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . "admin/tokens.js");
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . "jquery/jqGrid/js/i18n/grid.locale-en.js");
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . "jquery/jqGrid/js/jquery.jqGrid.min.js");
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('generalscripts') . "jquery/css/jquery.multiselect.css");
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('generalscripts') . "jquery/css/jquery.multiselect.filter.css");
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('generalscripts') . "jquery/jqGrid/css/ui.jqgrid.css");
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('generalscripts') . "jquery/jqGrid/css/jquery.ui.datepicker.css");
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('styleurl')       . "admin/default/displayParticipants.css");

        Yii::app()->loadHelper('surveytranslator');
        Yii::import('application.libraries.Date_Time_Converter', true);
        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);

        $clang = $this->getController()->lang;
        $iSurveyId = sanitize_int($iSurveyId);
        $limit = (int) $limit;
        $start = (int) $start;
        $tkcount = Tokens_dynamic::model($iSurveyId)->count();
        $next = $start + $limit;
        $last = $start - $limit;
        $end = $tkcount - $limit;

        if ($end < 0)
        {
            $end = 0;
        }
        if ($last < 0)
        {
            $last = 0;
        }
        if ($next >= $tkcount)
        {
            $next = $tkcount - $limit;
        }
        if ($end < 0)
        {
            $end = 0;
        }

        $sBaseLanguage = GetBaseLanguageFromSurveyID($iSurveyId);
        $aData['next'] = $next;
        $aData['last'] = $last;
        $aData['end'] = $end;
        $limit = CHttpRequest::getPost('limit');
        $start = CHttpRequest::getPost('start');
        $searchstring = CHttpRequest::getPost('searchstring');
        $order = CHttpRequest::getPost('order');
        $order = preg_replace('/[^_ a-z0-9-]/i', '', $order);
        if ($order == '')
        {
            $order = 'tid';
        }

        $iquery = '';
        if (!empty($searchstring))
        {
            $idata = array("firstname", "lastname", "email", "emailstatus", "token");
            $iquery = array();
            foreach ($idata as $k)
                $iquery[] = $k . ' LIKE "' . $searchstring . '%"';
            $iquery = '(' . implode(' OR ', $iquery) . ')';
        }

        $tokens = Tokens_dynamic::model($iSurveyId)->findAll(array('condition' => $iquery, 'limit' => $limit, 'offset' => $start, 'order' => $order));
        $aData['bresult'] = array();
        foreach ($tokens as $token)
        {
            $aData['bresult'][] = $token->attributes;
        }

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['searchstring'] = $searchstring;
        $aData['surveyid'] = $iSurveyId;
        $aData['bgc'] = "";
        $aData['limit'] = $limit;
        $aData['start'] = $start;
        $aData['order'] = $order;
        $aData['surveyprivate'] = $aData['thissurvey']['anonymized'];
        $aData['dateformatdetails'] = $dateformatdetails;

        $this->_renderWrappedTemplate(array('tokenbar', 'browse'), $aData);
    }

    /**
     * This function sends the shared participant info to the share panel using JSON encoding
     * This function is called after the share panel grid is loaded
     * This function returns the json depending on the user logged in by checking it from the session
     * @param it takes the session user data loginID
     * @return JSON encoded string containg sharing information
     */
    function getTokens_json($iSurveyId)
    {
        $clang = $this->getController()->lang;
        $page  = CHttpRequest::getPost('page');
        $limit = CHttpRequest::getPost('rows');

        $tokens = Tokens_dynamic::model($iSurveyId)->findAll();

        $aData->page = $page;
        $aData->records = count($tokens);
        $aData->total = ceil($aData->records / $limit);

        for ($i = 0, $j = ($page - 1) * $limit; $i < $limit && $j < $aData->records; $i++, $j++)
        {
            $token = $tokens[$j];
            if ((int) $token['validfrom'])
                $token['validfrom'] = date('m/d/Y', strtotime(trim($token['validfrom'])));
            else
                $token['validfrom'] = '';
            if ((int) $token['validuntil'])
                $token['validuntil'] = date('m/d/Y', strtotime(trim($token['validuntil'])));
            else
                $token['validuntil'] = '';

            $aData->rows[$i]['id'] = $token['tid'];
            $action = '<input type="image" src="' . Yii::app()->getConfig('imageurl') . '/do_16.png" title="' . $clang->gT("Do survey") . '" alt="' . $clang->gT("Do survey") . '" onclick=\'window.open("' . Yii::app()->createUrl("optin/local/surveyid/{$iSurveyId}/token/{$token['token']}") . '", "_blank")\'>';
            $action .= '<input type="image" src="' . Yii::app()->getConfig('imageurl') . '/token_delete.png" title="' . $clang->gT("Delete token entry") . '" alt="' . $clang->gT("Delete token entry") . '" onclick=\'if (confirm("' . $clang->gT("Are you sure you want to delete this entry?") . ' (' . $token['tid'] . ')")) {$("#displaytokens").delRowData(' . $token['tid'] . ');$.post(delUrl,{tid:' . $token['tid'] . '});}\'>';

            if (strtolower($token['emailstatus']) == 'ok')
            {
                if ($token['sent'] == 'N')
                    $action .= '<input type="image" src="' . Yii::app()->getConfig('imageurl') . '/token_invite.png" name="sendinvitations" id="sendinvitations" title="' . $clang->gT("Send invitation emails to the selected entries (if they have not yet been sent an invitation email)") . '" onclick=\'window.open("' . Yii::app()->createUrl("admin/tokens/sa/email/surveyid/{$iSurveyId}/tokenids/" . $token['tid']) . '", "_blank")\' />';
                else
                    $action .= '<input type="image" src="' . Yii::app()->getConfig('imageurl') . '/token_remind.png" name="sendreminders" id="sendreminders" title="' . $clang->gT("Send reminder email to the selected entries (if they have already received the invitation email)") . '" onclick=\'window.open("' . Yii::app()->createUrl("admin/tokens/sa/email/action/remind/surveyid/{$iSurveyId}/tokenids/" . $token['tid']) . '", "_blank")\' />';
            }
            $action .= '<input style="height: 16; width: 16px; font-size: 8; font-family: verdana" type="image" src="' . Yii::app()->getConfig('imageurl') . '/token_edit.png" title="' . $clang->gT("Edit token entry") . '" alt="' . $clang->gT("Edit token entry") . '" onclick=\'window.open("' . Yii::app()->createUrl("/admin/tokens/sa/edit/surveyid/{$iSurveyId}/tokenid/{$token['tid']}") . '", "_top")\'>';

            $aData->rows[$i]['cell'] = array($token['tid'], $action, $token['firstname'], $token['lastname'], $token['email'], $token['emailstatus'], $token['token'], $token['language'], $token['sent'], $token['remindersent'], $token['remindercount'], $token['completed'], $token['usesleft'], $token['validfrom'], $token['validuntil']);
            $attributes = GetAttributeFieldNames($iSurveyId);
            foreach ($attributes as $attribute)
            {
                $aData->rows[$i]['cell'][] = $token[$attribute];
            }
        }

        echo ls_json_encode($aData);
    }

function editToken($iSurveyId)
{
    $sOperation = CHttpRequest::getPost('oper');

    if (trim($_POST['validfrom']) == '')
    {
        $from = null;
    }
    else
    {
        $from = date('Y-m-d H:i:s', strtotime(trim($_POST['validfrom'])));
    }
    if (trim($_POST['validuntil']) == '')
    {
        $until = null;
    }
    else
    {
        $until = date('Y-m-d H:i:s', strtotime(trim($_POST['validuntil'])));
    }
    // if edit it will update the row
    if ($sOperation == 'edit')
        {
            //            if (CHttpRequest::getPost('language') == '')
            //            {
            //                $sLang = Yii::app()->session['adminlang'];
            //            }
            //            else
            //            {
            //                $sLang = CHttpRequest::getPost('language');
            //            }
            Tokens_dynamic::sid($iSurveyId);

            echo $from . ',' . $until;
            $aData = array(
                'firstname' => CHttpRequest::getPost('firstname'),
                'lastname' => CHttpRequest::getPost('lastname'),
                'email' => CHttpRequest::getPost('email'),
                'emailstatus' => CHttpRequest::getPost('emailstatus'),
                'token' => CHttpRequest::getPost('token'),
                'language' => CHttpRequest::getPost('language'),
                'sent' => CHttpRequest::getPost('sent'),
                'remindersent' => CHttpRequest::getPost('remindersent'),
                'remindercount' => CHttpRequest::getPost('remindercount'),
                'completed' => CHttpRequest::getPost('completed'),
                'usesleft' => CHttpRequest::getPost('usesleft'),
                'validfrom' => $from,
                'validuntil' => $until);
            $attributes = GetAttributeFieldNames($iSurveyId);
            foreach ($attributes as $attribute)
            {
                $aData[$attribute] = CHttpRequest::getPost($attribute);
            }
            $token = Tokens_dynamic::model()->find('tid=' . CHttpRequest::getPost('id'));
            foreach ($aData as $k => $v)
                $token->$k = $v;
            echo $token->update();
        }
        // if add it will insert a new row
        elseif ($sOperation == 'add')
        {
            if (CHttpRequest::getPost('language') == '')
                $aData = array('firstname' => CHttpRequest::getPost('firstname'),
                    'lastname' => CHttpRequest::getPost('lastname'),
                    'email' => CHttpRequest::getPost('email'),
                    'emailstatus' => CHttpRequest::getPost('emailstatus'),
                    'token' => CHttpRequest::getPost('token'),
                    'language' => CHttpRequest::getPost('language'),
                    'sent' => CHttpRequest::getPost('sent'),
                    'remindersent' => CHttpRequest::getPost('remindersent'),
                    'remindercount' => CHttpRequest::getPost('remindercount'),
                    'completed' => CHttpRequest::getPost('completed'),
                    'usesleft' => CHttpRequest::getPost('usesleft'),
                    'validfrom' => $from,
                    'validuntil' => $until);
            $attributes = GetAttributeFieldNames($iSurveyId);
            foreach ($attributes as $attribute)
            {
                $aData[$attribute] = CHttpRequest::getPost($attribute);
            }
            echo ls_json_encode(var_export($aData));
            $token = new Tokens_dynamic;
            foreach ($aData as $k => $v)
                $token->$k = $v;
            echo $token->save();
        }
        elseif ($sOperation == 'del')
        {
            $_POST['tid'] = CHttpRequest::getPost('id');
            $this->delete($iSurveyId);
        }
    }

    /**
     * Add new token form
     */
    function addnew($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        Yii::app()->loadHelper("surveytranslator");

        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);

        if (!bHasSurveyPermission($iSurveyId, 'tokens', 'create'))
        {
            die("no permissions"); // TODO Replace
        }

        if (CHttpRequest::getPost('subaction'))
        {
            $clang = $this->getController()->lang;

            Yii::import('application.libraries.Date_Time_Converter');
            //Fix up dates and match to database format
            if (trim(CHttpRequest::getPost('validfrom')) == '')
            {
                $_POST['validuntil'] = null;
            }
            else
            {
                $datetimeobj = new Date_Time_Converter(array(trim(CHttpRequest::getPost('validfrom')), $dateformatdetails['phpdate'] . ' H:i'));
                $_POST['validuntil'] = $datetimeobj->convert('Y-m-d H:i:s');
            }
            if (trim(CHttpRequest::getPost('validuntil')) == '')
            {
                $_POST['validuntil'] = null;
            }
            else
            {
                $datetimeobj = new Date_Time_Converter(array(trim(CHttpRequest::getPost('validuntil')), $dateformatdetails['phpdate'] . ' H:i'));
                $_POST['validuntil'] = $datetimeobj->convert('Y-m-d H:i:s');
            }

            $sanitizedtoken = sanitize_token(CHttpRequest::getPost('token'));

            if (empty($sanitizedtoken))
            {
                $isvalidtoken = false;
                while ($isvalidtoken == false)
                {
                    $newtoken = sRandomChars(15);
                    if (!isset($existingtokens[$newtoken]))
                    {
                        $isvalidtoken = true;
                        $existingtokens[$newtoken] = null;
                    }
                }
                $sanitizedtoken = $newtoken;
            }

            $aData = array(
                'firstname' => CHttpRequest::getPost('firstname'),
                'lastname' => CHttpRequest::getPost('lastname'),
                'email' => sanitize_email(CHttpRequest::getPost('email')),
                'emailstatus' => CHttpRequest::getPost('emailstatus'),
                'token' => $sanitizedtoken,
                'language' => sanitize_languagecode(CHttpRequest::getPost('language')),
                'sent' => CHttpRequest::getPost('sent'),
                'remindersent' => CHttpRequest::getPost('remindersent'),
                'completed' => CHttpRequest::getPost('completed'),
                'usesleft' => CHttpRequest::getPost('usesleft'),
                'validfrom' => CHttpRequest::getPost('validfrom'),
                'validuntil' => CHttpRequest::getPost('validuntil'),
            );

            // add attributes
            $attrfieldnames = GetAttributeFieldnames($iSurveyId);
            foreach ($attrfieldnames as $attr_name)
            {
                $aData[$attr_name] = $_POST[$attr_name];
            }

            $udresult = Tokens_dynamic::model($iSurveyId)->findAll("token <> '' and token = '$sanitizedtoken'");
            if (count($udresult) == 0)
            {
                // AutoExecute
                $token = new Tokens_dynamic;
                foreach ($aData as $k => $v)
                    $token->$k = $v;
                $inresult = $token->save();
                $aData['success'] = true;
            }
            else
            {
                $aData['success'] = false;
            }

            $aData['thissurvey'] = getSurveyInfo($iSurveyId);
            $aData['surveyid'] = $iSurveyId;

            $this->_renderWrappedTemplate(array('tokenbar', 'addtokenpost'), $aData);
        }
        else
        {
            self::_handletokenform($iSurveyId, "addnew");
        }
    }

    /**
     * Edit Tokens
     */
    function edit($iSurveyId, $iTokenId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $iTokenId = sanitize_int($iTokenId);

        if (!bHasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            die("no permissions"); // TODO Replace
        }

        Yii::app()->loadHelper("surveytranslator");
        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);

        if (CHttpRequest::getPost('subaction'))
        {
            $clang = $this->getController()->lang;

            Yii::import('application.libraries.Date_Time_Converter', true);
            if (trim(CHttpRequest::getPost('validfrom')) == '')
            {
                $_POST['validfrom'] = null;
            }
            else
            {
                $datetimeobj = new Date_Time_Converter(array(trim(CHttpRequest::getPost('validfrom')), $dateformatdetails['phpdate'] . ' H:i'));
                $_POST['validfrom'] = $datetimeobj->convert('Y-m-d H:i:s');
            }
            if (trim(CHttpRequest::getPost('validuntil')) == '')
            {
                $_POST['validuntil'] = null;
            }
            else
            {
                $datetimeobj = new Date_Time_Converter(array(trim(CHttpRequest::getPost('validuntil')), $dateformatdetails['phpdate'] . ' H:i'));
                $_POST['validuntil'] = $datetimeobj->convert('Y-m-d H:i:s');
            }

            $aData['thissurvey'] = getSurveyInfo($iSurveyId);
            $aData['surveyid'] = $iSurveyId;

            $aTokenData['firstname'] = CHttpRequest::getPost('firstname');
            $aTokenData['lastname'] = CHttpRequest::getPost('lastname');
            $aTokenData['email'] = sanitize_email(CHttpRequest::getPost('email'));
            $aTokenData['emailstatus'] = CHttpRequest::getPost('emailstatus');
            $santitizedtoken = sanitize_token(CHttpRequest::getPost('token'));
            $aTokenData['token'] = $santitizedtoken;
            $aTokenData['language'] = sanitize_languagecode(CHttpRequest::getPost('language'));
            $aTokenData['sent'] = CHttpRequest::getPost('sent');
            $aTokenData['completed'] = CHttpRequest::getPost('completed');
            $aTokenData['usesleft'] = CHttpRequest::getPost('usesleft');
            $aTokenData['validfrom'] = CHttpRequest::getPost('validfrom');
            $aTokenData['validuntil'] = CHttpRequest::getPost('validuntil');
            $aTokenData['remindersent'] = CHttpRequest::getPost('remindersent');
            $aTokenData['remindercount'] = intval(CHttpRequest::getPost('remindercount'));

            $udresult = Tokens_dynamic::model($iSurveyId)->findAll("tid <> '$iTokenId' and token <> '' and token = '$santitizedtoken'");

            if (count($udresult) == 0)
            {
                //$aTokenData = array();
                $attrfieldnames = GetAttributeFieldnames($iSurveyId);
                foreach ($attrfieldnames as $attr_name)
                {
                    $aTokenData[$attr_name] = CHttpRequest::getPost($attr_name);
                }

                $token = Tokens_dynamic::model($iSurveyId)->findByPk($iTokenId);
                foreach ($aTokenData as $k => $v)
                    $token->$k = $v;
                $token->save();

                $this->_renderWrappedTemplate(array('tokenbar', 'message' => array(
                    'title' => $clang->gT("Success"),
                    'message' => $clang->gT("The token entry was successfully updated.") . "<br /><br />\n"
                        . "\t\t<input type='button' value='" . $clang->gT("Display tokens") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/browse/surveyid/$iSurveyId/") . "', '_top')\" />\n"
                )), $aData);
            }
            else
            {
                $this->_renderWrappedTemplate(array('tokenbar', 'message' => array(
                    'title' => $clang->gT("Failed"),
                    'message' => $clang->gT("There is already an entry with that exact token in the table. The same token cannot be used in multiple entries.") . "<br /><br />\n"
                        . "\t\t<input type='button' value='" . $clang->gT("Show this token entry") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/edit/surveyid/$iSurveyId/tokenid/$iTokenId") . "', '_top')\" />\n"
                )));
            }
        }
        else
        {
            $this->_handletokenform($iSurveyId, "edit", $iTokenId);
        }
    }

    /**
     * Delete tokens
     */
    function delete($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $iTokenId = CHttpRequest::getPost('tid');

        if (bHasSurveyPermission($iSurveyId, 'tokens', 'delete'))
        {
            $aTokenIds = explode(',', $iTokenId); //Make the tokenids string into an array
            Tokens_dynamic::model($iSurveyId)->deleteRecords($aTokenIds);
        }
    }

    /**
     * Add dummy tokens form
     */
    function addDummies($iSurveyId, $subaction = '')
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $clang = $this->getController()->lang;

        if (!bHasSurveyPermission($iSurveyId, 'tokens', 'create'))
        {
            die("No permissions."); // TODO Replace
        }

        $this->getController()->loadHelper("surveytranslator");

        if (!empty($subaction) && $subaction == 'add')
        {
            $this->getController()->loadLibrary('Date_Time_Converter');
            $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);

            //Fix up dates and match to database format
            if (trim(CHttpRequest::getPost('validfrom')) == '')
            {
                $_POST['validfrom'] = null;
            }
            else
            {
                $datetimeobj = new Date_Time_Converter(array(trim(CHttpRequest::getPost('validfrom')), $dateformatdetails['phpdate'] . ' H:i'));
                $_POST['validfrom'] = $datetimeobj->convert('Y-m-d H:i:s');
            }
            if (trim(CHttpRequest::getPost('validuntil')) == '')
            {
                $_POST['validuntil'] = null;
            }
            else
            {
                $datetimeobj = new Date_Time_Converter(array(trim(CHttpRequest::getPost('validuntil')), $dateformatdetails['phpdate'] . ' H:i'));
                $_POST['validuntil'] = $datetimeobj->convert('Y-m-d H:i:s');
            }

            $santitizedtoken = '';

            $aData = array('firstname' => CHttpRequest::getPost('firstname'),
                'lastname' => CHttpRequest::getPost('lastname'),
                'email' => sanitize_email(CHttpRequest::getPost('email')),
                'emailstatus' => 'OK',
                'token' => $santitizedtoken,
                'language' => sanitize_languagecode(CHttpRequest::getPost('language')),
                'sent' => 'N',
                'remindersent' => 'N',
                'completed' => 'N',
                'usesleft' => CHttpRequest::getPost('usesleft'),
                'validfrom' => CHttpRequest::getPost('validfrom'),
                'validuntil' => CHttpRequest::getPost('validuntil'));

            // add attributes
            $attrfieldnames = GetAttributeFieldnames($iSurveyId);
            foreach ($attrfieldnames as $attr_name)
            {
                $aData[$attr_name] = CHttpRequest::getPost($attr_name);
            }

            $amount = sanitize_int(CHttpRequest::getPost('amount'));
            $tokenlength = sanitize_int(CHttpRequest::getPost('tokenlen'));

            for ($i = 0; $i < $amount; $i++)
            {
                $aDataToInsert = $aData;
                $aDataToInsert['firstname'] = str_replace('{TOKEN_COUNTER}', $i, $aDataToInsert['firstname']);
                $aDataToInsert['lastname'] = str_replace('{TOKEN_COUNTER}', $i, $aDataToInsert['lastname']);
                $aDataToInsert['email'] = str_replace('{TOKEN_COUNTER}', $i, $aDataToInsert['email']);

                $isvalidtoken = false;
                while ($isvalidtoken == false)
                {
                    $newtoken = sRandomChars($tokenlength);
                    if (!isset($existingtokens[$newtoken]))
                    {
                        $isvalidtoken = true;
                        $existingtokens[$newtoken] = null;
                    }
                }

                $aDataToInsert['token'] = $newtoken;
                Tokens_dynamic::insertToken($iSurveyId, $aDataToInsert);
            }

            $this->_renderWrappedTemplate(array('message' => array(
                'title' => $clang->gT("Success"),
                'message' => $clang->gT("New dummy tokens were added.") . "<br /><br />\n<input type='button' value='"
                    . $clang->gT("Display tokens") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/browse/surveyid/$iSurveyId") . "', '_top')\" />\n"
            ) ));
        }
        else
        {
            $tkcount = Tokens_dynamic::model($iSurveyId)->count();
            $tokenlength = Yii::app()->db->createCommand()->select('tokenlength')->from('{{surveys}}')->where('sid=' . $iSurveyId)->query()->readColumn(0);

            if (empty($tokenlength))
            {
                $tokenlength = 15;
            }

            $thissurvey = getSurveyInfo($iSurveyId);
            $aData['thissurvey'] = $thissurvey;
            $aData['surveyid'] = $iSurveyId;
            $aData['tokenlength'] = $tokenlength;
            $aData['dateformatdetails'] = getDateFormatData(Yii::app()->session['dateformat']);

            $this->_renderWrappedTemplate(array('tokenbar', 'dummytokenform'), $aData);
        }
    }

    /**
     * Handle managetokenattributes action
     */
    function managetokenattributes($iSurveyId)
    {
        $clang = $this->getController()->lang;
        $iSurveyId = sanitize_int($iSurveyId);

        if (!bHasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            die("no permissions"); // TODO Replace
        }

        Yii::app()->loadHelper("surveytranslator");

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;
        $aData['tokenfields'] = GetTokenFieldsAndNames($iSurveyId, true);
        $aData['nrofattributes'] = 0;
        $aData['examplerow'] = Tokens_dynamic::model($iSurveyId)->find();

        $this->_renderWrappedTemplate(array('tokenbar', 'managetokenattributes'), $aData);
    }

    /**
     * Update token attributes
     */
    function updatetokenattributes($iSurveyId)
    {
        $clang = $this->getController()->lang;
        $iSurveyId = sanitize_int($iSurveyId);
        if (!bHasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            die();
        }

        $number2add = sanitize_int(CHttpRequest::getPost('addnumber'), 1, 100);
        $tokenattributefieldnames = GetAttributeFieldNames($iSurveyId);
        $i = 1;

        for ($b = 0; $b < $number2add; $b++)
        {
            while (in_array('attribute_' . $i, $tokenattributefieldnames) !== false)
            {
                $i++;
            }
            $tokenattributefieldnames[] = 'attribute_' . $i;
            Yii::app()->db->createCommand(Yii::app()->db->getSchema()->addColumn("{{tokens_{$iSurveyId}}}", 'attribute_' . $i, 'VARCHAR(255)'))->execute();
            $fields['attribute_' . $i] = array('type' => 'VARCHAR', 'constraint' => '255');
        }

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;

        $this->_renderWrappedTemplate(array('tokenbar', 'message' => array(
            'title' => sprintf($clang->gT("%s field(s) were successfully added."), $number2add),
            'message' => "<br /><input type='button' value='" . $clang->gT("Back to attribute field management.") . "' onclick=\"window.open('" . $this->getController()->createUrl("/admin/tokens/sa/managetokenattributes/surveyid/$iSurveyId") . "', '_top')\" />"
        )), $aData);
    }

    /**
     * updatetokenattributedescriptions action
     */
    function updatetokenattributedescriptions($iSurveyId)
    {
        $clang = $this->getController()->lang;
        $iSurveyId = sanitize_int($iSurveyId);
        if (!bHasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            die();
        }

        // find out the existing token attribute fieldnames
        $tokenattributefieldnames = GetAttributeFieldNames($iSurveyId);
        $fieldcontents = '';
        foreach ($tokenattributefieldnames as $fieldname)
        {
            $fieldcontents.=$fieldname . '=' . strip_tags(CHttpRequest::getPost('description_' . $fieldname)) . "\n";
        }

        Survey::model()->updateSurvey(array("attributedescriptions" => $fieldcontents), "sid=$iSurveyId");
        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;
        $this->_renderWrappedTemplate(array('tokenbar', 'message' => array(
            'title' => $clang->gT('Token attribute descriptions were successfully updated.'),
            'message' => "<br /><input type='button' value='" . $clang->gT('Back to attribute field management.') . "' onclick=\"window.open('" . $this->getController()->createUrl("/admin/tokens/sa/managetokenattributes/surveyid/$iSurveyId") . "', '_top')\" />"
        )), $aData);
    }

    /**
     * Handle email action
     */
    function email($iSurveyId, $aTokenIds = null)
    {
        $clang = $this->getController()->lang;
        $iSurveyId = sanitize_int($iSurveyId);

        if (!bHasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            die("no permissions"); // TODO Replace
        }

        $sSubAction = CHttpRequest::getParam('action');
        $sSubAction = !in_array($sSubAction, array('email', 'remind')) ? 'email' : $sSubAction;
        $bEmail = $sSubAction == 'email';

        Yii::app()->loadHelper('surveytranslator');
        Yii::app()->loadHelper('/admin/htmleditor');
        Yii::app()->loadHelper('replacements');

        $query = Tokens_dynamic::model($iSurveyId)->find();
        $aExampleRow = empty($query) ? array() : $query->attributes;
        $aSurveyLangs = GetAdditionalLanguagesFromSurveyID($iSurveyId);
        $sBaseLanguage = GetBaseLanguageFromSurveyID($iSurveyId);
        array_unshift($aSurveyLangs, $sBaseLanguage);
        $aTokenIds = $this->_getTokenIds($aTokenIds);
        $aTokenFields = GetTokenFieldsAndNames($iSurveyId, true);
        $iAttributes = 0;
        if (getEmailFormat($iSurveyId) == 'html')
        {
            $bHtml = true;
        }
        else
        {
            $bHtml = false;
        }

        $modrewrite = Yii::app()->getConfig("modrewrite");
        $timeadjust = Yii::app()->getConfig("timeadjust");

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;
        $aData['sSubAction'] = $sSubAction;
        $aData['bEmail'] = $bEmail;
        $aData['surveylangs'] = $aSurveyLangs;
        $aData['baselang'] = $sBaseLanguage;
        $aData['tokenfields'] = $aTokenFields;
        $aData['nrofattributes'] = $iAttributes;
        $aData['examplerow'] = $aExampleRow;
        $aData['tokenids'] = $aTokenIds;
        $aData['ishtml'] = $bHtml;
        $iMaxEmails = CHttpRequest::getPost('maxemails');

        if (CHttpRequest::getPost('bypassbademails') == 'Y')
        {
            $SQLemailstatuscondition = " AND emailstatus = 'OK'";
        }
        else
        {
            $SQLemailstatuscondition = " AND emailstatus <> 'OptOut'";
        }

        if (!CHttpRequest::getPost('ok'))
        {
            if (empty($aData['tokenids']))
            {
                $aTokens = Tokens_dynamic::model($iSurveyId)->findUninvited($aTokenIds, 0, $bEmail, $SQLemailstatuscondition);
                foreach($aTokens as $aToken)
                {
                    $aData['tokenids'][] = $aToken['tid'];
                }
            }
            $this->_renderWrappedTemplate(array('tokenbar', $sSubAction), $aData);
        }
        else
        {
            $SQLremindercountcondition = "";
            $SQLreminderdelaycondition = "";

            if (!$bEmail)
            {
                if (CHttpRequest::getPost('maxremindercount') &&
                        CHttpRequest::getPost('maxremindercount') != '' &&
                        intval(CHttpRequest::getPost('maxremindercount')) != 0)
                {
                    $SQLremindercountcondition = " AND remindercount < " . intval(CHttpRequest::getPost('maxremindercount'));
                }

                if (CHttpRequest::getPost('minreminderdelay') &&
                        CHttpRequest::getPost('minreminderdelay') != '' &&
                        intval(CHttpRequest::getPost('minreminderdelay')) != 0)
                {
                    // CHttpRequest::getPost('minreminderdelay') in days (86400 seconds per day)
                    $compareddate = date_shift(
                            date("Y-m-d H:i:s", time() - 86400 * intval(CHttpRequest::getPost('minreminderdelay'))), "Y-m-d H:i", $timeadjust);
                    $SQLreminderdelaycondition = " AND ( "
                            . " (remindersent = 'N' AND sent < '" . $compareddate . "') "
                            . " OR "
                            . " (remindersent < '" . $compareddate . "'))";
                }
            }

            $ctresult = Tokens_dynamic::model($iSurveyId)->findUninvited($aTokenIds, 0, $bEmail, $SQLemailstatuscondition, $SQLremindercountcondition, $SQLreminderdelaycondition);
            $ctcount = count($ctresult);

            $emresult = Tokens_dynamic::model($iSurveyId)->findUninvited($aTokenIds, $iMaxEmails, $bEmail, $SQLemailstatuscondition, $SQLremindercountcondition, $SQLreminderdelaycondition);
            $emcount = count($emresult);

            foreach ($aSurveyLangs as $language)
            {
                $_POST['message_' . $language] = auto_unescape(CHttpRequest::getPost('message_' . $language));
                $_POST['subject_' . $language] = auto_unescape(CHttpRequest::getPost('subject_' . $language));
                if ($bHtml)
                    $_POST['message_' . $language] = html_entity_decode(CHttpRequest::getPost('message_' . $language), ENT_QUOTES, Yii::app()->getConfig("emailcharset"));
            }

            $attributes = GetTokenFieldsAndNames($iSurveyId);
            $tokenoutput = "";

            if ($emcount > 0)
            {
                foreach ($emresult as $emrow)
                {
                    $to = array();
                    $aEmailaddresses = explode(';', $emrow['email']);
                    foreach ($aEmailaddresses as $sEmailaddress)
                    {
                        $to[] = ($emrow['firstname'] . " " . $emrow['lastname'] . " <{$sEmailaddress}>");
                    }
                    $fieldsarray["{EMAIL}"] = $emrow['email'];
                    $fieldsarray["{FIRSTNAME}"] = $emrow['firstname'];
                    $fieldsarray["{LASTNAME}"] = $emrow['lastname'];
                    $fieldsarray["{TOKEN}"] = $emrow['token'];
                    $fieldsarray["{LANGUAGE}"] = $emrow['language'];

                    foreach ($attributes as $attributefield => $attributedescription)
                    {
                        $fieldsarray['{' . strtoupper($attributefield) . '}'] = $emrow[$attributefield];
                    }

                    $emrow['language'] = trim($emrow['language']);
                    $found = array_search($emrow['language'], $aSurveyLangs);
                    if ($emrow['language'] == '' || $found == false)
                    {
                        $emrow['language'] = $sBaseLanguage;
                    }

                    $from = CHttpRequest::getPost('from_' . $emrow['language']);

                    $fieldsarray["{OPTOUTURL}"] = $this->getController()->createUrl("/optout/langcode/" . trim($emrow['language']) . "/surveyid/{$iSurveyId}/token/{$emrow['token']}");
                    $fieldsarray["{OPTINURL}"] = $this->getController()->createUrl("/optin/langcode/" . trim($emrow['language']) . "/surveyid/{$iSurveyId}/token/{$emrow['token']}");
                    $fieldsarray["{SURVEYURL}"] = $this->getController()->createUrl("/survey/langcode/" . trim($emrow['language']) . "/surveyid/{$iSurveyId}/token/{$emrow['token']}");

                    foreach(array('OPTOUT', 'OPTIN', 'SURVEY') as $key)
                    {
                        $url = $fieldsarray["{{$key}URL}"];
                        $fieldsarray["{{$key}URL}"] = "<a href='{$url}'>" . htmlspecialchars($url) . '</a>';
                        if ($key == 'SURVEY')
                        {
                            $fieldsarray["@@SURVEYURL@@"] = $url;
                        }
                    }

                    $customheaders = array('1' => "X-surveyid: " . $iSurveyId,
                        '2' => "X-tokenid: " . $fieldsarray["{TOKEN}"]);

                    global $maildebug;
                    $modsubject = Replacefields(CHttpRequest::getPost('subject_' . $emrow['language']), $fieldsarray);
                    $modmessage = Replacefields(CHttpRequest::getPost('message_' . $emrow['language']), $fieldsarray);

                    if (trim($emrow['validfrom']) != '' && convertDateTimeFormat($emrow['validfrom'], 'Y-m-d H:i:s', 'U') * 1 > date('U') * 1)
                    {
                        $tokenoutput .= $emrow['tid'] . " " . ReplaceFields($clang->gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) delayed: Token is not yet valid.") . "<br />", $fieldsarray);
                    }
                    elseif (trim($emrow['validuntil']) != '' && convertDateTimeFormat($emrow['validuntil'], 'Y-m-d H:i:s', 'U') * 1 < date('U') * 1)
                    {
                        $tokenoutput .= $emrow['tid'] . " " . ReplaceFields($clang->gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) skipped: Token is not valid anymore.") . "<br />", $fieldsarray);
                    }
                    elseif (SendEmailMessage($modmessage, $modsubject, $to, $from, Yii::app()->getConfig("sitename"), $bHtml, getBounceEmail($iSurveyId), null, $customheaders))
                    {
                        // Put date into sent
                        $udequery = Tokens_dynamic::model($iSurveyId)->findByPk($emrow['tid']);
                        if ($bEmail)
                        {
                            $tokenoutput .= $clang->gT("Invitation sent to:");
                            $udequery->sent = date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"));
                        }
                        else
                        {
                            $tokenoutput .= $clang->gT("Reminder sent to:");
                            $udequery->remindersent = date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"));
                            $udequery->remindercount = $udequery->remindercount + 1;
                        }
                        $udequery->save();

                        $tokenoutput .= " {$emrow['firstname']} {$emrow['lastname']} ({$emrow['email']})<br />\n";
                        if (Yii::app()->getConfig("emailsmtpdebug") == 2)
                        {
                            $tokenoutput .= $maildebug;
                        }
                    }
                    else
                    {
                        $tokenoutput .= ReplaceFields($clang->gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) failed. Error Message:") . " " . $maildebug . "<br />", $fieldsarray);
                    }
                    unset($fieldsarray);
                }
                if ($ctcount > $emcount)
                {
                    $i = 0;
                    if (isset($aTokenIds))
                    {
                        while ($i < $iMaxEmails)
                        {
                            array_shift($aTokenIds);
                            $i++;
                        }
                        $tids = implode('|', $aTokenIds);
                    }
                    $lefttosend = $ctcount - $iMaxEmails;
                }

                $aData['tids'] = $tids;

                $this->_renderWrappedTemplate(array('tokenbar', 'emailpost', 'emailwarning'), $aData);
            }
            else
            {
                $this->_renderWrappedTemplate(array('tokenbar', 'message' => array(
                    'title' => $clang->gT("Warning"),
                    'message' => $clang->gT("There were no eligible emails to send. This will be because none satisfied the criteria of:")
                            . "<br/>&nbsp;<ul><li>" . $clang->gT("having a valid email address") . "</li>"
                            . "<li>" . $clang->gT("not having been sent an invitation already") . "</li>"
                            . "<li>" . $clang->gT("having already completed the survey") . "</li>"
                            . "<li>" . $clang->gT("having a token") . "</li></ul>"
                )), $aData);
            }
        }
    }

    /**
     * Export Dialog
     */
    function exportdialog($iSurveyId)
    {
        $clang = $this->getController()->lang;
        $iSurveyId = sanitize_int($iSurveyId);
        if (!bHasSurveyPermission($iSurveyId, 'tokens', 'export'))//EXPORT FEATURE SUBMITTED BY PIETERJAN HEYSE
        {
            die();
        }

        if (CHttpRequest::getPost('submit'))
        {
            Yii::app()->loadHelper("export");
            tokens_export($iSurveyId);
        }

        $aData['resultr'] = Tokens_dynamic::model($iSurveyId)->find(array('select' => 'language', 'group' => 'language'));
        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;

        $this->_renderWrappedTemplate(array('tokenbar', 'exportdialog'), $aData);
    }

    /**
     * Performs a ldap import
     *
     * @access public
     * @param int $iSurveyId
     * @return void
     */
    public function importldap($iSurveyId)
    {
        $iSurveyId = (int) $iSurveyId;
        $clang = $this->getController()->lang;

        Yii::app()->loadConfig('ldap');
        Yii::app()->loadHelper('ldap');

        $tokenoutput = '';
        if (!bHasSurveyPermission($iSurveyId, 'tokens', 'create'))
        {
            die('access denied');
        }

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['iSurveyId'] = $aData['surveyid'] = $iSurveyId;
        $aData['ldap_queries'] = Yii::app()->getConfig('ldap_queries');

        if (!CHttpRequest::getPost('submit'))
        {
            $this->_renderWrappedTemplate(array('tokenbar', 'ldapform'), $aData);
        }
        else
        {
            $ldap_queries = Yii::app()->getConfig('ldap_queries');
            $ldap_server = Yii::app()->getConfig('ldap_server');

            $duplicatelist = array();
            $invalidemaillist = array();
            $tokenoutput .= "\t<tr><td colspan='2' height='4'><strong>"
                    . $clang->gT("Uploading LDAP Query") . "</strong></td></tr>\n"
                    . "\t<tr><td align='center'>\n";
            $ldapq = CHttpRequest::getPost('ldapQueries'); // the ldap query id

            $ldap_server_id = $ldap_queries[$ldapq]['ldapServerId'];
            $ldapserver = $ldap_server[$ldap_server_id]['server'];
            $ldapport = $ldap_server[$ldap_server_id]['port'];
            if (isset($ldap_server[$ldap_server_id]['encoding']) &&
                    $ldap_server[$ldap_server_id]['encoding'] != 'utf-8' &&
                    $ldap_server[$ldap_server_id]['encoding'] != 'UTF-8')
            {
                $ldapencoding = $ldap_server[$ldap_server_id]['encoding'];
            }
            else
            {
                $ldapencoding = '';
            }

            // define $attrlist: list of attributes to read from users' entries
            $attrparams = array('firstname_attr', 'lastname_attr',
                'email_attr', 'token_attr', 'language');

            $aTokenAttr = GetAttributeFieldNames($iSurveyId);
            foreach ($aTokenAttr as $thisattrfieldname)
            {
                $attridx = substr($thisattrfieldname, 10); // the 'attribute_' prefix is 10 chars long
                $attrparams[] = "attr" . $attridx;
            }

            foreach ($attrparams as $id => $attr)
            {
                if (array_key_exists($attr, $ldap_queries[$ldapq]) &&
                        $ldap_queries[$ldapq][$attr] != '')
                {
                    $attrlist[] = $ldap_queries[$ldapq][$attr];
                }
            }

            // Open connection to server
            $ds = ldap_getCnx($ldap_server_id);

            if ($ds)
            {
                // bind to server
                $resbind = ldap_bindCnx($ds, $ldap_server_id);

                if ($resbind)
                {
                    $ResArray = array();
                    $resultnum = ldap_doTokenSearch($ds, $ldapq, $ResArray, $iSurveyId);
                    $xz = 0; // imported token count
                    $xv = 0; // meet minim requirement count
                    $xy = 0; // check for duplicates
                    $duplicatecount = 0; // duplicate tokens skipped count
                    $invalidemailcount = 0;

                    if ($resultnum >= 1)
                    {
                        foreach ($ResArray as $responseGroupId => $responseGroup)
                        {
                            for ($j = 0; $j < $responseGroup['count']; $j++)
                            {
                                // first let's initialize everything to ''
                                $myfirstname = '';
                                $mylastname = '';
                                $myemail = '';
                                $mylanguage = '';
                                $mytoken = '';
                                $myattrArray = array();

                                // The first 3 attrs MUST exist in the ldap answer
                                // ==> send PHP notice msg to apache logs otherwise
                                $meetminirequirements = true;
                                if (isset($responseGroup[$j][$ldap_queries[$ldapq]['firstname_attr']]) &&
                                        isset($responseGroup[$j][$ldap_queries[$ldapq]['lastname_attr']])
                                )
                                {
                                    // minimum requirement for ldap
                                    // * at least a firstanme
                                    // * at least a lastname
                                    // * if filterblankemail is set (default): at least an email address
                                    $myfirstname = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['firstname_attr']]);
                                    $mylastname = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['lastname_attr']]);
                                    if (isset($responseGroup[$j][$ldap_queries[$ldapq]['email_attr']]))
                                    {
                                        $myemail = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['email_attr']]);
                                        $myemail = sanitize_email($myemail);
                                        ++$xv;
                                    }
                                    elseif ($filterblankemail !== true)
                                    {
                                        $myemail = '';
                                        ++$xv;
                                    }
                                    else
                                    {
                                        $meetminirequirements = false;
                                    }
                                }
                                else
                                {
                                    $meetminirequirements = false;
                                }

                                // The following attrs are optionnal
                                if (isset($responseGroup[$j][$ldap_queries[$ldapq]['token_attr']]))
                                    $mytoken = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['token_attr']]);

                                foreach ($aTokenAttr as $thisattrfieldname)
                                {
                                    $attridx = substr($thisattrfieldname, 10); // the 'attribute_' prefix is 10 chars long
                                    if (isset($ldap_queries[$ldapq]['attr' . $attridx]) &&
                                            isset($responseGroup[$j][$ldap_queries[$ldapq]['attr' . $attridx]]))
                                        $myattrArray[$attridx] = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['attr' . $attridx]]);
                                }

                                if (isset($responseGroup[$j][$ldap_queries[$ldapq]['language']]))
                                    $mylanguage = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['language']]);

                                // In case Ldap Server encoding isn't UTF-8, let's translate
                                // the strings to UTF-8
                                if ($ldapencoding != '')
                                {
                                    $myfirstname = @mb_convert_encoding($myfirstname, "UTF-8", $ldapencoding);
                                    $mylastname = @mb_convert_encoding($mylastname, "UTF-8", $ldapencoding);
                                    foreach ($aTokenAttr as $thisattrfieldname)
                                    {
                                        $attridx = substr($thisattrfieldname, 10); // the 'attribute_' prefix is 10 chars long
                                        @mb_convert_encoding($myattrArray[$attridx], "UTF-8", $ldapencoding);
                                    }
                                }

                                // Now check for duplicates or bad formatted email addresses
                                $dupfound = false;
                                $invalidemail = false;
                                if ($filterduplicatetoken)
                                {
                                    $dupquery = "SELECT firstname, lastname from {{tokens_$iSurveyId}} where email=" . db_quoteall($myemail) . " and firstname=" . db_quoteall($myfirstname) . " and lastname=" . db_quoteall($mylastname);
                                    $dupresult = Yii::app()->db->createCommand($dupquery)->query();
                                    if ($dupresult->getRowCount() > 0)
                                    {
                                        $dupfound = true;
                                        $duplicatelist[] = $myfirstname . " " . $mylastname . " (" . $myemail . ")";
                                        $xy++;
                                    }
                                }
                                if ($filterblankemail && $myemail == '')
                                {
                                    $invalidemail = true;
                                    $invalidemaillist[] = $myfirstname . " " . $mylastname . " ( )";
                                }
                                elseif ($myemail != '' && !validate_email($myemail))
                                {
                                    $invalidemail = true;
                                    $invalidemaillist[] = $myfirstname . " " . $mylastname . " (" . $myemail . ")";
                                }

                                if ($invalidemail)
                                {
                                    ++$invalidemailcount;
                                }
                                elseif ($dupfound)
                                {
                                    ++$duplicatecount;
                                }
                                elseif ($meetminirequirements === true)
                                {
                                    // No issue, let's import
                                    $iq = "INSERT INTO {{tokens_$iSurveyId}} \n"
                                            . "(firstname, lastname, email, emailstatus, token, language";

                                    foreach ($aTokenAttr as $thisattrfieldname)
                                    {
                                        $attridx = substr($thisattrfieldname, 10); // the 'attribute_' prefix is 10 chars long
                                        if (!empty($myattrArray[$attridx]))
                                        {
                                            $iq .= ", $thisattrfieldname";
                                        }
                                    }
                                    $iq .=") \n"
                                            . "VALUES (" . db_quoteall($myfirstname) . ", " . db_quoteall($mylastname) . ", " . db_quoteall($myemail) . ", 'OK', " . db_quoteall($mytoken) . ", " . db_quoteall($mylanguage) . "";

                                    foreach ($aTokenAttr as $thisattrfieldname)
                                    {
                                        $attridx = substr($thisattrfieldname, 10); // the 'attribute_' prefix is 10 chars long
                                        if (!empty($myattrArray[$attridx]))
                                        {
                                            $iq .= ", " . db_quoteall($myattrArray[$attridx]) . "";
                                        }// dbquote_all encloses str with quotes
                                    }
                                    $iq .= ")";
                                    $ir = Yii::app()->db->createCommand($iq)->execute();
                                    if (!$ir)
                                        $duplicatecount++;
                                    $xz++;
                                    // or die ("Couldn't insert line<br />\n$buffer<br />\n".htmlspecialchars($connect->ErrorMsg())."<pre style='text-align: left'>$iq</pre>\n");
                                }
                            } // End for each entry
                        } // End foreach responseGroup
                    } // End of if resnum >= 1

                    $aData['duplicatelist'] = $duplicatelist;
                    $aData['invalidemaillist'] = $invalidemaillist;
                    $aData['invalidemailcount'] = $invalidemailcount;
                    $aData['resultnum'] = $resultnum;
                    $aData['xv'] = $xv;
                    $aData['xy'] = $xy;
                    $aData['xz'] = $xz;

                    $this->_renderWrappedTemplate(array('tokenbar', 'ldappost'), $aData);
                }
                else
                {
                    $aData['sError'] = $clang->gT("Can't bind to the LDAP directory");
                    $this->_renderWrappedTemplate(array('tokenbar', 'ldapform'), $aData);
                }
                @ldap_close($ds);
            }
            else
            {
                $aData['sError'] = $clang->gT("Can't connect to the LDAP directory");
                $this->_renderWrappedTemplate(array('tokenbar', 'ldapform'), $aData);
            }
        }
    }

    /**
     * import from csv
     */
    function import($iSurveyId)
    {
        $clang = $this->getController()->lang;
        $iSurveyId = (int) $iSurveyId;

        if (!bHasSurveyPermission($iSurveyId, 'tokens', 'create'))
        {
            die('access denied');
        }

        $this->getController()->_js_admin_includes('scripts/tokens.js');

        $aEncodings = array(
            "armscii8" => $clang->gT("ARMSCII-8 Armenian")
            , "ascii" => $clang->gT("US ASCII")
            , "auto" => $clang->gT("Automatic")
            , "big5" => $clang->gT("Big5 Traditional Chinese")
            , "binary" => $clang->gT("Binary pseudo charset")
            , "cp1250" => $clang->gT("Windows Central European")
            , "cp1251" => $clang->gT("Windows Cyrillic")
            , "cp1256" => $clang->gT("Windows Arabic")
            , "cp1257" => $clang->gT("Windows Baltic")
            , "cp850" => $clang->gT("DOS West European")
            , "cp852" => $clang->gT("DOS Central European")
            , "cp866" => $clang->gT("DOS Russian")
            , "cp932" => $clang->gT("SJIS for Windows Japanese")
            , "dec8" => $clang->gT("DEC West European")
            , "eucjpms" => $clang->gT("UJIS for Windows Japanese")
            , "euckr" => $clang->gT("EUC-KR Korean")
            , "gb2312" => $clang->gT("GB2312 Simplified Chinese")
            , "gbk" => $clang->gT("GBK Simplified Chinese")
            , "geostd8" => $clang->gT("GEOSTD8 Georgian")
            , "greek" => $clang->gT("ISO 8859-7 Greek")
            , "hebrew" => $clang->gT("ISO 8859-8 Hebrew")
            , "hp8" => $clang->gT("HP West European")
            , "keybcs2" => $clang->gT("DOS Kamenicky Czech-Slovak")
            , "koi8r" => $clang->gT("KOI8-R Relcom Russian")
            , "koi8u" => $clang->gT("KOI8-U Ukrainian")
            , "latin1" => $clang->gT("cp1252 West European")
            , "latin2" => $clang->gT("ISO 8859-2 Central European")
            , "latin5" => $clang->gT("ISO 8859-9 Turkish")
            , "latin7" => $clang->gT("ISO 8859-13 Baltic")
            , "macce" => $clang->gT("Mac Central European")
            , "macroman" => $clang->gT("Mac West European")
            , "sjis" => $clang->gT("Shift-JIS Japanese")
            , "swe7" => $clang->gT("7bit Swedish")
            , "tis620" => $clang->gT("TIS620 Thai")
            , "ucs2" => $clang->gT("UCS-2 Unicode")
            , "ujis" => $clang->gT("EUC-JP Japanese")
            , "utf8" => $clang->gT("UTF-8 Unicode"));

        if (CHttpRequest::getPost('submit'))
        {
            if (CHttpRequest::getPost('csvcharset') && CHttpRequest::getPost('csvcharset'))  //sanitize charset - if encoding is not found sanitize to 'auto'
            {
                $uploadcharset = CHttpRequest::getPost('csvcharset');
                if (!array_key_exists($uploadcharset, $aEncodings))
                {
                    $uploadcharset = 'auto';
                }
                $filterduplicatetoken = (CHttpRequest::getPost('filterduplicatetoken') && CHttpRequest::getPost('filterduplicatetoken') == 'on');
                $filterblankemail = (CHttpRequest::getPost('filterblankemail') && CHttpRequest::getPost('filterblankemail') == 'on');
            }

            $attrfieldnames = GetAttributeFieldnames($iSurveyId);
            $duplicatelist = array();
            $invalidemaillist = array();
            $invalidformatlist = array();
            $firstline = array();

            $sPath = Yii::app()->getConfig('tempdir');
            $sFileName = $_FILES['the_file']['name'];
            $sFileTmpName = $_FILES['the_file']['tmp_name'];
            $sFilePath = $sPath . '/' . $sFileName;

            if (!@move_uploaded_file($sFileTmpName, $sFilePath))
            {
                $aData['sError'] = $clang->gT("Upload file not found. Check your permissions and path ({$sFilePath}) for the upload directory");
                $aData['aEncodings'] = $aEncodings;
                $aData['iSurveyId'] = $aData['surveyid'] = $iSurveyId;
                $aData['thissurvey'] = getSurveyInfo($iSurveyId);
                $this->_renderWrappedTemplate(array('tokenbar', 'csvupload'), $aData);
            }
            else
            {
                $xz = 0;
                $recordcount = 0;
                $xv = 0;
                // This allows to read file with MAC line endings too
                @ini_set('auto_detect_line_endings', true);
                // open it and trim the ednings
                $tokenlistarray = file($sFilePath);
                $sBaseLanguage = GetBaseLanguageFromSurveyID($iSurveyId);
                if (!CHttpRequest::getPost('filterduplicatefields') || (CHttpRequest::getPost('filterduplicatefields') && count(CHttpRequest::getPost('filterduplicatefields')) == 0))
                {
                    $filterduplicatefields = array('firstname', 'lastname', 'email');
                }
                else
                {
                    $filterduplicatefields = CHttpRequest::getPost('filterduplicatefields');
                }
                $separator = returnglobal('separator');
                foreach ($tokenlistarray as $buffer)
                {
                    $buffer = @mb_convert_encoding($buffer, "UTF-8", $uploadcharset);
                    $firstname = "";
                    $lastname = "";
                    $email = "";
                    $emailstatus = "OK";
                    $token = "";
                    $language = "";
                    $attribute1 = "";
                    $attribute2 = ""; //Clear out values from the last path, in case the next line is missing a value
                    if ($recordcount == 0)
                    {
                        // Pick apart the first line
                        $buffer = removeBOM($buffer);
                        $allowedfieldnames = array('firstname', 'lastname', 'email', 'emailstatus', 'token', 'language', 'validfrom', 'validuntil', 'usesleft');
                        $allowedfieldnames = array_merge($attrfieldnames, $allowedfieldnames);

                        switch ($separator)
                        {
                            case 'comma':
                                $separator = ',';
                                break;
                            case 'semicolon':
                                $separator = ';';
                                break;
                            default:
                                $comma = substr_count($buffer, ',');
                                $semicolon = substr_count($buffer, ';');
                                if ($semicolon > $comma)
                                    $separator = ';'; else
                                    $separator = ',';
                        }
                        $firstline = convertCSVRowToArray($buffer, $separator, '"');
                        $firstline = array_map('trim', $firstline);
                        $ignoredcolumns = array();
                        //now check the first line for invalid fields
                        foreach ($firstline as $index => $fieldname)
                        {
                            $firstline[$index] = preg_replace("/(.*) <[^,]*>$/", "$1", $fieldname);
                            $fieldname = $firstline[$index];
                            if (!in_array($fieldname, $allowedfieldnames))
                            {
                                $ignoredcolumns[] = $fieldname;
                            }
                        }
                        if (!in_array('firstname', $firstline) || !in_array('lastname', $firstline) || !in_array('email', $firstline))
                        {
                            $recordcount = count($tokenlistarray);
                            break;
                        }
                    }
                    else
                    {

                        $line = convertCSVRowToArray($buffer, $separator, '"');

                        if (count($firstline) != count($line))
                        {
                            $invalidformatlist[] = $recordcount;
                            $recordcount++;
                            continue;
                        }
                        $writearray = array_combine($firstline, $line);

                        //kick out ignored columns
                        foreach ($ignoredcolumns as $column)
                        {
                            unset($writearray[$column]);
                        }
                        $dupfound = false;
                        $invalidemail = false;

                        if ($filterduplicatetoken != false)
                        {
                            $dupquery = "SELECT tid from {{tokens_$iSurveyId}} where 1=1";
                            foreach ($filterduplicatefields as $field)
                            {
                                if (isset($writearray[$field]))
                                {
                                    $dupquery.= " and {$field} = '$writearray[$field]'";
                                }
                            }
                            $dupresult = Yii::app()->db->createCommand($dupquery)->query();
                            if ($dupresult->getRowCount() > 0)
                            {
                                $dupfound = true;
                                $duplicatelist[] = $writearray['firstname'] . " " . $writearray['lastname'] . " (" . $writearray['email'] . ")";
                            }
                        }


                        $writearray['email'] = trim($writearray['email']);

                        //treat blank emails
                        if ($filterblankemail && $writearray['email'] == '')
                        {
                            $invalidemail = true;
                            $invalidemaillist[] = $line[0] . " " . $line[1] . " ( )";
                        }
                        if ($writearray['email'] != '')
                        {
                            $aEmailAddresses = explode(';', $writearray['email']);
                            foreach ($aEmailAddresses as $sEmailaddress)
                            {
                                if (!validate_email($sEmailaddress))
                                {
                                    $invalidemail = true;
                                    $invalidemaillist[] = $line[0] . " " . $line[1] . " (" . $line[2] . ")";
                                }
                            }
                        }

                        if (!isset($writearray['token']))
                        {
                            $writearray['token'] = '';
                        }
                        else
                        {
                            $writearray['token'] = sanitize_token($writearray['token']);
                        }

                        if (!$dupfound && !$invalidemail)
                        {
                            if (!isset($writearray['emailstatus']) || $writearray['emailstatus'] == '')
                                $writearray['emailstatus'] = "OK";
                            if (!isset($writearray['language']) || $writearray['language'] == "")
                                $writearray['language'] = $sBaseLanguage;
                            if (isset($writearray['validfrom']) && trim($writearray['validfrom'] == ''))
                            {
                                unset($writearray['validfrom']);
                            }
                            if (isset($writearray['validuntil']) && trim($writearray['validuntil'] == ''))
                            {
                                unset($writearray['validuntil']);
                            }

                            // sanitize it before writing into table
                            $sanitizedArray = array_map('db_quoteall', array_values($writearray));

                            $iq = "INSERT INTO {{tokens_$iSurveyId}} \n"
                                    . "(" . implode(',', array_keys($writearray)) . ") \n"
                                    . "VALUES (" . implode(",", $sanitizedArray) . ")";
                            $ir = Yii::app()->db->createCommand($iq)->execute();

                            if (!$ir)
                            {
                                $duplicatelist[] = $writearray['firstname'] . " " . $writearray['lastname'] . " (" . $writearray['email'] . ")";
                            }
                            else
                            {
                                $xz++;
                            }
                        }
                        $xv++;
                    }
                    $recordcount++;
                }
                $recordcount = $recordcount - 1;

                unlink($sFilePath);

                $aData['tokenlistarray'] = $tokenlistarray;
                $aData['xz'] = $xz;
                $aData['xv'] = $xv;
                $aData['recordcount'] = $recordcount;
                $aData['firstline'] = $firstline;
                $aData['duplicatelist'] = $duplicatelist;
                $aData['invalidformatlist'] = $invalidformatlist;
                $aData['invalidemaillist'] = $invalidemaillist;
                $aData['thissurvey'] = getSurveyInfo($iSurveyId);
                $aData['iSurveyId'] = $aData['surveyid'] = $iSurveyId;

                $this->_renderWrappedTemplate(array('tokenbar', 'csvpost'), $aData);
            }

        }
        else
        {
            $aData['aEncodings'] = $aEncodings;
            $aData['iSurveyId'] = $iSurveyId;
            $aData['thissurvey'] = getSurveyInfo($iSurveyId);
            $aData['surveyid'] = $iSurveyId;
            $this->_renderWrappedTemplate(array('tokenbar', 'csvupload'), $aData);
        }
    }

    /**
     * Generate tokens
     */
    function tokenify($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $clang = $this->getController()->lang;
        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;

        if (!bHasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            die();
        }

        if (!CHttpRequest::getParam('ok'))
        {
            $this->_renderWrappedTemplate(array('tokenbar', 'message' => array(
                'title' => $clang->gT("Create tokens"),
                'message' => $clang->gT("Clicking yes will generate tokens for all those in this token list that have not been issued one. Is this OK?") . "<br /><br />\n"
                    . "<input type='submit' value='"
                    . $clang->gT("Yes") . "' onclick=\"" . get2post($this->getController()->createUrl("admin/tokens/sa/tokenify/surveyid/$iSurveyId") . "?ok=Y") . "\" />\n"
                    . "<input type='submit' value='"
                    . $clang->gT("No") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/index/surveyid/$iSurveyId") . "', '_top')\" />\n"
                    . "<br />\n"
            )), $aData);
        }
        else
        {
            //get token length from survey settings
            $newtokencount = Tokens_dynamic::model($iSurveyId)->createTokens($iSurveyId);
            $this->_renderWrappedTemplate(array('tokenbar', 'message' => array(
                'title' => $clang->gT("Create tokens"),
                'message' => str_replace("{TOKENCOUNT}", $newtokencount, $clang->gT("{TOKENCOUNT} tokens have been created"))
            )), $aData);
        }
    }

    /**
     * Remove Token Database
     */
    function kill($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $clang = $this->getController()->lang;
        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;

        if (!bHasSurveyPermission($iSurveyId, 'surveyactivation', 'update'))
        {
            die();
        }

        $date = date('YmdHis');
        if (!CHttpRequest::getPost('ok'))
        {
            $this->_renderWrappedTemplate(array('tokenbar', 'message' => array(
                'title' => $clang->gT("Delete Tokens Table"),
                'message' => $clang->gT("If you delete this table tokens will no longer be required to access this survey.") . "<br />" . $clang->gT("A backup of this table will be made if you proceed. Your system administrator will be able to access this table.") . "<br />\n"
                    . "( \"old_tokens_{$iSurveyId}_$date\" )<br /><br />\n"
                    . "<input type='submit' value='"
                    . $clang->gT("Delete Tokens") . "' onclick=\"" . get2post($this->getController()->createUrl("admin/tokens/sa/kill/surveyid/$iSurveyId") . "?ok=y") . "\" />\n"
                    . "<input type='submit' value='"
                    . $clang->gT("Cancel") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/index/surveyid/$iSurveyId") . "', '_top')\" />\n"
            )), $aData);
        }
        else
        {
            $oldtable = "tokens_$iSurveyId";
            $newtable = "old_tokens_{$iSurveyId}_$date";

            Yii::app()->db->createCommand()->renameTable("{{{$oldtable}}}", "{{{$newtable}}}");

            $this->_renderWrappedTemplate(array('tokenbar', 'message' => array(
                'title' => $clang->gT("Delete Tokens Table"),
                'message' => '<br />' . $clang->gT("The tokens table has now been removed and tokens are no longer required to access this survey.") . "<br /> " . $clang->gT("A backup of this table has been made and can be accessed by your system administrator.") . "<br />\n"
                    . "(\"old_tokens_{$iSurveyId}_$date\")" . "<br /><br />\n"
                    . "<input type='submit' value='"
                    . $clang->gT("Main Admin Screen") . "' onclick=\"window.open('" . Yii::app()->createURL("admin/") . "', '_top')\" />"
            )), $aData);
        }
    }

    function bouncesettings($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $clang = $this->getController()->lang;
        $aData['thissurvey'] = $aData['settings'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;

        if (!empty($_POST))
        {
            $fieldvalue = array(
                "bounceprocessing" => CHttpRequest::getPost('bounceprocessing'),
                "bounce_email" => CHttpRequest::getPost('bounce_email'),
            );

            if (CHttpRequest::getPost('bounceprocessing') == 'L')
            {
                $fieldvalue['bounceaccountencryption'] = CHttpRequest::getPost('bounceaccountencryption');
                $fieldvalue['bounceaccountuser'] = CHttpRequest::getPost('bounceaccountuser');
                $fieldvalue['bounceaccountpass'] = CHttpRequest::getPost('bounceaccountpass');
                $fieldvalue['bounceaccounttype'] = CHttpRequest::getPost('bounceaccounttype');
                $fieldvalue['bounceaccounthost'] = CHttpRequest::getPost('bounceaccounthost');
            }

            $survey = Survey::model()->findByAttributes(array('sid' => $iSurveyId));
            foreach ($fieldvalue as $k => $v)
                $survey->$k = $v;
            $survey->save();

            $this->_renderWrappedTemplate(array('tokenbar', 'message' => array(
                'title' => $clang->gT("Bounce settings"),
                'message' => $clang->gT("Bounce settings have been saved."),
                'class' => 'successheader'
            )), $aData);
        }
        else
        {
            $this->_renderWrappedTemplate(array('tokenbar', 'bounce'), $aData);
        }
    }

    /**
     * Handle token form for addnew/edit actions
     */
    function _handletokenform($iSurveyId, $subaction, $iTokenId="")
    {
        $clang = $this->getController()->lang;

        Yii::app()->loadHelper("surveytranslator");

        if ($subaction == "edit")
        {
            $aData['tokenid'] = $iTokenId;
            $aData['tokendata'] = Tokens_dynamic::model($iSurveyId)->findByPk($iTokenId);
        }

        $thissurvey = getSurveyInfo($iSurveyId);
        $aData['thissurvey'] = $thissurvey;
        $aData['surveyid'] = $iSurveyId;
        $aData['subaction'] = $subaction;
        $aData['dateformatdetails'] = getDateFormatData(Yii::app()->session['dateformat']);

        $this->_renderWrappedTemplate(array('tokenbar', 'tokenform'), $aData);
    }

    private function _getTokenIds($aTokenIds)
    {
        if (empty($aTokenIds))
        {
            $aTokenIds = CHttpRequest::getPost('tokenids', false);
        }
        if (!empty($aTokenIds))
        {
            $aTokenIds = explode('|', $aTokenIds);
            $aTokenIds = array_filter($aTokenIds);
            $aTokenIds = array_map('sanitize_int', $aTokenIds);
        }

        return array_unique(array_filter((array) $aTokenIds));
    }

    /**
     * Show dialogs and create a new tokens table
     */
    function _newtokentable($iSurveyId)
    {
        $clang = $this->getController()->lang;
        if (CHttpRequest::getPost('createtable') == "Y" && bHasSurveyPermission($iSurveyId, 'surveyactivation', 'update'))
        {
            $fields = array(
                'tid' => 'int(11) not null auto_increment primary key',
                'participant_id' => 'VARCHAR(50)',
                'firstname' => 'VARCHAR(40)',
                'lastname' => 'VARCHAR(40)',
                'email' => 'text',
                'emailstatus' => 'text',
                'token' => 'VARCHAR(35)',
                'language' => 'VARCHAR(25)',
                'blacklisted' => 'CHAR(17)',
                'sent' => 'VARCHAR(17) DEFAULT "N"',
                'remindersent' => 'VARCHAR(17) DEFAULT "N"',
                'remindercount' => 'INT(11) DEFAULT 0',
                'completed' => 'VARCHAR(17) DEFAULT "N"',
                'usesleft' => 'INT(11) DEFAULT 1',
                'validfrom' => 'DATETIME',
                'validuntil' => 'DATETIME',
                'mpid' => 'INT(11)'
            );
            $comm = Yii::app()->db->createCommand();
            $comm->createTable('{{tokens_' . $iSurveyId . '}}', $fields);

            //$tabname = "{$dbprefix}tokens_{$iSurveyId}"; # not using db_table_name as it quotes the table name (as does CreateTableSQL)
            /* $taboptarray = array('mysql' => 'ENGINE='.$aDatabasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci',
              'mysqli' => 'ENGINE='.$aDatabasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci');
              $dict = NewDataDictionary($connect);
              $sqlarray = $dict->CreateTableSQL($tabname, $createtokentable, $taboptarray);
              $execresult=$dict->ExecuteSQLArray($sqlarray, false);

              $createtokentableindex = $dict->CreateIndexSQL("{$tabname}_idx", $tabname, array('token'));
              $dict->ExecuteSQLArray($createtokentableindex, false) or die ("Failed to create token table index<br />$createtokentableindex<br /><br />".$connect->ErrorMsg());
              if ($connect->databaseType == 'mysql' || $connect->databaseType == 'mysqli')
              {
              $query = 'CREATE INDEX idx_'.$tabname.'_efl ON '.$tabname.' ( email(120), firstname, lastname )';
              $result=$connect->Execute($query) or die("Failed Rename!<br />".$query."<br />".$connect->ErrorMsg());
              } */

            $this->_renderWrappedTemplate(array('message' =>array(
                'title' => $clang->gT("Token control"),
                'message' => $clang->gT("A token table has been created for this survey.") . " (\"" . Yii::app()->db->tablePrefix . "tokens_$iSurveyId\")<br /><br />\n"
                    . "<input type='submit' value='"
                    . $clang->gT("Continue") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/index/surveyid/$iSurveyId") . "', '_top')\" />\n"
            )));
        }
        elseif (returnglobal('restoretable') == "Y" && tableExists(CHttpRequest::getPost('oldtable')) && bHasSurveyPermission($iSurveyId, 'surveyactivation', 'update'))
        {
            Yii::app()->db->createCommand()->renameTable(CHttpRequest::getPost('oldtable'), "{{tokens_$iSurveyId}}");

            $this->_renderWrappedTemplate(array('message' => array(
                'title' => $clang->gT("Import old tokens"),
                'message' => $clang->gT("A token table has been created for this survey and the old tokens were imported.") . " (\"" . Yii::app()->db->tablePrefix . "tokens_$iSurveyId" . "\")<br /><br />\n"
                    . "<input type='submit' value='"
                    . $clang->gT("Continue") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/index/surveyid/$iSurveyId") . "', '_top')\" />\n"
            )));
        }
        else
        {
            $this->getController()->loadHelper('database');
            $result = Yii::app()->db->createCommand(db_select_tables_like('{{old_tokens_' . $iSurveyId . '_%}}'))->queryAll();
            $tcount = count($result);
            if ($tcount > 0)
            {
                foreach ($result as $rows)
                {
                    $oldlist[] = reset($rows);
                }
                $aData['oldlist'] = $oldlist;
            }

            $thissurvey = getSurveyInfo($iSurveyId);
            $aData['thissurvey'] = $thissurvey;
            $aData['surveyid'] = $iSurveyId;
            $aData['tcount'] = $tcount;
            $aData['databasetype'] = Yii::app()->db->getDriverName();

            $this->_renderWrappedTemplate('tokenwarning', $aData);
        }
    }

    function getSearch_json($iSurveyId)
    {
        $page = CHttpRequest::getPost('page');
        $limit = CHttpRequest::getPost('rows');
        $fields = array('tid', 'firstname', 'lastname', 'email', 'emailstatus', 'token', 'language', 'sent', 'sentreminder', 'remindercount', 'completed', 'usesleft', 'validfrom', 'validuntil');
        $searchcondition = CHttpRequest::getQuery('search');
        $searchcondition = urldecode($searchcondition);
        $finalcondition = array();
        $condition = explode("||", $searchcondition);
        $aData = new Object();
        if (count($condition) == 3)
        {
            $records = Tokens_dynamic::model($iSurveyId)->getSearch($condition, $page, $limit);
            $aData->records = count(Tokens_dynamic::model($iSurveyId)->getSearch($condition, 0, 0));
        }
        else
        {
            $records = Tokens_dynamic::model($iSurveyId)->getSearchMultiple($condition, $page, $limit);
            $aData->records = count(Tokens_dynamic::model($iSurveyId)->getSearchMultiple($condition, 0, 0));
        }
        $aData->page = $page;
        $aData->total = ceil($aData->records / $limit);

        $i = 0;
        foreach ($records as $row => $value)
        {
            $sortablearray[$i] = array($value['tid'], $value['firstname'], $value['lastname'], $value['email'], $value['emailstatus'], $value['token'], $value['language'], $value['sent'], $value['remindersent'], $value['remindercount'], $value['completed'], $value['usesleft'], $value['validfrom'], $value['validuntil']);
            $i++;
        }

        function subval_sort($a, $subkey, $order)
        {
            foreach ($a as $k => $v)
            {
                $b[$k] = strtolower($v[$subkey]);
            }
            if ($order == "asc")
            {
                asort($b, SORT_REGULAR);
            }
            else
            {
                arsort($b, SORT_REGULAR);
            }
            foreach ($b as $key => $val)
            {
                $c[] = $a[$key];
            }
            return $c;
        }

        if (!empty($sortablearray))
        {
            $indexsort = array_search(CHttpRequest::getPost('sidx'), $fields);
            $sortedarray = subval_sort($sortablearray, $indexsort, CHttpRequest::getPost('sord'));
            $i = 0;
            $count = count($sortedarray[0]);
            foreach ($sortedarray as $key => $value)
            {
                $aData->rows[$i]['id'] = $value[0];
                $aData->rows[$i]['cell'] = array();
                for ($j = 0; $j < $count; $j++)
                {
                    array_push($aData->rows[$i]['cell'], $value[$j]);
                }
                $i++;
            }
        }
        echo ls_json_encode($aData);
    }

}
