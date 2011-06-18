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

// Security Checked: POST, GET, SESSION, REQUEST, returnglobal, DB

require_once(dirname(__FILE__).'/../classes/core/startup.php');

// XML code for LS1.70 is based on the experimental PHP4 domxml
// extension. PHP5 uses the PHP5/dom extension unless the old domxml is activated
// the following file is a wrapper to use PHP4/domxml scripts
// with PHP5/dom or PHP6/dom
// see http://alexandre.alapetite.net/doc-alex/domxml-php4-php5/index.en.html#licence
if (version_compare(PHP_VERSION,'5','>=')&& !(function_exists('domxml_new_doc')))
{
    require_once(dirname(__FILE__).'/classes/core/domxml-php4-to-php5.php');
}
require_once(dirname(__FILE__).'/../config-defaults.php');
require_once(dirname(__FILE__).'/../common.php');

require_once('htmleditor-functions.php');
//@ini_set('session.gc_maxlifetime', $sessionlifetime);     Might cause problems in client??

// Reset FileManagerContext
$_SESSION['FileManagerContext']='';

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}         //SurveyID
if (!isset($ugid)) {$ugid=returnglobal('ugid');}                //Usergroup-ID
if (!isset($gid)) {$gid=returnglobal('gid');}                   //GroupID
if (!isset($qid)) {$qid=returnglobal('qid');}                   //QuestionID
if (!isset($lid)) {$lid=returnglobal('lid');}                   //LabelID
if (!isset($code)) {$code=returnglobal('code');}                // ??
if (!isset($action)) {$action=returnglobal('action');}          //Desired action
if (!isset($subaction)) {$subaction=returnglobal('subaction');} //Desired subaction
if (!isset($editedaction)) {$editedaction=returnglobal('editedaction');} // for html editor integration


if ($action != 'showprintablesurvey' && substr($action,0,4)!= 'ajax')
{
    $adminoutput="<div id='wrapper'>";
}
else
{
    $adminoutput='';
}

if($casEnabled==true)
{
    include_once("login_check_cas.php");
}
else
{
    include_once('login_check.php');
}

if ( $action == 'CSRFwarn')
{
    include('access_denied.php');
}

if ( $action == 'FakeGET')
{
    include('access_denied.php');
}

if(isset($_SESSION['loginID']))
{
    //VARIOUS DATABASE OPTIONS/ACTIONS PERFORMED HERE
    if (in_array($action, array('updateemailtemplates','delsurvey','delgroup','delquestion','insertsurvey','updatesubquestions','copynewquestion','insertquestiongroup','insertCSV','insertquestion','updatesurveysettings','updatesurveysettingsandeditlocalesettings','updatesurveylocalesettings','updategroup','deactivate','savepersonalsettings','updatequestion','updateansweroptions','renumberquestions','updatedefaultvalues')))
    {
        include('database.php');
    }

    sendcacheheaders();

    /* Check user right actions for validity
     Currently existing user rights:
     `configurator`
     `create_survey`
     `create_user`
     `delete_user`
     `manage_label`
     `manage_template`
     `superadmin`
     */

    if ($action == 'importsurvey' || $action == 'copysurvey')
    {
        if ($_SESSION['USER_RIGHT_CREATE_SURVEY']==1)	{include('http_importsurvey.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'dumpdb')
    {
        if ($_SESSION['USER_RIGHT_CONFIGURATOR']==1)  {include('dumpdb.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'dumplabel')
    {
        if ($_SESSION['USER_RIGHT_MANAGE_LABEL']==1)  {include('dumplabel.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'exportlabelresources')
    {
        if ($_SESSION['USER_RIGHT_MANAGE_TEMPLATE']==1)  {$_SESSION['FileManagerContext']="edit:label:$lid"; include('export_resources_zip.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'checkintegrity')
    {
        if ($_SESSION['USER_RIGHT_CONFIGURATOR']==1)  {include('integritycheck.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == "globalsettings")
    {
        if ($_SESSION['USER_RIGHT_CONFIGURATOR']==1)  {globalsettingsdisplay();}
        else { include("access_denied.php");}
    }
    elseif ($action == "globalsettingssave")
    {
        if ($_SESSION['USER_RIGHT_CONFIGURATOR']==1)  {globalsettingssave();}
        else { include("access_denied.php");}
    }
    elseif ($action=='labels' || $action=='newlabelset' || $action=='insertlabelset' ||
    $action=='deletelabelset' || $action=='editlabelset' || $action=='modlabelsetanswers' ||
    $action=='updateset' || $action=='importlabels' ||$action == 'importlabelresources')
    {
        if ($_SESSION['USER_RIGHT_MANAGE_LABEL']==1)  {$_SESSION['FileManagerContext']="edit:label:$lid"; include('labels.php');}
        else { include('access_denied.php');}
    }
    elseif ($action=='templates' || $action=='templatecopy' || $action=='templatesavechanges' ||
    $action=='templaterename' || $action=='templateuploadfile' || $action=='templatefiledelete' ||
    $action=='templatezip'  || $action=='templaterefresh' || $action=='templateupload')
    {
        if ($_SESSION['USER_RIGHT_MANAGE_TEMPLATE']==1)  {include('templates.php');}
        else { include('access_denied.php');}
    }




    /* Check survey right actions for validity
     Currently existing survey rights:
     `edit_survey_property`
     `define_questions`
     `browse_response`
     `export`
     `delete_survey`
     `activate_survey`
     */

    if ($action == 'activate')
    {
        if(bHasSurveyPermission($surveyid,'surveyactivation','update'))    {include('activate.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'conditions')
    {
        if(bHasSurveyPermission($surveyid,'surveycontent','read'))    {include('conditionshandling.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'importsurveyresources')
    {
        if (bHasSurveyPermission($surveyid,'surveycontent','import'))	{$_SESSION['FileManagerContext']="edit:survey:$surveyid";include('import_resources_zip.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'exportstructureLsrcCsv')
    {
        if(bHasSurveyPermission($surveyid,'surveycontent','export'))    {include('export_structure_lsrc.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'exportstructurequexml')
    {
        if(bHasSurveyPermission($surveyid,'surveycontent','export'))    {include('export_structure_quexml.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'exportstructurexml')
    {
        if(bHasSurveyPermission($surveyid,'surveycontent','export'))    {include('export_structure_xml.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'exportstructurecsvGroup')
    {
        if(bHasSurveyPermission($surveyid,'surveycontent','export'))    {include('dumpgroup.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'exportstructureLsrcCsvGroup')
    {
        if(bHasSurveyPermission($surveyid,'surveycontent','export'))    {include('dumpgroup.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'exportstructurecsvQuestion')
    {
        if(bHasSurveyPermission($surveyid,'surveycontent','export'))    {include('dumpquestion.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'exportstructureLsrcCsvQuestion')
    {
        if(bHasSurveyPermission($surveyid,'surveycontent','export'))    {include('dumpquestion.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'exportsurvresources')
    {
        if(bHasSurveyPermission($surveyid,'surveycontent','export'))    {$_SESSION['FileManagerContext']="edit:survey:$surveyid";include('export_resources_zip.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'deactivate')
    {
        if(bHasSurveyPermission($surveyid,'surveyactivation','update'))    {include('deactivate.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'deletesurvey')
    {
        if(bHasSurveyPermission($surveyid,'survey','delete'))    {include('deletesurvey.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'resetsurveylogic')
    {
        if(bHasSurveyPermission($surveyid,'surveycontent','update'))    {include('resetsurveylogic.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'importgroup')
    {
        if(bHasSurveyPermission($surveyid,'surveycontent','import'))    {include('importgroup.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'importquestion')
    {
        if(bHasSurveyPermission($surveyid,'surveycontent','import'))    {include('importquestion.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'listcolumn')
    {
        if(bHasSurveyPermission($surveyid,'statistics','read'))    {include('listcolumn.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'previewquestion')
    {
        if(bHasSurveyPermission($surveyid,'surveycontent','read'))    {include('preview.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'previewgroup')
    {
	
        require_once('../index.php');
        exit;
    
    }
    elseif ($action=='addgroup' || $action=='editgroup' || $action=='ordergroups')
    {
        if(bHasSurveyPermission($surveyid,'surveycontent','read'))    {$_SESSION['FileManagerContext']="edit:group:$surveyid"; include('questiongrouphandling.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'saved')
    {
        if(bHasSurveyPermission($surveyid,'responses','read'))    {include('saved.php');}
        else { include('access_denied.php');}
    }
//<AdV>
    elseif ($action == 'translate')
    {
        if(bHasSurveyPermission($surveyid,'translations','read'))    {$_SESSION['FileManagerContext']="edit:translate:$surveyid"; include('translate.php');}
        else { include('access_denied.php'); }
    }
//</AdV>    
    elseif ($action == 'tokens')
    {
        if(bHasSurveyPermission($surveyid,'tokens','read'))    
        {
            $_SESSION['FileManagerContext']="edit:emailsettings:$surveyid"; 
            include('tokens.php'); 
        }
        else { include('access_denied.php'); }
    }
    elseif ($action == 'iteratesurvey')
    {
        if(bHasSurveyPermission($surveyid,'surveyactivation','update'))    {include('iterate_survey.php');}
        else { include('access_denied.php');}
    }
    elseif ($action=='showquexmlsurvey')
    {
        include('quexmlsurvey.php'); //Same rights as printable
    }
    elseif ($action=='showprintablesurvey')
    {
        include('printablesurvey.php'); //No special right needed to show the printable survey
    }
    elseif ($action=='listcolumn')
    {
        include('listcolumn.php');
    }
    elseif ($action=='update')
    {
        if( $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    include($homedir.'/update/updater.php');
        else { include('access_denied.php');}
    }
    elseif ($action=='assessments' || $action=='assessmentdelete' || $action=='assessmentedit' || $action=='assessmentadd' || $action=='assessmentupdate')
    {
        if(bHasSurveyPermission($surveyid,'assessments','read'))    {
            $_SESSION['FileManagerContext']="edit:assessments:$surveyid";
            include('assessments.php');
        }
        else { include('access_denied.php');}
    }
    elseif ($action == 'replacementfields')
    {
        switch ($editedaction)
        {
            case 'labels':
                if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $_SESSION['USER_RIGHT_MANAGE_LABEL']==1)
                {
                    $_SESSION['FileManagerContext']="edit:label:$lid";
                    include('fck_LimeReplacementFields.php');exit;
                }
                else
                {
                    include('access_denied.php');
                }
                break;
            case 'newsurvey':
                if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $_SESSION['USER_RIGHT_CREATE_SURVEY'] == 1)
                {
                    include('fck_LimeReplacementFields.php');exit;
                }
                else
                {
                    include('access_denied.php');
                }
                break;
            case 'editsurveylocalesettings':
            case 'updatesurveysettingsandeditlocalesettings':
            case 'translatetitle':
            case 'translatedescription':
            case 'translatewelcome':
            case 'translateend':
                if (bHasSurveyPermission($surveyid,'surveysettings','update') && bHasSurveyPermission($surveyid,'surveylocale','read'))
                {
                    $_SESSION['FileManagerContext']="edit:survey:$surveyid";
                    include('fck_LimeReplacementFields.php');exit;
                }
                else
                {
                    include('access_denied.php');
                }
                break;
            case 'tokens': // email
            case 'emailtemplates': // email
                if (bHasSurveyPermission($surveyid,'tokens','update'))
                {
                    $_SESSION['FileManagerContext']="edit:emailsettings:$surveyid";
                    include('fck_LimeReplacementFields.php');exit;
                }
                else
                {
                    include('access_denied.php');
                }
                break;
            case 'editquestion':
            case 'copyquestion':
            case 'addquestion':
            case 'translatequestion':
            case 'translatequestion_help':
                if (bHasSurveyPermission($surveyid,'surveycontent','read'))
                {
                    $_SESSION['FileManagerContext']="edit:question:$surveyid";
                    include('fck_LimeReplacementFields.php');exit;
                }
                else
                {
                    include('access_denied.php');
                }
                break;
            case 'editgroup':
            case 'addgroup':
            case 'translategroup':
            case 'translategroup_desc':
                if (bHasSurveyPermission($surveyid,'surveycontent','read'))
                {
                    $_SESSION['FileManagerContext']="edit:group:$surveyid";
                    include('fck_LimeReplacementFields.php');exit;
                }
                else
                {
                    include('access_denied.php');
                }
                break;
            case 'editanswer':
            case 'translateanswer':
                if (bHasSurveyPermission($surveyid,'surveycontent','read'))
                {
                    $_SESSION['FileManagerContext']="edit:answer:$surveyid";
                    include('fck_LimeReplacementFields.php');exit;
                }
                else
                {
                    include('access_denied.php');
                }
                break;
            case 'assessments':
            case 'assessmentedit':
                if(bHasSurveyPermission($surveyid,'assessments','read'))    {
                    $_SESSION['FileManagerContext']="edit:assessments:$surveyid";
                    include('fck_LimeReplacementFields.php');
                }
                else { include('access_denied.php');}
                break;
            default:
                break;
        }
    }
    elseif ($action == 'ajaxtranslategoogleapi')
    {
        if(bHasSurveyPermission($surveyid,'translations','read'))
        {
            include('translate_google_api.php');
        }
        else
        {
            include('access_denied.php');
        }
    }
    elseif ($action=='ajaxowneredit' || $action == 'ajaxgetusers'){
        
        include('surveylist.php');
    }
    if (!isset($assessmentsoutput) && !isset($statisticsoutput) && !isset($browseoutput) &&
        !isset($savedsurveyoutput) && !isset($listcolumnoutput) && !isset($conditionsoutput) && 
        !isset($importoldresponsesoutput) && !isset($exportroutput) && !isset($vvoutput) &&
        !isset($tokenoutput) && !isset($exportoutput) && !isset($templatesoutput) && !isset($translateoutput) && //<AdV>
        !isset($iteratesurveyoutput) && (substr($action,0,4)!= 'ajax') && ($action!='update') &&
        (isset($surveyid) || $action == "" || preg_match('/^(personalsettings|statistics|copysurvey|importsurvey|editsurveysettings|editsurveylocalesettings|updatesurveysettings|updatesurveysettingsandeditlocalesettings|updatedefaultvalues|ordergroups|dataentry|newsurvey|globalsettings|editusergroups|editusergroup|exportspss|surveyrights|quotas|editusers|login|browse|vvimport|vvexport|setuserrights|modifyuser|setusertemplates|deluser|adduser|userrights|usertemplates|moduser|addusertogroup|deleteuserfromgroup|globalsettingssave|savepersonalsettings|addusergroup|editusergroupindb|usergroupindb|finaldeluser|delusergroup|mailusergroup|mailsendusergroup)$/',$action)))
    {
        if ($action=='editsurveysettings' || $action=='editsurveylocalesettings')
        {
            $_SESSION['FileManagerContext']="edit:survey:$surveyid";
        }
        include('html_functions.php');
        include('html.php');
    }

    if ($action == "listsurveys"){
        include('html_functions.php');
        include('html.php');
        include('surveylist.php');
    }

    if ($action == 'dataentry')
    {
        if (bHasSurveyPermission($surveyid, 'responses','read') || bHasSurveyPermission($surveyid, 'responses','create')  || bHasSurveyPermission($surveyid, 'responses','update'))
        {
            include('dataentry.php');
        }
        else
        {
            include('access_denied.php');
        }
    }
    elseif ($action == 'exportresults')
    {
        if(bHasSurveyPermission($surveyid,'responses','export'))    {include('exportresults.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'statistics')
    {
        if(bHasSurveyPermission($surveyid,'statistics','read'))    {include('statistics.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'importoldresponses')
    {
        if(bHasSurveyPermission($surveyid,'responses','create'))    {include('importoldresponses.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'exportspss')
    {
        if(bHasSurveyPermission($surveyid,'responses','export'))
        {
            include('export_data_spss.php');
        }
        else
        {
            include('access_denied.php');
        }
    }
    elseif ($action == 'browse')
    {
        if(bHasSurveyPermission($surveyid,'responses','read') || bHasSurveyPermission($surveyid,'statistics','read'))
        {
            include('browse.php');
        }
        else
        {
            include('access_denied.php');
        }
    }
    elseif ($action == 'exportr')
    {
        if(bHasSurveyPermission($surveyid,'responses','export'))    {include('export_data_r.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'vvexport')
    {
        if(bHasSurveyPermission($surveyid,'responses','export'))    {include('vvexport.php');}
        else { include('access_denied.php');}
    }
    elseif ($action == 'vvimport')
    {
        if(bHasSurveyPermission($surveyid,'responses','create'))    {include('vvimport.php');}
        else { include('access_denied.php');}
    }
    if ($action=='addquestion'    || $action=='copyquestion' || $action=='editquestion' || $action=='editdefaultvalues' ||
        $action=='orderquestions' || $action=='ajaxquestionattributes' || $action=='ajaxlabelsetpicker' || $action=='ajaxlabelsetdetails')
    {
        if(bHasSurveyPermission($surveyid,'surveycontent','read'))
        {
            $_SESSION['FileManagerContext']="edit:question:$surveyid";
            include('questionhandling.php');
        }
        else
        {
            include('access_denied.php');
        }
    }


    if ($action=='adduser' || $action=='deluser'|| $action=='finaldeluser' || $action=='moduser' || $action=='setusertemplates' || $action=='usertemplates' ||                                        //Still to check
    $action=='userrights' || $action=='modifyuser' || $action=='editusers' ||
    $action=='addusergroup' || $action=='editusergroup' || $action=='mailusergroup' ||
    $action=='delusergroup' || $action=='usergroupindb' || $action=='mailsendusergroup' ||
    $action=='editusergroupindb' || $action=='editusergroups' || $action=='deleteuserfromgroup' ||
    $action=='addusertogroup' || $action=='setuserrights' || $action=='setasadminchild')
    {
        include ('userrighthandling.php');
    }


    // For some output we dont want to have the standard admin menu bar
    if (!isset($labelsoutput)  && !isset($templatesoutput) && !isset($printablesurveyoutput) &&
    !isset($assessmentsoutput) && !isset($tokenoutput) && !isset($browseoutput) && !isset($exportspssoutput) &&  !isset($exportroutput) &&
    !isset($dataentryoutput) && !isset($statisticsoutput)&& !isset($savedsurveyoutput)  && !isset($translateoutput) && //<AdV>
    !isset($exportoutput) && !isset($importoldresponsesoutput) && !isset($conditionsoutput) && 
    !isset($vvoutput) && !isset($listcolumnoutput) && !isset($importlabelresources) && !isset($iteratesurveyoutput) &&
    (substr($action,0,4)!= 'ajax') && $action!='update' && $action!='showphpinfo')
    {
        $adminoutput.= showadminmenu();
    }

    if (isset($databaseoutput))  {$adminoutput.= $databaseoutput;}
    if (isset($templatesoutput)) {$adminoutput.= $templatesoutput;}
    if (isset($accesssummary  )) {$adminoutput.= $accesssummary;}
    if (isset($surveysummary  )) {$adminoutput.= $surveysummary;}
    if (isset($usergroupsummary)){$adminoutput.= $usergroupsummary;}
    if (isset($usersummary    )) {$adminoutput.= $usersummary;}
    if (isset($groupsummary   )) {$adminoutput.= $groupsummary;}
    if (isset($questionsummary)) {$adminoutput.= $questionsummary;}
    if (isset($vasummary      )) {$adminoutput.= $vasummary;}
    if (isset($addsummary     )) {$adminoutput.= $addsummary;}
    if (isset($answersummary  )) {$adminoutput.= $answersummary;}
    if (isset($cssummary      )) {$adminoutput.= $cssummary;}
    if (isset($listcolumnoutput)) {$adminoutput.= $listcolumnoutput;}
    if (isset($ajaxoutput)) {$adminoutput.= $ajaxoutput;}


    if (isset($editgroup)) {$adminoutput.= $editgroup;}
    if (isset($editquestion)) {$adminoutput.= $editquestion;}
    if (isset($editdefvalues)) {$adminoutput.= $editdefvalues;}
    if (isset($editsurvey)) {$adminoutput.= $editsurvey;}
    if (isset($translateoutput)) {$adminoutput.= $translateoutput;}  //<AdV>
    if (isset($quotasoutput)) {$adminoutput.= $quotasoutput;}
    if (isset($labelsoutput)) {$adminoutput.= $labelsoutput;}
    if (isset($listsurveys)) {$adminoutput.= $listsurveys; }
    if (isset($integritycheck)) {$adminoutput.= $integritycheck;}
    if (isset($ordergroups)){$adminoutput.= $ordergroups;}
    if (isset($orderquestions)) {$adminoutput.= $orderquestions;}
    if (isset($surveysecurity)) {$adminoutput.= $surveysecurity;}
    if (isset($exportstructure)) {$adminoutput.= $exportstructure;}
    if (isset($newsurvey)) {$adminoutput.= $newsurvey;}
    if (isset($newgroupoutput)) {$adminoutput.= $newgroupoutput;}
    if (isset($newquestionoutput)) {$adminoutput.= $newquestionoutput;}
    if (isset($newanswer)) {$adminoutput.= $newanswer;}
    if (isset($editanswer)) {$adminoutput.= $editanswer;}
    if (isset($assessmentsoutput)) {$adminoutput.= $assessmentsoutput;}
    if (isset($sHTMLOutput))     {$adminoutput.= $sHTMLOutput;}
    

    if (isset($importsurvey)) {$adminoutput.= $importsurvey;}
    if (isset($importsurveyresourcesoutput)) {$adminoutput.= $importsurveyresourcesoutput;}
    if (isset($importgroup)) {$adminoutput.= $importgroup;}
    if (isset($importquestion)) {$adminoutput.= $importquestion;}
    if (isset($printablesurveyoutput)) {$adminoutput.= $printablesurveyoutput;}
    if (isset($activateoutput)) {$adminoutput.= $activateoutput;}
    if (isset($deactivateoutput)) {$adminoutput.= $deactivateoutput;}
    if (isset($tokenoutput)) {$adminoutput.= $tokenoutput;}
    if (isset($browseoutput)) {$adminoutput.= $browseoutput;}
    if (isset($iteratesurveyoutput)) {$adminoutput.= $iteratesurveyoutput;}
    if (isset($dataentryoutput)) {$adminoutput.= $dataentryoutput;}
    if (isset($statisticsoutput)) {$adminoutput.= $statisticsoutput;}
    if (isset($exportoutput)) {$adminoutput.= $exportoutput;}
    if (isset($savedsurveyoutput)) {$adminoutput.= $savedsurveyoutput;}
    if (isset($importoldresponsesoutput)) {$adminoutput.= $importoldresponsesoutput;}
    if (isset($conditionsoutput)) {$adminoutput.= $conditionsoutput;}
    if (isset($deletesurveyoutput)) {$adminoutput.= $deletesurveyoutput;}
    if (isset($resetsurveylogicoutput)) {$adminoutput.= $resetsurveylogicoutput;}
    if (isset($vvoutput)) {$adminoutput.= $vvoutput;}
    if (isset($dumpdboutput)) {$adminoutput.= $dumpdboutput;}
    if (isset($exportspssoutput)) {$adminoutput.= $exportspssoutput;}
    if (isset($exportroutput)) {$adminoutput.= $exportroutput;}
    if (isset($loginsummary)) {$adminoutput.= $loginsummary;}


    if (!isset($printablesurveyoutput) && $subaction!='export' && (substr($action,0,4)!= 'ajax'))
    {
        if (!isset($_SESSION['metaHeader'])) {$_SESSION['metaHeader']='';}
        $adminoutput = getAdminHeader($_SESSION['metaHeader']).$adminoutput;  // All future output is written into this and then outputted at the end of file
        unset($_SESSION['metaHeader']);
        $adminoutput.= "</div>\n";
        if(!isset($_SESSION['checksessionpost']))
        {
            $_SESSION['checksessionpost'] = '';
        }
        $adminoutput .= "<script type=\"text/javascript\">\n"
        . "<!--\n"
        . "\tfor(i=0; i<document.forms.length; i++)\n"
        . "\t{\n"
        . "var el = document.createElement('input');\n"
        . "el.type = 'hidden';\n"
        . "el.name = 'checksessionbypost';\n"
        . "el.value = '".$_SESSION['checksessionpost']."';\n"
        . "document.forms[i].appendChild(el);\n"
        . "\t}\n"
        . "\n"
        . "\tfunction addHiddenElement(theform,thename,thevalue)\n"
        . "\t{\n"
        . "var myel = document.createElement('input');\n"
        . "myel.type = 'hidden';\n"
        . "myel.name = thename;\n"
        . "theform.appendChild(myel);\n"
        . "myel.value = thevalue;\n"
        . "return myel;\n"
        . "\t}\n"
        . "\n"
        . "\tfunction sendPost(myaction,checkcode,arrayparam,arrayval)\n"
        . "\t{\n"
        . "var myform = document.createElement('form');\n"
        . "document.body.appendChild(myform);\n"
        . "myform.action =myaction;\n"
        . "myform.method = 'POST';\n"
        . "for (i=0;i<arrayparam.length;i++)\n"
        . "{\n"
        . "\taddHiddenElement(myform,arrayparam[i],arrayval[i])\n"
        . "}\n"
        . "addHiddenElement(myform,'checksessionbypost',checkcode)\n"
        . "myform.submit();\n"
        . "\t}\n"
        . "\n"
        . "//-->\n"
        . "</script>\n";
        $adminoutput .= getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
    }

}
else
{ //not logged in

sendcacheheaders();
if (!isset($_SESSION['metaHeader'])) {$_SESSION['metaHeader']='';}
$adminoutput = getAdminHeader($_SESSION['metaHeader']).$adminoutput.$loginsummary;  // All future output is written into this and then outputted at the end of file
unset($_SESSION['metaHeader']);
$adminoutput.= "</div>\n".getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
}
if (($action=='showphpinfo') && ($_SESSION['USER_RIGHT_CONFIGURATOR'] == 1))
{
    phpinfo();
}
else
{
    echo $adminoutput;
}

