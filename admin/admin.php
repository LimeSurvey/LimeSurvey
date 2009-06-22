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

if (!isset($adminlang)) {$adminlang=returnglobal('adminlang');} // Admin language
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}         //SurveyID
if (!isset($ugid)) {$ugid=returnglobal('ugid');}                //Usergroup-ID
if (!isset($gid)) {$gid=returnglobal('gid');}                   //GroupID
if (!isset($qid)) {$qid=returnglobal('qid');}                   //QuestionID
if (!isset($lid)) {$lid=returnglobal('lid');}                   //LabelID
if (!isset($code)) {$code=returnglobal('code');}                // ??
if (!isset($action)) {$action=returnglobal('action');}          //Desired action
if (!isset($subaction)) {$subaction=returnglobal('subaction');} //Desired subaction
if (!isset($editedaction)) {$editedaction=returnglobal('editedaction');} // for html editor integration



if ($action != 'showprintablesurvey')
{
  $adminoutput = helpscreenscript();
  $adminoutput .= "<table width='100%' border='0' cellpadding='0' cellspacing='0' >\n"
  ."\t<tr>\n"
  ."\t\t<td valign='top' align='center' bgcolor='#F8F8FF'>\n";
} else {$adminoutput='';}

if($casEnabled)
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

if(isset($_SESSION['loginID']) && $action!='login')
{
  //VARIOUS DATABASE OPTIONS/ACTIONS PERFORMED HERE
  if ($action == 'delsurvey'         || $action == 'delgroup'       || 
      $action == 'delquestion'       || $action == 'insertnewsurvey'||
      $action == 'copynewquestion'   || $action == 'insertnewgroup' || $action == 'insertCSV'         ||
      $action == 'insertnewquestion' || $action == 'updatesurvey'   || $action == 'updatesurvey2'     || 
      $action == 'updategroup'       || $action == 'deactivate'     || $action == 'savepersonalsettings' ||
      $action == 'updatequestion'    || $action == 'modanswer'      || $action == 'renumberquestions' ||
      $action == 'delattribute'      || $action == 'addattribute'   || $action == 'editattribute')
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
    
if ($action == 'importsurvey') 
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

if (isset($surveyid) && $surveyid)
{
$surquery = "SELECT * FROM {$dbprefix}surveys_rights WHERE sid=".db_quote($surveyid)." AND uid = ".db_quote($_SESSION['loginID']); //Getting rights for this survey
$surresult = db_execute_assoc($surquery);   
$surrows = $surresult->FetchRow();
}

if ($action == 'activate')
    {
    if($surrows['activate_survey'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('activate.php');}
        else { include('access_denied.php');}    
    }
elseif ($action == 'conditions')
{
    if($surrows['define_questions'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('conditionshandling.php');}
        else { include('access_denied.php');}    
    }    
elseif ($action == 'importsurvresources') 
  { 
      if ($surrows['define_questions'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)	{$_SESSION['FileManagerContext']="edit:survey:$surveyid";include('import_resources_zip.php');}
	    else { include('access_denied.php');}
  }      
elseif ($action == 'exportstructurecsv')
    {
    if($surrows['export'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('export_structure_csv.php');}
        else { include('access_denied.php');}    
    }
elseif ($action == 'exportstructureLsrcCsv')
    {
    if($surrows['export'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('export_structure_lsrc.php');}
        else { include('access_denied.php');}    
    }    
elseif ($action == 'exportstructurequexml')
    {
    if($surrows['export'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('export_structure_quexml.php');}
        else { include('access_denied.php');}    
    }    
elseif ($action == 'exportsurvresources')
    {
    if($surrows['export'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {$_SESSION['FileManagerContext']="edit:survey:$surveyid";include('export_resources_zip.php');}
        else { include('access_denied.php');}    
    }    
elseif ($action == 'dumpquestion')
    {
    if($surrows['export'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('dumpquestion.php');}
        else { include('access_denied.php');}    
    }    
elseif ($action == 'dumpgroup')
    {
    if($surrows['export'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('dumpgroup.php');}
        else { include('access_denied.php');}    
    }    
elseif ($action == 'deactivate')
    {
    if($surrows['activate_survey'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('deactivate.php');}
        else { include('access_denied.php');}    
    }
elseif ($action == 'deletesurvey')
    {
    if($surrows['delete_survey'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('deletesurvey.php');}
        else { include('access_denied.php');}    
    }    
elseif ($action == 'resetsurveylogic')
    {
    if($surrows['define_questions'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('resetsurveylogic.php');}
        else { include('access_denied.php');}    
    }    
elseif ($action == 'importgroup')
    {
    if($surrows['define_questions'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('importgroup.php');}
        else { include('access_denied.php');}    
    }
elseif ($action == 'importquestion')
    {
    if($surrows['define_questions'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('importquestion.php');}
        else { include('access_denied.php');}    
    }    
elseif ($action == 'listcolumn')
    {
    if($surrows['browse_response'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('listcolumn.php');}
        else { include('access_denied.php');}    
    }    
elseif ($action == 'previewquestion')
    {
    if($surrows['define_questions'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('preview.php');}
        else { include('access_denied.php');}    
    }
elseif ($action=='addgroup' || $action=='editgroup')        
    {
    if($surrows['define_questions'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {$_SESSION['FileManagerContext']="edit:group:$surveyid"; include('grouphandling.php');}
        else { include('access_denied.php');}    
    }
elseif ($action == 'vvexport')
    {
    if($surrows['browse_response'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('vvexport.php');}
        else { include('access_denied.php');}    
    }    
elseif ($action == 'vvimport')
    {
    if($surrows['browse_response'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('vvimport.php');}
        else { include('access_denied.php');}    
    }    
elseif ($action == 'importoldresponses')
    {
    if($surrows['browse_response'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('importoldresponses.php');}
        else { include('access_denied.php');}    
    }    
elseif ($action == 'saved')
    {
    if($surrows['browse_response'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('saved.php');}
        else { include('access_denied.php');}    
    }    
elseif ($action == 'exportresults')
    {
    if($surrows['export'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('exportresults.php');}
        else { include('access_denied.php');}    
    }    
elseif ($action == 'exportspss')
    {
    if($surrows['export'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('export_data_spss.php');}
        else { include('access_denied.php');}    
    }    
elseif ($action == 'exportr')
    {
    if($surrows['export'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('export_data_r.php');}
        else { include('access_denied.php');}    
    }    
elseif ($action == 'statistics')
    {
    if($surrows['browse_response'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('statistics.php');}
        else { include('access_denied.php');}    
    }    
elseif ($action == 'dataentry')
    {
    if($surrows['browse_response'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('dataentry.php');}
        else { include('access_denied.php');}    
    }    
elseif ($action == 'browse')
    {
    if($surrows['browse_response'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('browse.php');}               
        else { include('access_denied.php');}    
    }    
elseif ($action == 'tokens')
    {
    if($surrows['activate_survey'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {$_SESSION['FileManagerContext']="edit:emailsettings:$surveyid"; include('tokens.php');}               
        else { include('access_denied.php'); }    
    }    
elseif ($action == 'iteratesurvey')
    {
    if( ($surrows['browse_response'] && $surrows['activate_survey']) || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {include('iterate_survey.php');}               
        else { include('access_denied.php');}    
    }    
elseif ($action=='showprintablesurvey')  
    { 
        include('printablesurvey.php'); //No special right needed to show the printable survey
    } 
elseif ($action=='assessments' || $action=='assessmentdelete' || $action=='assessmentedit' || $action=='assessmentadd' || $action=='assessmentupdate')
    {
    if($surrows['define_questions'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {
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
		case 'updatesurvey':
			if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $surrows['edit_survey_property'])
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
			if ( $_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $surrows['activate_survey'])
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
			if ( $_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $surrows['define_questions'])
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
			if ( $_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $surrows['define_questions'])
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
			if ( $_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $surrows['define_questions'])
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
			if($surrows['define_questions'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {
				$_SESSION['FileManagerContext']="edit:assessments:$surveyid";
				include('fck_LimeReplacementFields.php');
			}
			else { include('access_denied.php');}    
		break;
		default:
		break;
	}
    }    
    
 if (!isset($assessmentsoutput) && !isset($statisticsoutput) && !isset($browseoutput) && !isset($savedsurveyoutput) && !isset( $listcolumnoutput  ) &&         
     !isset($dataentryoutput) && !isset($conditionsoutput) && !isset($importoldresponsesoutput) && !isset($exportspssoutput) && !isset($exportroutput) &&
     !isset($vvoutput) && !isset($tokenoutput) && !isset($exportoutput) && !isset($templatesoutput) &&  !isset($iteratesurveyoutput) && 
     (isset($surveyid) || $action=='listurveys' || $action=='personalsettings' || $action=='checksettings' ||       //Still to check
      $action=='editsurvey' || $action=='updatesurvey' || $action=='ordergroups'  ||
      $action=='newsurvey' || $action=='listsurveys' ||   
      $action=='surveyrights' || $action=='quotas') )
{
	if ($action=='editsurvey' || $action=='updatesurvey')
	{
		 $_SESSION['FileManagerContext']="edit:survey:$surveyid";
	}
	include('html.php');
}

 if ($action=='addquestion' || $action=='copyquestion' || $action=='editquestion' || 
     $action=='orderquestions' || $action=='editattribute' || $action=='delattribute' || 
     $action=='addattribute' )
    {if($surrows['define_questions'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)    {$_SESSION['FileManagerContext']="edit:question:$surveyid";include('questionhandling.php');}
        else { include('access_denied.php');}    
    }    

      
 if ($action=='adduser' || $action=='deluser' || $action=='moduser' || $action=='setusertemplates' || $action=='usertemplates' ||                                        //Still to check 
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
      !isset($dataentryoutput) && !isset($statisticsoutput)&& !isset($savedsurveyoutput) &&
      !isset($exportoutput) && !isset($importoldresponsesoutput) && !isset($conditionsoutput) &&
      !isset($vvoutput) && !isset($listcolumnoutput) && !isset($importlabelresources) && !isset($iteratesurveyoutput)) 
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

  
  if (isset($editgroup)) {$adminoutput.= $editgroup;}
  if (isset($editquestion)) {$adminoutput.= $editquestion;}
  if (isset($editsurvey)) {$adminoutput.= $editsurvey;}
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

  if (isset($importsurvey)) {$adminoutput.= $importsurvey;}
  if (isset($importsurvresourcesoutput)) {$adminoutput.= $importsurvresourcesoutput;}
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
                                                                        
  
  if (!isset($printablesurveyoutput) && ($subaction!='export'))
  {  
  if (!isset($_SESSION['metaHeader'])) {$_SESSION['metaHeader']='';}
  
  $adminoutput = getAdminHeader($_SESSION['metaHeader']).$adminoutput;  // All future output is written into this and then outputted at the end of file
  unset($_SESSION['metaHeader']);    
  $adminoutput.= "\t\t</td>\n".helpscreen()
              . "\t</tr>\n"
              . "</table>\n";
	if(!isset($_SESSION['checksessionpost']))
		$_SESSION['checksessionpost'] = '';
	$adminoutput .= "<script type=\"text/javascript\">\n"
	. "<!--\n"
	. "\tfor(i=0; i<document.forms.length; i++)\n"
	. "\t{\n"
	. "\t\tvar el = document.createElement('input');\n"
	. "\t\tel.type = 'hidden';\n"
	. "\t\tel.name = 'checksessionbypost';\n"
	. "\t\tel.value = '".$_SESSION['checksessionpost']."';\n"
	. "\t\tdocument.forms[i].appendChild(el);\n"
	. "\t}\n"
	. "\n"
	. "\tfunction addHiddenElement(theform,thename,thevalue)\n"
	. "\t{\n"
	. "\t\tvar myel = document.createElement('input');\n"
	. "\t\tmyel.type = 'hidden';\n"
	. "\t\tmyel.name = thename;\n"
	. "\t\ttheform.appendChild(myel);\n"
	. "\t\tmyel.value = thevalue;\n"
	. "\t\treturn myel;\n"
	. "\t}\n"
	. "\n"
	. "\tfunction sendPost(myaction,checkcode,arrayparam,arrayval)\n"
	. "\t{\n"
	. "\t\tvar myform = document.createElement('form');\n"
	. "\t\tdocument.body.appendChild(myform);\n"
	. "\t\tmyform.action =myaction;\n"
	. "\t\tmyform.method = 'POST';\n"
	. "\t\tfor (i=0;i<arrayparam.length;i++)\n"
	. "\t\t{\n"
	. "\t\t\taddHiddenElement(myform,arrayparam[i],arrayval[i])\n"
	. "\t\t}\n"
	. "\t\taddHiddenElement(myform,'checksessionbypost',checkcode)\n"
	. "\t\tmyform.submit();\n"
	. "\t}\n"
	. "\n"
	. "//-->\n"
	. "</script>\n";

	$adminoutput .= "".getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey Online Manual"));
  }
  
}
  else
  { //not logged in
    sendcacheheaders();
    if (!isset($_SESSION['metaHeader'])) {$_SESSION['metaHeader']='';}
    $adminoutput = getAdminHeader($_SESSION['metaHeader']).$adminoutput;  // All future output is written into this and then outputted at the end of file
    unset($_SESSION['metaHeader']);    
    $adminoutput.= "\t\t</td>\n".helpscreen()
                . "\t</tr>\n"
                . "</table>\n"
                . getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey Online Manual"));
  
  }

if (($action=='showphpinfo') && ($_SESSION['USER_RIGHT_CONFIGURATOR'] == 1)) 
{
	phpinfo();
}
else
{
	echo $adminoutput;
}


  function helpscreenscript()
  // returns the script part for online help to be included outside a table
  {
  	$helpoutput= "<script type='text/javascript'>\n"
    ."\tfunction showhelp(action)\n"
    ."\t\t{\n"
    ."\t\tvar name='help';\n"
    ."\t\tif (action == \"hide\")\n"
    ."\t\t\t{\n"
    ."\t\t\tdocument.getElementById(name).style.display='none';\n"
    ."\t\t\t}\n"
    ."\t\telse if (action == \"show\")\n"
    ."\t\t\t{\n"
    ."\t\t\tdocument.getElementById(name).style.display='';\n"
    ."\t\t\t}\n"
    ."\t\t}\n"
    ."</script>\n"; 
    return $helpoutput;
  }


  function helpscreen()
  // This functions loads the nescessary helpscreens for each action and hides the help window
  // 
  {
  	global $homeurl, $langdir,  $imagefiles;
  	global $surveyid, $gid, $qid, $action, $clang;

    $helpoutput="\t\t<td id='help' width='200' valign='top' style='display: none' bgcolor='#F8F8FF'>\n"
  	."\t\t\t<table width='100%'><tr><td>"
  	."<table width='100%' align='center' cellspacing='0'>\n"
  	."\t\t\t\t<tr>\n"
  	."\t\t\t\t\t<td bgcolor='#D2E0F2' height='8'>\n"
  	."\t\t\t\t\t\t<font size='1'><strong>"
  	.$clang->gT("Help")."</strong>\n"
  	."\t\t\t\t\t</font></td>\n"
  	."\t\t\t\t</tr>\n"
  	."\t\t\t\t<tr>\n"
  	."\t\t\t\t\t<td align='center' bgcolor='#EEF6FF' style='border-style: solid; border-width: 1px; border-color: #D2E0F2'>\n"
  	."\t\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20' hspace='0' border='0' align='left' />\n"
  	."\t\t\t\t\t\t<input type='image' src='$imagefiles/close.gif' name='CloseHelp' align='right' onclick=\"showhelp('hide')\" />\n"
  	."\t\t\t\t\t</td>\n"
  	."\t\t\t\t</tr>\n"
  	."\t\t\t\t<tr>\n"
  	."\t\t\t\t\t<td bgcolor='#EEF6FF' height='100%' style='border-width: 0px;'>\n";
  	//determine which help document to show
  	if (!$surveyid && $action != "editusers")
  	{
  		$helpdoc = "$langdir/admin.html";
  	}
  	elseif (!$surveyid && $action=="editusers")
  	{
  		$helpdoc = "$langdir/users.html";
  	}
  	elseif ($surveyid && !$gid)
  	{
  		$helpdoc = "$langdir/survey.html";
  	}
  	elseif ($surveyid && $gid && !$qid)
  	{
  		$helpdoc = "$langdir/group.html";
  	}
  	elseif ($surveyid && $gid && $qid && !returnglobal('viewanswer'))
  	{
  		$helpdoc = "$langdir/question.html";
  	}
  	elseif ($surveyid && $gid && $qid && (returnglobal('viewanswer')))
  	{
  		$helpdoc = "$langdir/answer.html";
  	}
  	$helpoutput.= "\t\t\t\t\t\t<iframe width='200' height='400' src='$helpdoc' marginwidth='2' marginheight='2'>\n"
  	."\t\t\t\t\t\t</iframe>\n"
  	."\t\t\t\t\t</td>"
  	."\t\t\t\t</tr>\n"
  	."\t\t\t</table></td></tr></table>\n"
  	."\t\t</td>\n";
  	return $helpoutput;
  }
  

    
function convertToArray($stringtoconvert, $seperator, $start, $end) 
// this function is still used to read SQL files from version 1.0 or older
{
    $begin=strpos($stringtoconvert, $start)+strlen($start);
    $len=strpos($stringtoconvert, $end)-$begin;
    $stringtoconvert=substr($stringtoconvert, $begin, $len);
    $stringtoconvert=str_replace('\n',"\n",$stringtoconvert);  //removes masking
    $stringtoconvert=stripslashes($stringtoconvert);
    $resultarray=explode($seperator, $stringtoconvert);
    return $resultarray;
}

function get2post($url)
{
	$url = preg_replace('/&amp;/i','&',$url);
	list($calledscript,$query) = explode('?',$url);
	$aqueryitems = explode('&',$query);
	$arrayParam = Array();
	$arrayVal = Array();
	
	foreach ($aqueryitems as $queryitem)
	{
		list($paramname, $value) = explode ('=', $queryitem);
		$arrayParam[] = "'".$paramname."'";
		$arrayVal[] = "'".$value."'";
	}
//	$Paramlist = "[" . implode(",",$arrayParam) . "]";
//	$Valuelist = "[" . implode(",",$arrayVal) . "]";
	$Paramlist = "new Array(" . implode(",",$arrayParam) . ")";
	$Valuelist = "new Array(" . implode(",",$arrayVal) . ")";
	$callscript = "sendPost('$calledscript','".$_SESSION['checksessionpost']."',$Paramlist,$Valuelist);";
	return $callscript;
}
  
?>
