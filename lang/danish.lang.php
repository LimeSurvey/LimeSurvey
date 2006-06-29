<?php
/*
    #############################################################
    # >>> PHPSurveyor                                           #
    #############################################################
    # > Author:  Jason Cleeland                                 #
    # > E-mail:  jason@cleeland.org                             #
    # > Mail:    Box 99, Trades Hall, 54 Victoria St,           #
    # >          CARLTON SOUTH 3053, AUSTRALIA                  #
    # > Date:    20 February 2003                               #
    #                                                           #
    # This set of scripts allows you to develop, publish and    #
    # perform data-entry on surveys.                            #
    #############################################################
    #                                                           #
    #   Copyright (C) 2003  Jason Cleeland                      #
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
    #                                                           #
    #     Translation by Mikkel Skovgaard Sørensen              #
    #                and Rolf Njor Jensen                       #
    #                and Carsten Høilund                        #
    #############################################################
*/
//SINGLE WORDS
define("_YES", "Ja");
define("_NO", "Nej");
define("_UNCERTAIN", "Ved ikke");
define("_ADMIN", "Admin");
define("_TOKENS", "Nøgler");
define("_FEMALE", "Kvinde");
define("_MALE", "Mand");
define("_NOANSWER", "Intet svar");
define("_NOTAPPLICABLE", "Ved ikke"); //New for 0.98rc5
define("_OTHER", "Andet");
define("_PLEASECHOOSE", "Vælg venligst");
define("_ERROR_PS", "Fejl");
define("_COMPLETE", "gennemført");
define("_INCREASE", "Hæv"); //NEW WITH 0.98
define("_SAME", "Samme"); //NEW WITH 0.98
define("_DECREASE", "Sænk"); //NEW WITH 0.98
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Bekræftelse");
define("_TOKEN_PS", "Nøgle");
define("_CONTINUE_PS", "Fortsæt");

//BUTTONS
define("_ACCEPT", "Accepter");
define("_PREV", "forrige");
define("_NEXT", "næste");
define("_LAST", "afslut");
define("_SUBMIT", "Indsend");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Vælg en af følgende");
define("_ENTERCOMMENT", "Skriv dine kommentarer her");
define("_NUMERICAL_PS", "Dette felt kan kun indeholde tal/numeriske tegn");
define("_CLEARALL", "Nulstil og forlad undersøgelsen");
define("_MANDATORY", "Dette spørgsmål er obligatorisk");
define("_MANDATORY_PARTS", "Udfyld venligst alle dele");
define("_MANDATORY_CHECK", "Afkryds som minimum en mulighed");
define("_MANDATORY_RANK", "Afgiv venligst en score i alle felter");
define("_MANDATORY_POPUP", "En eller flere felter som skal udfyldes er ikke udfyldt - der kan ikke fortsættes før disse er udfyldt"); //NEW in 0.98rc4
define("_VALIDATION", "Dette spørgsmål skal besvares korrekt"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "Et eller flere spørgsmål er ikke blevet besvaret korrekt. Du kan ikke fortsætte, før disse svar er gyldige"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Datoformat: ÅÅÅÅ-MM-DD");
define("_DATEFORMATEG", "(f.eks. 2003-12-24, hvis der skal angives juledag)");
define("_REMOVEITEM", "Fjern denne mulighed");
define("_RANK_1", "Klik på et emne i listen til venstre, startende med det du");
define("_RANK_2", "vurderer højest, og klik derefter nedefter til det lavest vurderede emne.");
define("_YOURCHOICES", "Dine valg");
define("_YOURRANKING", "Din vurdering");
define("_RANK_3", "Klik på saksen til højre for at fjerne");
define("_RANK_4", "det nederste emne på din vurderingsliste");
//From INDEX.PHP
define("_NOSID", "Der mangler at blive angivet en undersøgelses-nøgle");
define("_CONTACT1", "Kontakt venligst");
define("_CONTACT2", "for videre assistance");
define("_ANSCLEAR", "Undersøgelse nulstillet");
define("_RESTART", "Nulstil og start forfra");
define("_CLOSEWIN_PS", "Luk dette vindue");
define("_CONFIRMCLEAR", "Er du sikker på at du vil nulstille alle dine spørgsmål?");
define("_CONFIRMSAVE", "Er du sikker på at du vil gemme dine svar?");
define("_EXITCLEAR", "Nulstil og forlad undersøgelsen.");
//From QUESTION.PHP
define("_BADSUBMIT1", "Kan ikke gemme besvarelsen - der er ikke noget at gemme.");
define("_BADSUBMIT2", "Denne fejl er opstået fordi du allerede har gemt dine svar og har trykket på 'Opdater' i din browser. Dine besvarelser er allerede gemt.<br /><br />Hvis du har fået denne fejlmeddelse midt i en spørgeskema-undersøgelse bør du trykke på '<- Tilbage'-knappen i din browser efterfulgt af 'Opdater'. Dermed vil dit forrige svar gå tabt, men alle andre tidligere svar er gemt. Vi beklager de gener dette måtte medføre.");
define("_NOTACTIVE1", "Dine besvarelser er ikke gemt - undersøgelsen er endnu ikke sat i gang.");
define("_CLEARRESP", "Nulstil svar");
define("_THANKS", "Tak");
define("_SURVEYREC", "Dine svar er blevet gemt.");
define("_SURVEYCPL", "Undersøgelsen er gennemført");
define("_DIDNOTSAVE", "Kunne ikke gemme");
define("_DIDNOTSAVE2", "Der skete en uventet fejl og din besvarelse kunne ikke gemmes.");
define("_DIDNOTSAVE3", "Din besvarelse er ikke gået tabt, men er sendt til administratoren af undersøgelsen, som så senere tilføjer disse.");
define("_DNSAVEEMAIL1", "An error occurred saving a response to survey id");
define("_DNSAVEEMAIL2", "DATA TO BE ENTERED");
define("_DNSAVEEMAIL3", "SQL CODE THAT FAILED");
define("_DNSAVEEMAIL4", "ERROR MESSAGE");
define("_DNSAVEEMAIL5", "ERROR SAVING");
define("_SUBMITAGAIN", "Prøv igen");
define("_SURVEYNOEXIST", "Beklager, men kunne ikke finde undersøgelses-nøgle der matcher det valgte.");
define("_NOTOKEN1", "Dette er en lukket undersøgelse og kræver at du har en undersøgelses-nøgle for at deltage.");
define("_NOTOKEN2", "Hvis du har en undersøgelses-nøgle så indtast den herunder.");
define("_NOTOKEN3", "Den undersøgelses-nøgle, du har angivet, er ugyldig, eller er allerede brugt.");
define("_NOQUESTIONS", "Denne undersøgelse har endnu ingen spørgsmål og kan derfor ikke besvares.");
define("_FURTHERINFO", "For yderligere information kontakt");
define("_NOTACTIVE", "Denne undersøgelse er ikke aktiv og du kan derfor ikke deltage.");
define("_SURVEYEXPIRED", "Denne undersøgelse er ikke længere aktiv og du kan derfor ikke deltage.");

define("_SURVEYCOMPLETE", "Du har allerede gennemført denne undersøgelse."); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "Vælg kun en af nedenstående"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Vælg alle du er enig i"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Undersøgelsen er gemt"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Et nyt svar er gemt i undersøgelsen"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Klik på nedenstående link for at se de individuelle svar:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Vis statistikken her:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE5", "Klik på nedenstående link for at redigere de individuelle svar:"); //NEW for 0.99stable

define("_PRIVACY_MESSAGE", "<strong><i>Om beskyttelse af privatlivets fred</i></strong><br />"
                          ."Denne undersøgelse er anonym.<br />"
                          ."De gemte data indeholder ingen personhenførbare oplysninger, "
                          ."medmindre der i undersøgelsen er blevet spurgt specifikt efter "
						  ."dette. Hvis du har deltaget i en undersøgelse, der benytter "
						  ."nøgler som adgangskontrol, kan du være sikker på at nøglen ikke "
						  ."bliver gemt sammen med din besvarelse. Nøgler bliver gemt i en "
						  ."separat database, og bliver kun opdateret for at angive, om du har "
                          ."(eller ikke har) gennemført undersøgelsen. Det kan ikke lade sig "
                          ."gøre at sammenholde nøgler og besvarelser."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "Der er {NUMBEROFQUESTIONS} spørgsmål i denne undersøgelse."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Der er ét spørgsmål i denne undersøgelse."); //New for 0.98rc9 - singular version of above

define ("_RG_REGISTER1", "Du skal være registreret for at deltage i denne undersøgelse"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Du kan deltage i denne undersøgelse ved at udfylde skemaet herunder.<br />\n"
                        ."Udfyld nedenstående formular og du vil få tilsendt en"
						."e-mail med en henvisning så du kan deltage i undersøgelsen."); //NEW for 0.98rc9
define ("_RG_EMAIL", "E-mail-adresse"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Fornavn"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Efternavn"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "Den angivne e-mail-adresse er ugyldig.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "Den angivne e-mail-adresse er allerede registeret.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} Registreringsbekræftelse");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Tak fordi du vælger at deltage i undersøgelsen.<br /><br />\n"
                                   ."En e-mail er sendt til den angivne e-mail-adresse, hvori der findes"
                                   ."informationer om hvordan du deltager i undersøgelsen. Benyt henvisningen i e-mailen for at deltage.<br /><br />\n"
                                   ."Venlig hilsen <br /> {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<strong>Tak<br /><br />"
                       ."Du har nu besvaret alle spørgsmålene i denne undersøgelse.</strong><br /><br />"
                       ."Klik på ["._SUBMIT."] for at afslutte undersøgelsen og indsende dine svar."); //New for 0.98finalRC1

define("_SM_REVIEW", "Hvis du vil tjekke et eller flere af dine svar og evt. rette i dem, kan du gøre det ved at klikke på [<< "._PREV."]-knappen og gennemgå din besvarelse"); //New for 0.98finalRC1

//For the "printable" survey
define("_PS_CHOOSEONE", "Vælg venligst <strong>kun een</strong> af de følgende:"); //New for 0.98finalRC1
define("_PS_WRITE", "Indtast venligst dit svar her:"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Vælg venligst alle de muligheder der passer:"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Vælg venligst alle de muligheder der passer, og tilføj en kommentar:"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Vælg venligst det passende svar til hvert punkt:"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Indtast venligst dine svar her:"); //New for 0.98finalRC1
define("_PS_DATE", "Indtast venligst en dato:"); //New for 0.98finalRC1
define("_PS_COMMENT", "Tilføj en kommentar til dit svar her:"); //New for 0.98finalRC1
define("_PS_RANKING", "Nummerer venligst hver boks i foretrukne rækkefølge fra 1 til"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Indsend dit besvarelse:"); //New for 0.98finalRC1
define("_PS_THANKYOU", "Tak for din besvarelse."); //New for 0.98finalRC1
define("_PS_FAXTO", "Fax venligst den udfyldte besvarelse til:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Svar kun på dette spørgsmål"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "hvis du svarede"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "og"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "til spørgsmål"); //New for 0.98finalRC1
define("_PS_CON_OR", "eller"); //New for 0.98final (translated by machine)

//Save Messages
define("_SAVE_AND_RETURN", "Gem dine foreløbige svar");
define("_SAVEHEADING", "Gem din uafsluttede undersøgelse");
define("_RETURNTOSURVEY", "Tilbage til undersøgelsen");
define("_SAVENAME", "Navn");
define("_SAVEPASSWORD", "Kodeord");
define("_SAVEPASSWORDRPT", "Gentag kodeord");
define("_SAVE_EMAIL", "Din e-mail-adresse");
define("_SAVEEXPLANATION", "Skriv et navn og et kodeord til dine svar og klik på gem herunder.<br />\n"
                  ."Din besvarelse vil blive gemt under dette navn og kodeord, og kan færdiggøres "
                  ."senere ved at logge ind med det samme navn og kodeord.<br /><br />\n"
                  ."Hvis du angiver en e-mail-adresse, vil du få tilsendt detaljerne omkring din besvarelse.");
define("_SAVESUBMIT", "Gem nu");
define("_SAVENONAME", "Du skal give besvarelsen et navn.");
define("_SAVENOPASS", "Du skal skrive et kodeord til besvarelsen.");
define("_SAVENOPASS2", "Du skal bekræfte kodeordet til besvarelsen.");
define("_SAVENOMATCH", "Dine kodeord er ikke ens.");
define("_SAVEDUPLICATE", "Dette navn er allerede i brug til denne undersøgelse. Vælg et andet.");
define("_SAVETRYAGAIN", "Prøv venligst igen.");
define("_SAVE_EMAILSUBJECT", "Detaljerne om din gemte undersøgelse");
define("_SAVE_EMAILTEXT", "Du, eller en der benytter din e-mail-adresse, har gemt "
                         ."en midlertidig besvarelse. De følgende oplysninger kan "
                         ."benyttes til at vende tilbage til undersøgelsen, og "
						 ."genoptage besvarelsen.");
define("_SAVE_EMAILURL", "Genindlæs din besvarelse ved at klikke på følgende henvisning");
define("_SAVE_SUCCEEDED", "Dine svar er blevet gemt.");
define("_SAVE_FAILED", "Der skete en fejl, og dine svar blev ikke gemt.");
define("_SAVE_EMAILSENT", "En e-mail er blevet sendt med detaljerne omkring din gemte undersøgelse.");

//Load Messages
define("_LOAD_SAVED", "Hent en uafsluttet undersøgelse");
define("_LOADHEADING", "Hent en gemt undersøgelse");
define("_LOADEXPLANATION", "Du har her mulighed for at hente en undersøgelse, du har gemt tidligere.<br />\n"
              ."Skriv navnet og kodeordet du brugte til at gemme undersøgelsen.<br /><br />\n");
define("_LOADNAME", "Gemte navn");
define("_LOADPASSWORD", "Kodeord");
define("_LOADSUBMIT", "Hent nu");
define("_LOADNONAME", "Du angav ikke et navn");
define("_LOADNOPASS", "Du angav ikke et kodeord");
define("_LOADNOMATCH", "Kunne ikke finde en undersøgelse med det angivne navn");

define("_ASSESSMENT_HEADING", "Din evaluering");
?>
