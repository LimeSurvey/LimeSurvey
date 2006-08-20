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
    # Public License as published för the Free Software              #
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
    #   								 							#
    #  																#
    #                                                               #
    #  Editera this fil with an UTF-8 capable editor only!            #
    #                                                               #
    #################################################################
*/


//BUTTON BAR TITLES
define("_ADMINISTRATION", "Administration");
define("_SURVEY", "Undersökning");
define("_GROUP", "Grupp");
define("_QUESTION", "Fråga");
define("_ANSWERS", "Svar");
define("_CONDITIONS", "Villkor");
define("_HELP", "Hjälp");
define("_USERCONTROL", "Användarkontroll");
define("_ACTIVATE", "Aktivera Undersökning");
define("_DEACTIVATE", "Deaktivera Undersökning");
define("_CHECKFIELDS", "Kontrollera Databas Fält");
define("_CREATEDB", "Skapa Databas");
define("_CREATESURVEY", "Skapa Undersökning"); //Ny for 0.98rc4
define("_SETUP", "PHPSurveyor Setup");
define("_DELETESURVEY", "Radera Undersökning");
define("_EXPORTQUESTION", "Exportera Fråga");
define("_EXPORTSURVEY", "Exportera Undersökning");
define("_EXPORTLABEL", "Exportera Label Set");
define("_IMPORTQUESTION", "Importera Fråga");
define("_IMPORTGROUP", "Importera Grupp"); //Ny for 0.98rc5
define("_IMPORTSURVEY", "Importera Undersökning");
define("_IMPORTLABEL", "Importera Label Set");
define("_EXPORTRESULTS", "Exportera Svar");
define("_BROWSERESPONSES", "Browse Svar");
define("_BROWSESAVED", "Browse Saved Svar");
define("_STATISTICS", "Quick Statistics");
define("_VIEWRESPONSE", "View Svar");
define("_VIEWCONTROL", "Data View Kontroll");
define("_DATAENTRY", "Data Entry");
define("_TOKENCONTROL", "Token Kontroll");
define("_TOKENDBADMIN", "Token Databas Administration Options");
define("_DROPTOKENS", "Radera Tokens Table");
define("_EMAILINVITE", "Email Inbjudning");
define("_EMAILREMIND", "Email Påminnelse");
define("_TOKENIFY", "Skapa Tokens");
define("_UPLOADCSV", "Ladda upp CSV Fil");
define("_LABELCONTROL", "Label Sets Administration"); //NEW with 0.98rc3
define("_LABELSET", "Label Set"); //NEW with 0.98rc3
define("_LABELANS", "Labels"); //NEW with 0.98rc3
define("_OPTIONAL", "Optional"); //NEW with 0.98finalRC1

//DROPDOWN HEADINGS
define("_SURVEYS", "Undersökningar");
define("_grupper", "Grupper");
define("_QUESTIONS", "Frågor");
define("_QBYQ", "Fråga för Fråga");
define("_GBYG", "Grupp för Grupp");
define("_SBYS", "Alla Frågor Samtidigt");
define("_LABELSETS", "Sets"); //Ny with 0.98rc3

//BUTTON MOUSEOVERS
//administration bar
define("_A_HOME_BT", "Default Administration Page");
define("_A_SECURITY_BT", "Modifiera Säkerhets Inställningar");
define("_A_BADSECURITY_BT", "Aktivera Säkerhet");
define("_A_CHECKDB_BT", "Kontrollera Databas");
define("_A_DELETE_BT", "Radera Hela Undersökningen");
define("_A_ADDSURVEY_BT", "Skapa eller Importera Ny Undersökning");
define("_A_HELP_BT", "Visa Hjälp");
define("_A_CHECKSETTINGS", "Kontrollera Inställningar");
define("_A_BACKUPDB_BT", "Backup Entire Databas"); //Ny for 0.98rc10
define("_A_TEMPLATES_BT", "Mall Editor"); //Ny for 0.98rc9
//Undersökning bar
define("_S_ACTIVE_BT", "Denna undersökning is currently active");
define("_S_INACTIVE_BT", "Denna undersökning is not currently active");
define("_S_ACTIVATE_BT", "Aktivera denna Undersökning");
define("_S_DEACTIVATE_BT", "Deaktivera denna Undersökning");
define("_S_CANNOTACTIVATE_BT", "Kan inte Aktivera this Undersökning");
define("_S_DOSURVEY_BT", "Gör Undersökning");
define("_S_DATAENTRY_BT", "Dataentry Screen for Undersökning");
define("_S_PRINTABLE_BT", "Utskriftversion Version av Undersökningen");
define("_S_EDIT_BT", "Editera Denna Undersökning");
define("_S_DELETE_BT", "Radera Denna Undersökning");
define("_S_EXPORT_BT", "Exportera Denna Undersökning");
define("_S_EXPORT_BT_SINGLE", "Exportera detta Svar");
define("_S_BROWSE_BT", "Bläddra i Svar for denna Undersökning");
define("_S_TOKENS_BT", "Aktivera/Editera Tokens for denna Undersökning");
define("_S_ADDGROUP_BT", "Lägg till Ny Grupp till Undersökningen");
define("_S_MINIMISE_BT", "Dölj Detaljer för denna Undersökning");
define("_S_MAXIMISE_BT", "Visa Detaljer för denna Undersökning");
define("_S_CLOSE_BT", "Stäng denna Undersökning");
define("_S_SAVED_BT", "Titta på sparade men inte skickade Svar"); //Ny in 0.99dev01
define("_S_ASSESSMENT_BT", "Sätt utvärderings regler"); //Ny in  0.99dev01
//Grupp bar
define("_G_EDIT_BT", "Editera Denna Grupp");
define("_G_EXPORT_BT", "Exportera Denna Grupp"); //Ny in 0.98rc5
define("_G_DELETE_BT", "Radera Denna Grupp");
define("_G_ADDQUESTION_BT", "Lägg till Ny Fråga to Grupp");
define("_G_MINIMISE_BT", "Dölj Detaljer of denna Grupp");
define("_G_MAXIMISE_BT", "Visa Detaljer of denna Grupp");
define("_G_CLOSE_BT", "Stäng denna Grupp");
//Fråga bar
define("_Q_EDIT_BT", "Editera Denna Fråga");
define("_Q_COPY_BT", "Kopiera Denna Fråga"); //Ny in 0.98rc4
define("_Q_DELETE_BT", "Radera Denna Fråga");
define("_Q_EXPORT_BT", "Exportera Denna Fråga");
define("_Q_CONDITIONS_BT", "Sätt Villkor för denna Fråga");
define("_Q_ANSWERS_BT", "Editera/Lägg till Svar for denna Fråga");
define("_Q_LABELS_BT", "Editera/Lägg till Label Sets");
define("_Q_MINIMISE_BT", "Dölj Detaljer of denna Fråga");
define("_Q_MAXIMISE_BT", "Visa Detaljer of denna Fråga");
define("_Q_CLOSE_BT", "Stäng denna Fråga");
//Browse Button Bar
define("_B_ADMIN_BT", "Åter till Undersökning Administration");
define("_B_SUMMARY_BT", "Visa summerad information");
define("_B_ALL_BT", "Visa Svar");
define("_B_LAST_BT", "Visa Sista 50 Svaren");
define("_B_STATISTICS_BT", "Statistik för dessa svar");
define("_B_EXPORT_BT", "Exportera Resultat till Applikation");
define("_B_BACKUP_BT", "Ta Backup av resultattablellen till en SQL fil");
define("_B_IMPORTOLDRESULTS_BT","Importera svar från en deaktiverad undersökning");

//Tokens Button Bar
define("_T_ALL_BT", "Visa Tokens");
define("_T_ADD_BT", "Lägg till new token entry");
define("_T_IMPORT_BT", "Importera Tokens från CSV Fil");
define("_T_EXPORT_BT", "Exportera Tokens till CSV fil"); //Ny for 0.98rc7
define("_T_INVITE_BT", "Skicka email inbjudan");
define("_T_REMIND_BT", "Skicka email påminnelse");
define("_T_TOKENIFY_BT", "Generera Tokens");
define("_T_KILL_BT", "Droppa tokens tabell");
//Labels Button Bar
define("_L_ADDSET_BT", "Lägg till new label set");
define("_L_EDIT_BT", "Editera label set");
define("_L_DEL_BT", "Radera label set");
//Datacontrols
define("_D_BEGIN", "Visa första..");
define("_D_BACK", "Visa förra..");
define("_D_FORWARD", "Visa nästa..");
define("_D_END", "Visa sista..");

//DATA LABELS
//surveys
define("_SL_TITLE", "Titel:");
define("_SL_SURVEYURL", "Undersökning URL:"); //new in 0.98rc5
define("_SL_DESCRIPTION", "Besktivning:");
define("_SL_WELCOME", "Välkommen:");
define("_SL_ADMIN", "Administratör:");
define("_SL_EMAIL", "Admin Email:");
define("_SL_FAXTO", "Faxa Till:");
define("_SL_annonym", "Anonym?");
define("_SL_EXPIRY", "Upphör:");
define("_SL_EXPIRYDATE", "Upphör datum:");
define("_SL_FORMAT", "Format:");
define("_SL_DATESTAMP", "Datum stämpel?");
define("_SL_IPADDRESS", "IP Adress"); //Ny with 0.991
define("_SL_TEMPLATE", "Mall:");
define("_SL_LANGUAGE", "Sråk:");
define("_SL_LINK", "Exit Link:");  //Modified in 0.99
define("_SL_URL", "Slut URL:");
define("_SL_URLDESCRIP", "URL Beskrivning:");
define("_SL_STATUS", "Status:");
define("_SL_SELSQL", "Välj SQL Fil:");
define("_SL_USECOOKIES", "Använd Cookies?"); //NEW with 098rc3
define("_SL_NOTIFICATION", "Notifiering:"); //Ny with 098rc5
define("_SL_ALLOWREGISTER", "Tillåt publik registrering?"); //Ny with 0.98rc9
define("_SL_ATTRIBUTENAMES", "Token Attribut Namn:"); //Ny with 0.98rc9
define("_SL_EMAILINVITE_SUBJ", "Inbjudning Email Subject:"); //Ny with 0.99dev01
define("_SL_EMAILINVITE", "Inbjudning Email:"); //Ny with 0.98rc9
define("_SL_EMAILREMIND_SUBJ", "Email Påminnelse Subject:"); //Ny with 0.99dev01
define("_SL_EMAILREMIND", "Email Påminnelse:"); //Ny with 0.98rc9
define("_SL_EMAILREGISTER_SUBJ", "Public registration Email Subject:"); //Ny with 0.99dev01
define("_SL_EMAILREGISTER", "Public registration Email:"); //Ny with 0.98rc9
define("_SL_EMAILCONFIRM_SUBJ", "Bekräftelse Email Subject"); //Ny with 0.99dev01
define("_SL_EMAILCONFIRM", "Bekräftelse Email"); //Ny with 0.98rc9
define("_SL_REPLACEOK", "Detta kommer att ersätta den existerande texten. Fortsätta?"); //Ny with 0.98rc9
define("_SL_ALLOWSAVE", "Tillåt sparningar?"); //Ny with 0.99dev01
define("_SL_AUTONUMBER", "Starta ID nummmer med:"); //Ny with 0.99dev01
define("_SL_AUTORELOAD", "Automatically load URL when undersökning complete?"); //Ny with 0.99dev01
define("_SL_ALLOWPREV", "Visa [<< Prev] knappen"); //Ny with 0.99dev01
define("_SL_USE_DEFAULT","Use default");
define("_SL_UPD_SURVEY","Uppdatera undersökning");

//grupper
define("_GL_TITLE", "Titel:");
define("_GL_DESCRIPTION", "Beskrivning:");
define("_GL_EDITGROUP","Editera Grupp for Undersökning ID"); // Ny with 0.99dev02
define("_GL_UPDATEGROUP","Uppdatera Grupp"); // Ny with 0.99dev02
//questions
define("_QL_EDITQUESTION", "Editera Fråga");// Ny with 0.99dev02
define("_QL_UPDATEQUESTION", "Uppdatera Fråga");// Ny with 0.99dev02
define("_QL_CODE", "Code:");
define("_QL_QUESTION", "Fråga:");
define("_QL_VALIDATION", "Validation:"); //Ny in VALIDATION VERSION
define("_QL_HELP", "Hjälp:");
define("_QL_TYPE", "Type:");
define("_QL_GROUP", "Grupp:");
define("_QL_MANDATORY", "Mandatory:");
define("_QL_OTHER", "Other:");
define("_QL_LABELSET", "Label Set:");
define("_QL_COPYANS", "Kopiera Svar?"); //Ny in 0.98rc3
define("_QL_QUESTIONATTRIBUTES", "Fråga Attributes:"); //Ny in 0.99dev01
define("_QL_COPYATT", "Kopiera Attributes?"); //Ny in 0.99dev01
//answers
define("_AL_CODE", "Code");
define("_AL_ANSWER", "Answer");
define("_AL_DEFAULT", "Default");
define("_AL_MOVE", "Move");
define("_AL_ACTION", "Action");
define("_AL_UP", "Up");
define("_AL_DN", "Dn");
define("_AL_SAVE", "Spara");
define("_AL_DEL", "Del");
define("_AL_ADD", "Lägg till");
define("_AL_FIXSORT", "Fix Sort");
define("_AL_SORTALPHA", "Sort Alpha"); //Ny in 0.98rc8 - Sort Svar Alphabetically
//users
define("_UL_USER", "User");
define("_UL_PASSWORD", "Lösenord");
define("_UL_SECURITY", "Säkerhet");
define("_UL_ACTION", "Action");
define("_UL_EDIT", "Editera");
define("_UL_DEL", "Radera");
define("_UL_ADD", "Lägg till");
define("_UL_TURNOFF", "Turn Off Säkerhet");

//tokens
define("_TL_FIRST", "First Name");
define("_TL_LAST", "Last Name");
define("_TL_EMAIL", "Email");
define("_TL_TOKEN", "Token");
define("_TL_INVITE", "Invite sent?");
define("_TL_DONE", "Completed?");
define("_TL_ACTION", "Actions");
define("_TL_ATTR1", "Attribute 1"); //Ny for 0.98rc7
define("_TL_ATTR2", "Attribute 2"); //Ny for 0.98rc7
define("_TL_MPID", "MPID"); //Ny for 0.98rc7
//labels
define("_LL_NAME", "Set Name"); //NEW with 098rc3
define("_LL_CODE", "Code"); //NEW with 098rc3
define("_LL_ANSWER", "Titel"); //NEW with 098rc3
define("_LL_SORTORDER", "Order"); //NEW with 098rc3
define("_LL_ACTION", "Action"); //Ny with 098rc3

//QUESTION TYPES
define("_5PT", "5 Point Choice");
define("_DATE", "Date");
define("_GENDER", "Gender");
define("_LIST", "List (Radio)"); //Changed with 0.99dev01
define("_LIST_DROPDOWN", "List (Dropdown)"); //Ny with 0.99dev01
define("_LISTWC", "List With Comment");
define("_MULTO", "Multiple Options");
define("_MULTOC", "Multiple Options with Comments");
define("_MULTITEXT", "Multiple Short Text");
define("_NUMERICAL", "Numerical Input");
define("_RANK", "Ranking");
define("_STEXT", "Short free text");
define("_LTEXT", "Long free text");
define("_HTEXT", "Huge free text"); //Ny with 0.99dev01
define("_YESNO", "Yes/No");
define("_ARR5", "Array (5 Point Choice)");
define("_ARR10", "Array (10 Point Choice)");
define("_ARRYN", "Array (Yes/No/Uncertain)");
define("_ARRMV", "Array (Increase, Same, Decrease)");
define("_ARRFL", "Array (Flexible Labels)"); //Release 0.98rc3
define("_ARRFLC", "Array (Flexible Labels) för Column"); //Release 0.98rc8
define("_SINFL", "Single (Flexible Labels)"); //(FOR LATER RELEASE)
define("_EMAIL", "Email Address"); //FOR LATER RELEASE
define("_BOILERPLATE", "Boilerplate Fråga"); //Ny in 0.98rc6
define("_LISTFL_DROPDOWN", "List (Flexible Labels) (Dropdown)"); //Ny in 0.99dev01
define("_LISTFL_RADIO", "List (Flexible Labels) (Radio)"); //Ny in 0.99dev01
define("_SLIDER", "Slider"); //Ny for slider mod

//GENERAL WORDS AND PHRASES
define("_AD_YES", "Yes");
define("_AD_NO", "No");
define("_AD_CANCEL", "Cancel");
define("_AD_CHOOSE", "Please Choose..");
define("_AD_OR", "OR"); //Ny in 0.98rc4
define("_ERROR", "Error");
define("_SUCCESS", "Success");
define("_REQ", "*Required");
define("_ADDS", "Lägg till Undersökning");
define("_ADDG", "Lägg till Grupp");
define("_ADDQ", "Lägg till Fråga");
define("_ADDA", "Lägg till Answer"); //Ny in 0.98rc4
define("_COPYQ", "Kopiera Fråga"); //Ny in 0.98rc4
define("_ADDU", "Lägg till User");
define("_SEARCH", "Search"); //Ny in 0.98rc4
define("_SAVE", "Spara Changes");
define("_NONE", "None"); //as in "Do not display anything", "or none chosen";
define("_GO_ADMIN", "Main Admin Screen"); //text to display to return/display main administration screen
define("_CONTINUE", "Continue");
define("_WARNING", "Warning");
define("_USERNAME", "User name");
define("_PASSWORD", "Lösenord");
define("_DELETE", "Radera");
define("_CLOSEWIN", "Stäng Window");
define("_TOKEN", "Token"); 
define("_DATESTAMP", "Date Stamp"); //Referring to the datestamp or time response submitted
define("_IPADDRESS", "IP Address"); //Referring to the ip address of the submitter - Ny with 0.991
define("_COMMENT", "Comment");
define("_FROM", "From"); //For emails
define("_SUBJECT", "Subject"); //For emails
define("_MESSAGE", "Message"); //For emails
define("_RELOADING", "Reloading Screen. Please wait.");
define("_ADD", "Lägg till");
define("_UPDATE", "Uppdatera");
define("_BROWSE", "Browse"); //Ny in 098rc5
define("_AND", "and"); //Ny with 0.98rc8
define("_SQL", "SQL"); //Ny with 0.98rc8
define("_PERCENTAGE", "Percentage"); //Ny with 0.98rc8
define("_COUNT", "Count"); //Ny with 0.98rc8

//SURVEY STATUS MESSAGES (new in 0.98rc3)
define("_SS_NOgrupper", "Antal grupper i denna undersökning:"); //NEW for release 0.98rc3
define("_SS_NOQUESTS", "Antal frågor i denna undersökning:"); //NEW for release 0.98rc3
define("_SS_annonym", "Denna undersökning är annonym."); //NEW for release 0.98rc3
define("_SS_TRACKED", "Denna undersökning är NOT annonym."); //NEW for release 0.98rc3
define("_SS_DATESTAMPED", "Svar kommer datumstämplas"); //NEW for release 0.98rc3
define("_SS_IPADDRESS", "IP Addressen kommer att loggas"); //Ny with 0.991
define("_SS_COOKIES", "Använder cookies för access kontroll."); //NEW for release 0.98rc3
define("_SS_QBYQ", "Presenteras fråga för fråga."); //NEW for release 0.98rc3
define("_SS_GBYG", "Presenteras grupp för grupp."); //NEW for release 0.98rc3
define("_SS_SBYS", "Presenteras på en sida."); //NEW for release 0.98rc3
define("_SS_ACTIVE", "Undersökningen är aktiv."); //NEW for release 0.98rc3
define("_SS_NOTACTIVE", "Undersökning är inte aktiv."); //NEW for release 0.98rc3
define("_SS_SURVEYTABLE", "Undersökningens tabell namn är:"); //NEW for release 0.98rc3
define("_SS_CANNOTACTIVATE", "Undersökningen kan inte aktiveras än."); //NEW for release 0.98rc3
define("_SS_ADDgrupper", "Du måste skapa grupper"); //NEW for release 0.98rc3
define("_SS_ADDQUESTS", "Du måste skapa frågor"); //NEW for release 0.98rc3
define("_SS_ALLOWREGISTER", "Om token används så kan du inte göra denna undersökning publik"); //NEW for release 0.98rc9
define("_SS_ALLOWSAVE", "Deltagare kan spara delvis genomförda undersökningar"); //NEW for release 0.99dev01

//QUESTION STATUS MESSAGES (new in 0.98rc4)
define("_QS_MANDATORY", "Obligatorisk Fråga"); //Ny for release 0.98rc4
define("_QS_OPTIONAL", "Valfri Fråga"); //Ny for release 0.98rc4
define("_QS_NOANSWERS", "Du måste lägga till svarsalternativ till denna fråga"); //Ny for release 0.98rc4
define("_QS_NOLID", "You need to choose a Label Set for denna question"); //Ny for release 0.98rc4
define("_QS_COPYINFO", "Kommentar: du måste ha en frågekod!"); //Ny for release 0.98rc4

//General Setup Messages
define("_ST_NODB1", "Den definerade undersöknings databasen existerar inte");
define("_ST_NODB2", "Antingen har den valda databasen inte skapats än eller så är det problem att accessa den");
define("_ST_NODB3", "PHPSurveyor kan skapa denna databas åt dig.");
define("_ST_NODB4", "Din valda databas heter:");
define("_ST_CREATEDB", "Skapa Databas");

//USER CONTROL MESSAGES
define("_UC_CREATE", "Skapar standard htaccess fil");
define("_UC_NOCREATE", "Kunde inte skapa htaccessfil. Kontrollera din config.php efter \$homedir inställningen, och att du har skrivrättigheter i denna mapp.");
define("_UC_SEC_DONE", "Säkerhets Nivåer är nu inställda!");
define("_UC_CREATE_DEFAULT", "Skapar standard användare");
define("_UC_UPDATE_TABLE", "Uppdaterar användar tabell");
define("_UC_HTPASSWD_ERROR", "Ett fel uppstod vid skapandet av htpasswd fil");
define("_UC_HTPASSWD_EXPLAIN", "Om du använder en Windows server är det rekommenderat att du kopierar apache htpasswd.exe filen till din admin mapp för att denna funktion skall fungera korrekt. Denna fil finns oftast i /apache group/apache/bin/");
define("_UC_SEC_REMOVE", "Tar bort säkerhetsinställningar");
define("_UC_ALL_REMOVED", "Access fil, password fil och användardatabas raderade");
define("_UC_ADD_USER", "Lägger till användare");
define("_UC_ADD_MISSING", "Kunde inte lägga till användare. Användarnamn och/eller lösenord angavs inte");
define("_UC_DEL_USER", "Raderar användare");
define("_UC_DEL_MISSING", "Kunde inte radera användare. Användarnamn angavs inte.");
define("_UC_MOD_USER", "Modifierar användare");
define("_UC_MOD_MISSING", "Kunde inte modifiera användare. Användarnamn och/eller lösenord angavs inte");
define("_UC_TURNON_MESSAGE1", "<p>Du har inte initierat säkerhetisntällningar för ditt undersöknings system därför är det ingen accesskontroll.</p>\nOm du klickar initiera säkerhets knappen nedan, standard APACHE security settings will be added to the administration directory of denna script. You will then need to use the default access username and password to access the administration and data entry scripts.");
define("_UC_TURNON_MESSAGE2", "It is highly recommended that once your security system has been initialised you change this default password.");
define("_UC_INITIALISE", "Initialise Säkerhet");
define("_UC_NOUSERS", "No users exist in your tabell. We recommend you 'turn off' security. You can then 'turn it on' again.");
define("_UC_TURNOFF", "Turn Off Säkerhet");

//Aktivera and deactivate messages
define("_AC_MULTI_NOANSWER", "Denna question is a multiple answer type question but has no answers.");
define("_AC_NOTYPE", "Denna question does not have a question 'type' set.");
define("_AC_NOLID", "Denna question requires a Labelset, but none is set."); //Ny for 0.98rc8
define("_AC_CON_OUTOFORDER", "Denna question has a condition set, however the condition is based on a question that appears after it.");
define("_AC_FAIL", "Undersökning does not pass consistency check");
define("_AC_PROBS", "The following problems have been found:");
define("_AC_CANNOTACTIVATE", "The undersökning cannot be activated until these problems have been resolved.");
define("_AC_READCAREFULLY", "READ THIS CAREFULLY BEFORE PROCEEDING");
define("_AC_ACTIVATE_MESSAGE1", "You should only activate a undersökning when you are absolutely certain that your undersökning setup is finished and will not need changing.");
define("_AC_ACTIVATE_MESSAGE2", "Once a undersökning is activated you can no longer:<ul><li>Lägg till or delete grupper</li><li>Lägg till or remove answers to Multiple Answer questions</li><li>Lägg till or delete questions</li></ul>");
define("_AC_ACTIVATE_MESSAGE3", "However you can still:<ul><li>Editera (change) your questions code, text or type</li><li>Editera (change) your group names</li><li>Lägg till, Remove or Editera pre-defined question answers (except for Multi-answer questions)</li><li>Change undersökning name or description</li></ul>");
define("_AC_ACTIVATE_MESSAGE4", "Once data has been entered into this undersökning, if you want to add or remove grupper or questions, you will need to de-activate this undersökning, which will move all data that has already been entered into a separate archived tabell.");
define("_AC_ACTIVATE", "Aktivera");
define("_AC_ACTIVATED", "Undersökning has been activated. Resultat tabell has been succesfully created.");
define("_AC_NOTACTIVATED", "Undersökning could not be actived.");
define("_AC_NOTPRIVATE", "This is not an annonym undersökning. A token tabell must also be created.");
define("_AC_REGISTRATION", "This undersökning allows public registration. A token tabell must also be created.");
define("_AC_CREATETOKENS", "Initialise Tokens");
define("_AC_SURVEYACTIVE", "This undersökning is now active, and responses can be recorded.");
define("_AC_DEACTIVATE_MESSAGE1", "In an active undersökning, a tabell is created to store all the data-entry records.");
define("_AC_DEACTIVATE_MESSAGE2", "When you de-activate a undersökning all the data entered in the original tabell will be moved elsewhere, and when you activate the undersökning again, the tabell will be empty. You will not be able to access this data using PHPSurveyor any more.");
define("_AC_DEACTIVATE_MESSAGE3", "De-activated undersökning data can only be accessed för system administrators using a MySQL data access tool like phpmyadmin. If your undersökning uses tokens, this tabell will also be renamed and will only be accessible för system administrators.");
define("_AC_DEACTIVATE_MESSAGE4", "Your responses tabell will be renamed to:");
define("_AC_DEACTIVATE_MESSAGE5", "You should export your responses before de-activating. Click \"Cancel\" to return to the main admin screen without de-activating this undersökning.");
define("_AC_DEACTIVATE", "De-Aktivera");
define("_AC_DEACTIVATED_MESSAGE1", "The responses tabell has been renamed to: ");
define("_AC_DEACTIVATED_MESSAGE2", "The responses to this undersökning are no longer available using PHPSurveyor.");
define("_AC_DEACTIVATED_MESSAGE3", "You should note the name of this tabell in case you need to access this information later.");
define("_AC_DEACTIVATED_MESSAGE4", "The tokens tabell associated with this undersökning has been renamed to: ");

//CHECKFIELDS
define("_CF_CHECKTABLES", "Checking to ensure all tables exist");
define("_CF_CHECKFIELDS", "Checking to ensure all fields exist");
define("_CF_CHECKING", "Checking");
define("_CF_TABLECREATED", "Table Skapad");
define("_CF_FIELDCREATED", "Field Skapad");
define("_CF_OK", "OK");
define("_CFT_PROBLEM", "It appears as if some tables or fields are missing från your database.");

//CREATE DATABASE (createdb.php)
define("_CD_DBCREATED", "Databas has been created.");
define("_CD_POPULATE_MESSAGE", "Please click below to populate the database");
define("_CD_POPULATE", "Populate Databas");
define("_CD_NOCREATE", "Could not create database");
define("_CD_NODBNAME", "Databas Information not provided. This script must be run från admin.php only.");

//DATABASE MODIFICATION MESSAGES
define("_DB_FAIL_GROUPNAME", "Grupp could not be added. It is missing the mandatory group name");
define("_DB_FAIL_GROUPUPDATE", "Grupp could not be updated");
define("_DB_FAIL_GROUPDELETE", "Grupp could not be raderad");
define("_DB_FAIL_NEWQUESTION", "Fråga could not be created.");
define("_DB_FAIL_QUESTIONTYPECONDITIONS", "Fråga could not be updated. There are conditions for other questions that rely on the answers to this question and changing the type will cause problems. You must delete these conditions before you can change the type of this question.");
define("_DB_FAIL_QUESTIONUPDATE", "Fråga could not be updated");
define("_DB_FAIL_QUESTIONDELCONDITIONS", "Fråga could not be raderad. There are conditions for other questions that rely on this question. You cannot delete this question until those conditions are removed");
define("_DB_FAIL_QUESTIONDELETE", "Fråga could not be raderad");
define("_DB_FAIL_NEWANSWERMISSING", "Answer could not be added. You must include both a Code and an Answer");
define("_DB_FAIL_NEWANSWERDUPLICATE", "Answer could not be added. There is already an answer with this code");
define("_DB_FAIL_ANSWERUPDATEMISSING", "Answer could not be updated. You must include both a Code and an Answer");
define("_DB_FAIL_ANSWERUPDATEDUPLICATE", "Answer could not be updated. There is already an answer with this code");
define("_DB_FAIL_ANSWERUPDATECONDITIONS", "Answer could not be updated. You have changed the answer code, but there are conditions to other questions which are dependant upon the old answer code to this question. You must delete these conditions before you can change the code to this answer.");
define("_DB_FAIL_ANSWERDELCONDITIONS", "Answer could not be raderad. There are conditions for other questions that rely on this answer. You cannot delete this answer until those conditions are removed");
define("_DB_FAIL_NEWSURVEY_TITLE", "Undersökning could not be created because it did not have a short title");
define("_DB_FAIL_NEWSURVEY", "Undersökning could not be created");
define("_DB_FAIL_SURVEYUPDATE", "Undersökning could not be updated");
define("_DB_FAIL_SURVEYDELETE", "Undersökning could not be raderad");

//DELETE SURVEY MESSAGES
define("_DS_NOSID", "You have not selected a undersökning to delete");
define("_DS_DELMESSAGE1", "You are about to delete this undersökning");
define("_DS_DELMESSAGE2", "This process will delete this undersökning, and all related grupper, questions answers and conditions.");
define("_DS_DELMESSAGE3", "We recommend that before you delete this undersökning you export the entire undersökning från the main administration screen.");
define("_DS_SURVEYACTIVE", "This undersökning is active and a responses tabell exists. If you delete this undersökning, these responses will be raderad. We recommend that you export the responses before deleting this undersökning.");
define("_DS_SURVEYTOKENS", "This undersökning has an associated tokens tabell. If you delete this undersökning this tokens tabell will be raderad. We recommend that you export or backup these tokens before deleting this undersökning.");
define("_DS_raderad", "This undersökning has been raderad.");

//DELETE QUESTION AND GROUP MESSAGES
define("_DG_RUSURE", "Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?"); //Ny for 098rc5
define("_DQ_RUSURE", "Deleting this question will also delete any answers it includes. Are you sure you want to continue?"); //Ny for 098rc5

//EXPORT MESSAGES
define("_EQ_NOQID", "No QID has been provided. Cannot dump question.");
define("_ES_NOSID", "No SID has been provided. Cannot dump undersökning");

//EXPORT RESULTS
define("_EX_FROMSTATS", "Filtered från Statistics Script");
define("_EX_FROM_SINGLE_ANSWER", "Single Svar");
define("_EX_HEADINGS", "Questions");
define("_EX_ANSWERS", "Svar");
define("_EX_FORMAT", "Format");
define("_EX_HEAD_ABBREV", "Abbreviated headings");
define("_EX_HEAD_FULL", "Full headings");
define("_EX_HEAD_CODES", "Fråga Codes");
define("_EX_ANS_ABBREV", "Answer Codes");
define("_EX_ANS_FULL", "Full Svar");
define("_EX_FORM_WORD", "Microsoft Word");
define("_EX_FORM_EXCEL", "Microsoft Excel");
define("_EX_FORM_CSV", "CSV Comma Delimited");
define("_EX_EXPORTDATA", "Exportera Data");
define("_EX_COLCONTROLS", "Column Kontroll"); //Ny for 0.98rc7
define("_EX_TOKENCONTROLS", "Token Kontroll"); //Ny for 0.98rc7
define("_EX_COLSELECT", "Choose columns"); //Ny for 0.98rc7
define("_EX_COLOK", "Choose the columns you wish to export."); //Ny for 0.98rc7
define("_EX_COLNOTOK", "Your undersökning contains more than 255 columns of responses. Spreadsheet applications such as Excel are limited to loading no more than 255. Select the columns you wish to export in the list below."); //Ny for 0.98rc7
define("_EX_TOKENMESSAGE", "Your undersökning can export associated token data with each response. Select any additional fields you would like to export."); //Ny for 0.98rc7
define("_EX_TOKSELECT", "Choose Token Fält"); //Ny for 0.98rc7

//IMPORT SURVEY MESSAGES
define("_IS_FAILUPLOAD", "An error occurred uploading your fil. This may be caused för incorrect permissions in your admin folder.");
define("_IS_OKUPLOAD", "Fil upload succeeded.");
define("_IS_READFILE", "Reading fil..");
define("_IS_WRONGFILE", "This fil is not a PHPSurveyor undersökning fil. Importera failed.");
define("_IS_IMPORTSUMMARY", "Undersökning Importera Summary");
define("_IS_SUCCESS", "Importera of Undersökning is completed.");
define("_IS_IMPFAILED", "Importera of this undersökning fil failed");
define("_IS_FILEFAILS", "Fil does not contain PHPSurveyor data in the correct format.");

//IMPORT GROUP MESSAGES
define("_IG_IMPORTSUMMARY", "Grupp Importera Summary");
define("_IG_SUCCESS", "Importera of Grupp is completed.");
define("_IG_IMPFAILED", "Importera of this group fil failed");
define("_IG_WRONGFILE", "This fil is not a PHPSurveyor group fil. Importera failed.");

//IMPORT QUESTION MESSAGES
define("_IQ_NOSID", "No SID (Undersökning) has been provided. Cannot import question.");
define("_IQ_NOGID", "No GID (Grupp) has been provided. Cannot import question");
define("_IQ_WRONGFILE", "This fil is not a PHPSurveyor question fil. Importera failed.");
define("_IQ_IMPORTSUMMARY", "Fråga Importera Summary");
define("_IQ_SUCCESS", "Importera of Fråga is completed");

//IMPORT LABELSET MESSAGES
define("_IL_DUPLICATE", "There was a duplicate labelset, so this set was not imported. The duplicate will be used instead.");

//BROWSE RESPONSES MESSAGES
define("_BR_NOSID", "You have not selected a undersökning to browse.");
define("_BR_NOTACTIVATED", "This undersökning has not been activated. There are no results to browse.");
define("_BR_NOSURVEY", "There is no matching undersökning.");
define("_BR_EDITRESPONSE", "Editera this entry");
define("_BR_DELRESPONSE", "Radera this entry");
define("_BR_DISPLAYING", "Records Displayed:");
define("_BR_STARTING", "Starting From:");
define("_BR_SHOW", "Visa");
define("_DR_RUSURE", "Are you sure you want to delete this entry?"); //Ny for 0.98rc6

//STATISTICS MESSAGES
define("_ST_FILTERSETTINGS", "Filter Inställningar");
define("_ST_VIEWALL", "View summary of all available fields"); //Ny with 0.98rc8
define("_ST_SHOWRESULTS", "View Stats"); //Ny with 0.98rc8
define("_ST_CLEAR", "Clear"); //Ny with 0.98rc8
define("_ST_RESPONECONT", "Svar Containing"); //Ny with 0.98rc8
define("_ST_NOGREATERTHAN", "Number greater than"); //Ny with 0.98rc8
define("_ST_NOLESSTHAN", "Number Less Than"); //Ny with 0.98rc8
define("_ST_DATEEQUALS", "Date (YYYY-MM-DD) equals"); //Ny with 0.98rc8
define("_ST_ORBETWEEN", "OR between"); //Ny with 0.98rc8
define("_ST_RESULTS", "Resultat"); //Ny with 0.98rc8 (Plural)
define("_ST_RESULT", "Result"); //Ny with 0.98rc8 (Singular)
define("_ST_RECORDSRETURNED", "No of records in this query"); //Ny with 0.98rc8
define("_ST_TOTALRECORDS", "Total records in undersökning"); //Ny with 0.98rc8
define("_ST_PERCENTAGE", "Percentage of total"); //Ny with 0.98rc8
define("_ST_FIELDSUMMARY", "Field Summary for"); //Ny with 0.98rc8
define("_ST_CALCULATION", "Calculation"); //Ny with 0.98rc8
define("_ST_SUM", "Sum"); //Ny with 0.98rc8 - Mathematical
define("_ST_STDEV", "Standard Deviation"); //Ny with 0.98rc8 - Mathematical
define("_ST_AVERAGE", "Average"); //Ny with 0.98rc8 - Mathematical
define("_ST_MIN", "Minimum"); //Ny with 0.98rc8 - Mathematical
define("_ST_MAX", "Maximum"); //Ny with 0.98rc8 - Mathematical
define("_ST_Q1", "1st Quartile (Q1)"); //Ny with 0.98rc8 - Mathematical
define("_ST_Q2", "2nd Quartile (Median)"); //Ny with 0.98rc8 - Mathematical
define("_ST_Q3", "3rd Quartile (Q3)"); //Ny with 0.98rc8 - Mathematical
define("_ST_NULLIGNORED", "*Null values are ignored in calculations"); //Ny with 0.98rc8
define("_ST_QUARTMETHOD", "*Q1 and Q3 calculated using <a href='http://mathforum.org/library/drmath/view/60969.html' target='_blank'>minitab method</a>"); //Ny with 0.98rc8

//DATA ENTRY MESSAGES
define("_DE_NOMODIFY", "Cannot be modified");
define("_DE_UPDATE", "Uppdatera Entry");
define("_DE_NOSID", "You have not selected a undersökning for data-entry.");
define("_DE_NOEXIST", "The undersökning you selected does not exist");
define("_DE_NOTACTIVE", "This undersökning is not yet active. Your response cannot be saved");
define("_DE_INSERT", "Inserting Data");
define("_DE_RECORD", "The entry was assigned the following record id: ");
define("_DE_ADDANOTHER", "Lägg till Another Record");
define("_DE_VIEWTHISONE", "View This Record");
define("_DE_BROWSE", "Browse Svar");
define("_DE_DELRECORD", "Record Raderad");
define("_DE_UPDATED", "Record has been updated.");
define("_DE_EDITING", "Editing Svar");
define("_DE_QUESTIONHELP", "Hjälp about this question");
define("_DE_CONDITIONHELP1", "Only answer this if the following conditions are met:"); 
define("_DE_CONDITIONHELP2", "to question {QUESTION}, you answered {ANSWER}"); //This will be a tricky one depending on your languages syntax. {ANSWER} is replaced with ALL ANSWERS, separated för _DE_OR (OR).
define("_DE_AND", "AND");
define("_DE_OR", "OR");
define("_DE_SAVEENTRY", "Spara as a partially completed undersökning"); //Ny in 0.99dev01
define("_DE_SAVEID", "Identifier:"); //Ny in 0.99dev01
define("_DE_SAVEPW", "Lösenord:"); //Ny in 0.99dev01
define("_DE_SAVEPWCONFIRM", "Confirm Lösenord:"); //Ny in 0.99dev01
define("_DE_SAVEEMAIL", "Email:"); //Ny in 0.99dev01

//TOKEN CONTROL MESSAGES
define("_TC_TOTALCOUNT", "Total Records in this Token Table:"); //Ny in 0.98rc4
define("_TC_NOTOKENCOUNT", "Total With No Unique Token:"); //Ny in 0.98rc4
define("_TC_INVITECOUNT", "Total Invitations Sent:"); //Ny in 0.98rc4
define("_TC_COMPLETEDCOUNT", "Total Undersökningar Completed:"); //Ny in 0.98rc4
define("_TC_NOSID", "You have not selected a undersökning");
define("_TC_DELTOKENS", "About to delete tokens tabell for this undersökning.");
define("_TC_DELTOKENSINFO", "If you delete this tabell tokens will no longer be required to access this undersökning.<br>A backup of this tabell will be made if you proceed. Your system administrator will be able to access this tabell.");
define("_TC_DELETETOKENS", "Radera Tokens");
define("_TC_TOKENSGONE", "The tokens tabell has now been removed and tokens are no longer required to access this undersökning.<BR> A backup of this tabell has been made and can be accessed för your system administrator.");
define("_TC_NOTINITIALISED", "Tokens have not been initialised for this undersökning.");
define("_TC_INITINFO", "If you initialise tokens for this undersökning, the undersökning will only be accessible to users who have been assigned a token.");
define("_TC_INITQ", "Do you want to create a tokens tabell for this undersökning?");
define("_TC_INITTOKENS", "Initialise Tokens");
define("_TC_CREATED", "A token tabell has been created for this undersökning.");
define("_TC_DELETEALL", "Radera all token entries");
define("_TC_DELETEALL_RUSURE", "Are you really sure you want to delete ALL token entries?");
define("_TC_ALLraderad", "All token entries have been raderad.");
define("_TC_CLEARINVITES", "Set all entries to 'No inbjudan sent'.");
define("_TC_CLEARINV_RUSURE", "Are you really sure you want to reset all inbjudan records to NO?");
define("_TC_CLEARTOKENS", "Radera all unique token numbers");
define("_TC_CLEARTOKENS_RUSURE", "Are you sure you want to delete all unique token numbers?");
define("_TC_TOKENSCLEARED", "All unique token numbers have been removed.");
define("_TC_INVITESCLEARED", "All invite entries have been set to 'Not Invited'.");
define("_TC_EDIT", "Editera Token Entry");
define("_TC_DEL", "Radera Token Entry");
define("_TC_DO", "Do Undersökning");
define("_TC_VIEW", "View Svar");
define("_TC_UPDATE", "Uppdatera Svar"); // Ny with 0.99 stable
define("_TC_INVITET", "Skicka inbjudan email to this entry");
define("_TC_REMINDT", "Skicka påminnelse email to this entry");
define("_TC_INVITESUBJECT", "Inbjudning to participate in {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSUBJECT", "Påminnelse to participate in {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSTARTAT", "Start at TID No:");
define("_TC_REMINDTID", "Sending to TID No:");
define("_TC_CREATETOKENSINFO", "Clicking yes will generate tokens for all those in this token list that have not been issued one. Is this OK?");
define("_TC_TOKENSCREATED", "{TOKENCOUNT} tokens have been created"); //Leave {TOKENCOUNT} for replacement in script with the number of tokens created
define("_TC_TOKENraderad", "Token has been raderad.");
define("_TC_SORTBY", "Sort för: ");
define("_TC_ADDEDIT", "Lägg till or Editera Token");
define("_TC_TOKENCREATEINFO", "You can leave this blank, and automatically generate tokens using 'Skapa Tokens'");
define("_TC_TOKENADDED", "Added Ny Token");
define("_TC_TOKENUPDATED", "Updated Token");
define("_TC_UPLOADINFO", "Fil should be a standard CSV (comma delimited) fil with no quotes. The first line should contain header information (will be removed). Data should be ordered as \"firstname, lastname, email, [token], [attribute1], [attribute2]\".");
define("_TC_UPLOADFAIL", "Ladda upp fil not found. Kontrollera your permissions and path for the upload directory"); //Ny for 0.98rc5
define("_TC_IMPORT", "Importing CSV Fil");
define("_TC_CREATE", "Creating Token Entries");
define("_TC_TOKENS_CREATED", "{TOKENCOUNT} Records Skapad");
define("_TC_NONETOSEND", "There were no eligible emails to send. This will be because none satisfied the criteria of - having an email address, not having been sent an inbjudan already, having already completed the undersökning and having a token.");
define("_TC_NOREMINDERSTOSEND", "There were no eligible emails to send. This will be because none satisfied the criteria of - having an email address, having been sent an inbjudan, but not having yet completed the undersökning.");
define("_TC_NOEMAILTEMPLATE", "Inbjudning Mall cannot be found. This fil must exist in the default template folder.");
define("_TC_NOREMINDTEMPLATE", "Påminnelse Mall cannot be found. This fil must exist in the default template folder.");
define("_TC_SENDEMAIL", "Skicka Invitations");
define("_TC_SENDINGEMAILS", "Sending Invitations");
define("_TC_SENDINGREMINDERS", "Sending Reminders");
define("_TC_EMAILSTOGO", "There are more emails pending than can be sent in one batch. Continue sending emails för clicking below.");
define("_TC_EMAILSREMAINING", "There are {EMAILCOUNT} emails still to be sent."); //Leave {EMAILCOUNT} for replacement in script för number of emails remaining
define("_TC_SENDREMIND", "Skicka Reminders");
define("_TC_INVITESENTTO", "Inbjudning Sent To:"); //is followed för token name
define("_TC_REMINDSENTTO", "Påminnelse Sent To:"); //is followed för token name
define("_TC_UPDATEDB", "Uppdatera tokens tabell with new fields"); //Ny for 0.98rc7
define("_TC_MAILTOFAILED", "Mail to {FIRSTNAME} {LASTNAME} ({EMAIL}) Failed"); //Ny for 0.991
define("_TC_EMAILINVITE_SUBJ", "Inbjudning to participate in undersökning"); //Ny for 0.99dev01
define("_TC_EMAILINVITE", "Dear {FIRSTNAME},\n\nYou have been invited to participate in a undersökning.\n\n"
						 ."The undersökning is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
						 ."To participate, please click on the link below.\n\nSincerely,\n\n"
						 ."{ADMINNAME} ({ADMINEMAIL})\n\n"
						 ."----------------------------------------------\n"
						 ."Click here to do the undersökning:\n"
						 ."{SURVEYURL}"); //Ny for 0.98rc9 - default Email Inbjudning
define("_TC_EMAILREMIND_SUBJ", "Påminnelse to participate in undersökning"); //Ny for 0.99dev01
define("_TC_EMAILREMIND", "Dear {FIRSTNAME},\n\nRecently we invited you to participate in a undersökning.\n\n"
						 ."We note that you have not yet completed the undersökning, and wish to remind you that the undersökning is still available should you wish to take part.\n\n"
						 ."The undersökning is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
						 ."To participate, please click on the link below.\n\nSincerely,\n\n"
						 ."{ADMINNAME} ({ADMINEMAIL})\n\n"
						 ."----------------------------------------------\n"
						 ."Click here to do the undersökning:\n"
						 ."{SURVEYURL}"); //Ny for 0.98rc9 - default Email Påminnelse
define("_TC_EMAILREGISTER_SUBJ", "Undersökning Registration Bekräftelse"); //Ny for 0.99dev01
define("_TC_EMAILREGISTER", "Dear {FIRSTNAME},\n\n"
						  ."You, or someone using your email address, have registered to "
						  ."participate in an online undersökning titled {SURVEYNAME}.\n\n"
						  ."To complete this undersökning, click on the following URL:\n\n"
						  ."{SURVEYURL}\n\n"
						  ."If you have any questions about this undersökning, or if you "
						  ."did not register to participate and believe this email "
						  ."is in error, please contact {ADMINNAME} at {ADMINEMAIL}.");//NEW for 0.98rc9
define("_TC_EMAILCONFIRM_SUBJ", "Bekräftelse of completed undersökning"); //Ny for 0.99dev01
define("_TC_EMAILCONFIRM", "Dear {FIRSTNAME},\n\nThis email is to confirm that you have completed the undersökning titled {SURVEYNAME} "
						  ."and your response has been saved. Thank you for participating.\n\n"
						  ."If you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\n"
						  ."Sincerely,\n\n"
						  ."{ADMINNAME}"); //Ny for 0.98rc9 - Bekräftelse Email

//labels.php
define("_LB_NEWSET", "Skapa Ny Label Set");
define("_LB_EDITSET", "Editera Label Set");
define("_LB_FAIL_UPDATESET", "Uppdatera of Label Set failed");
define("_LB_FAIL_INSERTSET", "Insert of new Label Set failed");
define("_LB_FAIL_DELSET", "Couldn't Radera Label Set - There are questions that rely on this. You must delete these questions first.");
define("_LB_ACTIVEUSE", "You cannot change codes, add or delete entries in this label set because it is being used för an active undersökning.");
define("_LB_TOTALUSE", "Some surveys currently use this label set. Modifying the codes, adding or deleting entries to this label set may produce undesired results in other surveys.");
//Exportera Labels
define("_EL_NOLID", "No LID has been provided. Cannot dump label set.");
//Importera Labels
define("_IL_GOLABELADMIN", "Return to Labels Admin");

//PHPSurveyor System Summary
define("_PS_TITLE", "PHPSurveyor System Summary");
define("_PS_DBNAME", "Databas Name");
define("_PS_DEFLANG", "Default Sråk");
define("_PS_CURLANG", "Denna Sråk");
define("_PS_USERS", "Users");
define("_PS_ACTIVESURVEYS", "Active Undersökningar");
define("_PS_DEACTSURVEYS", "De-activated Undersökningar");
define("_PS_ACTIVETOKENS", "Active Token Tables");
define("_PS_DEACTTOKENS", "De-activated Token Tables");
define("_PS_CHECKDBINTEGRITY", "Kontrollera PHPSurveyor Data Integrity"); //Ny with 0.98rc8

//Notification Levels
define("_NT_NONE", "No email notification"); //Ny with 098rc5
define("_NT_SINGLE", "Basic email notification"); //Ny with 098rc5
define("_NT_RESULTS", "Skicka email notification with result codes"); //Ny with 098rc5

//CONDITIONS TRANSLATIONS
define("_CD_CONDITIONDESIGNER", "Condition Designer"); //Ny with 098rc9
define("_CD_ONLYSHOW", "Only show question {QID} IF"); //Ny with 098rc9 - {QID} is repleaced leave there
define("_CD_AND", "AND"); //Ny with 098rc9
define("_CD_COPYCONDITIONS", "Kopiera Villkor"); //Ny with 098rc9
define("_CD_CONDITION", "Condition"); //Ny with 098rc9
define("_CD_ADDCONDITION", "Lägg till Condition"); //Ny with 098rc9
define("_CD_EQUALS", "Equals"); //Ny with 098rc9
define("_CD_COPYRUSURE", "Are you sure you want to copy these condition(s) to the questions you have selected?"); //Ny with 098rc9
define("_CD_NODIRECT", "You cannot run this script directly."); //Ny with 098rc9
define("_CD_NOSID", "You have not selected a Undersökning."); //Ny with 098rc9
define("_CD_NOQID", "You have not selected a Fråga."); //Ny with 098rc9
define("_CD_DIDNOTCOPYQ", "Did not copy questions"); //Ny with 098rc9
define("_CD_NOCONDITIONTOCOPY", "No condition selected to copy från"); //Ny with 098rc9
define("_CD_NOQUESTIONTOCOPYTO", "No question selected to copy condition to"); //Ny with 098rc9
define("_CD_COPYTO", "copy to"); //Ny with 0.991

//TEMPLATE EDITOR TRANSLATIONS
define("_TP_CREATENEW", "Skapa new template"); //Ny with 098rc9
define("_TP_NEWTEMPLATECALLED", "Skapa new template called:"); //Ny with 098rc9
define("_TP_DEFAULTNEWTEMPLATE", "NewTemplate"); //Ny with 098rc9 (default name for new template)
define("_TP_CANMODIFY", "This template can be modified"); //Ny with 098rc9
define("_TP_CANNOTMODIFY", "This template cannot be modified"); //Ny with 098rc9
define("_TP_RENAME", "Rename this template");  //Ny with 098rc9
define("_TP_RENAMETO", "Rename this template to:"); //Ny with 098rc9
define("_TP_COPY", "Make a copy of this template");  //Ny with 098rc9
define("_TP_COPYTO", "Skapa a copy of this template called:"); //Ny with 098rc9
define("_TP_COPYOF", "copy_of_"); //Ny with 098rc9 (prefix to default copy name)
define("_TP_FILECONTROL", "Fil Kontroll:"); //Ny with 098rc9
define("_TP_STANDARDFILES", "Standard Files:");  //Ny with 098rc9
define("_TP_NOWEDITING", "Now editing:");  //Ny with 098rc9
define("_TP_OTHERFILES", "Other Files:"); //Ny with 098rc9
define("_TP_PREVIEW", "Preview:"); //Ny with 098rc9
define("_TP_DELETEFILE", "Radera"); //Ny with 098rc9
define("_TP_UPLOADFILE", "Ladda upp"); //Ny with 098rc9
define("_TP_SCREEN", "Screen:"); //Ny with 098rc9
define("_TP_WELCOMEPAGE", "Welcome Page"); //Ny with 098rc9
define("_TP_QUESTIONPAGE", "Fråga Page"); //Ny with 098rc9
define("_TP_SUBMITPAGE", "Submit Page");
define("_TP_COMPLETEDPAGE", "Completed Page"); //Ny with 098rc9
define("_TP_CLEARALLPAGE", "Clear All Page"); //Ny with 098rc9
define("_TP_REGISTERPAGE", "Register Page"); //Ny with 098finalRC1
define("_TP_EXPORT", "Exportera Mall"); //Ny with 098rc10
define("_TP_LOADPAGE", "Load Page"); //Ny with 0.99dev01
define("_TP_SAVEPAGE", "Spara Page"); //Ny with 0.99dev01

//Saved Undersökningar
define("_SV_RESPONSES", "Saved Svar:");
define("_SV_IDENTIFIER", "Identifier");
define("_SV_RESPONSECOUNT", "Answered");
define("_SV_IP", "IP Address");
define("_SV_DATE", "Date Saved");
define("_SV_REMIND", "Remind");
define("_SV_EDIT", "Editera");

//VVExport/Importera
define("_VV_IMPORTFILE", "Importera a VV undersökning fil");
define("_VV_EXPORTFILE", "Exportera a VV undersökning fil");
define("_VV_FILE", "Fil:");
define("_VV_SURVEYID", "Undersökning ID:");
define("_VV_EXCLUDEID", "Exclude record IDs?");
define("_VV_INSERT", "When an imported record matches an existing record ID:");
define("_VV_INSERT_ERROR", "Report an error (and skip the new record).");
define("_VV_INSERT_RENUMBER", "Renumber the new record.");
define("_VV_INSERT_IGNORE", "Ignore the new record.");
define("_VV_INSERT_REPLACE", "Replace the existing record.");
define("_VV_DONOTREFRESH", "Important Note:<br />Do NOT refresh this page, as this will import the fil again and produce duplicates");
define("_VV_IMPORTNUMBER", "Total records imported:");
define("_VV_ENTRYFAILED", "Importera Failed on Record");
define("_VV_BECAUSE", "because");
define("_VV_EXPORTDEACTIVATE", "Exportera, then de-activate undersökning");
define("_VV_EXPORTONLY", "Exportera but leave undersökning active");
define("_VV_RUSURE", "If you have chosen to export and de-activate, this will rename your current responses tabell and it will not be easy to restore it. Are you sure?");

//SPSS Exportera
define("_SPSS_EXPORTFILE", "Exportera result to a SPSS command fil");

//Assessments
define("_AS_TITLE", "Assessments");
define("_AS_DESCRIPTION", "If you create any assessments in this page, for the currently selected undersökning, the assessment will be performed at the end of the undersökning after submission");
define("_AS_NOSID", "No SID Provided");
define("_AS_SCOPE", "Scope");
define("_AS_MINIMUM", "Minimum");
define("_AS_MAXIMUM", "Maximum");
define("_AS_GID", "Grupp");
define("_AS_NAME", "Name/Header");
define("_AS_HEADING", "Heading");
define("_AS_MESSAGE", "Message");
define("_AS_URL", "URL");
define("_AS_SCOPE_GROUP", "Grupp");
define("_AS_SCOPE_TOTAL", "Total");
define("_AS_ACTIONS", "Actions");
define("_AS_EDIT", "Editera");
define("_AS_DELETE", "Radera");
define("_AS_ADD", "Lägg till");
define("_AS_UPDATE", "Uppdatera");

//Fråga Number regeneration
define("_RE_REGENNUMBER", "Regenerate Fråga Numbers:"); //NEW for release 0.99dev2
define("_RE_STRAIGHT", "Straight"); //NEW for release 0.99dev2
define("_RE_BYGROUP", "By Grupp"); //NEW for release 0.99dev2

// Databse Consistency Kontrollera
define ("_DC_TITLE", "Data Consistency Kontrollera<br /><font size='1'>If errors are showing up you might have to execute this script repeatedly. </font>"); // Ny with 0.99stable
define ("_DC_QUESTIONSOK", "All questions meet consistency standards"); // Ny with 0.99stable
define ("_DC_ANSWERSOK", "All answers meet consistency standards"); // Ny with 0.99stable
define ("_DC_CONDITIONSSOK", "All conditions meet consistency standards"); // Ny with 0.99stable
define ("_DC_grupperOK", "All grupper meet consistency standards"); // Ny with 0.99stable
define ("_DC_NOACTIONREQUIRED", "No database action required"); // Ny with 0.99stable
define ("_DC_QUESTIONSTODELETE", "The following questions should be raderad"); // Ny with 0.99stable
define ("_DC_ANSWERSTODELETE", "The following answers should be raderad"); // Ny with 0.99stable
define ("_DC_CONDITIONSTODELETE", "The following conditions should be raderad"); // Ny with 0.99stable
define ("_DC_grupperTODELETE", "The following grupper should be raderad"); // Ny with 0.99stable
define ("_DC_ASSESSTODELETE", "The following assessments should be raderad"); // Ny with 0.99stable
define ("_DC_QATODELETE", "The following question attributes should be raderad"); // Ny with 0.99stable
define ("_DC_QAOK", "All question_attributes meet consistency standards"); // Ny with 0.99stable
define ("_DC_ASSESSOK", "All assessments meet consistency standards"); // Ny with 0.99stable

// Importera old Svar dialogue

define ("_IORD_TITLE", "Importera responses från an old (deactivated) undersökning tabell into an active undersökning"); // Ny with 0.991stable
define ("_IORD_TARGETID", "Target Undersökning ID"); // Ny with 0.991stable
define ("_IORD_BTIMPORT", "Importera Svar"); // Ny with 0.991stable


?>