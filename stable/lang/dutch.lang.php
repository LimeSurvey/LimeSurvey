<?php
/*
	#############################################################
	# >>> PHPSurveyor  										    #
	#############################################################
	# > Author:  Jason Cleeland									#
	# > E-mail:  jason@cleeland.org								#
	# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
	# >          CARLTON SOUTH 3053, AUSTRALIA					#
	# > Date: 	 20 February 2003								#
	#															#
	# This set of scripts allows you to develop, publish and	#
	# perform data-entry on surveys.	        				#
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

//DUTCH TRANSLATION
//NEDERLANDSTALIGE VERTALING

//LOSSE WOORDEN
define("_YES", "Ja");
define("_NO", "Neen");
define("_UNCERTAIN", "Niet zeker");

define("_ADMIN", "Admin");
define("_TOKENS", "Sleutel");
define("_FEMALE", "Vrouwelijk");
define("_MALE", "Mannelijk");
define("_NOANSWER", "Geen antwoord");
define("_NOTAPPLICABLE", "Niet van toepassing"); //New for 0.98rc5
define("_OTHER", "Andere");
define("_PLEASECHOOSE", "Selecteer");
define("_ERROR_PS", "Fout");
define("_COMPLETE", "volledige");
define("_INCREASE", "Toenemend"); //NEW WITH 0.98
define("_SAME", "Zelfde"); //NEW WITH 0.98
define("_DECREASE", "Afnemend"); //NEW WITH 0.98
define("_REQUIRED", "<font color='red'>*</font>");
//from questions.php
define("_CONFIRMATION", "Bevestiging");
define("_TOKEN_PS", "Sleutel");
define("_CONTINUE_PS", "Ga verder");

//BUTTONS
define("_ACCEPT", "Aanvaarden");
define("_PREV", "Vorige");
define("_NEXT", "Volgende");
define("_LAST", "Laatste");
define("_SUBMIT", "Versturen");

//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Kies één van de volgende");
define("_ENTERCOMMENT", "Geef uw opmerkingen hier in");
define("_NUMERICAL_PS", "In dit veld kunnen enkel nummers ingegeven worden");
define("_CLEARALL", "Afbreken en antwoorden verwijderen");
define("_MANDATORY", "Dit is een verplichte vraag");
define("_MANDATORY_PARTS", "Vervolledig alle velden");
define("_MANDATORY_CHECK", "Kies minimum één optie");
define("_MANDATORY_RANK", "Rangschik alle opties");
define("_MANDATORY_POPUP", "Eén of meerdere verplichte velden zijn niet ingevuld. U kan niet verdergaan zonder deze te beantwoorden."); //NEW in 0.98rc4
define("_VALIDATION", "This question must be answered correctly"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Formaat: JJJJ-MM-DD");
define("_DATEFORMATEG", "(vb: 2003-12-25 voor Kerstmis)");
define("_REMOVEITEM", "Verwijder dit antwoord");
define("_RANK_1", "Selecteer een optie in de lijst aan de linkerzijde, beginnend met het meest");
define("_RANK_2", "toepasselijke optie gaande naar de minst toepasselijke optie.");
define("_YOURCHOICES", "Uw keuzes");
define("_YOURRANKING", "Uw rangschikking");
define("_RANK_3", "Klik op de schaar rechts naast de optie");
define("_RANK_4", "om deze te verwijderen van uw rangschikking");
//From INDEX.PHP
define("_NOSID", "U hebt geen enquête nummer opgegeven.");
define("_CONTACT1", "Contacteer");
define("_CONTACT2", "voor assistentie");
define("_ANSCLEAR", "Antwoorden verwijderd");
define("_RESTART", "Herstart deze enquête");
define("_CLOSEWIN_PS", "Sluit dit venster");
define("_CONFIRMCLEAR", "Bent u zeker dat u alle antwoorden wil verwijderen?");
define("_CONFIRMSAVE", "Are you sure you want to save your responses?");
define("_EXITCLEAR", "Afbreken en antwoorden verwijderen");
//From QUESTION.PHP
define("_BADSUBMIT1", "Gegevens kunnen niet verzonden worden. Er zijn er geen.");
define("_BADSUBMIT2", "Deze fout kan voorkomen als u uw antwoorden reeds heeft bewaard en daarna uw browser hebt ververst.<br /><br />Indien u deze fout krijgt terwijl u de enquête aan het invullen bent, klik dan op 'Terug' in uw browser en ververs het scherm. De antwoorden van de laatste pagina zijn verloren maar de andere antwoorden blijven behouden. Dit probleem kan voorkomen als de server te zwaar belast is op dat moment. Onze verontschuldigingen voor het ongemak.");
define("_NOTACTIVE1", "Uw antwoorden werden niet bewaard. Deze enquête is nog niet aktief.");
define("_CLEARRESP", "Verwijder antwoorden");
define("_THANKS", "Dank u");
define("_SURVEYREC", "Uw antwoorden werden bewaard.");
define("_SURVEYCPL", "Enquête be�indigd.");
define("_DIDNOTSAVE", "Kon niet bewaren");
define("_DIDNOTSAVE2", "Door een onverwachte fout werden uw antwoorden niet bewaard.");
define("_DIDNOTSAVE3", "Uw antwoorden zijn niet verloren. Ze werden doorgestuurd naar de enquête administrator. Ze zullen op een later tijdstip worden ingevoerd.");
define("_DNSAVEEMAIL1", "Er is een fout opgetreden bij het bewaren van antwoorden van enquête nr. ");
define("_DNSAVEEMAIL2", "Error: EEMAIL2");
define("_DNSAVEEMAIL3", "SQL CODE THAT FAILED");
define("_DNSAVEEMAIL4", "ERROR MESSAGE");
define("_DNSAVEEMAIL5", "ERROR SAVING");
define("_SUBMITAGAIN", "Probeer opnieuw door te sturen");
define("_SURVEYNOEXIST", "Sorry. Er is geen enquête met dit nummer.");
define("_NOTOKEN1", "Dit is een gecontrolleerde enquête. U hebt een geldige code nodig om deel te nemen.");
define("_NOTOKEN2", "Indien u een code hebt ontvangen, geef ze dan hier in en klik op 'Verder'.");
define("_NOTOKEN3", "De code die u hebt ingegeven is ongeldig of ze werd reeds gebruikt.");
define("_NOQUESTIONS", "Er zijn nog geen vragen aangemaakt voor deze enquête en kan dus niet getest worden.");
define("_FURTHERINFO", "Voor meer informatie contacteer");
define("_NOTACTIVE", "Deze enquête is momenteel niet aktief. U kan uw vragen niet bewaren.");
define("_SURVEYEXPIRED", "Deze enquête is niet meer beschikbaar.");

define("_SURVEYCOMPLETE", "U hebt deze enquête reeds beantwoord."); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "Kies één van volgende opties"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Selecteer alle toepasselijke opties"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Enquête verzonden"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Er is weer een enquête beantwoord"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Klik de volgende link om de individuele antwoorden te zien:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Bekijk statistieken -> klik hier:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE5", "Click the following link to edit the individual response:"); //NEW for 0.99stable

define("_PRIVACY_MESSAGE", "<strong><i>Privacy verklaring</i></strong><br />"
						  ."Deze enquête is anoniem.<br />"
						  ."De bewaarde antwoorden bevatten geen identiteitsgegevens "
						  ."tenzij u bij een bepaalde vraag identiteitsgegevens hebt ingevi=uld."
						  ."Indien u via een code hebt ingelogd kunnen wij u verzekeren dat deze "
						  ."niet bewaard werd in combinatie met uw antwoorden maar wel in een "
						  ."alleenstaande, niet gekoppelde, tabel en dit enkel om na te kijken "
						  ."of voor die code de enquête reeds werd ingevuld of niet. "
						  ."Er is geen enkele manier om de codes te koppelen aan de antwoorden."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "Er zijn {NUMBEROFQUESTIONS} vragen in deze enquête."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Er is slechts 1 vraag in deze enque�te."); //New for 0.98rc9 - singular version of above

define ("_RG_REGISTER1", "Registratie is vereist om deel te nemen aan deze enquête."); //NEW for 0.98rc9
define ("_RG_REGISTER2", "U kan uzelf registreren om deel te nemen aan deze enquête.<br />\n"
					."Vul volgende gegevens in en u zal onmiddellijk een email ontvangen met daarin "
					."een link naar de enquête."); //NEW for 0.98rc9
define ("_RG_EMAIL", "Email Adres"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Voorname"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Familiename"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "Het emailadres dat u opgaf is niet geldig. Probeer nog eens.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "Het emailadres dat u opgaf is reeds geregistreerd.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} Registratie Bevestiging");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Bedankt voor uw registratie om deel te nemen aan deze enquête.<br /><br />\n"
							."Er werd een email gestuurd naar het opgegeven adres met toegangsgegevens "
							."voor deze enquête. Volg de link in deze email om de enquête te starten.<br /><br />\n"
							."Enquête verantwoordelijke {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<strong>Dank u<br /><br />"
					   ."U hebt alle vragen in deze enquête beantwoord.</strong><br /><br />"
					   ."Klik op ["._SUBMIT."] om uw antwoorden te bewaren.");
define("_SM_REVIEW", "Indien u uw antwoorden nog eens wil nakijken of wijzigen, "
					."blader dan door de enquête met de [<< "._PREV."] en ["._NEXT." >>] knoppen.");

//For the "printable" survey
define("_PS_CHOOSEONE", "Kies <strong>één</strong> van volgende antwoorden"); //New for 0.98finalRC1
define("_PS_WRITE", "Schrijf uw antwoord hier"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Selecteer alle toepasselijke antwoorden"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Selecteer alle toepasselijke antwoorden en geef uw commentaar"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Kies het toepasselijk antwoord voor elke optie"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Schrijf uw antwoord hier"); //New for 0.98finalRC1
define("_PS_DATE", "Vul een datum in"); //New for 0.98finalRC1
define("_PS_COMMENT", "Verduidelijk uw antwoord"); //New for 0.98finalRC1
define("_PS_RANKING", "Geef een nummer voor elke optie volgens uw voorkeur van 1 tot"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Verstuur uw enquête"); //New for 0.98finalRC1
define("_PS_THANKYOU", "Bedankt om deel te nemen aan deze enquête."); //New for 0.98finalRC1
define("_PS_FAXTO", "Graag uw enquête faxen naar:"); //New for 0.98finaclRC1  

define("_PS_CON_ONLYANSWER", "Enkel deze vraag beantwoorden"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "als uw antwoord"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "en"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "was op vraag"); //New for 0.98finalRC1
define("_PS_CON_OR", "or"); //New for 0.98finalRC2

//Save Messages
define("_SAVE_AND_RETURN", "Sla uw antwoorden zover op");
define("_SAVEHEADING", "Sla uw onvoltooide enquête op");
define("_RETURNTOSURVEY", "Terug naar de enqeute");
define("_SAVENAME", "Naam");
define("_SAVEPASSWORD", "Wachtwoord");
define("_SAVEPASSWORDRPT", "Herhaal wachtwoord");
define("_SAVE_EMAIL", "Uw e-mail adres");
define("_SAVEEXPLANATION", "Vul een naam en wachtwoord in voor deze enquête en druk op save.<br />\n"
				  ."Uw enquête wordt opgeslagen en kan later worden aangevuld door in te loggen "
				  ."met de door u opgegeven inloggevens.<br /><br />\n"
				  ."Als u een e-mail adres op geeft sturen we u de details per e-mail").
define("_SAVESUBMIT", "Nu opslaan");
define("_SAVENONAME", "Vul a.u.b een naam in voor de opgeslagen sessie.");
define("_SAVENOPASS", "Vul a.u.b. een wachtwoord in voor de opgeslagen sessie.");
define("_SAVENOMATCH", "De wachtwoorden komen niet overeen.");
define("_SAVEDUPLICATE", "De naam voor de enquête is reeds ingebruik, de naam moet uniek zijn.");
define("_SAVETRYAGAIN", "Probeert u het nog eens.");
define("_SAVE_EMAILSUBJECT", "Opgeslagen enquête details");
define("_SAVE_EMAILTEXT", "U, of iemand anders die uw e-mail adres heeft gebruikt"
						 ."heeft tijdelijk zijn antwoorden op een enquête opgeslagen."
						 ."De volgende details kunt u gebruiken om de enquête te vervolgen.");
define("_SAVE_EMAILURL", "Herlaad de enquête door op de volgende link te klikken:");
define("_SAVE_SUCCEEDED", "Uw antwoorden zijn succesvol opgeslagen.");
define("_SAVE_FAILED", "Er heeft zich een onverwachte fout voorgedaan, uw antwoorden zijn niet opgeslagen.");
define("_SAVE_EMAILSENT", "Uw heeft een e-mail toegezonden gekregen met details over de opgeslagen enquête.");

//Load Messages
define("_LOAD_SAVED", "Laad onvoltooide enquête");
define("_LOADHEADING", "Laad een eerder opgeslagen enquête");
define("_LOADEXPLANATION", "U kunt een enquête laden die u hier eerder heeft opgeslagen.<br />\n"
			  ."Vul de eerder door u verzonnen naam en wachtwoord in<br /><br />\n");
define("_LOADNAME", "Naam");
define("_LOADPASSWORD", "Wachtwoord");
define("_LOADSUBMIT", "Laad nu");
define("_LOADNONAME", "U heeft geen naam opgegeven");
define("_LOADNOPASS", "U heeft geen wachtwoord opgegeven");
define("_LOADNOMATCH", "Er is geen enquête gevonden met deze naam");

define("_ASSESSMENT_HEADING", "Uw toetsing");
?>