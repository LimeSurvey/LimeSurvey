<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
* Export Action
*
* This controller performs export actions
*
* @package		LimeSurvey
* @subpackage	Backend
*/
class export extends Survey_Common_Action {

    function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        Yii::app()->loadHelper('export');
    }

    public function survey()
    {
        $action = Yii::app()->request->getParam('action');
        $iSurveyID = sanitize_int(Yii::app()->request->getParam('surveyid'));

        if ( Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'export') )
        {
            $this->_surveyexport($action, $iSurveyID);
            return;
        }
    }

    /**
    * This function exports a ZIP archives of several ZIP archives - it is used in the listSurvey controller
    * The SIDs are read from session flashdata.
    *
    */
    public function surveyarchives()
    {
        if ( ! Permission::model()->hasGlobalPermission('superadmin','read') )
        {
            die('Access denied.');
        }

        $aSurveyIDs = $this->session->flashdata('sids');
        $aExportedFiles = array();

        foreach ($aSurveyIDs as $iSurveyID)
        {
            $iSurveyID = (int)$iSurveyID;

            if ( $iSurveyID > 0 )
            {
                $aExportedFiles[$iSurveyID] = $this->_exportarchive($iSurveyID,FALSE);
            }
        }

        if ( count($aExportedFiles) > 0 )
        {
            $aZIPFileName=$this->config->item("tempdir") . DIRECTORY_SEPARATOR . randomChars(30);

            $this->load->library("admin/pclzip", array('p_zipname' => $aZIPFileName));

            $zip = new PclZip($aZIPFileName);
            foreach ($aExportedFiles as $iSurveyID=>$sFileName)
            {
                $zip->add(
                array(
                array(
                PCLZIP_ATT_FILE_NAME => $sFileName,
                PCLZIP_ATT_FILE_NEW_FULL_NAME => 'survey_archive_' . $iSurveyID . '.zip')
                )
                );

                unlink($sFileName);
            }
        }

        if ( is_file($aZIPFileName) )
        {
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

    public function group()
    {
        $gid = sanitize_int(Yii::app()->request->getParam('gid'));
        $iSurveyID = sanitize_int(Yii::app()->request->getParam('surveyid'));

        group_export("exportstructurecsvGroup", $iSurveyID, $gid);

        return;
    }

    public function question()
    {
        $gid = sanitize_int(Yii::app()->request->getParam('gid'));
        $qid = sanitize_int(Yii::app()->request->getParam('qid'));
        $iSurveyID = sanitize_int(Yii::app()->request->getParam('surveyid'));
        questionExport("exportstructurecsvQuestion", $iSurveyID, $gid, $qid);
    }

    public function exportresults()
    {
        $iSurveyID = sanitize_int(Yii::app()->request->getParam('surveyid'));

        if ( ! isset($imageurl) ) { $imageurl = "./images"; }
        if ( ! isset($iSurveyID) ) { $iSurveyID = returnGlobal('sid'); }
        if ( ! isset($exportstyle) ) { $exportstyle = returnGlobal('exportstyle'); }
        if ( ! isset($answers) ) { $answers = returnGlobal('answers'); }
        if ( ! isset($type) ) { $type = returnGlobal('type'); }
        if ( ! isset($convertyto1) ) { $convertyto1 = returnGlobal('convertyto1'); }
        if ( ! isset($convertnto2) ) { $convertnto2 = returnGlobal('convertnto2'); }
        if ( ! isset($convertyto) ) { $convertyto = returnGlobal('convertyto'); }
        if ( ! isset($convertnto) ) { $convertnto = returnGlobal('convertnto'); }
        if ( ! isset($convertspacetous) ) { $convertspacetous = returnGlobal('convertspacetous'); }
        $clang = Yii::app()->lang;

        if ( ! Permission::model()->hasSurveyPermission($iSurveyID, 'responses', 'export') )
        {
            $this->getController()->error('Access denied!');
        }

        Yii::app()->loadHelper("admin/exportresults");

        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."expressions/em_javascript.js");
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . '/exportresults.js');

        $surveybaselang = Survey::model()->findByPk($iSurveyID)->language;
        $exportoutput = "";

        // Get info about the survey
        $thissurvey = getSurveyInfo($iSurveyID);

        // Load ExportSurveyResultsService so we know what exports are available
        $resultsService = new ExportSurveyResultsService();
        $exports = $resultsService->getExports();

        if ( ! $exportstyle )
        {
            //FIND OUT HOW MANY FIELDS WILL BE NEEDED - FOR 255 COLUMN LIMIT
            $aFieldMap = createFieldMap($iSurveyID,'full',false,false,getBaseLanguageFromSurveyID($iSurveyID));
            if ($thissurvey['savetimings'] === "Y") {
                //Append survey timings to the fieldmap array
                $aFieldMap = $aFieldMap + createTimingsFieldMap($iSurveyID, 'full',false,false,getBaseLanguageFromSurveyID($iSurveyID));
            }
            $iFieldCount = count($aFieldMap);

            $selecthide = "";
            $selectshow = "";
            $selectinc = "";
            if ( incompleteAnsFilterState() == "complete" )
            {
                $selecthide = "selected='selected'";
            }
            elseif ( incompleteAnsFilterState() == "incomplete" )
            {
                $selectinc = "selected='selected'";
            }
            else
            {
                $selectshow = "selected='selected'";
            }

            $aFields=array();
            foreach($aFieldMap as $sFieldName=>$fieldinfo)
            {
                $sCode=viewHelper::getFieldCode($fieldinfo);
                $aFields[$sFieldName]=$sCode.' - '.htmlspecialchars(ellipsize(html_entity_decode(viewHelper::getFieldText($fieldinfo)),30,.6,'...'));
                $aFieldsOptions[$sFieldName]=array('title'=>viewHelper::getFieldText($fieldinfo),'data-fieldname'=>$fieldinfo['fieldname'],'data-emcode'=>viewHelper::getFieldCode($fieldinfo,array('LEMcompat'=>true))); // No need to filter title : Yii do it (remove all tag)
            }

            $data['SingleResponse']=(int)returnGlobal('id');
            $data['selecthide'] = $selecthide;
            $data['selectshow'] = $selectshow;
            $data['selectinc'] = $selectinc;
            $data['afieldcount'] = $iFieldCount;
            $data['aFields'] = $aFields;
            $data['aFieldsOptions'] = $aFieldsOptions;
            //get max number of datasets
            $iMaximum = SurveyDynamic::model($iSurveyID)->getMaxId();

            $data['max_datasets'] = $iMaximum;
            $data['surveyid'] = $iSurveyID;
            $data['imageurl'] = Yii::app()->getConfig('imageurl');
            $data['thissurvey'] = $thissurvey;
            $data['display']['menu_bars']['browse'] = $clang->gT("Export results");

            // Export plugins, leave out all entries that are not plugin
            $exports = array_filter($exports);
            $exportData = array();
            foreach ($exports as $key => $plugin) {
                $event = new PluginEvent('listExportOptions');
                $event->set('type', $key);
                $oPluginManager = App()->getPluginManager();
                $oPluginManager->dispatchEvent($event, $plugin);
                $exportData[$key] = array(
                    'onclick' => $event->get('onclick'),
                    'label'   => $event->get('label'),
                    'checked' => $event->get('default', false),
                    'tooltip' => $event->get('tooltip', null)
                    );
            }

            $data['exports'] = $exportData;    // Pass available exports

            $this->_renderWrappedTemplate('export', 'exportresults_view', $data);

            return;
        }

        // Export Language is set by default to surveybaselang
        // * the explang language code is used in SQL queries
        // * the alang object is used to translate headers and hardcoded answers
        // In the future it might be possible to 'post' the 'export language' from
        // the exportresults form
        $explang = $surveybaselang;
        $elang = new limesurvey_lang($explang);

        //Get together our FormattingOptions and then call into the exportSurvey
        //function.
        $options = new FormattingOptions();
        $options->selectedColumns = Yii::app()->request->getPost('colselect');
        $options->responseMinRecord = sanitize_int(Yii::app()->request->getPost('export_from'));
        $options->responseMaxRecord = sanitize_int(Yii::app()->request->getPost('export_to'));
        $options->answerFormat = $answers;
        $options->convertN = $convertnto2;
        $options->output = 'display';

        if ( $options->convertN )
        {
            $options->nValue = $convertnto;
        }

        $options->convertY = $convertyto1;

        if ( $options->convertY )
        {
            $options->yValue = $convertyto;
        }

        $options->headerSpacesToUnderscores = $convertspacetous;
        $options->headingFormat = $exportstyle;
        $options->responseCompletionState = incompleteAnsFilterState();

        // Replace token information by the column name
        if ( in_array('first_name', Yii::app()->request->getPost('attribute_select', array())) )
        {
            $options->selectedColumns[]="firstname";
        }

        if ( in_array('last_name', Yii::app()->request->getPost('attribute_select', array())) )
        {
            $options->selectedColumns[]="lastname";
        }

        if ( in_array('email_address', Yii::app()->request->getPost('attribute_select', array())) )
        {
            $options->selectedColumns[]="email";
        }
        $attributeFields = array_keys(getTokenFieldsAndNames($iSurveyID, TRUE));
        foreach ($attributeFields as $attr_name)
        {
            if ( in_array($attr_name, Yii::app()->request->getPost('attribute_select',array())) )
            {
                $options->selectedColumns[]=$attr_name;
            }
        }

        if (Yii::app()->request->getPost('response_id'))
        {
            $sFilter="{{survey_{$iSurveyID}}}.id=".(int)Yii::app()->request->getPost('response_id');
        }
        else
        {
            $sFilter='';
        }
        viewHelper::disableHtmlLogging();
        $resultsService->exportSurvey($iSurveyID, $explang, $type, $options, $sFilter);

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
    public function exportspss()
    {
        global $length_vallabel;
        $iSurveyID = sanitize_int(Yii::app()->request->getParam('sid'));
        $clang = $this->getController()->lang;
        //for scale 1=nominal, 2=ordinal, 3=scale

        //		$typeMap = $this->_getTypeMap();

        $filterstate = incompleteAnsFilterState();
        $spssver = returnGlobal('spssver');

        if ( is_null($spssver) )
        {
            if ( ! Yii::app()->session['spssversion'] )
            {
                Yii::app()->session['spssversion'] = 2;	//Set default to 2, version 16 or up
            }

            $spssver = Yii::app()->session['spssversion'];
        }
        else
        {
            Yii::app()->session['spssversion'] = $spssver;
        }

        $length_varlabel = '231'; // Set the max text length of Variable Labels
        $length_vallabel = '120'; // Set the max text length of Value Labels

        switch ( $spssver )
        {
            case 1:	//<16
                $iLength	 = '255'; // Set the max text length of the Value
                break;
            case 2:	//>=16
                $iLength	 = '16384'; // Set the max text length of the Value
                break;
            default:
                $iLength	 = '16384'; // Set the max text length of the Value
        }

        $headerComment = '*$Rev: 121017 $' . " $filterstate $spssver.\n";

        if ( isset($_POST['dldata']) ) $subaction = "dldata";
        if ( isset($_POST['dlstructure']) ) $subaction = "dlstructure";

        if  ( ! isset($subaction) )
        {
            $selecthide = "";
            $selectshow = "";
            $selectinc = "";

            switch ($filterstate)
            {
                case "incomplete":
                    $selectinc="selected='selected'";
                    break;
                case "complete":
                    $selecthide="selected='selected'";
                    break;
                default:
                    $selectshow="selected='selected'";
            }

            $data['selectinc'] = $selectinc;
            $data['selecthide'] = $selecthide;
            $data['selectshow'] = $selectshow;
            $data['spssver'] = $spssver;
            $data['surveyid'] = $iSurveyID;
            $data['display']['menu_bars']['browse'] = $clang->gT('Export results');

            $this->_renderWrappedTemplate('export', 'spss_view', $data);
            return;
        }

        // Get Base language:
        $language = Survey::model()->findByPk($iSurveyID)->language;
        $clang = new limesurvey_lang($language);
        Yii::app()->loadHelper("admin/exportresults");
        viewHelper::disableHtmlLogging();

        if ( $subaction == 'dldata' )
        {
            header("Content-Disposition: attachment; filename=survey_" . $iSurveyID . "_SPSS_data_file.dat");
            header("Content-type: text/comma-separated-values; charset=UTF-8");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: public");

            if ( $spssver == 2 )
            {
                echo "\xEF\xBB\xBF";
            }

            SPSSExportData($iSurveyID, $iLength);

            exit;
        }

        if ( $subaction == 'dlstructure' )
        {
            header("Content-Disposition: attachment; filename=survey_" . $iSurveyID . "_SPSS_syntax_file.sps");
            header("Content-type: application/download; charset=UTF-8");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: public");

            // Build array that has to be returned
            $fields = SPSSFieldMap($iSurveyID);

            //Now get the query string with all fields to export
            $query = SPSSGetQuery($iSurveyID, 500, 0);  // Sample first 500 responses for adjusting fieldmap
            $result = $query->queryAll();

            $num_fields = 0;
            //Now we check if we need to adjust the size of the field or the type of the field
            foreach ( $result as $row )
            {

                foreach ( $fields as $iIndex=>$aField )
                {
                    //Performance improvement, don't recheck fields that have valuelabels
                    if ( ! isset($aField['answers']) )
                    {
                        $strTmp = mb_substr(stripTagsFull($row[$aField['sql_name']]), 0, $iLength);
                        $len = mb_strlen($strTmp);

                        if ( $len > $fields[$iIndex]['size'] ) $fields[$iIndex]['size'] = $len;

                        if ( trim($strTmp) != '' )
                        {
                            if ( $fields[$iIndex]['SPSStype'] == 'F' && (isNumericExtended($strTmp) === FALSE || $fields[$iIndex]['size'] > 16) )
                            {
                                $fields[$iIndex]['SPSStype'] = 'A';
                            }
                        }
                    }
                }
            }

            /**
            * End of DATA print out
            *
            * Now $fields contains accurate length data, and the DATA LIST can be rendered -- then the contents of the temp file can
            * be sent to the client.
            */
            if ( $spssver == 2 )
            {
                echo "\xEF\xBB\xBF";
            }

            echo $headerComment;

            if  ($spssver == 2 )
            {
                echo "SET UNICODE=ON.\n";
            }

            echo "SHOW LOCALE.\n";
            echo "PRESERVE LOCALE.\n";
            echo "SET LOCALE='en_UK'.\n";

            echo "GET DATA\n"
            ." /TYPE=TXT\n"
            ." /FILE='survey_" . $iSurveyID . "_SPSS_data_file.dat'\n"
            ." /DELCASE=LINE\n"
            ." /DELIMITERS=\",\"\n"
            ." /QUALIFIER=\"'\"\n"
            ." /ARRANGEMENT=DELIMITED\n"
            ." /FIRSTCASE=1\n"
            ." /IMPORTCASE=ALL\n"
            ." /VARIABLES=";

            foreach ( $fields as $field )
            {
                if( $field['SPSStype'] == 'DATETIME23.2' ) $field['size'] = '';

                if($field['SPSStype'] == 'F' && ($field['LStype'] == 'N' || $field['LStype'] == 'K'))
                {
                    $field['size'] .= '.' . ($field['size']-1);
                }

                if ( !$field['hide'] ) echo "\n {$field['id']} {$field['SPSStype']}{$field['size']}";
            }

            echo ".\nCACHE.\n"
            ."EXECUTE.\n";

            //Create the variable labels:
            echo "*Define Variable Properties.\n";
            foreach ( $fields as $field )
            {
                if ( ! $field['hide'] )
                {
                    $label_parts = strSplitUnicode(str_replace('"','""',stripTagsFull($field['VariableLabel'])), $length_varlabel-strlen($field['id']));
                    //if replaced quotes are splitted by, we need to mve the first quote to the next row
                    foreach($label_parts as $idx => $label_part)
                    {
                        if($idx != count($label_parts) && substr($label_part,-1) == '"' && substr($label_part,-2) != '"')
                        {
                            $label_parts[$idx] = rtrim($label_part, '"');
                            $label_parts[$idx + 1] = '"' . $label_parts[$idx + 1];
                        }
                    }
                    echo "VARIABLE LABELS " . $field['id'] . " \"" . implode("\"+\n\"", $label_parts) . "\".\n";
                }
            }

            // Create our Value Labels!
            echo "*Define Value labels.\n";
            foreach ( $fields as $field )
            {
                if ( isset($field['answers']) )
                {
                    $answers = $field['answers'];

                    //print out the value labels!
                    echo "VALUE LABELS  {$field['id']}\n";

                    $i=0;
                    foreach ( $answers as $answer )
                    {
                        $i++;

                        if ( $field['SPSStype'] == "F" && isNumericExtended($answer['code']) )
                        {
                            $str = "{$answer['code']}";
                        }
                        else
                        {
                            $str = "\"{$answer['code']}\"";
                        }

                        if ( $i < count($answers) )
                        {
                            echo " $str \"{$answer['value']}\"\n";
                        }
                        else
                        {
                            echo " $str \"{$answer['value']}\".\n";
                        }
                    }
                }
            }

            foreach ( $fields as $field )
            {
                if( $field['scale'] !== '' )
                {
                    switch ( $field['scale'] )
                    {
                        case 2:
                            echo "VARIABLE LEVEL {$field['id']}(ORDINAL).\n";
                            break;
                        case 3:
                            echo "VARIABLE LEVEL {$field['id']}(SCALE).\n";
                    }
                }
            }

            //Rename the Variables (in case somethings goes wrong, we still have the OLD values
            foreach ( $fields as $field )
            {
                if ( isset($field['sql_name']) && $field['hide'] === 0 )
                {
                    $ftitle = $field['title'];

                    if ( ! preg_match ("/^([a-z]|[A-Z])+.*$/", $ftitle) )
                    {
                        $ftitle = "q_" . $ftitle;
                    }

                    $ftitle = str_replace(array(" ","-",":",";","!","/","\\","'"), array("_","_hyph_","_dd_","_dc_","_excl_","_fs_","_bs_",'_qu_'), $ftitle);

                    if ( $ftitle != $field['title'] )
                    {
                        echo "* Variable name was incorrect and was changed from {$field['title']} to $ftitle .\n";
                    }

                    echo "RENAME VARIABLE ( " . $field['id'] . ' = ' . $ftitle . " ).\n";
                }
            }
            echo "RESTORE LOCALE.\n";
            exit;
        }
    }

    public function vvexport()
    {
        $iSurveyId = sanitize_int(Yii::app()->request->getParam('surveyid'));
        $subaction = Yii::app()->request->getParam('subaction');

        //Exports all responses to a survey in special "Verified Voting" format.
        $clang = $this->getController()->lang;

        if ( ! Permission::model()->hasSurveyPermission($iSurveyId, 'responses','export') )
        {
            Yii::app()->session['flashmessage'] = $clang->gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }

        if ( $subaction != "export" )
        {
            $aData['selectincansstate']=incompleteAnsFilterState();
            $aData['surveyid'] = $iSurveyId;
            $aData['display']['menu_bars']['browse'] = $clang->gT("Export VV file");
            $fieldmap = createFieldMap($iSurveyId,'full',false,false,getBaseLanguageFromSurveyID($iSurveyId));

            Survey::model()->findByPk($iSurveyId)->language;
            $surveytable = "{{survey_$iSurveyId}}";
            // Control if fieldcode are unique
            $fieldnames = Yii::app()->db->schema->getTable($surveytable)->getColumnNames();
            foreach ( $fieldnames as $field )
            {
                $fielddata=arraySearchByKey($field, $fieldmap, "fieldname", 1);
                $fieldcode[]=viewHelper::getFieldCode($fielddata,array("LEMcompat"=>true));
            }
            $aData['uniquefieldcode']=(count(array_unique ($fieldcode))==count($fieldcode)); // Did we need more control ?
            $aData['vvversionseleted']=($aData['uniquefieldcode'])?2:1;
            $this->_renderWrappedTemplate('export', 'vv_view', $aData);
        }
        elseif ( isset($iSurveyId) && $iSurveyId )
        {
            //Export is happening
            $extension = sanitize_paranoid_string(returnGlobal('extension'));
            $vvVersion = (int) Yii::app()->request->getPost('vvversion');
            $vvVersion = (in_array($vvVersion,array(1,2)))?$vvVersion:2;// Only 2 version actually, default to 2
            $fn = "vvexport_$iSurveyId." . $extension;

            $this->_addHeaders($fn, "text/comma-separated-values", 0, "cache");

            $s="\t";

            $fieldmap = createFieldMap($iSurveyId,'full',false,false,getBaseLanguageFromSurveyID($iSurveyId));
            $surveytable = "{{survey_$iSurveyId}}";

            Survey::model()->findByPk($iSurveyId)->language;

            $fieldnames = Yii::app()->db->schema->getTable($surveytable)->getColumnNames();

            //Create the human friendly first line
            $firstline = "";
            $secondline = "";
            foreach ( $fieldnames as $field )
            {
                $fielddata=arraySearchByKey($field, $fieldmap, "fieldname", 1);

                if ( count($fielddata) < 1 )
                {
                    $firstline .= $field;
                }
                else
                {
                    $firstline.=preg_replace('/\s+/', ' ', strip_tags($fielddata['question']));
                }
                $firstline .= $s;
                if($vvVersion==2){
                    $fieldcode=viewHelper::getFieldCode($fielddata,array("LEMcompat"=>true));
                    $fieldcode=($fieldcode)?$fieldcode:$field;// $fieldcode is empty for token if there are no token table
                }else{
                    $fieldcode=$field;
                }
                $secondline .= $fieldcode.$s;
            }

            $vvoutput = $firstline . "\n";
            $vvoutput .= $secondline . "\n";
            $query = "SELECT * FROM ".Yii::app()->db->quoteTableName($surveytable);

            if (incompleteAnsFilterState() == "incomplete")
            {
                $query .= " WHERE submitdate IS NULL ";
            }
            elseif (incompleteAnsFilterState() == "complete")
            {
                $query .= " WHERE submitdate >= '01/01/1980' ";
            }
            $result = Yii::app()->db->createCommand($query)->query();

            echo $vvoutput;
            foreach ($result as $row)
            {
                foreach ( $fieldnames as $field )
                {
                    if ( is_null($row[$field]) )
                    {
                        $value = '{question_not_shown}';
                    }
                    else
                    {
                        $value = trim($row[$field]);
                        // sunscreen for the value. necessary for the beach.
                        // careful about the order of these arrays:
                        // lbrace has to be substituted *first*
                        $value = str_replace(
                        array(
                        "{",
                        "\n",
                        "\r",
                        "\t"),
                        array("{lbrace}",
                        "{newline}",
                        "{cr}",
                        "{tab}"
                        ),
                        $value
                        );
                    }

                    // one last tweak: excel likes to quote values when it
                    // exports as tab-delimited (esp if value contains a comma,
                    // oddly enough).  So we're going to encode a leading quote,
                    // if it occurs, so that we can tell the difference between
                    // strings that "really are" quoted, and those that excel quotes
                    // for us.
                    $value = preg_replace('/^"/','{quote}',$value);
                    // yay!  that nasty soab won't hurt us now!
                    if( $field == "submitdate" && !$value ) { $value = "NULL"; }

                    $sun[]=$value;
                }

                /* it is important here to stream output data, line by line
                 * in order to avoid huge memory consumption when exporting large
                 * quantities of answers */
                echo implode($s, $sun)."\n";

                unset($sun);
            }

            exit;
        }
    }

    /**
    * quexml survey export
    */
    public function showquexmlsurvey()
    {
        $iSurveyID = sanitize_int(Yii::app()->request->getParam('surveyid'));
        $lang = ( isset($_GET['lang']) ) ? Yii::app()->request->getParam('lang') : NULL;
        $tempdir = Yii::app()->getConfig("tempdir");

        // Set the language of the survey, either from GET parameter of session var
        if ( $lang != NULL )
        {
            $lang = preg_replace("/[^a-zA-Z0-9-]/", "", $lang);
            if ( $lang ) $surveyprintlang = $lang;
        }
        else
        {
            $surveyprintlang=Survey::model()->findByPk($iSurveyID)->language;
        }

        // Setting the selected language for printout
        $clang = new limesurvey_lang($surveyprintlang);

        Yii::import("application.libraries.admin.quexmlpdf", TRUE);
        $quexmlpdf = new quexmlpdf($this->getController());

	$quexmlpdf->setLanguage($surveyprintlang);

        set_time_limit(120);

        $noheader = TRUE;

        $quexml = quexml_export($iSurveyID, $surveyprintlang);

        $quexmlpdf->create($quexmlpdf->createqueXML($quexml));

        //NEED TO GET QID from $quexmlpdf
        $qid = intval($quexmlpdf->getQuestionnaireId());

        $zipdir= $this->_tempdir($tempdir);

        $f1 = "$zipdir/quexf_banding_{$qid}_{$surveyprintlang}.xml";
        $f2 = "$zipdir/quexmlpdf_{$qid}_{$surveyprintlang}.pdf";
        $f3 = "$zipdir/quexml_{$qid}_{$surveyprintlang}.xml";
        $f4 = "$zipdir/readme.txt";

        file_put_contents($f1, $quexmlpdf->getLayout());
        file_put_contents($f2, $quexmlpdf->Output("quexml_$qid.pdf", 'S'));
        file_put_contents($f3, $quexml);
        file_put_contents($f4, $clang->gT('This archive contains a PDF file of the survey, the queXML file of the survey and a queXF banding XML file which can be used with queXF: http://quexf.sourceforge.net/ for processing scanned surveys.'));

        Yii::app()->loadLibrary('admin.pclzip');
        $zipfile="$tempdir/quexmlpdf_{$qid}_{$surveyprintlang}.zip";
        $z = new PclZip($zipfile);
        $z->create($zipdir, PCLZIP_OPT_REMOVE_PATH, $zipdir);

        unlink($f1);
        unlink($f2);
        unlink($f3);
        unlink($f4);
        rmdir($zipdir);

        $fn = "quexmlpdf_{$qid}_{$surveyprintlang}.zip";
        $this->_addHeaders($fn, "application/zip", 0);
        header('Content-Transfer-Encoding: binary');

        // load the file to send:
        readfile($zipfile);
        unlink($zipfile);
    }

    public function resources()
    {
        switch ( Yii::app()->request->getParam('export') )
        {
            case 'survey' :
                $iSurveyID = sanitize_int(Yii::app()->getRequest()->getParam('surveyid'));
                $resourcesdir = 'surveys/' . $iSurveyID;
                $zipfilename = "resources-survey-$iSurveyID.zip";
                break;
            case 'label' :
                $lid = sanitize_int(Yii::app()->getRequest()->getParam('lid'));
                $resourcesdir = 'labels/' . $lid;
                $zipfilename = "resources-labelset-$lid.zip";
                break;
        }

        if (!empty($zipfilename) && !empty($resourcesdir))
        {
            $resourcesdir = Yii::app()->getConfig('uploaddir') . "/{$resourcesdir}/";
            $tmpdir = Yii::app()->getConfig('tempdir') . '/';
            $zipfilepath = $tmpdir . $zipfilename;
            Yii::app()->loadLibrary('admin.pclzip');
            $zip = new PclZip($zipfilepath);
            $zipdirs = array();
            foreach (array('files', 'flash', 'images') as $zipdir)
            {
                if (is_dir($resourcesdir . $zipdir))
                    $zipdirs[] = $resourcesdir . $zipdir . '/';
            }
            if ($zip->create($zipdirs, PCLZIP_OPT_REMOVE_PATH, $resourcesdir) === 0)
            {
                die("Error : ".$zip->errorInfo(true));
            }
            elseif (file_exists($zipfilepath))
            {
                $this->_addHeaders($zipfilename, 'application/force-download', 0);
                readfile($zipfilepath);
                unlink($zipfilepath);
                exit;
            }
        }
    }

    public function dumplabel()
    {
        $lid = sanitize_int(Yii::app()->request->getParam('lid'));
        // DUMP THE RELATED DATA FOR A SINGLE QUESTION INTO A SQL FILE FOR IMPORTING LATER ON OR
        // ON ANOTHER SURVEY SETUP DUMP ALL DATA WITH RELATED QID FROM THE FOLLOWING TABLES
        // 1. questions
        // 2. answers

        $lids=returnGlobal('lids');

        if ( ! $lid && ! $lids )
        {
            die('No LID has been provided. Cannot dump label set.');
        }

        if ( $lid )
        {
            $lids = array($lid);
        }

        $lids = array_map('sanitize_int', $lids);

        $fn = "limesurvey_labelset_" . implode('_', $lids) . ".lsl";
        $xml = getXMLWriter();

        $this->_addHeaders($fn, "application/force-download", "Mon, 26 Jul 1997 05:00:00 GMT", "cache");

        $xml->openURI('php://output');

        $xml->setIndent(TRUE);
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('document');
        $xml->writeElement('LimeSurveyDocType', 'Label set');
        $xml->writeElement('DBVersion', getGlobalSetting("DBVersion"));

        // Label sets table
        $lsquery = "SELECT * FROM {{labelsets}} WHERE lid=" . implode(' or lid=', $lids);
        buildXMLFromQuery($xml, $lsquery, 'labelsets');

        // Labels
        $lquery = "SELECT lid, code, title, sortorder, language, assessment_value FROM {{labels}} WHERE lid=" . implode(' or lid=', $lids);
        buildXMLFromQuery($xml, $lquery, 'labels');
        $xml->endElement(); // close columns
        $xml->endDocument();
        exit;
    }

    /**
    * Exports a archive (ZIP) of the current survey (structure, responses, timings, tokens)
    *
    * @param integer $iSurveyID  The ID of the survey to export
    * @param boolean $bSendToBrowser If TRUE (default) then the ZIP file is sent to the browser
    * @return string Full path of the ZIP filename if $bSendToBrowser is set to TRUE, otherwise no return value
    */
    private function _exportarchive($iSurveyID, $bSendToBrowser=TRUE)
    {
        $aSurveyInfo = getSurveyInfo($iSurveyID);

        $sTempDir = Yii::app()->getConfig("tempdir");

        $aZIPFileName = $sTempDir . DIRECTORY_SEPARATOR . randomChars(30);
        $sLSSFileName = $sTempDir . DIRECTORY_SEPARATOR . randomChars(30);
        $sLSRFileName = $sTempDir . DIRECTORY_SEPARATOR . randomChars(30);
        $sLSTFileName = $sTempDir . DIRECTORY_SEPARATOR . randomChars(30);
        $sLSIFileName = $sTempDir . DIRECTORY_SEPARATOR . randomChars(30);

        Yii::import('application.libraries.admin.pclzip', TRUE);
        $zip = new PclZip($aZIPFileName);

        file_put_contents($sLSSFileName, surveyGetXMLData($iSurveyID));

        $this->_addToZip($zip, $sLSSFileName, 'survey_' . $iSurveyID . '.lss');

        unlink($sLSSFileName);

        if ( $aSurveyInfo['active'] == 'Y' )
        {
            getXMLDataSingleTable($iSurveyID, 'survey_' . $iSurveyID, 'Responses', 'responses', $sLSRFileName, FALSE);
            $this->_addToZip($zip, $sLSRFileName, 'survey_' . $iSurveyID . '_responses.lsr');
            unlink($sLSRFileName);
        }

        if ( tableExists('{{tokens_' . $iSurveyID . '}}') )
        {
            getXMLDataSingleTable($iSurveyID, 'tokens_' . $iSurveyID, 'Tokens', 'tokens', $sLSTFileName);
            $this->_addToZip($zip, $sLSTFileName, 'survey_' . $iSurveyID . '_tokens.lst');
            unlink($sLSTFileName);
        }

        if ( tableExists('{{survey_' . $iSurveyID . '_timings}}') )
        {
            getXMLDataSingleTable($iSurveyID, 'survey_' . $iSurveyID . '_timings', 'Timings', 'timings', $sLSIFileName);
            $this->_addToZip($zip, $sLSIFileName, 'survey_' . $iSurveyID . '_timings.lsi');
            unlink($sLSIFileName);
        }

        if ( is_file($aZIPFileName) )
        {
            if ( $bSendToBrowser )
            {
                $fn = "survey_archive_{$iSurveyID}.lsa";

                //Send the file for download!
                $this->_addHeaders($fn, "application/force-download", 0);

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

    private function _addToZip($zip, $name, $full_name)
    {
        $zip->add(
        array(
        array(
        PCLZIP_ATT_FILE_NAME => $name,
        PCLZIP_ATT_FILE_NEW_FULL_NAME => $full_name
        )
        )
        );
    }

    private function _surveyexport($action, $iSurveyID)
    {
        viewHelper::disableHtmlLogging();
        if ( $action == "exportstructurexml" )
        {
            $fn = "limesurvey_survey_{$iSurveyID}.lss";

            $this->_addHeaders($fn, "text/xml", "Mon, 26 Jul 1997 05:00:00 GMT");

            echo surveyGetXMLData($iSurveyID);
            exit;
        }
        elseif ($action == "exportstructurejson")
        {
            $fn = "limesurvey_survey_{$iSurveyID}.json";
            $this->_addHeaders($fn, "application/json", "Mon, 26 Jul 1997 05:00:00 GMT");
            $surveyInXmlFormat = surveyGetXMLData($iSurveyID);
            // now convert this xml into json format and then return
            echo _xmlToJson($surveyInXmlFormat);
            exit;
        }

        elseif ( $action == "exportstructurequexml" )
        {
            if ( isset($surveyprintlang) && ! empty($surveyprintlang) )
            {
                $quexmllang = $surveyprintlang;
            }
            else
            {
                $quexmllang=Survey::model()->findByPk($iSurveyID)->language;
            }

            if ( ! (isset($noheader) && $noheader == TRUE) )
            {
                $fn = "survey_{$iSurveyID}_{$quexmllang}.xml";

                $this->_addHeaders($fn, "text/xml", "Mon, 26 Jul 1997 05:00:00 GMT");

                echo quexml_export($iSurveyID, $quexmllang);
                exit;
            }
        }
        elseif ($action == 'exportstructuretsv')
        {
            $this->_exporttsv($iSurveyID);
        }
        elseif ( $action == "exportarchive" )
        {
            $this->_exportarchive($iSurveyID);
        }
    }

    /**
     * Generate an TSV (tab-separated value) file for the survey structure
     * @param type $surveyid
     */
    private function _exporttsv($surveyid)
    {
        $fn = "limesurvey_survey_$surveyid.txt";
        header("Content-Type: text/tab-separated-values charset=UTF-8");
        header("Content-Disposition: attachment; filename=$fn");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: public");                          // HTTP/1.0

        $data =& LimeExpressionManager::TSVSurveyExport($surveyid);

        $lines = array();
        foreach($data as $row)
        {
            $lines[] = implode("\t",str_replace(array("\t","\n","\r"),array(" "," "," "),$row));
        }
        $output = implode("\n",$lines);
//        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo $output;
        return;
    }

    private function _addHeaders($filename, $content_type, $expires, $pragma = "public")
    {
        header("Content-Type: {$content_type}; charset=UTF-8");
        header("Content-Disposition: attachment; filename={$filename}");
        header("Expires: {$expires}");    // Date in the past
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: {$pragma}");                          // HTTP/1.0
    }

    private function _xmlToJson($fileContents) {
        $fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);
        $fileContents = trim(str_replace('"', "'", $fileContents));
        $simpleXml = simplexml_load_string($fileContents,'SimpleXMLElement', LIBXML_NOCDATA);
        $json = json_encode($simpleXml);
        return $json;
    }

    /**
    * Renders template(s) wrapped in header and footer
    *
    * @param string $sAction Current action, the folder to fetch views from
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction = 'export', $aViewUrls = array(), $aData = array())
    {
        App()->getClientScript()->registerPackage('jquery-superfish');

        $aData['display']['menu_bars']['gid_action'] = 'exportstructureGroup';

        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }
}
