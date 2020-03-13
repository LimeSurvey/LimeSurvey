<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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

//include_once("login_check.php");
//Security Checked: POST/GET/SESSION/DB/returnGlobal

/**
 * @param integer $lid
 */
function updateset($lid)
{


    // Get added and deleted languagesid arrays
    $newlanidarray = Yii::app()->getRequest()->getPost('languageids');
    $postlabel_name = Yii::app()->getRequest()->getPost('label_name');

    $labelset = LabelSet::model()->findByAttributes(array('lid' => $lid));
    $oldlangidsarray = explode(' ', $labelset->languages);

    $addlangidsarray = array_diff($newlanidarray, $oldlangidsarray);
    $dellangidsarray = array_diff($oldlangidsarray, $newlanidarray);

    // If new languages are added, create labels' codes and sortorder for the new languages
    $result = Label::model()->findAllByAttributes(array('lid' => $lid), array('order' => 'code, sortorder, assessment_value'));
    if ($result) {
            foreach ($result as $row) {
                        $oldcodesarray[$row['code']] = array('sortorder'=> $row['sortorder'], 'assessment_value'=> $row['assessment_value']);
            }
    }

    if (isset($oldcodesarray) && count($oldcodesarray) > 0) {
            foreach ($addlangidsarray as $addedlangid) {
                        foreach ($oldcodesarray as $oldcode => $olddata) {
                                        $sqlvalues[] = array('lid' => $lid, 'code' => $oldcode, 'sortorder' => $olddata['sortorder'], 'language' => $addedlangid, 'assessment_value' => $olddata['assessment_value']);
                        }
            }
    }

    if (isset($sqlvalues)) {
        foreach ($sqlvalues as $sqlvalue) {
            $label = new Label();
            foreach ($sqlvalue as $name => $value) {
                $label->setAttribute($name, $value);
            }
            $label->save();
        }
    }

    // If languages are removed, delete labels for these languages
    $criteria = new CDbCriteria;
    $criteria->addColumnCondition(array('lid' => $lid));
    $langcriteria = new CDbCriteria();
    foreach ($dellangidsarray as $dellangid) {
            $langcriteria->addColumnCondition(array('language' => $dellangid), 'OR');
    }
    $criteria->mergeWith($langcriteria);

    if (!empty($dellangidsarray)) {
            Label::model()->deleteAll($criteria);
    }

    // Update the label set itself
    $labelset->label_name = $postlabel_name;
    $labelset->languages = implode(' ', $newlanidarray);
    $labelset->save();
}

/**
* Deletes a label set alog with its labels
* @deprecated use LabelSet::delete()
* @param mixed $lid Label ID
* @return boolean Returns always true
*/
function deletelabelset($lid)
{
    Yii::app()->db->createCommand()->delete(Label::model()->tableName(), array('in', 'lid', $lid));
    Yii::app()->db->createCommand()->delete(LabelSet::model()->tableName(), array('in', 'lid', $lid));
    rmdirr(Yii::app()->getConfig('uploaddir').'/labels/'.$lid);
    return true;
}



function insertlabelset()
{
    $postlabel_name = flattenText(Yii::app()->getRequest()->getPost('label_name'), false, true, 'UTF-8', true);

    $data = array(
        'label_name' => $postlabel_name,
        'languages' => sanitize_languagecodeS(implode(' ', Yii::app()->getRequest()->getPost('languageids', array('en'))))
    );
    $result = LabelSet::model()->insertRecords($data);
    if (!$result) {
        Yii::app()->session['flashmessage'] = gT("Inserting the label set failed.");
    } else {
        return $result;
    }
}

/**
 * @param null|integer $lid
 */
function modlabelsetanswers($lid)
{

    //global  $labelsoutput;



    $ajax = false;

    if (isset($_POST['ajax']) && $_POST['ajax'] == "1") {
        $ajax = true;
    }
    if (!isset($_POST['method'])) {
        $_POST['method'] = gT("Save");
    }

    $sPostData = Yii::app()->getRequest()->getPost('dataToSend');
    $sPostData = str_replace("\t", '', $sPostData);
    $data = json_decode($sPostData);

    if ($ajax) {
            $lid = insertlabelset();
    }
    $aErrors = array();
    if (count(array_unique($data->{'codelist'})) == count($data->{'codelist'})) {

        $query = "DELETE FROM {{labels}} WHERE lid = '$lid'";

        Yii::app()->db->createCommand($query)->execute();

        foreach ($data->{'codelist'} as $index=>$codeid) {

            $codeObj = $data->$codeid;


            $actualcode = $codeObj->{'code'};
            //$codeid = App()->db->quoteValue($codeid,true);

            $assessmentvalue = (int) ($codeObj->{'assessmentvalue'});
            foreach ($data->{'langs'} as $lang) {

                $strTemp = 'text_'.$lang;
                $title = $codeObj->$strTemp;
                $sortorder = $index;

                $oLabel = new Label();
                $oLabel->lid = $lid;
                $oLabel->code = $actualcode;
                $oLabel->title = $title;
                $oLabel->sortorder = $sortorder;
                $oLabel->assessment_value = $assessmentvalue;
                $oLabel->language = $lang;
                if ($oLabel->validate()) {
                    $oLabel->save();
                } else {
                    $aErrors[] = $oLabel->getErrors();
                }
            }
        }
        if (count($aErrors)) {
            Yii::app()->session['flashmessage'] = gT("Not all labels were updated successfully.");
        } else {
            Yii::app()->session['flashmessage'] = gT("Labels sucessfully updated");
        }
    } else {
        Yii::app()->setFlashMessage(gT("Can't update labels because you are using duplicated codes"), 'error');
    }

    if ($ajax) { die(); }
}

/**
* Function rewrites the sortorder for a label set
*
* @param mixed $lid Label set ID
*/
function fixorder($lid)
{



    $qulabelset = "SELECT * FROM {{labelsets}} WHERE lid=$lid";
    $rslabelset = Yii::app()->db->createCommand($qulabelset)->query();
    $rwlabelset = $rslabelset->read();
    $lslanguages = explode(" ", trim($rwlabelset['languages']));
    foreach ($lslanguages as $lslanguage) {
        $query = "SELECT lid, code, title, sortorder FROM {{labels}} WHERE lid=:lid and language=:lang ORDER BY sortorder, code";
        $result = Yii::app()->createCommand($query)->query(array(':lid' => $lid, ':lang' => $lslanguage)); // or safeDie("Can't read labels table: $query // (lid=$lid, language=$lslanguage) "
        $position = 0;
        foreach ($result->readAll() as $row) {
            $position = sprintf("%05d", $position);
            $query2 = "UPDATE {{labels}} SET sortorder='$position' WHERE lid=".$row['lid']." AND code=".$row['code']." AND title=".$row['title']." AND language='$lslanguage' ";
            Yii::app()->db->createCommand($query2)->execute();
            $position++;
        }
    }
}
