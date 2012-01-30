<?php
/*
	#############################################################
	# >>> PHPSurveyor					    					#
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
	# Croatian Language File				    				#
	# Created by Katarina Pavić [kate@mi2.hr]					#
	# Web Survey Methodology - http://www.websm.org/	    	#
	#							    							#
	#############################################################
*/
//SINGLE WORDS
define("_YES", "Da");
define("_NO", "Ne");
define("_UNCERTAIN", "Neodlučan/na");
define("_ADMIN", "Admin");
define("_TOKENS", "Šifra");
define("_FEMALE", "Ženski");
define("_MALE", "Muški");
define("_NOANSWER", "Bez odgovora");
define("_NOTAPPLICABLE", "N/A"); //New for 0.98rc5
define("_OTHER", "Drugo");
define("_PLEASECHOOSE", "Odaberite");
define("_ERROR_PS", "Greška");
define("_COMPLETE", "Dovršeno");
define("_INCREASE", "Povećaj"); //NEW WITH 0.98
define("_SAME", "Bez promjena"); //NEW WITH 0.98
define("_DECREASE", "Smanji"); //NEW WITH 0.98
define("_REQUIRED", "*"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Potvrdi");
define("_TOKEN_PS", "Šifra");
define("_CONTINUE_PS", "Nastavi");

//BUTTONS
define("_ACCEPT", "Prihvati");
define("_PREV", "Natrag");
define("_NEXT", "Naprijed");
define("_LAST", "Na kraj");
define("_SUBMIT", "Spremi podatke");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Molimo vas da odaberete jednu od sljedećih opcija");
define("_ENTERCOMMENT", "Molimo upišite komentar");
define("_NUMERICAL_PS", "Ovdje možete upisivati isključivo znamenke");
define("_CLEARALL", "Zatvori bez slanja odgovora");
define("_MANDATORY", "Na ovo pitanje obvezno je odgovoriti");
define("_MANDATORY_PARTS", "Molimo vas da odgovorite na sva pitanja");
define("_MANDATORY_CHECK", "Molimo vas da unesete bar jedan odgovor");
define("_MANDATORY_RANK", "Molimo vas da rangirajte sve odgovore");
define("_MANDATORY_POPUP", "Niste odgovorili na jedno ili više obaveznih pitanja. Da biste nastavili odgovorite na sva obavezna pitanja."); //NEW in 0.98rc4
define("_VALIDATION", "Na ovo pitanje morate točno odgovoriti"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "Dali ste netočan odgovor na jedno ili više pitanja. Da biste nastavili unesite ispravan odgovor."); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "U obliku: LLLL-MM-DD");
define("_DATEFORMATEG", "(npr.: 2005-12-25 za Božić");
define("_REMOVEITEM", "Ukloni");
define("_RANK_1", "Kliknite na listu s lijeve strane, počevši s vašim");
define("_RANK_2", "poredaj od najvišeg prema najnižem");
define("_YOURCHOICES", "Vaš izbor");
define("_YOURRANKING", "Vaša ocijena");
define("_RANK_3", "Kliknite na škare na desnoj strani");
define("_RANK_4", "Poništite zadnji unos");
//From INDEX.PHP
define("_NOSID", "Nemate identifikacijski broj");
define("_CONTACT1", "Molimo, javite se");
define("_CONTACT2", "za daljnju pomoć");
define("_ANSCLEAR", "Odgovori su obrisani");
define("_RESTART", "Ponovo započni s ispunjavanjem upitnika");
define("_CLOSEWIN_PS", "Zatvori prozor");
define("_CONFIRMCLEAR", "Jeste li sigurni da želite obrisati sve odgovore?");
define("_CONFIRMSAVE", "Želite li spremiti vaše odgovore?");
define("_EXITCLEAR", "Zatvori upitnik bez slanja odgovora");
//From QUESTION.PHP
define("_BADSUBMIT1", "Odgovori neće biti spremljeni - nema odgovora");
define("_BADSUBMIT2", "Ova greška pojavljuje se ukoliko ste već spremili vaše odgovore te kliknuli 'referesh' u pregledniku. Vaši odgovori već su spremljeni.<br /><br />Ukoliko primite ovu poruku tokom ispunjavanja upitnika pritisnite za '<- NATRAG' ('<- BACK') u vašem pregledniku i stranica će se osvježiti. Izgubit ćete odgovore sa posljednje stranice, ali će ostali biti spremljeni. Ovaj problem može biti izazvan visokim opterećenjem web servera. Ispričavamo se zbog neugodnosti.");
define("_NOTACTIVE1", "Vaši odgovori nisu spremljeni. Upitnik nije aktivan");
define("_CLEARRESP", "Obriši sve odgovore.");
define("_THANKS", "Hvala");
define("_SURVEYREC", "Vaši odgovori su spremljeni");
define("_SURVEYCPL", "Završili ste s ispunjavanjem upitnika");
define("_DIDNOTSAVE", "Nije spremljeno.");
define("_DIDNOTSAVE2", "Došlo je do neočekivane pogreške. Vaši odgovori ne mogu se spremiti.");
define("_DIDNOTSAVE3", "Vaši odgovori nisu izgubljeni: poslani su administratoru upitnika i koji će ih naknadno uključiti u rezultate.");
define("_DNSAVEEMAIL1", "Greška pri pokušaju unosa identifikacijskog broja");
define("_DNSAVEEMAIL2", "Podaci za unos");
define("_DNSAVEEMAIL3", "Greška pri upisivanju SQL koda");
define("_DNSAVEEMAIL4", "GREŠKA");
define("_DNSAVEEMAIL5", "GREŠKA U SPREMANJU");
define("_SUBMITAGAIN", "Pokušajte ponovno");
define("_SURVEYNOEXIST", "Ovaj upitnik ne postoji.");
define("_NOTOKEN1", "Ovaj upitnik nije dostupan javnosti. Za sudjelovanje je potrebna šifra.");
define("_NOTOKEN2", "Ukoliko ste dobili šifru, molimo, unesite je u donju kućicu i kliknite na dugme Nastavi.");
define("_NOTOKEN3", "Šifra koju ste unijeli je neispravna ili je već iskorištena.");
define("_NOQUESTIONS", "Ovaj upitnik još nema pitanja, te ne može biti testiran ni završen.");
define("_FURTHERINFO", "Za dodatne informacije obratite se:");
define("_NOTACTIVE", "Ovaj upitnik trenutno nije aktivan. Vaši odgovori neće biti spremljeni.");
define("_SURVEYEXPIRED", "Upitnik više nije dostupan.");

define("_SURVEYCOMPLETE", "Ovaj upitnik ste već ispunili."); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "Odaberite samo jedno od ponuđenog"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Odaberite sve što se odnosi na tvrdnju"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Upitnik je poslan"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Zabilježen je novi unos u upitniku"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Ako želite vidjeti odgovore kliknite na:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Statistike pogledajte ovdje:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE5", "Kliknite na sljedeći link ako želite mijenjati vaše odgovore:"); //NEW for 0.99stable

/* define("_PRIVACY_MESSAGE", "<strong><i>Obavijest o privatnosti</i></strong><br />"  ."Ovaj upitnik je anonimnan.<br />" ."Vaš odgovori na pitanja u upitniku ne sadrže nikakve " ."osobne podatke, osim u slučaju kad su ta pitanja eksplicitno "  ."postavljena u upitniku. Ukoliko ste ispunili  "  ."upitnik za pristup kojemu je bila potrebna indetifikacija putem e-maila .", možete biti sigurni da adresa" ."nije sačuvana zajedno s vašim odgovorima. Ona je spremljena " ."u posebnoj bazi podataka i bit će " ."upotrebljena isključivo kao dokaz da ste "				 ."(ili niste) ispunili ovaj upitnik: ne postoji način "."na koji bi se vaši osobni podaci (npr. e-mail adresa) povezali s vašim odgovorima u ovom upitniku." );*/

define("_THEREAREXQUESTIONS", "Ovaj upitnik sadrži {NUMBEROFQUESTIONS} pitanja."); //New for 0.98rc9 Treba sadržavati {NUMBEROFQUESTIONS} koji će biti zamjenjeni brojačem pitanja.
define("_THEREAREXQUESTIONS_SINGLE", "U ovom upitniku samo je jedno pitanje."); //New for 0.98rc9 - singular version of above

define ("_RG_REGISTER1", "Ukoliko želite ispuniti ovaj upitnik, morate se registrirati."); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Ukoliko želite ispuniti ovaj upitnik, morate se registrirati.<br />\n"
						."Upišite i pošaljite vaše podatke. Link za sudjelovanje primit ćete za nekoliko trenutaka putem maila. "); //NEW for 0.98rc9
define ("_RG_EMAIL", "E-mail adresa"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Ime"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Prezime"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "Unešena e-mail adresa nije valjana. Pokušajte ponovo.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "E- mail adresa je već registrirana.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "Potrda registracije -- {SURVEYNAME}");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Hvala što ste se prijavili za sudjelovanje u ovom istraživanju.<br /><br />\n"
								   ."Na vašu e-mail adresu poslana poruka s podacima o pristupu upitniku "
								   ."Molimo vas provjerite e-mail i slijedite link kako biste ispunili upitnik.<br /><br />\n"
								   ."Hvala, {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<strong>Hvala<br /><br />"
					   ."Odgovorili ste na sva pitanja u upitniku.</strong><br /><br />"
					   ."Kliknite na ["._SUBMIT."] da biste završili i spremili odgovore ."); //New for 0.98finalRC1
define("_SM_REVIEW", "Ukoliko želite provjeriti ili promijeniti odgovore, "
					."kliknite na [<< "._PREV."]  ."); //New for 0.98finalRC1


//For the "printable" survey
define("_PS_CHOOSEONE", "Molimo, odaberite  <strong> jedan </strong> od ponuđenih odgovora:"); //New for 0.98finalRC1
define("_PS_WRITE", "Ovdje upišite odgovor:"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Molimo odaberite <strong>sve</strong>što se odnosi na tvrdnju:"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Molimo vas da odaberete sve što se odnosi na tvrdnju i unesete komentar:"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Molimo vas da odaberete primjeren odgovor za svaku tvrdnju."); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Molimo vas da upišite odgovor:"); //New for 0.98finalRC1
define("_PS_DATE", "Molimo vas da upišete datum:"); //New for 0.98finalRC1
define("_PS_COMMENT", "Komentirajte svoj izbor:"); //New for 0.98finalRC1
define("_PS_RANKING", "Molimo vas da obilježite svako polje s obzirom na vaše preferencije od 1 do 10"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Pošalji upitnik."); //New for 0.98finalRC1
define("_PS_THANKYOU", "Zahvaljujemo na vašem sudjelovanju u upitniku."); //New for 0.98finalRC1
define("_PS_FAXTO", "Molimo, faksirajte vaš upitnik na:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Odgovorite samo na ovo pitanje"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "Ukoliko ste odgovorili"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "i"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "na pitanje"); //New for 0.98finalRC1
define("_PS_CON_OR", "ili"); //New for 0.98finalRC2

//Save Messages
define("_SAVE_AND_RETURN", "Spremi do sada unesene odgovore");
define("_SAVEHEADING", "Spremi nedovršeni upitnik");
define("_RETURNTOSURVEY", "Nastavi ispunjavati upitnik");
define("_SAVENAME", "Ime");
define("_SAVEPASSWORD", "Šifra");
define("_SAVEPASSWORDRPT", "Šifra (potvrdi)");
define("_SAVE_EMAIL", "E-mail");
define("_SAVEEXPLANATION", "Unesite ime i šifru za ovaj upitnik i kliknite na spremi.<br />\n"
				  ."Vaš upitnik će biti spremljen pod navedenim imenom i šifrom: možete ga"
				  ." nastaviti ispunjati u bilo koje vrijeme koristeći iste podatke za pristup.<br /><br />\n"
				  ."Ukoliko unesete vašu e-mail adresu, poslat ćemo vam e-mail sa informacijama o upitniku. ");
define("_SAVESUBMIT", "Spremi");
define("_SAVENONAME", "Morate unijeti ime kako biste pristupili spremljenom upitniku.");
define("_SAVENOPASS", "Morate unijeti lozinku kako biste prisupili spremljenom upitniku.");
define("_SAVENOMATCH", "Vaše šifre nisu jednake.");
define("_SAVEDUPLICATE", "Ovo ime je već iskorišteno u ovom upitniku. Kako biste spremili upitnik, vaše ime mora biti jedinstveno.");
define("_SAVETRYAGAIN", "Molimo, pokušajte ponovno.");
define("_SAVE_EMAILSUBJECT", "Informacije o spremljenom upitniku");
define("_SAVE_EMAILTEXT", "Vi ili netko tko koristi vašu e-mail adresu spremio je "
						 ."nedovršeni upitnik. Koristite ove podatke kako bi "
						 ."nastavili s ispunjavanjem upitnika:");
define("_SAVE_EMAILURL", "Nastavite s ispunjavanjem upitnika klikom na ovaj URL:");
define("_SAVE_SUCCEEDED", "Vaši odgovori su uspiješno spremljeni");
define("_SAVE_FAILED", "Došlo je do pogreška. Vaši odgovori nisu spremljeni");
define("_SAVE_EMAILSENT", "Poslan vam je e-mail s vašim odgovorima na upitnik.");

//Load Messages
define("_LOAD_SAVED", "Učitaj nedovršeni upitnik");
define("_LOADHEADING", "Učitaj prethodno spremljeni upitnik");
define("_LOADEXPLANATION", "Ovdje možete učitati prethodno spremljeni upitnik.<br />\n"
			  ."Ukucajte ime i šifru koju ste koristili pri spremanju upitnika.<br /><br />\n");
define("_LOADNAME", "Spremljeno ime");
define("_LOADPASSWORD", "Šifra");
define("_LOADSUBMIT", "Učitaj");
define("_LOADNONAME", "Niste upisali ime");
define("_LOADNOPASS", "Niste upisali šifru");
define("_LOADNOMATCH", "Nema odgovarajućeg spremljenog upitnika");

define("_ASSESSMENT_HEADING", "Vaša procjena");
?>
