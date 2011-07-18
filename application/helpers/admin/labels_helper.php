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
 * $Id: htmleditor-functions.php 10193 2011-06-05 12:20:37Z c_schmitz $
 */

//include_once("login_check.php");
//Security Checked: POST/GET/SESSION/DB/returnglobal

function updateset($lid)
{
    //global $labelsoutput;
    
    $CI =& get_instance();
    $CI->load->helper('database');
    $clang = $CI->limesurvey_lang;
    
    // Get added and deleted languagesid arrays

    if ($CI->input->post('languageids'))
    {
        $postlanguageids=sanitize_languagecodeS($CI->input->post('languageids'));
    }

    if ($CI->input->post('label_name'))
    {
        $postlabel_name=sanitize_labelname($CI->input->post('label_name'));
    }

    $newlanidarray=explode(" ",trim($postlanguageids));

    //$postlanguageids = db_quoteall($postlanguageids,true);
    //$postlabel_name = db_quoteall($postlabel_name,true);
    $oldlangidsarray=array();
    $query = "SELECT languages FROM ".$CI->db->dbprefix."labelsets WHERE lid=".$lid;
    $result=db_execute_assoc($query);
    if ($result)
    {
        foreach ($result->result_array() as $row) {$oldlangids=$row['languages'];}
        $oldlangidsarray=explode(" ",trim($oldlangids));
    }
    $addlangidsarray=array_diff($newlanidarray,$oldlangidsarray);
    $dellangidsarray=array_diff($oldlangidsarray,$newlanidarray);

    // If new languages are added, create labels' codes and sortorder for the new languages
    $query = "SELECT code,sortorder,assessment_value FROM ".$CI->db->dbprefix."labels WHERE lid=".$lid." GROUP BY code,sortorder,assessment_value";
    $result=db_execute_assoc($query);
    if ($result) { foreach ($result->result_array() as $row) {$oldcodesarray[$row['code']]=array('sortorder'=>$row['sortorder'],'assessment_value'=>$row['assessment_value']);} }
    if (isset($oldcodesarray) && count($oldcodesarray) > 0 )
    {
        foreach ($addlangidsarray as $addedlangid)
        {
            foreach ($oldcodesarray as $oldcode => $olddata)
            {
                $sqlvalues[]= array('lid' => $lid, 'code' => $oldcode, 'sortorder' => $olddata['sortorder'], 'language' => $addedlangid, 'assessment_value' => $olddata['assessment_value']);
            }
        }
    }
    if (isset($sqlvalues))
    {
        db_switchIDInsert('labels',true);
        $CI->load->model('labels_model');
        foreach ($sqlvalues as $sqlline)
        {
            //$query = "INSERT INTO ".db_table_name('labels')." (lid,code,sortorder,language,assessment_value) VALUES ".($sqlline);
            
            $result=$CI->labels_model->insertRecords($sqlline); //($query);)
            if (!$result)
            {
                $labelsoutput= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to Copy already defined labels to added languages","js")." - ".$query."\")\n //-->\n</script>\n";
            }
        }
        db_switchIDInsert('labels',false);
    }

    // If languages are removed, delete labels for these languages
    $sqlwherelang='';
    foreach ($dellangidsarray as $dellangid)
    {
        $sqlwherelang .= " OR language='".$dellangid."'";
    }
    if ($sqlwherelang)
    {
        $query = "DELETE FROM ".$CI->db->dbprefix."labels WHERE lid=$lid AND (".trim($sqlwherelang, ' OR').")";
        $result=db_execute_assoc($query);
        if (!$result)
        {
            $labelsoutput= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to delete labels for removed languages","js")." - ".$query."\")\n //-->\n</script>\n";
        }
    }

    // Update the label set itself
    $query = "UPDATE ".$CI->db->dbprefix."labelsets SET label_name='{$postlabel_name}', languages='{$postlanguageids}' WHERE lid=$lid";
    if (!$result = db_execute_assoc($query))
    {
        $labelsoutput= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Update of Label Set failed","js")." - ".$query."\")\n //-->\n</script>\n";
    }
    if (isset($labelsoutput))
    {
        echo $labelsoutput;
        exit();
    }
}


/**
* Deletes a label set alog with its labels
*
* @param mixed $lid Label ID
* @return boolean Returns always true
*/
function deletelabelset($lid)
{
    //global $connect;
    
    $CI =& get_instance();
    $CI->load->helper('database');
    
    $query = "DELETE FROM ".$CI->db->dbprefix."labels WHERE lid=$lid";
    $result = db_execute_assoc($query);
    $query = "DELETE FROM ".$CI->db->dbprefix."labelsets WHERE lid=$lid";
    $result = db_execute_assoc($query);
    return true;
}



function insertlabelset()
{
    //global $labelsoutput;
    //	$labelsoutput.= $_POST['languageids'];  For debug purposes
    $CI =& get_instance();
    $CI->load->helper('database');
    $clang = $CI->limesurvey_lang;
    
    
    if ($CI->input->post('languageids'))
    {
        $postlanguageids=sanitize_languagecodeS($CI->input->post('languageids'));
    }

    if ($CI->input->post('label_name'))
    {
        $postlabel_name=sanitize_labelname($CI->input->post('label_name'));
    }

    //postlabel_name = db_quoteall($postlabel_name,true);
    //$postlanguageids = db_quoteall($postlanguageids,true);
    $data = array(
            'label_name' => $postlabel_name,
            'languages' => $postlanguageids
            );
    $CI->load->model('labelsets_model');
    //$query = "INSERT INTO ".db_table_name('labelsets')." (label_name,languages) VALUES ({$postlabel_name},{$postlanguageids})";
    if (!$result = $CI->labelsets_model->insertRecords($data))
    {
        safe_die("Inserting the label set failed:<br />".$query."<br />");
    }
    else
    {
        return $CI->db->insert_id(); //$connect->Insert_ID(db_table_name_nq('labelsets'),"lid");
    }

}

function modlabelsetanswers($lid)
{

    //global  $labelsoutput;
    
    $CI =& get_instance();
    $CI->load->helper('database');
    $clang = $CI->limesurvey_lang;
    
    $ajax = false;
    $_POST = $CI->input->post();
    if (isset($_POST['ajax']) && $_POST['ajax'] == "1"){
        $ajax = true;
    }
    if (!isset($_POST['method'])) {
        $_POST['method'] = $clang->gT("Save");
    }
   
    $data = json_decode(stripslashes($_POST['dataToSend']));
    
    if ($ajax){
        $lid = insertlabelset();
    }

    if (count(array_unique($data->{'codelist'})) == count($data->{'codelist'}))
    {
        /**if ($filterxsshtml)
        {
            require_once("../classes/inputfilter/class.inputfilter_clean.php");
            $myFilter = new InputFilter('','',1,1,1);
        } */

        $query = "DELETE FROM ".$CI->db->dbprefix."labels  WHERE `lid` = '$lid'";

        $result = db_execute_assoc($query); // or safe_die($connect->ErrorMsg());
        
        foreach($data->{'codelist'} as $index=>$codeid){
            
            $codeObj = $data->$codeid;

            
            $actualcode = $codeObj->{'code'};
            //$codeid = db_quoteall($codeid,true);

            $assessmentvalue = (int)($codeObj->{'assessmentvalue'});
            $CI->load->model('labels_model');
            foreach($data->{'langs'} as $lang){
                
                $strTemp = 'text_'.$lang;
                $title = $codeObj->$strTemp;

                /**if ($filterxsshtml)
                    $title=$myFilter->process($title);
                else
                    $title = html_entity_decode($title, ENT_QUOTES, "UTF-8");
                */

                // Fix bug with FCKEditor saving strange BR types
                $title =fix_FCKeditor_text($title);
                //$title = db_quoteall($title,true);


                $sort_order = $index;
                //$lang = db_quoteall($lang);
                
                $insertdata = array(
                        'lid' => $lid,
                        'code' => $actualcode,
                        'title' => $title,
                        'sortorder' => $sort_order,
                        'assessment_value' => $assessmentvalue,
                        'language' => $lang
                
                );
                
                //$query = "INSERT INTO ".db_table_name('labels')." (`lid`,`code`,`title`,`sortorder`, `assessment_value`, `language`)
                //    VALUES('$lid',$actualcode,$title,$sort_order,$assessmentvalue,$lang)";

                $result = $CI->labels_model->insertRecords($insertdata); //($query) or safe_die($connect->ErrorMsg());)
            }
            
            
            

        }
        

    $CI->session->set_userdata('flashmessage', $clang->gT("Labels sucessfully updated"));

    }
    else
    {
        $labelsoutput= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Can't update labels because you are using duplicated codes","js")."\")\n //-->\n</script>\n";
    }

    if ($ajax){ die(); }
    
    if (isset($labelsoutput))
    {
        echo $labelsoutput;
        exit();
    }

}

/**
* Function rewrites the sortorder for a label set
*
* @param mixed $lid Label set ID
*/
function fixorder($lid) {
    //global $labelsoutput;
    
    $CI =& get_instance();
    $CI->load->helper('database');
    $clang = $CI->limesurvey_lang;
    
    $qulabelset = "SELECT * FROM ".$CI->db->dbprefix."labelsets WHERE lid=$lid";
    $rslabelset = db_execute_assoc($qulabelset);  //or safe_die($connect->ErrorMsg());
    $rwlabelset=$rslabelset->row_array();
    $lslanguages=explode(" ", trim($rwlabelset['languages']));
    foreach ($lslanguages as $lslanguage)
    {
        $query = "SELECT lid, code, title, sortorder FROM ".$CI->db->dbprefix."labels WHERE lid=? and language=? ORDER BY sortorder, code";
        $result = db_execute_assosc($query, array($lid,$lslanguage)); // or safe_die("Can't read labels table: $query // (lid=$lid, language=$lslanguage) ".$connect->ErrorMsg());
        $position=0;
        foreach ($result->result_array() as $row)
        {
            $position=sprintf("%05d", $position);
            //$query2="UPDATE ".$CI->db->dbprefix."labels SET sortorder='$position' WHERE lid=? AND code=? AND title=? AND language='$lslanguage' ";
            $query2="UPDATE ".$CI->db->dbprefix."labels SET sortorder='$position' WHERE lid=".$row['lid']." AND code=".$row['code']." AND title=".$row['title']." AND language='$lslanguage' ";
            $result2=db_execute_assosc($query2); //$connect->Execute($query2, array ($row[0], $row[1], $row[2])) or safe_die ("Couldn't update sortorder<br />$query2<br />".$connect->ErrorMsg());
            $position++;
        }
    }
}