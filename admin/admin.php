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
	if (
		preg_match
		  (
			'/^(delsurvey|delgroup|delquestion|insertnewsurvey|updatesubquestions|copynewquestion|insertnewgroup|insertCSV|insertnewquestion|updatesurvey|updatesurvey2|updategroup|deactivate|savepersonalsettings|updatequestion|updateansweroptions|renumberquestions)$/', 
			$action
		  )
	
		)
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
        if(hasRight($surveyid,'activate_survey'))    {include('activate.php');}
            else { include('access_denied.php');}    
        }
    elseif ($action == 'conditions')
    {
        if(hasRight($surveyid,'define_questions'))    {include('conditionshandling.php');}
            else { include('access_denied.php');}    
        }    
    elseif ($action == 'importsurveyresources') 
      { 
        if (hasRight($surveyid,'define_questions'))	{$_SESSION['FileManagerContext']="edit:survey:$surveyid";include('import_resources_zip.php');}
	        else { include('access_denied.php');}
      }      
    elseif ($action == 'exportstructurecsv')
        {
        if(hasRight($surveyid,'export'))    {include('export_structure_csv.php');}
            else { include('access_denied.php');}    
        }
    elseif ($action == 'exportstructureLsrcCsv')
        {
        if(hasRight($surveyid,'export'))    {include('export_structure_lsrc.php');}
            else { include('access_denied.php');}    
        }    
    elseif ($action == 'exportstructurequexml')
        {
        if(hasRight($surveyid,'export'))    {include('export_structure_quexml.php');}
            else { include('access_denied.php');}    
        }    
    elseif ($action == 'exportstructurecsvGroup')
        {
        if(hasRight($surveyid,'export'))    {include('dumpgroup.php');}
            else { include('access_denied.php');}    
        }    
    elseif ($action == 'exportstructureLsrcCsvGroup')
        {
        if(hasRight($surveyid,'export'))    {include('dumpgroup.php');}
            else { include('access_denied.php');}    
        }
    elseif ($action == 'exportstructurecsvQuestion')
        {
        if(hasRight($surveyid,'export'))    {include('dumpquestion.php');}
            else { include('access_denied.php');}    
        }    
    elseif ($action == 'exportstructureLsrcCsvQuestion')
        {
        if(hasRight($surveyid,'export'))    {include('dumpquestion.php');}
            else { include('access_denied.php');}    
        }    
    elseif ($action == 'exportsurvresources')
        {
        if(hasRight($surveyid,'export'))    {$_SESSION['FileManagerContext']="edit:survey:$surveyid";include('export_resources_zip.php');}
            else { include('access_denied.php');}    
        }    
    //elseif ($action == 'dumpquestion')
    //    {
    //    if(hasRight($surveyid,'export'))    {include('dumpquestion.php');}
    //        else { include('access_denied.php');}    
    //    }    
    //elseif ($action == 'dumpgroup')
    //    {
    //    if(hasRight($surveyid,'export'))    {include('dumpgroup.php');}
    //        else { include('access_denied.php');}    
    //    }    
    elseif ($action == 'deactivate')
        {
        if(hasRight($surveyid,'activate_survey'))    {include('deactivate.php');}
            else { include('access_denied.php');}    
        }
    elseif ($action == 'deletesurvey')
        {
        if(hasRight($surveyid,'delete_survey'))    {include('deletesurvey.php');}
            else { include('access_denied.php');}    
        }    
    elseif ($action == 'resetsurveylogic')
        {
        if(hasRight($surveyid,'define_questions'))    {include('resetsurveylogic.php');}
            else { include('access_denied.php');}    
        }    
    elseif ($action == 'importgroup')
        {
        if(hasRight($surveyid,'define_questions'))    {include('importgroup.php');}
            else { include('access_denied.php');}    
        }
    elseif ($action == 'importquestion')
        {
        if(hasRight($surveyid,'define_questions'))    {include('importquestion.php');}
            else { include('access_denied.php');}    
        }    
    elseif ($action == 'listcolumn')
        {
        if(hasRight($surveyid,'browse_response'))    {include('listcolumn.php');}
            else { include('access_denied.php');}    
        }    
    elseif ($action == 'previewquestion')
        {
        if(hasRight($surveyid,'define_questions'))    {include('preview.php');}
            else { include('access_denied.php');}    
        }
    elseif ($action=='addgroup' || $action=='editgroup')        
        {
        if(hasRight($surveyid,'define_questions'))    {$_SESSION['FileManagerContext']="edit:group:$surveyid"; include('grouphandling.php');}
            else { include('access_denied.php');}    
        }
    elseif ($action == 'saved')
        {
        if(hasRight($surveyid,'browse_response'))    {include('saved.php');}
            else { include('access_denied.php');}    
        }    
    elseif ($action == 'exportresults')
        {
        if(hasRight($surveyid,'export'))    {include('exportresults.php');}
            else { include('access_denied.php');}    
        }    
    elseif ($action == 'tokens')
        {
        if(hasRight($surveyid,'activate_survey'))    {$_SESSION['FileManagerContext']="edit:emailsettings:$surveyid"; include('tokens.php');}               
            else { include('access_denied.php'); }    
        }    
    elseif ($action == 'iteratesurvey')
        {
        if(hasRight($surveyid,'browse_response') && hasRight($surveyid,'activate_survey'))    {include('iterate_survey.php');}               
            else { include('access_denied.php');}    
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
        if(hasRight($surveyid,'define_questions'))    {
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
			    if (hasRight($surveyid,'edit_survey_property'))
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
			    if (hasRight($surveyid,'activate_survey'))
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
			    if (hasRight($surveyid,'define_questions'))
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
			    if (hasRight($surveyid,'define_questions'))
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
			    if (hasRight($surveyid,'define_questions'))
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
			    if(hasRight($surveyid,'define_questions'))    {
				    $_SESSION['FileManagerContext']="edit:assessments:$surveyid";
				    include('fck_LimeReplacementFields.php');
			    }
			    else { include('access_denied.php');}    
		    break;
		    default:
		    break;
	    }
    }    
 if (!isset($assessmentsoutput) && !isset($statisticsoutput) && !isset($browseoutput) && 
	 !isset($savedsurveyoutput) && !isset($listcolumnoutput) && !isset($conditionsoutput) && 
	 !isset($importoldresponsesoutput) && !isset($exportroutput) && !isset($vvoutput) && 
	 !isset($tokenoutput) && !isset($exportoutput) && !isset($templatesoutput) &&  
	 !isset($iteratesurveyoutput) && (substr($action,0,4)!= 'ajax') && ($action!='update') && 
        (  
	    isset($surveyid) || $action == "" ||
		preg_match
		  (
			'/^(listsurveys|personalsettings|statistics|importsurvey|editsurvey|updatesurvey|ordergroups|dataentry|newsurvey|listsurveys|globalsettings|editusergroups|exportspss|surveyrights|quotas|editusers|login|browse|vvimport|vvexport|setuserrights|modifyuser|setusertemplates|deluser|adduser|userrights|usertemplates|moduser)$/', 
			$action
		  )
		) 
	 )
	{
		if ($action=='editsurvey' || $action=='updatesurvey')
		{
			 $_SESSION['FileManagerContext']="edit:survey:$surveyid";
		}
		include('html.php');
	}

    if ($action == 'dataentry')
    {
        if(hasRight($surveyid,'browse_response')) 
        {
            include('dataentry.php');
        }
        else 
        { 
            include('access_denied.php');
        }    
    }    
    elseif ($action == 'statistics')
    {
    if(hasRight($surveyid,'browse_response'))    {include('statistics.php');}
        else { include('access_denied.php');}    
    }    
    elseif ($action == 'importoldresponses')
        {
        if(hasRight($surveyid,'browse_response'))    {include('importoldresponses.php');}
            else { include('access_denied.php');}    
        }    
    elseif ($action == 'exportspss')
    {
        if(hasRight($surveyid,'export'))    
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
        if(hasRight($surveyid,'browse_response'))    
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
        if(hasRight($surveyid,'export'))    {include('export_data_r.php');}
            else { include('access_denied.php');}    
    }   
    elseif ($action == 'vvexport')
    {
        if(hasRight($surveyid,'browse_response'))    {include('vvexport.php');}
            else { include('access_denied.php');}    
    }    
    elseif ($action == 'vvimport')
    {
        if(hasRight($surveyid,'browse_response'))    {include('vvimport.php');}
            else { include('access_denied.php');}    
    }  
    if ($action=='addquestion'    || $action=='copyquestion' || $action=='editquestion' || 
        $action=='orderquestions' || $action=='ajaxquestionattributes' || $action=='ajaxlabelsetpicker' || $action=='ajaxlabelsetdetails')
    {
        if(hasRight($surveyid,'define_questions'))    
        {
            $_SESSION['FileManagerContext']="edit:question:$surveyid";
            include('questionhandling.php');
        }    
        else 
        { 
            include('access_denied.php');
        }    
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
        !isset($vvoutput) && !isset($listcolumnoutput) && !isset($importlabelresources) && !isset($iteratesurveyoutput) && 
        (substr($action,0,4)!= 'ajax') && $action!='update') 
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

        $adminoutput .= getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey Online Manual"));
    }
  
}
  else
  { //not logged in
    sendcacheheaders();
    if (!isset($_SESSION['metaHeader'])) {$_SESSION['metaHeader']='';}
    $adminoutput = getAdminHeader($_SESSION['metaHeader']).$adminoutput.$loginsummary;  // All future output is written into this and then outputted at the end of file
    unset($_SESSION['metaHeader']);    
    $adminoutput.= "</div>\n".getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey Online Manual"));
  }

if (($action=='showphpinfo') && ($_SESSION['USER_RIGHT_CONFIGURATOR'] == 1)) 
{
	phpinfo();
}
else
{
	echo $adminoutput;
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
		$arrayVal[] = substr($value, 0, 9) != "document." ? "'".$value."'" : $value;
	}
//	$Paramlist = "[" . implode(",",$arrayParam) . "]";
//	$Valuelist = "[" . implode(",",$arrayVal) . "]";
	$Paramlist = "new Array(" . implode(",",$arrayParam) . ")";
	$Valuelist = "new Array(" . implode(",",$arrayVal) . ")";
	$callscript = "sendPost('$calledscript','".$_SESSION['checksessionpost']."',$Paramlist,$Valuelist);";
	return $callscript;
}
  
?>
