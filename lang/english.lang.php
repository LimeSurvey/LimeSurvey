<?php
/*
	#############################################################
	# >>> PHP Surveyor  										#
	#############################################################
	# > Author:  Jason Cleeland									#
	# > E-mail:  jason@cleeland.org								#
	# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
	# >          CARLTON SOUTH 3053, AUSTRALIA					#
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
	# Public License as published by the Free Software 			#
	# Foundation; either version 2 of the License, or (at your 	#
	# option) any later version.								#
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
//SINGLE WORDS
define("_YES", "Yes");
define("_NO", "No");
define("_UNCERTAIN", "Uncertain");
define("_ADMIN", "Admin");
define("_TOKENS", "Tokens");
define("_FEMALE", "Female");
define("_MALE", "Male");
define("_NOANSWER", "No answer");
define("_OTHER", "Other");
define("_PLEASECHOOSE", "Please choose");
define("_ERROR", "Error");
define("_COMPLETE", "complete");
define("_INCREASE", "Increase"); //NEW WITH 0.98
define("_SAME", "Same"); //NEW WITH 0.98
define("_DECREASE", "Decrease"); //NEW WITH 0.98
//from questions.php
define("_CONFIRMATION", "Confirmation");
define("_TOKEN", "Token");
define("_CONTINUE", "Continue");

//BUTTONS
define("_ACCEPT", "Accept");
define("_PREV", "prev");
define("_NEXT", "next");
define("_LAST", "last");
define("_SUBMIT", "submit");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Please choose one of the following");
define("_ENTERCOMMENT", "Please enter your comment here");
define("_NUMERICAL", "Only numbers may be entered in this field");
define("_CLEARALL", "Exit and Clear Survey");
define("_MANDATORY", "This question is mandatory");
define("_MANDATORY_PARTS", "Please complete all parts");
define("_MANDATORY_CHECK", "Please check at least one item");
define("_MANDATORY_RANK", "Please rank all items");
define("_MANDATORY_POPUP", "One or more mandatory questions have not been answered. You cannot proceed until these have been completed"); //NEW in 0.98rc4
define("_DATEFORMAT", "Format: YYYY-MM-DD");
define("_DATEFORMATEG", "(eg: 2003-12-25 for Christmas day)");
define("_REMOVEITEM", "Remove this item");
define("_RANK_1", "Click on an item in the list on the left, starting with your");
define("_RANK_2", "highest ranking item, moving through to your lowest ranking item.");
define("_YOURCHOICES", "Your Choices");
define("_YOURRANKING", "Your Ranking");
define("_RANK_3", "Click on the scissors next to each item on the right");
define("_RANK_4", "to remove the last entry in your ranked list");
//From INDEX.PHP
define("_NOSID", "You have not provided a survey identification number");
define("_CONTACT1", "Please contact");
define("_CONTACT2", "for further assistance");
define("_ANSCLEAR", "Answers Cleared");
define("_RESTART", "Restart this Survey");
define("_CLOSEWIN", "Close this Window");
define("_CONFIRMCLEAR", "Are you sure you want to clear all your responses?");
define("_EXITCLEAR", "Exit and Clear Survey");
//From QUESTION.PHP
define("_BADSUBMIT1", "Cannot submit results - there are none to submit.");
define("_BADSUBMIT2", "This error can occur if you have already submitted your responses and pressed 'refresh' on your browser. In this case, your responses have already been saved.<br /><br />If you receive this message in the middle of completing a survey, you should choose '<- BACK' on your browser and then refresh/reload the previous page. While you will lose answers from the last page all your others will still exist. This problem can occur if the webserver is suffering from overload or excessive use. We apologise for this problem.");
define("_NOTACTIVE1", "Your survey responses have not been recorded. This survey is not yet active.");
define("_CLEARRESP", "Clear Responses");
define("_THANKS", "Thank you");
define("_SURVEYREC", "Your survey responses have been recorded.");
define("_SURVEYCPL", "Survey Completed");
define("_DIDNOTSAVE", "Did Not Save");
define("_DIDNOTSAVE2", "An unexpected error has occurred and your responses cannot be saved.");
define("_DIDNOTSAVE3", "Your responses have not been lost and have been emailed to the survey administrator and will be entered into our database at a later point.");
define("_DNSAVEEMAIL1", "An error occurred saving a response to survey id");
define("_DNSAVEEMAIL2", "DATA TO BE ENTERED");
define("_DNSAVEEMAIL3", "SQL CODE THAT FAILED");
define("_DNSAVEEMAIL4", "ERROR MESSAGE");
define("_DNSAVEEMAIL5", "ERROR SAVING");
define("_SUBMITAGAIN", "Try to submit again");
define("_SURVEYNOEXIST", "Sorry. There is no matching survey.");
define("_NOTOKEN1", "This is a controlled survey. You need a valid token to participate.");
define("_NOTOKEN2", "If you have been issued with a token, please enter it in the box below and click continue.");
define("_NOTOKEN3", "The token you have provided is either not valid, or has already been used.");
define("_NOQUESTIONS", "This survey does not yet have any questions and cannot be tested or completed.");
define("_FURTHERINFO", "For further information contact");
define("_NOTACTIVE", "This survey is not currently active. You will not be able to save your responses.");

define("_SURVEYCOMPLETE", "You have already completed this survey.");

define("_INSTRUCTION_LIST", "Choose only one of the following"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Check any that apply"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Survey Submitted"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "A new response was entered for your survey"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Click the following link to see the individual response:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "View statistics by clicking here:"); //NEW for 098rc5
?>
