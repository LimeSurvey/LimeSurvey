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
 * 
 * Description: Javascript file for templates. Put JS-functions for your template here.
 *  
 * 
 * $Id:$
 */


/*
 * The function focusFirst puts the Focus on the first non-hidden element in the Survey. 
 * 
 * Normally this is the first input field (the first answer).
 */
function focusFirst(Event)
{
	var i=0;
	// count up as long as the elements are hidden
	while(document.forms[0].elements[i].type == "hidden")
	{
		i++;
	}
	// put focus on the element we just counted.
	document.forms[0].elements[i].focus();
	return;
}
/*
 * The focusFirst function is added to the eventlistener, when the page is loaded.
 * 
 * This can be used to start other functions on pageload as well. Just copy the lines and replace the function name.
 */

/** UnComment if you want to use the focusFirst function

//var ie is set in startpage.pstpl to true (Internet Explorer) or false (other Browser than IE) with conditional comments. IE needs his own attachEvent.
if(ie) 
	{window.attachEvent("onload", focusFirst);}
else // EventListener are supported from gecko and webkit Browsers (Firefox, Iceweasel, Safari, Chrome etc.)
	{document.addEventListener("load", focusFirst, true);}
	
**/