<?php
/*
	#############################################################
	# >>> PHP Surveyor                                          #
	#############################################################
	# > Author:  Jason Cleeland                                 #
	# > E-mail:  jason@cleeland.org                             #
	# > Mail:    Box 99, Trades Hall, 54 Victoria St,           #
	# >          CARLTON SOUTH 3053, AUSTRALIA                  #
	# > Date: 	 20 February 2003                           #
	#                                                           #
	# This set of scripts allows you to develop, publish and    #
	# perform data-entry on surveys.                            #
	#############################################################
	#                                                           #
	#	Copyright (C) 2003  Jason Cleeland                  #
	#                                                           #
	# This program is free software; you can redistribute       #
	# it and/or modify it under the terms of the GNU General    #
	# Public License as published by the Free Software          #
	# Foundation; either version 2 of the License, or (at your  #
	# option) any later version.                                #
	#                                                           #
	# This program is distributed in the hope that it will be   #
	# useful, but WITHOUT ANY WARRANTY; without even the        #
	# implied warranty of MERCHANTABILITY or FITNESS FOR A      #
	# PARTICULAR PURPOSE.  See the GNU General Public License   #
	# for more details.                                         #
	#                                                           #
	# You should have received a copy of the GNU General        #
	# Public License along with this program; if not, write to  #
	# the Free Software Foundation, Inc., 59 Temple Place -     #
	# Suite 330, Boston, MA  02111-1307, USA.                   #
	#############################################################

        # Norwegian translation 2004-01-26 rev. 1 by;
	# Odd-Jarle Kristoffersen

*/
//SINGLE WORDS
define("_YES", "Ja");
define("_NO", "Nei");
define("_UNCERTAIN", "Ikke sikker");
define("_ADMIN", "Admin");
define("_TOKENS", "ID");
define("_FEMALE", "Kvinne");
define("_MALE", "Mann");
define("_NOANSWER", "Ingen svar");
define("_NOTAPPLICABLE", "Gjelder ikke"); //New for 0.98rc5
define("_OTHER", "Annen");
define("_PLEASECHOOSE", "Velg");
define("_ERROR_PS", "Feil");
define("_COMPLETE", "komplett");
define("_INCREASE", "&Oslash;k"); //NEW WITH 0.98
define("_SAME", "Samme"); //NEW WITH 0.98
define("_DECREASE", "Minsk"); //NEW WITH 0.98
//from questions.php
define("_CONFIRMATION", "Bekreftelse");
define("_TOKEN_PS", "ID");
define("_CONTINUE_PS", "Fortsett");

//BUTTONS
define("_ACCEPT", "Godkjenn");
define("_PREV", "forrige");
define("_NEXT", "neste");
define("_LAST", "siste");
define("_SUBMIT", "send");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Velg en av f&oslash;lgende");
define("_ENTERCOMMENT", "Skriv dine kommentarer her");
define("_NUMERICAL_PS", "Kun nummer kan brukes i disse feltene");
define("_CLEARALL", "Avbryt og t&oslash;m unders&oslash;kelse");
define("_MANDATORY", "Dette sp&oslash;rsm&aring;let m&aring; besvares");
define("_MANDATORY_PARTS", "Vennligst fyll ut alle felt");
define("_MANDATORY_CHECK", "Velg minst et alternativ");
define("_MANDATORY_RANK", "Vennligst ranger alle elementene");
define("_MANDATORY_POPUP", "Et eller flere sp&oslash;rsm&aring;l er ikke besvart. Du kan ikke fortsette f&oslash;r disse er besvart."); //NEW in 0.98rc4
define("_DATEFORMAT", "Format: &Aring;&Aring;&Aring;&Aring-MM-DD");
define("_DATEFORMATEG", "(f.eks 2003-12-24 for juleaften)");
define("_REMOVEITEM", "Fjern dette elementet");
define("_RANK_1", "Klikk p&aring; et element i listen til venstre. Start med ditt");
define("_RANK_2", "h&oslash;yeste rangerte element, og fortsett til ditt lavest rangerte element.");
define("_YOURCHOICES", "Dine valg");
define("_YOURRANKING", "Din rangering");
define("_RANK_3", "Klikk p&aring; saksen ved siden av hvert element til h&oslash;yre");
define("_RANK_4", "for &aring; fjerne det siste elementet i rangeringslisten din");
//From INDEX.PHP
define("_NOSID", "Du har ikke angitt unders&oslash;kelse nummer");
define("_CONTACT1", "Kontakt");
define("_CONTACT2", "for hjelp");
define("_ANSCLEAR", "Svar slettet");
define("_RESTART", "Start unders&oslash;kelse p&aring; nytt");
define("_CLOSEWIN_PS", "Lukk vindu");
define("_CONFIRMCLEAR", "Er du sikker p&aring; at du vil slette alle svarene?");
define("_EXITCLEAR", "Avbryt og slett svar");
//From QUESTION.PHP
define("_BADSUBMIT1", "Kan ikke sende svarene. Det er ingen svar.");
define("_BADSUBMIT2", "Denne feilen oppst&aring;r n&aring;r du allerede har sendt inn dine svar og har trykket p&aring; 'OPPDATER' i webleseren din. I dette tilfellet har svarene dine allerede blitt lagret.<BR /><BR />Hvis du mottar denne meldingen midt i en unders&oslash;kelse burde du trykke '<- TILBAKE' i webleseren din og s&aring; 'OPPDATER' p&aring; forrige side. Du vil m&aring;tte svare p&aring; forrige sp&oslash;rsm&aring;l p&aring; nytt, men alle svar f&oslash;r det er lagret. Dette problemet opps&aring;r n&aring;r webleseren din er overbelastet. Vi beklager problemene dette medf&oslash;rer.");
define("_NOTACTIVE1", "Dine svar har ikke blitt lagret. Denne unders&oslash;kelsen er ikke aktiv enn&aring;");
define("_CLEARRESP", "Slett svar");
define("_THANKS", "Takk");
define("_SURVEYREC", "Dine svar har blitt lagret");
define("_SURVEYCPL", "Unders&oslash;kelse ferdig");
define("_DIDNOTSAVE", "Lagret ikke");
define("_DIDNOTSAVE2", "En uventet feil har medf&oslash;rt at dine svar ikke kunne lagres.");
define("_DIDNOTSAVE3", "Dine svar er ikke registret, men har blitt sendt til ledelsen for unders&oslash;kelsen og vil bli lagret manuelt i databasen.");
define("_DNSAVEEMAIL1", "En feil oppsto ved lagring av svar p&aring; sp&oslash;rsm&aring;l nr.");
define("_DNSAVEEMAIL2", "DATA SOM SKULLE LAGRES");
define("_DNSAVEEMAIL3", "SQL KODE SOM FEILET");
define("_DNSAVEEMAIL4", "FEILMELDING");
define("_DNSAVEEMAIL5", "FEIL VED LAGRING");
define("_SUBMITAGAIN", "Pr&oslash;v &aring; lagre igjen");
define("_SURVEYNOEXIST", "Beklager, det finnes ingen slik unders&oslash;kelse");
define("_NOTOKEN1", "Beklager, dette er en begrenset unders&oslash;kelse. Du trenger en ID for &aring; delta.");
define("_NOTOKEN2", "Hvis du har blitt tildelt en ID, skriv den inn i feltet under og trykk fortsett.");
define("_NOTOKEN3", "ID'en du skrev inn er enten ugyldig, eller har allerede blitt benyttet.");
define("_NOQUESTIONS", "Denne unders&oslash;kelsen har ingen sp&oslash;rsm&aring;l og kan derfor ikke kj&oslash;res n&aring;.");
define("_FURTHERINFO", "For mer informasjon, kontakt");
define("_NOTACTIVE", "Denne unders&oslash;kelsen er ikke aktiv. Du vil ikke kunne lagre dine svar.");
define("_SURVEYEXPIRED", "Denne unders&oslash;kelsen er ikke lengre aktiv.");

define("_SURVEYCOMPLETE", "Du har allerede svart p&aring; denne unders&oslash;kelsen"); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "Velg kun en av f&oslash;lgende"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Velg alle alternativ du &oslash;nsker"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Unders&oslash;kelse sendt"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Et nytt svar var gitt for din unders&oslash;kelse"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Klikk p&aring; f&oslash;lgende link for &aring; se de individuelle svarene:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Se statistikken ved &aring; klikke her:"); //NEW for 098rc5

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
