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
    # This language file kindly provided by Ulrika Olsson       #
    #                                                           #
    # Updates 2004-2006 by Björn Mildh - bjorn at mildh dot se  #
    #                                                           #
    #############################################################
*/
//SINGLE WORDS
define("_YES", "Ja");
define("_NO", "Nej");
define("_UNCERTAIN", "Vet ej");
define("_ADMIN", "Admin");
define("_TOKENS", "Behörighetskoder");
define("_FEMALE", "Kvinna");
define("_MALE", "Man");
define("_NOANSWER", "Inget svar");
define("_NOTAPPLICABLE", "N/A"); //New for 0.98rc5 (Det finns ingen förkortning av Ej tillämpbar)
define("_OTHER", "Annat");
define("_PLEASECHOOSE", "Välj");
define("_ERROR_PS", "Fel");
define("_COMPLETE", "komplett");
define("_INCREASE", "öka"); //NEW WITH 0.98
define("_SAME", "Samma"); //NEW WITH 0.98
define("_DECREASE", "Minska"); //NEW WITH 0.98
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Bekräftelse");
define("_TOKEN_PS", "Behörighetskod");
define("_CONTINUE_PS", "Fortsätt");

//BUTTONS
define("_ACCEPT", "Acceptera");
define("_PREV", "föreg.");
define("_NEXT", "nästa");
define("_LAST", "sista");
define("_SUBMIT", "skicka");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Välj ett av de följande");
define("_ENTERCOMMENT", "Skriv din kommentar här");
define("_NUMERICAL_PS", "Endast nummer kan skrivas i detta fält");
define("_CLEARALL", "Lämna och rensa enkäten");
define("_MANDATORY", "Denna fråga är obligatorisk");
define("_MANDATORY_PARTS", "Du måste fylla i alla delar");
define("_MANDATORY_CHECK", "Välj minst ett objekt");
define("_MANDATORY_RANK", "Rangordna alla alternativen");
define("_MANDATORY_POPUP", "En eller flera obligatoriska frågor har inte besvarats. Du kan inte fortsätta innan de är besvarade"); //NEW in 0.98rc4
define("_VALIDATION", "Den här frågan måste besvaras korrekt"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "En eller flera frågor har inte besvarats på rätt sätt. Du kan inte fortsätta fürrän dessa svar är korrekta"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Format: åååå-MM-DD");
define("_DATEFORMATEG", "(tex: 2004-12-24 för Julafton)");
define("_REMOVEITEM", "Ta bort detta objekt");
define("_RANK_1", "Klicka på ett objekt i listan till vänster, börja med ditt");
define("_RANK_2", "högst rankade objekt, upprepa tills ditt lägst rankade objekt.");
define("_YOURCHOICES", "Dina val");
define("_YOURRANKING", "Din rangordning");
define("_RANK_3", "Klicka på saxen till höger om objektet");
define("_RANK_4", "för att ta bort det sist elementet i listan.");
//From INDEX.PHP
define("_NOSID", "Du har inte angett ett id-nummer för enkäten");
define("_CONTACT1", "Var god kontakta");
define("_CONTACT2", "för ytterligare assistans");
define("_ANSCLEAR", "Svaren rensade");
define("_RESTART", "Starta om enkäten");
define("_CLOSEWIN_PS", "Stäng fönstret");
define("_CONFIRMCLEAR", "är du säker på att du vill rensa dina svar?");
define("_CONFIRMSAVE", "är du säker på att du vill spara dina svar?");
define("_EXITCLEAR", "Lämna och rensa enkäten");
//From QUESTION.PHP
define("_BADSUBMIT1", "Kan inte skicka resultaten - det finns inga att skicka.");
define("_BADSUBMIT2", "Detta fel kan uppstå om du redan har skickat dina svar och klickat på 'uppdatera' på din webbläsare. I så fall så är dina svar redan sparade.");
define("_NOTACTIVE1", "Dina enkätsvar är inte sparade. Denna enkät är inte aktiviverad ännu.");
define("_CLEARRESP", "Rensa svaren");
define("_THANKS", "Tack");
define("_SURVEYREC", "Dina enkätsvar är sparade.");
define("_SURVEYCPL", "Enkäten klar");
define("_DIDNOTSAVE", "Sparade inte");
define("_DIDNOTSAVE2", "Ett oväntat fel har uppstått och dina svar kan inte sparas.");
define("_DIDNOTSAVE3", "Dina svar har inte försvunnit, utan de har mailats till enkätadministratören och kommer att läggas in i databasen vid ett senare tillfälle.");
define("_DNSAVEEMAIL1", "Ett fel uppstod under försök att spara svaret till enkät-id");
define("_DNSAVEEMAIL2", "Data skall fyllas i");
define("_DNSAVEEMAIL3", "Sql-kod som har misslyckats");
define("_DNSAVEEMAIL4", "Felmeddelande");
define("_DNSAVEEMAIL5", "Fel vid sparandet");
define("_SUBMITAGAIN", "Försök att skicka igen");
define("_SURVEYNOEXIST", "Tyvärr. Det finns ingen matchade enkät.");
define("_NOTOKEN1", "Detta är en kontrollerad enkät. Du behöver en giltlig behörigetskod för att delta");
define("_NOTOKEN2", "Om du har fått en behörighetskod, skriv in den i rutan nedan och fortsätt.");
define("_NOTOKEN3", "Behörighetskoden som du angett är antingen ogiltlig eller redan använd.");
define("_NOQUESTIONS", "Denna enkät har ännu inga frågor och kan inte testas eller färdigställas.");
define("_FURTHERINFO", "För ytterligare information kontakta");
define("_NOTACTIVE", "Denna enkät är inte aktiv för tillfället. Du kan därför inte spara dina svar.");
define("_SURVEYEXPIRED", "Denna enkät är inte längre tillgänglig."); //NEW for 098rc5

define("_SURVEYCOMPLETE", "Du har redan svarat på den här enkäten.");

define("_INSTRUCTION_LIST", "Välj bara en av följande"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Välj vilka som stämmer"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Enkäten skickad"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Ett nytt svar till din enkät har lämnats"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Se det enskilda svaret genom att klicka här:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Se statistik för enkäten genom att klicka här:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE5", "Klicka på följande länk för att ändra det enskilda svaret:"); //NEW for 0.99stable

define("_PRIVACY_MESSAGE", "<strong><i>Hantering av personuppgifter. </i></strong><br />"
                          ."Den här enkäten är anonym.<br />"
                          ."De svar på enkäten som sparas innehåller ingen information som "
                          ."kan identifiera den som svarat utom om denna fråga specifikt ställts "
                          ."i enkäten. även om det krävs ett id-nummer för att kunna besvara "
                          ."enkäten sparas inte denna personliga information tillsammans med "
                          ."enkätsvaret. Id-numret används endast för att avgöra om du har "
                          ."svarat (eller inte svarat) på enkäten och den informationen sparas "
                          ."separat. Det finns inget sätt att avgöra vilket id-nummer som hör "
                          ."ihop med ett visst svar i den här enkäten."); //New for 0.98rc9


define("_THEREAREXQUESTIONS", "Den här undersökningen innehåller {NUMBEROFQUESTIONS} frågor."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Det finns 1 fråga i enkäten."); //New for 0.98rc9 - singular version of above

define ("_RG_REGISTER1", "Du måste vara registrerad för att genomföra den här enkäten"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Du måste registrera dig innan du fyller i den här enkäten.<br />\n"
                        ."Fyll i dina uppgifter nedan och så skickas en länk till "
                        ."enkäten till dig med e-post genast."); //NEW for 0.98rc9
define ("_RG_EMAIL", "E-postadress"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Förnamn"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Efternamn"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "E-postadressen du angav är inte giltig. Var vänlig försök igen.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "Din e-postadress har redan anmälts.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} Bekräftelse på registrering");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Tack för att du registerat dig för att genomföra den här enkäten.<br /><br />\n"
                                   ."Ett e-postmeddelande med dina uppgifter har sänts till den adress du angav."
                                   ."Följ den bifogade länken i e-postmeddelandet för att fortsätta.<br /><br />\n"
                                   ."Enkät-ansvarig {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<strong>Tack!<br /><br />"
    ."Du har besvarat alla frågor i den här enkäten.</strong><br /><br />"
    ."Klicka på ["._SUBMIT."] för att slutföra och spara dina svar."); //New for 0.98finalRC1
define("_SM_REVIEW", "Om du vill kontrollera dina svar och/eller ändra dem, "
    ."kan du göra det genom att klicka på [<< "._PREV."]-knappen och bläddra "
    ."genom dina svar."); //New for 0.98finalRC1

//For the "printable" survey
define("_PS_CHOOSEONE", "Välj <strong>endast en</strong> av följande:"); //New for 0.98finalRC1
define("_PS_WRITE", "Skriv ditt svar här:"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Välj <strong>alla</strong> som stämmer:"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Välj alla som stämmer och skriv en kommentar:"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Välj det korrekta svaret för varje punkt:"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Skriv ditt/dina svar här:"); //New for 0.98finalRC1
define("_PS_DATE", "Fyll i datum:"); //New for 0.98finalRC1
define("_PS_COMMENT", "Kommentera dina val här:"); //New for 0.98finalRC1
define("_PS_RANKING", "Rangordna i varje ruta med ett nummer från 1 till"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Lämna in din enkät."); //New for 0.98finalRC1
define("_PS_THANKYOU", "Tack för att du svarat på denna enkät."); //New for 0.98finalRC1
define("_PS_FAXTO", "Faxa den ifyllda enkäten till:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Svara bara på denna fråga"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "om du svarat"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "och"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "på fråga"); //New for 0.98finalRC1
define("_PS_CON_OR", "eller"); //New for 0.98finalRC2

//Save Messages
define("_SAVE_AND_RETURN", "Spara dina svar så här långt");
define("_SAVEHEADING", "Spara din oavslutade enkät");
define("_RETURNTOSURVEY", "Tillbaka till enkäten");
define("_SAVENAME", "Namn");
define("_SAVEPASSWORD", "Lösenord");
define("_SAVEPASSWORDRPT", "Upprepa lösenord");
define("_SAVE_EMAIL", "Din e-postadress");
define("_SAVEEXPLANATION", "Fyll i namn och lösenord för den här enkäten och klicka nedan.<br />\n"
                  ."Din enkät kommer att sparas med hjälp av det namnet och lösenordet och du kan "
                  ."senare fortsätta fylla i den genom att logga in med samma namn och lösenord.<br /><br />\n"
                  ."Om du anger en e-postadress skickas uppgifterna till dig med e-post "
                  ."på den adressen.");
define("_SAVESUBMIT", "Spara nu");
define("_SAVENONAME", "Du måste ange ett namn för den här omgången svar.");
define("_SAVENOPASS", "Du måste ange ett lösenord för den här omgången svar.");
define("_SAVENOMATCH", "Lösenorden stömmer inte överens.");
define("_SAVEDUPLICATE", "Det här namnet har redan använts för denna enkät. Du måste ange ett unikt namn när du sparar.");
define("_SAVETRYAGAIN", "Var vänlig försök igen.");
define("_SAVE_EMAILSUBJECT", "Sparade enkätsvar");
define("_SAVE_EMAILTEXT", "Du, eller någon annan som angett din e-postadress, har sparat "
                         ."en oavslutad enkät. Följande uppgifter kan användas "
                         ."för att återvända till enkäten och fortsätta där du "
                         ."lämnade den.");
define("_SAVE_EMAILURL", "Uppdatera din enkät genom att klicka på följande länk:");
define("_SAVE_SUCCEEDED", "Dina enkätsvar har sparats");
define("_SAVE_FAILED", "Ett fel uppstod och dina enkätsvar har inte sparats.");
define("_SAVE_EMAILSENT", "Ett e-postmeddelande med detaljer om din sparade enkät har skickats.");

//Load Messages
define("_LOAD_SAVED", "öppna ofullständigt besvarad enkät");
define("_LOADHEADING", "öppnar tidigare sparad enkät");
define("_LOADEXPLANATION", "Du kan öppna en enkät som du tidigare sparat från denna sida.<br />\n"
              ."Fyll i samma 'namn' och 'lösenord' som du använde för att spara enkäten.<br /><br />\n");
define("_LOADNAME", "Sparat namn");
define("_LOADPASSWORD", "Lösenord");
define("_LOADSUBMIT", "öppna nu");
define("_LOADNONAME", "Du angav inget namn");
define("_LOADNOPASS", "Du angav inget lösenord");
define("_LOADNOMATCH", "Det finns ingen enkät som stämmer överens");

define("_ASSESSMENT_HEADING", "Din uppskattning");
?>
