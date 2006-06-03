<?php
/*
	#############################################################
	# >>> PHPSurveyor  											#
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
define("_YES", "Kyllä");
define("_NO", "Ei");
define("_UNCERTAIN", "En osaa sanoa");
define("_ADMIN", "Ylläpitäjä");
define("_TOKENS", "Tunnisteet");
define("_FEMALE", "Nainen");
define("_MALE", "Mies");
define("_NOANSWER", "Ei vastausta");
define("_NOTAPPLICABLE", "Ei tietoa"); //New for 0.98rc5
define("_OTHER", "Muu");
define("_PLEASECHOOSE", "Valitse");
define("_ERROR_PS", "Virhe");
define("_COMPLETE", "valmis");
define("_INCREASE", "Kasvaa"); //NEW WITH 0.98
define("_SAME", "Säilyy"); //NEW WITH 0.98
define("_DECREASE", "Pienenee"); //NEW WITH 0.98
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Vahvistus");
define("_TOKEN_PS", "Tunniste");
define("_CONTINUE_PS", "Jatka");

//BUTTONS
define("_ACCEPT", "Hyväksy");
define("_PREV", "Edellinen");
define("_NEXT", "Seuraava");
define("_LAST", "Viimeinen");
define("_SUBMIT", "Lähetä");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Valitse yksi seuraavista");
define("_ENTERCOMMENT", "Kommenttisi");
define("_NUMERICAL_PS", "Anna vastauksesi numeroina");
define("_CLEARALL", "Poistu tallentamatta");
define("_MANDATORY", "Kysymys on pakollinen");
define("_MANDATORY_PARTS", "Ole hyvä ja täydennä puuttuvat vastaukset");
define("_MANDATORY_CHECK", "Valitse vähintään yksi");
define("_MANDATORY_RANK", "Aseta kaikki kohdat järjestykseen");
define("_MANDATORY_POPUP", "Yhdestä tai useammasta pakollisesta kysymyksestä puuttuu vastaus. Täydennä vastaukset jatkaaksesi."); //NEW in 0.98rc4
define("_VALIDATION", "Vastauksen tulee olla oikeellisessa muodossa"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "Yhden tai useamman kysymyken vastaus ei ole oikeellisessa muodossa. Korjaa vastaukset jatkaaksesi."); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Muodossa: VVVV-KK-PP");
define("_DATEFORMATEG", "(esim: 2003-12-25)");
define("_REMOVEITEM", "Poista");
define("_RANK_1", "Klikkaa vasemmalla olevan listan kohtia tärkeysjärjestyksessä, aloittaen");
define("_RANK_2", "kaikken tärkeimmästä. Viimeiseksi klikkaat siis vähiten tärkeintä.");
define("_YOURCHOICES", "Valinnat");
define("_YOURRANKING", "Arviot");
define("_RANK_3", "Napsauttamalla saksia kunkin kohdan oikealla puolella");
define("_RANK_4", "voit poistaa listan viimeisen kohdan");
//From INDEX.PHP
define("_NOSID", "Kyselyn tunnistenumero puuttuu");
define("_CONTACT1", "Yhteyshenkilönä toimiva");
define("_CONTACT2", "auttaa ongelmatilanteissa");
define("_ANSCLEAR", "Vastaukset tyhjennetty");
define("_RESTART", "Palaa kyselyn alkuun");
define("_CLOSEWIN_PS", "Sulje ikkuna");
define("_CONFIRMCLEAR", "Vastauksiasi ei tallennneta. Oletko varma?");
define("_CONFIRMSAVE", "Vastauksesi tallennetaan. Oletko varma?");
define("_EXITCLEAR", "Poistu ja tyhjennä kysely");
//From QUESTION.PHP
define("_BADSUBMIT1", "Lomake on tyhjä joten sitä ei voitu lähettää");
define("_BADSUBMIT2", "Mikäli olet jo lähettänyt vastauksesi ja jostain syystä painoit juuri selaimen 'Lataa uudelleen'-painiketta, vastauksesi on tallennettu - ei huolta.<br /><br />Mikäli vastaamisesi on vielä kesken, klikkaa selaimen 'Takaisin'-painiketta ja sen jälkeen napsauta 'Lataa uudelleen'. Joudut täyttämään kyseisen sivun vastaukset uudelleen mutta aikemmat vastauksesi ovat tallessa. Virheen syynä on ruuhkainen verkkopalvelimenne - olemme pahoillamme.");
define("_NOTACTIVE1", "Vastauksia ei tallennettu. Kysely ei ole vielä aktiivinen.");
define("_CLEARRESP", "Tyhjennä vastaukset");
define("_THANKS", "Kiitokset");
define("_SURVEYREC", "Vastauksesi on tallennettu.");
define("_SURVEYCPL", "Kysely on valmis");
define("_DIDNOTSAVE", "Vastauksia ei tallennettu");
define("_DIDNOTSAVE2", "Järjestelmävirheen vuoksi vastauksia ei voitu tallentaa");
define("_DIDNOTSAVE3", "Vastaukset lähettiin ylläpitäjälle, joka tallentaa ne myöhemmin tietokantaan.");
define("_DNSAVEEMAIL1", "Lomakkeen tallennuksessa on tapahtunut virhe - kyselyn tunniste:");
define("_DNSAVEEMAIL2", "SYÖTETYT TIEDOT");
define("_DNSAVEEMAIL3", "SQL-LAUSE");
define("_DNSAVEEMAIL4", "VIRHEILMOITUS");
define("_DNSAVEEMAIL5", "Virhe tallennettaessa vastauksia tietokantaan");
define("_SUBMITAGAIN", "Yritä uudelleen");
define("_SURVEYNOEXIST", "Pahoittelumme - ei kyselyä.");
define("_NOTOKEN1", "Kysely on kohdennettu tietylle vastaajaryhmälle. Tarvitset tunnisteen osallistuaksesi.");
define("_NOTOKEN2", "Kirjoita saamasi tunniste allaolevaan kenttään ja napsauta 'Jatka'");
define("_NOTOKEN3", "Tunniste on virheellinen tai se on jo käytetty.");
define("_NOQUESTIONS", "Kyselyyn ei voi vielä vastata sillä siinä ei ole vielä kysymyksiä.");
define("_FURTHERINFO", "Lisätietoja:");
define("_NOTACTIVE", "Kysely ei ole aktiivinen. Vastauksia ei tallenneta.");
define("_SURVEYEXPIRED", "Kysely ei ole enää saatavilla.");

define("_SURVEYCOMPLETE", "Kyselyyn on jo vastattu."); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "Valitse yksi seuraavista"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Valitse mielestäsi sopivat"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Kysely on lähetetty"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Uusi vastaus kyselyssä"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Klikkaa linkkiä vastaukseen:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Näytä tilastot klikkaamalla tästä:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE5", "Klikkaa linkkä muokataksesi vastausta:"); //NEW for 0.99stable

define("_PRIVACY_MESSAGE", "<strong><i>Yksityisyyden suoja</i></strong><br />"
						  ."Kaikki vastaukset ovat anonyymejä.<br />"
						  ."Yksittäistä vastaajaa ei voida tunnistaa tallennettujen " 
						  ."tietojen perusteella, ellei kyselyssä erikseen pyydetä "
						  ."tunnistamisen mahdollistavia tietoja. Suljetussa kyselyssä"
						  ."käytetty tunnistekoodi pidetään erillään vastauksista ja  "
						  ."sitä ei voida käyttää yksittäisen vastaajan identifioimiseen. "
						  ); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "Kyselylomakkeessa on {NUMBEROFQUESTIONS} kysymystä."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Kyselyssä on vain yksi kysymys."); //New for 0.98rc9 - singular version of above
						  
define ("_RG_REGISTER1", "Rekisteröidy vastataksesi tutkimukseen"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Tutkimukseen osallistuminen vaatii rekisteröinnin.<br />\n"
						."Täydennä yhteystietosi ja saat välittömästi sähköpostitse "
						."linkin, jotka kautta voit osallistua tutkimukseen."); //NEW for 0.98rc9
define ("_RG_EMAIL", "Sähköposti"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Etunimi"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Sukunimi"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "Sähköpostiosoite on virheellinen. Ole hyvä ja yritä uudelleen.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "Sähköpostiosoitteeseen on jo lähetetty tutkimuslinkki.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "Osallistuminen {SURVEYNAME} -tutkimukseen");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Kiitos tutkimustamme kohtaan esittämästäsi mielenkiinnosta.<br /><br />\n"
								   ."Sähköpostiisi on lähetetty viesti, jossa on linkki kyselyymme. "
								   ."Klikkaa linkkiä päästäksesi kyselyyn.<br /><br />\n"
								   ."Yhteyshenkilö {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<strong>Kiitos!<br /><br />"
					   ."Olet vastannut kaikkiin kysymyksiin.</strong><br /><br />"
					   ."Klikkaa ["._SUBMIT."] tallentaaksesi vastaukset.");
define("_SM_REVIEW", "Jos haluat tarkistaa tai korjata vastauksiasi, "
					."voit palata kysymyksiin klikkaamalla [<< "._PREV."] -painiketta.");

//For the "printable" survey
define("_PS_CHOOSEONE", "Valitse <strong>yksi</strong> seuraavista:"); //New for 0.98finalRC1
define("_PS_WRITE", "Vastauksesi:"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Valitse <strong>kaikki</strong> sopivat:"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Valitse kaikki sopivat vaihtoehdot ja kommentoi:"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Valitse sopivin vaihtoehto:"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Kirjoita vastauksesi tähän:"); //New for 0.98finalRC1
define("_PS_DATE", "Päivämäärä:"); //New for 0.98finalRC1
define("_PS_COMMENT", "Vastauskommentti:"); //New for 0.98finalRC1
define("_PS_RANKING", "Aseta kohdat järjestykseen välillä 1-"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Lähetä vastaukset."); //New for 0.98finalRC1
define("_PS_THANKYOU", "Kiitos vastauksistasi!."); //New for 0.98finalRC1
define("_PS_FAXTO", "Faksaa täyttämäsi lomake numeroon:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Vastaa vain tähän kysymykseen"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "jos vastasit"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "ja"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "kysymykseen"); //New for 0.98finalRC1
define("_PS_CON_OR", "tai"); //New for 0.98finalRC2

//Save Messages
define("_SAVE_AND_RETURN", "Tallenna vastaukset ja jatka");
define("_SAVEHEADING", "Tallennä keskeneräiset vastaukset");
define("_RETURNTOSURVEY", "Palaa kyselyyn");
define("_SAVENAME", "Nimi");
define("_SAVEPASSWORD", "Salasana");
define("_SAVEPASSWORDRPT", "Toista salasana");
define("_SAVE_EMAIL", "Sähköposti");
define("_SAVEEXPLANATION", "Voit tallentaa keskeneräisen kyselyn jatkaaksesi sen täyttämistä myöhemmin.<br />\n"
				  ."Anna kyselyvastauksillesi nimi ja salasana, joita käyttäen voit palata kyselyyn. "
				  ."Mikäli annat myös sähköpostiosoitteen, saat viestin jossa on linkki kyselyvastaukseesi.<br /><br />\n");
define("_SAVESUBMIT", "Tallenna");
define("_SAVENONAME", "Tallennettavalla kyselyllä tulee olla nimi");
define("_SAVENOPASS", "Tallennettavalle kyselylle tulee antaa salasana.");
define("_SAVENOPASS2", "Muistitko toistaa salasanan?");
define("_SAVENOMATCH", "Salasanat eivät täsmää.");
define("_SAVEDUPLICATE", "Nimi on jo käytössä.");
define("_SAVETRYAGAIN", "Yritä uudelleen.");
define("_SAVE_EMAILSUBJECT", "Tallennettu kyselylomake");
define("_SAVE_EMAILTEXT", "Olet tallentanut keskeneräisen kyselyvastauksesi. "
						 ."Voit palata kyselyyn käyttäen allaolevia tietoja. ");
define("_SAVE_EMAILURL", "Palaa kyselyyn:");
define("_SAVE_SUCCEEDED", "Vastauksesi on tallennettu");
define("_SAVE_FAILED", "Pahoittelumme - järjestelmävirheen vuoksi vastauksiasi ei voitu tallentaa.");
define("_SAVE_EMAILSENT", "Sähköpostiisi on lähetty viesti, jossa olevien ohjeiden mukaan voit jatkaa vastaamista myöhemmin.");

//Load Messages
define("_LOAD_SAVED", "Palaa keskeneräiseen kyselyyn");
define("_LOADHEADING", "Jatka vastaamista");
define("_LOADEXPLANATION", "Anna nimi ja salasana, joita käytit vastauksiesi tallentamisessa<br />\n
			  .<br />\n");
define("_LOADNAME", "Nimi");
define("_LOADPASSWORD", "Salasana");
define("_LOADSUBMIT", "Palaa kyselyyn");
define("_LOADNONAME", "Nimi puuttuu");
define("_LOADNOPASS", "Salasana puuttuu");
define("_LOADNOMATCH", "Antamallasi nimellä ei ole tallennettu vastauksia");

define("_ASSESSMENT_HEADING", "Arvio");
?>