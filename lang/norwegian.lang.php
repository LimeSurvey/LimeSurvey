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
	# Norwegian translation 2004-10-29 rev. 2 by;
	# Eirik Sunde

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
define("_INCREASE", "Øk"); //NEW WITH 0.98
define("_SAME", "Uendret"); //NEW WITH 0.98
define("_DECREASE", "Reduser"); //NEW WITH 0.98
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
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
define("_CHOOSEONE", "Velg en av følgende");
define("_ENTERCOMMENT", "Skriv dine kommentarer her");
define("_NUMERICAL_PS", "Kun nummer kan brukes i disse feltene");
define("_CLEARALL", "Avbryt og tøm undersøkelse");
define("_MANDATORY", "Dette spørsmålet må besvares");
define("_MANDATORY_PARTS", "Vennligst fyll ut alle feltene");
define("_MANDATORY_CHECK", "Velg minst et alternativ");
define("_MANDATORY_RANK", "Vennligst ranger alle elementene");
define("_MANDATORY_POPUP", "Et eller flere spørsmål er ikke besvart. Du kan ikke fortsette før disse er besvart."); //NEW in 0.98rc4
define("_VALIDATION", "This question must be answered correctly"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Format: ÅÅÅÅ-MM-DD");
define("_DATEFORMATEG", "(f.eks 2003-12-24 for julaften)");
define("_REMOVEITEM", "Fjern dette elementet");
define("_RANK_1", "Klikk på et element i listen til venstre. Start med ditt");
define("_RANK_2", "høyeste rangerte element, og fortsett til ditt lavest rangerte element.");
define("_YOURCHOICES", "Dine valg");
define("_YOURRANKING", "Din rangering");
define("_RANK_3", "Klikk på saksen ved siden av hvert element til høyre");
define("_RANK_4", "for å fjerne det siste elementet i rangeringslisten din");
//From INDEX.PHP
define("_NOSID", "Du har ikke angitt undersøkelsesnummer");
define("_CONTACT1", "Kontakt");
define("_CONTACT2", "for hjelp");
define("_ANSCLEAR", "Svar slettet");
define("_RESTART", "Start undersøkelse på nytt");
define("_CLOSEWIN_PS", "Lukk vindu");
define("_CONFIRMCLEAR", "Er du sikker på at du vil slette alle svarene?");
define("_CONFIRMSAVE", "Are you sure you want to save your responses?");
define("_EXITCLEAR", "Avbryt og slett svar");
//From QUESTION.PHP
define("_BADSUBMIT1", "Kan ikke sende svarene. Det er ingen svar.");
define("_BADSUBMIT2", "Denne feilen oppstår når du allerede har sendt inn dine svar og har trykket på 'OPPDATER' i webleseren din. I dette tilfellet har svarene dine allerede blitt lagret.<BR /><BR />Hvis du mottar denne meldingen midt i en undersøkelse burde du trykke '<- TILBAKE' i webleseren din og så 'OPPDATER' på forrige side. Du vil måtte svare på forrige spørsmål på nytt, men alle svar før det er lagret. Dette problemet oppsår når webleseren din er overbelastet. Vi beklager problemene dette medfører.");
define("_NOTACTIVE1", "Svarene dine har ikke blitt lagret. Denne undersøkelsen er foreløpig ikke aktiv");
define("_CLEARRESP", "Slett svar");
define("_THANKS", "Takk");
define("_SURVEYREC", "Svarene dine har nå blitt lagret");
define("_SURVEYCPL", "Undersøkelse ferdig");
define("_DIDNOTSAVE", "Lagret ikke");
define("_DIDNOTSAVE2", "En uventet feil har medført at svarene dine ikke kunne lagres.");
define("_DIDNOTSAVE3", "Svarene dine er ikke registret, men har blitt sendt til ledelsen for undersøkelsen og vil bli lagret manuelt i databasen.");
define("_DNSAVEEMAIL1", "En feil oppsto ved lagring av svar på spørsmål nr.");
define("_DNSAVEEMAIL2", "DATA SOM SKULLE LAGRES");
define("_DNSAVEEMAIL3", "SQL KODE SOM FEILET");
define("_DNSAVEEMAIL4", "FEILMELDING");
define("_DNSAVEEMAIL5", "FEIL VED LAGRING");
define("_SUBMITAGAIN", "Prøv å lagre igjen");
define("_SURVEYNOEXIST", "Beklager, den forespurte undersøkelsen finnes ikke");
define("_NOTOKEN1", "Beklager, dette er en begrenset undersøkelse. Du trenger en ID for å delta.");
define("_NOTOKEN2", "Hvis du har blitt tildelt en ID, skriv den inn i feltet under og trykk fortsett.");
define("_NOTOKEN3", "ID'en du skrev inn er enten ugyldig, eller har allerede blitt benyttet.");
define("_NOQUESTIONS", "Denne undersøkelsen har ingen spørsmål og kan derfor ikke kjøres.");
define("_FURTHERINFO", "For mer informasjon, kontakt");
define("_NOTACTIVE", "Denne undersøkelsen er ikke aktiv. Du vil ikke kunne lagre dine svar.");
define("_SURVEYEXPIRED", "Denne undersøkelsen er ikke lengre aktiv.");

define("_SURVEYCOMPLETE", "Du har allerede svart på denne undersøkelsen"); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "Velg kun en av følgende"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Velg alle alternativ du ønsker"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Undersøkelse sendt"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Et nytt svar var gitt for din undersøkelse"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Klikk på følgende link for å se de individuelle svarene:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Se statistikken ved å klikke her:"); //NEW for 098rc5

define("_PRIVACY_MESSAGE", "<b><i>A Note On Privacy</i></b><br />"
						  ."Denne undersøkelsen er anonym.<br />"
						  ."Svarene dine inneholder ikke noen informasjon om deg med mindre "
						  ."et spesifikt spørsmål i undersøkelsen spørr om dette. Hvis du "
						  ."har svart på en undersøkelse som bruker et identifiserede nummer "
						  ."for å gi deg tilgang til undersøkelsen kan du leve i den visshet "
						  ."om at dette nummeret ikke er lagret sammen med svarene dine. "
						  ."Det er lagret i en egen database og vil kun bli brukt for "
						  ."indikere at du har svart på undersøkelsen eller ikke. "
						  ."Det er ikke mulig å sammenlige dette nummeret med svarene i denne "
						  ."undersøkelsen."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "Det er {NUMBEROFQUESTIONS} spørsmål i denne undersøkelsen."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Det er ett spørsmål i denne undersøkelsen."); //New for 0.98rc9 - singular version of above

define ("_RG_REGISTER1", "Du må være registrert for å fullføre denne undersøkelsen"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Du kan registrere deg hvis du ønsker å ta del i den <br />\n"
						."Legg inn detaljene dine under. En epost med den iformasjonen du trenger for å delta "
						."i denne undersøkelsen vil bli send umiddelbart."); //NEW for 0.98rc9
define ("_RG_EMAIL", "Epostadresse"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Fornavn"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Etternavn"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "Epostadressen du oppga er ikke gyldig. Vennligst prøv igjen.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "Epostadressen du brukte er allerede registrert.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} Registreringsbekreftelse");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Takk for at du registrerte deg for å delta i denne undersøkelsen.<br /><br />\n"
								   ."En epost har blitt sendt til adressen du oppga med tilgangsinformasjon "
								   ."for undersøkelsen. Vennligst følg lenken i eposten for å fortsette.<br /><br />\n"
								   ."Undersøkelsesleder {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<b>Takk<br /><br />"
					   ."Du har nå fullført undersøkelsen.</b><br /><br />"
					   ."Trykk ["._SUBMIT."] for å lagre svarene dine."); //New for 0.98finalRC1
define("_SM_REVIEW", "Hvis du ønsker å kontrollere eller å endre svarene dine "
					."kan du trykke [<< "._PREV."]"
					."."); //New for 0.98finalRC1

//For the "printable" survey
define("_PS_CHOOSEONE", "Vennligst velg  <b>kun en</b> av følgende"); //New for 0.98finalRC1
define("_PS_WRITE", "Vennligst skriv svaret ditt her"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Vennligts velg <b>alt</b> som passer"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Vennligst velg alt som passer og gi en kommentar"); //New for 0.98finalRC1
define("_PS_EACHITEM", "vennligst velg et passende svar for hvert element"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "vennligst skriv svarene dine her"); //New for 0.98finalRC1
define("_PS_DATE", "Vennligt oppgi en dato"); //New for 0.98finalRC1
define("_PS_COMMENT", "Kommenter valget ditt her"); //New for 0.98finalRC1
define("_PS_RANKING", "Vennligst nummerer hver boks i prioritert rekkefølge fra 1 til"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Send inn undersøkelsen"); //New for 0.98finalRC1
define("_PS_THANKYOU", "Takk for at du gjennomførte undersøkelsen."); //New for 0.98finalRC1
define("_PS_FAXTO", "Vennligst fax den ferdige undersøkelsen til:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Svar kun på dette spørsmålet"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "hvis du svarte"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "og"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "på spørsmål"); //New for 0.98finalRC1
define("_PS_CON_OR", "eller"); //New for 0.98finalRC2

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
