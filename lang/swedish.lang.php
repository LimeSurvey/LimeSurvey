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
define("_NOTAPPLICABLE", "N/A"); //New for 0.98rc5
define("_OTHER", "Annat");
define("_PLEASECHOOSE", "Välj");
define("_ERROR", "Fel");
define("_COMPLETE", "complete");
//from questions.php
define("_CONFIRMATION", "Bekräftelse");
define("_TOKEN", "Behörighetskod");
define("_CONTINUE", "Fortsätt");
define("_INCREASE", "Increase"); //NEW WITH 0.98
define("_SAME", "Same"); //NEW WITH 0.98
define("_DECREASE", "Decrease"); //NEW WITH 0.98

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
define("_NUMERICAL", "Endast nummer kan skrivas i detta fält");
define("_CLEARALL", "Lämna och rensa enkäten");
define("_MANDATORY", "Denna fråga är obligatorisk");
define("_MANDATORY_PARTS", "Var god, fyll i alla delar");
define("_MANDATORY_CHECK", "Välj minst ett objekt");
define("_MANDATORY_RANK", "Rangordna alla alternativen");
define("_MANDATORY_POPUP", "One or more mandatory questions have not been answered. You cannot proceed until these have been completed"); //NEW in 0.98rc4
define("_DATEFORMAT", "Format: ÅÅÅÅ-MM-DD");
define("_DATEFORMATEG", "(tex: 2003-12-24 för Julafton)");
define("_REMOVEITEM", "Ta bort detta objekt");
define("_RANK_1", "Klicka på ett objekt i listan till vänster, börja med ditt");
define("_RANK_2", "högst rankade objekt, upprepa tills ditt lägst rankade objekt.");
define("_YOURCHOICES", "Dina val");
define("_YOURRANKING", "Din rangordning");
define("_RANK_3", "Klicka på saxen till höger om objektet");
define("_RANK_4", "för att ta bort det sist elementet i listan.");
//From INDEX.PHP
define("_NOSID", "Du har inte angett ett identifikationsnummer för enkäten");
define("_CONTACT1", "Var god kontakta");
define("_CONTACT2", "för ytterligare assistans");
define("_ANSCLEAR", "Svaren rensade");
define("_RESTART", "Starta om enkäten");
define("_CLOSEWIN", "Stäng fönstret");
define("_CONFIRMCLEAR", "Är du säker på att du vill rensa dina svar?");
define("_EXITCLEAR", "Lämna och rensa enkäten");
//From QUESTION.PHP
define("_BADSUBMIT1", "Kan inte skicka resultaten - det finns inga att skicka.");
define("_BADSUBMIT2", "Detta fel kan uppstå om du redan har skickat dina svar och klickat på 'uppdatera' på din bläddrare. I så fall så är dina svar redan sparade.");
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

define("_SURVEYCOMPLETE", "You have already completed this survey.");

define("_INSTRUCTION_LIST", "Choose only one of the following"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Check any that apply"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Survey Submitted"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "A new response was entered for your survey"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Click the following link to see the individual response:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "View statistics by clicking here:"); //NEW for 098rc5
?>
