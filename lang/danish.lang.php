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
	#                                                           #
	#     Translation by Mikkel Skovgaard Sørensen              #
	#                                                           #
	#############################################################
*/
//SINGLE WORDS
define("_YES", "Ja");
define("_NO", "Nej");
define("_UNCERTAIN", "Ved ikke");
define("_ADMIN", "Admin");
define("_TOKENS", "Nøgler");
define("_FEMALE", "Kvinde");
define("_MALE", "Mand");
define("_NOANSWER", "Intet svar");
define("_NOTAPPLICABLE", "Ved ikke"); //New for 0.98rc5
define("_OTHER", "Andet");
define("_PLEASECHOOSE", "Vælg venligst");
define("_ERROR_PS", "Fejl");
define("_COMPLETE", "gennemført");
define("_INCREASE", "Hæv"); //NEW WITH 0.98
define("_SAME", "Samme"); //NEW WITH 0.98
define("_DECREASE", "Sænk"); //NEW WITH 0.98
//from questions.php
define("_CONFIRMATION", "Bekræftelse");
define("_TOKEN_PS", "Nøgle");
define("_CONTINUE_PS", "Forsæt");

//BUTTONS
define("_ACCEPT", "Accepter");
define("_PREV", "forrige");
define("_NEXT", "næste");
define("_LAST", "afslut");
define("_SUBMIT", "afsend");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Vælg en af følgende");
define("_ENTERCOMMENT", "Skriv dine kommentarer her");
define("_NUMERICAL_PS", "Det felt kan kun indeholde tal/numeriske tegn");
define("_CLEARALL", "Nulstil og forlad undersøgelsen");
define("_MANDATORY", "Dette spørgsmål er obligatorisk");
define("_MANDATORY_PARTS", "Udfyld venligst alle dele");
define("_MANDATORY_CHECK", "Afkryds som minimum en mulighed");
define("_MANDATORY_RANK", "Afgiv venligst en score i alle felter");
define("_MANDATORY_POPUP", "En eller flere felter som skal udfyldes er ikke udfyldt - der kan ikke forsættes før disse er udfyldt"); //NEW in 0.98rc4
define("_DATEFORMAT", "Datoformat: ÅÅÅÅ-MM-DD");
define("_DATEFORMATEG", "(eg: 2003-12-24 hvis der skal angives juledag)");
define("_REMOVEITEM", "Fjern denne mulighed");
define("_RANK_1", "Klik på et emne i listen til venstre, startende med det du");
define("_RANK_2", "vurdere højst, og klik derefter nedefter til det lavest vurderede emne.");
define("_YOURCHOICES", "Dine valg");
define("_YOURRANKING", "Din vurdering");
define("_RANK_3", "Klik på saks ikonet til højre for");
define("_RANK_4", "at fjerne det nederst emne på din vurderingsliste");
//From INDEX.PHP
define("_NOSID", "Der mangler at blive angivet en undersøgelses nøgle/id");
define("_CONTACT1", "Kontakt venligst");
define("_CONTACT2", "for videre assistance");
define("_ANSCLEAR", "Svar gennemført");
define("_RESTART", "Nulstil og start forfra");
define("_CLOSEWIN_PS", "Luk dette vindue");
define("_CONFIRMCLEAR", "Er du sikker på at du vil nulstille alle dine spørgsmål?");
define("_EXITCLEAR", "Nulstil og forlad undersøgelsen.");
//From QUESTION.PHP
define("_BADSUBMIT1", "Kan ikke gemme besvarelsen - der er ikke noget at gemme.");
define("_BADSUBMIT2", "Denne fejl er opstået fordi du allerede har gemt dine svar og har trykket på 'Opdater' i din browser. Dine besvarelser er allerede gemt.<br /><br />Hvis du har fået denne fejlmeddelse midt i en spørgeskema undersøgelse bør du trykke på '<- Tilbage' knappen i din browser og tryk på 'Opdater'. Dermed vil dit forrige svar gå tabt men alle andre tidligere svar er gemt, vi beklager de gener dette måtte medføre.");
define("_NOTACTIVE1", "Dine besvarelser er ikke gemt - undersøgelsen er endnu ikke sat igang.");
define("_CLEARRESP", "Nulstil svar");
define("_THANKS", "Tak");
define("_SURVEYREC", "Dine besvarelser er blevet gemt.");
define("_SURVEYCPL", "Undersøgelsen er gennemført");
define("_DIDNOTSAVE", "Kunne ikke gemme");
define("_DIDNOTSAVE2", "Der skete en uventet fejl og dine besvarelser kunne ikke gemmes.");
define("_DIDNOTSAVE3", "Dine besvarelser er ikke gået tabt - men er sendt til administratoren af undersøgelsen som så senere tilføjer disse.");
define("_DNSAVEEMAIL1", "An error occurred saving a response to survey id");
define("_DNSAVEEMAIL2", "DATA TO BE ENTERED");
define("_DNSAVEEMAIL3", "SQL CODE THAT FAILED");
define("_DNSAVEEMAIL4", "ERROR MESSAGE");
define("_DNSAVEEMAIL5", "ERROR SAVING");
define("_SUBMITAGAIN", "Prøv igen");
define("_SURVEYNOEXIST", "Desværre, kunne ikke finde undersøgelses nøgle/id der matcher det valgte.");
define("_NOTOKEN1", "Dette er en lukket undersøgelse og kræver at du har en undersøgelses nøgle/id for at deltage.");
define("_NOTOKEN2", "Hvis du har en undersøgelses nøgle/id så indtast den herunder.");
define("_NOTOKEN3", "Den undersøgelses nøgle/id du har angivet er ugyldig eller er allerede brugt.");
define("_NOQUESTIONS", "Denne undersøgelse har endnu ingen spørgsmål og kan derfor ikke benyttes.");
define("_FURTHERINFO", "For yderligere information kontakt");
define("_NOTACTIVE", "Denne undersøgelse er ikke aktiv og du kan derfor ikke deltage.");
define("_SURVEYEXPIRED", "Denne undersøgelse er ikke længere aktiv og du kan derfor ikke deltage.");

define("_SURVEYCOMPLETE", "Du har allerede gennemført denne undersøgelse."); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "Vælg kun en af nedenstående"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Vælg alle du er enig i"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Undersøgelsen er gemt"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Et nyt svar er gemt i undersøgelsen"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Klik på nedenstående link for at se de individuelle svar:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Vis statistikken her:"); //NEW for 098rc5

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
?>
