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
	#															#
	# This language file kindly provided by Ulrika Olsson		#
	#															#
	# Updated for 0.98rc9 and slightly edited by				#
	# Björn Mildh - bjorn at mildh dot se - 2004-06-30			#
	#															#
	#############################################################
*/
//SINGLE WORDS
define("_YES", "Ja");
define("_NO", "Nej");
define("_UNCERTAIN", "Vet ej");
define("_ADMIN", "Admin");
define("_TOKENS", "Behörighetskoder");
define("_FEMALE", "Kvinna");
define("_MALE", "Man");
define("_NOANSWER", "Inget svar");
define("_NOTAPPLICABLE", "N/A"); //New for 0.98rc5 (Det finns ingen förkortning av Ej tillämpbar)
define("_OTHER", "Annat");
define("_PLEASECHOOSE", "Välj");
define("_ERROR_PS", "Fel");
define("_COMPLETE", "komplett");
//from questions.php
define("_CONFIRMATION", "Bekräftelse");
define("_TOKEN_PS", "Behörighetskod");
define("_CONTINUE_PS", "Fortsätt");
define("_INCREASE", "Öka"); //NEW WITH 0.98
define("_SAME", "Samma"); //NEW WITH 0.98
define("_DECREASE", "Minska"); //NEW WITH 0.98
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01

//BUTTONS
define("_ACCEPT", "Acceptera");
define("_PREV", "föreg.");
define("_NEXT", "nästa");
define("_LAST", "sista");
define("_SUBMIT", "skicka");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Välj ett av de följande");
define("_ENTERCOMMENT", "Skriv din kommentar här");
define("_NUMERICAL_PS", "Endast nummer kan skrivas i detta fält");
define("_CLEARALL", "Lämna och rensa enkäten");
define("_MANDATORY", "Denna fråga är obligatorisk");
define("_MANDATORY_PARTS", "Du måste fylla i alla delar");
define("_MANDATORY_CHECK", "Välj minst ett objekt");
define("_MANDATORY_RANK", "Rangordna alla alternativen");
define("_MANDATORY_POPUP", "En eller flera obligatoriska frågor har inte besvarats. Du kan inte fortsätta innan de är besvarade"); //NEW in 0.98rc4
define("_VALIDATION", "This question must be answered correctly"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Format: ÅÅÅÅ-MM-DD");
define("_DATEFORMATEG", "(tex: 2004-12-24 för Julafton)");
define("_REMOVEITEM", "Ta bort detta objekt");
define("_RANK_1", "Klicka på ett objekt i listan till vänster, börja med ditt");
define("_RANK_2", "högst rankade objekt, upprepa tills ditt lägst rankade objekt.");
define("_YOURCHOICES", "Dina val");
define("_YOURRANKING", "Din rangordning");
define("_RANK_3", "Klicka på saxen till höger om objektet");
define("_RANK_4", "för att ta bort det sist elementet i listan.");
//From INDEX.PHP
define("_NOSID", "Du har inte angett ett id-nummer för enkäten");
define("_CONTACT1", "Var god kontakta");
define("_CONTACT2", "för ytterligare assistans");
define("_ANSCLEAR", "Svaren rensade");
define("_RESTART", "Starta om enkäten");
define("_CLOSEWIN_PS", "Stäng fönstret");
define("_CONFIRMCLEAR", "Är du säker på att du vill rensa dina svar?");
define("_CONFIRMSAVE", "Are you sure you want to save your responses?");
define("_EXITCLEAR", "Lämna och rensa enkäten");
//From QUESTION.PHP
define("_BADSUBMIT1", "Kan inte skicka resultaten - det finns inga att skicka.");
define("_BADSUBMIT2", "Detta fel kan uppstå om du redan har skickat dina svar och klickat på 'uppdatera' på din webbläsare. I så fall så är dina svar redan sparade.");
define("_NOTACTIVE1", "Dina enkätsvar är inte sparade. Denna enkät är inte aktiviverad ännu.");
define("_CLEARRESP", "Rensa svaren");
define("_THANKS", "Tack");
define("_SURVEYREC", "Dina enkätsvar är sparade.");
define("_SURVEYCPL", "Enkäten klar");
define("_DIDNOTSAVE", "Sparade inte");
define("_DIDNOTSAVE2", "Ett oväntat fel har uppstått och dina svar kan inte sparas.");
define("_DIDNOTSAVE3", "Dina svar har inte försvunnit, utan de har mailats till enkätadministratören och kommer att läggas in i databasen vid ett senare tillfälle.");
define("_DNSAVEEMAIL1", "Ett fel uppstod under försök att spara svaret till enkät-id");
define("_DNSAVEEMAIL2", "DATA SKALL FYLLAS I");
define("_DNSAVEEMAIL3", "SQL-KOD SOM HAR MISSLYCKATS");
define("_DNSAVEEMAIL4", "FELMEDDELANDE");
define("_DNSAVEEMAIL5", "FEL VID SPARANDET");
define("_SUBMITAGAIN", "Försök att skicka igen");
define("_SURVEYNOEXIST", "Tyvärr. Det finns ingen matchade enkät.");
define("_NOTOKEN1", "Detta är en kontrollerad enkät. Du behöver en giltlig behörigetskod för att delta");
define("_NOTOKEN2", "Om du har fått en behörighetskod, skriv in den i rutan nedan och fortsätt.");
define("_NOTOKEN3", "Behörighetskoden som du angett är antingen ogiltlig eller redan använd.");
define("_NOQUESTIONS", "Denna enkät har ännu inga frågor och kan inte testas eller färdigställas.");
define("_FURTHERINFO", "För ytterligare information kontakta");
define("_NOTACTIVE", "Denna enkät är inte aktiv för tillfället. Du kan därför inte spara dina svar.");
define("_SURVEYEXPIRED", "Denna enkät är inte längre tillgänglig."); //NEW for 098rc5

define("_SURVEYCOMPLETE", "Du har redan svarat på den här enkäten.");

define("_INSTRUCTION_LIST", "Välj bara en av följande"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Välj vilka som stämmer"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Enkäten skickad"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Ett nytt svar till din enkät har lämnats"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Se det enskilda svaret genom att klicka här:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Se statistik för enkäten genom att klicka här:"); //NEW for 098rc5

define("_PRIVACY_MESSAGE", "<b><i>Hantering av personuppgifter. </i></b><br />"
						  ."Den här enkäten är anonym.<br />"
						  ."De svar på enkäten som sparas innehåller ingen information som "
						  ."kan identifiera den som svarat utom om denna fråga specifikt ställts "
						  ."i enkäten. Även om det krävs ett id-nummer för att kunna besvara "
						  ."enkäten sparas inte denna personliga information tillsammans med "
						  ."enkätsvaret. Id-numret används endast för att avgöra om du har "
						  ."svarat (eller inte svarat) på enkäten och den informationen sparas "
						  ."separat. Det finns inget sätt att avgöra vilket id-nummer som hör "
						  ."ihop med ett visst svar i den här enkäten."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "Den här undersökningen innehåller {NUMBEROFQUESTIONS} frågor."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Det finns 1 fråga i enkäten."); //New for 0.98rc9 - singular version of above

define ("_RG_REGISTER1", "Du måste vara registrerad för att genomföra den här enkäten"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Du måste registrera dig innan du fyller i den här enkäten.<br />\n"
						."Fyll i dina uppgifter nedan och så skickas en länk till "
						."enkäten till dig med e-post genast."); //NEW for 0.98rc9
define ("_RG_EMAIL", "E-postadress"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Förnamn"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Efternamn"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "E-postadressen du angav är inte giltig. Var vänlig försök igen.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "Din e-postadress har redan anmälts.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} Bekräftelse på registrering");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Tack för att du registerat dig för att genomföra den här enkäten.<br /><br />\n"
								   ."Ett e-postmeddelande med dina uppgifter har sänts till den adress du angav."
								   ."Följ den bifogade länken i e-postmeddelandet för att fortsätta.<br /><br />\n"
								   ."Enkät-ansvarig {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<b>Tack!<br /><br />"
	."Du har besvarat alla frågor i den här enkäten.</b><br /><br />"
	."Klicka på ["._SUBMIT."] för att slutföra och spara dina svar."); //New for 0.98finalRC1 - by Bjorn Mildh
define("_SM_REVIEW", "Om du vill kontrollera dina svar och/eller ändra dem, "
	."kan du göra det genom att klicka på [<< "._PREV."]-knappen och bläddra "
	."genom dina svar."); //New for 0.98finalRC1 - by Bjorn Mildh

//For the "printable" survey
define("_PS_CHOOSEONE", "Välj <b>endast en</b> av följande:"); //New for 0.98finalRC1
define("_PS_WRITE", "Skriv ditt svar här:"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Välj <b>alla</b> som stämmer:"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Välj alla som stämmer och skriv en kommentar:"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Välj det korrekta svaret för varje punkt:"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Skriv ditt/dina svar här:"); //New for 0.98finalRC1
define("_PS_DATE", "Fyll i datum:"); //New for 0.98finalRC1
define("_PS_COMMENT", "Kommentera dina val här:"); //New for 0.98finalRC1
define("_PS_RANKING", "Rangordna i varje ruta med ett nummer från 1 till"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Lämna in din enkät."); //New for 0.98finalRC1
define("_PS_THANKYOU", "Tack för att du svarat på denna enkät."); //New for 0.98finalRC1
define("_PS_FAXTO", "Faxa den ifyllda enkäten till:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Svara bara på denna fråga"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "om du svarat"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "och"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "på fråga"); //New for 0.98finalRC1
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
?>
