<?php
/*
    #################################################################
    # >>> PHPSurveyor                                               #
    #################################################################
    # > Author:  Jason Cleeland                                     #
    # > E-mail:  jason@cleeland.org                                 #
    # > Mail:    Box 99, Trades Hall, 54 Victoria St,               #
    # >          CARLTON SOUTH 3053, AUSTRALIA                      #
    # > Date:    20 February 2003                                   #
    #                                                               #
    # This set of scripts allows you to develop, publish and        #
    # perform data-entry on surveys.                                #
    #################################################################
    #   Copyright (C) 2003  Jason Cleeland                          #
    #                                                               #
    # This program is free software; you can redistribute           #
    # it and/or modify it under the terms of the GNU General        #
    # Public License as published by the Free Software              #
    # Foundation; either version 2 of the License, or (at your      #
    # option) any later version.                                    #
    #                                                               #
    # This program is distributed in the hope that it will be       #
    # useful, but WITHOUT ANY WARRANTY; without even the            #
    # implied warranty of MERCHANTABILITY or FITNESS FOR A          #
    # PARTICULAR PURPOSE.  See the GNU General Public License       #
    # for more details.                                             #
    #                                                               #
    # You should have received a copy of the GNU General            #
    # Public License along with this program; if not, write to      #
    # the Free Software Foundation, Inc., 59 Temple Place -         #
    # Suite 330, Boston, MA  02111-1307, USA.                       #
    #################################################################
    #                                                               #
    #  Translation kindly provided by Peter De Berdt				#
    #  																#
    #                                                               #
    #  Edit this file with an UTF-8 capable editor only!            #
    #                                                               #
    #################################################################
*/


//BUTTON BAR TITLES
define("_ADMINISTRATION", "Administratie");
define("_SURVEY", "Vragenlijst");
define("_GROUP", "Groep");
define("_QUESTION", "Vraag");
define("_ANSWERS", "Antwoorden");
define("_CONDITIONS", "Condities");
define("_HELP", "Help");
define("_USERCONTROL", "Gebruikersbeheer");
define("_ACTIVATE", "Activateren vragenlijst");
define("_DEACTIVATE", "Deactiveren vragenlijst");
define("_CHECKFIELDS", "Controleren database velden");
define("_CREATEDB", "Toevoegen database");
define("_CREATESURVEY", "Toevoegen vragenlijst"); //New for 0.98rc4
define("_SETUP", "PHPSurveyor Configuratie");
define("_DELETESURVEY", "Wissen vragenlijst");
define("_EXPORTQUESTION", "Exporteren vraag");
define("_EXPORTSURVEY", "Exporteren vragenlijst");
define("_EXPORTLABEL", "Exporteren label set");
define("_IMPORTQUESTION", "Importeren vraag");
define("_IMPORTGROUP", "Importeren groep"); //New for 0.98rc5
define("_IMPORTSURVEY", "Importeren vragenlijst");
define("_IMPORTLABEL", "Importeren label set");
define("_EXPORTRESULTS", "Exporteren antwoorden");
define("_BROWSERESPONSES", "Bekijken deelnames");
define("_BROWSESAVED", "Browse Saved Responses");
define("_STATISTICS", "Snelle statistieken");
define("_VIEWRESPONSE", "Bekijken deelname");
define("_VIEWCONTROL", "Controle dataweergave");
define("_DATAENTRY", "Ingave data");
define("_TOKENCONTROL", "Controle toegangscodes");
define("_TOKENDBADMIN", "Toegangscode Administratie Opties");
define("_DROPTOKENS", "Wissen toegangscodetabel");
define("_EMAILINVITE", "E-mail uitnodiging");
define("_EMAILREMIND", "E-mail herinnering");
define("_TOKENIFY", "Maak toegangscodes");
define("_UPLOADCSV", "Upload CSV bestand");
define("_LABELCONTROL", "Label sets administratie"); //NEW with 0.98rc3
define("_LABELSET", "Label set"); //NEW with 0.98rc3
define("_LABELANS", "Labels"); //NEW with 0.98rc3
define("_OPTIONAL", "Optional"); //NEW with 0.98finalRC1

//DROPDOWN HEADINGS
define("_SURVEYS", "Vragenlijsten");
define("_GROUPS", "Groepen");
define("_QUESTIONS", "Vragen");
define("_QBYQ", "Vraag per vraag");
define("_GBYG", "Groep per groep");
define("_SBYS", "Alles ineens");
define("_LABELSETS", "Sets"); //New with 0.98rc3

//BUTTON MOUSEOVERS
//administration bar
define("_A_HOME_BT", "Standaard administratie pagina");
define("_A_SECURITY_BT", "Wijzigen beveiligingsinstellingen");
define("_A_BADSECURITY_BT", "Activeer beveiliging");
define("_A_CHECKDB_BT", "Controleren database");
define("_A_DELETE_BT", "Wissen volledige vragenlijst");
define("_A_ADDSURVEY_BT", "Toevoegen of importeren nieuwe vragenlijst");
define("_A_HELP_BT", "Toon help");
define("_A_CHECKSETTINGS", "Controleren instellingen");
define("_A_BACKUPDB_BT", "Backup Entire Database"); //New for 0.98rc10
define("_A_TEMPLATES_BT", "Template Editor"); //New for 0.98rc9
//Survey bar
define("_S_ACTIVE_BT", "Deze vragenlijst is momenteel actief");
define("_S_INACTIVE_BT", "Deze vragenlijst is momenteel niet actief");
define("_S_ACTIVATE_BT", "Activeer deze vragenlijst");
define("_S_DEACTIVATE_BT", "Deactiveer deze vragenlijst");
define("_S_CANNOTACTIVATE_BT", "Kan deze vragenlijst niet activeren");
define("_S_DOSURVEY_BT", "Test vragenlijst");
define("_S_DATAENTRY_BT", "Ingavescherm van de vragenlijst");
define("_S_PRINTABLE_BT", "Printvriendelijke versie van de vragenlijst");
define("_S_EDIT_BT", "Bewerk huidige vragenlijst");
define("_S_DELETE_BT", "Wis huidige vragenlijst");
define("_S_EXPORT_BT", "Exporteer huidige vragenlijst");
define("_S_BROWSE_BT", "Bekijk deelnames van deze vragenlijst");
define("_S_TOKENS_BT", "Activeer/Bewerk toegangscodes voor deze vragenlijst");
define("_S_ADDGROUP_BT", "Nieuwe groep aan deze vragenlijst toevoegen");
define("_S_MINIMISE_BT", "Verberg details van deze vragenlijst");
define("_S_MAXIMISE_BT", "Toon details van deze vragenlijst");
define("_S_CLOSE_BT", "Sluit deze vragenlijst");
define("_S_SAVED_BT", "View Saved but not submitted Responses"); //New in 0.99dev01
define("_S_ASSESSMENT_BT", "Set assessment rules"); //New in  0.99dev01
//Group bar
define("_G_EDIT_BT", "Bewerk huidige groep");
define("_G_EXPORT_BT", "Exporteer huidige groep"); //New in 0.98rc5
define("_G_DELETE_BT", "Wis huidige groep");
define("_G_ADDQUESTION_BT", "Toevoegen nieuwe vraag aan deze groep");
define("_G_MINIMISE_BT", "Verberg details van deze groep");
define("_G_MAXIMISE_BT", "Toon details van deze groep");
define("_G_CLOSE_BT", "Sluit deze groep");
//Question bar
define("_Q_EDIT_BT", "Bewerk huidige vraag");
define("_Q_COPY_BT", "Kopieer huidige vraag"); //New in 0.98rc4
define("_Q_DELETE_BT", "Wis huidige vraag");
define("_Q_EXPORT_BT", "Exporteer huidige vraag");
define("_Q_CONDITIONS_BT", "Stel condities voor deze vraag in");
define("_Q_ANSWERS_BT", "Bewerken/Toevoegen van antwoorden bij deze vraag");
define("_Q_LABELS_BT", "Bewerken/Toevoegen van label sets");
define("_Q_MINIMISE_BT", "Verberg details van deze vraag");
define("_Q_MAXIMISE_BT", "Toon details van deze vraag");
define("_Q_CLOSE_BT", "Sluit deze vraag");
//Browse Button Bar
define("_B_ADMIN_BT", "Keer terug naar Vragenlijst Administratie");
define("_B_SUMMARY_BT", "Toon samenvatting");
define("_B_ALL_BT", "Toon deelnames");
define("_B_LAST_BT", "Toon laatste 50 deelnames");
define("_B_STATISTICS_BT", "Toon statistieken van deze deelnames");
define("_B_EXPORT_BT", "Exporteer deelnames naar programma");
define("_B_BACKUP_BT", "Backup deelnametabel als SQL bestand");
//Tokens Button Bar
define("_T_ALL_BT", "Toon toegangscodes");
define("_T_ADD_BT", "Toevoegen nieuwe toegangscode");
define("_T_IMPORT_BT", "Importeer toegangscodes uit CSV bestand");
define("_T_EXPORT_BT", "Exporteer toegangscodes uit CSV bestand"); //New for 0.98rc7
define("_T_INVITE_BT", "Verstuur e-mail uitnodiging");
define("_T_REMIND_BT", "Verstuur e-mail herinnering");
define("_T_TOKENIFY_BT", "Maak toegangscodes aan");
define("_T_KILL_BT", "Wis toegangscodetabel");
//Labels Button Bar
define("_L_ADDSET_BT", "Toevoegen nieuwe label set");
define("_L_EDIT_BT", "Bewerk label set");
define("_L_DEL_BT", "Wis label set");
//Datacontrols
define("_D_BEGIN", "Toon begin..");
define("_D_BACK", "Toon einde..");
define("_D_FORWARD", "Toon volgende..");
define("_D_END", "Toon laatste..");

//DATA LABELS
//surveys
define("_SL_TITLE", "Titel:");
define("_SL_SURVEYURL", "Vragenlijst URL:"); //new in 0.98rc5
define("_SL_DESCRIPTION", "Omschrijving:");
define("_SL_WELCOME", "Welkom:");
define("_SL_ADMIN", "Verantwoordelijke:");
define("_SL_EMAIL", "E-mail verantwoordelijke:");
define("_SL_FAXTO", "Fax naar:");
define("_SL_ANONYMOUS", "Anoniem?");
define("_SL_EXPIRYDATE", "Vervalt:");
define("_SL_FORMAT", "Formaat:");
define("_SL_DATESTAMP", "Datum?");
define("_SL_IPADDRESS", "IP Address"); //New with 0.991
define("_SL_TEMPLATE", "Sjabloon:");
define("_SL_LANGUAGE", "Taal:");
define("_SL_LINK", "Link:");
define("_SL_URL", "URL op einde:");
define("_SL_URLDESCRIP", "URL beschrijving:");
define("_SL_STATUS", "Status:");
define("_SL_SELSQL", "Selecteer SQL bestand:");
define("_SL_USECOOKIES", "Gebruiken Cookies?"); //NEW with 098rc3
define("_SL_NOTIFICATION", "Verwittiging:"); //New with 098rc5
define("_SL_ALLOWREGISTER", "Allow public registration?"); //New with 0.98rc9
define("_SL_ATTRIBUTENAMES", "Token Attribute Names:"); //New with 0.98rc9
define("_SL_EMAILINVITE_SUBJ", "Invitation Email Subject:"); //New with 0.99dev01
define("_SL_EMAILINVITE", "Invitation Email:"); //New with 0.98rc9
define("_SL_EMAILREMIND_SUBJ", "Email Reminder Subject:"); //New with 0.99dev01
define("_SL_EMAILREMIND", "Email Reminder:"); //New with 0.98rc9
define("_SL_EMAILREGISTER_SUBJ", "Public registration Email Subject:"); //New with 0.99dev01
define("_SL_EMAILREGISTER", "Public registration Email:"); //New with 0.98rc9
define("_SL_EMAILCONFIRM_SUBJ", "Confirmation Email Subject"); //New with 0.99dev01
define("_SL_EMAILCONFIRM", "Confirmation Email"); //New with 0.98rc9
define("_SL_REPLACEOK", "This will replace the existing text. Continue?"); //New with 0.98rc9
define("_SL_ALLOWSAVE", "Allow Saves?"); //New with 0.99dev01
define("_SL_AUTONUMBER", "Start ID numbers at:"); //New with 0.99dev01
define("_SL_AUTORELOAD", "Automatically load URL when survey complete?"); //New with 0.99dev01
define("_SL_ALLOWPREV", "Show [<< Prev] button"); //New with 0.99dev01
define("_SL_USE_DEFAULT","Use default");
define("_SL_UPD_SURVEY","Update survey");

//groups
define("_GL_TITLE", "Titel:");
define("_GL_DESCRIPTION", "Omschrijving:");
define("_GL_EDITGROUP","Edit Group for Survey ID"); // New with 0.99dev02
define("_GL_UPDATEGROUP","Update Group"); // New with 0.99dev02
//questions
define("_QL_EDITQUESTION", "Edit Question");// New with 0.99dev02
define("_QL_UPDATEQUESTION", "Update Question");// New with 0.99dev02
define("_QL_CODE", "Code:");
define("_QL_QUESTION", "Vraag:");
define("_QL_VALIDATION", "Validation:"); //New in VALIDATION VERSION
define("_QL_HELP", "Help:");
define("_QL_TYPE", "Type:");
define("_QL_GROUP", "Groep:");
define("_QL_MANDATORY", "Verplicht:");
define("_QL_OTHER", "Andere:");
define("_QL_LABELSET", "Label set:");
define("_QL_COPYANS", "Kopieer antwoorden?"); //New in 0.98rc3
define("_QL_QUESTIONATTRIBUTES", "Question Attributes:"); //New in 0.99dev01
define("_QL_COPYATT", "Copy Attributes?"); //New in 0.99dev01
//answers
define("_AL_CODE", "Code");
define("_AL_ANSWER", "Antwoord");
define("_AL_DEFAULT", "Standaard");
define("_AL_MOVE", "Verplaats");
define("_AL_ACTION", "Bewerking");
define("_AL_UP", "Hoger");
define("_AL_DN", "Lager");
define("_AL_SAVE", "Bewaar");
define("_AL_DEL", "Wis");
define("_AL_ADD", "Toevoegen");
define("_AL_FIXSORT", "Herstel sorteervolgorde");
define("_AL_SORTALPHA", "Sort Alpha"); //New in 0.98rc8 - Sort Answers Alphabetically
//users
define("_UL_USER", "Gebruiker");
define("_UL_PASSWORD", "Paswoord");
define("_UL_SECURITY", "Beveiliging");
define("_UL_ACTION", "Bewerking");
define("_UL_EDIT", "Bewerk");
define("_UL_DEL", "Wis");
define("_UL_ADD", "Toevoegen");
define("_UL_TURNOFF", "Zet beveiliging af");
//tokens
define("_TL_FIRST", "Voornaam");
define("_TL_LAST", "Naam");
define("_TL_EMAIL", "E-mail");
define("_TL_TOKEN", "Toegangscode");
define("_TL_INVITE", "Uitnodiging sturen?");
define("_TL_DONE", "Be&euml;indigd?");
define("_TL_ACTION", "Bewerkingen");
define("_TL_ATTR1", "Attribute 1"); //New for 0.98rc7
define("_TL_ATTR2", "Attribute 2"); //New for 0.98rc7
define("_TL_MPID", "MPID"); //New for 0.98rc7
//labels
define("_LL_NAME", "Naam"); //NEW with 098rc3
define("_LL_CODE", "Code"); //NEW with 098rc3
define("_LL_ANSWER", "Titel"); //NEW with 098rc3
define("_LL_SORTORDER", "Volgorde"); //NEW with 098rc3
define("_LL_ACTION", "Bewerking"); //New with 098rc3

//QUESTION TYPES
define("_5PT", "5 punt keuze");
define("_DATE", "Datum");
define("_GENDER", "Geslacht");
define("_LIST", "E&eacute;nkeuze (Radio)"); //Changed with 0.99dev01
define("_LIST_DROPDOWN", "E&eacute;nkeuze (Dropdown)"); //New with 0.99dev01
define("_LISTWC", "E&eacute;nkeuze met opmerkingen");
define("_MULTO", "Meerkeuze");
define("_MULTOC", "Meerkeuze met opmerkingen");
define("_MULTITEXT", "Meerdere korte antwoorden");
define("_NUMERICAL", "Numerische vraag");
define("_RANK", "Schaal");
define("_STEXT", "Kort vrij antwoord");
define("_LTEXT", "Lang vrij antwoord");
define("_HTEXT", "Huge free text"); //New with 0.99dev01
define("_YESNO", "Ja/Nee");
define("_ARR5", "Matrix (5 punten)");
define("_ARR10", "Matrix (10 punten)");
define("_ARRYN", "Matrix (Ja/Nee/Misschien)");
define("_ARRMV", "Matrix (Meer, Gelijk, Minder)");
define("_ARRFL", "Matrix (flexibele labels)"); //Release 0.98rc3
define("_ARRFLC", "Array (Flexible Labels) by Column"); //Release 0.98rc8
define("_SINFL", "Enkel (flexibele labels)"); //(FOR LATER RELEASE)
define("_EMAIL", "E-mailadres"); //FOR LATER RELEASE
define("_BOILERPLATE", "Hangijzervraag"); //New in 0.98rc6
define("_LISTFL_DROPDOWN", "List (Flexible Labels) (Dropdown)"); //New in 0.99dev01
define("_LISTFL_RADIO", "List (Flexible Labels) (Radio)"); //New in 0.99dev01
define("_SLIDER", "Slider"); //New for slider mod

//GENERAL WORDS AND PHRASES
define("_AD_YES", "Ja");
define("_AD_NO", "Nee");
define("_AD_CANCEL", "Annuleer");
define("_AD_CHOOSE", "Maak uw keuze..");
define("_AD_OR", "OF"); //New in 0.98rc4
define("_ERROR", "Fout");
define("_SUCCESS", "Succesvol");
define("_REQ", "*Verplicht");
define("_ADDS", "Toevoegen vragenlijst");
define("_ADDG", "Toevoegen groep");
define("_ADDQ", "Toevoegen vraag");
define("_ADDA", "Toevoegen antwoord"); //New in 0.98rc4
define("_COPYQ", "Kopieer vraag"); //New in 0.98rc4
define("_ADDU", "Toevoegen gebruiker");
define("_SEARCH", "Zoeken"); //New in 0.98rc4
define("_SAVE", "Bewaar aanpassingen");
define("_NONE", "Geen"); //as in "Do not display anything", "or none chosen";
define("_GO_ADMIN", "Hoofd Administratie scherm"); //text to display to return/display main administration screen
define("_CONTINUE", "Ga verder");
define("_WARNING", "Waarschuwing");
define("_USERNAME", "Gebruikersnaam");
define("_PASSWORD", "Paswoord");
define("_DELETE", "Wis");
define("_CLOSEWIN", "Sluit venster");
define("_TOKEN", "Toegangscode");
define("_DATESTAMP", "Datum"); //Referring to the datestamp or time response submitted
define("_IPADDRESS", "IP Adress"); //Referring to the ip address of the submitter - New with 0.991
define("_COMMENT", "Opmerking");
define("_FROM", "Van"); //For emails
define("_SUBJECT", "Onderwerp"); //For emails
define("_MESSAGE", "Bericht"); //For emails
define("_RELOADING", "Opnieuw laden van het scherm. Gelieve te wachten.");
define("_ADD", "Toevoegen");
define("_UPDATE", "Bijwerken");
define("_BROWSE", "Bladeren"); //New in 098rc5
define("_AND", "and"); //New with 0.98rc8
define("_SQL", "SQL"); //New with 0.98rc8
define("_PERCENTAGE", "Percentage"); //New with 0.98rc8
define("_COUNT", "Count"); //New with 0.98rc8

//SURVEY STATUS MESSAGES (new in 0.98rc3)
define("_SS_NOGROUPS", "Aantal groepen in vragenlijst:"); //NEW for release 0.98rc3
define("_SS_NOQUESTS", "Aantal vragen in vragenlijst:"); //NEW for release 0.98rc3
define("_SS_ANONYMOUS", "Deze vragenlijst is anoniem."); //NEW for release 0.98rc3
define("_SS_TRACKED", "Deze vragenlijst is NIET anoniem."); //NEW for release 0.98rc3
define("_SS_DATESTAMPED", "Deelnames zullen met datum bijgehouden worden"); //NEW for release 0.98rc3
define("_SS_IPADDRESS", "IP Addresses will be logged"); //New with 0.991
define("_SS_COOKIES", "Er worden cookies gebruikt voor controle."); //NEW for release 0.98rc3
define("_SS_QBYQ", "Weergave vraag per vraag."); //NEW for release 0.98rc3
define("_SS_GBYG", "Weergave groep per groep."); //NEW for release 0.98rc3
define("_SS_SBYS", "Weergave op &eacute;&eacute;n pagina."); //NEW for release 0.98rc3
define("_SS_ACTIVE", "Vragenlijst is momenteel actief."); //NEW for release 0.98rc3
define("_SS_NOTACTIVE", "Vragenlijst is momenteel niet actief."); //NEW for release 0.98rc3
define("_SS_SURVEYTABLE", "Naam vragenlijsttabel is:"); //NEW for release 0.98rc3
define("_SS_CANNOTACTIVATE", "Vragenlijst kan nog niet geactiveerd worden."); //NEW for release 0.98rc3
define("_SS_ADDGROUPS", "U moet nog groepen toevoegen"); //NEW for release 0.98rc3
define("_SS_ADDQUESTS", "U moet nog vragen toevoegen"); //NEW for release 0.98rc3
define("_SS_ALLOWREGISTER", "If tokens are used, the public may register for this survey"); //NEW for release 0.98rc9
define("_SS_ALLOWSAVE", "Participants can save partially finished surveys"); //NEW for release 0.99dev01

//QUESTION STATUS MESSAGES (new in 0.98rc4)
define("_QS_MANDATORY", "Verplichte vraag"); //New for release 0.98rc4
define("_QS_OPTIONAL", "Optionele vraag"); //New for release 0.98rc4
define("_QS_NOANSWERS", "U moet nog antwoorden aan deze vraag toevoegen"); //New for release 0.98rc4
define("_QS_NOLID", "U moet nog een label set voor deze vraag kiezen"); //New for release 0.98rc4
define("_QS_COPYINFO", "Nota: u MOET een nieuwe code voor de vraag opgeven"); //New for release 0.98rc4

//General Setup Messages
define("_ST_NODB1", "De ingestelde vragenlijst database bestaat niet");
define("_ST_NODB2", "Ofwel is uw database nog niet aangemaakt, ofwel is er iets verkeerd mee.");
define("_ST_NODB3", "PHPSurveyor kan proberen de database voor u aan te maken.");
define("_ST_NODB4", "Uw ingestelde database naam is:");
define("_ST_CREATEDB", "Maak database");

//USER CONTROL MESSAGES
define("_UC_CREATE", "Aanmaken standaard htaccess bestand");
define("_UC_NOCREATE", "Kon het htaccess bestand niet maken. Controleer config.php op \$homedir instelling, en op schrijfrechten in de juiste mappen.");
define("_UC_SEC_DONE", "Beveiligingsniveaus zijn nu ingesteld!");
define("_UC_CREATE_DEFAULT", "Bezig met het aanmaken van de standaardgebruikers");
define("_UC_UPDATE_TABLE", "Bijwerken gebruikertabel");
define("_UC_HTPASSWD_ERROR", "Er deed zich een fout voor bij het aanmaken van het htpasswd bestand");
define("_UC_HTPASSWD_EXPLAIN", "Als u een Windows server gebruikt, is het aanbevolen dat u het apache htpasswd.exe bestand in uw admin map kopieert om deze functie te kunnen gebruiken. Het bestand wordt meestal gevonden in /apache group/apache/bin/");
define("_UC_SEC_REMOVE", "Verwijderen beveiligingsinstellingen");
define("_UC_ALL_REMOVED", "Toegangsbestand, paswoordbestand en gebruikersdatabase gewist");
define("_UC_ADD_USER", "Bezig met het toevoegen van de gebruiker");
define("_UC_ADD_MISSING", "Kon de gebruiker niet toevoegen. Gebruikersnaam en/of paswoord zijn niet opgegeven");
define("_UC_DEL_USER", "Bezig met het wissen van de gebruiker");
define("_UC_DEL_MISSING", "Kon de gebruiker niet wissen. Gebruikersnaam is niet opgegeven.");
define("_UC_MOD_USER", "Bezig met het bewerken van de gebruiker");
define("_UC_MOD_MISSING", "Kon de gebruiker niet bewerken. Gebruikersnaam en/of paswoord zijn niet opgegeven");
define("_UC_TURNON_MESSAGE1", "U hebt de beveiligingsinstellingen van uw vragenlijst administratie nog niet opgestart en daardoor zijn er geen beperkingen op de toegang.</p>\nAls u op 'Opstarten beveiliging' hieronder drukt, wordt de standaard APACHE beveiliging toegepast op de administratiesectie van deze site. Vanaf dan zijn een gebruikersnaam en paswoord verplicht om toegang te krijgen.");
define("_UC_TURNON_MESSAGE2", "Het is aanbevolen dat u dit standaard paswoord na het starten van het beveiligingssysteem verandert.");
define("_UC_INITIALISE", "Opstarten beveiliging");
define("_UC_NOUSERS", "Er bestaan geen gebruikers in uw tabel. Het is aanbevolen de beveiliging af te zetten. Daarna kunt u het terug aan zetten.");
define("_UC_TURNOFF", "Afzetten beveiliging");

//Activate and deactivate messages
define("_AC_MULTI_NOANSWER", "Deze vraag is een meerkeuzevraag maar er zijn geen antwoorden.");
define("_AC_NOTYPE", "Er is bij deze vraag geen type gedefineerd.");
define("_AC_NOLID", "This question requires a Labelset, but none is set."); //New for 0.98rc8
define("_AC_CON_OUTOFORDER", "Deze vraag heeft een conditie, maar de conditie is bepaald op een vraag die erna verschijnt.");
define("_AC_FAIL", "Deze vragenlijst is niet consistent bevonden");
define("_AC_PROBS", "De volgende problemen zijn gevonden:");
define("_AC_CANNOTACTIVATE", "Deze vragenlijst kan niet worden geactiveerd zolang deze problemen niet zijn opgelost");
define("_AC_READCAREFULLY", "LEES DIT ZORGVULDIG ALVORENS VERDER TE GAAN");
define("_AC_ACTIVATE_MESSAGE1", "U kunt best een vragenlijst pas activeren wanneer u er absoluut zeker van bent dat de opstelling klaar is en u geen aanpassingen meer zult moeten doen.");
define("_AC_ACTIVATE_MESSAGE2", "Eens een vragenlijst geactiveerd is, kunt u volgende zaken niet meer:<ul><li>Toevoegen en wissen van groepen</li><li>Toevoegen of wissen van antwoorden in meerkeuzevragen</li><li>Toevoegen of wissen van antwoorden</li></ul>");
define("_AC_ACTIVATE_MESSAGE3", "U kan wel nog:<ul><li>De code, tekst of het tupe veranderen</li><li>De namen van de groepen veranderen</li><li>Voorgedefinieerde antwoorden (uitgezonderd meerkeuzeantwoorden) toevoegen, wissen of aanpassen</li><li>De naam of omschrijving van de vragenlijst wijzigen</li></ul>");
define("_AC_ACTIVATE_MESSAGE4", "Eens gegevens in deze vragenlijst zijn ingebracht, zal u de vragenlijst moeten deactiveren om groepen of vragen toe te voegen of verwijderen, alle bestaande gegevens worden dan naar een archief verplaatst.");
define("_AC_ACTIVATE", "Activeer");
define("_AC_ACTIVATED", "Vragenlijst is geactiveerd. Deelnametabel is met succes aangemaakt.");
define("_AC_NOTACTIVATED", "Vragenlijst kon niet worden geactiveerd.");
define("_AC_NOTPRIVATE", "Dit is geen anonieme vragenlijst. Er moet ook een toegangscodetabel aangemaakt worden.");
define("_AC_REGISTRATION", "This survey allows public registration. A token table must also be created.");
define("_AC_CREATETOKENS", "Instellen toegangscodes");
define("_AC_SURVEYACTIVE", "Deze vragenlijst is nu actief en deelnames zullen opgeslagen worden.");
define("_AC_DEACTIVATE_MESSAGE1", "Voor een actieve vragenlijst wordt een tabel aangemaakt die alle gegevens bewaard.");
define("_AC_DEACTIVATE_MESSAGE2", "Wanneer u een vragenlijst deactiveert, worden deze gegevens naar een andere tabel verplaatst en bij heractivatie is de tabel opnieuw leeg. Je kan dan deze gegevens niet langer met PHPSurveyor consulteren.");
define("_AC_DEACTIVATE_MESSAGE3", "Gedeactiveerde vragenlijstgegevens kunnen momenteel alleen door systeembeheerders met een MySQL tool zoals phpMyAdmin geconsulteerd worden. Als uw vragenlijst toegangscodes gebruikt, zal ook deze tabel hernoemd worden en enkel door systeembeheerder toegankelijk zijn.");
define("_AC_DEACTIVATE_MESSAGE4", "Uw deelnametabel wordt hernoemd naar:");
define("_AC_DEACTIVATE_MESSAGE5", "U exporteert best de deelnames alvorens te deactiveren. Klik \"Annuleer\" om naar het hoofdmenu terug te keren zonder deze vragenlijst te deactiveren");
define("_AC_DEACTIVATE", "Deactiveer");
define("_AC_DEACTIVATED_MESSAGE1", "De deelnametabel wordt hernoemd naar: ");
define("_AC_DEACTIVATED_MESSAGE2", "De deelnames van deze vragenlijst zijn niet langer beschikbaar in PHPSurveyor.");
define("_AC_DEACTIVATED_MESSAGE3", "U noteert best de naam van deze tabel voor het geval u deze informatie later nog nodig heeft.");
define("_AC_DEACTIVATED_MESSAGE4", "De toegangscodetabel van deze vragenlijst is hernoemd naar: ");

//CHECKFIELDS
define("_CF_CHECKTABLES", "Bezig met verifi&euml;ren of alle tabellen bestaan");
define("_CF_CHECKFIELDS", "Bezig met verifi&euml;ren of alle velden bestaan");
define("_CF_CHECKING", "Bezig met controleren");
define("_CF_TABLECREATED", "Tabel aangemaakt");
define("_CF_FIELDCREATED", "Veld aangemaakt");
define("_CF_OK", "OK");
define("_CFT_PROBLEM", "Er blijken bepaalde tabellen of velden in de database te ontbreken.");

//CREATE DATABASE (createdb.php)
define("_CD_DBCREATED", "Database is aangemaakt.");
define("_CD_POPULATE_MESSAGE", "Gelieve hieronder te klikken om de database te vullen");
define("_CD_POPULATE", "Vul database");
define("_CD_NOCREATE", "Kon de database niet aanmaken");
define("_CD_NODBNAME", "Database informatie niet ingevuld. Dit script mag enkel vanuit admin.php gestart worden.");

//DATABASE MODIFICATION MESSAGES
define("_DB_FAIL_GROUPNAME", "Groep kon niet aangemaakt worden. De verplichte groepnaam ontbreekt");
define("_DB_FAIL_GROUPUPDATE", "Groep kon niet bijgewerkt worden");
define("_DB_FAIL_GROUPDELETE", "Groep kon niet gewist worden");
define("_DB_FAIL_NEWQUESTION", "Vraag kon niet aangemaakt worden");
define("_DB_FAIL_QUESTIONTYPECONDITIONS", "De vraag kon niet bijgewerkt worden. Er zijn condities voor andere vragen die afhangen van antwoorden in deze vraag en het veranderen van het type zou voor problemen zorgen. U moet deze condities verwijderen voor u het type van deze vraag kunt veranderen.");
define("_DB_FAIL_QUESTIONUPDATE", "Vraag kon niet bijgewerkt worden");
define("_DB_FAIL_QUESTIONDELCONDITIONS", "Vraag kon niet gewist worden. There are conditions for other questions that rely on this question. Er zijn condities voor andere vragen die afhangen van antwoorden in deze vraag en het verwijderen van deze vraag zou voor problemen zorgen. U moet deze condities verwijderen voor u deze vraag kunt wissen.");
define("_DB_FAIL_QUESTIONDELETE", "Vraag kon niet gewist worden");
define("_DB_FAIL_NEWANSWERMISSING", "Antwoord kon niet toegevoegd worden. U moet zowel een code als een antwoord opgeven");
define("_DB_FAIL_NEWANSWERDUPLICATE", "Antwoord kon niet toegevoegd worden. Er is al een antwoord met deze code");
define("_DB_FAIL_ANSWERUPDATEMISSING", "Antwoord kon niet bijgewerkt worden. U moet zowel een code als een antwoord opgeven");
define("_DB_FAIL_ANSWERUPDATEDUPLICATE", "Antwoord kon niet bijgewerkt worden. Er is al een antwoord met deze code");
define("_DB_FAIL_ANSWERUPDATECONDITIONS", "Antwoord kon niet bijgewerkt worden. U heeft de code verandert, maar er zijn condities voor andere vragen die afhangen van antwoorden in deze vraag en het veranderen van de code van dit antwoord zou voor problemen zorgen. U moet deze condities verwijderen voor u deze code kunt veranderen");
define("_DB_FAIL_ANSWERDELCONDITIONS", "Antwoord kon niet verwijderd worden. Er zijn condities voor andere vragen die afhangen van antwoorden in deze vraag en het verwijderen van dit antwoord zou voor problemen zorgen. U moet deze condities verwijderen voor u dit antwoord kunt wissen");
define("_DB_FAIL_NEWSURVEY_TITLE", "Vragenlijst kon niet aangemaakt worden omdat er geen korte titel opgegeven is");
define("_DB_FAIL_NEWSURVEY", "Vragenlijst kon niet aangemaakt worden");
define("_DB_FAIL_SURVEYUPDATE", "Vragenlijst kon niet bijgewerkt worden");
define("_DB_FAIL_SURVEYDELETE", "Vragenlijst kon niet gewist worden");

//DELETE SURVEY MESSAGES
define("_DS_NOSID", "U heeft geen onderzoek geselecteerd om te wissen");
define("_DS_DELMESSAGE1", "U staat op het punt deze vragenlijst te wissen");
define("_DS_DELMESSAGE2", "Deze handeling zal de vragenlijst verwijderen, met hieraan vast alle groepen, vragen, antwoorden en condities.");
define("_DS_DELMESSAGE3", "Het is aanbevolen de vragenlijst vanop het hoofdmenu te exporteren alvorens deze definitief te wissen.");
define("_DS_SURVEYACTIVE", "Deze vragenlijst is actief en er bestaat een deelnametabel. Als u deze vragenlijst wist, zullen ook de deelnames gewist worden. Het is aanbevolen deze eerst te exporteren alvorens deze vragenlijst te wissen.");
define("_DS_SURVEYTOKENS", "Deze vragenlijst heeft een toegangscodetabel. Als u de vragenlijst wist, zal ook deze tabel gewist worden. Het is aan te raden deze tabel te exporteren of te backuppen alvorens deze vragenlijst te wissen.");
define("_DS_DELETED", "Deze vragenlijst is gewist.");

//DELETE QUESTION AND GROUP MESSAGES
define("_DG_RUSURE", "Het wissen van deze groep zal ook alle vragen en antwoorden erbij wissen. Bent u zeker dat u wil verdergaan?"); //New for 098rc5
define("_DQ_RUSURE", "Het wissen van deze veraag zal ook alle antwoorden erbij wissen. Bent u zeker dat u wil verdergaan?"); //New for 098rc5

//EXPORT MESSAGES
define("_EQ_NOQID", "Er werd geen QID opgegeven. Kan de vraag niet dumpen.");
define("_ES_NOSID", "Er werd geen SID opgegeven. Kan de vragenlijst niet dumpen.");

//EXPORT RESULTS
define("_EX_FROMSTATS", "Gefilterd uit de statistieken");
define("_EX_HEADINGS", "Vragen");
define("_EX_ANSWERS", "Antwoorden");
define("_EX_FORMAT", "Formaat");
define("_EX_HEAD_ABBREV", "Afgekorte hoofding");
define("_EX_HEAD_FULL", "Volledige hoofding");
define("_EX_ANS_ABBREV", "Antwoordcodes");
define("_EX_ANS_FULL", "Volledige antwoorden");
define("_EX_FORM_WORD", "Microsoft Word");
define("_EX_FORM_EXCEL", "Microsoft Excel");
define("_EX_FORM_CSV", "CSV Komma gescheiden");
define("_EX_EXPORTDATA", "Exporteer gegevens");
define("_EX_COLCONTROLS", "Beheer kolommen"); //New for 0.98rc7
define("_EX_TOKENCONTROLS", "Beheer toegangscodes"); //New for 0.98rc7
define("_EX_COLSELECT", "Kies kolommen"); //New for 0.98rc7
define("_EX_COLOK", "Kies de kolommen die u wenst te exporteren. Selecteer geen enkele om alle kolommen te exporteren."); //New for 0.98rc7
define("_EX_COLNOTOK", "Uw vragenlijst bevat meer dan 255 kolommen aan antwoorden. Bepaalde spreadsheet applicaties zoals Excel laten slechts toe er 255 te importeren. Selecteer hieronder de kolommen die u wenst te exporteren."); //New for 0.98rc7
define("_EX_TOKENMESSAGE", "Uw vragenlijst kan de gegevens van de toegangscodes bij elk antwoord exporteren. Selecteer eventueel de extra velden die u wenst te exporteren."); //New for 0.98rc7
define("_EX_TOKSELECT", "Kies velden uit de toegangscodes"); //New for 0.98rc7

//IMPORT SURVEY MESSAGES
define("_IS_FAILUPLOAD", "Er deed zich een fout voor bij het uploaden van uw bestand. Dit kan onjuiste rechten in uw admin map veroorzaken.");
define("_IS_OKUPLOAD", "Upload geslaagd.");
define("_IS_READFILE", "Bezig met lezen van het bestand..");
define("_IS_WRONGFILE", "Dit bestand is geen PHPSurveyor bestand. Import mislukt.");
define("_IS_IMPORTSUMMARY", "Samenvatting van import vragenlijst");
define("_IS_SUCCESS", "Import van de vragenlijst is voltooid.");
define("_IS_IMPFAILED", "Import van deze vragenlijst is mislukt");
define("_IS_FILEFAILS", "Bestand bevat geen PHPSurveyor gegevens in een correct formaat");

//IMPORT GROUP MESSAGES
define("_IG_IMPORTSUMMARY", "Samenvatting van import groep");
define("_IG_SUCCESS", "Import van de groep is voltooid.");
define("_IG_IMPFAILED", "Import van deze groep is mislukt");
define("_IG_WRONGFILE", "Dit bestand is geen PHPSurveyor groep bestand. Import mislukt");

//IMPORT QUESTION MESSAGES
define("_IQ_NOSID", "Er werd geen SID (Vragenlijst) opgegeven. Kan de vraag niet importeren");
define("_IQ_NOGID", "Er werd geen GID (Groep) opgegeven. Kan de vraag niet importeren");
define("_IQ_WRONGFILE", "Dit bestand is geen PHPSurveyor vraagbestand. Import mislukt");
define("_IQ_IMPORTSUMMARY", "Samenvatting van import vraag");
define("_IQ_SUCCESS", "Import van de vraag is voltooid.");

//IMPORT LABELSET MESSAGES
define("_IL_DUPLICATE", "There was a duplicate labelset, so this set was not imported. The duplicate will be used instead.");

//BROWSE RESPONSES MESSAGES
define("_BR_NOSID", "U hebt geen vragenlijst opgegeven om te bekijken.");
define("_BR_NOTACTIVATED", "Deze vragenlijst is niet geactiveerd. Er zijn geen deelnames om te bekijken.");
define("_BR_NOSURVEY", "Er is geen bijhorende vragenlijst.");
define("_BR_EDITRESPONSE", "Bewerk deelname");
define("_BR_DELRESPONSE", "Wis deelname");
define("_BR_DISPLAYING", "Records weergegeven:");
define("_BR_STARTING", "Starten vanaf:");
define("_BR_SHOW", "Toon");
define("_DR_RUSURE", "Weet u zeker dat u deze deelname wil wissen?"); //New for 0.98rc6

//STATISTICS MESSAGES
define("_ST_FILTERSETTINGS", "Filter instellingen");
define("_ST_VIEWALL", "View summary of all available fields"); //New with 0.98rc8
define("_ST_SHOWRESULTS", "View Stats"); //New with 0.98rc8
define("_ST_CLEAR", "Clear"); //New with 0.98rc8
define("_ST_RESPONECONT", "Responses Containing"); //New with 0.98rc8
define("_ST_NOGREATERTHAN", "Number greater than"); //New with 0.98rc8
define("_ST_NOLESSTHAN", "Number Less Than"); //New with 0.98rc8
define("_ST_DATEEQUALS", "Date (YYYY-MM-DD) equals"); //New with 0.98rc8
define("_ST_ORBETWEEN", "OR between"); //New with 0.98rc8
define("_ST_RESULTS", "Results"); //New with 0.98rc8 (Plural)
define("_ST_RESULT", "Result"); //New with 0.98rc8 (Singular)
define("_ST_RECORDSRETURNED", "No of records in this query"); //New with 0.98rc8
define("_ST_TOTALRECORDS", "Total records in survey"); //New with 0.98rc8
define("_ST_PERCENTAGE", "Percentage of total"); //New with 0.98rc8
define("_ST_FIELDSUMMARY", "Field Summary for"); //New with 0.98rc8
define("_ST_CALCULATION", "Calculation"); //New with 0.98rc8
define("_ST_SUM", "Sum"); //New with 0.98rc8 - Mathematical
define("_ST_STDEV", "Standard Deviation"); //New with 0.98rc8 - Mathematical
define("_ST_AVERAGE", "Average"); //New with 0.98rc8 - Mathematical
define("_ST_MIN", "Minimum"); //New with 0.98rc8 - Mathematical
define("_ST_MAX", "Maximum"); //New with 0.98rc8 - Mathematical
define("_ST_Q1", "1st Quartile (Q1)"); //New with 0.98rc8 - Mathematical
define("_ST_Q2", "2nd Quartile (Median)"); //New with 0.98rc8 - Mathematical
define("_ST_Q3", "3rd Quartile (Q3)"); //New with 0.98rc8 - Mathematical
define("_ST_NULLIGNORED", "*Null values are ignored in calculations"); //New with 0.98rc8
define("_ST_QUARTMETHOD", "*Q1 and Q3 calculated using <a href='http://mathforum.org/library/drmath/view/60969.html' target='_blank'>minitab method</a>"); //New with 0.98rc8

//DATA ENTRY MESSAGES
define("_DE_NOMODIFY", "Kan niet aangepast worden");
define("_DE_UPDATE", "Bijwerken deelname");
define("_DE_NOSID", "U heeft geen vragenlijst voor ingave geselecteerd.");
define("_DE_NOEXIST", "De vragenlijst die u selecteerde bestaat niet");
define("_DE_NOTACTIVE", "Deze vragenlijst is nog niet actief. Uw antwoorden worden niet bewaard");
define("_DE_INSERT", "Invoegen gegevens");
define("_DE_RECORD", "De deelname kreeg volgend record id: ");
define("_DE_ADDANOTHER", "Toevoegen extra record");
define("_DE_VIEWTHISONE", "Bekijk dit record");
define("_DE_BROWSE", "Bekijk deelnames");
define("_DE_DELRECORD", "Record gewist");
define("_DE_UPDATED", "Record bijgewerkt.");
define("_DE_EDITING", "Bezig met bewerken deelname");
define("_DE_QUESTIONHELP", "Help over deze vraag");
define("_DE_CONDITIONHELP1", "Enkel antwoord als de volgende voorwaarden zijn voldaan:"); 
define("_DE_CONDITIONHELP2", "op vraag {QUESTION}, antwoordde u {ANSWER}"); //This will be a tricky one depending on your languages syntax. {ANSWER} is replaced with ALL ANSWERS, separated by _DE_OR (OR).
define("_DE_AND", "EN");
define("_DE_OR", "OF");
define("_DE_SAVEENTRY", "Save as a partially completed survey"); //New in 0.99dev01
define("_DE_SAVEID", "Identifier:"); //New in 0.99dev01
define("_DE_SAVEPW", "Password:"); //New in 0.99dev01
define("_DE_SAVEPWCONFIRM", "Confirm Password:"); //New in 0.99dev01
define("_DE_SAVEEMAIL", "Email:"); //New in 0.99dev01

//TOKEN CONTROL MESSAGES
define("_TC_TOTALCOUNT", "Totaal aantal records in deze toegangscodetabel:"); //New in 0.98rc4
define("_TC_NOTOKENCOUNT", "Totaal zonder unieke toegangscode:"); //New in 0.98rc4
define("_TC_INVITECOUNT", "Totaal aantal uitnodigingen verstuurd:"); //New in 0.98rc4
define("_TC_COMPLETEDCOUNT", "Totaal aantal vragenlijsten voltooid:"); //New in 0.98rc4
define("_TC_NOSID", "U heeft geen vragenlijst geselecteerd");
define("_TC_DELTOKENS", "De toegangscodetabel wordt dadelijk gewist.");
define("_TC_DELTOKENSINFO", "Als u deze tabel wist, zal er niet langer een toegangscode nodig zijn om deze vragenlijst in te vullen. Er wordt een backup van deze tabel gemaakt als u verdergaat. Enkel de systeembeheerder kan nog in deze backup.");
define("_TC_DELETETOKENS", "Wis toegangscodes");
define("_TC_TOKENSGONE", "De toegangscodetabel is nu verwijderd en er is geen toegangscode meer nodig om de vragenlijst in te vullen. Er is een backup gemaakt waar enkel nog de systeembeheerder aan kan.");
define("_TC_NOTINITIALISED", "Toegangscodes zijn niet geactiveerd voor deze vragenlijst.");
define("_TC_INITINFO", "Als u de toegangscodes voor deze vragenlijst activeert, zal deze vragenlijst enkel toegangelijk zijn voor gebruikers die een toegangscode ontvangen hebben.");
define("_TC_INITQ", "Wilt u een toegangscodetabel voor deze vragenlijst aanmaken?");
define("_TC_INITTOKENS", "Activeer toegangscodes");
define("_TC_CREATED", "Er werd een toegangscodetabel voor deze vragenlijst aangemaakt.");
define("_TC_DELETEALL", "Wis alle toegangscodes");
define("_TC_DELETEALL_RUSURE", "Weet u zeker dat u ALLE toegangscodes wil wissen?");
define("_TC_ALLDELETED", "Alle toegangscodes werden gewist");
define("_TC_CLEARINVITES", "Stel alle gebruikers op 'N'iet uitgenodigd");
define("_TC_CLEARINV_RUSURE", "Weet u zeker dat u alle uitnodigingen op NEE wil plaatsen?");
define("_TC_CLEARTOKENS", "Wis alle unieke toegangscodes");
define("_TC_CLEARTOKENS_RUSURE", "Weet u zeker dat u alle unieke toegangscodes wil wissen?");
define("_TC_TOKENSCLEARED", "Alle unieke toegangscodes werden gewist");
define("_TC_INVITESCLEARED", "Alle uitnodigingen werden terug op NEE geplaatst");
define("_TC_EDIT", "Wijzig deelnemer");
define("_TC_DEL", "Wis deelnemer");
define("_TC_DO", "Neem vragenlijst af");
define("_TC_VIEW", "Toon deelname");
define("_TC_UPDATE", "Update Response"); // New with 0.99 stable
define("_TC_INVITET", "Verstuur uitnodigingsmail naar deze deelnemer");
define("_TC_REMINDT", "Verstuur herinneringsmail naar deze deelnemer");
define("_TC_INVITESUBJECT", "Uitnodiging om deel te nemen aan {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSUBJECT", "Herinnering om deel te nemen aan {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSTARTAT", "Start vanaf TID nr.:");
define("_TC_REMINDTID", "Versturen naar TID nr.:");
define("_TC_CREATETOKENSINFO", "Als u bevestigd zullen toegangscodes voor alle deelnemers in de lijst die geen paswoord hebben automatisch aangemaakt worden. Is dit OK?");
define("_TC_TOKENSCREATED", "{TOKENCOUNT} toegangscodes zijn aangemaakt"); //Leave {TOKENCOUNT} for replacement in script with the number of tokens created
define("_TC_TOKENDELETED", "Toegangscode is gewist.");
define("_TC_SORTBY", "Sorteer op: ");
define("_TC_ADDEDIT", "Toevoegen of bewerken toegangscode");
define("_TC_TOKENCREATEINFO", "U kan dit leeg laten en automatisch toegangscodes genereren met 'Aanmaken toegangscodes'");
define("_TC_TOKENADDED", "Nieuwe toegangscodes toegevoegd");
define("_TC_TOKENUPDATED", "Toegangscode bijgewerkt");
define("_TC_UPLOADINFO", "Het bestand moet een standaard CSV (kommagescheiden) bestand zijn zonder aanhalingstekens. De eerste lijn moet de hoofding bevatten (zal niet geÃ¯mporteerd worden. De gegevens dienen geordend worden als 'voornaam, naam, e-mail, [paswoord], [attribute1], [attribute2]'.");
define("_TC_UPLOADFAIL", "Uploadbestand niet gevonden. Controleer de rechten en het path naar de upload map"); //New for 0.98rc5
define("_TC_IMPORT", "Bezig met het importeren van het CSV bestand");
define("_TC_CREATE", "Bezig met het aanmaken van de deelnemers");
define("_TC_TOKENS_CREATED", "{TOKENCOUNT} records aangemaakt");
define("_TC_NONETOSEND", "Er waren geen e-mails te versturen. De mogelijke oorzaken hiervan zijn - niet hebben van een e-mailadres, reeds een uitnodiging verstuurd, de vragenlijst al ingevuld met een toegangscode.");
define("_TC_NOREMINDERSTOSEND", "Er waren geen e-mails te versturen. De mogelijke oorzaken hiervan zijn - niet hebben van een e-mailadres, reeds een herinnering verstuurd, de vragenlijst al ingevuld..");
define("_TC_NOEMAILTEMPLATE", "Sjabloon uitnodiging niet gevonden. Dit bestand moet bestaan in de standaard sjabloon map.");
define("_TC_NOREMINDTEMPLATE", "Herinnering sjabloon niet gevonden. Dit bestand moet bestaan in de standaard sjabloon map.");
define("_TC_SENDEMAIL", "Verstuur uitnodigingen");
define("_TC_SENDINGEMAILS", "Bezig met versturen van uitnodigingen");
define("_TC_SENDINGREMINDERS", "Bezig met versturen van herinneringen");
define("_TC_EMAILSTOGO", "Er zijn meer e-mails lopende dan in &eacute;&eacute;n keer kunnen verstuurd worden. Ga verder met het versturen door hieronder te klikken.");
define("_TC_EMAILSREMAINING", "Er zijn nog {EMAILCOUNT} e-mails te versturen."); //Leave {EMAILCOUNT} for replacement in script by number of emails remaining
define("_TC_SENDREMIND", "Verstuur herinneringen");
define("_TC_INVITESENTTO", "Uitnodiging verzonden naar:"); //is followed by token name
define("_TC_REMINDSENTTO", "Herinnering verzonden naar:"); //is followed by token name
define("_TC_UPDATEDB", "Update toegangscodetabel met nieuwe velden"); //New for 0.98rc7
define("_TC_EMAILINVITE_SUBJ", "Invitation to participate in survey"); //New for 0.99dev01
define("_TC_EMAILINVITE", "Dear {FIRSTNAME},\n\nYou have been invited to participate in a survey.\n\n"
						 ."The survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
						 ."To participate, please click on the link below.\n\nSincerely,\n\n"
						 ."{ADMINNAME} ({ADMINEMAIL})\n\n"
						 ."----------------------------------------------\n"
						 ."Click here to do the survey:\n"
						 ."{SURVEYURL}"); //New for 0.98rc9 - default Email Invitation
define("_TC_EMAILREMIND_SUBJ", "Reminder to participate in survey"); //New for 0.99dev01
define("_TC_EMAILREMIND", "Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\n"
						 ."We note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\n"
						 ."The survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
						 ."To participate, please click on the link below.\n\nSincerely,\n\n"
						 ."{ADMINNAME} ({ADMINEMAIL})\n\n"
						 ."----------------------------------------------\n"
						 ."Click here to do the survey:\n"
						 ."{SURVEYURL}"); //New for 0.98rc9 - default Email Reminder
define("_TC_EMAILREGISTER_SUBJ", "Survey Registration Confirmation"); //New for 0.99dev01
define("_TC_EMAILREGISTER", "Dear {FIRSTNAME},\n\n"
						  ."You, or someone using your email address, have registered to "
						  ."participate in an online survey titled {SURVEYNAME}.\n\n"
						  ."To complete this survey, click on the following URL:\n\n"
						  ."{SURVEYURL}\n\n"
						  ."If you have any questions about this survey, or if you "
						  ."did not register to participate and believe this email "
						  ."is in error, please contact {ADMINNAME} at {ADMINEMAIL}.");//NEW for 0.98rc9
define("_TC_EMAILCONFIRM_SUBJ", "Confirmation of completed survey"); //New for 0.99dev01
define("_TC_EMAILCONFIRM", "Dear {FIRSTNAME},\n\nThis email is to confirm that you have completed the survey titled {SURVEYNAME} "
						  ."and your response has been saved. Thank you for participating.\n\n"
						  ."If you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\n"
						  ."Sincerely,\n\n"
						  ."{ADMINNAME}"); //New for 0.98rc9 - Confirmation Email

//labels.php
define("_LB_NEWSET", "Toevoegen label set");
define("_LB_EDITSET", "Bewerk label set");
define("_LB_FAIL_UPDATESET", "Bijwerken van label set mislukt");
define("_LB_FAIL_INSERTSET", "Toevoegen van label set mislukt");
define("_LB_FAIL_DELSET", "Kon label set niet wissen - Er zijn vragen die ervan afhangen. U moet deze eerst wissen.");
define("_LB_ACTIVEUSE", "U kan de codes niet veranderen, items toevoegen of wissen omdat deze label set in een actieve vragenlijst wordt gebruikt.");
define("_LB_TOTALUSE", "Er zijn vragenlijsten die momenteel deze label set gebruiken. Codes wijzigen, toevoegen of verwijderen in deze label set zou ongewenste gevolgen kunnen hebben.");
//Export Labels
define("_EL_NOLID", "Er werd geen LID opgegeven. Kan label set niet dumpen.");
//Import Labels
define("_IL_GOLABELADMIN", "Terug naar Label Administratie");

//PHPSurveyor System Summary
define("_PS_TITLE", "Samenvatting PHPSurveyor Systeem");
define("_PS_DBNAME", "Naam database");
define("_PS_DEFLANG", "Standaardtaal");
define("_PS_CURLANG", "Huidige taal");
define("_PS_USERS", "Gebruikers");
define("_PS_ACTIVESURVEYS", "Actieve vragenlijsten");
define("_PS_DEACTSURVEYS", "Gedeactiveerde vragenlijsten");
define("_PS_ACTIVETOKENS", "Actieve toegangscodetabellen");
define("_PS_DEACTTOKENS", "Gedeactiveerde toegangscodetabellen");
define("_PS_CHECKDBINTEGRITY", "Check PHPSurveyor Data Integrity"); //New with 0.98rc8

//Notification Levels
define("_NT_NONE", "Geen e-mail verwittiging"); //New with 098rc5
define("_NT_SINGLE", "Eenvoudige e-mail verwittiging"); //New with 098rc5
define("_NT_RESULTS", "E-mail verwittiging met resultaten (codes)"); //New with 098rc5

//CONDITIONS TRANSLATIONS
define("_CD_CONDITIONDESIGNER", "Condition Designer"); //New with 098rc9
define("_CD_ONLYSHOW", "Only show question {QID} IF"); //New with 098rc9 - {QID} is repleaced leave there
define("_CD_AND", "AND"); //New with 098rc9
define("_CD_COPYCONDITIONS", "Copy Conditions"); //New with 098rc9
define("_CD_CONDITION", "Condition"); //New with 098rc9
define("_CD_ADDCONDITION", "Add Condition"); //New with 098rc9
define("_CD_EQUALS", "Equals"); //New with 098rc9
define("_CD_COPYRUSURE", "Are you sure you want to copy these condition(s) to the questions you have selected?"); //New with 098rc9
define("_CD_NODIRECT", "You cannot run this script directly."); //New with 098rc9
define("_CD_NOSID", "You have not selected a Survey."); //New with 098rc9
define("_CD_NOQID", "You have not selected a Question."); //New with 098rc9
define("_CD_DIDNOTCOPYQ", "Did not copy questions"); //New with 098rc9
define("_CD_NOCONDITIONTOCOPY", "No condition selected to copy from"); //New with 098rc9
define("_CD_NOQUESTIONTOCOPYTO", "No question selected to copy condition to"); //New with 098rc9
define("_CD_COPYTO", "copy to"); //New with 0.991

//TEMPLATE EDITOR TRANSLATIONS
define("_TP_CREATENEW", "Create new template"); //New with 098rc9
define("_TP_NEWTEMPLATECALLED", "Create new template called:"); //New with 098rc9
define("_TP_DEFAULTNEWTEMPLATE", "NewTemplate"); //New with 098rc9 (default name for new template)
define("_TP_CANMODIFY", "This template can be modified"); //New with 098rc9
define("_TP_CANNOTMODIFY", "This template cannot be modified"); //New with 098rc9
define("_TP_RENAME", "Rename this template");  //New with 098rc9
define("_TP_RENAMETO", "Rename this template to:"); //New with 098rc9
define("_TP_COPY", "Make a copy of this template");  //New with 098rc9
define("_TP_COPYTO", "Create a copy of this template called:"); //New with 098rc9
define("_TP_COPYOF", "copy_of_"); //New with 098rc9 (prefix to default copy name)
define("_TP_FILECONTROL", "File Control:"); //New with 098rc9
define("_TP_STANDARDFILES", "Standard Files:");  //New with 098rc9
define("_TP_NOWEDITING", "Now editing:");  //New with 098rc9
define("_TP_OTHERFILES", "Other Files:"); //New with 098rc9
define("_TP_PREVIEW", "Preview:"); //New with 098rc9
define("_TP_DELETEFILE", "Delete"); //New with 098rc9
define("_TP_UPLOADFILE", "Upload"); //New with 098rc9
define("_TP_SCREEN", "Screen:"); //New with 098rc9
define("_TP_WELCOMEPAGE", "Welcome Page"); //New with 098rc9
define("_TP_QUESTIONPAGE", "Question Page"); //New with 098rc9
define("_TP_SUBMITPAGE", "Submit Page");
define("_TP_COMPLETEDPAGE", "Completed Page"); //New with 098rc9
define("_TP_CLEARALLPAGE", "Clear All Page"); //New with 098rc9
define("_TP_REGISTERPAGE", "Register Page"); //New with 098finalRC1
define("_TP_EXPORT", "Export Template"); //New with 098rc10
define("_TP_LOADPAGE", "Load Page"); //New with 0.99dev01
define("_TP_SAVEPAGE", "Save Page"); //New with 0.99dev01

//Saved Surveys
define("_SV_RESPONSES", "Saved Responses:");
define("_SV_IDENTIFIER", "Identifier");
define("_SV_RESPONSECOUNT", "Answered");
define("_SV_IP", "IP Address");
define("_SV_DATE", "Date Saved");
define("_SV_REMIND", "Remind");
define("_SV_EDIT", "Edit");

//VVEXPORT/IMPORT
define("_VV_IMPORTFILE", "Import a VV survey file");
define("_VV_EXPORTFILE", "Export a VV survey file");
define("_VV_FILE", "File:");
define("_VV_SURVEYID", "Survey ID:");
define("_VV_EXCLUDEID", "Exclude record IDs?");
define("_VV_INSERT", "When an imported record matches an existing record ID:");
define("_VV_INSERT_ERROR", "Report an error (and skip the new record).");
define("_VV_INSERT_RENUMBER", "Renumber the new record.");
define("_VV_INSERT_IGNORE", "Ignore the new record.");
define("_VV_INSERT_REPLACE", "Replace the existing record.");
define("_VV_DONOTREFRESH", "Important Note:<br />Do NOT refresh this page, as this will import the file again and produce duplicates");
define("_VV_IMPORTNUMBER", "Total records imported:");
define("_VV_ENTRYFAILED", "Import Failed on Record");
define("_VV_BECAUSE", "because");
define("_VV_EXPORTDEACTIVATE", "Export, then de-activate survey");
define("_VV_EXPORTONLY", "Export but leave survey active");
define("_VV_RUSURE", "If you have chosen to export and de-activate, this will rename your current responses table and it will not be easy to restore it. Are you sure?");

//ASSESSMENTS
define("_AS_TITLE", "Assessments");
define("_AS_DESCRIPTION", "If you create any assessments in this page, for the currently selected survey, the assessment will be performed at the end of the survey after submission");
define("_AS_NOSID", "No SID Provided");
define("_AS_SCOPE", "Scope");
define("_AS_MINIMUM", "Minimum");
define("_AS_MAXIMUM", "Maximum");
define("_AS_GID", "Group");
define("_AS_NAME", "Name/Header");
define("_AS_HEADING", "Heading");
define("_AS_MESSAGE", "Message");
define("_AS_URL", "URL");
define("_AS_SCOPE_GROUP", "Group");
define("_AS_SCOPE_TOTAL", "Total");
define("_AS_ACTIONS", "Actions");
define("_AS_EDIT", "Edit");
define("_AS_DELETE", "Delete");
define("_AS_ADD", "Add");
define("_AS_UPDATE", "Update");

//Question Number regeneration
define("_RE_REGENNUMBER", "Regenerate Question Numbers:"); //NEW for release 0.99dev2
define("_RE_STRAIGHT", "Straight"); //NEW for release 0.99dev2
define("_RE_BYGROUP", "By Group"); //NEW for release 0.99dev2

// Databse Consistency Check
define ("_DC_TITLE", "Data Consistency Check<br /><font size='1'>If errors are showing up you might have to execute this script repeatedly. </font>"); // New with 0.99stable
define ("_DC_QUESTIONSOK", "All questions meet consistency standards"); // New with 0.99stable
define ("_DC_ANSWERSOK", "All answers meet consistency standards"); // New with 0.99stable
define ("_DC_CONDITIONSSOK", "All conditions meet consistency standards"); // New with 0.99stable
define ("_DC_GROUPSOK", "All groups meet consistency standards"); // New with 0.99stable
define ("_DC_NOACTIONREQUIRED", "No database action required"); // New with 0.99stable
define ("_DC_QUESTIONSTODELETE", "The following questions should be deleted"); // New with 0.99stable
define ("_DC_ANSWERSTODELETE", "The following answers should be deleted"); // New with 0.99stable
define ("_DC_CONDITIONSTODELETE", "The following conditions should be deleted"); // New with 0.99stable
define ("_DC_GROUPSTODELETE", "The following groups should be deleted"); // New with 0.99stable
define ("_DC_ASSESSTODELETE", "The following assessments should be deleted"); // New with 0.99stable
define ("_DC_QATODELETE", "The following question attributes should be deleted"); // New with 0.99stable
define ("_DC_QAOK", "All question_attributes meet consistency standards"); // New with 0.99stable
define ("_DC_ASSESSOK", "All assessments meet consistency standards"); // New with 0.99stable

?>