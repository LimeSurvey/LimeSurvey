<?php
/*
	#############################################################
	# >>> PHP Surveyor					    					#
	#############################################################
	# > Author:  Jason Cleeland				    				#
	# > E-mail:  jason@cleeland.org				    			#
	# > Mail:    Box 99, Trades Hall, 54 Victoria St,	    	#
	# >          CARLTON SOUTH 3053, AUSTRALIA		    		#
	# > Date: 	 20 February 2003			    				#
	#							    							#
	# This set of scripts allows you to develop, publish and    #
	# perform data-entry on surveys.			    			#
	#############################################################
	#							    							#
	#	Copyright (C) 2003  Jason Cleeland		    			#
	#							    							#
	# This program is free software; you can redistribute 	    #
	# it and/or modify it under the terms of the GNU General    #
	# Public License as published by the Free Software 	    	#
	# Foundation; either version 2 of the License, or (at your  #
	# option) any later version.				    			#
	#							    							#
	# This program is distributed in the hope that it will be   #
	# useful, but WITHOUT ANY WARRANTY; without even the 	    #
	# implied warranty of MERCHANTABILITY or FITNESS FOR A 	    #
	# PARTICULAR PURPOSE.  See the GNU General Public License   #
	# for more details.					    					#
	#							    							#
	# You should have received a copy of the GNU General 	    #
	# Public License along with this program; if not, write to  #
	# the Free Software Foundation, Inc., 59 Temple Place -     #
	# Suite 330, Boston, MA  02111-1307, USA.		    		#
	#############################################################
	#							    							#
	# Slovenian Language File				    				#
	# Created by Gasper Koren [gasper@fdvinfo.net]		 	   	#
	# Web Survey Methodology - http://www.websm.org/	    	#
	#							    							#
	#############################################################
*/
//SINGLE WORDS
define("_YES", "Da");
define("_NO", "Ne");
define("_UNCERTAIN", "Neodloèen");
define("_ADMIN", "Admin");
define("_TOKENS", "Gesla");
define("_FEMALE", "®enski");
define("_MALE", "Mo¹ki");
define("_NOANSWER", "Brez odgovora");
define("_NOTAPPLICABLE", "N/A"); //New for 0.98rc5
define("_OTHER", "Drugo");
define("_PLEASECHOOSE", "Prosimo, izberite");
define("_ERROR_PS", "Napaka");
define("_COMPLETE", "Zakljuèeno");
define("_INCREASE", "Poveèalo"); //NEW WITH 0.98
define("_SAME", "Ostalo enako"); //NEW WITH 0.98
define("_DECREASE", "Zmanj¹alo"); //NEW WITH 0.98
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Potrditev");
define("_TOKEN_PS", "Geslo");
define("_CONTINUE_PS", "Nadaljuj");

//BUTTONS
define("_ACCEPT", "Sprejmi");
define("_PREV", "Nazaj");
define("_NEXT", "Naprej");
define("_LAST", "Zadnje");
define("_SUBMIT", "Po¹lji podatke");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Prosimo, izberite eno izmed naslednjih mo¾nosti");
define("_ENTERCOMMENT", "Prosimo, vpi¹ite svoj komentar");
define("_NUMERICAL_PS", "V to polje lahko vpisujete samo ¹tevilke");
define("_CLEARALL", "Izhod brez po¹iljanja odgovorov");
define("_MANDATORY", "Na to vpra¹anje morate obvezno odgovoriti");
define("_MANDATORY_PARTS", "Prosimo, odgovorite na vsa vpra¹anja");
define("_MANDATORY_CHECK", "Prosimo, izberite vsaj eno izmed mo¾nosti");
define("_MANDATORY_RANK", "Prosimo, rangirajte vse");
define("_MANDATORY_POPUP", "Niste odgovorili na eno ali veè obveznih vpra¹anj, zato z anketo ne morete nadaljevati!"); //NEW in 0.98rc4
define("_VALIDATION", "This question must be answered correctly"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "V obliki: LLLL-MM-DD");
define("_DATEFORMATEG", "(npr.: 2004-12-25 za Bo¾iè)");
define("_REMOVEITEM", "Odstrani");
define("_RANK_1", "Kliknite na listo na levi strani. Priènite z najvi¹je");
define("_RANK_2", "ocenjenim in nadaljujte do najni¾je ocenjenega.");
define("_YOURCHOICES", "Va¹a izbira");
define("_YOURRANKING", "Va¹a ocena");
define("_RANK_3", "Kliknite na ¹karje na desni,");
define("_RANK_4", "ce ¾elite izbrisati zadnji vnos");
//From INDEX.PHP
define("_NOSID", "Manjka identifikacijska ¹tevilka ankete!");
define("_CONTACT1", "Prosimo, obrnite se na");
define("_CONTACT2", "za nadaljno pomoè in vpra¹anja");
define("_ANSCLEAR", "Odgovori so izbrisani");
define("_RESTART", "Ponovno zaèni z anketo");
define("_CLOSEWIN_PS", "Zapri okno");
define("_CONFIRMCLEAR", "Ali ste preprièani, da ¾elite izbrisati va¹e odgovore?");
define("_EXITCLEAR", "Zapusti anketo brez po¹iljanja odgovorov");
//From QUESTION.PHP
define("_BADSUBMIT1", "Odgovorov ni mogoèe poslati -- odgovorov ni.");
define("_BADSUBMIT2", "Ta napaka se lahko pojavi, èe ste ¾e posredovali va¹e odgovore in nato pritisnili gumb za osve¾itev strani (<i>Refresh</i>). Va¹i odgovori so bili v tem primeru ¾e shranjeni.<br /><br />Èe se to sporoèilo pojavi med anketiranjem, morate pritisniti gumb '<- NAZAJ' ('<- BACK') v va¹em brskalniku in nato OSVE®I/REFRESH. Dokler ne odgovorite na zadnje vpra¹anje v anketi, so va¹i odgovori ¹e vedno dosegljivi. Na podobno te¾avo lahko naletite tudi, ob preobremenjenosti stre¾nika. Za te¾ave se vam opravièujemo.");
define("_NOTACTIVE1", "Va¹i odgovori se niso zabele¾ili. Anketa ¹e ni aktivna!");
define("_CLEARRESP", "Izbri¹i odgovore.");
define("_THANKS", "Hvala lepa");
define("_SURVEYREC", "Va¹i odgovori so se shranili!");
define("_SURVEYCPL", "Anketa je konèana.");
define("_DIDNOTSAVE", "Ni bilo shranjeno.");
define("_DIDNOTSAVE2", "Pri¹lo je do neprièakovane napake. Va¹ih odgovorov ni mogoèe shraniti.");
define("_DIDNOTSAVE3", "Va¹i odgovori NISO bili izgiubljeni. Poslani so bili administratorju ankete in bodo vkljuèeni v rezultate.");
define("_DNSAVEEMAIL1", "Napaka v identifikacijski ¹tevilki");
define("_DNSAVEEMAIL2", "Podatki za vnos");
define("_DNSAVEEMAIL3", "Napaka v SQL kodi");
define("_DNSAVEEMAIL4", "SPOROÈILO Z NAPAKO");
define("_DNSAVEEMAIL5", "NAPAKA PRI SHRANJEVANJU");
define("_SUBMITAGAIN", "Poskusite ponovno");
define("_SURVEYNOEXIST", "Oprostite. Ta anketa ne obstaja.");
define("_NOTOKEN1", "Ta anketa ni javno dostopna. Za sodelovanje potrebujete geslo.");
define("_NOTOKEN2", "Èe vam je bilo geslo posredovano, ga vpi¹ite v spodnje polje in kliknite za nadaljevanje.");
define("_NOTOKEN3", "Geslo, ki ste ga posredovali ni veljavno ali pa je bilo ¾e uporabljeno.");
define("_NOQUESTIONS", "Ta anketa se nima nobenih vpra¹anj.");
define("_FURTHERINFO", "Za dodatne informacije se obrnite na");
define("_NOTACTIVE", "Ta anketa trenutno ni aktivna. Va¹i odgovori ne bodo shranjeni.");
define("_SURVEYEXPIRED", "Anketa je zakljuèena.");

define("_SURVEYCOMPLETE", "Na to anketo ste ¾e odgovorili."); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "Izberite samo eno izmed mo¾nosti"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Mo¾nih je veè odgovorov"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Anketa je bila poslana"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Va¹a anketa je dobila nov odgovor"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Individualni potatki so vam na voljo tukaj:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Osnovne statistike so vam na voljo tukaj:"); //NEW for 098rc5

define("_PRIVACY_MESSAGE", "<b><i>Obvestilo o varovanju zasebnosti</i></b><br />"
						  ."Ta anketa je anonimna.<br />"
						  ."Va¹i odgovori na anketna vpra¹anja, ki se shranjujejo v bazo odgovorov ne vsebujejo "
						  ."nobenih informacij, prek katerih bi vas bilo mogoèe identificirati razen v primeru "
						  ."ko so le te del odgovora na anketno vpra¹anje. Èe odgovarjate na anketo, ki "
						  ."za dostop uporablja identifikacijsko geslo, se podatki o geslu ne hranijo "
						  ."skupaj z odgovori na anketna vpra¹anja. Identifikacijski podatki se hranijo "
						  ."v posebni bazi in slu¾ijo zgolj kot informacija, èe ste ¾e (oz. ¹e niste) "
						  ."odgovorili na anketo. Gesel v nobenem primeru ni mogoèe povezati z "
						  ."odgovori na anketo."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "V tej anketi je {NUMBEROFQUESTIONS} vpra¹anj."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "V tej anketi je samo eno vpra¹anje."); //New for 0.98rc9 - singular version of above

define ("_RG_REGISTER1", "Èe ¾elite odgovoriti na anketo, se morate registrirati."); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Èe ¾elite sodelovati v anketi se lahko registrirate.<br />\n"
						."Vpi¹ite svoje podatke in veljaven e-mail naslov. Navodila za sodelovanje "
						."boste v kratkem prejeli po elektronski po¹ti."); //NEW for 0.98rc9
define ("_RG_EMAIL", "E-mail naslov"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Ime"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Priimek"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "Ta e-mail naslov ni veljaven. Poskusite znova.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "E-mail naslov, ki ste ga vpisali je bil ¾e uporabljen v tej anketi.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "Potrditev registracije -- {SURVEYNAME}");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Hvala ker ste se prijavili za sodelovanje v anketi.<br /><br />\n"
								   ."Na e-mail naslov, ki ste ga navedli vam je bilo poslano sporoèilo z navodili za dostop do ankete. "
								   ."Za nadaljevanje upo¹tevajte ta navodila.<br /><br />\n"
								   ."Lep pozdrav, {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<b>Najlep¹a hvala<br /><br />"
					   ."Z odgovarjanjem na anketo ste zakljuèili.</b><br /><br />"
					   ."S klikom na ["._SUBMIT."] boste shranili va¹e odgovore."); //New for 0.98finalRC1
define("_SM_REVIEW", "Èe ¾elite preveriti va¹e odgovore ali jih popraviti, "
					."lahko to storite s klikanjem na gumb [<< "._PREV."] ."); //New for 0.98finalRC1
//For the "printable" survey
define("_PS_CHOOSEONE", "Prosimo, izberite  <b>eno</b> izmed mo¾nosti"); //New for 0.98finalRC1
define("_PS_WRITE", "Vpi¹ite va¹ odgovor"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Mo¾nih je <b>veè</b> odgovorov"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Izberite ustrezne odgovore in podajte komentar"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Izberite primeren odgovor za vsako trditev."); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Prosimo, vpi¹ite odgovor"); //New for 0.98finalRC1
define("_PS_DATE", "Prosimo, vpi¹ite datum"); //New for 0.98finalRC1
define("_PS_COMMENT", "Komentirajte va¹o izbiro"); //New for 0.98finalRC1
define("_PS_RANKING", "Prosimo, o¹tevièite vsako polje glede na va¹e preference od 1 do"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Po¹lji anketo"); //New for 0.98finalRC1
define("_PS_THANKYOU", "Najlep¹a hvala za sodelovanje v anketi."); //New for 0.98finalRC1
define("_PS_FAXTO", "Prosimo, po¹ljite va¹o izpolnjeno anketo po telefaksu na ¹tevilko:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Odgoorite samo na to vpra¹anje"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "èe odgvovorite"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "in"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "na vpra¹anje"); //New for 0.98finalRC1
define("_PS_CON_OR", "ali"); //New for 0.98finalRC2
?>
