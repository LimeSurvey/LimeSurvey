<?php
/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Configuration file for the File Manager Connector for PHP.
 */

global $Config ;


// read LimeSurvey config files and standard library
require_once(dirname(__FILE__).'/../../../../../../../config-defaults.php');
require_once(dirname(__FILE__).'/../../../../../../../common.php');
require_once(dirname(__FILE__).'/../../../../../../admin_functions.php');

$usquery = "SELECT stg_value FROM ".db_table_name("settings_global")." where stg_name='SessionName'";
$usresult = db_execute_assoc($usquery,'',true);
if ($usresult)
{
    $usrow = $usresult->FetchRow();
    @session_name($usrow['stg_value']);
}
else
{
    session_name("LimeSurveyAdmin");
}

session_set_cookie_params(0,$relativeurl.'/');
if (session_id() == "") @session_start();


// SECURITY: You must explicitly enable this "connector". (Set it to "true").
// WARNING: don't just set "$Config['Enabled'] = true ;", you must be sure that only
//		authenticated users can access this file or use some kind of session checking.
$Config['Enabled'] = false ;



if ($demoModeOnly === false &&
isset($_SESSION['loginID']) &&
isset($_SESSION['FileManagerContext']))
{
    // disable upload at survey creation time
    // because we don't know the sid yet
    if (preg_match('/^(create|edit):(question|group|answer)/',$_SESSION['FileManagerContext']) != 0 ||
    preg_match('/^edit:survey/',$_SESSION['FileManagerContext']) !=0 ||
    preg_match('/^edit:assessments/',$_SESSION['FileManagerContext']) !=0 ||
    preg_match('/^edit:emailsettings/',$_SESSION['FileManagerContext']) != 0)
    {
        $contextarray=explode(':',$_SESSION['FileManagerContext'],3);
        $surveyid=$contextarray[2];



        if(bHasSurveyPermission($surveyid,'surveycontent','update'))
        {
            $Config['Enabled'] = true ;
            $Config['UserFilesPath'] = "$relativeurl/upload/surveys/$surveyid/" ;
            //$Config['UserFilesPath'] = "$rooturl/upload/$surveyid/" ;
            $Config['UserFilesAbsolutePath'] = "$rootdir/upload/surveys/$surveyid/" ;

        }

    }
    elseif (preg_match('/^edit:label/',$_SESSION['FileManagerContext']) != 0)
    {
        $contextarray=explode(':',$_SESSION['FileManagerContext'],3);
        $labelid=$contextarray[2];
        // check if the user has label management right and labelid defined
        if ($_SESSION['USER_RIGHT_MANAGE_LABEL']==1 && isset($labelid) && $labelid != '')
        {
            $Config['Enabled'] = true ;
            $Config['UserFilesPath'] = "$relativeurl/upload/labels/$labelid/" ;
            $Config['UserFilesAbsolutePath'] = "$rootdir/upload/labels/$labelid/" ;
        }
    }
    else
    {
        // send a notice message
        //SendError(1, $clang->gT("Upload of files is not enabled in this mode"));
        //		echo "<script type=\"text/javascript\">\n"
        //		. "<!--\n"
        //		. "alert('".$clang->gT("Upload of files is not enabled in this mode")."');\n"
        //		. "-->\n"
        //		. "</script>\n";
        //
        //		echo $clang->gT("Upload of files is not enabled in this mode");

        // I can't find a way to notify the user here :-(
    }
}





// Path to user files relative to the document root.
//$Config['UserFilesPath'] = '/userfiles/' ;

// Fill the following value it you prefer to specify the absolute path for the
// user files directory. Useful if you are using a virtual directory, symbolic
// link or alias. Examples: 'C:\\MySite\\userfiles\\' or '/root/mysite/userfiles/'.
// Attention: The above 'UserFilesPath' must point to the same directory.
//$Config['UserFilesAbsolutePath'] = '' ;

// Due to security issues with Apache modules, it is recommended to leave the
// following setting enabled.
$Config['ForceSingleExtension'] = true ;

// Perform additional checks for image files
// if set to true, validate image size (using getimagesize)
$Config['SecureImageUploads'] = true;

// What the user can do with this connector
//$Config['ConfigAllowedCommands'] = array('QuickUpload', 'FileUpload', 'GetFolders', 'GetFoldersAndFiles', 'CreateFolder') ;
$Config['ConfigAllowedCommands'] = array('QuickUpload', 'FileUpload', 'GetFoldersAndFiles') ;

// Allowed Resource Types
$Config['ConfigAllowedTypes'] = array('File', 'Image', 'Flash', 'Media') ;

// For security, HTML is allowed in the first Kb of data for files having the
// following extensions only.
//$Config['HtmlExtensions'] = array("html", "htm", "xml", "xsd", "txt", "js") ;
$Config['HtmlExtensions'] = array("txt") ;

/*
 Configuration settings for each Resource Type

 - AllowedExtensions: the possible extensions that can be allowed.
 If it is empty then any file type can be uploaded.
 - DeniedExtensions: The extensions that won't be allowed.
 If it is empty then no restrictions are done here.

 For a file to be uploaded it has to fulfill both the AllowedExtensions
 and DeniedExtensions (that's it: not being denied) conditions.

 - FileTypesPath: the virtual folder relative to the document root where
 these resources will be located.
 Attention: It must start and end with a slash: '/'

 - FileTypesAbsolutePath: the physical path to the above folder. It must be
 an absolute path.
 If it's an empty string then it will be autocalculated.
 Useful if you are using a virtual directory, symbolic link or alias.
 Examples: 'C:\\MySite\\userfiles\\' or '/root/mysite/userfiles/'.
 Attention: The above 'FileTypesPath' must point to the same directory.
 Attention: It must end with a slash: '/'

 - QuickUploadPath: the virtual folder relative to the document root where
 these resources will be uploaded using the Upload tab in the resources
 dialogs.
 Attention: It must start and end with a slash: '/'

 - QuickUploadAbsolutePath: the physical path to the above folder. It must be
 an absolute path.
 If it's an empty string then it will be autocalculated.
 Useful if you are using a virtual directory, symbolic link or alias.
 Examples: 'C:\\MySite\\userfiles\\' or '/root/mysite/userfiles/'.
 Attention: The above 'QuickUploadPath' must point to the same directory.
 Attention: It must end with a slash: '/'

 NOTE: by default, QuickUploadPath and QuickUploadAbsolutePath point to
 "userfiles" directory to maintain backwards compatibility with older versions of FCKeditor.
 This is fine, but you in some cases you will be not able to browse uploaded files using file browser.
 Example: if you click on "image button", select "Upload" tab and send image
 to the server, image will appear in FCKeditor correctly, but because it is placed
 directly in /userfiles/ directory, you'll be not able to see it in built-in file browser.
 The more expected behaviour would be to send images directly to "image" subfolder.
 To achieve that, simply change
 $Config['QuickUploadPath']['Image']			= $Config['UserFilesPath'] ;
 $Config['QuickUploadAbsolutePath']['Image']	= $Config['UserFilesAbsolutePath'] ;
 into:
 $Config['QuickUploadPath']['Image']			= $Config['FileTypesPath']['Image'] ;
 $Config['QuickUploadAbsolutePath']['Image'] 	= $Config['FileTypesAbsolutePath']['Image'] ;

 */

//$Config['AllowedExtensions']['File']	= array('7z', 'aiff', 'asf', 'avi', 'bmp', 'csv', 'doc', 'fla', 'flv', 'gif', 'gz', 'gzip', 'jpeg', 'jpg', 'mid', 'mov', 'mp3', 'mp4', 'mpc', 'mpeg', 'mpg', 'ods', 'odt', 'pdf', 'png', 'ppt', 'pxd', 'qt', 'ram', 'rar', 'rm', 'rmi', 'rmvb', 'rtf', 'sdc', 'sitd', 'swf', 'sxc', 'sxw', 'tar', 'tgz', 'tif', 'tiff', 'txt', 'vsd', 'wav', 'wma', 'wmv', 'xls', 'xml', 'zip') ;
$Config['AllowedExtensions']['File']	= array_map('trim',explode(',',$allowedresourcesuploads));
$Config['DeniedExtensions']['File']		= array() ;
//$Config['FileTypesPath']['File']		= $Config['UserFilesPath'] . 'file/' ;
$Config['FileTypesPath']['File']		= $Config['UserFilesPath'];

//$Config['FileTypesAbsolutePath']['File']= ($Config['UserFilesAbsolutePath'] == '') ? '' : $Config['UserFilesAbsolutePath'].'file/' ;
$Config['FileTypesAbsolutePath']['File']= ($Config['UserFilesAbsolutePath'] == '') ? '' : $Config['UserFilesAbsolutePath'];
$Config['QuickUploadPath']['File']		= $Config['UserFilesPath'] ;
$Config['QuickUploadAbsolutePath']['File']= $Config['UserFilesAbsolutePath'] ;

//$Config['AllowedExtensions']['Image']	= array('bmp','gif','jpeg','jpg','png') ;
$Config['AllowedExtensions']['Image']	= $Config['AllowedExtensions']['File'];
$Config['DeniedExtensions']['Image']	= array() ;
//$Config['FileTypesPath']['Image']		= $Config['UserFilesPath'] . 'image/' ;
$Config['FileTypesPath']['Image']		= $Config['UserFilesPath'];
//$Config['FileTypesAbsolutePath']['Image']= ($Config['UserFilesAbsolutePath'] == '') ? '' : $Config['UserFilesAbsolutePath'].'image/' ;
$Config['FileTypesAbsolutePath']['Image']= ($Config['UserFilesAbsolutePath'] == '') ? '' : $Config['UserFilesAbsolutePath'];
$Config['QuickUploadPath']['Image']		= $Config['UserFilesPath'] ;
$Config['QuickUploadAbsolutePath']['Image']= $Config['UserFilesAbsolutePath'] ;

//$Config['AllowedExtensions']['Flash']	= array('swf','flv') ;
$Config['AllowedExtensions']['Flash']	= $Config['AllowedExtensions']['File'];
$Config['DeniedExtensions']['Flash']	= array() ;
//$Config['FileTypesPath']['Flash']		= $Config['UserFilesPath'] . 'flash/' ;
$Config['FileTypesPath']['Flash']		= $Config['UserFilesPath'];
//$Config['FileTypesAbsolutePath']['Flash']= ($Config['UserFilesAbsolutePath'] == '') ? '' : $Config['UserFilesAbsolutePath'].'flash/' ;
$Config['FileTypesAbsolutePath']['Flash']= ($Config['UserFilesAbsolutePath'] == '') ? '' : $Config['UserFilesAbsolutePath'];
$Config['QuickUploadPath']['Flash']		= $Config['UserFilesPath'] ;
$Config['QuickUploadAbsolutePath']['Flash']= $Config['UserFilesAbsolutePath'] ;

//$Config['AllowedExtensions']['Media']	= array('aiff', 'asf', 'avi', 'bmp', 'fla', 'flv', 'gif', 'jpeg', 'jpg', 'mid', 'mov', 'mp3', 'mp4', 'mpc', 'mpeg', 'mpg', 'png', 'qt', 'ram', 'rm', 'rmi', 'rmvb', 'swf', 'tif', 'tiff', 'wav', 'wma', 'wmv') ;
$Config['AllowedExtensions']['Media']	= $Config['AllowedExtensions']['File'];
$Config['DeniedExtensions']['Media']	= array() ;
//$Config['FileTypesPath']['Media']		= $Config['UserFilesPath'] . 'media/' ;
$Config['FileTypesPath']['Media']		= $Config['UserFilesPath'];
//$Config['FileTypesAbsolutePath']['Media']= ($Config['UserFilesAbsolutePath'] == '') ? '' : $Config['UserFilesAbsolutePath'].'media/' ;
$Config['FileTypesAbsolutePath']['Media']= ($Config['UserFilesAbsolutePath'] == '') ? '' : $Config['UserFilesAbsolutePath'];
$Config['QuickUploadPath']['Media']		= $Config['UserFilesPath'] ;
$Config['QuickUploadAbsolutePath']['Media']= $Config['UserFilesAbsolutePath'] ;

?>
