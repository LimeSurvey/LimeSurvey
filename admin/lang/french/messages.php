<?php
/*
    #################################################################
    # >>> PHP Surveyor                                              #
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
    # This language file provided by Pascal BASTIEN 20/07/2004      #
    # Version 1.4.1                                                 #
    #################################################################
*/

//BUTTON BAR TITLES
define("_ADMINISTRATION", "Administration");
define("_SURVEY", "Questionnaire");
define("_GROUP", "Groupe");
define("_QUESTION", "Question");
define("_ANSWERS", "R&eacute;ponses");
define("_CONDITIONS", "Conditions");
define("_HELP", "Aide");
define("_USERCONTROL", "Controle Utilisateur");
define("_ACTIVATE", "Activer le Questionnaire");
define("_DEACTIVATE", "D&eacute;sactiver le Questionnaire");
define("_CHECKFIELDS", "Contr&ocirc;le des Champs de la Base de donn&eacute;es");
define("_CREATEDB", "Cr&eacute;er la Base de donn&eacute;es");
define("_CREATESURVEY", "Cr&eacute;er le Questionnaire"); //New for 0.98rc4
define("_SETUP", "Param&egrave;tres de PHPSurveyor");
define("_DELETESURVEY", "Supprimer le Questionnaire");
define("_EXPORTQUESTION", "Exporter la Question");
define("_EXPORTSURVEY", "Exporter le Questionnaire");
define("_EXPORTLABEL", "Exporter le jeu d Etiquettes");
define("_IMPORTQUESTION", "Importer la Question");
define("_IMPORTGROUP", "Importer le Groupe"); //New for 0.98rc5
define("_IMPORTSURVEY", "Importer le Questionnaire");
define("_IMPORTLABEL", "Importer jeu Etiquette");
define("_EXPORTRESULTS", "Exporter les r&eacute;ponses");
define("_BROWSERESPONSES", "Parcourir les r&eacute;ponses");
define("_BROWSESAVED", "Browse Saved Responses");
define("_STATISTICS", "Statistiques flash");
define("_VIEWRESPONSE", "Voir R&eacute;ponse");
define("_VIEWCONTROL", "Contr&ocirc;le de la Visualisation des donn&eacute;es");
define("_DATAENTRY", "Entr&eacute;e donn&eacute;es");
define("_TOKENCONTROL", "Contr&ocirc;le Invitation");
define("_TOKENDBADMIN", "Options d`administration de la Base de donn&eacute;es Invitation");
define("_DROPTOKENS", "Suppression de la Table des Invitations");
define("_EMAILINVITE", "Invitation par EMail");
define("_EMAILREMIND", "Rappel Email");
define("_TOKENIFY", "Cr&eacute;er les Invitations");
define("_UPLOADCSV", "Uploader le fichier CSV");
define("_LABELCONTROL", "Administration des jeux d`Etiquettes"); //NEW with 0.98rc3
define("_LABELSET", "Jeu d`Etiquette"); //NEW with 0.98rc3
define("_LABELANS", "Etiquettes"); //NEW with 0.98rc3
define("_OPTIONAL", "Optional"); //NEW with 0.98finalRC1

//DROPDOWN HEADINGS
define("_SURVEYS", "Questionnaires");
define("_GROUPS", "Groupes");
define("_QUESTIONS", "Questions");
define("_QBYQ", "Question par Question");
define("_GBYG", "Groupe par Groupe");
define("_SBYS", "Tout en un");
define("_LABELSETS", "Jeux"); //New with 0.98rc3

//BUTTON MOUSEOVERS
//administration bar
define("_A_HOME_BT", "Page dAdministration par D&eacute;faut");
define("_A_SECURITY_BT", "Modifier les param&egrave;tres de S&eacute;curit&eacute;");
define("_A_BADSECURITY_BT", "Activer la S&eacute;curit&eacute;");
define("_A_CHECKDB_BT", "V&eacute;rifier la Base de Donn&eacute;es");
define("_A_DELETE_BT", "Supprimer tout le Questionnaire");
define("_A_ADDSURVEY_BT", "Cr&eacute;er ou Importer un Nouveau Questionnaire");
define("_A_HELP_BT", "Aide");
define("_A_CHECKSETTINGS", "V&eacute;rifier les Param&egrave;tres");
define("_A_BACKUPDB_BT", "Backup Entire Database"); //New for 0.98rc10
define("_A_TEMPLATES_BT", "Editeur de Mod&egrave;les"); //New for 0.98rc9
//Survey bar
define("_S_ACTIVE_BT", "Ce Questionnaire est actuellement activ&eacute;");
define("_S_INACTIVE_BT", "Ce Questionnaire est actuellement D&eacute;sactiv&eacute;");
define("_S_ACTIVATE_BT", "Activer ce Questionnaire");
define("_S_DEACTIVATE_BT", "D&eacute;sactiver cet Questionnaire");
define("_S_CANNOTACTIVATE_BT", "Impossible d`activer ce Questionnaire");
define("_S_DOSURVEY_BT", "Ex&eacute;cuter (tester) le Questionnaire");
define("_S_DATAENTRY_BT", "Ecran de Saisie de Donn&eacute;es pour le Questionnaire");
define("_S_PRINTABLE_BT", "Version imprimable du Questionnaire");
define("_S_EDIT_BT", "Editer le Questionnaire Courant");
define("_S_DELETE_BT", "Supprimer le Questionnaire Courant");
define("_S_EXPORT_BT", "Exporter ce Questionnaire");
define("_S_BROWSE_BT", "Parcourir les R&eacute;ponses pour ce Questionnaire");
define("_S_TOKENS_BT", "Activer/Editer les Invitations pour ce Questionnaire");
define("_S_ADDGROUP_BT", "Ajouter un Nouveau Groupe au Questionnaire");
define("_S_MINIMISE_BT", "Masquer les D&eacute;tails de ce Questionnaire");
define("_S_MAXIMISE_BT", "Afficher les D&eacute;tails de ce Questionnaire");
define("_S_CLOSE_BT", "Fermer ce Questionnaire");
define("_S_SAVED_BT", "View Saved but not submitted Responses"); //New in 0.99dev01
define("_S_ASSESSMENT_BT", "Set assessment rules"); //New in  0.99dev01
//Group bar
define("_G_EDIT_BT", "Editer le Groupe en Cours");
define("_G_EXPORT_BT", "Exporter le Groupe en Cours"); //New in 0.98rc5
define("_G_DELETE_BT", "Supprimer le Groupe en Cours");
define("_G_ADDQUESTION_BT", "Ajouter une nouvelle Question au Groupe");
define("_G_MINIMISE_BT", "Masquer les D&eacute;tails de ce Groupe");
define("_G_MAXIMISE_BT", "Afficher les D&eacute;tails de ce Groupe");
define("_G_CLOSE_BT", "Fermer ce Groupe");
//Question bar
define("_Q_EDIT_BT", "Editer la Question en cours");
define("_Q_COPY_BT", "Copier la Question en Cours"); //New in 0.98rc4
define("_Q_DELETE_BT", "Supprimer la Question en Cours");
define("_Q_EXPORT_BT", "Exporter cette Question");
define("_Q_CONDITIONS_BT", "Affecter des Conditions pour Cette Question");
define("_Q_ANSWERS_BT", "Editer/Ajouter des R&eacute;ponses pour cette Question");
define("_Q_LABELS_BT", "Editer/Ajouter jeux d Etiquette");
define("_Q_MINIMISE_BT", "Masquer les D&eacute;tails de cette Question");
define("_Q_MAXIMISE_BT", "Afficher les D&eacute;tails de cette Question");
define("_Q_CLOSE_BT", "Fermer cette Question");
//Browse Button Bar
define("_B_ADMIN_BT", "Retourner &agrave; l Ecran d Administration du Questionnaire");
define("_B_SUMMARY_BT", "Montrer Info R&eacute;sum&eacute;");
define("_B_ALL_BT", "Afficher les R&eacute;ponses");
define("_B_LAST_BT", "Afficher les 50 derni&egrave;res R&eacute;ponses");
define("_B_STATISTICS_BT", "Donner les Statistiques de ces R&eacute;ponses");
define("_B_EXPORT_BT", "Exporter les R&eacute;sultats vers une Application");
define("_B_BACKUP_BT", "Sauvegarder vers un fichier SQL la Table de R&eacute;sultats");
//Tokens Button Bar
define("_T_ALL_BT", "Afficher les Invitations");
define("_T_ADD_BT", "Ajouter une nouvelle entr&eacute;e/Invitation");
define("_T_IMPORT_BT", "Importer Invitations &agrave; partir d un fichier CSV");
define("_T_EXPORT_BT", "Exporter des Invitations vers un Fichier CSV"); //New for 0.98rc7
define("_T_INVITE_BT", "Envoyer une invitation par Email");
define("_T_REMIND_BT", "Envoyer un rappel par EMail");
define("_T_TOKENIFY_BT", "G&eacute;n&eacute;rer des Invitations");
define("_T_KILL_BT", "Effacer la table des Invitations");
//Labels Button Bar
define("_L_ADDSET_BT", "Aj. Nouveau jeu Etiquette");
define("_L_EDIT_BT", "Editer un jeu d Etiquette");
define("_L_DEL_BT", "Supprimer un Jeu d Etiquette");
//Datacontrols
define("_D_BEGIN", "Montrer D&eacute;but..");
define("_D_BACK", "Montrer Pr&eacute;c&eacute;dant..");
define("_D_FORWARD", "Montrer Suivant..");
define("_D_END", "Montrer Fin..");

//DATA LABELS
//surveys
define("_SL_TITLE", "Titre:");
define("_SL_SURVEYURL", "URL du Questionnaire:"); //new in 0.98rc5
define("_SL_DESCRIPTION", "Description:");
define("_SL_WELCOME", "Message de Bienvenue:");
define("_SL_ADMIN", "Administrateur:");
define("_SL_EMAIL", "Email de l`Administrateur:");
define("_SL_FAXTO", "Fax &agrave;:");
define("_SL_ANONYMOUS", "Anonyme?");
define("_SL_EXPIRES", "Expire:");
define("_SL_FORMAT", "Format:");
define("_SL_DATESTAMP", "R&eacute;ponses dat&eacute;es");
define("_SL_TEMPLATE", "Mod&egrave;le:");
define("_SL_LANGUAGE", "Langue:");
define("_SL_LINK", "Lien:");
define("_SL_URL", "URL de Fin:");
define("_SL_URLDESCRIP", "Description de l`URL:");
define("_SL_STATUS", "Status:");
define("_SL_SELSQL", "S&eacute;lectionner un fichier SQL:");
define("_SL_USECOOKIES", "Utiliser des Cookies?"); //NEW with 098rc3
define("_SL_NOTIFICATION", "Notification:"); //New with 098rc5
define("_SL_ALLOWREGISTER", "Permettre l`enregistrement publique?"); //New with 0.98rc9
define("_SL_ATTRIBUTENAMES", "Noms Attribu&eacute; &agrave; l`Invitation:"); //New with 0.98rc9
define("_SL_EMAILINVITE_SUBJ", "Invitation Email Subject:"); //New with 0.99dev01
define("_SL_EMAILINVITE", "Invitation par Email:"); //New with 0.98rc9
define("_SL_EMAILREMIND_SUBJ", "Email Reminder Subject:"); //New with 0.99dev01
define("_SL_EMAILREMIND", "Rappel par Email:"); //New with 0.98rc9
define("_SL_EMAILREGISTER_SUBJ", "Public registration Email Subject:"); //New with 0.99dev01
define("_SL_EMAILREGISTER", "Enregistrement de l`Email Publique:"); //New with 0.98rc9
define("_SL_EMAILCONFIRM_SUBJ", "Confirmation Email Subject"); //New with 0.99dev01
define("_SL_EMAILCONFIRM", "Confirmation par Email"); //New with 0.98rc9
define("_SL_REPLACEOK", "Cela remplacera le texte existant. Continuer?"); //New with 0.98rc9
define("_SL_ALLOWSAVE", "Allow Saves?"); //New with 0.99dev01
define("_SL_AUTONUMBER", "Start ID numbers at:"); //New with 0.99dev01
define("_SL_AUTORELOAD", "Automatically load URL when survey complete?"); //New with 0.99dev01
define("_SL_ALLOWPREV", "Show [<< Prev] button"); //New with 0.99dev01
define("_SL_USE_DEFAULT","Use default");
define("_SL_UPD_SURVEY","Update survey");

//groups
define("_GL_TITLE", "Titre:");
define("_GL_DESCRIPTION", "Description:");
//questions
define("_QL_CODE", "Code:");
define("_QL_QUESTION", "Question:");
define("_QL_VALIDATION", "Validation:"); //New in VALIDATION VERSION
define("_QL_HELP", "Aide:");
define("_QL_TYPE", "Type:");
define("_QL_GROUP", "Groupe:");
define("_QL_MANDATORY", "Obligatoire:");
define("_QL_OTHER", "Autre:");
define("_QL_LABELSET", "Jeu d`Etiquette:");
define("_QL_COPYANS", "Copier les R&eacute;ponses?"); //New in 0.98rc3
define("_QL_QUESTIONATTRIBUTES", "Question Attributes:"); //New in 0.99dev01
define("_QL_COPYATT", "Copy Attributes?"); //New in 0.99dev01
//answers
define("_AL_CODE", "Code");
define("_AL_ANSWER", "R&eacute;ponse");
define("_AL_DEFAULT", "Defaut");
define("_AL_MOVE", "D&eacute;placer");
define("_AL_ACTION", "Action");
define("_AL_UP", "Haut");
define("_AL_DN", "Bas");
define("_AL_SAVE", "Sauver");
define("_AL_DEL", "Suppr.");
define("_AL_ADD", "Ajout");
define("_AL_FIXSORT", "Tri");
define("_AL_SORTALPHA", "Tri Alpha"); //New in 0.98rc8 - Sort Answers Alphabetically
//users
define("_UL_USER", "Utilisateur");
define("_UL_PASSWORD", "Mot de Passe");
define("_UL_SECURITY", "Protection");
define("_UL_ACTION", "Action");
define("_UL_EDIT", "Editer");
define("_UL_DEL", "Supprimer");
define("_UL_ADD", "Ajout");
define("_UL_TURNOFF", "D&eacute;sactiver la Protection");
//tokens
define("_TL_FIRST", "Pr&eacute;nom");
define("_TL_LAST", "Nom");
define("_TL_EMAIL", "Email");
define("_TL_TOKEN", "Invitation");
define("_TL_INVITE", "Envoyer l`Invitation?");
define("_TL_DONE", "Complet?");
define("_TL_ACTION", "Actions");
define("_TL_ATTR1", "Att_1"); //New for 0.98rc7
define("_TL_ATTR2", "Att_2"); //New for 0.98rc7
define("_TL_MPID", "MPID"); //New for 0.98rc7
//labels
define("_LL_NAME", "Nom du Jeu"); //NEW with 098rc3
define("_LL_CODE", "Code"); //NEW with 098rc3
define("_LL_ANSWER", "Titre"); //NEW with 098rc3
define("_LL_SORTORDER", "Commander"); //NEW with 098rc3
define("_LL_ACTION", "Action"); //New with 098rc3

//QUESTION TYPES
define("_5PT", "Alignement de 5 Boutons Radio");
define("_DATE", "Date");
define("_GENDER", "Genre");
define("_LIST", "Liste (Bouton Radio)");
define("_LIST_DROPDOWN", "Liste (Dropdown)"); //New with 0.99dev01
define("_LISTWC", "Liste d&eacute;roulante avec Commentaire");
define("_MULTO", "Case &agrave; Cocher");
define("_MULTOC", "Case &agrave; Cocher avec Commentaires");
define("_MULTITEXT", "Zones de Texte Court");
define("_NUMERICAL", "Entr&eacute;e Num&eacute;rique");
define("_RANK", "Classement");
define("_STEXT", "Texte Libre Court");
define("_LTEXT", "Zone de commentaires");
define("_HTEXT", "Huge free text"); //New with 0.99dev01
define("_YESNO", "Oui/Non");
define("_ARR5", "Ligne de 5 Boutons Radio");
define("_ARR10", "Ligne de 10 Boutons Radio");
define("_ARRYN", "Ligne (Oui/Non/Indiff&eacute;rent)");
define("_ARRMV", "Ligne (Augmenter, Sans changement, Diminuer)");
define("_ARRFL", "Ligne de Bouton Radio (Etiquettes Personnalis&eacute;es)"); //Release 0.98rc3
define("_ARRFLC", "Ligne de Bouton Radio (Etiquettes Personnalis&eacute;es en colonne"); //Release 0.98rc8
define("_SINFL", "Simple (Etiquettes Personnalis&eacute;es)"); //(FOR LATER RELEASE)
define("_EMAIL", "Adresse Email"); //FOR LATER RELEASE
define("_BOILERPLATE", "Texte Fixe"); //New in 0.98rc6
define("_LISTFL_DROPDOWN", "List (Flexible Labels) (Dropdown)"); //New in 0.99dev01
define("_LISTFL_RADIO", "List (Flexible Labels) (Radio)"); //New in 0.99dev01

//GENERAL WORDS AND PHRASES
define("_AD_YES", "Oui");
define("_AD_NO", "Non");
define("_AD_CANCEL", "Annuler");
define("_AD_CHOOSE", "S&eacute;lectionner..");
define("_AD_OR", "OU"); //New in 0.98rc4
define("_ERROR", "Erreur");
define("_SUCCESS", "Succ&eacute;s");
define("_REQ", "*Requis");
define("_ADDS", "Ajouter un Questionnaire");
define("_ADDG", "Ajouter un Groupe");
define("_ADDQ", "Ajouter une Question");
define("_ADDA", "Ajouter une R&eacute;ponse"); //New in 0.98rc4
define("_COPYQ", "Copier une Question"); //New in 0.98rc4
define("_ADDU", "Ajouter un utilisateurr");
define("_SEARCH", "Chercher"); //New in 0.98rc4
define("_SAVE", "Sauver les Modifications");
define("_NONE", "Rien"); //as in "Do not display anything", "or none chosen";
define("_GO_ADMIN", "Ecran Principal d Administration"); //text to display to return/display main administration screen
define("_CONTINUE", "Continuer");
define("_WARNING", "Avertissement");
define("_USERNAME", "Nom d`Utilisateur");
define("_PASSWORD", "Mot de Passe");
define("_DELETE", "Supprimer");
define("_CLOSEWIN", "Fermer la Fenêtre");
define("_TOKEN", "Invitation");
define("_DATESTAMP", "D&eacute;lai de R&eacute;ponse"); //Referring to the datestamp or time response submitted
define("_COMMENT", "Commentaire");
define("_FROM", "De"); //For emails
define("_SUBJECT", "Objet"); //For emails
define("_MESSAGE", "Message"); //For emails
define("_RELOADING", "Actualiser l`Ecran. Veuillez patienter.");
define("_ADD", "Ajouter");
define("_UPDATE", "Mise &agrave; Jour");
define("_BROWSE", "Parcourir"); //New in 098rc5
define("_AND", "et"); //New with 0.98rc8
define("_SQL", "SQL"); //New with 0.98rc8
define("_PERCENTAGE", "Pourcentage"); //New with 0.98rc8
define("_COUNT", "Decompte"); //New with 0.98rc8

//SURVEY STATUS MESSAGES (new in 0.98rc3)
define("_SS_NOGROUPS", "Nombre de Groupes dans le Questionnaire:"); //NEW for release 0.98rc3
define("_SS_NOQUESTS", "Nombre de Questions dans le Questionnaire:"); //NEW for release 0.98rc3
define("_SS_ANONYMOUS", "Ce Questionnaire est Anonyme."); //NEW for release 0.98rc3
define("_SS_TRACKED", "Ce questionnaire n`EST PAS anonyme."); //NEW for release 0.98rc3
define("_SS_DATESTAMPED", "Les R&eacute;ponses seront dat&eacute;es"); //NEW for release 0.98rc3
define("_SS_COOKIES", "Utilisation des Cookies pour le Contr&ocirc;le d`Acc&eacute;es."); //NEW for release 0.98rc3
define("_SS_QBYQ", "Pr&eacute;sentation: une question par Page."); //NEW for release 0.98rc3
define("_SS_GBYG", "Pr&eacute;sentation: un Groupe de Questions par Page."); //NEW for release 0.98rc3
define("_SS_SBYS", "Pr&eacute;sentation: une Page Simple."); //NEW for release 0.98rc3
define("_SS_ACTIVE", "Questionnaire en Cours."); //NEW for release 0.98rc3
define("_SS_NOTACTIVE", "Questionnaire Inactif."); //NEW for release 0.98rc3
define("_SS_SURVEYTABLE", "Nom de la Table du Questionnaire:"); //NEW for release 0.98rc3
define("_SS_CANNOTACTIVATE", "Impossible d`Activer le Questionnaire Maintenant."); //NEW for release 0.98rc3
define("_SS_ADDGROUPS", "Vous devez Ajouter des Groupes"); //NEW for release 0.98rc3
define("_SS_ADDQUESTS", "Vous devez Ajouter des Questions"); //NEW for release 0.98rc3
define("_SS_ALLOWREGISTER", "Si les Invitations sont utilis&eacute;es, les destinataires doivent être enregistr&eacute;s pour ce Questionnaire"); //NEW for release 0.98rc9
define("_SS_ALLOWSAVE", "Participants can save partially finished surveys"); //NEW for release 0.99dev01

//QUESTION STATUS MESSAGES (new in 0.98rc4)
define("_QS_MANDATORY", "Question Obligatoire"); //New for release 0.98rc4
define("_QS_OPTIONAL", "Question optionnelle"); //New for release 0.98rc4
define("_QS_NOANSWERS", "Vous devez ajouter des r&eacute;ponses &agrave; cette Question"); //New for release 0.98rc4
define("_QS_NOLID", "Vous devez choisir un jeu d`Etiquettes pour cette Question"); //New for release 0.98rc4
define("_QS_COPYINFO", "Note: vous devez OBLIGATOIREMENT saisir un nouveau Code pour la Question"); //New for release 0.98rc4

//General Setup Messages
define("_ST_NODB1", "La Base de Donn&eacute;es du Questionnaire D&eacute;finit n`existe pas");
define("_ST_NODB2", "Soit votre Base de Donn&eacute;es n`a pas &eacute;t&eacute; cr&eacute;e, soit il y a un probl&eacute;me pour y acc&eacute;der.");
define("_ST_NODB3", "PHPSurveyor peut tenter de cr&eacute;er la Base de Donn&eacute;es pour vous.");
define("_ST_NODB4", "Le Nom de votre Base de Donn&eacute;es S&eacute;lectionn&eacute;e est:");
define("_ST_CREATEDB", "Cr&eacute;er la Base de Donn&eacute;es");

//USER CONTROL MESSAGES
define("_UC_CREATE", "Cr&eacute;er le fichier htaccess par defaut");
define("_UC_NOCREATE", "Impossible de Cr&eacute;er le fichier htaccess. V&eacute;rifiez votre config.php sous \$homedir, et que vous avez les permissions d`&eacute;criture dans le bon r&eacute;pertoire.");
define("_UC_SEC_DONE", "Le niveau de s&eacute;curit&eacute; est maintenant configur&eacute;!");
define("_UC_CREATE_DEFAULT", "Cr&eacute;er les utilisateurs par D&eacute;faut");
define("_UC_UPDATE_TABLE", "Mise &agrave; jour de la table des Utilisateurs (users)");
define("_UC_HTPASSWD_ERROR", "Une erreure s`est produite lors de la cr&eacute;ation du fichier htpasswd");
define("_UC_HTPASSWD_EXPLAIN", "Si vous utilisez un serveur Windows il est recommand&eacute; de copier le fichier apache sous votre r&eacute;pertoire d`administration pour que cette fonction fonctionne correctement. Ce fichier se trouve g&eacute;n&eacute;ralement sous /apache group/apache/bin/");
define("_UC_SEC_REMOVE", "Enlever les param&eacute;tres de S&eacute;curit&eacute;");
define("_UC_ALL_REMOVED", "Access file, password file and user database deleted");
define("_UC_ADD_USER", "Ajout d`utilisateur");
define("_UC_ADD_MISSING", "Impossible d`ajouter un utilisateur. Le Nom d`utilisateur et/ou le mot de passe n`&eacute;taient pas renseign&eacute;s");
define("_UC_DEL_USER", "Supprimer l`utilisateur");
define("_UC_DEL_MISSING", "Impossible de supprimer l`utilisateur. Le Nom d`utilisateur n`&eacute;tait pas remplis.");
define("_UC_MOD_USER", "Modification de l`utilisateur");
define("_UC_MOD_MISSING", "Impossible de modifier l`utilisateur. Le Nom d`utilisateur et/ou le mot de passe n`&eacute;taient pas renseign&eacute;s");
define("_UC_TURNON_MESSAGE1", "Vous n`avez pas encore initialis&eacute;s les param&eacute;tres de s&eacute;curit&eacute; pour votre syst&eacute;me de Questionnaire et en cons&eacute;quence il n`y a pas de restrictions d`acc&eacute;s.</p>\nSi vous cliquez sur le bouton `initialiser la S&eacute;curit&eacute;` ci-dessous, les param&eacute;tres de s&eacute;curit&eacute; standard d`Apache seront ajout&eacute;s au r&eacute;pertoire d`administration de ce script. Vous aurez alors besoin d`utiliser le Nom d`utilisateur et le mot de passe par d&eacute;faut pour acc&eacute;der &agrave; l`Administration et aux scripts de saisie de donn&eacute;es.");
define("_UC_TURNON_MESSAGE2", "Il est fortement recommand&eacute;, qu`une fois votre syst&eacute;me de s&eacute;curit&eacute; initialis&eacute;, de changer le mot de passe par d&eacute;faut.");
define("_UC_INITIALISE", "Initialiser la Securit&eacute;");
define("_UC_NOUSERS", "Aucun utilisateur dans la table. Nous vous recommandons de `d&eacute;sactiver` la s&eacute;curit&eacute; ET de la `r&eacute;activer` ensuite.");
define("_UC_TURNOFF", "D&eacute;sactiver la s&eacute;curit&eacute;");

//Activate and deactivate messages
define("_AC_MULTI_NOANSWER", "Cette question est &agrave; r&eacute;ponses multiples mais n`a aucune r&eacute;ponses d&eacute;finie.");
define("_AC_NOTYPE", "Cette question n`a pas de `type` param&eacute;tr&eacute;.");
define("_AC_NOLID", "Un jeu d`Etiquette est requis pour cette question. Aucun n`est saisis."); //New for 0.98rc8
define("_AC_CON_OUTOFORDER", "Cette question &agrave; une condition param&eacute;tr&eacute;e, toutefois la condition est bas&eacute;e sur une question qui apparait apr&eacute;s elle.");
define("_AC_FAIL", "Le Questionnaire n`est pas valid&eacute; par le contr&ocirc;le de coh&eacute;rence");
define("_AC_PROBS", "Le probl&eacute;me suivant a &eacute;t&eacute; rencontr&eacute;:");
define("_AC_CANNOTACTIVATE", "Le Questionnaire ne peut pas être activ&eacute; jusqu`&agrave; ce que ces probl&eacute;mes soient r&eacute;solus");
define("_AC_READCAREFULLY", "LIRE CECI ATTENTIVEMENT AVANT DE POURSUIVRE");
define("_AC_ACTIVATE_MESSAGE1", "Vous devriez activer un Questionnaire seulement si vous êtes absolument certain que votre Questionnaire est correctement param&eacute;tr&eacute;/termin&eacute;eet n`aura pas besoin d`être modifi&eacute;.");
define("_AC_ACTIVATE_MESSAGE2", "Un fois qu`un Questionnaire est activ&eacute; vous ne pouvez plus:<ul><li>Ajouter ou supprimer des groupes</li><li>Ajouter ou enlever des R&eacute;ponses aux questions &agrave; r&eacute;ponses multiples</li><li>Ajouter ou supprimer des questions</li></ul>");
define("_AC_ACTIVATE_MESSAGE3", "Cependant vous pouvez toujours:<ul><li>Editer (Modifier) les codes de vos questions, le texte ou le type </li><li>Editer (Modifier) les noms de vos Groupes</li><li>Ajouter, Enlever ou Editer les r&eacute;ponses des questions pr&eacute;d&eacute;finies (&agrave; l`exception des questions &agrave; r&eacute;ponses multiples)</li><li>Changer le nom du Questionnaire ou sa description</li></ul>");
define("_AC_ACTIVATE_MESSAGE4", "Une fois que les donn&eacute;es sont saisies dans votre Questionnaire, si vous voulez ajouter ou enlever des groupes ou questions, vous devez d&eacute;sactiver ce questionnaire, ce qui d&eacute;placera toutes les donn&eacute;es qui ont d&eacute;j&agrave; &eacute;t&eacute; saisies dans une table archiv&eacute;e s&eacute;par&eacute;e.");
define("_AC_ACTIVATE", "Activer");
define("_AC_ACTIVATED", "Le Questionnaire a &eacute;t&eacute; activ&eacute;. La table r&eacute;sultat a &eacute;t&eacute; cr&eacute;e avec succ&eacute;s.");
define("_AC_NOTACTIVATED", "Le Questionnaire ne peut pas être activ&eacute;.");
define("_AC_NOTPRIVATE", "Ce n`est pas un questionnaire anonyme. Une table Invitation doit donc être cr&eacute;e.");
define("_AC_REGISTRATION", "This survey allows public registration. A token table must also be created."); //New for 0.98finalRC1
define("_AC_CREATETOKENS", "Initialiser les Invitations");
define("_AC_SURVEYACTIVE", "Ce questionnaire est maintenant activ&eacute;, et les r&eacute;ponses peuvent être enregistr&eacute;es.");
define("_AC_DEACTIVATE_MESSAGE1", "Dans un questionnaire activ&eacute;, une table est cr&eacute;e pour stocker toutes les donn&eacute;es saisies enregistr&eacute;es.");
define("_AC_DEACTIVATE_MESSAGE2", "Lorsque vous d&eacute;sactivez un questionnaire, toutes les donn&eacute;es saisies dans la table original seront d&eacute;plac&eacute;es ailleurs, ainsi lorsque vous r&eacute;activerez le questionnaire la table sera vide. Vous ne pourrez plus acc&eacute;der &agrave; ces donn&eacute;es avec PHPSurveyor.");
define("_AC_DEACTIVATE_MESSAGE3", "Seul un administrateur syst&eacute;me peut acc&eacute;der aux donn&eacute;es d`un questionnaire d&eacute;sactiv&eacute; en utilisant un gestionnaire de bases de donn&eacute;es MySQL tel que phpmyadmin par exemple. Si votre questionnaire utilise des Invitations, cette table sera &eacute;galement renomm&eacute;e et seul un administrateur syst&eacute;me y aura acc&eacute;s.");
define("_AC_DEACTIVATE_MESSAGE4", "Votre table de r&eacute;ponse sera renomm&eacute;e en:");
define("_AC_DEACTIVATE_MESSAGE5", "Vous devriez exporter vos r&eacute;ponses avant de d&eacute;sactiver. Cliquer \"Annuler\" pour retourner &agrave; l`&eacute;cran principal d`administration sans d&eacute;sactiver ce questionnaire.");
define("_AC_DEACTIVATE", "D&eacute;sactiver");
define("_AC_DEACTIVATED_MESSAGE1", "La table r&eacute;ponses a &eacute;t&eacute; renomm&eacute;e en: ");
define("_AC_DEACTIVATED_MESSAGE2", "Les r&eacute;ponses &agrave; ce questionnaire ne sont plus disponibles via PHPSurveyor.");
define("_AC_DEACTIVATED_MESSAGE3", "Vous devriez noter le nom de cette table dans le cas où vous auriez besoin d`y acc&eacute;der ult&eacute;rieurement.");
define("_AC_DEACTIVATED_MESSAGE4", "La table d`Invitations li&eacute;e &agrave; ce questionnaire a &eacute;t&eacute; renomm&eacute;e en: ");

//CHECKFIELDS
define("_CF_CHECKTABLES", "V&eacute;rification pour s`assurer qut toute les tables existent");
define("_CF_CHECKFIELDS", "V&eacute;rification pour s`assurer que tous les champs existent");
define("_CF_CHECKING", "V&eacute;rification");
define("_CF_TABLECREATED", "Table Cr&eacute;e");
define("_CF_FIELDCREATED", "Champ Cr&eacute;e");
define("_CF_OK", "OK");
define("_CFT_PROBLEM", "Il semble que quelques tables ou champs soient absents de votre base de donn&eacute;es.");

//CREATE DATABASE (createdb.php)
define("_CD_DBCREATED", "Base de donn&eacute;es cr&eacute;e.");
define("_CD_POPULATE_MESSAGE", "Veuillez cliquer ci-dessous pour peupler la base de donn&eacute;es");
define("_CD_POPULATE", "Peupler la base de donn&eacute;es");
define("_CD_NOCREATE", "Impossible de cr&eacute;er la base de donn&eacute;es");
define("_CD_NODBNAME", "Les informations de la Base de donn&eacute;es ne sont pas fournies. Ce script doit être &eacute;x&eacute;cut&eacute; &agrave; partir d`admin.php seulement.");

//DATABASE MODIFICATION MESSAGES
define("_DB_FAIL_GROUPNAME", "Le Groupe ne peut pas être ajout&eacute;:Nom du groupe obligatoire absent.");
define("_DB_FAIL_GROUPUPDATE", "Le Groupe ne peut pas être mis &agrave; jour");
define("_DB_FAIL_GROUPDELETE", "Le Groupe ne peut pas être supprimer");
define("_DB_FAIL_NEWQUESTION", "La Question ne peut pas être cr&eacute;e.");
define("_DB_FAIL_QUESTIONTYPECONDITIONS", "La Question ne peut pas être mise &agrave; jour. Il y a des conditions pour d`autres questions qui se fondent sur les r&eacute;ponses &agrave; cette question et changer le type poserait des probl&egrave;mes. Vous devez supprimer ces conditions avant de pouvoir changer le type de cette question.");
define("_DB_FAIL_QUESTIONUPDATE", "La Question ne peut pas être mise &agrave; jour");
define("_DB_FAIL_QUESTIONDELCONDITIONS", "La Question ne peut pas être supprim&eacute;e. qui se fondent sur cette question.  Vous ne pouvez pas supprimer cette question jusqu`&agrave; ce que ces conditions soient enlev&eacute;es");
define("_DB_FAIL_QUESTIONDELETE", "La Question ne peut pas être supprim&eacute;e");
define("_DB_FAIL_NEWANSWERMISSING", "La R&eacute;ponse ne peut pas être ajout&eacute;e. Vous devez inclure un code et une r&eacute;ponse");
define("_DB_FAIL_NEWANSWERDUPLICATE", "La R&eacute;ponse ne peut pas être ajout&eacute;e. Il y a d&eacute;j&agrave; une r&eacute;ponse avec ce code");
define("_DB_FAIL_ANSWERUPDATEMISSING", "La R&eacute;ponse ne peut pas être mise &agrave; jour. Vous devez inclure un code et une r&eacute;ponse");
define("_DB_FAIL_ANSWERUPDATEDUPLICATE", "La R&eacute;ponse ne peut pas être mise &agrave; jour. Il y a d&eacute;j&agrave; une r&eacute;ponse avec ce code");
define("_DB_FAIL_ANSWERUPDATECONDITIONS", "La R&eacute;ponse ne peut pas être mise &agrave; jour. Vous avez modifi&eacute; le code de r&eacute;ponse, mais il y a des conditions &agrave; d`autres questions qui d&eacute;pendent de l`ancien code de r&eacute;ponse de cette question.  Vous devez supprimer ces conditions avant de pouvoir modifier le code de cette r&eacute;ponse.");
define("_DB_FAIL_ANSWERDELCONDITIONS", "La R&eacute;ponse ne peut pas être supprim&eacute;e. Il y a des conditions pour d`autres questions qui se fondent sur cette r&eacute;ponse.  Vous ne pouvez pas supprimer cette r&eacute;ponse jusqu`&agrave; ce que ces conditions soient enlev&eacute;es");
define("_DB_FAIL_NEWSURVEY_TITLE", "Le questionnaire ne peut pas être cr&eacute;e parce qu`il n`a pas de titre court");
define("_DB_FAIL_NEWSURVEY", "Le questionnaire ne peut pas être cr&eacute;e");
define("_DB_FAIL_SURVEYUPDATE", "Le questionnaire ne peut pas être mis &agrave; jour");
define("_DB_FAIL_SURVEYDELETE", "Le questionnaire ne peut pas être supprim&eacute;");

//DELETE SURVEY MESSAGES
define("_DS_NOSID", "Vous n`avez pas s&eacute;lectionn&eacute; de questionnaire &agrave; supprimer");
define("_DS_DELMESSAGE1", "Vous êtes sur le point de supprimer ce questionnaire");
define("_DS_DELMESSAGE2", "Cette proc&eacute;dure supprimera ce questionnaire, tous les groupes associ&eacute;s, les r&eacute;ponses des Questions ainsi que les conditions.");
define("_DS_DELMESSAGE3", "Il est recommand&eacute; avant de supprimer ce questionnaire d`exporter enti&eacute;rement ce questionnaire &agrave; partir de l`&eacute;cran principal d`administration.");
define("_DS_SURVEYACTIVE", "Ce questionnaire est activ&eacute; et une table des r&eacute;ponses existe. Si vous supprimez ce questionnaire, ces r&eacute;ponses seront supprim&eacute;es. Il est recommand&eacute; d`exporter les r&eacute;ponses les r&eacute;ponses avant de supprimer ce questionnaire.");
define("_DS_SURVEYTOKENS", "Ce questionnaire a une table d`invitation associ&eacute;e. Si vous supprimez ce questionnaire cette table d`invitations sera supprim&eacute;e. Il est recommand&eacute; d`exporter ou faire une une sauvegarde de ces invitations avant de supprimer ce questionnaire.");
define("_DS_DELETED", "Ce questionnaire a &eacute;t&eacute; supprim&eacute;.");

//DELETE QUESTION AND GROUP MESSAGES
define("_DG_RUSURE", "Supprimer ce groupe supprimera &eacute;galement toute les questions et r&eacute;ponses qu`il contient. Etes-vous sûr de vouloir continuer?"); //New for 098rc5
define("_DQ_RUSURE", "Supprimer cette question supprimera &eacute;galement toutes les r&eacute;ponses qu`elle inclut. Etes-vous sûr de vouloir continuer"); //New for 098rc5

//EXPORT MESSAGES
define("_EQ_NOQID", "Aucun QID n`a &eacute;t&eacute; fourni. Impossible de vider la question.");
define("_ES_NOSID", "Aucun QID n`a &eacute;t&eacute; fourni. Impossible de vider le questionnaire");

//EXPORT RESULTS
define("_EX_FROMSTATS", "Filtr&eacute; par le script des statistiques");
define("_EX_HEADINGS", "Questions");
define("_EX_ANSWERS", "R&eacute;ponses");
define("_EX_FORMAT", "Format");
define("_EX_HEAD_ABBREV", "Entête  abr&eacute;g&eacute;s");
define("_EX_HEAD_FULL", "Entête complet");
define("_EX_ANS_ABBREV", "Codes de R&eacute;ponse");
define("_EX_ANS_FULL", "R&eacute;ponses compl&eacute;te (full answers)");
define("_EX_FORM_WORD", "Microsoft Word");
define("_EX_FORM_EXCEL", "Microsoft Excel");
define("_EX_FORM_CSV", "CSV-Texte (s&eacute;parateur: virgule)");
define("_EX_EXPORTDATA", "Exporter les donn&eacute;es");
define("_EX_COLCONTROLS", "Titre de la colonne(Column Control)"); //New for 0.98rc7
define("_EX_TOKENCONTROLS", "Contr&ocirc;le Invitation"); //New for 0.98rc7
define("_EX_COLSELECT", "Choisir les colonnes"); //New for 0.98rc7
define("_EX_COLOK", "Choisir les colonnes que vous voulez exporter.Ne rien S&eacute;lectionner pour exporter toute les colonnes."); //New for 0.98rc7
define("_EX_COLNOTOK", "Votre questionnaire contient plus de 255 colonnes de r&eacute;ponses. Les tableurs comme Excel sont limit&eacute;s &agrave; 255. S&eacute;lectionner les colonnes &agrave; exporter dans la liste ci-dessous.."); //New for 0.98rc7
define("_EX_TOKENMESSAGE", "Votre questionnaire peut exporter les donn&eacute;es des Invitations associ&eacute;s avec chaque r&eacute;ponse. S&eacute;lectionnez tous les champs additionnels que vous voudriez exporter."); //New for 0.98rc7
define("_EX_TOKSELECT", "Choisir les Champs d`Invitation"); //New for 0.98rc7

//IMPORT SURVEY MESSAGES
define("_IS_FAILUPLOAD", "Une erreur s`est produite durant la transmission de votre fichier.  Ceci peut être provoqu&eacute; par des permissions incorrectes dans votre dossier admin.");
define("_IS_OKUPLOAD", "Fichier transmis avec succ&eacute;s.");
define("_IS_READFILE", "Lecture du fichier..");
define("_IS_WRONGFILE", "Ce fichier n`est pas fichier de questionnaire PHPSurveyor. L`importation a &eacute;chou&eacute;.");
define("_IS_IMPORTSUMMARY", "Sommaire de l`importation du questionnaire");
define("_IS_SUCCESS", "L`importation du questionnaire est termin&eacute;e.");
define("_IS_IMPFAILED", "L`importation de ce fichier questionnaire a &eacute;chou&eacute;");
define("_IS_FILEFAILS", "Mauvais format de donn&eacute;es dans le fichier de donn&eacute;es PHPSurveyor.");

//IMPORT GROUP MESSAGES
define("_IG_IMPORTSUMMARY", "Sommaire de l`importation de Groupe");
define("_IG_SUCCESS", "L`importation du groupe est termin&eacute;e.");
define("_IG_IMPFAILED", "L`importation de ce groupe a &eacute;chou&eacute;");
define("_IG_WRONGFILE", "Ce fichier n`est pas un fichier de groupe PHPSurveyor.L`importation a &eacute;chou&eacute;.");

//IMPORT QUESTION MESSAGES
define("_IQ_NOSID", "Aucun SID (Questionnaire) n`a &eacute;t&eacute; fournis. Impossible d`importer une question.");
define("_IQ_NOGID", "Aucun GID (Groupe) n`a &eacute;t&eacute; fournis. Impossible d`importer une question");
define("_IQ_WRONGFILE", "Ce fichier n`est pas un fichier de question PHPSurveyor.L`importation a &eacute;chou&eacute;.");
define("_IQ_IMPORTSUMMARY", "Sommaire de l`importation de question");
define("_IQ_SUCCESS", "L`importation de Question est termin&eacute;e");

//IMPORT LABELSET MESSAGES
define("_IL_DUPLICATE", "There was a duplicate labelset, so this set was not imported. The duplicate will be used instead.");

//BROWSE RESPONSES MESSAGES
define("_BR_NOSID", "Vous n`avez pas S&eacute;lectionn&eacute; de questionnaire &agrave; parcourir.");
define("_BR_NOTACTIVATED", "Ce questionnaire n`a pas &eacute;t&eacute; activ&eacute;. Aucun r&eacute;sultats &agrave; parcourir.");
define("_BR_NOSURVEY", "Il n`y a pas de questionnaire associ&eacute;.");
define("_BR_EDITRESPONSE", "Editer cette saisie (entry)");
define("_BR_DELRESPONSE", "Supprimer cette saise");
define("_BR_DISPLAYING", "Enregistrements affich&eacute;s:");
define("_BR_STARTING", "A partir de:");
define("_BR_SHOW", "Afficher");
define("_DR_RUSURE", "Est-vous sûr de vouloir supprimer cette saisie?"); //New for 0.98rc6

//STATISTICS MESSAGES
define("_ST_FILTERSETTINGS", "Param&eacute;tres de Filtre");
define("_ST_VIEWALL", "Visualiser le sommaire de tous les champs disponibles"); //New with 0.98rc8
define("_ST_SHOWRESULTS", "Visualiser les Stats"); //New with 0.98rc8
define("_ST_CLEAR", "Effacer s&eacute;lection"); //New with 0.98rc8
define("_ST_RESPONECONT", "R&eacute;ponses Contenant"); //New with 0.98rc8
define("_ST_NOGREATERTHAN", "Nombre sup&eacute;rieur que"); //New with 0.98rc8
define("_ST_NOLESSTHAN", "Nombre inf&eacute;rieur &agrave;"); //New with 0.98rc8
define("_ST_DATEEQUALS", "Date (AAAA-MM-JJ) &eacute;gale"); //New with 0.98rc8
define("_ST_ORBETWEEN", "OU entre"); //New with 0.98rc8
define("_ST_RESULTS", "Resultats"); //New with 0.98rc8 (Plural)
define("_ST_RESULT", "Resultat"); //New with 0.98rc8 (Singular)
define("_ST_RECORDSRETURNED", "Aucun enregistrement dans cette requête"); //New with 0.98rc8
define("_ST_TOTALRECORDS", "Nombre d`Enregistrements Total dans un questionnaire"); //New with 0.98rc8
define("_ST_PERCENTAGE", "Pourcentage du total"); //New with 0.98rc8
define("_ST_FIELDSUMMARY", "Sommaire de champs pour"); //New with 0.98rc8
define("_ST_CALCULATION", "Calcul"); //New with 0.98rc8
define("_ST_SUM", "Somme"); //New with 0.98rc8 - Mathematical
define("_ST_STDEV", "Écart type"); //New with 0.98rc8 - Mathematical
define("_ST_AVERAGE", "Moyenne"); //New with 0.98rc8 - Mathematical
define("_ST_MIN", "Minimum"); //New with 0.98rc8 - Mathematical
define("_ST_MAX", "Maximum"); //New with 0.98rc8 - Mathematical
define("_ST_Q1", "1er Quartile (Q1)"); //New with 0.98rc8 - Mathematical
define("_ST_Q2", "2&egrave;me Quartile (Median)"); //New with 0.98rc8 - Mathematical
define("_ST_Q3", "3&egrave;me Quartile (Q3)"); //New with 0.98rc8 - Mathematical
define("_ST_NULLIGNORED", "*Des valeurs nulles sont ignor&eacute;es dans les calculs"); //New with 0.98rc8
define("_ST_QUARTMETHOD", "*Q1 and Q3 a &eacute;t&eacute; calcul&eacute; avec <a href=`http://mathforum.org/library/drmath/view/60969.html` target=`_blank`>minitab method</a>"); //New with 0.98rc8

//DATA ENTRY MESSAGES
define("_DE_NOMODIFY", "Ne peut pas être modifi&eacute;");
define("_DE_UPDATE", "Mettre &agrave; jour la saisie (Entry)");
define("_DE_NOSID", "Vous n`avez pas s&eacute;lectionn&eacute; de questionnaire pour la saisie des donn&eacute;es.");
define("_DE_NOEXIST", "Le questionnaire que vous avez s&eacute;lectionn&eacute; n`&eacute;xiste pas");
define("_DE_NOTACTIVE", "Ce questionnaire n`est pas encore activ&eacute;. Votre r&eacute;ponse ne peut pas être sauvegard&eacute;e");
define("_DE_INSERT", "Insertion de donn&eacute;e");
define("_DE_RECORD", "L`entr&eacute;e &eacute;tait assign&eacute;e &agrave; l`Id de l`Enregistrement suivant: ");
define("_DE_ADDANOTHER", "Ajouter un autre Enregistrement");
define("_DE_VIEWTHISONE", "Visualiser cet Enregistrement");
define("_DE_BROWSE", "Parcourir les R&eacute;ponses");
define("_DE_DELRECORD", "Enregistrement Supprim&eacute;");
define("_DE_UPDATED", "L`Enregistrement a &eacute;t&eacute; mis &agrave; jour.");
define("_DE_EDITING", "Editer une R&eacute;ponse");
define("_DE_QUESTIONHELP", "Aide sur cette question");
define("_DE_CONDITIONHELP1", "R&eacute;pondez seulement &agrave; ceci si les conditions suivantes sont r&eacute;unies:"); 
define("_DE_CONDITIONHELP2", "&agrave; la question {QUESTION}, vous avez r&eacute;pondu {ANSWER}"); //This will be a tricky one depending on your languages syntax. {ANSWER} is replaced with ALL ANSWERS, seperated by _DE_OR (OR).
define("_DE_AND", "ET (AND)");
define("_DE_OR", "OU (OR)");
define("_DE_SAVEENTRY", "Save as a partially completed survey"); //New in 0.99dev01
define("_DE_SAVEID", "Identifier:"); //New in 0.99dev01
define("_DE_SAVEPW", "Password:"); //New in 0.99dev01
define("_DE_SAVEPWCONFIRM", "Confirm Password:"); //New in 0.99dev01
define("_DE_SAVEEMAIL", "Email:"); //New in 0.99dev01

//TOKEN CONTROL MESSAGES
define("_TC_TOTALCOUNT", "Totale d`enregistrements dans cette table Invitation:"); //New in 0.98rc4
define("_TC_NOTOKENCOUNT", "Total sans Invitation Unique:"); //New in 0.98rc4
define("_TC_INVITECOUNT", "Total d`invitations envoy&eacute;es:"); //New in 0.98rc4
define("_TC_COMPLETEDCOUNT", "Total de Questionnaire termin&eacute;s:"); //New in 0.98rc4
define("_TC_NOSID", "Vous n`avez pas s&eacute;lectionn&eacute; de Questionnaire");
define("_TC_DELTOKENS", "Au sujet de la suppression de la table Invitation pour ce questionnaire.");
define("_TC_DELTOKENSINFO", "Si vous supprimez cette table des Invitations ne seront plus requises pour acc&eacute;der &agrave; ce questionnaire. Une sauvegarde de cette table sera effectu&eacute; si vous la supprimez. Votre administrateur syst&eacute;me pourra acc&eacute;der &agrave; cette table.");
define("_TC_DELETETOKENS", "Supprimer Invitations");
define("_TC_TOKENSGONE", "La table d`invitations a &eacute;t&eacute; enlev&eacute;e maintenant et des invitations ne sont plus requises pour acc&eacute;der &agrave; ce questionnaire. Une sauvegarde de cette table a &eacute;t&eacute; effectu&eacute;e. L`administrateur syst&eacute;me pourra y acc&eacute;der.");
define("_TC_NOTINITIALISED", "Aucune invitations n`a &eacute;t&eacute; initialis&eacute;e pour ce questionnaire.");
define("_TC_INITINFO", "Si vous initialisez des invitations pour ce questionnaire, seul les utilisateurs ayant une invitation pourront y acc&eacute;der.");
define("_TC_INITQ", "Voulez-vous cr&eacute;er des invitations pour ce questionnaire??");
define("_TC_INITTOKENS", "Initialiser Invitations");
define("_TC_CREATED", "Une table d`invitation a &eacute;t&eacute; cr&eacute;e pour ce questionnaire.");
define("_TC_DELETEALL", "Supprimer toutes les d`invitations");
define("_TC_DELETEALL_RUSURE", "Etes-vous sur de vouloir supprimer TOUTES les invitations?");
define("_TC_ALLDELETED", "Toutes les invitations ont &eacute;t&eacute; supprim&eacute;es");
define("_TC_CLEARINVITES", "Set all entries to `N` invitation sent");
define("_TC_CLEARINV_RUSURE", "Est-vous sûr de vouloir r&eacute;initialiser tous les enregistrements d`invitation &agrave; NON?");
define("_TC_CLEARTOKENS", "Supprimer tous les nombres uniques des invitations (All unique token numbers)");
define("_TC_CLEARTOKENS_RUSURE", "Etes-vous sûr de vouloir supprimer tous les nombres uniques des invitations?");
define("_TC_TOKENSCLEARED", "Tous les nombres uniques des invitations ont &eacute;t&eacute; enlev&eacute;s");
define("_TC_INVITESCLEARED", "Toute les entr&eacute;s des invitations ont &eacute;t&eacute; d&eacute;finies &agrave; N");
define("_TC_EDIT", "Editer Invitation (Token Entry)");
define("_TC_DEL", "Supprimer Invitation");
define("_TC_DO", "Faire un Questionnaire");
define("_TC_VIEW", "Voir R&eacute;ponse");
define("_TC_INVITET", "Envoyer une invitation par email &agrave; cette entr&eacute;e");
define("_TC_REMINDT", "Envoyer un rappel par email pour cette entr&eacute;e");
define("_TC_INVITESUBJECT", "Invitation pour r&eacute;pondre au questionnaire {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSUBJECT", "Rappel pour r&eacute;pondre au questionnaire {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSTARTAT", "Commencer &agrave; l`IID (TID) No:");
define("_TC_REMINDTID", "envoy&eacute; &agrave;l`IID (TID) No:");
define("_TC_CREATETOKENSINFO", "Cliquer sur Oui va g&eacute;n&eacute;rer des invitations pour ceux de la liste d`invitation qui n`en ont pas reçu. Etes-vous d`accord??");
define("_TC_TOKENSCREATED", "{TOKENCOUNT} invitations ont &eacute;t&eacute; cr&eacute;es"); //Leave {TOKENCOUNT} for replacement in script with the number of tokens created
define("_TC_TOKENDELETED", "Une Invitation a &eacute;t&eacute; supprim&eacute;e.");
define("_TC_SORTBY", "Tri par: ");
define("_TC_ADDEDIT", "Ajouter ou Editer une Invitation");
define("_TC_TOKENCREATEINFO", "Vous pouvez laisser cela &agrave; blanc et g&eacute;n&eacute;rer automatiquement des invitations avec `Cr&eacute;er Invitations`");
define("_TC_TOKENADDED", "Ajouter Nouvelle Invitation");
define("_TC_TOKENUPDATED", "Mise &agrave; jour Invitation");
define("_TC_UPLOADINFO", "Le fichier doit être un fichier standard CSV (d&eacute;limiteur: virgule) sans quotes. La premi&eacute;re ligne doit contenir une informations d`en-tête (qui sera enlev&eacute;e). Les donn&eacute;es devront être tri&eacute;es par \"Nom, Pr&eacute;nom, email, [token], [attribute1], [attribute2]\".");
define("_TC_UPLOADFAIL", "Fichier t&eacute;l&eacute;charg&eacute; non trouv&eacute;. V&eacute;rifier vos permissions et le chemin du r&eacute;pertoire de t&eacute;l&eacute;chargement (upload)"); //New for 0.98rc5
define("_TC_IMPORT", "Importation du fichier CSV");
define("_TC_CREATE", "Cr&eacute;ation des Entr&eacute;es des Invitations");
define("_TC_TOKENS_CREATED", "{TOKENCOUNT} Enregistrements cr&eacute;es");
define("_TC_NONETOSEND", "Il n`y avait aucun emails &eacute;ligibles &agrave; envoyer: aucun n`a satisfait les crit&egrave;res - email valide, invitation d&eacute;j&agrave; envoy&eacute;e, Questionnaire d&eacute;j&agrave; complet&eacute; et Invitation obtenue.");
define("_TC_NOREMINDERSTOSEND", "Il n`y avait aucun emails &eacute;ligibles &agrave; envoyer: aucun n`a satisfait les crit&egrave;res - email valide, invitation envoy&eacute;e mais Questionnaire pas encore complet&eacute;.");
define("_TC_NOEMAILTEMPLATE", "Mod&egrave;le d`Invitation non trouv&eacute;. Ce fichier doit exister dans le r&eacute;pertoire  Mod&egrave;le (Template) par d&eacute;faut.");
define("_TC_NOREMINDTEMPLATE", "Mod&egrave;le Rappel non trouv&eacute;. Ce fichier doit exister dans le r&eacute;pertoire  Mod&egrave;le (Template) par d&eacute;faut.");
define("_TC_SENDEMAIL", "Envoyer Invitations");
define("_TC_SENDINGEMAILS", "Envoi Invitations");
define("_TC_SENDINGREMINDERS", "Envoi Rappels");
define("_TC_EMAILSTOGO", "Il y a plus d`email en suspens qui peuvent être envoy&eacute;s en groupe (batch).  Continuez d`envoyer des email en cliquant ci-dessous.");
define("_TC_EMAILSREMAINING", "Il y a encore {EMAILCOUNT} &agrave; envoyer."); //Leave {EMAILCOUNT} for replacement in script by number of emails remaining
define("_TC_SENDREMIND", "Envoyer Rappels");
define("_TC_INVITESENTTO", "Invitation envoy&eacute;e &agrave;:"); //is followed by token name
define("_TC_REMINDSENTTO", "Rappel envoy&eacute; &agrave;:"); //is followed by token name
define("_TC_UPDATEDB", "Mettre &agrave; jour la table d`invitation (Tokens) avec des nouveaux champs"); //New for 0.98rc7
define("_TC_EMAILINVITE_SUBJ", "Invitation to participate in survey"); //New for 0.99dev01
define("_TC_EMAILINVITE", "{FIRSTNAME},\n\nVous avez &eacute;t&eacute; invit&eacute; &agrave; participer &agrave; un questionnaire.\n\n"
                         ."Celui-ci est intitul&eacute;:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
                         ."Pour participer, veuillez cliquer sur le lien ci-dessous.\n\nCordialement,\n\n"
                         ."{ADMINNAME} ({ADMINEMAIL})\n\n"
                         ."----------------------------------------------\n"
                         ."Cliquer ici pour faire le questionnaire:\n"
                         ."{SURVEYURL}"); //New for 0.98rc9 - Email d`Invitation par d&eacute;faut
define("_TC_EMAILREMIND_SUBJ", "Reminder to participate in survey"); //New for 0.99dev01
define("_TC_EMAILREMIND", "{FIRSTNAME},\n\nVous avez &eacute;t&eacute; invit&eacute; &agrave; participer &agrave; un questionnaire r&eacute;cemment.\n\n"
                         ."Nous avons pris en compte que vous n avez pas encore complet&eacute; le questionnaire, et nous vous rappelons que celui-ci est toujours disponible si vous souhaitez participer.\n\n"
                         ."Le questionnaire est intitul&eacute;:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
                         ."Pour participer, veuillez cliquer sur le lien ci-dessous.\n\nCordialement,\n\n"
                         ."{ADMINNAME} ({ADMINEMAIL})\n\n"
                         ."----------------------------------------------\n"
                         ."Cliquez ici pour faire le questionnaire:\n"
                         ."{SURVEYURL}"); //New for 0.98rc9 - Email de rappel par defaut
define("_TC_EMAILREGISTER_SUBJ", "Survey Registration Confirmation"); //New for 0.99dev01
define("_TC_EMAILREGISTER", "{FIRSTNAME},\n\n"
                          ."Vous (ou quelqu un utilisant votre adresse email) êtes enregistr&eacute;s pour "
                          ."participer &agrave; un questionnaire en ligne intitul&eacute;:\n\"{SURVEYNAME}\"\n\n"
                          ."Pour compl&eacute;ter ce questionnaire, cliquez sur le lien suivant:\n\n"
                          ."{SURVEYURL}\n\n"
                          ."Quel que soit votre question &agrave; propos de ce questionnaire, ou si vous "
                          ."ne vous êtes pas enregistr&eacute; pour participer &agrave; celui-ci et croyez qu il s agit "
                          ."d une erreur, veuillez contacter {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9
define("_TC_EMAILCONFIRM_SUBJ", "Confirmation of completed survey"); //New for 0.99dev01
define("_TC_EMAILCONFIRM", "{FIRSTNAME},\n\nCet email vous confirme que vous avez complet&eacute; le questionnaire intitul&eacute; {SURVEYNAME} "
                          ."et votre r&eacute;ponse &agrave; &eacute;t&eacute; enregistr&eacute;e. Merci.\n\n"
                          ."Si vous avez des questions &agrave; propos de cet email, veuillez contacter {ADMINNAME} ({ADMINEMAIL}).\n\n"
                          ."Cordialement,\n\n"
                          ."{ADMINNAME}"); //New for 0.98rc9 - Confirmation Email

//labels.php
define("_LB_NEWSET", "Cr&eacute;er Nouveau Jeu d`Etiquette");
define("_LB_EDITSET", "Editer Jeu d`Etiquette");
define("_LB_FAIL_UPDATESET", "La Mise &agrave; jour du Jeu d`Etiquette a &eacute;chou&eacute;");
define("_LB_FAIL_INSERTSET", "L`Insertion du nouveau Jeu d`Etiquette &agrave; &eacute;chou&eacute;");
define("_LB_FAIL_DELSET", "Impossible de supprimer le Jeu d`Etiquette - Il y a des questions qui y est reli&eacute;. Vous devez supprimer ces questions en premier..");
define("_LB_ACTIVEUSE", "Vous ne pouvez pas changer des codes, ajouter ou supprimer des entr&eacute;es dans ce jeu d`&eacute;tiquettes parce que ceux-ci sont utilis&eacute;s par un questionnaire activ&eacute;.");
define("_LB_TOTALUSE", "Quelques questionnaires utilisent actuellement ce jeu d`&eacute;tiquette. Modifier les codes, ajouter ou supprimer des entr&eacute;es de ce jeu pourrait entrainer des effets ind&eacute;sirables dans d`autres questionnaires.");
//Export Labels
define("_EL_NOLID", "Aucun JID (LID) fournis. Impossible  de vider (Dump) un jeu d`&eacute;tiquette.");
//Import Labels
define("_IL_GOLABELADMIN", "Retour &agrave; l`Administration d`Etiquettes");

//PHPSurveyor System Summary
define("_PS_TITLE", "R&eacute;sum&eacute; syst&egrave;me PHPSurveyor");
define("_PS_DBNAME", "Nom Base de donn&eacute;es");
define("_PS_DEFLANG", "Langage par d&eacute;faut");
define("_PS_CURLANG", "Langage courant");
define("_PS_USERS", "Utilisateurs");
define("_PS_ACTIVESURVEYS", "Questionnaires activ&eacute;s");
define("_PS_DEACTSURVEYS", "D&eacute;sactiver Questionnaires");
define("_PS_ACTIVETOKENS", "Tables d`Invitation (Token) activ&eacute;es");
define("_PS_DEACTTOKENS", "D&eacute;sactiver Tables Invitation");
define("_PS_CHECKDBINTEGRITY", "V&eacute;rifier l`Int&eacute;grit&eacute; Des Donn&eacute;es De PHPSurveyor"); //New with 0.98rc8

//Notification Levels
define("_NT_NONE", "Aucune notification par email"); //New with 098rc5
define("_NT_SINGLE", "Notification par email de base"); //New with 098rc5
define("_NT_RESULTS", "Envoyer notification par email avec des codes r&eacute;sultat"); //New with 098rc5

//CONDITIONS TRANSLATIONS
define("_CD_CONDITIONDESIGNER", "Concepteur De Condition"); //New with 098rc9
define("_CD_ONLYSHOW", "Montrer seulement question {QID} SI (IF)"); //New with 098rc9 - {QID} is repleaced leave there
define("_CD_AND", "ET (AND)"); //New with 098rc9
define("_CD_COPYCONDITIONS", "Copier Conditions"); //New with 098rc9
define("_CD_CONDITION", "Condition"); //New with 098rc9
define("_CD_ADDCONDITION", "Ajouter Condition"); //New with 098rc9
define("_CD_EQUALS", "Egales"); //New with 098rc9
define("_CD_COPYRUSURE", "Etes-vous sûr de vouloir copier ces condition(s) aux questions s&eacute;lectionn&eacute;es?"); //New with 098rc9
define("_CD_NODIRECT", "Vous ne pouvez pas &eacute;xecuter directement ce script."); //New with 098rc9
define("_CD_NOSID", "Vous n`avez pas s&eacute;lectionn&eacute; de Questionnaire."); //New with 098rc9
define("_CD_NOQID", "Vous n`avez pas s&eacute;lectionn&eacute; de Question."); //New with 098rc9
define("_CD_DIDNOTCOPYQ", "Questions non copi&eacute;es"); //New with 098rc9
define("_CD_NOCONDITIONTOCOPY", "Aucune condition &agrave; copier s&eacute;lectionn&eacute;e"); //New with 098rc9
define("_CD_NOQUESTIONTOCOPYTO", "Aucune question s&eacute;lectionn&eacute;e pour copier la condition &agrave;"); //New with 098rc9

//TEMPLATE EDITOR TRANSLATIONS
define("_TP_CREATENEW", "Cr&eacute;er Nouveau Mod&egrave;le"); //New with 098rc9
define("_TP_NEWTEMPLATECALLED", "Cr&eacute;er nouveau mod&egrave;le nomm&eacute;:"); //New with 098rc9
define("_TP_DEFAULTNEWTEMPLATE", "Nouveau Mod&egrave;le"); //New with 098rc9 (default name for new template)
define("_TP_CANMODIFY", "Ce mod&egrave;le peut être modifi&eacute;"); //New with 098rc9
define("_TP_CANNOTMODIFY", "Ce mod&egrave;le ne peut pas être modifi&eacute;"); //New with 098rc9
define("_TP_RENAME", "Renommer ce mod&egrave;le");  //New with 098rc9
define("_TP_RENAMETO", "Renommer ce mod&egrave;le en:"); //New with 098rc9
define("_TP_COPY", "Faire une copie de ce mod&egrave;le");  //New with 098rc9
define("_TP_COPYTO", "Cr&eacute;er une copie de ce mod&egrave;le nomm&eacute;:"); //New with 098rc9
define("_TP_COPYOF", "copie_de_"); //New with 098rc9 (prefix to default copy name)
define("_TP_FILECONTROL", "Contr&ocirc;le Fichier:"); //New with 098rc9
define("_TP_STANDARDFILES", "Fichiers Standards:");  //New with 098rc9
define("_TP_NOWEDITING", "Edition en cours:");  //New with 098rc9
define("_TP_OTHERFILES", "Autres fichiers:"); //New with 098rc9
define("_TP_PREVIEW", "Aperçu:"); //New with 098rc9
define("_TP_DELETEFILE", "Supprimer"); //New with 098rc9
define("_TP_UPLOADFILE", "T&eacute;lecharger (Upload)"); //New with 098rc9
define("_TP_SCREEN", "Ecran:"); //New with 098rc9
define("_TP_WELCOMEPAGE", "Page d`Accueil"); //New with 098rc9
define("_TP_QUESTIONPAGE", "Page de Question"); //New with 098rc9
define("_TP_SUBMITPAGE", "Envoyer Page");
define("_TP_COMPLETEDPAGE", "Page Compl&eacute;t&eacute;e"); //New with 098rc9
define("_TP_CLEARALLPAGE", "Effacer toute la Page"); //New with 098rc9
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
?>