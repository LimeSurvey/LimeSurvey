<?php
/*
	#############################################################
	# >>> PHPSurveyor  										#
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
	#							    							#
	# Hungarian Language File				    				#
	# Created by David Selmeczi 						    	#
	#							    							#
	#############################################################
*/
//SINGLE WORDS
define("_YES", "Igen");
define("_NO", "Nem");
define("_UNCERTAIN", "Nem tudom");
define("_ADMIN", "Admin");
define("_TOKENS", "K&oacute;dok");
define("_FEMALE", "N&otilde;");
define("_MALE", "F&eacute;rfi");
define("_NOANSWER", "Nincs v&aacute;lasz");
define("_NOTAPPLICABLE", "???"); //New for 0.98rc5
define("_OTHER", "M&aacute;s");
define("_PLEASECHOOSE", "K&eacute;rem v&aacute;lasszon");
define("_ERROR_PS", "Hiba");
define("_COMPLETE", "teljes");
define("_INCREASE", "N&ouml;vel"); //NEW WITH 0.98
define("_SAME", "Ugyanaz"); //NEW WITH 0.98
define("_DECREASE", "Cs&ouml;kkent"); //NEW WITH 0.98
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Meger&otilde;s&iacute;t&eacute;s");
define("_TOKEN_PS", "K&oacute;d");
define("_CONTINUE_PS", "Folytat&aacute;s");

//BUTTONS
define("_ACCEPT", "Elfogadom");
define("_PREV", "elõzõ");
define("_NEXT", "következõ");
define("_LAST", "utolsó");
define("_SUBMIT", "Elküldöm");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "K&eacute;rem v&aacute;lsszon egyet az al&aacute;bbiak k&ouml;z&uuml;l");
define("_ENTERCOMMENT", "Az &Ouml;n megjegyz&eacute;se ehhez");
define("_NUMERICAL_PS", "Ebbe a mez&otilde;be csak sz&aacute;mokat &iacute;rhat");
define("_CLEARALL", "Kil&eacute;p&eacute;s &eacute;s a k&eacute;rd&otilde;&iacute;v t&ouml;rl&eacute;se");
define("_MANDATORY", "Erre a k&eacute;rd&eacute;sre k&ouml;telez&otilde; v&aacute;lszolni");
define("_MANDATORY_PARTS", "K&eacute;rem t&ouml;lts&ouml;n ki mindent");
define("_MANDATORY_CHECK", "Jel&ouml;lj&ouml;n be legal&aacute;bb egy v&aacute;lszt");
define("_MANDATORY_RANK", "Rangsorolja az &ouml;sszeset");
define("_MANDATORY_POPUP", "Legal&aacute;bb egy k&ouml;telez&otilde;en kit&ouml;ltend&otilde; k&eacute;rd&eacute;sre nem v&aacute;lszolt. Addig nem l&eacute;phet tov&aacute;bb, am&iacute;g ezeket nem t&ouml;lti ki!"); //NEW in 0.98rc4
define("_VALIDATION", "This question must be answered correctly"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "D&aacute;tum form&aacute;tum: &Eacute;&Eacute;&Eacute;&Eacute;-HH-NN");
define("_DATEFORMATEG", "(pl: kar&aacute;csony napja: 2003-12-25)");
define("_REMOVEITEM", "E t&eacute;tel elt&aacute;vol&iacute;t&aacute;sa");
define("_RANK_1", "A bal oldali list&aacute;ban kattintson el&otilde;sz&ouml;r a legfontosabbra,");
define("_RANK_2", "majd sorban a legkev&eacute;sb&eacute; fontosig az &ouml;sszesre");
define("_YOURCHOICES", "Lehet&otilde;s&eacute;gek");
define("_YOURRANKING", "Az &Ouml;n rangsora");
define("_RANK_3", "Egy t&eacute;tel elt&aacute;vol&iacute;t&aacute;s&aacute;hoz kattintson a mellette tal&aacute;lhat&oacute;");
define("_RANK_4", "oll&oacute;ra. Így az utols&oacute; t&eacute;tel leker&uuml;l a list&aacute;r&oacute;l");
//From INDEX.PHP
define("_NOSID", "Nem adott meg k&eacute;rd&otilde;&iacute;v-azonos&iacute;t&oacute;t");
define("_CONTACT1", "A tov&aacute;bbi teend&otilde;k &uuml;gy&eacute;ben vegye fel a kapcsolatot:");
define("_CONTACT2", "");
define("_ANSCLEAR", "A v&aacute;laszok t&ouml;r&ouml;lve");
define("_RESTART", "A k&eacute;rd&otilde;&iacute;v újrakezd&eacute;se");
define("_CLOSEWIN_PS", "Ablak bez&aacute;r&aacute;sa");
define("_CONFIRMCLEAR", "Biztosan t&ouml;r&ouml;lni akarja a v&aacute;laszait?");
define("_CONFIRMSAVE", "Are you sure you want to save your responses?");
define("_EXITCLEAR", "Kil&eacute;p&eacute;s &eacute;s a k&eacute;rd&otilde;&iacute;v t&ouml;rl&eacute;se");
//From QUESTION.PHP
define("_BADSUBMIT1", "Nem tudom elk&uuml;ldeni az eredm&eacute;nyeket, mert nincsenek v&aacute;laszok.");
define("_BADSUBMIT2", "Ez a hiba akkor fordul el&otilde;, ha m&aacute;r elk&uuml;ldte a v&aacute;laszait &eacute;s ut&aacute;na megnyomta a 'Friss&iacute;t&eacute;s' gombot a b&ouml;ng&eacute;sz&otilde;n. Ebben az esetben a v&aacute;laszai m&aacute;r el vannak k&uuml;ldve.<br /><br />Ha viszont ezt a hib&aacute;t a k&eacute;rd&otilde;&iacute;v kit&ouml;lt&eacute;se k&ouml;zben kapta, akkor nyomja meg a b&ouml;ng&eacute;sz&otilde; '<- VISSZA/BACK' gombj&aacute;t, &eacute;s az &iacute;gy megjelen&otilde; oldalt friss&iacute;tse. Így az utols&oacute; oldal v&aacute;laszait elveszti, de minden el&otilde;z&otilde; megmarad. Ez a hiba akkor szokott el&otilde;fordulni, ha a szerver túl van terhelve. Eln&eacute;z&eacute;st k&eacute;r&uuml;nk a kellemetlens&eacute;g&eacute;rt.");
define("_NOTACTIVE1", "Your survey responses have not been recorded. This survey is not yet active.");
define("_CLEARRESP", "V&aacute;laszok t&ouml;rl&eacute;se");
define("_THANKS", "K&ouml;sz&ouml;nj&uuml;k");
define("_SURVEYREC", "V&aacute;laszait r&ouml;gz&iacute;tett&uuml;k");
define("_SURVEYCPL", "V&eacute;ge a k&eacute;rd&otilde;&iacute;vnek");
define("_DIDNOTSAVE", "Nem siker&uuml;lt elmenteni");
define("_DIDNOTSAVE2", "V&aacute;ratlan hiba k&ouml;vetkezett be, v&aacute;laszait nem siker&uuml;lt r&ouml;gz&iacute;teni.");
define("_DIDNOTSAVE3", "De a bevitt adatok nem vesztek el, hanem emailben tov&aacute;bb&iacute;tottuk a rendszer karbantart&oacute;j&aacute;nak, aki k&eacute;s&otilde;bb ezeket be fogja vinni az adatb&aacute;zisba.");
define("_DNSAVEEMAIL1", "Hiba l&eacute;pett fel a k&ouml;vetkez&otilde; k&eacute;rd&otilde;&iacute;v r&ouml;gz&iacute;t&eacute;sekor:");
define("_DNSAVEEMAIL2", "DATA TO BE ENTERED");
define("_DNSAVEEMAIL3", "SQL CODE THAT FAILED");
define("_DNSAVEEMAIL4", "ERROR MESSAGE");
define("_DNSAVEEMAIL5", "ERROR SAVING");
define("_SUBMITAGAIN", "Pr&oacute;b&aacute;lja meg újra elk&uuml;ldeni");
define("_SURVEYNOEXIST", "Nincs ilyen k&eacute;rd&otilde;&iacute;v.");
define("_NOTOKEN1", "Ez a k&eacute;rd&otilde;&iacute;v z&aacute;rtk&ouml;rû, a felm&eacute;r&eacute;sben val&oacute; r&eacute;szv&eacute;telehez egy k&oacute;dra van sz&uuml;ks&eacute;ge.");
define("_NOTOKEN2", "Ha kapott ilyen k&oacute;dot, &iacute;rja be az al&aacute;bbi mez&otilde;be, majd kattintson a 'Tov&aacute;bb' gombra.");
define("_NOTOKEN3", "A megadott k&oacute;d &eacute;rv&eacute;nytelen vagy m&aacute;r valaki felhaszn&aacute;lta egy k&eacute;rd&otilde;&iacute;v kit&ouml;lt&eacute;s&eacute;hez.");
define("_NOQUESTIONS", "Ez a k&eacute;rd&otilde;&iacute;v egyel&otilde;re nem tartalmaz k&eacute;rd&eacute;seket, ez&eacute;rt nem lehet kipr&oacute;b&aacute;lni vagy kit&ouml;lteni.");
define("_FURTHERINFO", "Tov&aacute;bbi inform&aacute;ci&oacute;:");
define("_NOTACTIVE", "Ez a k&eacute;rd&otilde;&iacute;v egyel&otilde;re nem akt&iacute;v, ez&eacute;rt a v&aacute;laszokat nem lehet elmenteni.");
define("_SURVEYEXPIRED", "Ez a k&eacute;rd&otilde;&iacute;v m&aacute;r lej&aacute;rt, nem lehet kit&ouml;lteni.");

define("_SURVEYCOMPLETE", "A k&eacute;rd&otilde;&iacute;vet m&aacute;r kit&ouml;lt&ouml;tte egyszer"); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "V&aacute;lasszon egyet az al&aacute;bbiak k&ouml;z&uuml;l"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "V&aacute;lasszon ki egyet vagy t&ouml;bbet az al&aacute;bbiak k&ouml;z&uuml;l"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Kit&ouml;lt&ouml;tt k&eacute;rd&otilde;&iacute;v &eacute;rkezett"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "V&aacute;laszok &eacute;rkeztek a k&ouml;vetkez&otilde; k&eacute;rd&otilde;&iacute;vhez"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Kattintson ide e k&eacute;rd&otilde;&iacute;v megtekint&eacute;s&eacute;hez:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Itt tekintheti meg a statisztik&aacute;kat:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE5", "Click the following link to edit the individual response:"); //NEW for 0.99stable

define("_PRIVACY_MESSAGE", "<strong><i>A Note On Privacy</i></strong><br />"
						  ."This survey is anonymous.<br />"
						  ."The record kept of your survey responses does not contain any "
						  ."identifying information about you unless a specific question "
						  ."in the survey has asked for this. If you have responded to a "
						  ."survey that used an identifying token to allow you to access "
						  ."the survey, you can rest assured that the identifying token "
						  ."is not kept with your responses. It is managed in a separate "
						  ."database, and will only be updated to indicate that you have "
						  ."(or haven't) completed this survey. There is no way of matching "
						  ."identification tokens with survey responses in this survey."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "There are {NUMBEROFQUESTIONS} questions in this survey."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "There is 1 question in this survey."); //New for 0.98rc9 - singular version of above

define ("_RG_REGISTER1", "You must be registered to complete this survey"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "You may register for this survey if you wish to take part.<br />\n"
						."Enter your details below, and an email containing the link to "
						."participate in this survey will be sent immediately."); //NEW for 0.98rc9
define ("_RG_EMAIL", "Email Address"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "First Name"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Last Name"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "The email you used is not valid. Please try again.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "The email you used has already been registered.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} Registration Confirmation");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Thank you for registering to participate in this survey.<br /><br />\n"
								   ."An email has been sent to the address you provided with access details "
								   ."for this survey. Please follow the link in that email to proceed.<br /><br />\n"
								   ."Survey Administrator {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<strong>Thank You<br /><br />"
					   ."You have completed answering the questions in this survey.</strong><br /><br />"
					   ."Click on ["._SUBMIT."] now to complete the process and save your answers."); //New for 0.98finalRC1
define("_SM_REVIEW", "If you want to check any of the answers you have made, and/or change them, "
					."you can do that now by clicking on the [<< "._PREV."] button and browsing "
					."through your responses."); //New for 0.98finalRC1

//For the "printable" survey
define("_PS_CHOOSEONE", "Please choose <strong>only one</strong> of the following:"); //New for 0.98finalRC1
define("_PS_WRITE", "Please write your answer here:"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Please choose <strong>all</strong> that apply:"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Please choose all that apply and provide a comment:"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Please choose the appropriate response for each item:"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Please write your answer(s) here:"); //New for 0.98finalRC1
define("_PS_DATE", "Please enter a date:"); //New for 0.98finalRC1
define("_PS_COMMENT", "Make a comment on your choice here:"); //New for 0.98finalRC1
define("_PS_RANKING", "Please number each box in order of preference from 1 to"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Submit Your Survey."); //New for 0.98finalRC1
define("_PS_THANKYOU", "Thank you for completing this survey."); //New for 0.98finalRC1
define("_PS_FAXTO", "Please fax your completed survey to:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Only answer this question"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "if you answered"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "and"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "to question"); //New for 0.98finalRC1
define("_PS_CON_OR", "or"); //New for 0.98finalRC2

//Save Messages
define("_SAVE_AND_RETURN", "Save your responses so far");
define("_SAVEHEADING", "Save Your Unfinished Survey");
define("_RETURNTOSURVEY", "Return To Survey");
define("_SAVENAME", "Name");
define("_SAVEPASSWORD", "Password");
define("_SAVEPASSWORDRPT", "Repeat Password");
define("_SAVE_EMAIL", "Your Email");
define("_SAVEEXPLANATION", "Enter a name and password for this survey and click save below.<br />\n"
				  ."Your survey will be saved using that name and password, and can be "
				  ."completed later by logging in with the same name and password.<br /><br />\n"
				  ."If you give an email address, an email containing the details will be sent "
				  ."to you.");
define("_SAVESUBMIT", "Save Now");
define("_SAVENONAME", "You must supply a name for this saved session.");
define("_SAVENOPASS", "You must supply a password for this saved session.");
define("_SAVENOMATCH", "Your passwords do not match.");
define("_SAVEDUPLICATE", "This name has already been used for this survey. You must use a unique save name.");
define("_SAVETRYAGAIN", "Please try again.");
define("_SAVE_EMAILSUBJECT", "Saved Survey Details");
define("_SAVE_EMAILTEXT", "You, or someone using your email address, have saved "
						 ."a survey in progress. The following details can be used "
						 ."to return to this survey and continue where you left "
						 ."off.");
define("_SAVE_EMAILURL", "Reload your survey by clicking on the following URL:");
define("_SAVE_SUCCEEDED", "Your survey responses have been saved succesfully");
define("_SAVE_FAILED", "An error occurred and your survey responses were not saved.");
define("_SAVE_EMAILSENT", "An email has been sent with details about your saved survey.");

//Load Messages
define("_LOAD_SAVED", "Load unfinished survey");
define("_LOADHEADING", "Load A Previously Saved Survey");
define("_LOADEXPLANATION", "You can load a survey that you have previously saved from this screen.<br />\n"
			  ."Type in the 'name' you used to save the survey, and the password.<br /><br />\n");
define("_LOADNAME", "Saved name");
define("_LOADPASSWORD", "Password");
define("_LOADSUBMIT", "Load Now");
define("_LOADNONAME", "You did not provide a name");
define("_LOADNOPASS", "You did not provide a password");
define("_LOADNOMATCH", "There is no matching saved survey");

define("_ASSESSMENT_HEADING", "Your Assessment");
?>
