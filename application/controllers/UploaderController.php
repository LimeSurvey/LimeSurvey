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
 *	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
 */

class UploaderController extends AdminController {
	function run()
	{

		$uploaddir = Yii::app()->getConfig("uploaddir");
		$tempdir = Yii::app()->getConfig("tempdir");
		
		Yii::app()->loadHelper("database");
        $param = $_REQUEST;
	
		if (isset($param['filegetcontents']))
		{
		    $sFileName=$param['filegetcontents'];
		    if (substr($sFileName,0,6)=='futmp_')
		    {
		        $sFileDir = $tempdir.'/uploads/';
		    }
		    elseif(substr($sFileName,0,3)=='fu_'){
		        $sFileDir = "{$uploaddir}/surveys/{$surveyid}/files/";
		    }
            header('Content-Type: '.mime_content_type($sFileDir.$sFileName));
		    readfile($sFileDir.$sFileName);
		    exit();
		}
			
		if (!isset($surveyid))
		{
		    $surveyid=sanitize_int(@$param['sid']);
		}
		else
		{
		    //This next line ensures that the $surveyid value is never anything but a number.
		    $surveyid=sanitize_int($surveyid);
		}
		
		if(isset($param['mode']) && $param['mode'] == "upload")
		{
			$clang = Yii::app()->lang;
		
		    $sTempUploadDir = $tempdir.'/uploads/';
		    $filename = $_FILES['uploadfile']['name'];
		    $size = 0.001 * $_FILES['uploadfile']['size'];
		    $valid_extensions = strtolower($_POST['valid_extensions']);
		    $maxfilesize = (int) $_POST['max_filesize'];
		    $preview = $_POST['preview'];
		    $fieldname = $_POST['fieldname'];
		    $aFieldMap=createFieldMap($surveyid);
		    if (!isset($aFieldMap[$fieldname])) die();
		    $aAttributes=getQuestionAttributeValues($aFieldMap[$fieldname]['qid'],$aFieldMap[$fieldname]['type']);
		
		    $valid_extensions_array = explode(",", $aAttributes['allowed_filetypes']);
		    $valid_extensions_array = array_map('trim',$valid_extensions_array);
		
		    $pathinfo = pathinfo($_FILES['uploadfile']['name']);
		    $ext = $pathinfo['extension'];
            $randfilename = 'futmp_'.sRandomChars(15).'.'.$pathinfo['extension'];
            $randfileloc = $sTempUploadDir . $randfilename;
		
		    // check to see that this file type is allowed
		    // it is also  checked at the client side, but jst double checking
		    if (!in_array(strtolower($ext), $valid_extensions_array))
		    {
		        $return = array(
		                        "success" => false,
		                        "msg" => sprintf($clang->gT("Sorry, this file extension (%s) is not allowed!"),$ext)
		                    );
		
		        echo ls_json_encode($return);
		        exit ();
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
		            echo ls_json_encode($return);
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
		            echo ls_json_encode($return);
		
		            // TODO : unlink this file since this is just a preview
		            // unlink($randfileloc);
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
		            echo ls_json_encode($return);
		        }
		        elseif ($iFileUploadTotalSpaceMB>0 && ((fCalculateTotalFileUploadUsage()+($size/1024/1024))>$iFileUploadTotalSpaceMB))
		        {
		            $return = array(
		                "success" => false,
		                 "msg" => $clang->gT("We are sorry but there was a system error and your file was not saved. An email has been dispatched to notify the survey administrator.",'unescaped')
		            );
		            echo ls_json_encode($return);
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
		
		            echo ls_json_encode($return);
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
		
		                echo ls_json_encode($return);
		            }
		            // check to ensure that the file does not cross the maximum file size
		            else if ( $_FILES['uploadfile']['error'] == 1 ||  $_FILES['uploadfile']['error'] == 2 || $size > $maxfilesize)
		            {
		                $return = array(
		                                "success" => false,
		                                "msg" => sprintf($clang->gT("Sorry, this file is too large. Only files upto %s KB are allowed."), $maxfilesize)
		                            );
		
		                echo ls_json_encode($return);
		            }
		            else
		            {
		                $return = array(
		                            "success" => false,
		                            "msg" => $clang->gT("Unknown error")
		                        );
		                echo ls_json_encode($return);
		            }
		        }
		    }
		return;
		}
		
		$meta = '<script type="text/javascript">
		    var uploadurl = "'.$this->createUrl('/uploader/index/mode/upload/').'";
            var imageurl = "'.Yii::app()->getConfig('imageurl').'/";
		    var surveyid = "'.$surveyid.'";
		    var fieldname = "'.$param['fieldname'].'";
		    var questgrppreview  = '.$param['preview'].';
		</script>';
		
		$meta .='<script type="text/javascript" src="'.Yii::app()->getConfig("generalscripts").'/ajaxupload.js"></script>
		<script type="text/javascript" src="'.Yii::app()->getConfig("generalscripts").'/uploader.js"></script>
		<link type="text/css" href="'.Yii::app()->getConfig("generalscripts").'/uploader.css" rel="stylesheet" />';
		
		$clang = Yii::app()->lang;
					
		$header = getHeader($meta);
		
		echo $header;
		
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
		
		$fn = $param['fieldname'];
		$qid = $param['qid'];
		$qidattributes=getQuestionAttributeValues($qid);
		
		$body = '
		        <div id="notice"></div>
		        <input type="hidden" id="ia"                value="'.$fn.'" />
		        <input type="hidden" id="'.$fn.'_minfiles"          value="'.$qidattributes['min_num_of_files'].'" />
		        <input type="hidden" id="'.$fn.'_maxfiles"          value="'.$qidattributes['max_num_of_files'].'" />
		        <input type="hidden" id="'.$fn.'_maxfilesize"       value="'.$qidattributes['max_filesize'].'" />
		        <input type="hidden" id="'.$fn.'_allowed_filetypes" value="'.$qidattributes['allowed_filetypes'].'" />
		        <input type="hidden" id="preview"                   value="'.Yii::app()->session['preview'].'" />
		        <input type="hidden" id="'.$fn.'_show_comment"      value="'.$qidattributes['show_comment'].'" />
		        <input type="hidden" id="'.$fn.'_show_title"        value="'.$qidattributes['show_title'].'" />
		        <input type="hidden" id="'.$fn.'_licount"           value="0" />
		        <input type="hidden" id="'.$fn.'_filecount"         value="0" />
		
		        <!-- The upload button -->
		        <div align="center" class="upload-div">
		            <button id="button1" class="upload-button" type="button" >'.$clang->gT("Select file").'</button>
		        </div>
		
		        <p class="uploadmsg">'.sprintf($clang->gT("You can upload %s under %s KB each.",'js'),$qidattributes['allowed_filetypes'],$qidattributes['max_filesize']).'</p>
		        <div class="uploadstatus" id="uploadstatus"></div>
		
		        <!-- The list of uploaded files -->
		        <ul id="'.$fn.'_listfiles"></ul>
		
		    </body>
		</html>';
		echo $body;
		
		
	}
	
}
