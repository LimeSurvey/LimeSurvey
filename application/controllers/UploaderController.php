<?php

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
 * @TODO: Fix this stuff into proper views soon.
 * And by soon I mean yesterday
 */

class UploaderController extends SurveyController
{
    /**
     * @param int $actionID
     * @return void
     */
    public function run($actionID)
    {
        $surveyid = Yii::app()->session['LEMsid'];
        if (empty($surveyid)) {
            throw new CHttpException(401, gT("We are sorry but your session has expired."));
        }
        $oSurvey = Survey::model()->findByPk($surveyid);
        if (!$oSurvey) {
            throw new CHttpException(400);
        }

        $sLanguage = Yii::app()->session['responses_' . $surveyid]['s_lang'] ?? "";
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
        $sFileGetContentFiltered = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $sFileGetContent);
        $sFileNameFiltered = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $sFileName);
        $sFieldNameFiltered = preg_replace('/[^X0-9]/', '', (string) $sFieldName);
        if ($sFileGetContent != $sFileGetContentFiltered || $sFileName != $sFileNameFiltered || $sFieldName != $sFieldNameFiltered) {
            // If one seems to be a hack: Bad request
            throw new CHttpException(400); // See for debug > 1
        }
        if ($sFileGetContentFiltered) {
            if (substr($sFileGetContentFiltered, 0, 6) == 'futmp_') {
                $sFileDir = $tempdir . '/upload/';
            } elseif (substr($sFileGetContentFiltered, 0, 3) == 'fu_') {
                // Need to validate $_SESSION['srid'], and this file is from this srid !
                $sFileDir = "{$uploaddir}/surveys/{$surveyid}/files/";
            } else {
                throw new CHttpException(400); // See for debug > 1
            }
            if (is_file($sFileDir . $sFileGetContentFiltered)) {
                // Validate file before else 500 error by getMimeType
                $mimeType = LSFileHelper::getMimeType($sFileDir . $sFileGetContentFiltered, null, false);
                if (is_null($mimeType)) {
                    $mimeType = "application/octet-stream"; // Can not really get content if not image
                }
                header('Content-Type: ' . $mimeType);
                readfile($sFileDir . $sFileGetContentFiltered);
                Yii::app()->end();
            } else {
                Yii::app()->end();
            }
        } elseif ($bDelete) {
            if (substr((string) $sFileName, 0, 6) == 'futmp_') {
                $sFileDir = $tempdir . '/upload/';
            } elseif (substr((string) $sFileName, 0, 3) == 'fu_') {
                // Need to validate $_SESSION['srid'], and this file is from this srid !
                $sFileDir = "{$uploaddir}/surveys/{$surveyid}/files/";
            } else {
                throw new CHttpException(400); // See for debug > 1
            }
            if (isset($_SESSION[$sFieldName])) {
                // We already have $sFieldName ?
                $sJSON = $_SESSION[$sFieldName] ?? '';
                $aFiles = json_decode(stripslashes($sJSON), true) ?? [];

                if (substr((string) $sFileName, 0, 3) == 'fu_') {
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
            if (@unlink($sFileDir . $sFileNameFiltered)) {
                echo sprintf(gT('File %s deleted'), CHtml::encode($sOriginalFileName));
            } else {
                echo gT('Oops, There was an error deleting the file');
            }
            Yii::app()->end();
        }

        // TODO: Split into two controller methods.
        if ($sMode == "upload") {
            // Check if $_FILES and $_POST are empty.
            // That probably indicates post_max_size has been exceeded.
            // https://www.php.net/manual/en/ini.core.php#ini.post-max-size
            if (empty($_POST) && empty($_FILES)) {
                if (YII_DEBUG || Permission::isForcedSuperAdmin(Permission::getUserId())) {
                    throw new CHttpException(500, "Empty \$_POST and \$_FILES. Probably post_max_size was exceeded.");
                }
                $return = array(
                    "success" => false,
                    "msg" => gT("Sorry, there was an error uploading your file.")
                );
                echo ls_json_encode($return);
                Yii::app()->end();
            }

            $sTempUploadDir = $tempdir . '/upload/';
            // Check if exists and is writable
            if (!file_exists($sTempUploadDir)) {
                // Try to create
                mkdir($sTempUploadDir);
            }
            $filename = sanitize_filename($_FILES['uploadfile']['name'], false, false, true);
            $size = $_FILES['uploadfile']['size'] / 1024;
            $preview = Yii::app()->session['preview'];
            /* Find the question by sFieldName : must be a upload question type, and id is end of sFieldName in $surveyid*/
            $aFieldName = explode("X", $sFieldName);
            if (empty($aFieldName[2]) || !ctype_digit($aFieldName[2])) {
                throw new CHttpException(400);
            }
            $oQuestion = self::getQuestion($surveyid, $aFieldName[2]);
            $aAttributes = QuestionAttribute::model()->getQuestionAttributes($oQuestion);
            $maxfilesize = min(intval($aAttributes['max_filesize']), getMaximumFileUploadSize() / 1024);
            if ($maxfilesize <= 0) {
                $maxfilesize = getMaximumFileUploadSize() / 1024;
            }
            /* Start to check upload error */
            if ($_FILES['uploadfile']['error'] > 2) {
                $return = array(
                    "success" => false,
                    "msg" => gT("Sorry, there was an error uploading your file.")
                );
                /* Show error code for user forcedSuperAdmin right */
                if (Permission::isForcedSuperAdmin(Permission::model()->getUserId())) {
                    $return['msg'] = sprintf(gT("Sorry, there was an error uploading your file, error code : %s."), $_FILES['uploadfile']['error']);
                }
                //header('Content-Type: application/json');
                echo ls_json_encode($return);
                Yii::app()->end();
            }
            /* Upload error due file size */
            if ($_FILES['uploadfile']['error'] == 1 || $_FILES['uploadfile']['error'] == 2) {
                $return = array(
                    "success" => false,
                    "msg" => sprintf(gT("Sorry, this file is too large. Only files upto %s KB are allowed."), $maxfilesize)
                );
                //header('Content-Type: application/json');
                echo ls_json_encode($return);
                Yii::app()->end();
            }

            /* Some information for plugin */
            $valid_extensions_array = explode(",", (string) $aAttributes['allowed_filetypes']);
            $valid_extensions_array = array_map('trim', $valid_extensions_array);
            $pathinfo = pathinfo((string) $_FILES['uploadfile']['name']);
            $ext = strtolower($pathinfo['extension']);
            $randfilename = 'futmp_' . randomChars(15) . '_' . $ext;
            $randfileloc = $sTempUploadDir . $randfilename;
            // event to allow updating some part
            $event = new PluginEvent('beforeProcessFileUpload');
            /* Current state */
            $event->set('surveyId', $surveyid);
            $event->set('responseId', Yii::app()->session['responses_' . $surveyid]['srid']); // NULL if not exist
            $event->set('qid', $oQuestion->qid);
            $event->set('preview', $preview);
            $event->set('fieldname', $sFieldName);
            $event->set('maxfilesize', $maxfilesize);
            $event->set('valid_extensions_array', $valid_extensions_array);
            $event->set('success', true);

            /* The file */
            $event->set('filename', $filename);
            $event->set('size', $size);
            $event->set('tmp_name', $_FILES['uploadfile']['tmp_name']);
            $event->set('ext', $ext);
            $event->set('randfilename', $randfilename);
            $event->set('randfileloc', $randfileloc);

            App()->getPluginManager()->dispatchEvent($event);
            /* New state */
            $success = $event->get('success', true);
            $message = $event->get('msg', '');
            $disableCheck = $event->get('disableCheck', false);
            $moveFile = $event->get('movefile', true);
            /* New file */
            $filename = $event->get('filename');
            $ext = $event->get('ext');
            $size = $event->get('size');
            $uploadfile_tmp_name = $event->get('tmp_name');
            $randfilename = $event->get('randfilename');
            $randfileloc = $event->get('randfileloc');

            /* @var string used to show error on extension */
            $cleanExt = CHtml::encode($ext);

            if ($uploadfile_tmp_name !== $_FILES['uploadfile']['tmp_name']) {
                @unlink($_FILES['uploadfile']['tmp_name']);
            }
            if (!$success) {
                $return = array(
                    "success" => false,
                    "msg" => !empty($message) ? $message : gT("An unknown error occurred")
                );
                echo ls_json_encode($return);
                Yii::app()->end();
            }
            if (!is_file($uploadfile_tmp_name)) {
                $return = array(
                    "success" => false,
                    "msg" => !empty($message) ? $message : gT("An unknown error occurred")
                );
                echo ls_json_encode($return);
                Yii::app()->end();
            }

            /* Check size against $aAttributes['max_filesize'] */
            if (!$disableCheck && $size > $maxfilesize) {
                $return = array(
                    "success" => false,
                    "msg" => sprintf(gT("Sorry, this file is too large. Only files upto %s KB are allowed."), $maxfilesize)
                );
                //header('Content-Type: application/json');
                echo ls_json_encode($return);
                Yii::app()->end();
            }

            // check to see that this file type is allowed
            // it is also  checked at the client side, but jst double checking
            if (!$disableCheck && !in_array($ext, $valid_extensions_array)) {
                $return = array(
                    "success" => false,
                    "msg" => sprintf(gT("Sorry, this file extension (%s) is not allowed!"), $cleanExt)
                );
                //header('Content-Type: application/json');
                echo ls_json_encode($return);
                Yii::app()->end();
            }
            /* extension checked : check mimeType */
            $extByMimeType = LSFileHelper::getExtensionByMimeType($_FILES['uploadfile']['tmp_name'], null);
            if (!$disableCheck && is_null($extByMimeType)) {
                /* Lack of finfo_open or mime_content_type ? But can be a not found extension too.*/
                /* Check if can find mime type of favicon.ico , without extension */
                /* Use CFileHelper because sure it work with included */
                if (empty(LSFileHelper::getMimeType(APPPATH . "favicon.ico", null, null))) {
                    $disableCheck = true;
                    Yii::log("Unable to check mime type of files, check for finfo_open or mime_content_type function.", \CLogger::LEVEL_ERROR, 'application.controller.uploader.upload');
                    if (YII_DEBUG || Permission::isForcedSuperAdmin(Permission::model()->getUserId())) {
                        /* This is a security issue and a server issue : always show at forced super admin */
                        throw new CHttpException(500, "Unable to check mime type of files, please activate FileInfo on server.");
                    }
                }
            }
            if (!$disableCheck && empty($extByMimeType)) {
                // FileInfo is OK, but can not find the mime type of file …
                $realMimeType = LSFileHelper::getMimeType($_FILES['uploadfile']['tmp_name'], null, false);
                Yii::log("Unable to extension for mime type " . $realMimeType, \CLogger::LEVEL_ERROR, 'application.controller.uploader.upload');
                $return = array(
                    "success" => false,
                    "msg" => sprintf(gT("Sorry, unable to check extension of this file type %s."), $realMimeType),
                );
                //header('Content-Type: application/json');
                echo ls_json_encode($return);
                Yii::app()->end();
            }
            if (!$disableCheck && !in_array($extByMimeType, $valid_extensions_array)) {
                $realMimeType = LSFileHelper::getMimeType($_FILES['uploadfile']['tmp_name'], null, false);
                $return = array(
                    "success" => false,
                    "msg" => sprintf(gT("Sorry, file type %s (extension : %s) is not allowed!"), $realMimeType, $extByMimeType)
                );
                //header('Content-Type: application/json');
                echo ls_json_encode($return);
                Yii::app()->end();
            }
            /* Plugin already move file : return status */
            if (!$moveFile) {
                $return = array(
                    "success"  => true,
                    "size"     => $size,
                    "name"     => $filename,
                    "ext"      => $cleanExt,
                    "filename" => $randfilename,
                    "msg"      =>  !empty($message) ? $message : gT("The file has been successfully uploaded.")
                );
                //header('Content-Type: application/json');
                echo ls_json_encode($return);
                Yii::app()->end();
            }
            // if everything went fine and the file was uploaded successfully,
            // If this is just a preview, don't save the file
            if ($preview) {
                if (move_uploaded_file($uploadfile_tmp_name, $randfileloc)) {
                    /** @psalm-suppress UndefinedVariable TODO: Dead code? */
                    $return = array(
                                "success"       => true,
                                "file_index"    => $filecount,
                                "size"          => $size,
                                "name"          => rawurlencode(basename((string) $filename)),
                                "ext"           => $cleanExt,
                                "filename"      => $randfilename,
                                "msg"           =>  !empty($message) ? $message : gT("The file has been successfully uploaded.")
                            );
                    // TODO : unlink this file since this is just a preview. But we can do it only if it's not needed, and still needed to have the file content
                    // Maybe use a javascript 'onunload' on preview question/group
                    // unlink($randfileloc)
                    //header('Content-Type: application/json');
                    echo ls_json_encode($return);
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
                if (move_uploaded_file($uploadfile_tmp_name, $randfileloc)) {
                    $return = array(
                        "success"  => true,
                        "size"     => $size,
                        "name"     => $filename,
                        "ext"      => $cleanExt,
                        "filename" => $randfilename,
                        "msg"      =>  !empty($message) ? $message : gT("The file has been successfully uploaded.")
                    );
                    //header('Content-Type: application/json');
                    echo ls_json_encode($return);
                    Yii::app()->end();
                }
            }
            /* We get there : an unknow error happen … maybe a move_uploaded_file error (with debug=0) */
            $return = array(
                "success" => false,
                "msg" => gT("An unknown error occurred")
            );
            /* Add information for for user forcedSuperAdmin right */
            if (Permission::isForcedSuperAdmin(Permission::model()->getUserId())) {
                $return['msg'] = sprintf(gT("An unknown error happened when moving file %s to %s."), $uploadfile_tmp_name, $randfileloc);
            }
            //header('Content-Type: application/json');
            echo ls_json_encode($return);
            Yii::app()->end();
        }
        /* No action */
        $meta = '';
        App()->getClientScript()->registerPackage('question-file-upload');

        $aSurveyInfo = getSurveyInfo($surveyid, $sLanguage);
        $oEvent = new PluginEvent('beforeSurveyPage');
        $oEvent->set('surveyId', $surveyid);
        App()->getPluginManager()->dispatchEvent($oEvent);
        if (!is_null($oEvent->get('template'))) {
            $aSurveyInfo['templatedir'] = $oEvent->get('template');
        }
        $sTemplateDir = getTemplatePath($aSurveyInfo['template']);
        $sTemplateUrl = getTemplateURL($aSurveyInfo['template']) . "/";
        $oTemplate = Template::model()->getInstance('', $surveyid);
        $sNeededScriptVar = '
            var uploadurl = uploadurl || "' . $this->createUrl('/uploader/index/mode/upload/') . '";
            var imageurl = imageurl || "' . Yii::app()->getConfig('imageurl') . '/";
            var surveyid = surveyid || "' . $surveyid . '";
            showpopups="' . $oTemplate->showpopups . '";
        ';
        $sLangScript = "{
                     titleFld: '" . gT('Title', 'js') . "',
                     commentFld: '" . gT('Comment', 'js') . "',
                     filenameFld: '" . gT('File name', 'js') . "',
                     errorNoMoreFiles: '" . gT('Sorry, no more files can be uploaded!', 'js') . "',
                     errorOnlyAllowed: '" . gT('Sorry, only %s files can be uploaded for this question!', 'js') . "',
                     uploading: '" . gT('Uploading', 'js') . "',
                     selectfile: '" . gT('Select file', 'js') . "',
                     errorNeedMore: '" . gT('Please upload %s more file(s).', 'js') . "',
                     errorMoreAllowed: '" . gT('If you wish, you may upload %s more file(s); else you may return back to survey.', 'js') . "',
                     errorMaxReached: '" . gT('The maximum number of files has been uploaded. You may return back to survey.', 'js') . "',
                     errorTooMuch: '" . gT('The maximum number of files has been uploaded. You may return back to survey.', 'js') . "',
                     errorNeedMoreConfirm: '" . gT("You need to upload %s more files for this question.\nAre you sure you want to exit?", 'js') . "',
                     deleteFile : '" . gT('Delete', 'js') . "',
                     editFile : '" . gT('Edit', 'js') . "'
                    }
        ";

        $sLangScriptVar = "
            uploadLang = " . $sLangScript . ";";

        $oTemplate = Template::model()->getInstance('', $surveyid);
        App()->getClientScript()->registerScript('sNeededScriptVar', $sNeededScriptVar, LSYii_ClientScript::POS_BEGIN);
        App()->getClientScript()->registerScript('sLangScriptVar', $sLangScriptVar, LSYii_ClientScript::POS_BEGIN);

        $header = getHeader($meta);

        echo $header;

        $fn = $sFieldName;
        $qid = (int) Yii::app()->request->getParam('qid');
        $oQuestion = self::getQuestion($surveyid, $qid);
        $qidattributes = QuestionAttribute::model()->getQuestionAttributes($oQuestion);
        $qidattributes['max_filesize'] = floor(min(intval($qidattributes['max_filesize']), getMaximumFileUploadSize() / 1024));
        if ($qidattributes['max_filesize'] <= 0) {
            $qidattributes['max_filesize'] = getMaximumFileUploadSize() / 1024;
        }
        $minfiles = "";
        if (!empty($qidattributes['min_num_of_files'])) {
            $minfiles = intval($qidattributes['min_num_of_files']);
        }
        $maxfiles = "";
        if (!empty($qidattributes['max_num_of_files'])) {
            $maxfiles = intval($qidattributes['max_num_of_files']);
        }
        $aData = [
            'fn' => $fn,
            'qid' => $qid,
            'minfiles' => $minfiles,
            'maxfiles' => $maxfiles,
            'qidattributes' => $qidattributes
        ];

        $body = '<body class="uploader">';
        $scripts = "<script>\n
            $(function(){
                " . $sNeededScriptVar . "\n\n
                " . $sLangScriptVar . "\n\n
                window.uploadModalObjects = window.uploadModalObjects || {};
                window.uploadModalObjects['" . $fn . "'] = window.getUploadHandler(  "
                    . $qid . ", {"
                    . "qid : '" . $qid . "', "
                    . "sFieldName : '" . $sFieldName . "', "
                    . "sPreview : '" . $sPreview . "', "
                    . "questgrppreview : '" . $sPreview . "', "
                    . "uploadurl : '" . $this->createUrl('/uploader/index/mode/upload/') . "', "
                    . "csrfToken: " . ls_json_encode(Yii::app()->request->csrfToken) . ", " // does not need quotes as json_encode already encodes
                    . "showpopups: '" . Yii::app()->getConfig("showpopups") . "', "
                    . "uploadLang: " . $sLangScript
                    . "});
            });
        </script>";
        $container = $this->renderPartial('/survey/questions/answer/file_upload/modal-container', $aData, true);
        $body .= $container . $scripts;
        $body .= '</body>
        </html>';
        App()->getClientScript()->render($body);
        echo $body;
    }

    /**
     * Helper function to get question
     * @param integer $surveyid the survey id
     * @param integer $qid the question id
     * @throw CHttpException if question is invalid
     * @return \Question
     */
    private static function getQuestion($surveyid, $qid)
    {
        $oQuestion = Question::model()->find("sid = :sid and qid = :qid and type = :type", [":sid" => $surveyid, ":qid" => $qid, ":type" =>  Question::QT_VERTICAL_FILE_UPLOAD]);
        if ($oQuestion) {
            return $oQuestion;
        }
        /* Log as warning */
        \Yii::log(sprintf("Invalid upload question %s in survey %s", $qid, $surveyid), 'warning', 'application.UploaderController');
        /* Show information if user have surveycontent/update (can set question type) */
        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update')) {
            throw new CHttpException(400, sprintf(gT("Invalid upload question %s in survey %s"), $qid, $surveyid));
        }
        throw new CHttpException(400);
    }
}
