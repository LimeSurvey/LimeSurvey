<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
* $Id: export.php 11280 2011-10-27 04:27:08Z azammitdcarf $
*
*/

/**
* Export Action
*
* This controller performs export actions
*
* @package		LimeSurvey
* @subpackage	Backend
*/
class export extends Survey_Common_Action {

    /**
     * Run, routes it down to valid sub-action
	 *
     * @access public
     * @return void
     */
	public function run($sa)
	{
		Yii::app()->loadHelper('export');

		if ($sa == 'question')
			$this->route('question', array('surveyid', 'gid', 'qid'));
		elseif ($sa == 'vvexport')
			$this->route('vvexport', array('surveyid', 'subaction'));
		elseif ($sa == 'group')
			$this->route('group', array('surveyid', 'gid'));
		elseif ($sa == 'exportresults')
			$this->route('exportresults', array('surveyid'));
		elseif ($sa == 'survey')
			$this->route('survey', array('action', 'surveyid'));
		elseif ($sa == 'showquexmlsurvey')
			$this->route('showquexmlsurvey', array('surveyid', 'lang'));
		elseif ($sa == 'exportspss')
			$this->route('exportspss', array('sid', 'lang'));
		elseif ($sa == 'exportr')
			$this->route('exportr', array('sid', 'subaction'));
	}

    function survey($action,$surveyid)
    {
        $iSurveyID= (int) $surveyid;
        if(bHasSurveyPermission($iSurveyID,'surveycontent','export')) {
            self::_surveyexport($action, $iSurveyID);
            return;
        }
    }

    /**
    * This function exports a ZIP archives of several ZIP archives - it is used in the listSurvey controller
    * The SIDs are read from session flashdata.
    *
    */
    function surveyarchives()
    {
        if (!$this->session->userdata('USER_RIGHT_SUPERADMIN')) die('Access denied.');
        $aSurveyIDs= $this->session->flashdata('sids');
        $aExportedFiles=array();
        foreach ($aSurveyIDs as $iSurveyID)
        {
            $iSurveyID=(int)$iSurveyID;
            if ($iSurveyID>0)
            {
                $aExportedFiles[$iSurveyID]=$this->_exportarchive($iSurveyID,false);
            }
        }
        if (count($aExportedFiles)>0){
            $aZIPFileName=$this->config->item("tempdir").DIRECTORY_SEPARATOR.sRandomChars(30);
            $this->load->library("admin/pclzip/pclzip",array('p_zipname' => $aZIPFileName));
            $zip = new PclZip($aZIPFileName);
            foreach ($aExportedFiles as $iSurveyID=>$sFileName)
            {
                $zip->add(array(array(PCLZIP_ATT_FILE_NAME=>$sFileName,
                                      PCLZIP_ATT_FILE_NEW_FULL_NAME =>'survey_archive_'.$iSurveyID.'.zip')));
                unlink($sFileName);
            }
        }
        if (is_file($aZIPFileName)) {
                //Send the file for download!
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

                header("Content-Type: application/force-download");
                header( "Content-Disposition: attachment; filename=survey_archives_pack.zip" );
                header( "Content-Description: File Transfer");
                @readfile($aZIPFileName);

                //Delete the temporary file
                unlink($aZIPFileName);
                return;
        }
    }


    function _surveyexport($action, $surveyid)
    {
        $surveyid = sanitize_int($surveyid);
        if($action == "exportstructurexml")
        {
            $fn = "limesurvey_survey_$surveyid.lss";
            header("Content-Type: text/xml");
            header("Content-Disposition: attachment; filename=$fn");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: public");                          // HTTP/1.0
            echo survey_getXMLData($surveyid);
            exit;
        }
        elseif($action == "exportstructurequexml")
        {
            if (isset($surveyprintlang) && !empty($surveyprintlang))
                $quexmllang = $surveyprintlang;
            else
                $quexmllang=GetBaseLanguageFromSurveyID($surveyid);
            if (!(isset($noheader) && $noheader == true))
            {
                $fn = "survey_{$surveyid}_{$quexmllang}.xml";
                header("Content-Type: text/xml");
                header("Content-Disposition: attachment; filename=$fn");
                header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Pragma: public");                          // HTTP/1.0

                echo quexml_export($surveyid, $quexmllang);
                exit;
            }
        }
        elseif($action == "exportstructureLsrcCsv")
        {
            lsrccsv_export($surveyid);
        }
        elseif($action == "exportarchive")
        {
            $this->_exportarchive($surveyid);
        }

    }

    /**
    * Exports a archive (ZIP) of the current survey (structure, responses, timings, tokens)
    *
    * @param integer $iSurveyID  The ID of the survey to export
    * @param boolean $bSendToBrowser If true (default) then the ZIP file is sent to the browser
    * @return string Full path of the ZIP filename if $bSendToBrowser is set to true, otherwise no return value
    */
    function _exportarchive($iSurveyID, $bSendToBrowser=true)
    {
        $aSurveyInfo=getSurveyInfo($iSurveyID);
        $sTempDir = Yii::app()->getConfig("tempdir");
        $aZIPFileName=$sTempDir.DIRECTORY_SEPARATOR.sRandomChars(30);
        $sLSSFileName=$sTempDir.DIRECTORY_SEPARATOR.sRandomChars(30);
        $sLSRFileName=$sTempDir.DIRECTORY_SEPARATOR.sRandomChars(30);
        $sLSTFileName=$sTempDir.DIRECTORY_SEPARATOR.sRandomChars(30);
        $sLSIFileName=$sTempDir.DIRECTORY_SEPARATOR.sRandomChars(30);

    	Yii::import('application.libraries.admin.pclzip.pclzip', true);
        $zip = new PclZip($aZIPFileName);

        file_put_contents($sLSSFileName,survey_getXMLData($iSurveyID));
        $zip->add(array(array(PCLZIP_ATT_FILE_NAME=>$sLSSFileName,
                        PCLZIP_ATT_FILE_NEW_FULL_NAME =>'survey_'.$iSurveyID.'.lss')));
        unlink($sLSSFileName);

        if ($aSurveyInfo['active']=='Y')
        {
            getXMLDataSingleTable($iSurveyID,'survey_'.$iSurveyID,'Responses','responses',$sLSRFileName, false);
            $zip->add(array(array(PCLZIP_ATT_FILE_NAME=>$sLSRFileName,
                            PCLZIP_ATT_FILE_NEW_FULL_NAME =>'survey_'.$iSurveyID.'_responses.lsr')));
            unlink($sLSRFileName);
        }
    	if (Yii::app()->db->schema->getTable('{{tokens_'.$iSurveyID . '}}'))
        {
            getXMLDataSingleTable($iSurveyID,'tokens_'.$iSurveyID,'Tokens','tokens',$sLSTFileName);
            $zip->add(array(array(PCLZIP_ATT_FILE_NAME=>$sLSTFileName,
                            PCLZIP_ATT_FILE_NEW_FULL_NAME =>'survey_'.$iSurveyID.'_tokens.lst')));
            unlink($sLSTFileName);
        }
        if (Yii::app()->db->schema->getTable('{{survey_'.$iSurveyID.'_timings}}'))
        {
            getXMLDataSingleTable($iSurveyID,'survey_'.$iSurveyID.'_timings','Timings','timings',$sLSIFileName);
            $zip->add(array(array(PCLZIP_ATT_FILE_NAME=>$sLSIFileName,
                            PCLZIP_ATT_FILE_NEW_FULL_NAME =>'survey_'.$iSurveyID.'_timings.lsi')));
            unlink($sLSIFileName);
        }
        if (is_file($aZIPFileName)) {
            if ($bSendToBrowser)
            {
                //Send the file for download!
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

                header("Content-Type: application/force-download");
                header( "Content-Disposition: attachment; filename=survey_archive_{$iSurveyID}.zip" );
                header( "Content-Description: File Transfer");
                @readfile($aZIPFileName);

                //Delete the temporary file
                unlink($aZIPFileName);
                return;
            }
            else
            {
                return($aZIPFileName);
            }
        }

    }

    function group($surveyid, $gid)
    {
        $surveyid = sanitize_int($surveyid);
        $gid = sanitize_int($gid);
        if(Yii::app()->getConfig("export4lsrc") === true && bHasSurveyPermission($surveyid,'survey','export')) {
            if(!empty($_POST['action']))
            {
                group_export($_POST['action'], $surveyid, $gid);
                return;
            }
            $css_admin_includes[] = Yii::app()->getConfig('styleurl')."admin/default/superfish.css";
            Yii::app()->setConfig("css_admin_includes", $css_admin_includes);
            $this->getController()->_getAdminHeader();
            $this->getController()->_showadminmenu($surveyid);
            $this->_surveybar($surveyid,$gid);
            $this->_questiongroupbar($surveyid,$gid,null,"exportstructureGroup");
            $this->getController()->render("/admin/export/group_view", array("surveyid" => $surveyid, "gid" => $gid));
            $this->getController()->_loadEndScripts();
            $this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));
        }
        else
        {
            group_export("exportstructurecsvGroup", $surveyid, $gid);
            return;
        }
    }

    function question($surveyid, $gid, $qid)
    {
        $surveyid = sanitize_int($surveyid);
        $gid = sanitize_int($gid);
        $qid = sanitize_int($qid);
        if(Yii::app()->getConfig('export4lsrc') === true && bHasSurveyPermission($surveyid,'survey','export')) {
            if(!empty($_POST['action']))
            {
                question_export($_POST['action'], $surveyid, $gid, $qid);
                return;
            }
            $css_admin_includes[] = Yii::app()->getConfig('styleurl') . "admin/default/superfish.css";
            Yii::app()->setConfig("css_admin_includes", $css_admin_includes);
            $this->getController()->_getAdminHeader();
            $this->getController()->_showadminmenu($surveyid);
            $this->_surveybar($surveyid,$gid);
            $this->_questiongroupbar($surveyid,$gid,$qid,"exportstructureGroup");
            $this->getController()->render("/admin/export/question_view", array("surveyid" => $surveyid, "gid" => $gid, "qid" =>$qid));
            $this->getController()->_loadEndScripts();
            $this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));
        }
        else
        {
            question_export("exportstructurecsvQuestion", $surveyid, $gid, $qid);
            return;
        }
    }

    function exportresults($surveyid)
    {
        $surveyid = sanitize_int($surveyid);
        if (!isset($imageurl)) {$imageurl="./images";}
        if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
        if (!isset($exportstyle)) {$exportstyle=returnglobal('exportstyle');}
        if (!isset($answers)) {$answers=returnglobal('answers');}
        if (!isset($type)) {$type=returnglobal('type');}
        if (!isset($convertyto1)) {$convertyto1=returnglobal('convertyto1');}
        if (!isset($convertnto2)) {$convertnto2=returnglobal('convertnto2');}
        if (!isset($convertspacetous)) {$convertspacetous=returnglobal('convertspacetous');}

        $clang = Yii::app()->lang;

        if (!bHasSurveyPermission($surveyid, 'responses','export'))
        {
            exit;
        }

        //include_once(dirname(__FILE__)."/classes/phpexcel/PHPExcel.php");
        //include_once(dirname(__FILE__)."/classes/tcpdf/extensiontcpdf.php");
        //include_once(dirname(__FILE__)."/exportresults_objects.php");
        Yii::app()->loadHelper("admin/exportresults");

        $surveybaselang=GetBaseLanguageFromSurveyID($surveyid);
        $exportoutput="";

        // Get info about the survey
        $thissurvey=getSurveyInfo($surveyid);

        if (!$exportstyle)
        {

            //FIND OUT HOW MANY FIELDS WILL BE NEEDED - FOR 255 COLUMN LIMIT
            $excesscols=createFieldMap($surveyid);
            $excesscols=array_keys($excesscols);



            $afieldcount = count($excesscols);
            $this->getController()->_getAdminHeader();
            $this->_browsemenubar($surveyid, $clang->gT("Export results"));

            if (incompleteAnsFilterstate() == "filter")
            {
                $selecthide="selected='selected'";
                $selectshow="";
                $selectinc="";
            }
            elseif (incompleteAnsFilterstate() == "inc")
            {
                $selecthide="";
                $selectshow="";
                $selectinc="selected='selected'";
            }
            else
            {
                $selecthide="";
                $selectshow="selected='selected'";
                $selectinc="";
            }
            $data['selecthide'] = $selecthide;
            $data['selectshow'] = $selectshow;
            $data['selectinc'] = $selectinc;
            $data['afieldcount'] = $afieldcount;
            $data['excesscols'] = $excesscols;

            //get max number of datasets

            $max_datasets_query = Yii::app()->db->createCommand("SELECT COUNT(id) AS count FROM {{survey_$surveyid}}")->query()->read();
            $max_datasets = $max_datasets_query['count'];

            $data['clang'] = $clang;
            $data['max_datasets'] = $max_datasets;
        	$data['surveyid'] = $surveyid;
        	$data['imageurl'] = Yii::app()->getConfig('imageurl');
        	$data['thissurvey'] = $thissurvey;

            $this->getController()->render("/admin/export/exportresults_view", $data);
            $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
            return;
        }

        // Export Language is set by default to surveybaselang
        // * the explang language code is used in SQL queries
        // * the alang object is used to translate headers and hardcoded answers
        // In the future it might be possible to 'post' the 'export language' from
        // the exportresults form
        $explang = $surveybaselang;
        $elang=new limesurvey_lang(array($explang));

        //Get together our FormattingOptions and then call into the exportSurvey
        //function.
        $options = new FormattingOptions();
        $options->selectedColumns = $_POST['colselect'];
        $options->responseMinRecord = sanitize_int($_POST['export_from']) - 1;
        $options->responseMaxRecord = sanitize_int($_POST['export_to']) - 1;
        $options->answerFormat = $answers;
        $options->convertN = $convertnto2;
        if ($options->convertN)
        {
            $options->nValue = $convertnto2;
        }
        $options->convertY = $convertyto1;
        if ($options->convertY)
        {
            $options->yValue = $convertyto;
        }
        $options->format = $type;
        $options->headerSpacesToUnderscores = $convertspacetous;
        $options->headingFormat = $exportstyle;
        $options->responseCompletionState = incompleteAnsFilterstate();

        //If we have no data for the filter state then default to show all.
        if (empty($options->responseCompletionState))
        {
        	if (!isset($_POST['attribute_select']))
        		$_POST['attribute_select'] = array();

            $options->responseCompletionState = 'show';
            if (in_array('first_name',$_POST['attribute_select']))
            {
                $dquery .= ", {{tokens_$surveyid}}.firstname";
            }
            if (in_array('last_name',$_POST['attribute_select']))
            {
                $dquery .= ", {{tokens_$surveyid}}.lastname";
            }
            if (in_array('email_address',$_POST['attribute_select']))
            {
                $dquery .= ", {{tokens_$surveyid}}.email";
            }
            if (in_array('token',$_POST['attribute_select']))
            {
                $dquery .= ", {{tokens_$surveyid}}.token";
            }
			$attributeFields=GetTokenFieldsAndNames($surveyid,true);
            foreach ($attributeFields as $attr_name=>$attr_desc)
            {
                if (in_array($attr_name,$_POST['attribute_select']))
                {
                    $dquery .= ", {{tokens_$surveyid}}.$attr_name";
                }
            }
        }
        if ($options->responseCompletionState == 'inc')
        {
            $options->responseCompletionState = 'incomplete';
        }

        $resultsService = new ExportSurveyResultsService();
        $resultsService->exportSurvey($surveyid, $explang, $options);

        exit;

    }

    /*
    * The SPSS DATA LIST / BEGIN DATA parser is rather simple minded, the number after the type
    * specifier identifies the field width (maximum number of characters to scan)
    * It will stop short of that number of characters, honouring quote delimited
    * space separated strings, however if the width is too small the remaining data in the current
    * line becomes part of the next column.  Since we want to restrict this script to ONE scan of
    * the data (scan & output at same time), the information needed to construct the
    * DATA LIST is held in the $fields array, while the actual data is written to a
    * to a temporary location, updating length (size) values in the $fields array as
    * the tmp file is generated (uses @fwrite's return value rather than strlen).
    * Final output renders $fields to a DATA LIST, and then stitches in the tmp file data.
    *
    * Optimization opportunities remain in the VALUE LABELS section, which runs a query / column
    */
    function exportspss($sid, $subaction = null)
    {
    	global $surveyid;

        $surveyid = (int) $sid;
        $clang = $this->getController()->lang;
        //for scale 1=nominal, 2=ordinal, 3=scale
        $typeMap = array(
        '5'=>Array('name'=>'5 Point Choice','size'=>1,'SPSStype'=>'F','Scale'=>3),
        'B'=>Array('name'=>'Array (10 Point Choice)','size'=>1,'SPSStype'=>'F','Scale'=>3),
        'A'=>Array('name'=>'Array (5 Point Choice)','size'=>1,'SPSStype'=>'F','Scale'=>3),
        'F'=>Array('name'=>'Array (Flexible Labels)','size'=>1,'SPSStype'=>'F'),
        '1'=>Array('name'=>'Array (Flexible Labels) Dual Scale','size'=>1,'SPSStype'=>'F'),
        'H'=>Array('name'=>'Array (Flexible Labels) by Column','size'=>1,'SPSStype'=>'F'),
        'E'=>Array('name'=>'Array (Increase, Same, Decrease)','size'=>1,'SPSStype'=>'F','Scale'=>2),
        'C'=>Array('name'=>'Array (Yes/No/Uncertain)','size'=>1,'SPSStype'=>'F'),
        'X'=>Array('name'=>'Boilerplate Question','size'=>1,'SPSStype'=>'A','hide'=>1),
        'D'=>Array('name'=>'Date','size'=>10,'SPSStype'=>'SDATE'),
        'G'=>Array('name'=>'Gender','size'=>1,'SPSStype'=>'F'),
        'U'=>Array('name'=>'Huge Free Text','size'=>1,'SPSStype'=>'A'),
        'I'=>Array('name'=>'Language Switch','size'=>1,'SPSStype'=>'A'),
        '!'=>Array('name'=>'List (Dropdown)','size'=>1,'SPSStype'=>'F'),
        'W'=>Array('name'=>'List (Flexible Labels) (Dropdown)','size'=>1,'SPSStype'=>'F'),
        'Z'=>Array('name'=>'List (Flexible Labels) (Radio)','size'=>1,'SPSStype'=>'F'),
        'L'=>Array('name'=>'List (Radio)','size'=>1,'SPSStype'=>'F'),
        'O'=>Array('name'=>'List With Comment','size'=>1,'SPSStype'=>'F'),
        'T'=>Array('name'=>'Long free text','size'=>1,'SPSStype'=>'A'),
        'K'=>Array('name'=>'Multiple Numerical Input','size'=>1,'SPSStype'=>'F'),
        'M'=>Array('name'=>'Multiple choice','size'=>1,'SPSStype'=>'F'),
        'P'=>Array('name'=>'Multiple choice with comments','size'=>1,'SPSStype'=>'F'),
        'Q'=>Array('name'=>'Multiple Short Text','size'=>1,'SPSStype'=>'F'),
        'N'=>Array('name'=>'Numerical Input','size'=>3,'SPSStype'=>'F','Scale'=>3),
        'R'=>Array('name'=>'Ranking','size'=>1,'SPSStype'=>'F'),
        'S'=>Array('name'=>'Short free text','size'=>1,'SPSStype'=>'F'),
        'Y'=>Array('name'=>'Yes/No','size'=>1,'SPSStype'=>'F'),
        ':'=>Array('name'=>'Multi flexi numbers','size'=>1,'SPSStype'=>'F','Scale'=>3),
        ';'=>Array('name'=>'Multi flexi text','size'=>1,'SPSStype'=>'A'),
        '|'=>Array('name'=>'File upload','size'=>1,'SPSStype'=>'A'),
        '*'=>Array('name'=>'Equation','size'=>1,'SPSStype'=>'A'),
        );

        $filterstate = incompleteAnsFilterstate();
        $spssver = returnglobal('spssver');
        if (is_null($spssver)) {
            if (!Yii::app()->session['spssversion']) {
                Yii::app()->session['spssversion'] = 2;	//Set default to 2, version 16 or up
            }
            $spssver = Yii::app()->session['spssversion'];
        } else {
            Yii::app()->session['spssversion'] = $spssver;
        }

        $length_varlabel = '255'; // Set the max text length of Variable Labels
        $length_vallabel = '120'; // Set the max text length of Value Labels

        switch ($spssver) {
            case 1:	//<16
                $length_data	 = '255'; // Set the max text length of the Value
                break;
            case 2:	//>=16
                $length_data	 = '16384'; // Set the max text length of the Value
                break;
            default:
                $length_data	 = '16384'; // Set the max text length of the Value

        }

        $headerComment = '*$Rev: 10193 $' . " $filterstate $spssver.\n";

        if (isset($_POST['dldata'])) $subaction = "dldata";
        if (isset($_POST['dlstructure'])) $subaction = "dlstructure";

        if  (!isset($subaction))
        {
            $this->getController()->_getAdminHeader();
            $this->_browsemenubar($surveyid, $clang->gT('Export results'));

            $selecthide="";
            $selectshow="";
            $selectinc="";
            switch ($filterstate) {
                case "inc":
                    $selectinc="selected='selected'";
                    break;
                case "filter":
                    $selecthide="selected='selected'";
                    break;
                default:
                    $selectshow="selected='selected'";
            }

            $data['clang'] = $clang;
            $data['selectinc'] = $selectinc;
            $data['selecthide'] = $selecthide;
            $data['selectshow'] = $selectshow;
            $data['spssver'] = $spssver;
        	$data['surveyid'] = $surveyid;
            $this->getController()->render("/admin/export/spss_view",$data);
            $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));

        } else {
            // Get Base language:

            $language = GetBaseLanguageFromSurveyID($surveyid);
            $clang = new limesurvey_lang(array($language));
            Yii::app()->loadHelper("admin/exportresults");
        }



        if  ($subaction=='dldata') {
            header("Content-Disposition: attachment; filename=survey_".$surveyid."_SPSS_data_file.dat");
            header("Content-type: text/comma-separated-values; charset=UTF-8");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: public");

            if ($spssver == 2) echo "\xEF\xBB\xBF";
            $na = "";
            spss_export_data($na);

            exit;
        }


        if  ($subaction=='dlstructure') {
            header("Content-Disposition: attachment; filename=survey_".$surveyid."_SPSS_syntax_file.sps");
            header("Content-type: application/download; charset=UTF-8");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: public");

            // Build array that has to be returned
            $fields = spss_fieldmap();

            //Now get the query string with all fields to export
            $query = spss_getquery();
            $result=Yii::app()->db->createCommand($query)->query()->readAll(); //Checked

            $num_fields = isset($result[0]) ? count($result[0]) : 0;

            //Now we check if we need to adjust the size of the field or the type of the field
            foreach ($result as $row) {
                $row = array_values($row);
                $fieldno = 0;
                while ($fieldno < $num_fields)
                {
                    //Performance improvement, don't recheck fields that have valuelabels
                    if (!isset($fields[$fieldno]['answers'])) {
                        $strTmp=mb_substr(strip_tags_full($row[$fieldno]), 0, $length_data);
                        $len = mb_strlen($strTmp);
                        if($len > $fields[$fieldno]['size']) $fields[$fieldno]['size'] = $len;

                        if (trim($strTmp) != ''){
                            if ($fields[$fieldno]['SPSStype']=='F' && (my_is_numeric($strTmp)===false || $fields[$fieldno]['size']>16))
                            {
                                $fields[$fieldno]['SPSStype']='A';
                            }
                        }
                    }
                    $fieldno++;
                }
            }

            /**
            * End of DATA print out
            *
            * Now $fields contains accurate length data, and the DATA LIST can be rendered -- then the contents of the temp file can
            * be sent to the client.
            */
            if ($spssver == 2) echo "\xEF\xBB\xBF";
            echo $headerComment;
            if ($spssver == 2) echo "SET UNICODE=ON.\n";
            echo "GET DATA\n"
            ." /TYPE=TXT\n"
            ." /FILE='survey_".$surveyid."_SPSS_data_file.dat'\n"
            ." /DELCASE=LINE\n"
            ." /DELIMITERS=\",\"\n"
            ." /QUALIFIER=\"'\"\n"
            ." /ARRANGEMENT=DELIMITED\n"
            ." /FIRSTCASE=1\n"
            ." /IMPORTCASE=ALL\n"
            ." /VARIABLES=";
            foreach ($fields as $field){
                if($field['SPSStype'] == 'DATETIME23.2') $field['size']='';
                if($field['SPSStype']=='F' && ($field['LStype'] == 'N' || $field['LStype']=='K')) {
                    $field['size'].='.'.($field['size']-1);
                }
                if (!$field['hide']) echo "\n {$field['id']} {$field['SPSStype']}{$field['size']}";
            }
            echo ".\nCACHE.\n"
            ."EXECUTE.\n";

            //Create the variable labels:
            echo "*Define Variable Properties.\n";
            foreach ($fields as $field) {
                if (!$field['hide']) echo "VARIABLE LABELS " . $field['id'] . " \"" . str_replace('"','""',mb_substr(strip_tags_full($field['VariableLabel']),0,$length_varlabel)) . "\".\n";
            }

            // Create our Value Labels!
            echo "*Define Value labels.\n";
            foreach ($fields as $field) {
                if (isset($field['answers'])) {
                    $answers = $field['answers'];
                    //print out the value labels!
                    echo "VALUE LABELS  {$field['id']}\n";
                    $i=0;
                    foreach ($answers as $answer) {
                        $i++;
                        if ($field['SPSStype']=="F" && my_is_numeric($answer['code'])) {
                            $str = "{$answer['code']}";
                        } else {
                            $str = "\"{$answer['code']}\"";
                        }
                        if ($i < count($answers)) {
                            echo " $str \"{$answer['value']}\"\n";
                        } else {
                            echo " $str \"{$answer['value']}\".\n";
                        }
                    }
                }
            }

            foreach ($fields as $field){
                if($field['scale']!=='') {
                    switch ($field['scale']) {
                        case 2:
                            echo "VARIABLE LEVEL {$field['id']}(ORDINAL).\n";
                            break;
                        case 3:
                            echo "VARIABLE LEVEL {$field['id']}(SCALE).\n";
                    }
                }
            }

            //Rename the Variables (in case somethings goes wrong, we still have the OLD values
            foreach ($fields as $field){
                if (isset($field['sql_name']) && $field['hide']===0) {
                    $ftitle = $field['title'];
                    if (!preg_match ("/^([a-z]|[A-Z])+.*$/", $ftitle)) {
                        $ftitle = "q_" . $ftitle;
                    }
                    $ftitle = str_replace(array(" ","-",":",";","!","/","\\","'"), array("_","_hyph_","_dd_","_dc_","_excl_","_fs_","_bs_",'_qu_'), $ftitle);
                    if ($ftitle != $field['title']) echo "* Variable name was incorrect and was changed from {$field['title']} to $ftitle .\n";
                    echo "RENAME VARIABLE ( " . $field['id'] . ' = ' . $ftitle . " ).\n";
                }
            }
            exit;
        }


    }

    /*
    * The SPSS DATA LIST / BEGIN DATA parser is rather simple minded, the number after the type
    * specifier identifies the field width (maximum number of characters to scan)
    * It will stop short of that number of characters, honouring quote delimited
    * space separated strings, however if the width is too small the remaining data in the current
    * line becomes part of the next column.  Since we want to restrict this script to ONE scan of
    * the data (scan & output at same time), the information needed to construct the
    * DATA LIST is held in the $fields array, while the actual data is written to a
    * to a temporary location, updating length (size) values in the $fields array as
    * the tmp file is generated (uses @fwrite's return value rather than strlen).
    * Final output renders $fields to a DATA LIST, and then stitches in the tmp file data.
    *
    * Optimization opportunities remain in the VALUE LABELS section, which runs a query / column
    */
    function exportr($sid, $subaction = null)
    {
    	global $surveyid;
        $surveyid = (int) $sid;
        $clang = $this->controller->lang;

        $length_vallabel = '120'; // Set the max text length of Value Labels
        $length_data = '25500'; // Set the max text length of Text Data
        $length_varlabel = '25500'; // Set the max text length of Variable Labels
        $headerComment = '';
        $tempFile = '';

        //for scale 1=nominal, 2=ordinal, 3=scale
        $typeMap = array(
	        '5'=>Array('name'=>'5 Point Choice','size'=>1,'SPSStype'=>'F','Scale'=>3),
	        'B'=>Array('name'=>'Array (10 Point Choice)','size'=>1,'SPSStype'=>'F','Scale'=>3),
	        'A'=>Array('name'=>'Array (5 Point Choice)','size'=>1,'SPSStype'=>'F','Scale'=>3),
	        'F'=>Array('name'=>'Array (Flexible Labels)','size'=>1,'SPSStype'=>'F'),
	        '1'=>Array('name'=>'Array (Flexible Labels) Dual Scale','size'=>1,'SPSStype'=>'F'),
	        'H'=>Array('name'=>'Array (Flexible Labels) by Column','size'=>1,'SPSStype'=>'F'),
	        'E'=>Array('name'=>'Array (Increase, Same, Decrease)','size'=>1,'SPSStype'=>'F','Scale'=>2),
	        'C'=>Array('name'=>'Array (Yes/No/Uncertain)','size'=>1,'SPSStype'=>'F'),
	        'X'=>Array('name'=>'Boilerplate Question','size'=>1,'SPSStype'=>'A','hide'=>1),
	        'D'=>Array('name'=>'Date','size'=>10,'SPSStype'=>'SDATE'),
	        'G'=>Array('name'=>'Gender','size'=>1,'SPSStype'=>'F'),
	        'U'=>Array('name'=>'Huge Free Text','size'=>1,'SPSStype'=>'A'),
	        'I'=>Array('name'=>'Language Switch','size'=>1,'SPSStype'=>'A'),
	        '!'=>Array('name'=>'List (Dropdown)','size'=>1,'SPSStype'=>'F'),
	        'W'=>Array('name'=>'List (Flexible Labels) (Dropdown)','size'=>1,'SPSStype'=>'F'),
	        'Z'=>Array('name'=>'List (Flexible Labels) (Radio)','size'=>1,'SPSStype'=>'F'),
	        'L'=>Array('name'=>'List (Radio)','size'=>1,'SPSStype'=>'F'),
	        'O'=>Array('name'=>'List With Comment','size'=>1,'SPSStype'=>'F'),
	        'T'=>Array('name'=>'Long free text','size'=>1,'SPSStype'=>'A'),
	        'K'=>Array('name'=>'Multiple Numerical Input','size'=>1,'SPSStype'=>'F'),
	        'M'=>Array('name'=>'Multiple choice','size'=>1,'SPSStype'=>'F'),
	        'P'=>Array('name'=>'Multiple choice with comments','size'=>1,'SPSStype'=>'F'),
	        'Q'=>Array('name'=>'Multiple Short Text','size'=>1,'SPSStype'=>'F'),
	        'N'=>Array('name'=>'Numerical Input','size'=>3,'SPSStype'=>'F','Scale'=>3),
	        'R'=>Array('name'=>'Ranking','size'=>1,'SPSStype'=>'F'),
	        'S'=>Array('name'=>'Short free text','size'=>1,'SPSStype'=>'F'),
	        'Y'=>Array('name'=>'Yes/No','size'=>1,'SPSStype'=>'F'),
	        ':'=>Array('name'=>'Multi flexi numbers','size'=>1,'SPSStype'=>'F','Scale'=>3),
	        ';'=>Array('name'=>'Multi flexi text','size'=>1,'SPSStype'=>'A'),
	        '*'=>Array('name'=>'Equation','size'=>1,'SPSStype'=>'A'),
        );

        if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
        $filterstate = incompleteAnsFilterstate();

        $headerComment = '#$Rev: 10193 $' . " $filterstate.\n";

        if (isset($_POST['dldata'])) $subaction = "dldata";
        if (isset($_POST['dlstructure'])) $subaction = "dlstructure";

        if  (!isset($subaction))
        {
            $this->controller->_getAdminHeader();
            $this->_browsemenubar($surveyid, $clang->gT('Export results'));

            $selecthide="";
            $selectshow="";
            $selectinc="";
            switch ($filterstate) {
                case "inc":
                    $selectinc="selected='selected'";
                    break;
                case "filter":
                    $selecthide="selected='selected'";
                    break;
                default:
                    $selectshow="selected='selected'";
            }

            $data['clang'] = $clang;
            $data['selectinc'] = $selectinc;
            $data['selecthide'] = $selecthide;
            $data['selectshow'] = $selectshow;
            $data['filename'] = "survey_".$surveyid."_R_syntax_file.R";
        	$data['surveyid'] = $surveyid;
            $this->controller->render("/admin/export/r_view",$data);
            $this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->controller->lang->gT("LimeSurvey online manual"));
        }
        else
        {
            // Get Base language:
            $language = GetBaseLanguageFromSurveyID($surveyid);
            $clang = new limesurvey_lang(array($language));
            Yii::app()->loadHelper("admin/exportresults");
        }


        if  ($subaction=='dldata')
        {
            header("Content-Disposition: attachment; filename=survey_".$surveyid."_R_data_file.csv");
            header("Content-type: text/comma-separated-values; charset=UTF-8");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: public");

            $na="";	//change to empty string instead of two double quotes to fix warnings on NA
            spss_export_data($na);

            exit;
        }

        if  ($subaction=='dlstructure')
        {
            header("Content-Disposition: attachment; filename=survey_".$surveyid."_R_syntax_file.R");
            header("Content-type: application/download; charset=UTF-8");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: public");

            echo $headerComment;
            echo "data <- read.table(\"survey_".$surveyid
            ."_R_data_file.csv\", sep=\",\", quote = \"'\", "
            ."na.strings=c(\"\",\"\\\"\\\"\"), "
            ."stringsAsFactors=FALSE)\n\n";
            //  echo "names(data) <- paste(\"V\",1:dim(data)[2],sep=\"\")\n\n";

            // Build array that has to be returned
            $fields = spss_fieldmap("V");

            //Now get the query string with all fields to export
            $query = spss_getquery();

            $result=Yii::app()->db->createCommand($query)->query(); //Checked
        	$result = $result->readAll();
            $num_fields = isset($result[0]) ? count($result[0]) : array();

            //Now we check if we need to adjust the size of the field or the type of the field
            foreach ($result as $row) {
                $row = array_values($row);
                $fieldno = 0;
                while ($fieldno < $num_fields)
                {
                    //Performance improvement, don't recheck fields that have valuelabels
                    if (!isset($fields[$fieldno]['answers'])) {
                        $strTmp=mb_substr(strip_tags_full($row[$fieldno]), 0, $length_data);
                        $len = mb_strlen($strTmp);
                        if($len > $fields[$fieldno]['size']) $fields[$fieldno]['size'] = $len;

                        if (trim($strTmp) != ''){
                            if ($fields[$fieldno]['SPSStype']=='F' && (my_is_numeric($strTmp)===false || $fields[$fieldno]['size']>16))
                            {
                                $fields[$fieldno]['SPSStype']='A';
                            }
                        }
                    }
                    $fieldno++;
                }
            }

            $errors = "";
            $i = 1;
            foreach ($fields as $field)
            {
                if($field['SPSStype'] == 'DATETIME23.2') $field['size']='';
                if($field['LStype'] == 'N' || $field['LStype']=='K')
                {
                    $field['size'].='.'.($field['size']-1);
                }
                switch ($field['SPSStype'])
                {
                    case 'F':
                        $type="numeric";
                        break;
                    case 'A':
                        $type="character";
                        break;
                    case 'DATETIME23.2':
                    case 'SDATE':
                        $type="character";
                        //@TODO set $type to format for date
                        break;
                }

                if (!$field['hide'])
                {
                    echo "data[, " . $i . "] <- "
                    ."as.$type(data[, " . $i . "])\n";

                    echo 'attributes(data)$variable.labels[' . $i . '] <- "'
                    . addslashes(
                    htmlspecialchars_decode(
                    mb_substr(
                    strip_tags_full(
                    $field['VariableLabel']),0,$length_varlabel)))  // <AdV> added htmlspecialchars_decode
                    . '"' . "\n";

                    // Create the value Labels!
                    if (isset($field['answers']))
                    {
                        $answers = $field['answers'];
                        //print out the value labels!
                        // data$V14=factor(data$V14,levels=c(1,2,3),labels=c("Yes","No","Uncertain"))
                        echo 'data[, ' . $i .'] <- factor(data[, ' . $i . '], levels=c(';
                        $str="";
                        foreach ($answers as $answer) {
                            if ($field['SPSStype']=="F" && my_is_numeric($answer['code'])) {
                                $str .= ",{$answer['code']}";
                            } else {
                                $str .= ",\"{$answer['code']}\"";
                            }
                        }
                        $str = mb_substr($str,1);
                        echo $str . '),labels=c(';
                        $str="";
                        foreach ($answers as $answer) {
                            $str .= ",\"{$answer['value']}\"";
                        }
                        $str = mb_substr($str,1);
                        if($field['scale']!=='' && $field['scale'] == 2 ) {
                            $scale = ",ordered=TRUE";
                        } else {
                            $scale = "";
                        }
                        echo "$str)$scale)\n";
                    }

                    //Rename the Variables (in case somethings goes wrong, we still have the OLD values
                    if (isset($field['sql_name']))
                    {
                        $ftitle = $field['title'];
                        if (!preg_match ("/^([a-z]|[A-Z])+.*$/", $ftitle))
                        {
                            $ftitle = "q_" . $ftitle;
                        }
                        $ftitle = str_replace(array("-",":",";","!"), array("_hyph_","_dd_","_dc_","_excl_"), $ftitle);
                        if (!$field['hide'])
                        {
                            if ($ftitle != $field['title'])
                            {
                                $errors .= "# Variable name was incorrect and was changed from {$field['title']} to $ftitle .\n";
                            }
                            echo "names(data)[" . $i . "] <- "
                            . "\"". $ftitle . "\"\n";  // <AdV> added \n
                        }
                        $i++;
                    }
                    else
                    {
                        echo "#sql_name not set\n";
                    }

                }
                else
                {
                    echo "#Field hidden\n";
                }
                echo "\n";

            }  // end foreach
            echo $errors;
            exit;
        }


    }

    function vvexport($surveyid, $subaction = null)
    {
        $surveyid = (int) $surveyid;

        //Exports all responses to a survey in special "Verified Voting" format.
        $clang = $this->getController()->lang;

        if (!bHasSurveyPermission($surveyid, 'responses','export'))
        {
            return;
        }

        if ($subaction != "export")
        {
            if(incompleteAnsFilterstate() == "inc")
            {
                $selecthide="";
                $selectshow="";
                $selectinc="selected='selected'";
            }
            elseif (incompleteAnsFilterstate() == "filter")
            {
                $selecthide="selected='selected'";
                $selectshow="";
                $selectinc="";
            }
            else
            {
                $selecthide="";
                $selectshow="selected='selected'";
                $selectinc="";
            }

            $this->getController()->_getAdminHeader();
			$this->_browsemenubar($surveyid, $clang->gT("Export VV file"));

            $data["clang"] = $clang;
            $data['selectinc'] = $selectinc;
            $data['selecthide'] = $selecthide;
            $data['selectshow'] = $selectshow;
        	$data['surveyid'] = $surveyid;
            $this->getController()->render("/admin/export/vv_view", $data);
            $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
        }
        elseif (isset($surveyid) && $surveyid)
        {
            //Export is happening
            $extension=sanitize_paranoid_string(returnglobal('extension'));
            //header("Content-Disposition: attachment; filename=vvexport_$surveyid.".$extension);
            //header("Content-type: text/comma-separated-values; charset=UTF-8");
            //header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            //header("Pragma: cache");
            $s="\t";

            $fieldmap=createFieldMap($surveyid, "full");
            $surveytable = "{{survey_$surveyid}}";

            GetBaseLanguageFromSurveyID($surveyid);

            $fieldnames = Yii::app()->db->schema->getTable($surveytable)->getColumnNames();

            //Create the human friendly first line
            $firstline="";
            $secondline="";
            foreach ($fieldnames as $field)
            {
                $fielddata=arraySearchByKey($field, $fieldmap, "fieldname", 1);
                //$vvoutput .= "<pre>";print_r($fielddata);$vvoutput .= "</pre>";
                if (count($fielddata) < 1) {$firstline.=$field;}
                else
                //{$firstline.=str_replace("\n", " ", str_replace("\t", "   ", strip_tags($fielddata['question'])));}
                {$firstline.=preg_replace('/\s+/',' ',strip_tags($fielddata['question']));}
                $firstline .= $s;
                $secondline .= $field.$s;
            }
            $vvoutput = $firstline."\n";
            $vvoutput .= $secondline."\n";
            $query = "SELECT * FROM $surveytable";
            if (incompleteAnsFilterstate() == "inc")
            {
                $query .= " WHERE submitdate IS NULL ";
            }
            elseif (incompleteAnsFilterstate() == "filter")
            {
                $query .= " WHERE submitdate >= '01/01/1980' ";
            }
            $result = Yii::app()->db->createCommand($query)->query();

            foreach ($result->readAll() as $row)
            {
                foreach ($fieldnames as $field)
                {
                    if (is_null($row[$field]))
                    {
                        $value='{question_not_shown}';
                    }
                    else
                    {
                        $value=trim($row[$field]);
                        // sunscreen for the value. necessary for the beach.
                        // careful about the order of these arrays:
                        // lbrace has to be substituted *first*
                        $value=str_replace(array("{",
                        "\n",
                        "\r",
                        "\t"),
                        array("{lbrace}",
                        "{newline}",
                        "{cr}",
                        "{tab}"),
                        $value);
                    }
                    // one last tweak: excel likes to quote values when it
                    // exports as tab-delimited (esp if value contains a comma,
                    // oddly enough).  So we're going to encode a leading quote,
                    // if it occurs, so that we can tell the difference between
                    // strings that "really are" quoted, and those that excel quotes
                    // for us.
                    $value=preg_replace('/^"/','{quote}',$value);
                    // yay!  that nasty soab won't hurt us now!
                    if($field == "submitdate" && !$value) {$value = "NULL";}
                    $sun[]=$value;
                }
                $beach=implode($s, $sun);
                $vvoutput .= $beach;
                unset($sun);
                $vvoutput .= "\n";
            }
            echo $vvoutput;
            exit;

            //$vvoutput .= "<pre>$firstline</pre>";
            //$vvoutput .= "<pre>$secondline</pre>";
            //$vvoutput .= "<pre>"; print_r($fieldnames); $vvoutput .= "</pre>";
            //$vvoutput .= "<pre>"; print_r($fieldmap); $vvoutput .= "</pre>";

        }

    }

    /**
    * quexml survey export
    */
    function showquexmlsurvey($surveyid, $lang = null)
    {
        $surveyid = sanitize_int($surveyid);
        $tempdir = Yii::app()->getConfig("tempdir");

        // Set the language of the survey, either from GET parameter of session var
        if (isset($lang))
        {
            $lang = preg_replace("/[^a-zA-Z0-9-]/", "", $lang);
            if ($lang) $surveyprintlang = $lang;
        } else
        {
            $surveyprintlang=GetbaseLanguageFromSurveyid($surveyid);
        }

        // Setting the selected language for printout
        $clang = new limesurvey_lang(array($surveyprintlang));

        Yii::import("application.libraries.admin.queXMLPDF", true);
        $quexmlpdf = new queXMLPDF($this->getController()); //new queXMLPDF(PDF_PAGE_ORIENTATION, 'mm', PDF_PAGE_FORMAT, true, 'UTF-8', false);

        set_time_limit(120);

        $noheader = true;

        $quexml = quexml_export($surveyid, $surveyprintlang);

        $quexmlpdf->create($quexmlpdf->createqueXML($quexml));

        //NEED TO GET QID from $quexmlpdf
        $qid = intval($quexmlpdf->getQuestionnaireId());

        $zipdir= self::_tempdir($tempdir);

        $f1 = "$zipdir/quexf_banding_{$qid}_{$surveyprintlang}.xml";
        $f2 = "$zipdir/quexmlpdf_{$qid}_{$surveyprintlang}.pdf";
        $f3 = "$zipdir/quexml_{$qid}_{$surveyprintlang}.xml";
        $f4 = "$zipdir/readme.txt";

        file_put_contents($f1, $quexmlpdf->getLayout());
        file_put_contents($f2, $quexmlpdf->Output("quexml_$qid.pdf", 'S'));
        file_put_contents($f3, $quexml);
        file_put_contents($f4, $clang->gT('This archive contains a PDF file of the survey, the queXML file of the survey and a queXF banding XML file which can be used with queXF: http://quexf.sourceforge.net/ for processing scanned surveys.'));

    	Yii::import('application.libraries.admin.phpzip', true);
        $z = new Phpzip;
        $zipfile="$tempdir/quexmlpdf_{$qid}_{$surveyprintlang}.zip";
        $z->Zip($zipdir, $zipfile);

        unlink($f1);
        unlink($f2);
        unlink($f3);
        unlink($f4);
        rmdir($zipdir);

        header('Content-Type: application/zip');
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="quexmlpdf_' . $qid . '_' . $surveyprintlang . '.zip"');
        header("Pragma: public");

        // load the file to send:
        readfile($zipfile);
        unlink($zipfile);
    }

    function resources($action, $id)
    {
        $id = sanitize_int($id);
        $this->load->library("admin/Phpzip");
        $z = $this->phpzip;
        if ($action == "exportsurvresources") {
            $surveyid = $id;
            $resourcesdir=$this->config->item("uploaddir")."/surveys/$surveyid/";
            $zipfile=$this->config->item("tempdir")."/resources-survey-$surveyid.zip";
            $z -> Zip($resourcesdir, $zipfile);
            if (is_file($zipfile)) {
                //Send the file for download!
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

                header("Content-Type: application/force-download");
                header( "Content-Disposition: attachment; filename=resources-survey-$surveyid.zip" );
                header( "Content-Description: File Transfer");
                @readfile($zipfile);

                //Delete the temporary file
                unlink($zipfile);
                return;
            }
        }
        if ($action == "exportlabelresources") {
            $lid = $id;
            $resourcesdir=$this->config->item("uploaddir")."/labels/$lid/";
            $zipfile=$this->config->item("tempdir")."/resources-labelset-$lid.zip";
            $z -> Zip($resourcesdir, $zipfile);
            if (is_file($zipfile)) {
                //Send the file for download!
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

                header("Content-Type: application/force-download");
                header( "Content-Disposition: attachment; filename=resources-label-$lid.zip" );
                header( "Content-Description: File Transfer");
                @readfile($zipfile);

                //Delete the temporary file
                unlink($zipfile);
                return;
            }
        }
    }

    function dumplabel($lid=null)
    {
        $lid = sanitize_int($lid);
        // DUMP THE RELATED DATA FOR A SINGLE QUESTION INTO A SQL FILE FOR IMPORTING LATER ON OR
        // ON ANOTHER SURVEY SETUP DUMP ALL DATA WITH RELATED QID FROM THE FOLLOWING TABLES
        // 1. questions
        // 2. answers

        $lids=returnglobal('lids');
        if (!$lid && !$lids) die('No LID has been provided. Cannot dump label set.');

        if ($lid)
        {
            $lids=array($lid);
        }
        $lids=array_map('sanitize_int',$lids);

        $fn = "limesurvey_labelset_".implode('_',$lids).".lsl";
        $xml = getXMLWriter();

        header("Content-Type: text/html/force-download");
        header("Content-Disposition: attachment; filename=$fn");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: cache");                          // HTTP/1.0

        $xml->openURI('php://output');

        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('document');
        $xml->writeElement('LimeSurveyDocType','Label set');
        $xml->writeElement('DBVersion',$this->config->item("dbversionnumber"));
        // Label sets table
        $dbprefix = $this->db->dbprefix;
        $lsquery = "SELECT * FROM {$dbprefix}labelsets WHERE lid=".implode(' or lid=',$lids);
        BuildXMLFromQuery($xml,$lsquery,'labelsets');
        // Labels
        $lquery = "SELECT lid, code, title, sortorder, language, assessment_value FROM {$dbprefix}labels WHERE lid=".implode(' or lid=',$lids);
        BuildXMLFromQuery($xml,$lquery,'labels');
        $xml->endElement(); // close columns
        $xml->endDocument();
        exit;


    }

    /**
    * Comes from http://fr2.php.net/tempnam
    */
    function _tempdir($dir, $prefix='', $mode=0700)
    {
        if (substr($dir, -1) != '/') $dir .= '/';

        do
        {
            $path = $dir.$prefix.mt_rand(0, 9999999);
        } while (!mkdir($path, $mode));

        return $path;
    }

}
