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
*/
//SINGLE WORDS
define("_YES", "Taip");
define("_NO", "Ne");
define("_UNCERTAIN", "Neisitikinęs");
define("_ADMIN", "Admin");
define("_TOKENS", "Kodai");
define("_FEMALE", "Moteris");
define("_MALE", "Vyras");
define("_NOANSWER", "Nėra atsakymo");
define("_NOTAPPLICABLE", "Nesusiję"); //New for 0.98rc5
define("_OTHER", "Kita");
define("_PLEASECHOOSE", "Pasirinkite");
define("_ERROR_PS", "Klaida");
define("_COMPLETE", "Baigta");
define("_INCREASE", "Padidinti"); //NEW WITH 0.98
define("_SAME", "Tas pats"); //NEW WITH 0.98
define("_DECREASE", "Sumažinti"); //NEW WITH 0.98
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Patvirtinimas");
define("_TOKEN_PS", "Kodas");
define("_CONTINUE_PS", "Tęsti");

//BUTTONS
define("_ACCEPT", "Priimti");
define("_PREV", "Ankstesnis");
define("_NEXT", "Paskesnis");
define("_LAST", "paskutinis");
define("_SUBMIT", "Patvirtinti");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Pasirinkite iš pateiktų variantų");
define("_ENTERCOMMENT", "Įveskite savo komentarą čia");
define("_NUMERICAL_PS", "Į šį laukelį galima įvesti tik skaičius");
define("_CLEARALL", "Išeiti ir išvalyti apklausą");
define("_MANDATORY", "Šis klausimas yra privalomas");
define("_MANDATORY_PARTS", "Užbaikite visas dalis");
define("_MANDATORY_CHECK", "Pasirinkite bent vieną");
define("_MANDATORY_RANK", "Įvertinkite visus pateiktus");
define("_MANDATORY_POPUP", "Vienas arba daugiau privalomų klausimų liko neatsakyti. Tęsti bus galima tik juos visus atsakius"); //NEW in 0.98rc4
define("_VALIDATION", "This question must be answered correctly"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Formatas: MMMM-MM-DD");
define("_DATEFORMATEG", "(t.y.: 2003-12-25 šv. Kalėdų diena)");
define("_REMOVEITEM", "Pašalinkite šį įrašą");
define("_RANK_1", "Paspauskite ant įrašo kairėje esančiame sąraše, pradedant nuo");
define("_RANK_2", "įrašo kurį vertinate aukščiausiai, tęsiant iki žemiausiai vertinamo įrašo.");
define("_YOURCHOICES", "Jūsų pasirinkimai");
define("_YOURRANKING", "Jūsų įvertinimai");
define("_RANK_3", "Paspauskite ant žirklių įrašo dešinėje");
define("_RANK_4", "kad pašalintumėte žemiausiai įvertintą įrašą iš sąrašo");
//From INDEX.PHP
define("_NOSID", "Jūs neįvedėte apklausos identifikavimo numerio");
define("_CONTACT1", "Susisiekite");
define("_CONTACT2", "jei reikia pagalbos");
define("_ANSCLEAR", "Atsakymai panaikinti");
define("_RESTART", "Pradėti apklausą iš naujo");
define("_CLOSEWIN_PS", "Uždaryti šį langą");
define("_CONFIRMCLEAR", "Ar tikrai norite panaikinti visus savo atsakymus?");
define("_CONFIRMSAVE", "Are you sure you want to save your responses?");
define("_EXITCLEAR", "Išvalyti apklausą ir išeiti");
//From QUESTION.PHP
define("_BADSUBMIT1", "Rezultatų perduoti neina, nes jų nėra.");
define("_BADSUBMIT2", "Ši klaida atsiranda, jei jau buvote perdavę apklausos rezultatus ir paspaudėte REFRESH mygtuką naršyklėje. Šiuo atveju jūsų rezultatai buvo išsaugoti.<br /><br />Jei šį pranešimą gavote bepildydami apklausą, naršyklėje paspauskite BACK ir tada REFRESH. Nors jūs ir prarasite paskutiniuosius atsakymus, visi kiti liks. Ši problema atsiranda, jei naršyklė yra perkrauta. Atsiprašome už nepatogumus.");
define("_NOTACTIVE1", "Jūsų atsakymai nebuvo išsaugoti. Ši apklausa dar neaktyvuota.");
define("_CLEARRESP", "Išvalyti atsakymus");
define("_THANKS", "Ačiū");
define("_SURVEYREC", "Jūsų atsakymai išsaugoti.");
define("_SURVEYCPL", "Apklausa baigta");
define("_DIDNOTSAVE", "Neišsaugota");
define("_DIDNOTSAVE2", "Įvyko nežinoma klaida, todėl jūsų atsakymų išsaugoti neįmanoma.");
define("_DIDNOTSAVE3", "Jūsų atsakymai nepradingo. Jie buvo išsiųsti elektroniniu paštu apklausos administratoriui ir bus įvesti vėliau.");
define("_DNSAVEEMAIL1", "Įvyko klaida išsaugant atsakymus aplausai, kurios ID");
define("_DNSAVEEMAIL2", "DUOMENYS, KURIUOS REIKIA ĮVESTI");
define("_DNSAVEEMAIL3", "SQL KODAS, KURIS NULŪŽO");
define("_DNSAVEEMAIL4", "KLAIDOS PRANEŠIMAS");
define("_DNSAVEEMAIL5", "KLAIDA IŠSAUGANT");
define("_SUBMITAGAIN", "Pabandykite perduoti dar kartą");
define("_SURVEYNOEXIST", "Gaila, tačiau tokios apklausos nėra.");
define("_NOTOKEN1", "Ši apklausa yra kontroliuojama ir jums reikia galiojančio kodo.");
define("_NOTOKEN2", "Jei jums buvo duotas kodas, įveskite jš į žemiau esantį laukelį ir paspauskite mygtuką.");
define("_NOTOKEN3", "Kodas kurį įvedėte yra arba negaliojantis arba jau buvo panaudotas.");
define("_NOQUESTIONS", "Ši apklausa neturi jokių klausimų, todėl jos neįmanoma išbandyti arba atlikti.");
define("_FURTHERINFO", "Norėdami papildomos informacijos susisiekite su");
define("_NOTACTIVE", "Ši apklausa nėra aktyvuota. Jūs negalėsite išsaugoti savo atsakymų.");
define("_SURVEYEXPIRED", "Ši apklausa jau nebenaudojama.");

define("_SURVEYCOMPLETE", "Šią apklausą jūs jau įvykdėte."); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "Pasirinkite vieną iš sekančių"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Pažymėkite visus, kurie tinka"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Apklausa atlikta"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Šiai apklausai buvo įvesti jūsų nauji atsakymai"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Paspauskite šią nuorodą norėdami peržiūrėti atskirus atsakymus:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Paspauskite šią nuorodą norėdami peržiūrėti statistiką:"); //NEW for 098rc5

define("_PRIVACY_MESSAGE", "<strong><i>Apklausos anonimiškumas</i></strong><br />"
						  ."Ši apklausa yr anoniminė.<br />"
						  ."Jūsų pateiktuose apklausos atsakymuose nėra "
						  ."jokių duomenų, kurie jus identifikuotų, nebent buvo užduotas "
						  ."toks klausimas apklausoje. Jei jūs dalyvavote apklausoje "
						  ."kuri naudojo specialų kodą, kad leistų jums ją atlikti, "
						  ."užtikriname jus, identifikavimo kodas "
						  ."nebuvo išsaugotas su jūsų atsakymais. Šie kodai yra saugomi "
						  ."atskiroje duomenų bazėje ir bus naudojami tik tam, kad patikrinti "
						  ."ar jūs atlikote šią apklausą. Būdo sugretinti identifikacinius kodus "
						  ."su apklausos atsakymais nėra."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "Šioje apklausoje yra {NUMBEROFQUESTIONS} klausimų."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Apklausoje yra tik vienas klausimas."); //New for 0.98rc9 - singular version of above
						  
define ("_RG_REGISTER1", "Jūs turite būti užsiregistravęs, jei norite dalyvauti šioje apklausoje."); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Užsiregistruokite, jei norite dalyvauti šioje apklausoje.<br />\n"
						."Įveskite savo duomenis ir netrukus gausite elektroninį laišką su nuoroda "
						."kurią paspaudę galėsite sudalyvauti apklausoje."); //NEW for 0.98rc9
define ("_RG_EMAIL", "E-pašto adresas"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Vardas"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Pavardė"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "Jūs įvedėte netinkamą elektroninio pašto adresą. Įveskite tinkamą.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "Šis elektroninio pašto adresas jau buvo panaudotas.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} apklausos registracijos patvirtinimas");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Ačiū kad užsiregistravote dalyvauti šioje apklausoje.<br /><br />\n"
								   ."Į jūsų nurodytą adresą buvo išsiųstas laiškas su nuoroda į "
								   ."šią apklausą. Pasinaudokite ta nuoroda, kad ją pasiektumėte.<br /><br />\n"
								   ."Apklausos administratorius {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<strong>Ačiū<br /><br />"
					   ."Jūs baigėte šią apklausą.</strong><br /><br />"
					   ."Paspauskite šią nuorodą  ["._SUBMIT."] ir jūsų atsakymai bus išsaugoti.");
define("_SM_REVIEW", "Jei notite patikrinti jūsų įvestus atsakymus ir/arba juos pakeisti, "
					."paspauskite nršyklės mygtuką [<< "._PREV."] "
					."ir galėsite atsakymus peržiūrėti.");

//For the "printable" survey
define("_PS_CHOOSEONE", "Pasirinkite <strong>tik vieną</strong> iš sekančių:"); //New for 0.98finalRC1
define("_PS_WRITE", "Įrašykite savo atsakymą čia:"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Pasirinkite <strong>visus</strong> kurie tinka"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Pasirinkite visus kurie tinka ir pakomentuokite"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Pasirinkite atitinkamą atsakymą/komentarą kiekvienam"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Savo atsakymą (-us) rašykite čia"); //New for 0.98finalRC1
define("_PS_DATE", "Įveskite datą"); //New for 0.98finalRC1
define("_PS_COMMENT", "Čia pakomentuokite savo pasirinkimą"); //New for 0.98finalRC1
define("_PS_RANKING", "Sunumeruokite kiekviną laukelį savo nuožiūra nuo 1 iki"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Įvesti apklausos rezultatus"); //New for 0.98finalRC1
define("_PS_THANKYOU", "Ačiū kad dalyvavote apklausoje."); //New for 0.98finalRC1
define("_PS_FAXTO", "Išsiųskite atliktą apklausą faksu:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Atsakykite tik į šį klausiąm"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "jei atsakėte"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "ir"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "į klausimą"); //New for 0.98finalRC1
define("_PS_CON_OR", "arba"); //New for 0.98finalRC2

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