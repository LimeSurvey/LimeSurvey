/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * Copyright (C) 2010 GsiLL / Denis Chenu
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
	$('#limesurvey :input:visible:enabled:first').focus();
}

// Replace common alert with jquery-ui dialog
// Uncomment this part to test this function
/*function alert(text) {
	var $dialog = $('<div></div>')
		.html(text)
		.dialog({
			title: 'Alert',
			buttons: { "Ok": function() { $(this).dialog("close"); } },
			modal: true
		});

	$dialog.dialog('open');
}*/

/*
 * The focusFirst function is added to the eventlistener, when the page is loaded.
 * 
 * This can be used to start other functions on pageload as well. Just put it inside the 'ready' function block
 */



$(document).ready(function(){
  // focusFirst(); /** Uncomment if you want to use the focusFirst function **/
  
   // If the question code and number are hidden, hide any white space in the span(for dumb browsers)
   if($('.qnumcode').text().replace(/\s/g, '').length == 0) {
	   $('.qnumcode').hide();
   }
})


