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
define("_TOKENS", "Kódok");
define("_FEMALE", "Nõ");
define("_MALE", "Férfi");
define("_NOANSWER", "Nincs válasz");
define("_NOTAPPLICABLE", "???"); //New for 0.98rc5
define("_OTHER", "Más");
define("_PLEASECHOOSE", "Kérem válasszon");
define("_ERROR_PS", "Hiba");
define("_COMPLETE", "teljes");
define("_INCREASE", "Növel"); //NEW WITH 0.98
define("_SAME", "Ugyanaz"); //NEW WITH 0.98
define("_DECREASE", "Csökkent"); //NEW WITH 0.98
//from questions.php
define("_CONFIRMATION", "Megerõsítés");
define("_TOKEN_PS", "Kód");
define("_CONTINUE_PS", "Folytatás");

//BUTTONS
define("_ACCEPT", "Elfogadom");
define("_PREV", "elõzõ");
define("_NEXT", "következõ");
define("_LAST", "utolsó");
define("_SUBMIT", "Elküldöm");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Kérem válsszon egyet az alábbiak közül");
define("_ENTERCOMMENT", "Az Ön megjegyzése ehhez");
define("_NUMERICAL_PS", "Ebbe a mezõbe csak számokat írhat");
define("_CLEARALL", "Kilépés és a kérdõív törlése");
define("_MANDATORY", "Erre a kérdésre kötelezõ válszolni");
define("_MANDATORY_PARTS", "Kérem töltsön ki mindent");
define("_MANDATORY_CHECK", "Jelöljön be legalább egy válszt");
define("_MANDATORY_RANK", "Rangsorolja az összeset");
define("_MANDATORY_POPUP", "Legalább egy kötelezõen kitöltendõ kérdésre nem válszolt. Addig nem léphet tovább, amíg ezeket nem tölti ki!"); //NEW in 0.98rc4
define("_DATEFORMAT", "Dátum formátum: ÉÉÉÉ-HH-NN");
define("_DATEFORMATEG", "(pl: karácsony napja: 2003-12-25)");
define("_REMOVEITEM", "E tétel eltávolítása");
define("_RANK_1", "A bal oldali listában kattintson elõször a legfontosabbra,");
define("_RANK_2", "majd sorban a legkevésbé fontosig az összesre");
define("_YOURCHOICES", "Lehetõségek");
define("_YOURRANKING", "Az Ön rangsora");
define("_RANK_3", "Egy tétel eltávolításához kattintson a mellette található");
define("_RANK_4", "ollóra. Így az utolsó tétel lekerül a listáról");
//From INDEX.PHP
define("_NOSID", "Nem adott meg kérdõív-azonosítót");
define("_CONTACT1", "A további teendõk ügyében vegye fel a kapcsolatot:");
define("_CONTACT2", "");
define("_ANSCLEAR", "A válaszok törölve");
define("_RESTART", "A kérdõív újrakezdése");
define("_CLOSEWIN_PS", "Ablak bezárása");
define("_CONFIRMCLEAR", "Biztosan törölni akarja a válaszait?");
define("_EXITCLEAR", "Kilépés és a kérdõív törlése");
//From QUESTION.PHP
define("_BADSUBMIT1", "Nem tudom elküldeni az eredményeket, mert nincsenek válaszok.");
define("_BADSUBMIT2", "Ez a hiba akkor fordul elõ, ha már elküldte a válaszait és utána megnyomta a 'Frissítés' gombot a böngészõn. Ebben az esetben a válaszai már el vannak küldve.<br /><br />Ha viszont ezt a hibát a kérdõív kitöltése közben kapta, akkor nyomja meg a böngészõ '<- VISSZA/BACK' gombját, és az így megjelenõ oldalt frissítse. Így az utolsó oldal válaszait elveszti, de minden elõzõ megmarad. Ez a hiba akkor szokott elõfordulni, ha a szerver túl van terhelve. Elnézést kérünk a kellemetlenségért.");
define("_NOTACTIVE1", "Your survey responses have not been recorded. This survey is not yet active.");
define("_CLEARRESP", "Válaszok törlése");
define("_THANKS", "Köszönjük");
define("_SURVEYREC", "Válaszait rögzítettük");
define("_SURVEYCPL", "Vége a kérdõívnek");
define("_DIDNOTSAVE", "Nem sikerült elmenteni");
define("_DIDNOTSAVE2", "Váratlan hiba következett be, válaszait nem sikerült rögzíteni.");
define("_DIDNOTSAVE3", "De a bevitt adatok nem vesztek el, hanem emailben továbbítottuk a rendszer karbantartójának, aki késõbb ezeket be fogja vinni az adatbázisba.");
define("_DNSAVEEMAIL1", "Hiba lépett fel a következõ kérdõív rögzítésekor:");
define("_DNSAVEEMAIL2", "DATA TO BE ENTERED");
define("_DNSAVEEMAIL3", "SQL CODE THAT FAILED");
define("_DNSAVEEMAIL4", "ERROR MESSAGE");
define("_DNSAVEEMAIL5", "ERROR SAVING");
define("_SUBMITAGAIN", "Próbálja meg újra elküldeni");
define("_SURVEYNOEXIST", "Nincs ilyen kérdõív.");
define("_NOTOKEN1", "Ez a kérdõív zártkörû, a felmérésben való részvételehez egy kódra van szüksége.");
define("_NOTOKEN2", "Ha kapott ilyen kódot, írja be az alábbi mezõbe, majd kattintson a 'Tovább' gombra.");
define("_NOTOKEN3", "A megadott kód érvénytelen vagy már valaki felhasználta egy kérdõív kitöltéséhez.");
define("_NOQUESTIONS", "Ez a kérdõív egyelõre nem tartalmaz kérdéseket, ezért nem lehet kipróbálni vagy kitölteni.");
define("_FURTHERINFO", "További információ:");
define("_NOTACTIVE", "Ez a kérdõív egyelõre nem aktív, ezért a válaszokat nem lehet elmenteni.");
define("_SURVEYEXPIRED", "Ez a kérdõív már lejárt, nem lehet kitölteni.");

define("_SURVEYCOMPLETE", "A kérdõívet már kitöltötte egyszer"); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "Válasszon egyet az alábbiak közül"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Válasszon ki egyet vagy többet az alábbiak közül"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Kitöltött kérdõív érkezett"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Válaszok érkeztek a következõ kérdõívhez"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Kattintson ide e kérdõív megtekintéséhez:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Itt tekintheti meg a statisztikákat:"); //NEW for 098rc5

define("_PRIVACY_MESSAGE", "<b><i>A Note On Privacy</i></b><br />"
						  ."This survey is anonymous.<br />"
						  ."The record kept of your survey responses does not contain any "
						  ."identifying information about you unless a specific question "
						  ."in the survey has asked for this. If you have responded to a "
						  ."survey that used an identifying token to allow you to access "
						  ."the survey, you can rest assured that the identifying token "
						  ."is not kept with your responses. It is managed in a seperate "
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
?>
