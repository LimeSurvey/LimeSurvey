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
 *  $Id$
 */

class UploaderController extends AdminController {
    function run($actionID)
    {
        $iSurveyID = $_SESSION['LEMsid'];
        if (isset($_SESSION['survey_'.$iSurveyID]['s_lang']))
        {
            $sLanguage = $_SESSION['survey_'.$iSurveyID]['s_lang'];
        }
        else
        {
            $sLanguage='';
        }
        $clang = SetSurveyLanguage( $iSurveyID, $sLanguage);
        $sUploadDir = Yii::app()->getConfig("uploaddir");
        $sTempDir = Yii::app()->getConfig("tempdir");

        Yii::app()->loadHelper("database");
        $aRequest = $_REQUEST;
        if (isset($aRequest['filegetcontents']))
        {
            $sFileName=$aRequest['filegetcontents'];
            if (substr($sFileName,0,6)=='futmp_')
            {
                $sFileDir = $sTempDir.'/upload/';
            }
            elseif(substr($sFileName,0,3)=='fu_'){
                $sFileDir = "{$sUploadDir}/surveys/{$iSurveyID}/files/";
            }
            header('Content-Type: '. CFileHelper::getMimeType($sFileDir.$sFileName));
            readfile($sFileDir.$sFileName);
            exit();
        }
        elseif (isset($aRequest['delete']))
        {
            $sFieldname = $aRequest['fieldname'];
            $sFilename = sanitize_filename($aRequest['filename']);
            $sOriginalFileName=sanitize_filename($aRequest['name']);
            if (substr($sFilename,0,6)=='futmp_')
            {
                $sFileDir = $sTempDir.'/upload/';
            }
            elseif(substr($sFilename,0,3)=='fu_'){
                $sFileDir = "{$sUploadDir}/surveys/{$iSurveyID}/files/";
            }
            else die('Invalid filename');

            if(isset($_SESSION[$sFieldname])) {
                $sJSON = $_SESSION[$sFieldname];
                $aFiles = json_decode(stripslashes($sJSON),true);

                if(substr($sFilename,0,3)=='fu_'){
                    $iFileIndex=0;
                    $bFound=false;
                    foreach ($aFiles as $aFile)
                    {
                       if ($aFile['filename']==$sFilename)
                       {
                        $bFound=true;
                        break;
                       }
                       $iFileIndex++;
                    }
                    if ($bFound==true) unset($aFiles[$iFileIndex]);
                   $_SESSION[$sFieldname] = ls_json_encode($aFiles);
                }
            }
            if (@unlink($sFileDir.$sFilename))
            {
               echo sprintf($clang->gT('File %s deleted'), $sOriginalFileName);
            }
            else
                echo $clang->gT('Oops, There was an error deleting the file');
            exit();
        }


        if(isset($aRequest['mode']) && $aRequest['mode'] == "upload")
        {
            $clang = Yii::app()->lang;

            $sTempUploadDir = $sTempDir.'/upload/';
            // Check if exists and is writable
            if (!file_exists($sTempUploadDir)) {
                // Try to create
                mkdir($sTempUploadDir);
            }
            $sFileName = $_FILES['uploadfile']['name'];
            $fSize = 0.001 * $_FILES['uploadfile']['size'];
            $sValidExtensions = strtolower($_POST['valid_extensions']);
            $iMaximumFileSize = (int) $_POST['max_filesize'];
            $bIsPreview = $_POST['preview'];

            $aValidExtensions = explode(",", $aAttributes['allowed_filetypes']);
            $aValidExtensions = array_map('trim',$aValidExtensions);

            $aPathInfo = pathinfo($_FILES['uploadfile']['name']);
            $sExtension = $aPathInfo['extension'];
            $sRandomFileName = 'futmp_'.randomChars(15).'_'.$aPathInfo['extension'];
            $sRandomFileNameLocation = $sTempUploadDir . $sRandomFileName;

            // check to see that this file type is allowed
            // it is also  checked at the client side, but jst double checking
            if (!in_array(strtolower($sExtension), $aValidExtensions))
            {
                $aReturn = array(
                                "success" => false,
                                "msg" => sprintf($clang->gT("Sorry, this file extension (%s) is not allowed!"),$sExtension)
                            );
                echo ls_json_encode($aReturn);
                exit ();
            }

            // If this is just a preview, don't save the file
            if ($bIsPreview)
            {
                if ($fSize > $iMaximumFileSize)
                {
                    $aReturn = array(
                        "success" => false,
                        "msg" => sprintf($clang->gT("Sorry, this file is too large. Only files upto %s KB are allowed."), $iMaximumFileSize)
                    );
                    echo ls_json_encode($aReturn);
                    exit ();
                }

                else if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $sRandomFileNameLocation))
                {

                    $aReturn = array(
                                "success"       => true,
                                "file_index"    => $filecount,
                                "size"          => $fSize,
                                "name"          => rawurlencode(basename($sFileName)),
                                "ext"           => $sExtension,
                                "filename"      => $sRandomFileName,
                                "msg"           => $clang->gT("The file has been successfuly uploaded.")
                            );
                    echo ls_json_encode($aReturn);
                    // TODO : unlink this file since this is just a preview
                    // unlink($randfileloc);
                    exit ();
                }
            }
            else
            {    // if everything went fine and the file was uploaded successfuly,
                 // send the file related info back to the client
                $iFileUploadTotalSpaceMB = Yii::app()->getConfig("file_upload_total_space_mb");
                if ($fSize > $iMaximumFileSize)
                {
                    $aReturn = array(
                        "success" => false,
                         "msg" => sprintf($clang->gT("Sorry, this file is too large. Only files up to %s KB are allowed.",'unescaped'), $iMaximumFileSize)
                    );
                    echo ls_json_encode($aReturn);
                    exit ();
                }
                elseif ($iFileUploadTotalSpaceMB>0 && ((calculateTotalFileUploadUsage()+($fSize/1024/1024))>$iFileUploadTotalSpaceMB))
                {
                    $aReturn = array(
                        "success" => false,
                         "msg" => $clang->gT("We are sorry but there was a system error and your file was not saved. An email has been dispatched to notify the survey administrator.",'unescaped')
                    );
                    echo ls_json_encode($aReturn);
                    exit ();
                }
                elseif (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $sRandomFileNameLocation))
                {


                    $aReturn = array(
                        "success" => true,
                        "size"    => $fSize,
                        "name"    => rawurlencode(basename($sFileName)),
                        "ext"     => $sExtension,
                        "filename"=> $sRandomFileName,
                        "msg"     => $clang->gT("The file has been successfuly uploaded.")
                    );

                    echo ls_json_encode($aReturn);
                    exit ();
                }
                // if there was some error, report error message
                else
                {
                    // check for upload error
                    if ($_FILES['uploadfile']['error'] > 2)
                    {
                        $aReturn = array(
                                        "success" => false,
                                        "msg" => $clang->gT("Sorry, there was an error uploading your file")
                                    );

                        echo ls_json_encode($aReturn);
                        exit ();
                    }
                    // check to ensure that the file does not cross the maximum file size
                    else if ( $_FILES['uploadfile']['error'] == 1 ||  $_FILES['uploadfile']['error'] == 2 || $fSize > $iMaximumFileSize)
                    {
                        $aReturn = array(
                                        "success" => false,
                                        "msg" => sprintf($clang->gT("Sorry, this file is too large. Only files upto %s KB are allowed."), $iMaximumFileSize)
                                    );

                        echo ls_json_encode($aReturn);
                        exit ();
                    }
                    else
                    {
                        $aReturn = array(
                                    "success" => false,
                                    "msg" => $clang->gT("Unknown error")
                                );
                        echo ls_json_encode($aReturn);
                        exit ();
                    }
                }
            }
        return;
        }
        $sAdditionalHeaders ='<script type="text/javascript" src="'.Yii::app()->getConfig("generalscripts").'jquery/jquery.js"></script>';
        $sAdditionalHeaders .= '<script type="text/javascript">
            var uploadurl = "'.$this->createUrl('/uploader/index/mode/upload/').'";
            var imageurl = "'.Yii::app()->getConfig('imageurl').'/";
            var surveyid = "'.$iSurveyID.'";
            var fieldname = "'.$aRequest['fieldname'].'";
            var questgrppreview  = '.$aRequest['preview'].';
        </script>';
        $sAdditionalHeaders .='<script type="text/javascript" src="'.Yii::app()->getConfig("generalscripts").'/ajaxupload.js"></script>
        <script type="text/javascript" src="'.Yii::app()->getConfig("generalscripts").'/uploader.js"></script>
        <link type="text/css" href="'.Yii::app()->getConfig("publicstyleurl").'uploader.css" rel="stylesheet" />';

        $clang = Yii::app()->lang;

        echo getHeader($sAdditionalHeaders);

        echo "<script type='text/javascript'>
                var translt = {
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
            </script>\n";

        $sFieldName = $aRequest['fieldname'];
        $iMinFiles = sanitize_int($aRequest['minfiles']);
        $iMaxFiles = sanitize_int($aRequest['maxfiles']);

        $sBody = '
                <div id="notice"></div>
                <input type="hidden" id="ia" value="'.$sFieldName.'" />
                <input type="hidden" id="'.$sFieldName.'_minfiles" value="'.$iMinFiles.'" />
                <input type="hidden" id="'.$sFieldName.'_maxfiles" value="'.$iMaxFiles.'" />
                <input type="hidden" id="'.$sFieldName.'_maxfilesize" value="'.$aAttributes['max_filesize'].'" />
                <input type="hidden" id="'.$sFieldName.'_allowed_filetypes" value="'.$aAttributes['allowed_filetypes'].'" />
                <input type="hidden" id="preview" value="'.Yii::app()->session['preview'].'" />
                <input type="hidden" id="'.$sFieldName.'_show_comment" value="'.$aAttributes['show_comment'].'" />
                <input type="hidden" id="'.$sFieldName.'_show_title" value="'.$aAttributes['show_title'].'" />
                <input type="hidden" id="'.$sFieldName.'_licount" value="0" />
                <input type="hidden" id="'.$sFieldName.'_filecount" value="0" />

                <!-- The upload button -->
                <div align="center" class="upload-div">
                    <button id="button1" class="upload-button" type="button" >'.$clang->gT("Select file").'</button>
                </div>

                <p class="uploadmsg">'.sprintf($clang->gT("You can upload %s under %s KB each.",'js'),$aAttributes['allowed_filetypes'],$aAttributes['max_filesize']).'</p>
                <div class="uploadstatus" id="uploadstatus"></div>

                <!-- The list of uploaded files -->
                <ul id="'.$sFieldName.'_listfiles"></ul>

            </body>
        </html>';
        echo $sBody;


    }

}
