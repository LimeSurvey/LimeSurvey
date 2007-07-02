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
define("_YES", "Kyllä");
define("_NO", "Ei");
define("_UNCERTAIN", "Epävarma");
define("_ADMIN", "Ylläpitäjä");
define("_TOKENS", "Avaimet");
define("_FEMALE", "Nainen");
define("_MALE", "Mies");
define("_NOANSWER", "Ei vastausta");
define("_NOTAPPLICABLE", "Ei hyväksyttävä"); //New for 0.98rc5
define("_OTHER", "Muu");
define("_PLEASECHOOSE", "Valitse");
define("_ERROR_PS", "Virhe");
define("_COMPLETE", "valmis");
define("_INCREASE", "Kasvata"); //NEW WITH 0.98
define("_SAME", "Sama"); //NEW WITH 0.98
define("_DECREASE", "Vähennä"); //NEW WITH 0.98
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Vahvistus");
define("_TOKEN_PS", "Avain");
define("_CONTINUE_PS", "Jatka");

//BUTTONS
define("_ACCEPT", "Hyväksy");
define("_PREV", "edellinen");
define("_NEXT", "seuraava");
define("_LAST", "viimeinen");
define("_SUBMIT", "lähetä");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Valitse yksi seuraavista");
define("_ENTERCOMMENT", "Kirjoita kommenttisi tähän");
define("_NUMERICAL_PS", "Tähän saa kirjoittaa pelkästään numeroita");
define("_CLEARALL", "Lopeta ja tyhjennä kysely");
define("_MANDATORY", "Tähän kysymykseen on pakko vastata");
define("_MANDATORY_PARTS", "Täytä kaikki kohdat");
define("_MANDATORY_CHECK", "Valitse ainakin yksi kohta");
define("_MANDATORY_RANK", "Arvioi kaikki kohdat");
define("_MANDATORY_POPUP", "Yksi tai useampi pakollinen kohta jäi vastaamatta. Et voi jatkaa, jollet vastaa niihin"); //NEW in 0.98rc4
define("_VALIDATION", "Tähän kysymykseen pitää vastata oikein"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "Yhteen tai useampaan kohtaan ei ole vastattu oikein. Et voi jatkaa, mikäli vastaukset ovat virheellisiä"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Muoto: VVVV-KK-PP");
define("_DATEFORMATEG", "(esim: 2003-12-25 on joulupäivä)");
define("_REMOVEITEM", "Poista tämä kohta");
define("_RANK_1", "Klikkaa kohtaa vasemmalla, jota");
define("_RANK_2", "arvostat eniten jatkaen kohtaan, jota arvostat vähiten.");
define("_YOURCHOICES", "Valintasi");
define("_YOURRANKING", "Arvostelusi");
define("_RANK_3", "Klikkaa saksien kuvaa kohdan oikealla puolella");
define("_RANK_4", "poistaaksesi viimeeksi lisätyn kohdan");
//From INDEX.PHP
define("_NOSID", "Et ole antanut tutkimuksen ID-numeroa");
define("_CONTACT1", "Ota yhteyttä");
define("_CONTACT2", "saadaksesi lisätietoja");
define("_ANSCLEAR", "Vastaukset on tyhjennetty");
define("_RESTART", "Aloita alusta");
define("_CLOSEWIN_PS", "Sulje tämä ikkuna");
define("_CONFIRMCLEAR", "Oletko varma, että tahdot tyhjentää kaikki vastauksesi");
define("_CONFIRMSAVE", "Oletko varma, että tahdot tallettaa kaikki vastauksesi?");
define("_EXITCLEAR", "Lopeta ja tyhjennä kysely");
//From QUESTION.PHP
define("_BADSUBMIT1", "En voi lähettää vastauksia - vastauksia ei ole olemassa.");
define("_BADSUBMIT2", "Virhe voi sattua, mikäli lähetit vastauksesi ja painoit 'päivitä'-painiketta selaimessa. Jos näin kävi, niin vastauksesi ovat tallessa.<br /><br />Jos sait tämän ilmoituksen kesken kyselyn, niin sinun pitäisi valita '<- TAKAISIN' selaimessa ja päivittää sivu. Menetätä sivun vastaukset, mutta muut vastaukset pysyvät tallessa. Tämä ilmoitus johtuu viasta palvelimessa.");
define("_NOTACTIVE1", "Vastauksia ei talletettu. Kysely ei ole vielä aktivoitu.");
define("_CLEARRESP", "Tyhjennä vastaukset");
define("_THANKS", "Kiitos");
define("_SURVEYREC", "Vastauksesi on talletettu.");
define("_SURVEYCPL", "Kysely on valmis");
define("_DIDNOTSAVE", "Ei talletettu");
define("_DIDNOTSAVE2", "Odottamaton virhe sattui ja vastauksia ei talletettu.");
define("_DIDNOTSAVE3", "Vastauksesi eivät ole menneet hukkaan vaan on lähetetty sähköpostilla, josta hän ne myöhemmin tallettaa.");
define("_DNSAVEEMAIL1", "Virhe sattui talletettaessa kysymyksen ID-kenttää");
define("_DNSAVEEMAIL2", "TALLETETTAVA DATA");
define("_DNSAVEEMAIL3", "SQL KOODI, JOKA AIHEUTTI VIRHEEN");
define("_DNSAVEEMAIL4", "VIRHEILMOITUS");
define("_DNSAVEEMAIL5", "VIRHE TALELTETTAESSA");
define("_SUBMITAGAIN", "Yritä lähettää uudelleen");
define("_SURVEYNOEXIST", "Ei tutkimusta tuolla ID:llä.");
define("_NOTOKEN1", "Tämä on rajoitettu kysely. Tarvitset oikean avaimen.");
define("_NOTOKEN2", "Jos sinulla on avain, niin kirjoita se allaolevaan laatikkoon ja klikkaa 'jatka'.");
define("_NOTOKEN3", "Tarjoamasi avain on joko epäkelpo tai käytetty.");
define("_NOQUESTIONS", "Kyselyssä ei ole vielä yhtään kysymystä.");
define("_FURTHERINFO", "Saadaksesi lisätietoja, ota yhteyttä");
define("_NOTACTIVE", "Kysely ei ole nyt aktiivinen. Et voi tallettaa vastauksiasi.");
define("_SURVEYEXPIRED", "Kysely ei ole enää käytettävissä.");

define("_SURVEYCOMPLETE", "Olet jo vastannut kyselyyn."); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "Valitse vain yksi seuraavista"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Valitse kaikki sopivat"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Kysely talletettu"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Uusi vastaus on lähetetty kyselyyn"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Klikkaa seuraavaa linkkiä nähdäksesi yksittäiset vastaukset:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Katso tilastot klikkaamalla tästä:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE5", "Klikkaa seuraavaa linkkiä muokataksesi yksittäistä vastausta:"); //NEW for 0.99stable

define("_PRIVACY_MESSAGE", "<strong><i>Yksityisyydestä</i></strong><br />"
						  ."Vastaat kyselyyn nimettömänä.<br />"
						  ."Tietokantaan ei talleteta mitään tietoja "
						  ."joista vastaaja kävisi ilmi. "
						  ."Jos vastasit "
						  ."kyselyyn, joka käytti avaimia tutkimukseen pääsemiseen, "
						  ."niin voit olla varma, että avainta ei ole liitetty  "
						  ."vastausten oheen. Avaimia säilytetään erillisessä tietokannassa, "
						  ."johon päivitetään vain sen  se tieto, että olet (tai et ole)"
						  ."vastannut kyselyyn. Ei ole olemassa mitään keinoa, jolla avaimet  "
						  ."voitaisiin yhdistää vastauksiisi."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "Kyselyssä on {NUMBEROFQUESTIONS} kysymystä."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Kyselyssä on yksi kysymys."); //New for 0.98rc9 - singular version of above
						  
define ("_RG_REGISTER1", "Sinun pitää olla rekisteröitynyt tehdäksesi kyselyn"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Sinä voit rekisteröityä, jos haluat osallistua kyselyyn.<br />\n"
						."Anna omat tietosi alla ja saat heti sähköpostiisi linkin, "
						."jonka kautta voit vastata tähän kyselyyn."); //NEW for 0.98rc9
define ("_RG_EMAIL", "Sähköpostiosoite"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Etunimi"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Sukunimi"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "Antamasi sähköpostiosoite ei ole oikea, tarkista osoite.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "Antamasi sähköpostiosoite on jo rekisteröity.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} vahvistus rekisteröinnistä");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Kiitos siitä, että rekisteröidyit osallistuaksesi tähän kyselyyn.<br /><br />\n"
								   ."Antamaasi sähköpostiosoitteeseen on lähetetty tiedot, joilla voit osallistua kyselyyn. "
								   ."Klikkaa viestissä ollutta linkkiä osallistuaksesi kyselyyn.<br /><br />\n"
								   ."Kyselyn vastuuhenkilö {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<strong>Kiitos<br /><br />"
					   ."Olet vastannut kyselyn kaikkiin kysymyksiin.</strong><br /><br />"
					   ."Paina ["._SUBMIT."] lopettaaksesi ja tallettaaksesi vastaukset.");
define("_SM_REVIEW", "Jos tahdot tarkistaa vastaukset, jotka annoit, ja muuttaa niitä "
					."voit tehdä sen painamalla [<< "._PREV."] painiketta ja selaamalla "
					."vastauksesi.");

//For the "printable" survey
define("_PS_CHOOSEONE", "Valitse <strong>vain yksi</strong> seuraavista:"); //New for 0.98finalRC1
define("_PS_WRITE", "Kirjoita vastauksesi tähän:"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Valitse <strong>kaikki</strong> sopivat:"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Valitse kaikki sopivat ja kirjoita vielä kommentti:"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Valitse sopiva vastaus kuhunkin kohtaan:"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Kirjoita vastauksesi tähän:"); //New for 0.98finalRC1
define("_PS_DATE", "Anna päivämäärä:"); //New for 0.98finalRC1
define("_PS_COMMENT", "Kommentoi vastauksestasi tähän:"); //New for 0.98finalRC1
define("_PS_RANKING", "Numeroi joka laatikko mieltymyksesi mukaan alkaen luvusta 1 lukuun"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Talleta vastaukset."); //New for 0.98finalRC1
define("_PS_THANKYOU", "Kiitos, kun vastasit tähän kyselyyn."); //New for 0.98finalRC1
define("_PS_FAXTO", "Faksaa vastauksesi numeroon:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Vastaa tähän kysymykseen vain"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "jos vastasit"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "ja"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "kysymykseen"); //New for 0.98finalRC1
define("_PS_CON_OR", "tai"); //New for 0.98finalRC2

//Save Messages
define("_SAVE_AND_RETURN", "Talleta tämänhetkiset vastaukset");
define("_SAVEHEADING", "Talleta keskeneräinen kysely");
define("_RETURNTOSURVEY", "Palaa kyselyyn");
define("_SAVENAME", "Nimi");
define("_SAVEPASSWORD", "Salasana");
define("_SAVEPASSWORDRPT", "Salasana uudestaan");
define("_SAVE_EMAIL", "Sähköposti");
define("_SAVEEXPLANATION", "Anna nimi ja salasana kyselylle ja paina alla olevaa talleta painiketta.<br />\n"
				  ."Sinun vastauksesi talletetaan käyttäen antamaasi nimeä ja salasanaa "
				  ."ja voit täydentää ne valmiiksi myöhemmin.<br /><br />\n"
				  ."Jos annat sähköpostiosoitteen, niin saat postiisi viestin, jossa on tarkemmat yksityiskohdat.");
define("_SAVESUBMIT", "Talleta");
define("_SAVENONAME", "Sinun pitää antaa talletettavalle istunnolle nimi.");
define("_SAVENOPASS", "Sinun pitää antaa talletettavalle istunnolle salasana.");
define("_SAVENOMATCH", "Salasanat eivät täsmää.");
define("_SAVEDUPLICATE", "Tätä nimeä on jo käytetty vastausten tallettamiseen. Keksi toinen nimi.");
define("_SAVETRYAGAIN", "Yritä uudestaan.");
define("_SAVE_EMAILSUBJECT", "Talletettujen vastausten yksityiskohdat");
define("_SAVE_EMAILTEXT", "Sinä, tai joku muu sinun sähköpostiosoitetta käyttänyt, on tallettanut "
						 ."kyselyvastaukset. Käytä seuraavia tietoja "
						 ."palataksesi kyselyyn ja täydentääksesi vastaukset. ");
define("_SAVE_EMAILURL", "Päivitä kysely klikkaamalla seuraavaa osoitetta:");
define("_SAVE_SUCCEEDED", "Vastauksesi on talletettu onnistuneesti");
define("_SAVE_FAILED", "Tapahtui virhe ja vastauksiasi ei talletettu.");
define("_SAVE_EMAILSENT", "Yksityiskohdat on lähetetty sinulle sähköpostiisi.");

//Load Messages
define("_LOAD_SAVED", "Lataa keskeneräiset vastaukset");
define("_LOADHEADING", "Lataa aiemmin täytetty kysely");
define("_LOADEXPLANATION", "Voit ladata vastaukset, jotka on aiemmin talletettu.<br />\n"
			  ."Kirjoita istunnon 'nimi', jolla talletit vastaukset ja salasana.<br /><br />\n");
define("_LOADNAME", "Tallennusnimi");
define("_LOADPASSWORD", "Salasana");
define("_LOADSUBMIT", "Lataa nyt");
define("_LOADNONAME", "Et antanut nimeä");
define("_LOADNOPASS", "Et antanut salasanaa");
define("_LOADNOMATCH", "Antamillasi tiedoilla ei löytynyt talletettuja vastauksia.");

define("_ASSESSMENT_HEADING", "Palautteesi");
?>
