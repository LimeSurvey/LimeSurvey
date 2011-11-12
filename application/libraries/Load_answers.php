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
 * $Id: save.php 10195 2011-06-05 15:16:12Z lemeur $
*/

class Load_answers {
	
	function run($args) {	
		extract($args);
		$CI =& get_instance();
		$_POST = $CI->input->post();
		

		if (!isset($thistpl)) {die ("Error!");}
		sendcacheheaders();
		doHeader();
		foreach(file("$thistpl/startpage.pstpl") as $op)
		{
		    echo templatereplace($op);
		}
		echo "\n\n<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->\n"
		."\t<script type='text/javascript'>\n"
		."function checkconditions(value, name, type)\n"
		."\t{\n"
		."\t}\n"
		."\t</script>\n\n";
		
		echo "<form method='post' action='$relativeurl/index.php'>\n";
		foreach(file("$thistpl/load.pstpl") as $op)
		{
		    echo templatereplace($op);
		}
		//PRESENT OPTIONS SCREEN (Replace with Template Later)
		//END
		//echo "<input type='hidden' name='PHPSESSID' value='".session_id()."'>\n";
		echo "<input type='hidden' name='sid' value='$surveyid' />\n";
		echo "<input type='hidden' name='loadall' value='reload' />\n";
		if (isset($clienttoken) && $clienttoken != "")
		{
		    echo "<input type='hidden' name='token' value='$clienttoken' />\n";
		}
		echo "</form>";
		
		foreach(file("$thistpl/endpage.pstpl") as $op)
		{
		    echo templatereplace($op);
		}
		doFooter();
		exit;

		
	}
}