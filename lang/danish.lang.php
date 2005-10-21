<?php
/*
    #############################################################
    # >>> PHPSurveyor                                          #
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
    #     Translation by Mikkel Skovgaard S&oslash;rensen       #
    #                and Rolf Njor Jensen                       #
    #############################################################
*/
//SINGLE WORDS
define("_YES", "Ja");
define("_NO", "Nej");
define("_UNCERTAIN", "Ved ikke");
define("_ADMIN", "Admin");
define("_TOKENS", "N&oslash;gler");
define("_FEMALE", "Kvinde");
define("_MALE", "Mand");
define("_NOANSWER", "Intet svar");
define("_NOTAPPLICABLE", "Ved ikke"); //New for 0.98rc5
define("_OTHER", "Andet");
define("_PLEASECHOOSE", "V&aelig;lg venligst");
define("_ERROR_PS", "Fejl");
define("_COMPLETE", "gennemf&oslash;rt");
define("_INCREASE", "H&aelig;v"); //NEW WITH 0.98
define("_SAME", "Samme"); //NEW WITH 0.98
define("_DECREASE", "S&aelig;nk"); //NEW WITH 0.98
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Bekr&aelig;ftelse");
define("_TOKEN_PS", "N&oslash;gle");
define("_CONTINUE_PS", "Fors&aelig;t");

//BUTTONS
define("_ACCEPT", "Accepter");
define("_PREV", "forrige");
define("_NEXT", "næste");
define("_LAST", "afslut");
define("_SUBMIT", "afsend");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "V&aelig;lg en af f&oslash;lgende");
define("_ENTERCOMMENT", "Skriv dine kommentarer her");
define("_NUMERICAL_PS", "Det felt kan kun indeholde tal/numeriske tegn");
define("_CLEARALL", "Nulstil og forlad unders&oslash;gelsen");
define("_MANDATORY", "Dette sp&oslash;rgsm&aring;l er obligatorisk");
define("_MANDATORY_PARTS", "Udfyld venligst alle dele");
define("_MANDATORY_CHECK", "Afkryds som minimum en mulighed");
define("_MANDATORY_RANK", "Afgiv venligst en score i alle felter");
define("_MANDATORY_POPUP", "En eller flere felter som skal udfyldes er ikke udfyldt - der kan ikke fors&aelig;ttes f&oslash;r disse er udfyldt"); //NEW in 0.98rc4
define("_VALIDATION", "This question must be answered correctly"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Datoformat: &Aring;&Aring;&Aring;&Aring;-MM-DD");
define("_DATEFORMATEG", "(eg: 2003-12-24 hvis der skal angives juledag)");
define("_REMOVEITEM", "Fjern denne mulighed");
define("_RANK_1", "Klik p&aring; et emne i listen til venstre, startende med det du");
define("_RANK_2", "vurdere h&oslash;jst, og klik derefter nedefter til det lavest vurderede emne.");
define("_YOURCHOICES", "Dine valg");
define("_YOURRANKING", "Din vurdering");
define("_RANK_3", "Klik p&aring; saks ikonet til h&oslash;jre for");
define("_RANK_4", "at fjerne det nederst emne p&aring; din vurderingsliste");
//From INDEX.PHP
define("_NOSID", "Der mangler at blive angivet en unders&oslash;gelses n&oslash;gle/id");
define("_CONTACT1", "Kontakt venligst");
define("_CONTACT2", "for videre assistance");
define("_ANSCLEAR", "Svar gennemf&oslash;rt");
define("_RESTART", "Nulstil og start forfra");
define("_CLOSEWIN_PS", "Luk dette vindue");
define("_CONFIRMCLEAR", "Er du sikker p&aring; at du vil nulstille alle dine sp&oslash;rgsm&aring;l?");
define("_CONFIRMSAVE", "Are you sure you want to save your responses?");
define("_EXITCLEAR", "Nulstil og forlad unders&oslash;gelsen.");
//From QUESTION.PHP
define("_BADSUBMIT1", "Kan ikke gemme besvarelsen - der er ikke noget at gemme.");
define("_BADSUBMIT2", "Denne fejl er opst&aring;et fordi du allerede har gemt dine svar og har trykket p&aring; 'Opdater' i din browser. Dine besvarelser er allerede gemt.<br /><br />Hvis du har f&aring;et denne fejlmeddelse midt i en sp&oslash;rgeskema unders&oslash;gelse b&oslash;r du trykke p&aring; '<- Tilbage' knappen i din browser og tryk p&aring; 'Opdater'. Dermed vil dit forrige svar g&aring; tabt men alle andre tidligere svar er gemt, vi beklager de gener dette m&aring;tte medf&oslash;re.");
define("_NOTACTIVE1", "Dine besvarelser er ikke gemt - unders&oslash;gelsen er endnu ikke sat igang.");
define("_CLEARRESP", "Nulstil svar");
define("_THANKS", "Tak");
define("_SURVEYREC", "Dine besvarelser er blevet gemt.");
define("_SURVEYCPL", "Unders&oslash;gelsen er gennemf&oslash;rt");
define("_DIDNOTSAVE", "Kunne ikke gemme");
define("_DIDNOTSAVE2", "Der skete en uventet fejl og dine besvarelser kunne ikke gemmes.");
define("_DIDNOTSAVE3", "Dine besvarelser er ikke g&aring;et tabt - men er sendt til administratoren af unders&oslash;gelsen som s&aring; senere tilf&oslash;jer disse.");
define("_DNSAVEEMAIL1", "An error occurred saving a response to survey id");
define("_DNSAVEEMAIL2", "DATA TO BE ENTERED");
define("_DNSAVEEMAIL3", "SQL CODE THAT FAILED");
define("_DNSAVEEMAIL4", "ERROR MESSAGE");
define("_DNSAVEEMAIL5", "ERROR SAVING");
define("_SUBMITAGAIN", "Pr&oslash;v igen");
define("_SURVEYNOEXIST", "Desv&aelig;rre, kunne ikke finde unders&oslash;gelses n&oslash;gle/id der matcher det valgte.");
define("_NOTOKEN1", "Dette er en lukket unders&oslash;gelse og kr&aelig;ver at du har en unders&oslash;gelses n&oslash;gle/id for at deltage.");
define("_NOTOKEN2", "Hvis du har en unders&oslash;gelses n&oslash;gle/id s&aring; indtast den herunder.");
define("_NOTOKEN3", "Den unders&oslash;gelses n&oslash;gle/id du har angivet er ugyldig eller er allerede brugt.");
define("_NOQUESTIONS", "Denne unders&oslash;gelse har endnu ingen sp&oslash;rgsm&aring;l og kan derfor ikke benyttes.");
define("_FURTHERINFO", "For yderligere information kontakt");
define("_NOTACTIVE", "Denne unders&oslash;gelse er ikke aktiv og du kan derfor ikke deltage.");
define("_SURVEYEXPIRED", "Denne unders&oslash;gelse er ikke l&aelig;ngere aktiv og du kan derfor ikke deltage.");

define("_SURVEYCOMPLETE", "Du har allerede gennemf&oslash;rt denne unders&oslash;gelse."); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "V&aelig;lg kun en af nedenst&aring;ende"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "V&aelig;lg alle du er enig i"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Unders&oslash;gelsen er gemt"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Et nyt svar er gemt i unders&oslash;gelsen"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Klik p&aring; nedenst&aring;ende link for at se de individuelle svar:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Vis statistikken her:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE5", "Click the following link to edit the individual response:"); //NEW for 0.99stable

define("_PRIVACY_MESSAGE", "<strong><i>A Note On Privacy</i></strong><br />"
                          ."This survey is anonymous.<br />"
                          ."The record kept of your survey responses does not contain any "
                          ."identifying information about you unless a specific question "
                          ."in the survey has asked for this. If you have responded to a "
                          ."survey that used an identifying token to allow you to access "
                          ."the survey, you can rest assured that the identifying token "
                          ."is not kept with your responses. It is managed in a separate "
                          ."database, and will only be updated to indicate that you have "
                          ."(or haven't) completed this survey. There is no way of matching "
                          ."identification tokens with survey responses in this survey."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "Der er {NUMBEROFQUESTIONS} sp&oslash;rgsm&aring;l i denne unders&oslash;gelse."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Der er et sp&oslash;rgsm&aring;l i denne unders&oslash;gelse."); //New for 0.98rc9 - singular version of above

define ("_RG_REGISTER1", "Du skal v&aelig;re registeret for at udfylde denne unders&oslash;gelse"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Du kan deltage i denne unders&oslash;gelse ved at udfylde skemaet herunder.<br />\n"
                        ."Udfyld nedenst&aring;ende formular og et link/url s&aring;"
                        ."du kan deltage i unders&oslash;gelsen vil blive tilsendt p&aring; e-mail."); //NEW for 0.98rc9
define ("_RG_EMAIL", "E-mail adrese"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Fornavn"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Efternavn"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "Den angivne e-mail adresse er ugyldig.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "Den angivne e-mail adresse er allerede registeret.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} Registering er gennemf&oslash;rt");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Tak fordi du v&aelig;lger at deltage i unders&oslash;gelsen.<br /><br />\n"
                                   ."En e-mail er sendt til den angivne e-mail adresse, hvor i der findes"
                                   ."informationer om hvordan du deltager i unders&oslash;gelsen. Benyt linket i e-mailen for at deltage.<br /><br />\n"
                                   ."Venlig hilsen <br /> {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<strong>Tak<br /><br />"
                       ."Du har nu besvaret alle sp&oslash;rgsm&aring;lene i denne unders&oslash;gelse.</strong><br /><br />"
                       ."Klik p&aring; ["._SUBMIT."] for at afslutte unders&oslash;gelsen og indsende dine svar."); //New for 0.98finalRC1

define("_SM_REVIEW", "Hvis du vil tjekke et eller flere af dine svar og evt. rette i dem, kan du g&oslash;re det ved at klikke p&aring; [<< "._PREV."] knappen og gennemg&aring; dine besvarelse."); //New for 0.98finalRC1

//For the "printable" survey
define("_PS_CHOOSEONE", "V&aelig;lg venligst <strong>kun een</strong> af de f&oslash;lgende"); //New for 0.98finalRC1
define("_PS_WRITE", "Indtast venligst dit svar her"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "V&aelig;lg venligst alle de muligheder der passer"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "V&aelig;lg venligst alle de muligheder der passer, og tilf&oslash;j en kommentar"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Please choose the appropriate response for each item"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Indtast venligst dine svar her"); //New for 0.98finalRC1
define("_PS_DATE", "Indtast venligst en dato"); //New for 0.98finalRC1
define("_PS_COMMENT", "Tilf&oslash;j en kommentar til dit svar her"); //New for 0.98finalRC1
define("_PS_RANKING", "Angiv et tal for hver boks i pr&aelig;ference orden fra 1 til"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Indsend dit besvarelse"); //New for 0.98finalRC1
define("_PS_THANKYOU", "Tak for din besvarelse."); //New for 0.98finalRC1
define("_PS_FAXTO", "Fax venligst den udfyldte besvarelse til:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Svar kun p&aring; dette sp&oslash;rgsm&aring;l"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "hvis du svarede"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "og"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "til sp&oslash;rgsm&aring;l"); //New for 0.98finalRC1
define("_PS_CON_OR", "eller"); //New for 0.98final (translated by machine)

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
