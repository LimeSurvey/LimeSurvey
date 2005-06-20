<?php
/*
	#############################################################
	# >>> PHP Surveyor                                          #
	#############################################################
	# > Author:  Jason Cleeland                                 #
	# > E-mail:  jason@cleeland.org                             #
	# > Mail:    Box 99, Trades Hall, 54 Victoria St,           #
	# >          CARLTON SOUTH 3053, AUSTRALIA                  #
	# > Date: 	 20 February 2003                           	#
	#                                                           #
	# This set of scripts allows you to develop, publish and    #
	# perform data-entry on surveys.                            #
	#############################################################
	#                                                           #
	#	Copyright (C) 2003  Jason Cleeland                  	#
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
    # Norwegian translations by									#
	# Odd-Jarle Kristoffersen									#
	# Eirik Sunde												#
	#############################################################

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
define("_CHOOSEONE", "Velg en av f&oslash;lgende");
define("_ENTERCOMMENT", "Skriv dine kommentarer her");
define("_NUMERICAL_PS", "Kun nummer kan brukes i disse feltene");
define("_CLEARALL", "Avbryt og t&oslash;m unders&oslash;kelse");
define("_MANDATORY", "Dette sp&oslash;rsm&aring;let m&aring; besvares");
define("_MANDATORY_PARTS", "Vennligst fyll ut alle feltene");
define("_MANDATORY_CHECK", "Velg minst et alternativ");
define("_MANDATORY_RANK", "Vennligst ranger alle elementene");
define("_MANDATORY_POPUP", "Et eller flere sp&oslash;rsm&aring;l er ikke besvart. Du kan ikke fortsette f&oslash;r disse er besvart."); //NEW in 0.98rc4
define("_VALIDATION", "This question must be answered correctly"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Format: &Aring;&Aring;&Aring;&Aring;-MM-DD");
define("_DATEFORMATEG", "(f.eks 2003-12-24 for julaften)");
define("_REMOVEITEM", "Fjern dette elementet");
define("_RANK_1", "Klikk p&aring; et element i listen til venstre. Start med ditt");
define("_RANK_2", "h&oslash;yeste rangerte element, og fortsett til ditt lavest rangerte element.");
define("_YOURCHOICES", "Dine valg");
define("_YOURRANKING", "Din rangering");
define("_RANK_3", "Klikk p&aring; saksen ved siden av hvert element til h&oslash;yre");
define("_RANK_4", "for &aring; fjerne det siste elementet i rangeringslisten din");
//From INDEX.PHP
define("_NOSID", "Du har ikke angitt unders&oslash;kelsesnummer");
define("_CONTACT1", "Kontakt");
define("_CONTACT2", "for hjelp");
define("_ANSCLEAR", "Svar slettet");
define("_RESTART", "Start unders&oslash;kelse p&aring; nytt");
define("_CLOSEWIN_PS", "Lukk vindu");
define("_CONFIRMCLEAR", "Er du sikker p&aring; at du vil slette alle svarene?");
define("_CONFIRMSAVE", "Are you sure you want to save your responses?");
define("_EXITCLEAR", "Avbryt og slett svar");
//From QUESTION.PHP
define("_BADSUBMIT1", "Kan ikke sende svarene. Det er ingen svar.");
define("_BADSUBMIT2", "Denne feilen oppst&aring;r n&aring;r du allerede har sendt inn dine svar og har trykket p&aring; 'OPPDATER' i webleseren din. I dette tilfellet har svarene dine allerede blitt lagret.<BR /><BR />Hvis du mottar denne meldingen midt i en unders&oslash;kelse burde du trykke '<- TILBAKE' i webleseren din og s&aring; 'OPPDATER' p&aring; forrige side. Du vil m&aring;tte svare p&aring; forrige sp&oslash;rsm&aring;l p&aring; nytt, men alle svar f&oslash;r det er lagret. Dette problemet opps&aring;r n&aring;r webleseren din er overbelastet. Vi beklager problemene dette medf&oslash;rer.");
define("_NOTACTIVE1", "Svarene dine har ikke blitt lagret. Denne unders&oslash;kelsen er forel&oslash;pig ikke aktiv");
define("_CLEARRESP", "Slett svar");
define("_THANKS", "Takk");
define("_SURVEYREC", "Svarene dine har n&aring; blitt lagret");
define("_SURVEYCPL", "Unders&oslash;kelse ferdig");
define("_DIDNOTSAVE", "Lagret ikke");
define("_DIDNOTSAVE2", "En uventet feil har medf&oslash;rt at svarene dine ikke kunne lagres.");
define("_DIDNOTSAVE3", "Svarene dine er ikke registret, men har blitt sendt til ledelsen for unders&oslash;kelsen og vil bli lagret manuelt i databasen.");
define("_DNSAVEEMAIL1", "En feil oppsto ved lagring av svar p&aring; sp&oslash;rsm&aring;l nr.");
define("_DNSAVEEMAIL2", "DATA SOM SKULLE LAGRES");
define("_DNSAVEEMAIL3", "SQL KODE SOM FEILET");
define("_DNSAVEEMAIL4", "FEILMELDING");
define("_DNSAVEEMAIL5", "FEIL VED LAGRING");
define("_SUBMITAGAIN", "Pr&oslash;v &aring; lagre igjen");
define("_SURVEYNOEXIST", "Beklager, den forespurte unders&oslash;kelsen finnes ikke");
define("_NOTOKEN1", "Beklager, dette er en begrenset unders&oslash;kelse. Du trenger en ID for &aring; delta.");
define("_NOTOKEN2", "Hvis du har blitt tildelt en ID, skriv den inn i feltet under og trykk fortsett.");
define("_NOTOKEN3", "ID'en du skrev inn er enten ugyldig, eller har allerede blitt benyttet.");
define("_NOQUESTIONS", "Denne unders&oslash;kelsen har ingen sp&oslash;rsm&aring;l og kan derfor ikke kj&oslash;res.");
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

define("_PRIVACY_MESSAGE", "<strong><i>A Note On Privacy</i></strong><br />"
						  ."Denne unders&oslash;kelsen er anonym.<br />"
						  ."Svarene dine inneholder ikke noen informasjon om deg med mindre "
						  ."et spesifikt sp&oslash;rsm&aring;l i unders&oslash;kelsen sp&oslash;rr om dette. Hvis du "
						  ."har svart p&aring; en unders&oslash;kelse som bruker et identifiserede nummer "
						  ."for &aring; gi deg tilgang til unders&oslash;kelsen kan du leve i den visshet "
						  ."om at dette nummeret ikke er lagret sammen med svarene dine. "
						  ."Det er lagret i en egen database og vil kun bli brukt for "
						  ."indikere at du har svart p&aring; unders&oslash;kelsen eller ikke. "
						  ."Det er ikke mulig &aring; sammenlige dette nummeret med svarene i denne "
						  ."unders&oslash;kelsen."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "Det er {NUMBEROFQUESTIONS} sp&oslash;rsm&aring;l i denne unders&oslash;kelsen."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Det er ett sp&oslash;rsm&aring;l i denne unders&oslash;kelsen."); //New for 0.98rc9 - singular version of above

define ("_RG_REGISTER1", "Du m&aring; være registrert for &aring; fullf&oslash;re denne unders&oslash;kelsen"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Du kan registrere deg hvis du &oslash;nsker &aring; ta del i den <br />\n"
						."Legg inn detaljene dine under. En epost med den iformasjonen du trenger for &aring; delta "
						."i denne unders&oslash;kelsen vil bli send umiddelbart."); //NEW for 0.98rc9
define ("_RG_EMAIL", "Epostadresse"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Fornavn"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Etternavn"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "Epostadressen du oppga er ikke gyldig. Vennligst pr&oslash;v igjen.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "Epostadressen du brukte er allerede registrert.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} Registreringsbekreftelse");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Takk for at du registrerte deg for &aring; delta i denne unders&oslash;kelsen.<br /><br />\n"
								   ."En epost har blitt sendt til adressen du oppga med tilgangsinformasjon "
								   ."for unders&oslash;kelsen. Vennligst f&oslash;lg lenken i eposten for &aring; fortsette.<br /><br />\n"
								   ."Unders&oslash;kelsesleder {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<strong>Takk<br /><br />"
					   ."Du har n&aring; fullf&oslash;rt unders&oslash;kelsen.</strong><br /><br />"
					   ."Trykk ["._SUBMIT."] for &aring; lagre svarene dine."); //New for 0.98finalRC1
define("_SM_REVIEW", "Hvis du &oslash;nsker &aring; kontrollere eller &aring; endre svarene dine "
					."kan du trykke [<< "._PREV."]"
					."."); //New for 0.98finalRC1

//For the "printable" survey
define("_PS_CHOOSEONE", "Vennligst velg  <strong>kun en</strong> av f&oslash;lgende"); //New for 0.98finalRC1
define("_PS_WRITE", "Vennligst skriv svaret ditt her"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Vennligts velg <strong>alt</strong> som passer"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Vennligst velg alt som passer og gi en kommentar"); //New for 0.98finalRC1
define("_PS_EACHITEM", "vennligst velg et passende svar for hvert element"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "vennligst skriv svarene dine her"); //New for 0.98finalRC1
define("_PS_DATE", "Vennligt oppgi en dato"); //New for 0.98finalRC1
define("_PS_COMMENT", "Kommenter valget ditt her"); //New for 0.98finalRC1
define("_PS_RANKING", "Vennligst nummerer hver boks i prioritert rekkef&oslash;lge fra 1 til"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Send inn unders&oslash;kelsen"); //New for 0.98finalRC1
define("_PS_THANKYOU", "Takk for at du gjennomf&oslash;rte unders&oslash;kelsen."); //New for 0.98finalRC1
define("_PS_FAXTO", "Vennligst fax den ferdige unders&oslash;kelsen til:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Svar kun p&aring; dette sp&oslash;rsm&aring;let"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "hvis du svarte"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "og"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "p&aring; sp&oslash;rsm&aring;l"); //New for 0.98finalRC1
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
