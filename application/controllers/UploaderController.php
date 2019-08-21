<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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

class UploaderController extends SurveyController
{
    public function run($actionID)
    {
        $surveyid = Yii::app()->session['LEMsid'];
        $oSurvey = Survey::model()->findByPk($surveyid);
        if (!$oSurvey) {
            throw new CHttpException(400);
        }

        $sLanguage = isset(Yii::app()->session['survey_'.$surveyid]['s_lang']) ? Yii::app()->session['survey_'.$surveyid]['s_lang'] : "";
        Yii::app()->setLanguage($sLanguage);
        $uploaddir = Yii::app()->getConfig("uploaddir");
        $tempdir = Yii::app()->getConfig("tempdir");
        Yii::app()->loadHelper("database");

        // Fill needed var
        $sFileGetContent = Yii::app()->request->getParam('filegetcontents', ''); // The file to view fu_ or fu_tmp
        $bDelete = Yii::app()->request->getParam('delete');
        $sFieldName = Yii::app()->request->getParam('fieldname');
        $sFileName = Yii::app()->request->getParam('filename', ''); // The file to delete fu_ or fu_tmp
        $sOriginalFileName = Yii::app()->request->getParam('name', ''); // Used for javascript return only
        $sMode = Yii::app()->request->getParam('mode');
        $sPreview = (int) Yii::app()->request->getParam('preview', 0);

        // Validate and filter and throw error if problems
        // Using 'futmp_'.randomChars(15).'_'.$pathinfo['extension'] for filename, then remove all other characters
        $sFileGetContentFiltered = preg_replace('/[^a-zA-Z0-9_]/', '', $sFileGetContent);
        $sFileNameFiltered = preg_replace('/[^a-zA-Z0-9_]/', '', $sFileName);
        $sFieldNameFiltered = preg_replace('/[^X0-9]/', '', $sFieldName);
        if ($sFileGetContent != $sFileGetContentFiltered || $sFileName != $sFileNameFiltered || $sFieldName != $sFieldNameFiltered) {
            // If one seems to be a hack: Bad request
            throw new CHttpException(400); // See for debug > 1
        }
        if ($sFileGetContent) {
            if (substr($sFileGetContent, 0, 6) == 'futmp_') {
                $sFileDir = $tempdir.'/upload/';
            } elseif (substr($sFileGetContent, 0, 3) == 'fu_') {
                // Need to validate $_SESSION['srid'], and this file is from this srid !
                $sFileDir = "{$uploaddir}/surveys/{$surveyid}/files/";
            } else {
                throw new CHttpException(400); // See for debug > 1
            }
            if (is_file($sFileDir.$sFileGetContent)) {
                // Validate file before else 500 error by getMimeType
                $mimeType = CFileHelper::getMimeType($sFileDir.$sFileGetContent, null, false);
                if (is_null($mimeType)) {
                    $mimeType = "application/octet-stream"; // Can not really get content if not image
                }
                header('Content-Type: '.$mimeType);
                readfile($sFileDir.$sFileGetContent);
                Yii::app()->end();
            } else {
                Yii::app()->end();
            }
        } elseif ($bDelete) {
            if (substr($sFileName, 0, 6) == 'futmp_') {
                $sFileDir = $tempdir.'/upload/';
            } elseif (substr($sFileName, 0, 3) == 'fu_') {
                // Need to validate $_SESSION['srid'], and this file is from this srid !
                $sFileDir = "{$uploaddir}/surveys/{$surveyid}/files/";
            } else {
                throw new CHttpException(400); // See for debug > 1
            }
            if (isset($_SESSION[$sFieldName])) {
                // We already have $sFieldName ?
                $sJSON = $_SESSION[$sFieldName];
                $aFiles = json_decode(stripslashes($sJSON), true);

                if (substr($sFileName, 0, 3) == 'fu_') {
                    $iFileIndex = 0;
                    $found = false;
                    foreach ($aFiles as $aFile) {
                        if ($aFile['filename'] == $sFileName) {
                            $found = true;
                            break;
                        }
                        $iFileIndex++;
                    }
                    if ($found == true) {
                        unset($aFiles[$iFileIndex]);
                    }
                    $_SESSION[$sFieldName] = ls_json_encode($aFiles);
                }
            }
            //var_dump($sFileDir.$sFilename);
            // Return some json to do a beautiful text
            if (@unlink($sFileDir.$sFileName)) {
                echo sprintf(gT('File %s deleted'), $sOriginalFileName);
            } else {
                echo gT('Oops, There was an error deleting the file');
            }
            Yii::app()->end();
        }


        if ($sMode == "upload") {
            $sTempUploadDir = $tempdir.'/upload/';
            // Check if exists and is writable
            if (!file_exists($sTempUploadDir)) {
                // Try to create
                mkdir($sTempUploadDir);
            }
            $filename = $_FILES['uploadfile']['name'];
            // Do we filter file name ? It's used on displaying only , but not save like that.
            //$filename = sanitize_filename($_FILES['uploadfile']['name']);// This remove all non alpha numeric characters and replaced by _ . Leave only one dot .
            $size = $_FILES['uploadfile']['size'] / 1024;
            $preview = Yii::app()->session['preview'];
            $aFieldMap = createFieldMap($oSurvey, 'short', false, false, $sLanguage);
            if (!isset($aFieldMap[$sFieldName])) {
                throw new CHttpException(400); // See for debug > 1
            }
            $aAttributes = QuestionAttribute::model()->getQuestionAttributes($aFieldMap[$sFieldName]['qid']);
            $maxfilesize = min(intval($aAttributes['max_filesize']), getMaximumFileUploadSize() / 1024);
            if($maxfilesize <= 0 ) {
                $maxfilesize = getMaximumFileUploadSize() / 1024;
            }
            /* Start to check upload error */
            if ($_FILES['uploadfile']['error'] > 2) {
                $return = array(
                    "success" => false,
                    "msg" => gT("Sorry, there was an error uploading your file.")
                );
                /* Show error code for user forcedSuperAdmin right */
                if( Permission::isForcedSuperAdmin(Permission::getUserId()) ) {
                    $return['msg'] = sprintf(gT("Sorry, there was an error uploading your file, error code : %s."),$_FILES['uploadfile']['error']);
                }
                //header('Content-Type: application/json');
                echo ls_json_encode($return);
                Yii::app()->end();
            }
            /* Upload error due file size */
            /* and check too $aAttributes['max_filesize'] */
            if ($size > $maxfilesize || $_FILES['uploadfile']['error'] == 1 || $_FILES['uploadfile']['error'] == 2 ) {
                $return = array(
                    "success" => false,
                    "msg" => sprintf(gT("Sorry, this file is too large. Only files upto %s KB are allowed."), $maxfilesize)
                );
                //header('Content-Type: application/json');
                echo ls_json_encode($return);
                Yii::app()->end();
            }

            $valid_extensions_array = explode(",", $aAttributes['allowed_filetypes']);
            $valid_extensions_array = array_map('trim', $valid_extensions_array);
            $pathinfo = pathinfo($_FILES['uploadfile']['name']);
            $ext = strtolower($pathinfo['extension']);
            $cleanExt = CHtml::encode($ext);
            $randfilename = 'futmp_'.randomChars(15).'_'.$pathinfo['extension'];
            $randfileloc = $sTempUploadDir.$randfilename;

            // check to see that this file type is allowed
            // it is also  checked at the client side, but jst double checking
            if (!in_array($ext, $valid_extensions_array)) {
                $return = array(
                                "success" => false,
                                "msg" => sprintf(gT("Sorry, this file extension (%s) is not allowed!"), $cleanExt)
                            );
                //header('Content-Type: application/json');
                echo ls_json_encode($return);
                Yii::app()->end();
            }
            /* extension checked : check mimeType */
            $extByMimeType = CFileHelper::getExtensionByMimeType($_FILES['uploadfile']['tmp_name'], null);
            $disableCheck = false;
            if(is_null($extByMimeType)) {
                /* Lack of finfo_open or mime_content_type ? But can be a not found extension too.*/
                /* Check if can find mime type of favicon.ico , without extension */
                if(CFileHelper::getMimeType(APPPATH."favicon.ico", null, false) != 'ico') { // hope we have favicon.ico for a long time
                    $disableCheck = true;
                    Yii::log("Unable to check mime type of files, check for finfo_open or mime_content_type function.",\CLogger::LEVEL_ERROR,'application.controller.uploader.upload');
                    if( YII_DEBUG || Permission::isForcedSuperAdmin(Permission::getUserId()) ) {
                        /* This is a security issue and a server issue : always show at forced super admin */
                        throw new CHttpException(500, "Unable to check mime type of files, please activate FileInfo on server.");
                    }
                }
            }
            if(!$disableCheck && empty($extByMimeType)) {
                // FileInfo is OK, but can not find the mime type of file …
                $return = array(
                    "success" => false,
                    "msg" => gT("Sorry, unable to check this file type!"),
                );
                //header('Content-Type: application/json');
                echo ls_json_encode($return);
                Yii::app()->end();
            }
            if (!$disableCheck && !in_array($extByMimeType, $valid_extensions_array)) {
                $realMimeType = CFileHelper::getMimeType($_FILES['uploadfile']['tmp_name'], null,false);
                $return = array(
                    "success" => false,
                    "msg" => sprintf(gT("Sorry, file type %s (extension : %s) is not allowed!"), $realMimeType,$extByMimeType)
                );
                //header('Content-Type: application/json');
                echo ls_json_encode($return);
                Yii::app()->end();
            }

            // if everything went fine and the file was uploaded successfully,
            // If this is just a preview, don't save the file
            if ($preview) {
                if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $randfileloc)) {
                    $return = array(
                                "success"       => true,
                                "file_index"    => $filecount,
                                "size"          => $size,
                                "name"          => rawurlencode(basename($filename)),
                                "ext"           => $cleanExt,
                                "filename"      => $randfilename,
                                "msg"           => gT("The file has been successfully uploaded.")
                            );
                    // TODO : unlink this file since this is just a preview. But we can do it only if it's not needed, and still needed to have the file content
                    // Maybe use a javascript 'onunload' on preview question/group
                    // unlink($randfileloc)
                    //header('Content-Type: application/json');
                    echo ls_json_encode($return); ;
                    Yii::app()->end();
                }
            } else {
                // send the file related info back to the client
                $iFileUploadTotalSpaceMB = Yii::app()->getConfig("iFileUploadTotalSpaceMB");
                if ($iFileUploadTotalSpaceMB > 0 && ((calculateTotalFileUploadUsage() + ($size / 1024 / 1024)) > $iFileUploadTotalSpaceMB)) {
                    $return = array(
                        "success" => false,
                            "msg" => gT("We are sorry but there was a system error and your file was not saved. An email has been dispatched to notify the survey administrator.", 'unescaped')
                    );
                    //header('Content-Type: application/json');
                    echo ls_json_encode($return);
                    Yii::app()->end();
                }
                if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $randfileloc)) {
                    $return = array(
                        "success" => true,
                        "size"    => $size,
                        "name"    => rawurlencode(basename($filename)),
                        "ext"     => $cleanExt,
                        "filename"      => $randfilename,
                        "msg"     => gT("The file has been successfully uploaded.")
                    );
                    //header('Content-Type: application/json');
                    echo ls_json_encode($return);
                    Yii::app()->end();
                }
            }
            /* We get there : an unknow error happen … maybe a move_uploaded_file error (with debug=0) */
            $return = array(
                "success" => false,
                "msg" => gT("An unknown error occured")
            );
            /* Add information for for user forcedSuperAdmin right */
            if( Permission::isForcedSuperAdmin(Permission::getUserId()) ) {
                $return['msg'] = sprintf(gT("An unknown error happened when moving file %s to %s."),$_FILES['uploadfile']['tmp_name'],$randfileloc);
            }
            //header('Content-Type: application/json');
            echo ls_json_encode($return);
            Yii::app()->end();
        }
        /* No action */
        $meta = '';
        App()->getClientScript()->registerPackage('jqueryui');
        App()->getClientScript()->registerPackage('jquery-superfish');
        
        $aSurveyInfo = getSurveyInfo($surveyid, $sLanguage);
        $oEvent = new PluginEvent('beforeSurveyPage');
        $oEvent->set('surveyId', $surveyid);
        App()->getPluginManager()->dispatchEvent($oEvent);
        if (!is_null($oEvent->get('template'))) {
            $aSurveyInfo['templatedir'] = $event->get('template');
        }
        $sTemplateDir = getTemplatePath($aSurveyInfo['template']);
        $sTemplateUrl = getTemplateURL($aSurveyInfo['template'])."/";
        $oTemplate = Template::model()->getInstance('', $surveyid);
        $sNeededScriptVar = '
            var uploadurl = "'.$this->createUrl('/uploader/index/mode/upload/').'";
            var imageurl = "'.Yii::app()->getConfig('imageurl').'/";
            var surveyid = "'.$surveyid.'";
            var fieldname = "'.$sFieldName.'";
            var questgrppreview  = '.$sPreview.';
            var csrfData = '.ls_json_encode(array(Yii::app()->request->csrfTokenName => Yii::app()->request->csrfToken)).';
            showpopups="'.$oTemplate->showpopups.'";
        ';
        $sLangScriptVar = "
                uploadLang = {
                     titleFld: '" . gT('Title', 'js')."',
                     commentFld: '" . gT('Comment', 'js')."',
                     errorNoMoreFiles: '" . gT('Sorry, no more files can be uploaded!', 'js')."',
                     errorOnlyAllowed: '" . gT('Sorry, only %s files can be uploaded for this question!', 'js')."',
                     uploading: '" . gT('Uploading', 'js')."',
                     selectfile: '" . gT('Select file', 'js')."',
                     errorNeedMore: '" . gT('Please upload %s more file(s).', 'js')."',
                     errorMoreAllowed: '" . gT('If you wish, you may upload %s more file(s); else you may return back to survey.', 'js')."',
                     errorMaxReached: '" . gT('The maximum number of files has been uploaded. You may return back to survey.', 'js')."',
                     errorTooMuch: '" . gT('The maximum number of files has been uploaded. You may return back to survey.', 'js')."',
                     errorNeedMoreConfirm: '" . gT("You need to upload %s more files for this question.\nAre you sure you want to exit?", 'js')."',
                     deleteFile : '".gT('Delete', 'js')."',
                     editFile : '".gT('Edit', 'js')."',
                    };
        ";
        App()->clientScript->registerScript('sNeededScriptVar', $sNeededScriptVar, CClientScript::POS_HEAD);
        App()->clientScript->registerScript('sLangScriptVar', $sLangScriptVar, CClientScript::POS_HEAD);
        Yii::app()->clientScript->registerPackage('survey-template-'.$oTemplate->sTemplateName);

        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig("generalscripts").'ajaxupload.js');
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig("generalscripts").'uploader.js');
        App()->clientScript->registerCssFile(Yii::app()->getConfig("publicstyleurl")."uploader.css");
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl')."uploader-files.css");
        App()->bootstrap->register();

        $header = getHeader($meta);

        echo $header;

        $fn = $sFieldName;
        $qid = (int) Yii::app()->request->getParam('qid');
        $minfiles = (int) Yii::app()->request->getParam('minfiles');
        $maxfiles = (int) Yii::app()->request->getParam('maxfiles');
        $qidattributes = QuestionAttribute::model()->getQuestionAttributes($qid);
        $maxfilesize = floor(min(intval($qidattributes['max_filesize']), getMaximumFileUploadSize() / 1024));
        if($maxfilesize <=0 ) {
            $maxfilesize = getMaximumFileUploadSize() / 1024;
        }
        $body = '</head><body class="uploader">
            <div class="model-container clearfix">
                <div id="notice" class="text-center"></div>
                <input type="hidden" id="ia"                value="'.$fn.'" />
                <input type="hidden" id="'.$fn.'_minfiles"          value="'.$minfiles.'" />
                <input type="hidden" id="'.$fn.'_maxfiles"          value="'.$maxfiles.'" />
                <input type="hidden" id="'.$fn.'_maxfilesize"       value="'.$maxfilesize.'" />
                <input type="hidden" id="'.$fn.'_allowed_filetypes" value="'.$qidattributes['allowed_filetypes'].'" />
                <input type="hidden" id="preview"                   value="'.Yii::app()->session['preview'].'" />
                <input type="hidden" id="'.$fn.'_show_comment"      value="'.$qidattributes['show_comment'].'" />
                <input type="hidden" id="'.$fn.'_show_title"        value="'.$qidattributes['show_title'].'" />
                <input type="hidden" id="'.$fn.'_licount"           value="0" />
                <input type="hidden" id="'.$fn.'_filecount"         value="0" />

                <!-- The upload button -->
                <div class="upload-div">
                    <button id="button1" class="btn btn-default" type="button" >'.gT("Select file").'</button>
                </div>

                <p class="alert alert-info uploadmsg">'.sprintf(gT("You can upload %s under %s KB each."), $qidattributes['allowed_filetypes'], $maxfilesize).'</p>
                <div id="uploadstatus" class="uploadstatus alert alert-warning hidden"></div>

                <!-- The list of uploaded files -->
            </div>
            </body>
        </html>';
        App()->getClientScript()->render($body);
        echo $body;
    }

}
