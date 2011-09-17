 
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
 * $Id: survey.php 10433 2011-07-06 14:18:45Z dionet $
 *
 */

class uploader extends LSCI_Controller {

	function __construct()
	{
		parent::__construct();
	}
	
	public function _remap($method, $params = array())
	{
		array_unshift($params, $method);
	    return call_user_func_array(array($this, "action"), $params);
	}

	function action()
	{
		//Replace $param:
		$arg_list = func_get_args();
		if($arg_list[0]==__CLASS__) array_shift($arg_list);
		if(count($arg_list)%2 == 0) {
		    for ($i = 0; $i < count($arg_list); $i+=2) {
		        //echo $arg_list[$i]."=" . $arg_list[$i+1] . "<br />\n";
				$param[$arg_list[$i]] = $arg_list[$i+1];
		    }
		}
		
		$uploaddir = $this->config->item("uploaddir");
		$tempdir = $this->config->item("tempdir");
		
		$this->load->helper("database");
	
		if (isset($param['filegetcontents']))
		{
		    $sFileName=sanitize_filename($param['filegetcontents']);
		    if (substr($sFileName,0,6)=='futmp_')
		    {
		        $sFileDir = $tempdir.'/uploads/';
		    }
		    elseif(substr($sFileName,0,3)=='fu_'){
		        $sFileDir = "{$uploaddir}/surveys/{$surveyid}/files/";
		    }
		    readfile($sFileDir.$sFileName);
		    exit();
		}
			
		if (!isset($surveyid))
		{
		    $surveyid=sanitize_int($param['sid']);
		}
		else
		{
		    //This next line ensures that the $surveyid value is never anything but a number.
		    $surveyid=sanitize_int($surveyid);
		}
				
		// Compute the Session name
		// Session name is based:
		// * on this specific limesurvey installation (Value SessionName in DB)
		// * on the surveyid (from Get or Post param). If no surveyid is given we are on the public surveys portal
		$usquery = "SELECT stg_value FROM ".$this->db->dbprefix("settings_global")." where stg_name='SessionName'";
		$usresult = db_execute_assoc($usquery,'',true);          //Checked
		if ($usresult)
		{
		    $usrow = $usresult->row_array();
		    $stg_SessionName=$usrow['stg_value'];
		    if ($surveyid)
		    {
		        if (isset($param['preview']) && $param['preview'] == 1)
		        {
		            @session_name($stg_SessionName);
		        }
		        else
		        {
		            @session_name($stg_SessionName.'-runtime-'.$surveyid);
		        }
		    }
		    else
		    {
		        @session_name($stg_SessionName.'-runtime-publicportal');
		    }
		}
		else
		{
		    session_name("LimeSurveyRuntime-$surveyid");
		}
		//session_set_cookie_params(0,$relativeurl.'/');
		//@session_start();
		
		if (!$this->session->userdata('fieldname'))
		{
		    die("You don't have a valid session !");
		}
		
		if(isset($param['mode']) && $param['mode'] == "upload")
		{
		    $baselang = GetBaseLanguageFromSurveyID($surveyid);
		    $this->load->library('Limesurvey_lang',array("langcode"=>$baselang));
			$clang = $this->limesurvey_lang;
		
		    $randfilename = 'futmp_'.sRandomChars(15);
		    $sTempUploadDir = $tempdir.'/uploads/';
		    $randfileloc = $sTempUploadDir . $randfilename;
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
		
		    // check to see that this file type is allowed
		    // it is also  checked at the client side, but jst double checking
		    if (!in_array(strtolower($ext), $valid_extensions_array))
		    {
		        $return = array(
		                        "success" => false,
		                        "msg" => sprintf($clang->gT("Sorry, this file extension (%s) is not allowed!"),$ext)
		                    );
		
		        echo json_encode($return);
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
		            echo json_encode($return);
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
		            echo json_encode($return);
		
		            // TODO : unlink this file since this is just a preview
		            // unlink($randfileloc);
		        }
		    }
		    else
		    {    // if everything went fine and the file was uploaded successfuly,
		         // send the file related info back to the client
		         $iFileUploadTotalSpaceMB = $this->config->item("iFileUploadTotalSpaceMB");
		        if ($size > $maxfilesize)
		        {
		            $return = array(
		                "success" => false,
		                 "msg" => sprintf($clang->gT("Sorry, this file is too large. Only files up to %s KB are allowed.",'unescaped'), $maxfilesize)
		            );
		            echo json_encode($return);
		        }
		        elseif ($iFileUploadTotalSpaceMB>0 && ((fCalculateTotalFileUploadUsage()+($size/1024/1024))>$iFileUploadTotalSpaceMB))
		        {
		            $return = array(
		                "success" => false,
		                 "msg" => $clang->gT("We are sorry but there was a system error and your file was not saved. An email has been dispatched to notify the survey administrator.",'unescaped')
		            );
		            echo json_encode($return);
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
		
		            echo json_encode($return);
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
		
		                echo json_encode($return);
		            }
		            // check to ensure that the file does not cross the maximum file size
		            else if ( $_FILES['uploadfile']['error'] == 1 ||  $_FILES['uploadfile']['error'] == 2 || $size > $maxfilesize)
		            {
		                $return = array(
		                                "success" => false,
		                                "msg" => sprintf($clang->gT("Sorry, this file is too large. Only files upto %s KB are allowed."), $maxfilesize)
		                            );
		
		                echo json_encode($return);
		            }
		            else
		            {
		                $return = array(
		                            "success" => false,
		                            "msg" => $clang->gT("Unknown error")
		                        );
		                echo json_encode($return);
		            }
		        }
		    }
		return;
		}
		
		$meta = '<script type="text/javascript">
		    var uploadurl = "'.site_url('uploader/mode/upload/').'";
		    var surveyid = "'.$surveyid.'";
		    var fieldname = "'.$param['fieldname'].'";
		    var questgrppreview  = '.$param['preview'].';
		</script>';
		
		$meta .='<script type="text/javascript" src="'.$this->config->item("generalscripts").'/ajaxupload.js"></script>
		<script type="text/javascript" src="'.$this->config->item("generalscripts").'/uploader.js"></script>
		<link type="text/css" href="'.$this->config->item("generalscripts").'/uploader.css" rel="stylesheet" />';
		
		$baselang = GetBaseLanguageFromSurveyID($surveyid);
	    $this->load->library('Limesurvey_lang',array("langcode"=>$baselang));
		$clang = $this->limesurvey_lang;
					
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
		        <input type="hidden" id="preview"                   value="'.$this->session->userdata('preview').'" />
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
