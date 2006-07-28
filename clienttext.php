<?php
/*
	#############################################################
	# >>> PHPSurveyor  											#
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

// Todo: This whole file should be removed at a later time -
// Future text can be directly written in to the code
// DO NOT ADD ANYMORE CODE TO THIS FILE


define("_TOKENS", _("Tokens"));
define("_FEMALE", _("Female"));
define("_MALE", _("Male"));
define("_NOANSWER", _("No answer"));
define("_NOTAPPLICABLE", _("N/A")); //New for 0.98rc)5
define("_PLEASECHOOSE", _("Please choose"));
define("_ERROR_PS", _("Error"));
define("_COMPLETE", _("complete"));
define("_INCREASE", _("Increase")); //NEW WITH 0.9)8
define("_SAME", _("Same")); //NEW WITH 0.9)8
define("_DECREASE", _("Decrease")); //NEW WITH 0.9)8
define("_REQUIRED", _("<font color='red'>*</font>")); //NEW WITH 0.99dev0)1
//from questions.php
define("_CONFIRMATION", _("Confirmation"));
define("_TOKEN_PS", _("Token"));
define("_CONTINUE_PS", _("Continue"));

//BUTTONS
define("_ACCEPT", _("Accept"));
define("_PREV", _("prev"));
define("_NEXT", _("next"));
define("_LAST", _("last"));
define("_SUBMIT", _("submit"));


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", _("Please choose one of the following"));
define("_ENTERCOMMENT", _("Please enter your comment here"));
define("_NUMERICAL_PS", _("Only numbers may be entered in this field"));
define("_CLEARALL", _("Exit and Clear Survey"));
define("_MANDATORY", _("This question is mandatory"));
define("_MANDATORY_PARTS", _("Please complete all parts"));
define("_MANDATORY_CHECK", _("Please check at least one item"));
define("_MANDATORY_RANK", _("Please rank all items"));
define("_MANDATORY_POPUP", _("One or more mandatory questions have not been answered. You cannot proceed until these have been completed")); //NEW in 0.98rc)4
define("_VALIDATION", _("This question must be answered correctly")); //NEW in VALIDATION VERSIO)N
define("_VALIDATION_POPUP", _("One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid")); //NEW in VALIDATION VERSIO)N
define("_DATEFORMAT", _("Format: YYYY-MM-DD"));
define("_DATEFORMATEG", _("(eg: 2003-12-25 for Christmas day)"));
define("_REMOVEITEM", _("Remove this item"));
define("_RANK_1", _("Click on an item in the list on the left, starting with your"));
define("_RANK_2", _("highest ranking item, moving through to your lowest ranking item."));
define("_YOURCHOICES", _("Your Choices"));
define("_YOURRANKING", _("Your Ranking"));
define("_RANK_3", _("Click on the scissors next to each item on the right"));
define("_RANK_4", _("to remove the last entry in your ranked list"));
//From INDEX.PHP
define("_NOSID", _("You have not provided a survey identification number"));
define("_CONTACT1", _("Please contact"));
define("_CONTACT2", _("for further assistance"));
define("_ANSCLEAR", _("Answers Cleared"));
define("_RESTART", _("Restart this Survey"));
define("_CLOSEWIN_PS", _("Close this Window"));
define("_CONFIRMCLEAR", _("Are you sure you want to clear all your responses?"));
define("_CONFIRMSAVE", _("Are you sure you want to save your responses?"));
define("_EXITCLEAR", _("Exit and Clear Survey"));
//From QUESTION.PHP
define("_BADSUBMIT1", _("Cannot submit results - there are none to submit."));
define("_BADSUBMIT2", _("This error can occur if you have already submitted your responses and pressed 'refresh' on your browser. In this case, your responses have already been saved.<br /><br />If you receive this message in the middle of completing a survey, you should choose '<- BACK' on your browser and then refresh/reload the previous page. While you will lose answers from the last page all your others will still exist. This problem can occur if the webserver is suffering from overload or excessive use. We apologise for this problem."));
define("_NOTACTIVE1", _("Your survey responses have not been recorded. This survey is not yet active."));
define("_CLEARRESP", _("Clear Responses"));
define("_THANKS", _("Thank you"));
define("_SURVEYREC", _("Your survey responses have been recorded."));
define("_SURVEYCPL", _("Survey Completed"));
define("_DIDNOTSAVE", _("Did Not Save"));
define("_DIDNOTSAVE2", _("An unexpected error has occurred and your responses cannot be saved."));
define("_DIDNOTSAVE3", _("Your responses have not been lost and have been emailed to the survey administrator and will be entered into our database at a later point."));
define("_DNSAVEEMAIL1", _("An error occurred saving a response to survey id"));
define("_DNSAVEEMAIL2", _("DATA TO BE ENTERED"));
define("_DNSAVEEMAIL3", _("SQL CODE THAT FAILED"));
define("_DNSAVEEMAIL4", _("ERROR MESSAGE"));
define("_DNSAVEEMAIL5", _("Error saving survey results to database"));
define("_SUBMITAGAIN", _("Try to submit again"));
define("_SURVEYNOEXIST", _("Sorry. There is no matching survey."));
define("_NOTOKEN1", _("This is a controlled survey. You need a valid token to participate."));
define("_NOTOKEN2", _("If you have been issued with a token, please enter it in the box below and click continue."));
define("_NOTOKEN3", _("The token you have provided is either not valid, or has already been used."));
define("_NOQUESTIONS", _("This survey does not yet have any questions and cannot be tested or completed."));
define("_FURTHERINFO", _("For further information contact"));
define("_NOTACTIVE", _("This survey is not currently active. You will not be able to save your responses."));
define("_SURVEYEXPIRED", _("This survey is no longer available."));

define("_SURVEYCOMPLETE", _("You have already completed this survey.")); //NEW FOR 0.98rc)6

define("_INSTRUCTION_LIST", _("Choose only one of the following")); //NEW for 098rc)3
define("_INSTRUCTION_MULTI", _("Check any that apply")); //NEW for 098rc)3

define("_CONFIRMATION_MESSAGE1", _("Survey Submitted")); //NEW for 098rc)5
define("_CONFIRMATION_MESSAGE2", _("A new response was entered for your survey")); //NEW for 098rc)5
define("_CONFIRMATION_MESSAGE3", _("Click the following link to see the individual response:")); //NEW for 098rc)5
define("_CONFIRMATION_MESSAGE4", _("View statistics by clicking here:")); //NEW for 098rc)5
define("_CONFIRMATION_MESSAGE5", _("Click the following link to edit the individual response:")); //NEW for 0.99stabl)e

define("_PRIVACY_MESSAGE", _("(<strong><i>A Note On Privacy</i></strong><br />"
						  ."This survey is anonymous.<br />"
						  ."The record kept of your survey responses does not contain any "
						  ."identifying information about you unless a specific question "
						  ."in the survey has asked for this. If you have responded to a "
						  ."survey that used an identifying token to allow you to access "
						  ."the survey, you can rest assured that the identifying token "
						  ."is not kept with your responses. It is managed in a separate "
						  ."database, and will only be updated to indicate that you have "
						  ."(or haven't) completed this survey. There is no way of matching "
						  ."identification tokens with survey responses in this survey.")); //New for 0.98rc9

define("_THEREAREXQUESTIONS", _("There are {NUMBEROFQUESTIONS} questions in this survey.")); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count).
define("_THEREAREXQUESTIONS_SINGLE", _("There is 1 question in this survey.")); //New for 0.98rc9 - singular version of abov)e
						  
define ("_RG_REGISTER1", _("You must be registered to complete this survey")); //NEW for 0.98rc)9
define ("_RG_REGISTER2", _("You may register for this survey if you wish to take part.<br />\n)"
						."Enter your details below, and an email containing the link to "
						."participate in this survey will be sent immediately.")); //NEW for 0.98rc9
define ("_RG_EMAIL", _("Email Address")); //NEW for 0.98rc)9
define ("_RG_FIRSTNAME", _("First Name")); //NEW for 0.98rc)9
define ("_RG_LASTNAME", _("Last Name")); //NEW for 0.98rc)9
define ("_RG_INVALIDEMAIL", _("The email you used is not valid. Please try again."));//NEW for 0.98rc)9
define ("_RG_USEDEMAIL", _("The email you used has already been registered."));//NEW for 0.98rc)9
define ("_RG_EMAILSUBJECT", _("{SURVEYNAME} Registration Confirmation"));//NEW for 0.98rc)9
define ("_RG_REGISTRATIONCOMPLETE", _("Thank you for registering to participate in this survey.<br /><br />\n)"
								   ."An email has been sent to the address you provided with access details "
								   ."for this survey. Please follow the link in that email to proceed.<br /><br />\n"
								   ."Survey Administrator {ADMINNAME} ({ADMINEMAIL})"));//NEW for 0.98rc9

define("_SM_COMPLETED", _("<strong>Thank You<br /><br />)"
					   ."You have completed answering the questions in this survey.</strong><br /><br />"
					   ."Click on ["._SUBMIT."] now to complete the process and save your answers."));
define("_SM_REVIEW", _("If you want to check any of the answers you have made, and/or change them, )"
					."you can do that now by clicking on the [<< "._PREV."] button and browsing "
					."through your responses."));

//For the "printable" survey
define("_PS_CHOOSEONE", _("Please choose <strong>only one</strong> of the following:")); //New for 0.98finalRC)1
define("_PS_WRITE", _("Please write your answer here:")); //New for 0.98finalRC)1
define("_PS_CHOOSEANY", _("Please choose <strong>all</strong> that apply:")); //New for 0.98finalRC)1
define("_PS_CHOOSEANYCOMMENT", _("Please choose all that apply and provide a comment:")); //New for 0.98finalRC)1
define("_PS_EACHITEM", _("Please choose the appropriate response for each item:")); //New for 0.98finalRC)1
define("_PS_WRITEMULTI", _("Please write your answer(s) here:")); //New for 0.98finalRC)1
define("_PS_DATE", _("Please enter a date:")); //New for 0.98finalRC)1
define("_PS_COMMENT", _("Make a comment on your choice here:")); //New for 0.98finalRC)1
define("_PS_RANKING", _("Please number each box in order of preference from 1 to")); //New for 0.98finalRC)1
define("_PS_SUBMIT", _("Submit Your Survey.")); //New for 0.98finalRC)1
define("_PS_THANKYOU", _("Thank you for completing this survey.")); //New for 0.98finalRC)1
define("_PS_FAXTO", _("Please fax your completed survey to:")); //New for 0.98finaclRC)1

define("_PS_CON_ONLYANSWER", _("Only answer this question")); //New for 0.98finalRC)1
define("_PS_CON_IFYOU", _("if you answered")); //New for 0.98finalRC)1
define("_PS_CON_JOINER", _("and")); //New for 0.98finalRC)1
define("_PS_CON_TOQUESTION", _("to question")); //New for 0.98finalRC)1
define("_PS_CON_OR", _("or")); //New for 0.98finalRC)2

//Save Messages
define("_SAVE_AND_RETURN", _("Save your responses so far"));
define("_SAVEHEADING", _("Save Your Unfinished Survey"));
define("_RETURNTOSURVEY", _("Return To Survey"));
define("_SAVENAME", _("Name"));
define("_SAVEPASSWORD", _("Password"));
define("_SAVEPASSWORDRPT", _("Repeat Password"));
define("_SAVE_EMAIL", _("Your Email"));
define("_SAVEEXPLANATION", _("Enter a name and password for this survey and click save below.<br />\n)"
				  ."Your survey will be saved using that name and password, and can be "
				  ."completed later by logging in with the same name and password.<br /><br />\n"
				  ."If you give an email address, an email containing the details will be sent "
				  ."to you."));
define("_SAVESUBMIT", _("Save Now"));
define("_SAVENONAME", _("You must supply a name for this saved session."));
define("_SAVENOPASS", _("You must supply a password for this saved session."));
define("_SAVENOPASS2", _("You must re-enter a password for this saved session."));
define("_SAVENOMATCH", _("Your passwords do not match."));
define("_SAVEDUPLICATE", _("This name has already been used for this survey. You must use a unique save name."));
define("_SAVETRYAGAIN", _("Please try again."));
define("_SAVE_EMAILSUBJECT", _("Saved Survey Details"));
define("_SAVE_EMAILTEXT", _("You, or someone using your email address, have saved "
						 ."a survey in progress. The following details can be used "
						 ."to return to this survey and continue where you left "
						 ."off."));
define("_SAVE_EMAILURL", _("Reload your survey by clicking on the following URL:"));
define("_SAVE_SUCCEEDED", _("Your survey responses have been saved succesfully"));
define("_SAVE_FAILED", _("An error occurred and your survey responses were not saved."));
define("_SAVE_EMAILSENT", _("An email has been sent with details about your saved survey."));

//Load Messages
define("_LOAD_SAVED", _("Load unfinished survey"));
define("_LOADHEADING", _("Load A Previously Saved Survey"));
define("_LOADEXPLANATION", _("You can load a survey that you have previously saved from this screen.<br />\n"
			                ."Type in the 'name' you used to save the survey, and the password.<br /><br />\n"));
define("_LOADNAME", _("Saved name"));
define("_LOADPASSWORD", _("Password"));
define("_LOADSUBMIT", _("Load Now"));
define("_LOADNONAME", _("You did not provide a name"));
define("_LOADNOPASS", _("You did not provide a password"));
define("_LOADNOMATCH", _("There is no matching saved survey"));

define("_ASSESSMENT_HEADING", _("Your Assessment"));
?>
