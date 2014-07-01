<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * LimeSurvey
 * Copyright (C) 2007-2014 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

class UploaderController extends SurveyController {
    function run($actionID)
    {
        if(isset($_SESSION['LEMsid']) && $oSurvey=Survey::model()->findByPk($_SESSION['LEMsid'])){
            $surveyid= $_SESSION['LEMsid'];
        }else{
            throw new CHttpException(400);// See for debug > 1
        }
        if (isset($_SESSION['survey_'.$surveyid]['s_lang']))
        {
            $sLanguage = $_SESSION['survey_'.$surveyid]['s_lang'];
        }
        else
        {
            $sLanguage='';
        }

        $clang = SetSurveyLanguage( $surveyid, $sLanguage);
        $uploaddir = Yii::app()->getConfig("uploaddir");
        $tempdir = Yii::app()->getConfig("tempdir");

        Yii::app()->loadHelper("database");

        // Fill needed var
        $sFileGetContent=Yii::app()->request->getParam('filegetcontents','');// The file to view fu_ or fu_tmp
        $bDelete=Yii::app()->request->getParam('delete');
        $sFieldName = Yii::app()->request->getParam('fieldname');
        $sFileName = Yii::app()->request->getParam('filename','');// The file to delete fu_ or fu_tmp
        $sOriginalFileName = Yii::app()->request->getParam('name','');// Used for javascript return only
        $sMode = Yii::app()->request->getParam('mode');
        $sPreview=Yii::app()->request->getParam('preview',0);

        // Validate and filter and throw error if problems
        // Using 'futmp_'.randomChars(15).'_'.$pathinfo['extension'] for filename, then remove all other characters
        $sFileGetContentFiltered=preg_replace('/[^a-z0-9_]/', '', $sFileGetContent);
        $sFileNameFiltered = preg_replace('/[^a-z0-9_]/', '',$sFileName);
        $sFieldNameFiltered=preg_replace('/[^X0-9]/', '', $sFieldName);
        if($sFileGetContent!=$sFileGetContentFiltered || $sFileName!=$sFileNameFiltered || $sFieldName!=$sFieldNameFiltered)
        {// If one seems to be a hack: Bad request
            throw new CHttpException(400);// See for debug > 1
        }
        if ($sFileGetContent)
        {
            if (substr($sFileGetContent,0,6)=='futmp_')
            {
                $sFileDir = $tempdir.'/upload/';
            }
            elseif(substr($sFileGetContent,0,3)=='fu_')
            {
                // Need to validate $_SESSION['srid'], and this file is from this srid !
                $sFileDir = "{$uploaddir}/surveys/{$surveyid}/files/";
            }
            else
            {
                throw new CHttpException(400);// See for debug > 1
            }
            if(is_file($sFileDir.$sFileGetContent))// Validate file before else 500 error by getMimeType 
            {
                header('Content-Type: '. CFileHelper::getMimeType($sFileDir.$sFileGetContent));
                readfile($sFileDir.$sFileGetContent);
                Yii::app()->end();
            }
            else
            {
                Yii::app()->end();
            }
        }
        elseif ($bDelete) {
            if (substr($sFileName,0,6)=='futmp_')
            {
                $sFileDir = $tempdir.'/upload/';
            }
            elseif(substr($sFileName,0,3)=='fu_')
            {
                // Need to validate $_SESSION['srid'], and this file is from this srid !
                $sFileDir = "{$uploaddir}/surveys/{$surveyid}/files/";
            }
            else
            {
                throw new CHttpException(400);// See for debug > 1
            }
            if(isset($_SESSION[$sFieldName])) {// We already have $sFieldName ?
                $sJSON = $_SESSION[$sFieldName];
                $aFiles = json_decode(stripslashes($sJSON),true);

                if(substr($sFileName,0,3)=='fu_'){
                    $iFileIndex=0;
                    $found=false;
                    foreach ($aFiles as $aFile)
                    {
                       if ($aFile['filename']==$sFileName)
                       {
                        $found=true;
                        break;
                       }
                       $iFileIndex++;
                    }
                    if ($found==true) unset($aFiles[$iFileIndex]);
                   $_SESSION[$sFieldName] = ls_json_encode($aFiles);
                }
            }
            //var_dump($sFileDir.$sFilename);
            // Return some json to do a beautiful text
            if (@unlink($sFileDir.$sFileName))
            {
               echo sprintf($clang->gT('File %s deleted'), $sOriginalFileName);
            }
            else
                echo $clang->gT('Oops, There was an error deleting the file');
            Yii::app()->end();
        }


        if($sMode == "upload")
        {
            $clang = Yii::app()->lang;

            $sTempUploadDir = $tempdir.'/upload/';
            // Check if exists and is writable
            if (!file_exists($sTempUploadDir)) {
                // Try to create
                mkdir($sTempUploadDir);
            }
            $filename = $_FILES['uploadfile']['name'];
            // Do we filter file name ? It's used on displaying only , but not save like that.
            //$filename = sanitize_filename($_FILES['uploadfile']['name']);// This remove all non alpha numeric characters and replaced by _ . Leave only one dot .
            $size = 0.001 * $_FILES['uploadfile']['size'];
            $preview = Yii::app()->session['preview'];
            $aFieldMap = createFieldMap($surveyid,'short',false,false,$sLanguage);
            if (!isset($aFieldMap[$sFieldName]))
            {
                throw new CHttpException(400);// See for debug > 1
            }
            $aAttributes=getQuestionAttributeValues($aFieldMap[$sFieldName]['qid'],$aFieldMap[$sFieldName]['type']);

            $maxfilesize = (int) $aAttributes['max_filesize'];
            $valid_extensions_array = explode(",", $aAttributes['allowed_filetypes']);
            $valid_extensions_array = array_map('trim',$valid_extensions_array);

            $pathinfo = pathinfo($_FILES['uploadfile']['name']);
            $ext = $pathinfo['extension'];
            $randfilename = 'futmp_'.randomChars(15).'_'.$pathinfo['extension'];
            $randfileloc = $sTempUploadDir . $randfilename;

            // check to see that this file type is allowed
            // it is also  checked at the client side, but jst double checking
            if (!in_array(strtolower($ext), $valid_extensions_array))
            {
                $return = array(
                                "success" => false,
                                "msg" => sprintf($clang->gT("Sorry, this file extension (%s) is not allowed!"),$ext)
                            );
                //header('Content-Type: application/json');
                echo ls_json_encode($return);
                Yii::app()->end();
            }

            // If this is just a preview, don't save the file
            if ($preview)
            {
                if ($size > $maxfilesize)
                {
                    $return = array(
                        "success" => false,
                        "msg" => sprintf($clang->gT("Sorry, this file is too large. Only files upto %s KB are allowed."), $maxfilesize)
                    );
                    //header('Content-Type: application/json');
                    echo ls_json_encode($return);
                    Yii::app()->end();
                }

                else if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $randfileloc))
                {

                    $return = array(
                                "success"       => true,
                                "file_index"    => $filecount,
                                "size"          => $size,
                                "name"          => rawurlencode(basename($filename)),
                                "ext"           => $ext,
                                "filename"      => $randfilename,
                                "msg"           => $clang->gT("The file has been successfuly uploaded.")
                            );
                    // TODO : unlink this file since this is just a preview. But we can do it only if it's not needed, and still needed to have the file content
                    // Maybe use a javascript 'onunload' on preview question/group
                    // unlink($randfileloc)
                    //header('Content-Type: application/json');
                    echo ls_json_encode($return);;
                    Yii::app()->end();
                }
            }
            else
            {    // if everything went fine and the file was uploaded successfuly,
                 // send the file related info back to the client
                 $iFileUploadTotalSpaceMB = Yii::app()->getConfig("iFileUploadTotalSpaceMB");
                if ($size > $maxfilesize)
                {
                    $return = array(
                        "success" => false,
                         "msg" => sprintf($clang->gT("Sorry, this file is too large. Only files up to %s KB are allowed.",'unescaped'), $maxfilesize)
                    );
                    //header('Content-Type: application/json');
                    echo ls_json_encode($return);
                    Yii::app()->end();
                }
                elseif ($iFileUploadTotalSpaceMB>0 && ((calculateTotalFileUploadUsage()+($size/1024/1024))>$iFileUploadTotalSpaceMB))
                {
                    $return = array(
                        "success" => false,
                         "msg" => $clang->gT("We are sorry but there was a system error and your file was not saved. An email has been dispatched to notify the survey administrator.",'unescaped')
                    );
                    //header('Content-Type: application/json');
                    echo ls_json_encode($return);
                    Yii::app()->end();
                }
                elseif (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $randfileloc))
                {
                    $return = array(
                        "success" => true,
                        "size"    => $size,
                        "name"    => rawurlencode(basename($filename)),
                        "ext"     => $ext,
                        "filename"      => $randfilename,
                        "msg"     => $clang->gT("The file has been successfuly uploaded.")
                    );
                    //header('Content-Type: application/json');
                    echo ls_json_encode($return);
                    Yii::app()->end();
                }
                // if there was some error, report error message
                else
                {
                    // check for upload error
                    if ($_FILES['uploadfile']['error'] > 2)
                    {
                        $return = array(
                                        "success" => false,
                                        "msg" => $clang->gT("Sorry, there was an error uploading your file")
                                    );
                    //header('Content-Type: application/json');
                    echo ls_json_encode($return);
                    Yii::app()->end();
                    }
                    // check to ensure that the file does not cross the maximum file size
                    else if ( $_FILES['uploadfile']['error'] == 1 ||  $_FILES['uploadfile']['error'] == 2 || $size > $maxfilesize)
                    {
                        $return = array(
                                        "success" => false,
                                        "msg" => sprintf($clang->gT("Sorry, this file is too large. Only files upto %s KB are allowed."), $maxfilesize)
                                    );
                        //header('Content-Type: application/json');
                        echo ls_json_encode($return);
                        Yii::app()->end();
                    }
                    else
                    {
                        $return = array(
                                    "success" => false,
                                    "msg" => $clang->gT("Unknown error")
                                );
                        //header('Content-Type: application/json');
                        echo ls_json_encode($return);
                        Yii::app()->end();
                    }
                }
            }
        return;
        }
        $clang = Yii::app()->lang;
        $meta = '';
        App()->getClientScript()->registerPackage('jqueryui');
        App()->getClientScript()->registerPackage('jquery-superfish');
        $sNeededScriptVar='
            var uploadurl = "'.$this->createUrl('/uploader/index/mode/upload/').'";
            var imageurl = "'.Yii::app()->getConfig('imageurl').'/";
            var surveyid = "'.$surveyid.'";
            var fieldname = "'.$sFieldName.'";
            var questgrppreview  = '.$sPreview.';
            csrfToken = '.ls_json_encode(Yii::app()->request->csrfToken).';
            showpopups="'.Yii::app()->getConfig("showpopups").'";
        ';
        $sLangScriptVar="
                translt = {
                     titleFld: '" . $clang->gT('Title','js') . "',
                     commentFld: '" . $clang->gT('Comment','js') . "',
                     errorNoMoreFiles: '" . $clang->gT('Sorry, no more files can be uploaded!','js') . "',
                     errorOnlyAllowed: '" . $clang->gT('Sorry, only %s files can be uploaded for this question!','js') . "',
                     uploading: '" . $clang->gT('Uploading','js') . "',
                     selectfile: '" . $clang->gT('Select file','js') . "',
                     errorNeedMore: '" . $clang->gT('Please upload %s more file(s).','js') . "',
                     errorMoreAllowed: '" . $clang->gT('If you wish, you may upload %s more file(s); else you may return back to survey.','js') . "',
                     errorMaxReached: '" . $clang->gT('The maximum number of files has been uploaded. You may return back to survey.','js') . "',
                     errorTooMuch: '" . $clang->gT('The maximum number of files has been uploaded. You may return back to survey.','js') . "',
                     errorNeedMoreConfirm: '" . $clang->gT("You need to upload %s more files for this question.\nAre you sure you want to exit?",'js') . "'
                    };
        ";
        $aSurveyInfo=getSurveyInfo($surveyid, $sLanguage);
        $oEvent = new PluginEvent('beforeSurveyPage');
        $oEvent->set('surveyId', $surveyid);
        App()->getPluginManager()->dispatchEvent($oEvent);
        if (!is_null($oEvent->get('template')))
        {
            $aSurveyInfo['templatedir'] = $event->get('template');
        }
        $sTemplateDir = getTemplatePath($aSurveyInfo['template']);
        $sTemplateUrl = getTemplateURL($aSurveyInfo['template'])."/";
        App()->clientScript->registerScript('sNeededScriptVar',$sNeededScriptVar,CClientScript::POS_HEAD);
        App()->clientScript->registerScript('sLangScriptVar',$sLangScriptVar,CClientScript::POS_HEAD);
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig("generalscripts").'ajaxupload.js');
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig("generalscripts").'uploader.js');
        App()->getClientScript()->registerScriptFile("{$sTemplateUrl}template.js");
        App()->clientScript->registerCssFile(Yii::app()->getConfig("publicstyleurl")."uploader.css");
        if (file_exists($sTemplateDir .DIRECTORY_SEPARATOR.'jquery-ui-custom.css'))
        {
            Yii::app()->getClientScript()->registerCssFile("{$sTemplateUrl}jquery-ui-custom.css");
        }
        elseif(file_exists($sTemplateDir.DIRECTORY_SEPARATOR.'jquery-ui.css'))
        {
            Yii::app()->getClientScript()->registerCssFile("{$sTemplateUrl}jquery-ui.css");
        }
        else
        {
            Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl')."jquery-ui.css");
        }
        App()->clientScript->registerCssFile("{$sTemplateUrl}template.css");
        $header = getHeader($meta);

        echo $header;

        $fn = $sFieldName;
        $qid = (int)Yii::app()->request->getParam('qid');
        $minfiles = (int)Yii::app()->request->getParam('minfiles');
        $maxfiles = (int)Yii::app()->request->getParam('maxfiles');
        $qidattributes=getQuestionAttributeValues($qid);
        $qidattributes['max_filesize']=floor(min($qidattributes['max_filesize']*1024,getMaximumFileUploadSize())/1024);
        $body = '</head><body>
                <div id="notice"></div>
                <input type="hidden" id="ia"                value="'.$fn.'" />
                <input type="hidden" id="'.$fn.'_minfiles"          value="'.$minfiles.'" />
                <input type="hidden" id="'.$fn.'_maxfiles"          value="'.$maxfiles.'" />
                <input type="hidden" id="'.$fn.'_maxfilesize"       value="'.$qidattributes['max_filesize'].'" />
                <input type="hidden" id="'.$fn.'_allowed_filetypes" value="'.$qidattributes['allowed_filetypes'].'" />
                <input type="hidden" id="preview"                   value="'.Yii::app()->session['preview'].'" />
                <input type="hidden" id="'.$fn.'_show_comment"      value="'.$qidattributes['show_comment'].'" />
                <input type="hidden" id="'.$fn.'_show_title"        value="'.$qidattributes['show_title'].'" />
                <input type="hidden" id="'.$fn.'_licount"           value="0" />
                <input type="hidden" id="'.$fn.'_filecount"         value="0" />

                <!-- The upload button -->
                <div class="upload-div">
                    <button id="button1" class="button upload-button" type="button" >'.$clang->gT("Select file").'</button>
                </div>

                <p class="uploadmsg">'.sprintf($clang->gT("You can upload %s under %s KB each."),$qidattributes['allowed_filetypes'],$qidattributes['max_filesize']).'</p>
                <div class="uploadstatus" id="uploadstatus"></div>

                <!-- The list of uploaded files -->

            </body>
        </html>';
        App()->getClientScript()->render($body);
        echo $body;


    }

}
