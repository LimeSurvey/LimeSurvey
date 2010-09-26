<?php
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
 * $Id$
* 
*/

//Security Checked: POST, GET, SESSION, REQUEST, returnglobal, DB
require_once(dirname(__FILE__).'/classes/core/startup.php');   
require_once(dirname(__FILE__).'/config-defaults.php');
require_once(dirname(__FILE__).'/common.php');
if(isset($usepdfexport) && $usepdfexport == 1)
{
    require_once($pdfexportdir."/extensiontcpdf.php");
}
                                               
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
else {
        //This next line ensures that the $surveyid value is never anything but a number.
        $surveyid=sanitize_int($surveyid);
     }

// Compute the Session name
// Session name is based:
// * on this specific limesurvey installation (Value SessionName in DB)
// * on the surveyid (from Get or Post param). If no surveyid is given we are on the public surveys portal
$usquery = "SELECT stg_value FROM ".db_table_name("settings_global")." where stg_name='SessionName'";
$usresult = db_execute_assoc($usquery,'',true);          //Checked 
if ($usresult)
{
    $usrow = $usresult->FetchRow();
    $stg_SessionName=$usrow['stg_value'];
    if ($surveyid)
    {
        @session_name($stg_SessionName.'-runtime-'.$surveyid);
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
session_set_cookie_params(0,$relativeurl.'/');
@session_start();

if (isset($_SESSION['sid'])) {$surveyid=$_SESSION['sid'];}  else die('Invalid survey/session'); 

//Debut session time out
if (!isset($_SESSION['finished']) || !isset($_SESSION['srid']))
// Argh ... a session time out! RUN!
//display "sorry but your session has expired"
{
    require_once($rootdir.'/classes/core/language.php');
	$baselang = GetBaseLanguageFromSurveyID($surveyid);
	$clang = new limesurvey_lang($baselang);
	//A nice exit

	sendcacheheaders();
	doHeader();

    echo templatereplace(file_get_contents(sGetTemplatePath(validate_templatedir("default"))."/startpage.pstpl"));
	echo "<center><br />\n"
	."\t<font color='RED'><strong>".$clang->gT("ERROR")."</strong></font><br />\n"
	."\t".$clang->gT("We are sorry but your session has expired.")."<br />".$clang->gT("Either you have been inactive for too long, you have cookies disabled for your browser, or there were problems with your connection.")."<br />\n"
    ."\t".sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$siteadminname,$siteadminemail)."\n"
	."</center><br />\n";

    echo templatereplace(file_get_contents(sGetTemplatePath(validate_templatedir("default"))."/endpage.pstpl"));
	doFooter();
	exit;
};
//Fin session time out

$id=$_SESSION['srid']; //I want to see the answers with this id
$clang = $_SESSION['s_lang'];

//A little bit of debug to see in the noodles plate
/*if ($debug==2)
{
	echo "MonSurveyID $surveyid et ma langue ". $_SESSION['s_lang']. " et SRID = ". $_SESSION['srid'] ."<br />";
	echo "session id".session_id()." \n"."<br />";

	echo //"secanswer ". $_SESSION['secanswer']
	"oldsid ". $_SESSION['oldsid']."<br />"
	."step ". $_SESSION['step']."<br />"
	."scid ". $_SESSION['scid']
	."srid ". $_SESSION['srid']."<br />"
	."datestamp ". $_SESSION['datestamp']."<br />"
	."insertarray ". $_SESSION['insertarray']."<br />"
	."fieldarray ". $_SESSION['fieldarray']."<br />";
	."holdname". $_SESSION['holdname'];

	print " limit ". $limit."<br />"; //afficher les 50 derniéres réponses par ex. (pas nécessaire)
	print " surveyid ".$surveyid."<br />"; //sid
	print " id ".$id."<br />"; //identifiant de la réponses
	print " order ". $order ."<br />"; //ordre de tri (pas nécessaire)              
	print " this survey ". $thissurvey['tablename'];
};   */

//Ensure script is not run directly, avoid path disclosure
if (!isset($rootdir) || isset($_REQUEST['$rootdir'])) {die("browse - Cannot run this script directly");}

// Set the language for dispay
require_once($rootdir.'/classes/core/language.php');  // has been secured
if (isset($_SESSION['s_lang']))
{
    $clang = SetSurveyLanguage( $surveyid, $_SESSION['s_lang']);
    $language = $_SESSION['s_lang'];
} else {
    $language = GetBaseLanguageFromSurveyID($surveyid);
    $clang = SetSurveyLanguage( $surveyid, $language);
}

// Get the survey inforamtion
$thissurvey = getSurveyInfo($surveyid,$language);

//SET THE TEMPLATE DIRECTORY
if (!isset($thissurvey['templatedir']) || !$thissurvey['templatedir'])
{
    $thistpl=validate_templatedir("default");
}
else 
{
    $thistpl=validate_templatedir($thissurvey['templatedir']);
}

if ($thissurvey['printanswers']=='N') die();  //Die quietly if print answers is not permitted






//CHECK IF SURVEY IS ACTIVATED AND EXISTS
$actquery = "SELECT * FROM ".db_table_name('surveys')." as a inner join ".db_table_name('surveys_languagesettings')." as b on (b.surveyls_survey_id=a.sid and b.surveyls_language=a.language) WHERE a.sid=$surveyid";

$actresult = db_execute_assoc($actquery);    //Checked
$actcount = $actresult->RecordCount();
if ($actcount > 0)
{
	while ($actrow = $actresult->FetchRow())
	{
		$surveytable = db_table_name("survey_".$actrow['sid']);
		$surveyname = "{$actrow['surveyls_title']}";
	}
}


//OK. IF WE GOT THIS FAR, THEN THE SURVEY EXISTS AND IT IS ACTIVE, SO LETS GET TO WORK.
	//SHOW HEADER
    $printoutput = '';
    if(isset($usepdfexport) && $usepdfexport == 1)
    {
        $printoutput .= "<form action='printanswers.php?printableexport=pdf&sid=$surveyid' method='post'>\n<center><input type='submit' value='".$clang->gT("PDF Export")."'id=\"exportbutton\"/><input type='hidden' name='printableexport' /></center></form>";
    }
    if(isset($_POST['printableexport']))
    {
        $pdf = new PDF($pdforientation);
        $pdf->SetFont($pdfdefaultfont,'',$pdffontsize);
        $pdf->AddPage(); 
        $pdf->titleintopdf($clang->gT("Survey Name (ID)",'unescaped').": {$surveyname} ({$surveyid})");
    }
	$printoutput .= "\t<span class='printouttitle'><strong>".$clang->gT("Survey Name (ID)").":</strong> $surveyname ($surveyid)</span><br />\n";

//Get the fieldmap @TODO: do we need to filter out some fields?
$fnames = createFieldMap($surveyid,'full',false,false,$language);
unset ($fnames['id']);
unset ($fnames['lastpage']);
unset ($fnames['startlanguage']);
unset ($fnames['datestamp']);
unset ($fnames['startdate']);

	//SHOW INDIVIDUAL RECORD
	$idquery = "SELECT * FROM $surveytable WHERE id=$id";
	$idresult = db_execute_assoc($idquery) or safe_die ("Couldn't get entry<br />\n$idquery<br />\n".$connect->ErrorMsg()); //Checked   
	while ($idrow = $idresult->FetchRow()) {$id=$idrow['id']; $rlangauge=$idrow['startlanguage'];}
	$next=$id+1;
	$last=$id-1;
	$printoutput .= "<table class='printouttable' >\n";
    if(isset($_POST['printableexport']))
    {
        $pdf->intopdf($clang->gT("Question",'unescaped').": ".$clang->gT("Your Answer",'unescaped'));
    }
    $printoutput .= "<tr><th>".$clang->gT("Question")."</th><th>".$clang->gT("Your Answer")."</th></tr>\n";
	$idresult = db_execute_assoc($idquery) or safe_die ("Couldn't get entry<br />$idquery<br />".$connect->ErrorMsg()); //Checked   
	while ($idrow = $idresult->FetchRow())
	{
    $oldgid = 0;
    $oldqid = 0;
    foreach ($fnames as $fname)
		{
        $question = $fname['question'];
        if (isset($fname['gid']) && !empty($fname['gid'])) {
            //Check to see if gid is the same as before. if not show group name
            if ($oldgid !== $fname['gid']) {
                $oldgid = $fname['gid'];
                $printoutput .= "\t<tr><td colspan='2'>{$fname['group_name']}</td></tr>\n";
            }
        }
        if (isset($fname['qid']) && !empty($fname['qid'])) {
            //Check to see if gid is the same as before. if not show group name
            if ($oldqid !== $fname['qid']) {
                $oldqid = $fname['qid'];
                if (isset($fname['subquestion']) || isset($fname['subquestion1']) || isset($fname['subquestion2'])) {
                    $printoutput .= "\t<tr><td>{$fname['question']}</td></tr>\n";                    
                }
            }
        }
        if (isset($fname['subquestion']))  $question = "{$fname['subquestion']}";
        if (isset($fname['subquestion1'])) $question = "{$fname['subquestion1']}";
        if (isset($fname['subquestion2'])) $question .= "[{$fname['subquestion2']}]";
			$printoutput .= "\t<tr>\n"
        ."<td>$question</td>\n"
			."<td>"
        .getextendedanswer($fname['fieldname'], $idrow[$fname['fieldname']])
			."</td>\n"
			."\t</tr>\n";
            if(isset($_POST['printableexport']))
            {
            if (isset($fname['subquestion']))  $question .= " [{$fname['subquestion']}]";
            if (isset($fname['subquestion1'])) $question .= " [{$fname['subquestion1']}]";
            if (isset($fname['subquestion2'])) $question .= " [{$fname['subquestion2']}]";      
            $pdf->intopdf(FlattenText($question,true).": ".FlattenText(getextendedanswer($fname['fieldname'], $idrow[$fname['fieldname']]),true));
                $pdf->ln(2);
            }
		}
	}
    $printoutput .= "</table>\n";
    if(isset($_POST['printableexport']))
    {
    	// IE6 Header-Cache fix
		// Wenn der IE 6 das pdf file nicht erkennt, liegts am IE6 Nutzer
		//(zu doof, der Browser kennt keine pdf's oder kein reader ist installiert) 
		// oder daran das man multipleIE verwendet.
		
//		header('Cache-Control: no-cache, must-revalidate'); //Adjust maxage appropriately
//		header('Pragma: public');
//	
//		// Wir werden eine PDF Datei ausgeben 
//		// \n\n bewirkt eine korrektes erkennen des Content-type im IE
//		
//		header('Content-Disposition: attachment; filename="'.$clang->gT($surveyname).'-'.$surveyid.'.pdf"');
//		header('Content-type: application/pdf');
//	
//		header('Content-Transfer-Encoding: binary');
//		//header('Content-Length: '. filesize($filename)); 

		
	    //session_cache_limiter('private');
		header("Pragma: public");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		
		if (preg_match("/MSIE/i", $_SERVER["HTTP_USER_AGENT"]))
		{
			/*
			 *  $dateiname = $InternalReferenceNumber.'.pdf'; 
				$pdf->Output($dateiname, 'F'); 
				header('Content-type: application/pdf'); 
				header('Content-Disposition: attachment; filename="'.basename($dateiname).'"'); 
				readfile($dateiname);
			 */
			
			header("Content-type: application/pdf");
			header("Content-Transfer-Encoding: binary");
			
			
			header("Content-Disposition: Attachment; filename=\"". $clang->gT($surveyname)."-".$surveyid.".pdf\"");
			
			$pdf->Output("tmp/".$clang->gT($surveyname)."-".$surveyid.".pdf", "F");
			header("Content-Length: ". filesize("tmp/".$clang->gT($surveyname)."-".$surveyid.".pdf"));
			readfile("tmp/".$clang->gT($surveyname)."-".$surveyid.".pdf");
			unlink("tmp/".$clang->gT($surveyname)."-".$surveyid.".pdf");
			//$pdf->write_out($clang->gT($surveyname)."-".$surveyid.".pdf");
		}
		else
		{
			$pdf->Output($clang->gT($surveyname)."-".$surveyid.".pdf","DD");
		}
    }


//tadaaaaaaaaaaa : display the page with the answers of user
    if(!isset($_POST['printableexport']))
    {
		sendcacheheaders();
		doHeader();
    
    echo templatereplace(file_get_contents(sGetTemplatePath($thistpl).'/startpage.pstpl'));
    echo templatereplace(file_get_contents(sGetTemplatePath($thistpl).'/printanswers.pstpl'));
    echo templatereplace(file_get_contents(sGetTemplatePath($thistpl).'/endpage.pstpl'));
		echo "</body></html>";
    }
?>