<?php
/*
#############################################################
# >>> PHPSurveyor  										#
#############################################################
# > Author:  Jason Cleeland									#
# > E-mail:  jason@cleeland.org								#
# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
# >          CARLTON SOUTH 3053, AUSTRALIA
# > Date: 	 20 February 2003								#
#															#
# This set of scripts allows you to develop, publish and	#
# perform data-entry on surveys.							#
#############################################################
#															#
#	Copyright (C) 2003  Jason Cleeland						#
#															#
# This program is free software; you can redistribute 		#
# it and/or modify it under the terms of the GNU General 	#
# Public License Version 2 as published by the Free         #
# Software Foundation.										#
#															#
#															#
# This program is distributed in the hope that it will be 	#
# useful, but WITHOUT ANY WARRANTY; without even the 		#
# implied warranty of MERCHANTABILITY or FITNESS FOR A 		#
# PARTICULAR PURPOSE.  See the GNU General Public License 	#
# for more details.											#
#															#
# You should have received a copy of the GNU General 		#
# Public License along with this program; if not, write to 	#
# the Free Software Foundation, Inc., 59 Temple Place - 	#
# Suite 330, Boston, MA  02111-1307, USA.					#
#############################################################
*/
session_start();
require_once(dirname(__FILE__).'/../common.php');
include_once("login_check.php");
require_once(dirname(__FILE__).'/sessioncontrol.php');
require_once(dirname(__FILE__).'/../qanda.php');

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($qid)) {$qid=returnglobal('qid');}

//Ensure script is not run directly, avoid path disclosure
if (empty($surveyid)) {die("No SID provided.");}
if (empty($qid)) {die("No QID provided.");}

if (!isset($_GET['lang']) || $_GET['lang'] == "")
{
	$language = GetBaseLanguageFromSurveyID($surveyid);
} else {
	$language = $_GET['lang'];
}

$_SESSION['s_lang'] = $language;
$clang = new phpsurveyor_lang($language);

$qquery = 'SELECT * FROM '.db_table_name('questions')." WHERE sid='$surveyid' AND qid='$qid' AND language='{$language}'";
$qresult = db_execute_assoc($qquery);
$qrows = $qresult->FetchRow();

$ia = array(0 => $qid, 1 => "FIELDNAME", 2 => $qrows['title'], 3 => $qrows['question'], 4 => $qrows['type'], 5 => $qrows['gid'],
6 => $qrows['mandatory'], 7 => $qrows['other']);
$answers = retrieveAnswers($ia);
$thistpl="$publicdir/templates";

echo "\t\t\t\t<div id='question$qa[4]'";
$question="<label for='$answers[0][7]'>" . $answers[0][0] . "</label>";
$answer=$answers[0][1];
$help=$answers[0][2];
$questioncode=$answers[0][5];
echo templatereplace(file_get_contents("$thistpl/preview.pstpl"));
echo "\t\t\t\t</div>\n";


exit;
?>
