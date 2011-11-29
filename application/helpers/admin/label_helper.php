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
    $clang = Yii::app()->lang;

    // Get added and deleted languagesid arrays

    if (!empty($_POST['languageids']))
    {
        $postlanguageids=sanitize_languagecodeS($_POST['languageids']);
    }

    if (!empty($_POST['label_name']))
    {
        $postlabel_name=sanitize_labelname($_POST['label_name']);
    }

    $newlanidarray=explode(" ",trim($postlanguageids));

    //$postlanguageids = db_quoteall($postlanguageids,true);
    //$postlabel_name = db_quoteall($postlabel_name,true);
    $oldlangidsarray=array();
    $query = "SELECT languages FROM {{labelsets}} WHERE lid=".$lid;
    $result=Yii::app()->db->createCommand($query)->query();
    if ($result)
    {
        foreach ($result->readAll() as $row) {$oldlangids=$row['languages'];}
        $oldlangidsarray=explode(" ",trim($oldlangids));
    }
    $addlangidsarray=array_diff($newlanidarray,$oldlangidsarray);
    $dellangidsarray=array_diff($oldlangidsarray,$newlanidarray);

    // If new languages are added, create labels' codes and sortorder for the new languages
    $query = "SELECT code,sortorder,assessment_value FROM {{labels}} WHERE lid=".$lid." GROUP BY code,sortorder,assessment_value";
    $result=Yii::app()->db->createCommand($query)->query();
    if ($result) { foreach ($result->readAll() as $row) {$oldcodesarray[$row['code']]=array('sortorder'=>$row['sortorder'],'assessment_value'=>$row['assessment_value']);} }
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
        foreach ($sqlvalues as $sqlline)
        {
            //$query = "INSERT INTO ".db_table_name('labels')." (lid,code,sortorder,language,assessment_value) VALUES ".($sqlline);
			$result = Yii::app()->db->createCommand()->insert('{{labels}}', $sqlline);
            if (!$result)
            {
                $labelsoutput= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to Copy already defined labels to added languages","js")." - ".$query."\")\n //-->\n</script>\n";
            }
        }
    }

    // If languages are removed, delete labels for these languages
    $sqlwherelang='';
    foreach ($dellangidsarray as $dellangid)
    {
        $sqlwherelang .= " OR language='".$dellangid."'";
    }
    if ($sqlwherelang)
    {
        $query = "DELETE FROM {{labels}} WHERE lid=$lid AND (".trim($sqlwherelang, ' OR').")";
        $result=Yii::app()->db->createCommand($query)->execute();
    }

    // Update the label set itself
    $query = "UPDATE {{labelsets}} SET label_name='{$postlabel_name}', languages='{$postlanguageids}' WHERE lid=$lid";
	$result = Yii::app()->db->createCommand($query)->execute();

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

    $query = "DELETE FROM {{labels}} WHERE lid=$lid";
    $result = Yii::app()->db->createCommand($query)->execute();
    $query = "DELETE FROM {{labelsets}} WHERE lid=$lid";
    $result = Yii::app()->db->createCommand($query)->execute();
    return true;
}



function insertlabelset()
{
    //global $labelsoutput;
    //	$labelsoutput.= $_POST['languageids'];  For debug purposes
    $clang = Yii::app()->lang;


    if (!empty($_POST['languageids']))
    {
        $postlanguageids=sanitize_languagecodeS($_POST['languageids']);
    }

    if (!empty($_POST['label_name']))
    {
        $postlabel_name=sanitize_labelname($_POST['label_name']);
    }

    //postlabel_name = db_quoteall($postlabel_name,true);
    //$postlanguageids = db_quoteall($postlanguageids,true);
    $data = array(
    	'label_name' => $postlabel_name,
  	  'languages' => $postlanguageids
    );

    //$query = "INSERT INTO ".db_table_name('labelsets')." (label_name,languages) VALUES ({$postlabel_name},{$postlanguageids})";
    if (!$result = Yii::app()->db->createCommand()->insert('{{labelsets}}', $data))
    {
        safe_die("Inserting the label set failed:<br />".$query."<br />");
    }
    else
    {
        return Yii::app()->db->getLastInsertID(); //$connect->Insert_ID(db_table_name_nq('labelsets'),"lid");
    }

}

function modlabelsetanswers($lid)
{

    //global  $labelsoutput;

    $clang = Yii::app()->lang;

    $ajax = false;

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

        $query = "DELETE FROM {{labels}}  WHERE `lid` = '$lid'";

        $result = Yii::app()->db->createCommand($query)->execute(); // or safe_die($connect->ErrorMsg());

        foreach($data->{'codelist'} as $index=>$codeid){

            $codeObj = $data->$codeid;


            $actualcode = $codeObj->{'code'};
            //$codeid = db_quoteall($codeid,true);

            $assessmentvalue = (int)($codeObj->{'assessmentvalue'});
            foreach($data->{'langs'} as $lang){

                $strTemp = 'text_'.$lang;
                $title = $codeObj->$strTemp;

				$p = new CHtmlPurifier();

                if (Yii::app()->getConfig('filterxsshtml'))
                    $title = $p->purify($title);
                else
                    $title = html_entity_decode($title, ENT_QUOTES, "UTF-8");


                // Fix bug with FCKEditor saving strange BR types
                $title = fix_FCKeditor_text($title);
                $sort_order = $index;

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

            	$result = Yii::app()->db->createCommand()->insert('{{labels}}', $insertdata);
            }
        }


        Yii::app()->session['flashmessage'] = $clang->gT("Labels sucessfully updated");

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

    $clang = Yii::app()->lang;

    $qulabelset = "SELECT * FROM {{labelsets}} WHERE lid=$lid";
    $rslabelset = Yii::app()->db->createCommand($qulabelset)->query();  //or safe_die($connect->ErrorMsg());
    $rwlabelset=$rslabelset->read();
    $lslanguages=explode(" ", trim($rwlabelset['languages']));
    foreach ($lslanguages as $lslanguage)
    {
        $query = "SELECT lid, code, title, sortorder FROM {{labels}} WHERE lid=:lid and language=:lang ORDER BY sortorder, code";
        $result = Yii::app()->createCommand($query)->query(array(':lid' => $lid, ':lang' => $lslanguage)); // or safe_die("Can't read labels table: $query // (lid=$lid, language=$lslanguage) ".$connect->ErrorMsg());
        $position=0;
        foreach ($result->readAll() as $row)
        {
            $position=sprintf("%05d", $position);
            $query2="UPDATE {{labels}} SET sortorder='$position' WHERE lid=".$row['lid']." AND code=".$row['code']." AND title=".$row['title']." AND language='$lslanguage' ";
            $result2=Yii::app()->db->createCommand($query2)->execute();
            $position++;
        }
    }
}