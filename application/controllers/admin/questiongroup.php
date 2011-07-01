<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * LimeSurvey (tm)
 * Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * 
 */
 
 class questiongroup extends SurveyCommonController {
    
    function __construct()
	{
		parent::__construct();
	}
    
    
    function add($surveyid)
    {
        $action = "addgroup";//$this->input->post('action');
        $clang = $this->limesurvey_lang;
        
        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);
        self::_getAdminHeader();
        self::_showadminmenu();
        self::_surveybar($surveyid); 
        
        if ($action == "addgroup")
        {
            $this->load->helper('admin/htmleditor');      
            $this->load->helper('surveytranslator'); 
            $grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            $grplangs[] = $baselang;
            $grplangs = array_reverse($grplangs);
            
            $data['clang'] = $clang;
            $data['surveyid'] = $surveyid;
            $data['action'] = $action;
            $data['grplangs'] = $grplangs;
            $this->load->view('admin/Survey/QuestionGroups/addGroup_view',$data);
            
            /**
            $newgroupoutput = PrepareEditorScript();
            $newgroupoutput .= "<div class='header ui-widget-header'>".$clang->gT("Add question group")."</div>\n";
             $newgroupoutput .= "<div id='tabs'>\n<ul>\n";
        	 foreach ($grplangs as $grouplang)
             {
                $newgroupoutput .= '<li><a href="#'.$grouplang.'">'.GetLanguageNameFromCode($grouplang,false);
                if ($grouplang==$baselang) {$newgroupoutput .= '('.$clang->gT("Base language").')';}
                $newgroupoutput .= "</a></li>\n";
		     }
        		if (bHasSurveyPermission($surveyid,'surveycontent','import'))
                {
                    $newgroupoutput .= '<li><a href="#import">'.$clang->gT("Import question group")."</a></li>\n";
            		
            	}
        		$newgroupoutput .= "</ul>";
        
            //    $newgroupoutput .="<table width='100%' border='0'  class='tab-page'>\n\t<tr><td>\n"
            $newgroupoutput .="\n";
            $newgroupoutput .= "<form action='$scriptname' class='form30' id='newquestiongroup' name='newquestiongroup' method='post' onsubmit=\"if (1==0 ";
        
            foreach ($grplangs as $grouplang)
            {
                $newgroupoutput .= "|| document.getElementById('group_name_$grouplang').value.length==0 ";
            }
            $newgroupoutput .=" ) {alert ('".$clang->gT("Error: You have to enter a group title for each language.",'js')."'); return false;}\" >";
        
            foreach ($grplangs as $grouplang)
            {
                $newgroupoutput .= '<div id="'.$grouplang.'">';
                $newgroupoutput .= "<ul>"
                . "<li>"
                . "<label for='group_name_$grouplang'>".$clang->gT("Title").":</label>\n"
                . "<input type='text' size='80' maxlength='100' name='group_name_$grouplang' id='group_name_$grouplang' /><font color='red' face='verdana' size='1'> ".$clang->gT("Required")."</font></li>\n"
                . "\t<li><label for='description_$grouplang'>".$clang->gT("Description:")."</label>\n"
                . "<textarea cols='80' rows='8' id='description_$grouplang' name='description_$grouplang'></textarea>"
                . getEditor("group-desc","description_".$grouplang, "[".$clang->gT("Description:", "js")."](".$grouplang.")",$surveyid,'','',$action)
                . "</li>\n"
                . "</ul>"
                . "\t<p><input type='submit' value='".$clang->gT("Save question group")."' />\n"
                . "</div>\n";
            }
        
            $newgroupoutput.= "<input type='hidden' name='action' value='insertquestiongroup' />\n"
            . "<input type='hidden' name='sid' value='$surveyid' />\n"
            . "</form>\n";
        
        
            // Import TAB
            if (bHasSurveyPermission($surveyid,'surveycontent','import'))
            {
                $newgroupoutput .= '<div id="import">'."\n"
                . "<form enctype='multipart/form-data' class='form30' id='importgroup' name='importgroup' action='$scriptname' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
                . "<ul>\n"
                . "<li>\n"
                . "<label for='the_file'>".$clang->gT("Select question group file (*.lsg/*.csv):")."</label>\n"
                . "<input id='the_file' name=\"the_file\" type=\"file\" size=\"35\" /></li>\n"
                . "<li><label for='translinksfields'>".$clang->gT("Convert resource links?")."</label>\n"
                . "<input id='translinksfields' name=\"translinksfields\" type=\"checkbox\" checked=\"checked\"/></li></ul>\n"
                . "\t<p><input type='submit' value='".$clang->gT("Import question group")."' />\n"
                . "\t<input type='hidden' name='action' value='importgroup' />\n"
                . "\t<input type='hidden' name='sid' value='$surveyid' />\n"
                . "\t</form>\n";
                // End Import TABS
                $newgroupoutput.= "</div>";
            }
        	 
        
        
            // End of TABS
             $newgroupoutput.= "</div>"; */
        	
        
        }
        
        self::_loadEndScripts();
        
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
        
    }
    
    
    
    
    
 }