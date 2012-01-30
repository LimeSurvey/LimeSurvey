<?php
/*
	#############################################################
	# >>> PHPSurveyor  										#
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
define("_YES", "Da");
define("_NO", "Nu");
define("_UNCERTAIN", "Nu sunt sigur");
define("_ADMIN", "Administrator");
define("_TOKENS", "Coduri");
define("_FEMALE", "Feminin");
define("_MALE", "Masculin");
define("_NOANSWER", "Nu raspund");
define("_NOTAPPLICABLE", "Nu se aplica"); //New for 0.98rc5
define("_OTHER", "Altele");
define("_PLEASECHOOSE", "Va rugam alegeti");
define("_ERROR_PS", "Eroare");
define("_COMPLETE", "Complet");
define("_INCREASE", "In crestere"); //NEW WITH 0.98
define("_SAME", "La fel"); //NEW WITH 0.98
define("_DECREASE", "In descrestere"); //NEW WITH 0.98
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Confirmare");
define("_TOKEN_PS", "Cod");
define("_CONTINUE_PS", "Continua");

//BUTTONS
define("_ACCEPT", "Accepta");
define("_PREV", "inapoi");
define("_NEXT", "inainte");
define("_LAST", "ultimul");
define("_SUBMIT", "trimite");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Va rugam alegeti un raspuns dintre urmatoarele");
define("_ENTERCOMMENT", "Va rugam introduceti un comentariu aici");
define("_NUMERICAL_PS", "In acest camp pot fi introduse doar numere");
define("_CLEARALL", "Iesire si anulare ancheta");
define("_MANDATORY", "Aceasta intrebare este obligatorie");
define("_MANDATORY_PARTS", "Va rugam completati in intregime");
define("_MANDATORY_CHECK", "Va rugam selectati cel putin un item");
define("_MANDATORY_RANK", "Va rugam ordonati toti itemii");
define("_MANDATORY_POPUP", "Nu ati raspuns la una sau mai multe intrebari obligatorii. Nu puteti continua pana nu oferiti raspunsuri complete"); //NEW in 0.98rc4
define("_VALIDATION", "La aceasta intrebare nu ati raspuns corect"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "Nu puteti merge mai departe deoarece la una sau mai multe intrebari nu ati dat raspunsuri valide"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Format: AAAA-LL-ZZ");
define("_DATEFORMATEG", "(ex: 2005-12-25 pentru ziua de Craciun a anului 2005)");
define("_REMOVEITEM", "Sterge acest item");
define("_RANK_1", "Faceti clic pe un item din lista din stanga, incepand cu");
define("_RANK_2", "itemul de pe primul loc si continuand pana la itemul de pe ultimul loc.");
define("_YOURCHOICES", "Variantele de ordonat");
define("_YOURRANKING", "Ordinea dumneavoastra");
define("_RANK_3", "Faceti clic pe foarfecele de langa fiecare item din dreapta");
define("_RANK_4", "pentru a sterge ultimul element din lista");
//From INDEX.PHP
define("_NOSID", "Nu ati furnizat un cod de identificare pentru aceasta ancheta");
define("_CONTACT1", "Va rugam contactati");
define("_CONTACT2", "pentru informatii suplimentare");
define("_ANSCLEAR", "Raspunsurile au fost sterse");
define("_RESTART", "Reluati aceasta ancheta");
define("_CLOSEWIN_PS", "Inchideti fereastra");
define("_CONFIRMCLEAR", "Sunteti sigur ca vreti sa stergeti toate raspunsurile?");
define("_CONFIRMSAVE", "Sunteti sigur ca vreti sa salvati toate raspunsurile?");
define("_EXITCLEAR", "Iesire si stergere ancheta");
//From QUESTION.PHP
define("_BADSUBMIT1", "Nu se pot trimite rezultatele - nu exista nici unul.");

define("_BADSUBMIT2", "Aceasta eroare poate surveni daca ati trimis deja raspunsurile si ati apasat butonul 'refresh' al browserului. In acest caz, raspunsurile dumneavoastra au fost deja salvate.<br /><br />Daca ati primit acest mesaj in timp ce completati chestionarul, apasati butonul '<- BACK' al browserului si reincarcati pagina anterioara. Desi veti pierde raspunsurile de pa ultima pagina, toate celelalte vor fi pastrate. Aceasta problema poate aparea daca serverul este supraincarcat sau excesiv utilizat. Ne cerem scuze pentru aceasta problema.");



define("_NOTACTIVE1", "Raspunsurile dumneavoasra nu au fost inregistrate. Ancheta nu este activa.");
define("_CLEARRESP", "Sterge raspunsurile");
define("_THANKS", "Va multumim");
define("_SURVEYREC", "Raspunsurile dumneavoasra au fost inregistrate.");
define("_SURVEYCPL", "Chestionarul a fost completat in intregime");
define("_DIDNOTSAVE", "Raspunsurile nu au fost salvate");
define("_DIDNOTSAVE2", "S-a produs o eroare neasteptata si raspunsurile dumneavoastra nu pot fi salvate.");
define("_DIDNOTSAVE3", "Raspunsurile dumneavoastra nu au fost pierdute. Ele au fost trimise prin email administratorului si vor fi introduse in baza de date mai tarziu.");
define("_DNSAVEEMAIL1", "An error occurred saving a response to survey id");
define("_DNSAVEEMAIL2", "DATE DE INTRODUS");
define("_DNSAVEEMAIL3", "CODUL SQL A ESUAT");
define("_DNSAVEEMAIL4", "MESAJ DE EROARE");
define("_DNSAVEEMAIL5", "EROARE LA SALVARE");
define("_SUBMITAGAIN", "Incercati sa trimiteti din nou");
define("_SURVEYNOEXIST", "Ne pare rau, nu exista o astfel de ancheta.");
define("_NOTOKEN1", "Aceasta este o ancheta controlata. Aveti nevoie de un numar de cod valid pentru a participa.");
define("_NOTOKEN2", "Daca vi s-a emis un numar de cod, va rugam sa-l introduceti in campul de mai jos si sa apasati pe butonul continua.");
define("_NOTOKEN3", "Numarul de cod furnizat de dumneavoastra nu este valid, sau a mai fost utilizat.");
define("_NOQUESTIONS", "Acest chestionar nu are intrebari, deci nu poate fi testat sau completat.");
define("_FURTHERINFO", "Pentru informatii suplimentare contactati");
define("_NOTACTIVE", "Aceast ancheta nu este valida. Nu veti putea salva raspunsurile.");
define("_SURVEYEXPIRED", "Aceasta ancheta nu mai este disponibila.");

define("_SURVEYCOMPLETE", "Ati completat deja acest chestionar de ancheta."); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "Alegeti o singura varianta din cele ce urmeaza"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Marcati toate raspunsurile care corespund"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Chestionarul a fost inregistrat"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Aveti un nou raspuns la ancheta dumneavoastra"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Apasati pe linkul urmator pentru a vedea raspunsurile:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Clic aici pentru a vedea statisticile:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE5", "Clic pe acest link pentru a edita raspunsul:"); //NEW for 0.99stable

define("_PRIVACY_MESSAGE", "<strong><i>O nota cu privire la confidentialitate</i></strong><br />"
						  ."Aceasta ancheta este anonima.<br />"
						  ."Raspunsurile dumneavoastra nu contin nici o "
						  ."informatie care ar ajuta la identificarea dumneavoastra, cu exceptia cazului in care o intrebare specifica "
						  ."din ancheta a cerut acest lucru. 
daca ati participat la o ancheta unde vi s-a cerut "
						  ."un cod de identificare pentru a vi se permite accesul "
						  ."va asiguram ca respectivul cod "
						  ."nu este alaturat raspunsurilor dumneavoastra. El este stocat intr-o alta "
						  ."baza de date, si va fi actualizat numai pentru a indica faptul ca "
						  ."ati completat (sau nu) chestionarul. Nu exista nici o modalitate de a alatura codurile de identificare cu raspunsurile subiectilor."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "Sunt {NUMBEROFQUESTIONS} intrebari in acest chestionar."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "In acest chestionar exista o singura intrebare."); //New for 0.98rc9 - singular version of above
						  
define ("_RG_REGISTER1", "Trebuie sa va inregistrati pentru a completa acest chestionar"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Daca doriti sa participati la aceasta ancheta, va puteti inregistra.<br />\n"
						."Introduceti datele dumneavostra, si un email cu linkul catre acest survey "
						."va va fi trimis imediat."); //NEW for 0.98rc9
define ("_RG_EMAIL", "Adresa de email"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Prenume"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Nume"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "Adresa de email nu este valida. Incercati din nou.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "Acest email a fost deja inregistrat.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} - inregistrare confirmata");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Va multumim pentru participarea la aceasta ancheta.<br /><br />\n"
								   ."V-a fost trimis un email cu detaliile privind accesul "
								   ."la ancheta. Folositi linkul din mesaj pentru a continua.<br /><br />\n"
								   ."Survey Administrator {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<strong>Va multumin<br /><br />"
					   ."Ati raspuns la toate intrebarile acestui chestionar.</strong><br /><br />"
					   ."Faceti clic pe ["._SUBMIT."] pentru a salva raspunsurile.");

define("_SM_REVIEW", "Daca dorti sa verificati raspunsurile sau sa le schimbati, "
					."puteti apasa pe butonul [<< "._PREV."]  pentru "
					."a revedea raspunsurile.");

//For the "printable" survey
define("_PS_CHOOSEONE", "Va rugam alegeti <strong>numai o varianta</strong> din urmatoarele:"); //New for 0.98finalRC1
define("_PS_WRITE", "Va rugam sa scrieti raspunsul aici:"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Va rugam selectati <strong>toate variantele</strong> care corespund:"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Va rugam selectati toate variantele care corespund si faceti un comentariu:"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Va rugam sa alegeti raspunsul potrivit pentru fiecare item:"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Va rugam scrieti raspunsurile aici:"); //New for 0.98finalRC1
define("_PS_DATE", "Introduceti o data:"); //New for 0.98finalRC1
define("_PS_COMMENT", "Faceti un comentariu la alegere aici:"); //New for 0.98finalRC1
define("_PS_RANKING", "Va rugam sa numerotati fiecare casuta in ordinea preferintelor, de la 1 la"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Trimiteti chestionarul."); //New for 0.98finalRC1
define("_PS_THANKYOU", "Va multumim pentru participarea la aceasta ancheta."); //New for 0.98finalRC1
define("_PS_FAXTO", "Va rugam trimiteti chestionarul completat prin fax la:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Raspundeti numai la aceasta intrebare"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "daca ati raspuns"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "si"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "la intrebarea"); //New for 0.98finalRC1
define("_PS_CON_OR", "sau"); //New for 0.98finalRC2

//Save Messages
define("_SAVE_AND_RETURN", "Salvati raspunsurile date pana acum");
define("_SAVEHEADING", "Salvati chestionarul neterminat");
define("_RETURNTOSURVEY", "Intoarcere la chestionar");
define("_SAVENAME", "Nume");
define("_SAVEPASSWORD", "Parola");
define("_SAVEPASSWORDRPT", "Parola din nou");
define("_SAVE_EMAIL", "Email");
define("_SAVEEXPLANATION", "Introduceti un nume si o parola pentru aceasta ancheta si apasati butonul pentru salvare.<br />\n"
				  ."Ancheta va fi salvata sub numele si parola respectiva, si poate fi  "
				  ."completata mai tarziu daca faceti login cu acelasi nume si aceeasi parola.<br /><br />\n"
				  ."Daca furnizati o adresa de email, veti primi un mesaj continand "
		."detalii.");
define("_SAVESUBMIT", "Salvati acum");
define("_SAVENONAME", "Trebuie sa dati un nume acestei sesiuni session.");
define("_SAVENOPASS", "Trebuie sa dati o parola pentru aceasta sesiune.");
define("_SAVENOMATCH", "Parolele nu corespund.");
define("_SAVEDUPLICATE", "Acest nume a mai fost utilizat in aceasta ancheta. Numele trebuie sa fie unic.");
define("_SAVETRYAGAIN", "Incercati din nou.");
define("_SAVE_EMAILSUBJECT", "Detalii pentru ancheta salvata");
define("_SAVE_EMAILTEXT", "Dumneavoastra, sau cineva care a folosit adresa dumneavoastra de email, a salvat "
						 ."o ancheta in desfasurare. Detaliile ce urmeaza pot fi utilizate "
						 ."pentru a va intoarce la ancheta respectiva, la punctul unde ati "
						 ."lasat-o.");
define("_SAVE_EMAILURL", "Reincarcati ancheta apasand pe linkul urmator:");
define("_SAVE_SUCCEEDED", "Raspunsurile dumneavoastra au fost salvate");
define("_SAVE_FAILED", "A survenit o eroare si raspunsurile nu au fost salvate.");
define("_SAVE_EMAILSENT", "Veti primi un email cu detalii despre ancheta salvata.");

//Load Messages
define("_LOAD_SAVED", "Incarcati chestionarul neterminat");
define("_LOADHEADING", "Incarcati o ancheta salvata ulterior");
define("_LOADEXPLANATION", "Puteti incarca o ancheta pe care ati salvat-o anterior.<br />\n"
			  ."Introduceti 'numele' pe care l-ati folosit la salvare anchetei si parola.<br /><br />\n");
define("_LOADNAME", "Nume salvat");
define("_LOADPASSWORD", "Parola");
define("_LOADSUBMIT", "Incarcati acum");
define("_LOADNONAME", "Nu ati furnizat un nume");
define("_LOADNOPASS", "Nu ati furnizat o parola");
define("_LOADNOMATCH", "Nu a salvat nimeni o ancheta cu acest nume");

define("_ASSESSMENT_HEADING", "Evaluarea dumneavoastra");
?>
