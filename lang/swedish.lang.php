<?php
/*
    #############################################################
    # >>> PHP Surveyor                                          #
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
    # Updated for 0.98rc9 and higher by                         #
    # Bj&ouml;rn Mildh - bjorn at mildh dot se - 2005-03-05          #
    #                                                           #
    #############################################################
*/
//SINGLE WORDS
define("_YES", "Ja");
define("_NO", "Nej");
define("_UNCERTAIN", "Vet ej");
define("_ADMIN", "Admin");
define("_TOKENS", "Beh&ouml;righetskoder");
define("_FEMALE", "Kvinna");
define("_MALE", "Man");
define("_NOANSWER", "Inget svar");
define("_NOTAPPLICABLE", "N/A"); //New for 0.98rc5 (Det finns ingen f&ouml;rkortning av Ej till&auml;mpbar)
define("_OTHER", "Annat");
define("_PLEASECHOOSE", "V&auml;lj");
define("_ERROR_PS", "Fel");
define("_COMPLETE", "komplett");
define("_INCREASE", "&Ouml;ka"); //NEW WITH 0.98
define("_SAME", "Samma"); //NEW WITH 0.98
define("_DECREASE", "Minska"); //NEW WITH 0.98
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Bekr&auml;ftelse");
define("_TOKEN_PS", "Beh&ouml;righetskod");
define("_CONTINUE_PS", "Forts&auml;tt");

//BUTTONS
define("_ACCEPT", "Acceptera");
define("_PREV", "föreg.");
define("_NEXT", "nästa");
define("_LAST", "sista");
define("_SUBMIT", "skicka");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "V&auml;lj ett av de f&ouml;ljande");
define("_ENTERCOMMENT", "Skriv din kommentar h&auml;r");
define("_NUMERICAL_PS", "Endast nummer kan skrivas i detta f&auml;lt");
define("_CLEARALL", "L&auml;mna och rensa enk&auml;ten");
define("_MANDATORY", "Denna fr&aring;ga &auml;r obligatorisk");
define("_MANDATORY_PARTS", "Du m&aring;ste fylla i alla delar");
define("_MANDATORY_CHECK", "V&auml;lj minst ett objekt");
define("_MANDATORY_RANK", "Rangordna alla alternativen");
define("_MANDATORY_POPUP", "En eller flera obligatoriska fr&aring;gor har inte besvarats. Du kan inte forts&auml;tta innan de &auml;r besvarade"); //NEW in 0.98rc4
define("_VALIDATION", "Den h&auml;r fr&aring;gan m&aring;ste besvaras korrekt"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "En eller flera fr&aring;gor har inte besvarats p&aring; r&auml;tt s&auml;tt. Du kan inte forts&auml;tta f&ouml;rr&auml;n dessa svar &auml;r korrekta"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Format: &Aring;&Aring;&Aring;&Aring;-MM-DD");
define("_DATEFORMATEG", "(tex: 2004-12-24 f&ouml;r Julafton)");
define("_REMOVEITEM", "Ta bort detta objekt");
define("_RANK_1", "Klicka p&aring; ett objekt i listan till v&auml;nster, b&ouml;rja med ditt");
define("_RANK_2", "h&ouml;gst rankade objekt, upprepa tills ditt l&auml;gst rankade objekt.");
define("_YOURCHOICES", "Dina val");
define("_YOURRANKING", "Din rangordning");
define("_RANK_3", "Klicka p&aring; saxen till h&ouml;ger om objektet");
define("_RANK_4", "f&ouml;r att ta bort det sist elementet i listan.");
//From INDEX.PHP
define("_NOSID", "Du har inte angett ett id-nummer f&ouml;r enk&auml;ten");
define("_CONTACT1", "Var god kontakta");
define("_CONTACT2", "f&ouml;r ytterligare assistans");
define("_ANSCLEAR", "Svaren rensade");
define("_RESTART", "Starta om enk&auml;ten");
define("_CLOSEWIN_PS", "St&auml;ng f&ouml;nstret");
define("_CONFIRMCLEAR", "&Auml;r du s&auml;ker p&aring; att du vill rensa dina svar?");
define("_CONFIRMSAVE", "&Auml;r du s&auml;ker p&aring; att du vill spara dina svar?");
define("_EXITCLEAR", "L&auml;mna och rensa enk&auml;ten");
//From QUESTION.PHP
define("_BADSUBMIT1", "Kan inte skicka resultaten - det finns inga att skicka.");
define("_BADSUBMIT2", "Detta fel kan uppst&aring; om du redan har skickat dina svar och klickat p&aring; 'uppdatera' p&aring; din webbl&auml;sare. I s&aring; fall s&aring; &auml;r dina svar redan sparade.");
define("_NOTACTIVE1", "Dina enk&auml;tsvar &auml;r inte sparade. Denna enk&auml;t &auml;r inte aktiviverad &auml;nnu.");
define("_CLEARRESP", "Rensa svaren");
define("_THANKS", "Tack");
define("_SURVEYREC", "Dina enk&auml;tsvar &auml;r sparade.");
define("_SURVEYCPL", "Enk&auml;ten klar");
define("_DIDNOTSAVE", "Sparade inte");
define("_DIDNOTSAVE2", "Ett ov&auml;ntat fel har uppst&aring;tt och dina svar kan inte sparas.");
define("_DIDNOTSAVE3", "Dina svar har inte f&ouml;rsvunnit, utan de har mailats till enk&auml;tadministrat&ouml;ren och kommer att l&auml;ggas in i databasen vid ett senare tillf&auml;lle.");
define("_DNSAVEEMAIL1", "Ett fel uppstod under f&ouml;rs&ouml;k att spara svaret till enk&auml;t-id");
define("_DNSAVEEMAIL2", "Data skall fyllas i");
define("_DNSAVEEMAIL3", "Sql-kod som har misslyckats");
define("_DNSAVEEMAIL4", "Felmeddelande");
define("_DNSAVEEMAIL5", "Fel vid sparandet");
define("_SUBMITAGAIN", "F&ouml;rs&ouml;k att skicka igen");
define("_SURVEYNOEXIST", "Tyv&auml;rr. Det finns ingen matchade enk&auml;t.");
define("_NOTOKEN1", "Detta &auml;r en kontrollerad enk&auml;t. Du beh&ouml;ver en giltlig beh&ouml;rigetskod f&ouml;r att delta");
define("_NOTOKEN2", "Om du har f&aring;tt en beh&ouml;righetskod, skriv in den i rutan nedan och forts&auml;tt.");
define("_NOTOKEN3", "Beh&ouml;righetskoden som du angett &auml;r antingen ogiltlig eller redan anv&auml;nd.");
define("_NOQUESTIONS", "Denna enk&auml;t har &auml;nnu inga fr&aring;gor och kan inte testas eller f&auml;rdigst&auml;llas.");
define("_FURTHERINFO", "F&ouml;r ytterligare information kontakta");
define("_NOTACTIVE", "Denna enk&auml;t &auml;r inte aktiv f&ouml;r tillf&auml;llet. Du kan d&auml;rf&ouml;r inte spara dina svar.");
define("_SURVEYEXPIRED", "Denna enk&auml;t &auml;r inte l&auml;ngre tillg&auml;nglig."); //NEW for 098rc5

define("_SURVEYCOMPLETE", "Du har redan svarat p&aring; den h&auml;r enk&auml;ten.");

define("_INSTRUCTION_LIST", "V&auml;lj bara en av f&ouml;ljande"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "V&auml;lj vilka som st&auml;mmer"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Enk&auml;ten skickad"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Ett nytt svar till din enk&auml;t har l&auml;mnats"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Se det enskilda svaret genom att klicka h&auml;r:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Se statistik f&ouml;r enk&auml;ten genom att klicka h&auml;r:"); //NEW for 098rc5

define("_PRIVACY_MESSAGE", "<b><i>Hantering av personuppgifter. </i></b><br />"
                          ."Den h&auml;r enk&auml;ten &auml;r anonym.<br />"
                          ."De svar p&aring; enk&auml;ten som sparas inneh&aring;ller ingen information som "
                          ."kan identifiera den som svarat utom om denna fr&aring;ga specifikt st&auml;llts "
                          ."i enk&auml;ten. &Auml;ven om det kr&auml;vs ett id-nummer f&ouml;r att kunna besvara "
                          ."enk&auml;ten sparas inte denna personliga information tillsammans med "
                          ."enk&auml;tsvaret. Id-numret anv&auml;nds endast f&ouml;r att avg&ouml;ra om du har "
                          ."svarat (eller inte svarat) p&aring; enk&auml;ten och den informationen sparas "
                          ."separat. Det finns inget s&auml;tt att avg&ouml;ra vilket id-nummer som h&ouml;r "
                          ."ihop med ett visst svar i den h&auml;r enk&auml;ten."); //New for 0.98rc9


define("_THEREAREXQUESTIONS", "Den h&auml;r unders&ouml;kningen inneh&aring;ller {NUMBEROFQUESTIONS} fr&aring;gor."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Det finns 1 fr&aring;ga i enk&auml;ten."); //New for 0.98rc9 - singular version of above

define ("_RG_REGISTER1", "Du m&aring;ste vara registrerad f&ouml;r att genomf&ouml;ra den h&auml;r enk&auml;ten"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Du m&aring;ste registrera dig innan du fyller i den h&auml;r enk&auml;ten.<br />\n"
                        ."Fyll i dina uppgifter nedan och s&aring; skickas en l&auml;nk till "
                        ."enk&auml;ten till dig med e-post genast."); //NEW for 0.98rc9
define ("_RG_EMAIL", "E-postadress"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "F&ouml;rnamn"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Efternamn"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "E-postadressen du angav &auml;r inte giltig. Var v&auml;nlig f&ouml;rs&ouml;k igen.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "Din e-postadress har redan anm&auml;lts.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} Bekr&auml;ftelse p&aring; registrering");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Tack f&ouml;r att du registerat dig f&ouml;r att genomf&ouml;ra den h&auml;r enk&auml;ten.<br /><br />\n"
                                   ."Ett e-postmeddelande med dina uppgifter har s&auml;nts till den adress du angav."
                                   ."F&ouml;lj den bifogade l&auml;nken i e-postmeddelandet f&ouml;r att forts&auml;tta.<br /><br />\n"
                                   ."Enk&auml;t-ansvarig {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<b>Tack!<br /><br />"
    ."Du har besvarat alla fr&aring;gor i den h&auml;r enk&auml;ten.</b><br /><br />"
    ."Klicka p&aring; ["._SUBMIT."] f&ouml;r att slutf&ouml;ra och spara dina svar."); //New for 0.98finalRC1 - by Bjorn Mildh
define("_SM_REVIEW", "Om du vill kontrollera dina svar och/eller &auml;ndra dem, "
    ."kan du g&ouml;ra det genom att klicka p&aring; [<< "._PREV."]-knappen och bl&auml;ddra "
    ."genom dina svar."); //New for 0.98finalRC1 - by Bjorn Mildh

//For the "printable" survey
define("_PS_CHOOSEONE", "V&auml;lj <b>endast en</b> av f&ouml;ljande:"); //New for 0.98finalRC1
define("_PS_WRITE", "Skriv ditt svar h&auml;r:"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "V&auml;lj <b>alla</b> som st&auml;mmer:"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "V&auml;lj alla som st&auml;mmer och skriv en kommentar:"); //New for 0.98finalRC1
define("_PS_EACHITEM", "V&auml;lj det korrekta svaret f&ouml;r varje punkt:"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Skriv ditt/dina svar h&auml;r:"); //New for 0.98finalRC1
define("_PS_DATE", "Fyll i datum:"); //New for 0.98finalRC1
define("_PS_COMMENT", "Kommentera dina val h&auml;r:"); //New for 0.98finalRC1
define("_PS_RANKING", "Rangordna i varje ruta med ett nummer fr&aring;n 1 till"); //New for 0.98finalRC1
define("_PS_SUBMIT", "L&auml;mna in din enk&auml;t."); //New for 0.98finalRC1
define("_PS_THANKYOU", "Tack f&ouml;r att du svarat p&aring; denna enk&auml;t."); //New for 0.98finalRC1
define("_PS_FAXTO", "Faxa den ifyllda enk&auml;ten till:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Svara bara p&aring; denna fr&aring;ga"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "om du svarat"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "och"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "p&aring; fr&aring;ga"); //New for 0.98finalRC1
define("_PS_CON_OR", "eller"); //New for 0.98finalRC2

//Save Messages
define("_SAVE_AND_RETURN", "Spara dina svar s&aring; h&auml;r l&aring;ngt");
define("_SAVEHEADING", "Spara din oavslutade enk&auml;t");
define("_RETURNTOSURVEY", "Tillbaka till enk&auml;ten");
define("_SAVENAME", "Namn");
define("_SAVEPASSWORD", "L&ouml;senord");
define("_SAVEPASSWORDRPT", "Upprepa l&ouml;senord");
define("_SAVE_EMAIL", "Din e-postadress");
define("_SAVEEXPLANATION", "Fyll i namn och l&ouml;senord f&ouml;r den h&auml;r enk&auml;ten och klicka nedan.<br />\n"
                  ."Din enk&auml;t kommer att sparas med hj&auml;lp av det namnet och l&ouml;senordet och du kan "
                  ."senare forts&auml;tta fylla i den genom att logga in med samma namn och l&ouml;senord.<br /><br />\n"
                  ."Om du anger en e-postadress skickas uppgifterna till dig med e-post "
                  ."p&aring; den adressen.");
define("_SAVESUBMIT", "Spara nu");
define("_SAVENONAME", "Du m&aring;ste ange ett namn f&ouml;r den h&auml;r omg&aring;ngen svar.");
define("_SAVENOPASS", "Du m&aring;ste ange ett l&ouml;senord f&ouml;r den h&auml;r omg&aring;ngen svar.");
define("_SAVENOMATCH", "L&ouml;senorden st&ouml;mmer inte &ouml;verens.");
define("_SAVEDUPLICATE", "Det h&auml;r namnet har redan anv&auml;nts f&ouml;r denna enk&auml;t. Du m&aring;ste ange ett unikt namn n&auml;r du sparar.");
define("_SAVETRYAGAIN", "Var v&auml;nlig f&ouml;rs&ouml;k igen.");
define("_SAVE_EMAILSUBJECT", "Sparade enk&auml;tsvar");
define("_SAVE_EMAILTEXT", "Du, eller n&aring;gon annan som angett din e-postadress, har sparat "
                         ."en oavslutad enk&auml;t. F&ouml;ljande uppgifter kan anv&auml;ndas "
                         ."f&ouml;r att &aring;terv&auml;nda till enk&auml;ten och forts&auml;tta d&auml;r du "
                         ."l&auml;mnade den.");
define("_SAVE_EMAILURL", "Uppdatera din enk&auml;t genom att klicka p&aring; f&ouml;ljande l&auml;nk:");
define("_SAVE_SUCCEEDED", "Dina enk&auml;tsvar har sparats");
define("_SAVE_FAILED", "Ett fel uppstod och dina enk&auml;tsvar har inte sparats.");
define("_SAVE_EMAILSENT", "Ett e-postmeddelande med detaljer om din sparade enk&auml;t har skickats.");

//Load Messages
define("_LOAD_SAVED", "&Ouml;ppna ofullst&auml;ndigt besvarad enk&auml;t");
define("_LOADHEADING", "&Ouml;ppnar tidigare sparad enk&auml;t");
define("_LOADEXPLANATION", "Du kan &ouml;ppna en enk&auml;t som du tidigare sparat fr&aring;n denna sida.<br />\n"
              ."Fyll i samma 'namn' och 'l&ouml;senord' som du anv&auml;nde f&ouml;r att spara enk&auml;ten.<br /><br />\n");
define("_LOADNAME", "Sparat namn");
define("_LOADPASSWORD", "L&ouml;senord");
define("_LOADSUBMIT", "&Ouml;ppna nu");
define("_LOADNONAME", "Du angav inget namn");
define("_LOADNOPASS", "Du angav inget l&ouml;senord");
define("_LOADNOMATCH", "Det finns ingen enk&auml;t som st&auml;mmer &ouml;verens");

define("_ASSESSMENT_HEADING", "Din uppskattning");
?>
