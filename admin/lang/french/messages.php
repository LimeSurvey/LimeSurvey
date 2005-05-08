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
    # Version 1.4.1						                            #
    #                                                               #
    # Version 1.5.0 - updated by SÃ©bastien GAUGRY -                 #
    # note for french translation : for ' use &#146; (ISO code)	    #
    #                                                               #
    #                                                               #
    #                                                               #
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
define("_USERCONTROL", "Controle utilisateur");
define("_ACTIVATE", "Activer le questionnaire");
define("_DEACTIVATE", "D&eacute;sactiver le questionnaire");
define("_CHECKFIELDS", "Contr&ocirc;le des champs de la base de donn&eacute;es");
define("_CREATEDB", "Cr&eacute;er la base de donn&eacute;es");
define("_CREATESURVEY", "Cr&eacute;er le questionnaire"); //New for 0.98rc4
define("_SETUP", "Param&egrave;tres de PHPSurveyor");
define("_DELETESURVEY", "Supprimer le questionnaire");
define("_EXPORTQUESTION", "Exporter la question");
define("_EXPORTSURVEY", "Exporter le questionnaire");
define("_EXPORTLABEL", "Exporter le jeu d&#146;&eacute;tiquettes");
define("_IMPORTQUESTION", "Importer la question");
define("_IMPORTGROUP", "Importer le groupe"); //New for 0.98rc5
define("_IMPORTSURVEY", "Importer le questionnaire");
define("_IMPORTLABEL", "Importer le jeu d&#146;&eacute;tiquettes");
define("_EXPORTRESULTS", "Exporter les r&eacute;ponses");
define("_BROWSERESPONSES", "Parcourir les r&eacute;ponses");
define("_BROWSESAVED", "Parcourir les r&eacute;ponses sauvegard&eacute;es");
define("_STATISTICS", "Statistiques flash");
define("_VIEWRESPONSE", "Voir R&eacute;ponse");
define("_VIEWCONTROL", "Contr&ocirc;le de la visualisation des donn&eacute;es");
define("_DATAENTRY", "Entr&eacute;e donn&eacute;es");
define("_TOKENCONTROL", "Contr&ocirc;le des invitations");
define("_TOKENDBADMIN", "Options d&#146;administration de la base de donn&eacute;es des invitations");
define("_DROPTOKENS", "Suppression de la table des invitations");
define("_EMAILINVITE", "Invitation par mail");
define("_EMAILREMIND", "Rappel par mail");
define("_TOKENIFY", "Cr&eacute;er les invitations");
define("_UPLOADCSV", "Uploader le fichier CSV");
define("_LABELCONTROL", "Administration des jeux d&#146;&eacute;tiquettes"); //NEW with 0.98rc3
define("_LABELSET", "Jeu d&#146;&eacute;tiquettes"); //NEW with 0.98rc3
define("_LABELANS", "Etiquettes"); //NEW with 0.98rc3
define("_OPTIONAL", "Optionnel"); //NEW with 0.98finalRC1

//DROPDOWN HEADINGS
define("_SURVEYS", "Questionnaires");
define("_GROUPS", "Groupes");
define("_QUESTIONS", "Questions");
define("_QBYQ", "Question par question");
define("_GBYG", "Groupe par groupe");
define("_SBYS", "Tout en un");
define("_LABELSETS", "Jeux d&#146;&eacute;tiquettes"); //New with 0.98rc3

//BUTTON MOUSEOVERS
//administration bar
define("_A_HOME_BT", "Page d&#146;administration par d&eacute;faut");
define("_A_SECURITY_BT", "Modifier les param&egrave;tres de S&eacute;curit&eacute;");
define("_A_BADSECURITY_BT", "Activer la s&eacute;curit&eacute;");
define("_A_CHECKDB_BT", "V&eacute;rifier la base de donn&eacute;es");
define("_A_DELETE_BT", "Supprimer tout le questionnaire");
define("_A_ADDSURVEY_BT", "Cr&eacute;er ou importer un nouveau questionnaire");
define("_A_HELP_BT", "Aide");
define("_A_CHECKSETTINGS", "V&eacute;rifier les param&egrave;tres");
define("_A_BACKUPDB_BT", "Sauvegarder enti&eacute;rement la base de donn&eacute;e"); //New for 0.98rc10
define("_A_TEMPLATES_BT", "Editeur de mod&egrave;les"); //New for 0.98rc9
//Survey bar
define("_S_ACTIVE_BT", "Ce questionnaire est actuellement activ&eacute;");
define("_S_INACTIVE_BT", "Ce questionnaire est actuellement d&eacute;sactiv&eacute;");
define("_S_ACTIVATE_BT", "Activer ce questionnaire");
define("_S_DEACTIVATE_BT", "D&eacute;sactiver ce questionnaire");
define("_S_CANNOTACTIVATE_BT", "Impossible d&#146;activer ce questionnaire");
define("_S_DOSURVEY_BT", "Ex&eacute;cuter (tester) le questionnaire");
define("_S_DATAENTRY_BT", "Ecran de saisie de donn&eacute;es pour le questionnaire");
define("_S_PRINTABLE_BT", "Version imprimable du questionnaire");
define("_S_EDIT_BT", "Editer le questionnaire courant");
define("_S_DELETE_BT", "Supprimer le questionnaire courant");
define("_S_EXPORT_BT", "Exporter ce questionnaire");
define("_S_BROWSE_BT", "Parcourir les r&eacute;ponses pour ce questionnaire");
define("_S_TOKENS_BT", "Activer/Editer les invitations pour ce questionnaire");
define("_S_ADDGROUP_BT", "Ajouter un nouveau groupe au questionnaire");
define("_S_MINIMISE_BT", "Masquer les d&eacute;tails de ce questionnaire");
define("_S_MAXIMISE_BT", "Afficher les d&eacute;tails de ce questionnaire");
define("_S_CLOSE_BT", "Fermer ce questionnaire");
define("_S_SAVED_BT", "Voir les r&eacute;ponses enregistr&eacute;es mais non valid&eacute;es"); //New in 0.99dev01
define("_S_ASSESSMENT_BT", "D&eacute;finir des r&eacute;gles d&#146;&eacute;valuation"); //New in  0.99dev01
//Group bar
define("_G_EDIT_BT", "Editer le groupe en cours");
define("_G_EXPORT_BT", "Exporter le groupe en cours"); //New in 0.98rc5
define("_G_DELETE_BT", "Supprimer le groupe en cours");
define("_G_ADDQUESTION_BT", "Ajouter une nouvelle question au groupe");
define("_G_MINIMISE_BT", "Masquer les d&eacute;tails de ce groupe");
define("_G_MAXIMISE_BT", "Afficher les d&eacute;tails de ce groupe");
define("_G_CLOSE_BT", "Fermer ce groupe");
//Question bar
define("_Q_EDIT_BT", "Editer la question en cours");
define("_Q_COPY_BT", "Copier la question en cours"); //New in 0.98rc4
define("_Q_DELETE_BT", "Supprimer la question en cours");
define("_Q_EXPORT_BT", "Exporter cette question");
define("_Q_CONDITIONS_BT", "Affecter des conditions &agrave; question");
define("_Q_ANSWERS_BT", "Editer/Ajouter des r&eacute;ponses pour cette question");
define("_Q_LABELS_BT", "Editer/Ajouter des jeux d&#146;&eacute;tiquettes");
define("_Q_MINIMISE_BT", "Masquer les d&eacute;tails de cette question");
define("_Q_MAXIMISE_BT", "Afficher les d&eacute;tails de cette question");
define("_Q_CLOSE_BT", "Fermer cette question");
//Browse Button Bar
define("_B_ADMIN_BT", "Retourner &agrave; l&#146;cran d&#146;administration du questionnaire");
define("_B_SUMMARY_BT", "Montrer info R&eacute;sum&eacute;");
define("_B_ALL_BT", "Afficher les r&eacute;ponses");
define("_B_LAST_BT", "Afficher les 50 derni&egrave;res r&eacute;ponses");
define("_B_STATISTICS_BT", "Donner les statistiques de ces r&eacute;ponses");
define("_B_EXPORT_BT", "Exporter les r&eacute;sultats vers une application");
define("_B_BACKUP_BT", "Sauvegarder vers un fichier SQL la table de r&eacute;sultats");
//Tokens Button Bar
define("_T_ALL_BT", "Afficher les invitations");
define("_T_ADD_BT", "Ajouter une nouvelle invitation");
define("_T_IMPORT_BT", "Importer les invitations &agrave; partir d&#146;un fichier CSV");
define("_T_EXPORT_BT", "Exporter des Invitations vers un fichier CSV"); //New for 0.98rc7
define("_T_INVITE_BT", "Envoyer une invitation par mail");
define("_T_REMIND_BT", "Envoyer un rappel par mail");
define("_T_TOKENIFY_BT", "G&eacute;n&eacute;rer des invitations");
define("_T_KILL_BT", "Effacer la table des invitations");
//Labels Button Bar
define("_L_ADDSET_BT", "Ajouter un nouveau jeu d&#146;&eacute;tiquettes");
define("_L_EDIT_BT", "Editer un jeu d&#146;&eacute;tiquettes");
define("_L_DEL_BT", "Supprimer un jeu d&#146;eacute;tiquettes");
//Datacontrols
define("_D_BEGIN", "Montrer D&eacute;but...");
define("_D_BACK", "Montrer Pr&eacute;c&eacute;dant...");
define("_D_FORWARD", "Montrer Suivant...");
define("_D_END", "Montrer Fin...");

//DATA LABELS
//surveys
define("_SL_TITLE", "Titre :");
define("_SL_SURVEYURL", "URL du questionnaire :"); //new in 0.98rc5
define("_SL_DESCRIPTION", "Description :");
define("_SL_WELCOME", "Message de bienvenue :");
define("_SL_ADMIN", "Administrateur :");
define("_SL_EMAIL", "Mail de l&#146;administrateur :");
define("_SL_FAXTO", "Fax &agrave; :");
define("_SL_ANONYMOUS", "Anonyme :");
define("_SL_EXPIRES", "Date limite de r&eacute;ponse :");
define("_SL_FORMAT", "Format :");
define("_SL_DATESTAMP", "R&eacute;ponses dat&eacute;es :");
define("_SL_TEMPLATE", "Mod&egrave;le :");
define("_SL_LANGUAGE", "Langue :");
define("_SL_LINK", "Lien :");
define("_SL_URL", "URL de fin :");
define("_SL_URLDESCRIP", "Description de l&#146;URL :");
define("_SL_STATUS", "Status :");
define("_SL_SELSQL", "S&eacute;lectionner un fichier SQL :");
define("_SL_USECOOKIES", "Utiliser des cookies ?"); //NEW with 098rc3
define("_SL_NOTIFICATION", "Notification :"); //New with 098rc5
define("_SL_ALLOWREGISTER", "Permettre l&#146;enregistrement public ?"); //New with 0.98rc9
define("_SL_ATTRIBUTENAMES", "Nom Attribu&eacute; &agrave; l&#146;invitation :"); //New with 0.98rc9
define("_SL_EMAILINVITE_SUBJ", "Objet du mail d&#146;invitation :"); //New with 0.99dev01
define("_SL_EMAILINVITE", "Invitation par mail :"); //New with 0.98rc9
define("_SL_EMAILREMIND_SUBJ", "Objet du mail de rappel :"); //New with 0.99dev01
define("_SL_EMAILREMIND", "Rappel par mail :"); //New with 0.98rc9
define("_SL_EMAILREGISTER_SUBJ", "Objet du mail d&#146;enregistrement public :"); //New with 0.99dev01
define("_SL_EMAILREGISTER", "Enregistrement du mail public :"); //New with 0.98rc9
define("_SL_EMAILCONFIRM_SUBJ", "Objet du mail de confirmation :"); //New with 0.99dev01
define("_SL_EMAILCONFIRM", "Confirmation par mail"); //New with 0.98rc9
define("_SL_REPLACEOK", "Cela remplacera le texte existant. Continuer ?"); //New with 0.98rc9
define("_SL_ALLOWSAVE", "Autoriser les sauvegardes ?"); //New with 0.99dev01
define("_SL_AUTONUMBER", "D&eacute;marrer la num&eacute;rotation des ID &agrave; :"); //New with 0.99dev01
define("_SL_AUTORELOAD", "Charger automatiquement l&#146;URL quand le questionnaire est termin&eacute; ?"); //New with 0.99dev01
define("_SL_ALLOWPREV", "Montrer le bouton [<< Pr&eacute;c]"); //New with 0.99dev01
define("_SL_USE_DEFAULT","Utiliser les d&eacute;fauts");
define("_SL_UPD_SURVEY","Mise &agrave; jour du questionnaire");

//groups
define("_GL_TITLE", "Titre :");
define("_GL_DESCRIPTION", "Description :");
define("_GL_EDITGROUP","Edition du groupe pour le questionnaire ID"); // New with 0.99dev02
define("_GL_UPDATEGROUP","Mise &agrave; jour du groupe"); // New with 0.99dev02
//questions
define("_QL_EDITQUESTION", "Editer la question");// New with 0.99dev02
define("_QL_UPDATEQUESTION", "Mise &agrave; jour de la question");// New with 0.99dev02
define("_QL_CODE", "Code :");
define("_QL_QUESTION", "Question :");
define("_QL_VALIDATION", "Validation :"); //New in VALIDATION VERSION
define("_QL_HELP", "Aide :");
define("_QL_TYPE", "Type :");
define("_QL_GROUP", "Groupe :");
define("_QL_MANDATORY", "Obligatoire :");
define("_QL_OTHER", "Autre :");
define("_QL_LABELSET", "Jeu d&#146;&eacute;tiquettes :");
define("_QL_COPYANS", "Copier les R&eacute;ponses ?"); //New in 0.98rc3
define("_QL_QUESTIONATTRIBUTES", "Attributs de la question :"); //New in 0.99dev01
define("_QL_COPYATT", "Copier les attributs ?"); //New in 0.99dev01
//answers
define("_AL_CODE", "Code");
define("_AL_ANSWER", "R&eacute;ponse");
define("_AL_DEFAULT", "D&eacute;faut");
define("_AL_MOVE", "D&eacute;placer");
define("_AL_ACTION", "Action");
define("_AL_UP", "Haut");
define("_AL_DN", "Bas");
define("_AL_SAVE", "Sauver");
define("_AL_DEL", "Supprimer");
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
define("_UL_TURNOFF", "D&eacute;sactiver la protection");
//tokens
define("_TL_FIRST", "Pr&eacute;nom");
define("_TL_LAST", "Nom");
define("_TL_EMAIL", "Mail");
define("_TL_TOKEN", "Invitation");
define("_TL_INVITE", "Envoyer l&#146;invitation ?");
define("_TL_DONE", "Complet ?");
define("_TL_ACTION", "Actions");
define("_TL_ATTR1", "Att_1"); //New for 0.98rc7
define("_TL_ATTR2", "Att_2"); //New for 0.98rc7
define("_TL_MPID", "MPID"); //New for 0.98rc7
//labels
define("_LL_NAME", "Nom du jeu"); //NEW with 098rc3
define("_LL_CODE", "Code"); //NEW with 098rc3
define("_LL_ANSWER", "Titre"); //NEW with 098rc3
define("_LL_SORTORDER", "Commander"); //NEW with 098rc3
define("_LL_ACTION", "Action"); //New with 098rc3

//QUESTION TYPES
define("_5PT", "Alignement de 5 boutons radio");
define("_DATE", "Date");
define("_GENDER", "Genre");
define("_LIST", "Bouton radio");
define("_LIST_DROPDOWN", "Liste d&eacute;roulante"); //New with 0.99dev01
define("_LISTWC", "Liste d&eacute;roulante avec commentaires");
define("_MULTO", "Case &agrave; cocher");
define("_MULTOC", "Case &agrave; cocher avec commentaires");
define("_MULTITEXT", "Zones de texte court");
define("_NUMERICAL", "Entr&eacute;e num&eacute;rique");
define("_RANK", "Classement");
define("_STEXT", "Texte libre court");
define("_LTEXT", "Zone de commentaires");
define("_HTEXT", "Enorme zone de texte"); //New with 0.99dev01
define("_YESNO", "Oui/Non");
define("_ARR5", "Ligne de 5 boutons radio");
define("_ARR10", "Ligne de 10 boutons radio");
define("_ARRYN", "Ligne (Oui/Non/Indiff&eacute;rent)");
define("_ARRMV", "Ligne (Augmenter, Sans changement, Diminuer)");
define("_ARRFL", "Ligne de boutons radio (Etiquettes personnalis&eacute;es)"); //Release 0.98rc3
define("_ARRFLC", "Ligne de boutons radio (Etiquettes personnalis&eacute;es en colonne"); //Release 0.98rc8
define("_SINFL", "Simple (Etiquettes personnalis&eacute;es)"); //(FOR LATER RELEASE)
define("_EMAIL", "Adresse mail"); //FOR LATER RELEASE
define("_BOILERPLATE", "Texte fixe"); //New in 0.98rc6
define("_LISTFL_DROPDOWN", "Liste (Flexible Labels) (Dropdown)"); //New in 0.99dev01
define("_LISTFL_RADIO", "Liste (Flexible Labels) (Radio)"); //New in 0.99dev01

//GENERAL WORDS AND PHRASES
define("_AD_YES", "Oui");
define("_AD_NO", "Non");
define("_AD_CANCEL", "Annuler");
define("_AD_CHOOSE", "S&eacute;lectionner...");
define("_AD_OR", "OU"); //New in 0.98rc4
define("_ERROR", "Erreur");
define("_SUCCESS", "Succ&eacute;s");
define("_REQ", "*Requis");
define("_ADDS", "Ajouter un questionnaire");
define("_ADDG", "Ajouter un groupe");
define("_ADDQ", "Ajouter une question");
define("_ADDA", "Ajouter une r&eacute;ponse"); //New in 0.98rc4
define("_COPYQ", "Copier une question"); //New in 0.98rc4
define("_ADDU", "Ajouter un utilisateur");
define("_SEARCH", "Chercher"); //New in 0.98rc4
define("_SAVE", "Sauver les modifications");
define("_NONE", "Rien"); //as in "Do not display anything", "or none chosen";
define("_GO_ADMIN", "Ecran principal d&#146;administration"); //text to display to return/display main administration screen
define("_CONTINUE", "Continuer");
define("_WARNING", "Avertissement");
define("_USERNAME", "Nom d&#146;utilisateur");
define("_PASSWORD", "Mot de Passe");
define("_DELETE", "Supprimer");
define("_CLOSEWIN", "Fermer la fen&ecirc;tre");
define("_TOKEN", "Invitation");
define("_DATESTAMP", "D&eacute;lai de r&eacute;ponse"); //Referring to the datestamp or time response submitted
define("_COMMENT", "Commentaire");
define("_FROM", "De"); //For emails
define("_SUBJECT", "Objet"); //For emails
define("_MESSAGE", "Message"); //For emails
define("_RELOADING", "Actualisation de l&#146;&eacute;cran. Veuillez patienter.");
define("_ADD", "Ajouter");
define("_UPDATE", "Mise &agrave; jour");
define("_BROWSE", "Parcourir"); //New in 098rc5
define("_AND", "et"); //New with 0.98rc8
define("_SQL", "SQL"); //New with 0.98rc8
define("_PERCENTAGE", "Pourcentage"); //New with 0.98rc8
define("_COUNT", "D&eacute;compte"); //New with 0.98rc8

//SURVEY STATUS MESSAGES (new in 0.98rc3)
define("_SS_NOGROUPS", "Nombre de groupes dans le questionnaire :"); //NEW for release 0.98rc3
define("_SS_NOQUESTS", "Nombre de questions dans le questionnaire :"); //NEW for release 0.98rc3
define("_SS_ANONYMOUS", "Ce questionnaire est anonyme."); //NEW for release 0.98rc3
define("_SS_TRACKED", "Ce questionnaire n&#146;EST PAS anonyme."); //NEW for release 0.98rc3
define("_SS_DATESTAMPED", "Les R&eacute;ponses seront dat&eacute;es"); //NEW for release 0.98rc3
define("_SS_COOKIES", "Utilisation des Cookies pour le contr&ocirc;le d&#146;Acc&eacute;es."); //NEW for release 0.98rc3
define("_SS_QBYQ", "Pr&eacute;sentation : une question par page."); //NEW for release 0.98rc3
define("_SS_GBYG", "Pr&eacute;sentation : un groupe de questions par page."); //NEW for release 0.98rc3
define("_SS_SBYS", "Pr&eacute;sentation : une page simple."); //NEW for release 0.98rc3
define("_SS_ACTIVE", "Questionnaire en cours (actif)."); //NEW for release 0.98rc3
define("_SS_NOTACTIVE", "Questionnaire inactif."); //NEW for release 0.98rc3
define("_SS_SURVEYTABLE", "Nom de la table du questionnaire :"); //NEW for release 0.98rc3
define("_SS_CANNOTACTIVATE", "Impossible d&#146;activer le questionnaire maintenant."); //NEW for release 0.98rc3
define("_SS_ADDGROUPS", "Vous devez ajouter des groupes"); //NEW for release 0.98rc3
define("_SS_ADDQUESTS", "Vous devez ajouter des questions"); //NEW for release 0.98rc3
define("_SS_ALLOWREGISTER", "Si les invitations sont utilis&eacute;es, les destinataires doivent être enregistr&eacute;s pour ce questionnaire"); //NEW for release 0.98rc9
define("_SS_ALLOWSAVE", "Les participants peuvent sauvegarder un remplissage partiel du questionnaire"); //NEW for release 0.99dev01

//QUESTION STATUS MESSAGES (new in 0.98rc4)
define("_QS_MANDATORY", "Question obligatoire"); //New for release 0.98rc4
define("_QS_OPTIONAL", "Question optionnelle"); //New for release 0.98rc4
define("_QS_NOANSWERS", "Vous devez ajouter des r&eacute;ponses &agrave; cette question"); //New for release 0.98rc4
define("_QS_NOLID", "Vous devez choisir un jeu d&#146;&eacute;tiquettes pour cette question"); //New for release 0.98rc4
define("_QS_COPYINFO", "Note : vous devez OBLIGATOIREMENT saisir un nouveau code pour la question"); //New for release 0.98rc4

//General Setup Messages
define("_ST_NODB1", "La base de donn&eacute;es du questionnaire d&eacute;finit n&#146;existe pas");
define("_ST_NODB2", "Soit votre base de donn&eacute;es n&#146;a pas &eacute;t&eacute; cr&eacute;e, soit il y a un probl&eacute;me pour y acc&eacute;der.");
define("_ST_NODB3", "PHPSurveyor peut tenter de cr&eacute;er la base de donn&eacute;es pour vous.");
define("_ST_NODB4", "Le nom de votre base de donn&eacute;es S&eacute;lectionn&eacute;e est :");
define("_ST_CREATEDB", "Cr&eacute;er la base de donn&eacute;es");

//USER CONTROL MESSAGES
define("_UC_CREATE", "Cr&eacute;er le fichier htaccess par d&eacute;faut");
define("_UC_NOCREATE", "Impossible de cr&eacute;er le fichier htaccess. V&eacute;rifiez votre config.php sous \$homedir, et que vous avez les permissions d&#146;&eacute;criture dans le bon r&eacute;pertoire.");
define("_UC_SEC_DONE", "Le niveau de s&eacute;curit&eacute; est maintenant configur&eacute; !");
define("_UC_CREATE_DEFAULT", "Cr&eacute;er les utilisateurs par d&eacute;faut");
define("_UC_UPDATE_TABLE", "Mise &agrave; jour de la table des utilisateurs (users)");
define("_UC_HTPASSWD_ERROR", "Une erreur s&#146;est produite lors de la cr&eacute;ation du fichier htpasswd");
define("_UC_HTPASSWD_EXPLAIN", "Si vous utilisez un serveur Windows il est recommand&eacute; de copier le fichier apache sous votre r&eacute;pertoire d&#146;administration pour que cette fonction fonctionne correctement. Ce fichier se trouve g&eacute;n&eacute;ralement sous /apache group/apache/bin/");
define("_UC_SEC_REMOVE", "Enlever les param&eacute;tres de s&eacute;curit&eacute;");
define("_UC_ALL_REMOVED", "Les fichiers de permissions, de mot de passe et d&#146;utlisateurs ont &eacute;t&eacute; efface&acute;s");
define("_UC_ADD_USER", "Ajout d&#146;utilisateur");
define("_UC_ADD_MISSING", "Impossible d&#146;ajouter un utilisateur. Le nom d&#146;utilisateur et/ou le mot de passe n&#146;&eacute;taient pas renseign&eacute;s");
define("_UC_DEL_USER", "Supprimer l&#146;utilisateur");
define("_UC_DEL_MISSING", "Impossible de supprimer l&#146;utilisateur. Le nom d&#146;utilisateur n&#146;&eacute;tait pas rempli.");
define("_UC_MOD_USER", "Modification de l&#146;utilisateur");
define("_UC_MOD_MISSING", "Impossible de modifier l&#146;utilisateur. Le nom d&#146;utilisateur et/ou le mot de passe n&#146;&eacute;taient pas renseign&eacute;s");
define("_UC_TURNON_MESSAGE1", "Vous n&#146;avez pas encore initialis&eacute;s les param&eacute;tres de s&eacute;curit&eacute; pour votre syst&eacute;me de questionnaire et en cons&eacute;quence il n&#146;y a pas de restrictions d`acc&eacute;s.</p>\nSi vous cliquez sur le bouton `Initialiser la S&eacute;curit&eacute;` ci-dessous, les param&eacute;tres de s&eacute;curit&eacute; standard d`Apache seront ajout&eacute;s au r&eacute;pertoire d&#146;administration de ce script. Vous aurez alors besoin d&#146;utiliser le nom d&#146;utilisateur et le mot de passe par d&eacute;faut pour acc&eacute;der &agrave; l&#146;administration et aux scripts de saisie de donn&eacute;es.");
define("_UC_TURNON_MESSAGE2", "Il est fortement recommand&eacute;, qu&#146;une fois votre syst&eacute;me de s&eacute;curit&eacute; initialis&eacute;, de changer le mot de passe par d&eacute;faut.");
define("_UC_INITIALISE", "Initialiser la s&eacute;curit&eacute;");
define("_UC_NOUSERS", "Aucun utilisateur dans la table. Nous vous recommandons de `d&eacute;sactiver` la s&eacute;curit&eacute; ET de la `r&eacute;activer` ensuite.");
define("_UC_TURNOFF", "D&eacute;sactiver la s&eacute;curit&eacute;");

//Activate and deactivate messages
define("_AC_MULTI_NOANSWER", "Cette question est &agrave; r&eacute;ponses multiples mais n&#146;a aucune r&eacute;ponse de d&eacute;finie.");
define("_AC_NOTYPE", "Cette question n&#146;a pas de `type` param&eacute;tr&eacute;.");
define("_AC_NOLID", "Un jeu d&#146;&eacute;tiquettes est requis pour cette question. Aucun n&#146;est saisi."); //New for 0.98rc8
define("_AC_CON_OUTOFORDER", "Cette question &agrave; une condition param&eacute;tr&eacute;e, toutefois la condition est bas&eacute;e sur une question qui appara&icirc;t apr&eacute;s elle.");
define("_AC_FAIL", "Le questionnaire n&#146;est pas valid&eacute; par le contr&ocirc;le de coh&eacute;rence");
define("_AC_PROBS", "Le probl&eacute;me suivant a &eacute;t&eacute; rencontr&eacute; :");
define("_AC_CANNOTACTIVATE", "Le questionnaire ne peut pas être activ&eacute; jusqu&#146;&agrave; ce que ces probl&eacute;mes soient r&eacute;solus");
define("_AC_READCAREFULLY", "LIRE CECI ATTENTIVEMENT AVANT DE POURSUIVRE");
define("_AC_ACTIVATE_MESSAGE1", "Vous devriez activer un questionnaire seulement si vous &ecirc;tes absolument certain que votre questionnaire est correctement param&eacute;tr&eacute;/termin&eacute; et n&#146;aura pas besoin d&#146;&ecirc;tre modifi&eacute;.");
define("_AC_ACTIVATE_MESSAGE2", "Un fois qu&#146;un questionnaire est activ&eacute; vous ne pouvez plus :<ul><li>Ajouter ou supprimer des groupes</li><li>Ajouter ou enlever des r&eacute;ponses aux questions &agrave; r&eacute;ponses multiples</li><li>Ajouter ou supprimer des questions</li></ul>");
define("_AC_ACTIVATE_MESSAGE3", "Cependant vous pouvez toujours :<ul><li>Editer (Modifier) les codes de vos questions, le texte ou le type </li><li>Editer (Modifier) les noms de vos Groupes</li><li>Ajouter, Enlever ou Editer les r&eacute;ponses des questions pr&eacute;d&eacute;finies (&agrave; l&#146;exception des questions &agrave; r&eacute;ponses multiples)</li><li>Changer le nom du Questionnaire ou sa description</li></ul>");
define("_AC_ACTIVATE_MESSAGE4", "Une fois que les donn&eacute;es sont saisies dans votre questionnaire, si vous voulez ajouter ou enlever des groupes ou questions, vous devez d&eacute;sactiver ce questionnaire, ce qui d&eacute;placera toutes les donn&eacute;es qui ont d&eacute;j&agrave; &eacute;t&eacute; saisies dans une table archiv&eacute;e s&eacute;par&eacute;e.");
define("_AC_ACTIVATE", "Activer");
define("_AC_ACTIVATED", "Le questionnaire a &eacute;t&eacute; activ&eacute;. La table r&eacute;sultat a &eacute;t&eacute; cr&eacute;e avec succ&eacute;s.");
define("_AC_NOTACTIVATED", "Le questionnaire ne peut pas &ecirc;tre activ&eacute;.");
define("_AC_NOTPRIVATE", "Ce n&#146;est pas un questionnaire anonyme. Une table d&#146;invitation doit donc être cr&eacute;&eacute;e.");
define("_AC_REGISTRATION", "Ce questionnaire permet les enregistrement public. Une table TOKEN doit aussi &ecirc;tre cr&eacute;&eacute;e."); //New for 0.98finalRC1
define("_AC_CREATETOKENS", "Initialiser les invitations");
define("_AC_SURVEYACTIVE", "Ce questionnaire est maintenant activ&eacute;, et les r&eacute;ponses peuvent &ecirc;tre enregistr&eacute;es.");
define("_AC_DEACTIVATE_MESSAGE1", "Dans un questionnaire activ&eacute;, une table est cr&eacute;&eacute;e pour stocker toutes les donn&eacute;es saisies enregistr&eacute;es.");
define("_AC_DEACTIVATE_MESSAGE2", "Lorsque vous d&eacute;sactivez un questionnaire, toutes les donn&eacute;es saisies dans la table originale seront d&eacute;plac&eacute;es ailleurs, ainsi lorsque vous r&eacute;activerez le questionnaire la table sera vide. Vous ne pourrez plus acc&eacute;der &agrave; ces donn&eacute;es avec PHPSurveyor.");
define("_AC_DEACTIVATE_MESSAGE3", "Seul un administrateur syst&eacute;me peut acc&eacute;der aux donn&eacute;es d&#146;un questionnaire d&eacute;sactiv&eacute; en utilisant un gestionnaire de bases de donn&eacute;es MySQL tel que PhpMyAdmin par exemple. Si votre questionnaire utilise des invitations, cette table sera &eacute;galement renomm&eacute;e et seul un administrateur syst&eacute;me y aura acc&eacute;s.");
define("_AC_DEACTIVATE_MESSAGE4", "Votre table de r&eacute;ponse sera renomm&eacute;e en :");
define("_AC_DEACTIVATE_MESSAGE5", "Vous devriez exporter vos r&eacute;ponses avant de d&eacute;sactiver. Cliquez sur \"Annuler\" pour retourner &agrave; l&#146;&eacute;cran principal d&#146;administration sans d&eacute;sactiver ce questionnaire.");
define("_AC_DEACTIVATE", "D&eacute;sactiver");
define("_AC_DEACTIVATED_MESSAGE1", "La table r&eacute;ponses a &eacute;t&eacute; renomm&eacute;e en : ");
define("_AC_DEACTIVATED_MESSAGE2", "Les r&eacute;ponses &agrave; ce questionnaire ne sont plus disponibles via PHPSurveyor.");
;define("_AC_DEACTIVATED_MESSAGE3", "Vous devriez noter le nom de cette table dans le cas o&ugrave; vous auriez besoin dy acc&eacute;der ult&eacute;rieurement.");
define("_AC_DEACTIVATED_MESSAGE4", "La table d&#146invitation li&eacute;e &agrave; ce questionnaire a &eacute;t&eacute; renomm&eacute;e en : ");

//CHECKFIELDS
define("_CF_CHECKTABLES", "V&eacute;rification pour s&#146;assurer que toutes les tables existent");
define("_CF_CHECKFIELDS", "V&eacute;rification pour s&#146;assurer que tous les champs existent");
define("_CF_CHECKING", "V&eacute;rification");
define("_CF_TABLECREATED", "Table cr&eacute;&eacute;e");
define("_CF_FIELDCREATED", "Champ cr&eacute;e");
define("_CF_OK", "OK");
define("_CFT_PROBLEM", "Il semble que quelques tables ou champs soient absents de votre base de donn&eacute;es.");

//CREATE DATABASE (createdb.php)
define("_CD_DBCREATED", "Base de donn&eacute;es cr&eacute;e.");
define("_CD_POPULATE_MESSAGE", "Veuillez cliquer ci-dessous pour peupler la base de donn&eacute;es");
define("_CD_POPULATE", "Peupler la base de donn&eacute;es");
define("_CD_NOCREATE", "Impossible de cr&eacute;er la base de donn&eacute;es");
define("_CD_NODBNAME", "Les informations de la base de donn&eacute;es ne sont pas fournies. Ce script doit être &eacute;x&eacute;cut&eacute; &agrave; partir d&#146;admin.php seulement.");

//DATABASE MODIFICATION MESSAGES
define("_DB_FAIL_GROUPNAME", "Le groupe ne peut pas &ecirc;tre ajout&eacute;: nom du groupe obligatoire absent.");
define("_DB_FAIL_GROUPUPDATE", "Le groupe ne peut pas &ecirc;tre mis &agrave; jour");
define("_DB_FAIL_GROUPDELETE", "Le groupe ne peut pas &ecirc;tre supprim&eacute;");
define("_DB_FAIL_NEWQUESTION", "La question ne peut pas &ecirc;tre cr&eacute;e.");
define("_DB_FAIL_QUESTIONTYPECONDITIONS", "La question ne peut pas &ecirc;tre mise &agrave; jour. Il y a des conditions pour d&#146;autres questions qui se fondent sur les r&eacute;ponses &agrave; cette question et changer le type poserait des probl&egrave;mes. Vous devez supprimer ces conditions avant de pouvoir changer le type de cette question.");
define("_DB_FAIL_QUESTIONUPDATE", "La question ne peut pas &ecirc;tre mise &agrave; jour");
define("_DB_FAIL_QUESTIONDELCONDITIONS", "La question ne peut pas &ecirc;tre supprim&eacute;e. qui se fondent sur cette question.  Vous ne pouvez pas supprimer cette question jusqu&#146;&agrave; ce que ces conditions soient enlev&eacute;es");
define("_DB_FAIL_QUESTIONDELETE", "La question ne peut pas &ecirc;tre supprim&eacute;e");
define("_DB_FAIL_NEWANSWERMISSING", "La r&eacute;ponse ne peut pas &ecirc;tre ajout&eacute;e. Vous devez inclure un code et une r&eacute;ponse");
define("_DB_FAIL_NEWANSWERDUPLICATE", "La r&eacute;ponse ne peut pas &ecirc;tre ajout&eacute;e. Il y a d&eacute;j&agrave; une r&eacute;ponse avec ce code");
define("_DB_FAIL_ANSWERUPDATEMISSING", "La r&eacute;ponse ne peut pas &ecirc;tre mise &agrave; jour. Vous devez inclure un code et une r&eacute;ponse");
define("_DB_FAIL_ANSWERUPDATEDUPLICATE", "La r&eacute;ponse ne peut pas &ecirc;tre mise &agrave; jour. Il y a d&eacute;j&agrave; une r&eacute;ponse avec ce code");
define("_DB_FAIL_ANSWERUPDATECONDITIONS", "La r&eacute;ponse ne peut pas &ecirc;tre mise &agrave; jour. Vous avez modifi&eacute; le code de r&eacute;ponse, mais il y a des conditions &agrave; d&#146;autres questions qui d&eacute;pendent de l&#146;ancien code de r&eacute;ponse de cette question.  Vous devez supprimer ces conditions avant de pouvoir modifier le code de cette r&eacute;ponse.");
define("_DB_FAIL_ANSWERDELCONDITIONS", "La r&eacute;ponse ne peut pas &ecirc;tre supprim&eacute;e. Il y a des conditions pour d&#146;autres questions qui se fondent sur cette r&eacute;ponse.  Vous ne pouvez pas supprimer cette r&eacute;ponse jusqu`&agrave; ce que ces conditions soient enlev&eacute;es");
define("_DB_FAIL_NEWSURVEY_TITLE", "Le questionnaire ne peut pas &ecirc;tre cr&eacute;e parce qu&#146;il n&#146;a pas de titre court");
define("_DB_FAIL_NEWSURVEY", "Le questionnaire ne peut pas &ecirc;tre cr&eacute;e");
define("_DB_FAIL_SURVEYUPDATE", "Le questionnaire ne peut pas &ecirc;tre mis &agrave; jour");
define("_DB_FAIL_SURVEYDELETE", "Le questionnaire ne peut pas &ecirc;tre supprim&eacute;");

//DELETE SURVEY MESSAGES
define("_DS_NOSID", "Vous n&#146;avez pas s&eacute;lectionn&eacute; de questionnaire &agrave; supprimer");
define("_DS_DELMESSAGE1", "Vous &ecirc;tes sur le point de supprimer ce questionnaire");
define("_DS_DELMESSAGE2", "Cette proc&eacute;dure supprimera ce questionnaire, tous les groupes associ&eacute;s, les r&eacute;ponses des questions ainsi que les conditions.");
define("_DS_DELMESSAGE3", "Il est recommand&eacute; avant de supprimer ce questionnaire d&#146;exporter enti&eacute;rement ce questionnaire &agrave; partir de l&#146;&eacute;cran principal d&#146;administration.");
define("_DS_SURVEYACTIVE", "Ce questionnaire est activ&eacute; et une table des r&eacute;ponses existe. Si vous supprimez ce questionnaire, ces r&eacute;ponses seront supprim&eacute;es. Il est recommand&eacute; d&#146;exporter les r&eacute;ponses les r&eacute;ponses avant de supprimer ce questionnaire.");
define("_DS_SURVEYTOKENS", "Ce questionnaire a une table d&#146invitations associ&eacute;e. Si vous supprimez ce questionnaire cette table d&#146;invitations sera supprim&eacute;e. Il est recommand&eacute; d&#146;exporter ou faire une une sauvegarde de ces invitations avant de supprimer ce questionnaire.");
define("_DS_DELETED", "Ce questionnaire a &eacute;t&eacute; supprim&eacute;.");

//DELETE QUESTION AND GROUP MESSAGES
define("_DG_RUSURE", "Supprimer ce groupe supprimera &eacute;galement toute les questions et r&eacute;ponses qu&#146;il contient. Etes-vous s&ucirc;r de vouloir continuer ?"); //New for 098rc5
define("_DQ_RUSURE", "Supprimer cette question supprimera &eacute;galement toutes les r&eacute;ponses qu&#146;elle inclut. Etes-vous s&ucirc;r de vouloir continuer ?"); //New for 098rc5

//EXPORT MESSAGES
define("_EQ_NOQID", "Aucun QID n&#146;a &eacute;t&eacute; fourni. Impossible de vider la question.");
define("_ES_NOSID", "Aucun QID n&#146;a &eacute;t&eacute; fourni. Impossible de vider le questionnaire.");

//EXPORT RESULTS
define("_EX_FROMSTATS", "Filtr&eacute; par le script des statistiques");
define("_EX_HEADINGS", "Questions");
define("_EX_ANSWERS", "R&eacute;ponses");
define("_EX_FORMAT", "Format");
define("_EX_HEAD_ABBREV", "Ent&ecirc;te  abr&eacute;g&eacute;s");
define("_EX_HEAD_FULL", "Ent&ecirc;te complet");
define("_EX_ANS_ABBREV", "Codes de R&eacute;ponse");
define("_EX_ANS_FULL", "R&eacute;ponses compl&eacute;te");
define("_EX_FORM_WORD", "Microsoft Word");
define("_EX_FORM_EXCEL", "Microsoft Excel");
define("_EX_FORM_CSV", "CSV-Texte (s&eacute;parateur : virgule)");
define("_EX_EXPORTDATA", "Exporter les donn&eacute;es");
define("_EX_COLCONTROLS", "Titre de la colonne(Column Control)"); //New for 0.98rc7
define("_EX_TOKENCONTROLS", "Contr&ocirc;le Invitation"); //New for 0.98rc7
define("_EX_COLSELECT", "Choisir les colonnes"); //New for 0.98rc7
define("_EX_COLOK", "Choisir les colonnes que vous voulez exporter. Ne rien S&eacute;lectionner pour exporter toute les colonnes."); //New for 0.98rc7
define("_EX_COLNOTOK", "Votre questionnaire contient plus de 255 colonnes de r&eacute;ponses. Les tableurs comme Excel sont limit&eacute;s &agrave; 255. S&eacute;lectionner les colonnes &agrave; exporter dans la liste ci-dessous.."); //New for 0.98rc7
define("_EX_TOKENMESSAGE", "Votre questionnaire peut exporter les donn&eacute;es des Invitations associ&eacute;s avec chaque r&eacute;ponse. S&eacute;lectionnez tous les champs additionnels que vous voudriez exporter."); //New for 0.98rc7
define("_EX_TOKSELECT", "Choisir les champs d&#146;invitations"); //New for 0.98rc7

//IMPORT SURVEY MESSAGES
define("_IS_FAILUPLOAD", "Une erreur s&#146;est produite durant la transmission de votre fichier.  Ceci peut être provoqu&eacute; par des permissions incorrectes dans votre dossier admin.");
define("_IS_OKUPLOAD", "Fichier transmis avec succ&eacute;s.");
define("_IS_READFILE", "Lecture du fichier..");
define("_IS_WRONGFILE", "Ce fichier n&#146;est pas fichier de questionnaire PHPSurveyor. L&#146;importation a &eacute;chou&eacute;.");
define("_IS_IMPORTSUMMARY", "Sommaire de l&#146;importation du questionnaire");
define("_IS_SUCCESS", "L&#146;importation du questionnaire est termin&eacute;e.");
define("_IS_IMPFAILED", "L&#146;importation de ce fichier questionnaire a &eacute;chou&eacute;");
define("_IS_FILEFAILS", "Mauvais format de donn&eacute;es dans le fichier de donn&eacute;es PHPSurveyor.");

//IMPORT GROUP MESSAGES
define("_IG_IMPORTSUMMARY", "Sommaire de l&#146;importation de groupe");
define("_IG_SUCCESS", "L&#146;importation du groupe est termin&eacute;e.");
define("_IG_IMPFAILED", "L&#146;importation de ce groupe a &eacute;chou&eacute;");
define("_IG_WRONGFILE", "Ce fichier n&#146;est pas un fichier de groupe PHPSurveyor. L&#146;importation a &eacute;chou&eacute;.");

//IMPORT QUESTION MESSAGES
define("_IQ_NOSID", "Aucun SID (Questionnaire) n&#146;a &eacute;t&eacute; fourni. Impossible d&#146;importer une question.");
define("_IQ_NOGID", "Aucun GID (Groupe) n&#146;a &eacute;t&eacute; fourni. Impossible d&#146;importer une question.");
define("_IQ_WRONGFILE", "Ce fichier n&#146;est pas un fichier de question PHPSurveyor. L&#146;importation a &eacute;chou&eacute;.");
define("_IQ_IMPORTSUMMARY", "Sommaire de l&#146;importation de question");
define("_IQ_SUCCESS", "L&#146;importation de question est termin&eacute;e");

//IMPORT LABELSET MESSAGES
define("_IL_DUPLICATE", "Il y a un doublon dans les jeux d&#146;&eacute;tiquettes donc ce jeu n&#146;a pas &eacute;t&eacute; import&eacute;. Le doublon sera utlis&eacute; &agrave; la place.");

//BROWSE RESPONSES MESSAGES
define("_BR_NOSID", "Vous n&#146;avez pas S&eacute;lectionn&eacute; de questionnaire &agrave; parcourir.");
define("_BR_NOTACTIVATED", "Ce questionnaire n&#146;a pas &eacute;t&eacute; activ&eacute;. Aucun r&eacute;sultats &agrave; parcourir.");
define("_BR_NOSURVEY", "Il n&#146;y a pas de questionnaire associ&eacute;.");
define("_BR_EDITRESPONSE", "Editer cette saisie ");
define("_BR_DELRESPONSE", "Supprimer cette saisie");
define("_BR_DISPLAYING", "Enregistrements affich&eacute;s :");
define("_BR_STARTING", "A partir de :");
define("_BR_SHOW", "Afficher");
define("_DR_RUSURE", "Est-vous s&ucirc;r de vouloir supprimer cette saisie ?"); //New for 0.98rc6

//STATISTICS MESSAGES
define("_ST_FILTERSETTINGS", "Param&eacute;tres de filtre");
define("_ST_VIEWALL", "Visualiser le sommaire de tous les champs disponibles"); //New with 0.98rc8
define("_ST_SHOWRESULTS", "Visualiser les Stats"); //New with 0.98rc8
define("_ST_CLEAR", "Effacer s&eacute;lection"); //New with 0.98rc8
define("_ST_RESPONECONT", "R&eacute;ponses contenant :"); //New with 0.98rc8
define("_ST_NOGREATERTHAN", "Nombre sup&eacute;rieur que"); //New with 0.98rc8
define("_ST_NOLESSTHAN", "Nombre inf&eacute;rieur &agrave;"); //New with 0.98rc8
define("_ST_DATEEQUALS", "Date (AAAA-MM-JJ) &eacute;gale"); //New with 0.98rc8
define("_ST_ORBETWEEN", "OU entre"); //New with 0.98rc8
define("_ST_RESULTS", "R&eacute;sultats"); //New with 0.98rc8 (Plural)
define("_ST_RESULT", "R&eacute;sultat"); //New with 0.98rc8 (Singular)
define("_ST_RECORDSRETURNED", "Aucun enregistrement dans cette requ&ecirc;te"); //New with 0.98rc8
define("_ST_TOTALRECORDS", "Nombre d&#146;enregistrements total dans le questionnaire"); //New with 0.98rc8
define("_ST_PERCENTAGE", "Pourcentage du total"); //New with 0.98rc8
define("_ST_FIELDSUMMARY", "Sommaire de champs pour"); //New with 0.98rc8
define("_ST_CALCULATION", "Calcul"); //New with 0.98rc8
define("_ST_SUM", "Somme"); //New with 0.98rc8 - Mathematical
define("_ST_STDEV", "Ecart type"); //New with 0.98rc8 - Mathematical
define("_ST_AVERAGE", "Moyenne"); //New with 0.98rc8 - Mathematical
define("_ST_MIN", "Minimum"); //New with 0.98rc8 - Mathematical
define("_ST_MAX", "Maximum"); //New with 0.98rc8 - Mathematical
define("_ST_Q1", "1er Quartile (Q1)"); //New with 0.98rc8 - Mathematical
define("_ST_Q2", "2&egrave;me Quartile (Median)"); //New with 0.98rc8 - Mathematical
define("_ST_Q3", "3&egrave;me Quartile (Q3)"); //New with 0.98rc8 - Mathematical
define("_ST_NULLIGNORED", "*Des valeurs nulles sont ignor&eacute;es dans les calculs"); //New with 0.98rc8
define("_ST_QUARTMETHOD", "*Q1 et Q3 ont &eacute;t&eacute; calcul&eacute;s avec <a href=`http://mathforum.org/library/drmath/view/60969.html` target=`_blank`>la m&eacute;thode MINITAB</a>"); //New with 0.98rc8

//DATA ENTRY MESSAGES
define("_DE_NOMODIFY", "Ne peut pas &ecirc;tre modifi&eacute;");
define("_DE_UPDATE", "Mettre &agrave; jour la saisie");
define("_DE_NOSID", "Vous n&#146;avez pas s&eacute;lectionn&eacute; de questionnaire pour la saisie des donn&eacute;es.");
define("_DE_NOEXIST", "Le questionnaire que vous avez s&eacute;lectionn&eacute; n&#146;&eacute;xiste pas");
define("_DE_NOTACTIVE", "Ce questionnaire n&#146;est pas encore activ&eacute;. Votre r&eacute;ponse ne peut pas être sauvegard&eacute;e");
define("_DE_INSERT", "Insertion de donn&eacute;e");
define("_DE_RECORD", "L&#146;entr&eacute;e &eacute;tait assign&eacute;e &agrave; l&#146;ID de l&#146;enregistrement suivant : ");
define("_DE_ADDANOTHER", "Ajouter un autre enregistrement");
define("_DE_VIEWTHISONE", "Visualiser cet enregistrement");
define("_DE_BROWSE", "Parcourir les r&eacute;ponses");
define("_DE_DELRECORD", "Enregistrement supprim&eacute;");
define("_DE_UPDATED", "L&#146;enregistrement a &eacute;t&eacute; mis &agrave; jour.");
define("_DE_EDITING", "Editer une r&eacute;ponse");
define("_DE_QUESTIONHELP", "Aide sur cette question");
define("_DE_CONDITIONHELP1", "R&eacute;pondez seulement &agrave; ceci si les conditions suivantes sont r&eacute;unies :"); 
define("_DE_CONDITIONHELP2", "&agrave; la question {QUESTION}, vous avez r&eacute;pondu {ANSWER}"); //This will be a tricky one depending on your languages syntax. {ANSWER} is replaced with ALL ANSWERS, seperated by _DE_OR (OR).
define("_DE_AND", "ET (AND)");
define("_DE_OR", "OU (OR)");
define("_DE_SAVEENTRY", "Sauvegarder les r&ecute;ponses partielles au questionnaire"); //New in 0.99dev01
define("_DE_SAVEID", "Identification :"); //New in 0.99dev01
define("_DE_SAVEPW", "Mot de passe :"); //New in 0.99dev01
define("_DE_SAVEPWCONFIRM", "Confirmer le mot de passe :"); //New in 0.99dev01
define("_DE_SAVEEMAIL", "Mail :"); //New in 0.99dev01

//TOKEN CONTROL MESSAGES
define("_TC_TOTALCOUNT", "Total d&#146;enregistrements dans cette table Invitation :"); //New in 0.98rc4
define("_TC_NOTOKENCOUNT", "Total sans invitation unique :"); //New in 0.98rc4
define("_TC_INVITECOUNT", "Total d&#146;invitations envoy&eacute;es :"); //New in 0.98rc4
define("_TC_COMPLETEDCOUNT", "Total de questionnaire termin&eacute;s :"); //New in 0.98rc4
define("_TC_NOSID", "Vous n&#146;avez pas s&eacute;lectionn&eacute; de questionnaire");
define("_TC_DELTOKENS", "Au sujet de la suppression de la table Invitation pour ce questionnaire.");
define("_TC_DELTOKENSINFO", "Si vous supprimez cette table, des invitations ne seront plus requises pour acc&eacute;der &agrave; ce questionnaire. Une sauvegarde de cette table sera effectu&eacute; si vous la supprimez. Votre administrateur syst&eacute;me pourra acc&eacute;der &agrave; cette table.");
define("_TC_DELETETOKENS", "Supprimer Invitations");
define("_TC_TOKENSGONE", "La table d&#146;invitations a &eacute;t&eacute; enlev&eacute;e maintenant et des invitations ne sont plus requises pour acc&eacute;der &agrave; ce questionnaire. Une sauvegarde de cette table a &eacute;t&eacute; effectu&eacute;e. L`administrateur syst&eacute;me pourra y acc&eacute;der.");
define("_TC_NOTINITIALISED", "Aucune invitation n&#146;a &eacute;t&eacute; initialis&eacute;e pour ce questionnaire.");
define("_TC_INITINFO", "Si vous initialisez des invitations pour ce questionnaire, seul les utilisateurs ayant une invitation pourront y acc&eacute;der.");
define("_TC_INITQ", "Voulez-vous cr&eacute;er des invitations pour ce questionnaire ?");
define("_TC_INITTOKENS", "Initialiser les invitations");
define("_TC_CREATED", "Une table d&#146;invitations a &eacute;t&eacute; cr&eacute;e pour ce questionnaire.");
define("_TC_DELETEALL", "Supprimer toutes les invitations");
define("_TC_DELETEALL_RUSURE", "Etes-vous s&ucirc;r de vouloir supprimer TOUTES les invitations?");
define("_TC_ALLDELETED", "Toutes les invitations ont &eacute;t&eacute; supprim&eacute;es");
define("_TC_CLEARINVITES", "Set all entries to `N` invitation sent");
define("_TC_CLEARINV_RUSURE", "Etes-vous sucirc;r de vouloir r&eacute;initialiser tous les enregistrements d&#146;invitation &agrave; NON ?");
define("_TC_CLEARTOKENS", "Supprimer tous les nombres uniques des invitations (All unique token numbers)");
define("_TC_CLEARTOKENS_RUSURE", "Etes-vous s&ucirc;r de vouloir supprimer tous les nombres uniques des invitations?");
define("_TC_TOKENSCLEARED", "Tous les nombres uniques des invitations ont &eacute;t&eacute; enlev&eacute;s");
define("_TC_INVITESCLEARED", "Toutes les entr&eacute;s des invitations ont &eacute;t&eacute; d&eacute;finies &agrave; N");
define("_TC_EDIT", "Editer les invitations (Token Entry)");
define("_TC_DEL", "Supprimer Invitation");
define("_TC_DO", "Faire un Questionnaire");
define("_TC_VIEW", "Voir R&eacute;ponse");
define("_TC_INVITET", "Envoyer une invitation par mail &agrave; cette entr&eacute;e");
define("_TC_REMINDT", "Envoyer un rappel par email pour cette entr&eacute;e");
define("_TC_INVITESUBJECT", "Invitation pour r&eacute;pondre au questionnaire {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSUBJECT", "Rappel pour r&eacute;pondre au questionnaire {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSTARTAT", "Commencer &agrave; l&#146;IID (TID) No :");
define("_TC_REMINDTID", "envoy&eacute; &agrave;l&#146;IID (TID) No :");
define("_TC_CREATETOKENSINFO", "Cliquer sur OUI va g&eacute;n&eacute;rer des invitations pour ceux de la liste d&#146;invitations qui n&#146;en ont pas reçues. Etes-vous d&#146;accord??");
define("_TC_TOKENSCREATED", "{TOKENCOUNT} invitations ont &eacute;t&eacute; cr&eacute;es"); //Leave {TOKENCOUNT} for replacement in script with the number of tokens created
define("_TC_TOKENDELETED", "Une invitation a &eacute;t&eacute; supprim&eacute;e.");
define("_TC_SORTBY", "Tri par : ");
define("_TC_ADDEDIT", "Ajouter ou &eacute;diter une invitation");
define("_TC_TOKENCREATEINFO", "Vous pouvez laisser cela &agrave; blanc et g&eacute;n&eacute;rer automatiquement des invitations avec `Cr&eacute;er Invitations`");
define("_TC_TOKENADDED", "Ajouter Nouvelle Invitation");
define("_TC_TOKENUPDATED", "Mise &agrave; jour Invitation");
define("_TC_UPLOADINFO", "Le fichier doit &ecirc;tre un fichier standard CSV (d&eacute;limiteur: virgule) sans quotes. La premi&eacute;re ligne doit contenir une informations d&#146;en-tête (qui sera enlev&eacute;e). Les donn&eacute;es devront &ecirc;tre tri&eacute;es par \"Nom, Pr&eacute;nom, email, [token], [attribute1], [attribute2]\".");
define("_TC_UPLOADFAIL", "Fichier t&eacute;l&eacute;charg&eacute; non trouv&eacute;. V&eacute;rifier vos permissions et le chemin du r&eacute;pertoire de t&eacute;l&eacute;chargement (upload)"); //New for 0.98rc5
define("_TC_IMPORT", "Importation du fichier CSV");
define("_TC_CREATE", "Cr&eacute;ation des entr&eacute;es des invitations");
define("_TC_TOKENS_CREATED", "{TOKENCOUNT} enregistrements cr&eacute;es");
define("_TC_NONETOSEND", "Il n&#146;y avait aucun mail &eacute;ligibles &agrave; envoyer : aucun n&#146;a satisfait les crit&egrave;res - mail valide, invitation d&eacute;j&agrave; envoy&eacute;e, questionnaire d&eacute;j&agrave; complet&eacute; et invitation obtenue.");
define("_TC_NOREMINDERSTOSEND", "Il n&#146;y avait aucun mail &eacute;ligibles &agrave; envoyer : aucun n&#146;a satisfait les crit&egrave;res - mail valide, invitation envoy&eacute;e mais questionnaire pas encore complet&eacute;.");
define("_TC_NOEMAILTEMPLATE", "Mod&egrave;le d&#146;invitation non trouv&eacute;. Ce fichier doit exister dans le r&eacute;pertoire  Mod&egrave;le (Template) par d&eacute;faut.");
define("_TC_NOREMINDTEMPLATE", "Mod&egrave;le Rappel non trouv&eacute;. Ce fichier doit exister dans le r&eacute;pertoire  Mod&egrave;le (Template) par d&eacute;faut.");
define("_TC_SENDEMAIL", "Envoyer invitations");
define("_TC_SENDINGEMAILS", "Envoi invitations");
define("_TC_SENDINGREMINDERS", "Envoi rappels");
define("_TC_EMAILSTOGO", "Il y a plus de mail en suspens qui peuvent être envoy&eacute;s en groupe (batch). Continuez d&#146;envoyer des mail en cliquant ci-dessous.");
define("_TC_EMAILSREMAINING", "Il y a encore {EMAILCOUNT} &agrave; envoyer."); //Leave {EMAILCOUNT} for replacement in script by number of emails remaining
define("_TC_SENDREMIND", "Envoyer rappels");
define("_TC_INVITESENTTO", "Invitation envoy&eacute;e &agrave; :"); //is followed by token name
define("_TC_REMINDSENTTO", "Rappel envoy&eacute; &agrave; :"); //is followed by token name
define("_TC_UPDATEDB", "Mettre &agrave; jour la table d&#146;invitation (Tokens) avec des nouveaux champs"); //New for 0.98rc7
define("_TC_EMAILINVITE_SUBJ", "Invitation &agrave; participer &agrave;a un questionnaire"); //New for 0.99dev01
define("_TC_EMAILINVITE", "{FIRSTNAME},\n\nVous avez &eacute;t&eacute; invit&eacute; &agrave; participer &agrave; un questionnaire.\n\n"
                         ."Celui-ci est intitul&eacute;:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
                         ."Pour participer, veuillez cliquer sur le lien ci-dessous.\n\nCordialement,\n\n"
                         ."{ADMINNAME} ({ADMINEMAIL})\n\n"
                         ."----------------------------------------------\n"
                         ."Cliquer ici pour faire le questionnaire :\n"
                         ."{SURVEYURL}"); //New for 0.98rc9 - Email d`Invitation par d&eacute;faut
define("_TC_EMAILREMIND_SUBJ", "RAPPEL pour r&eacute;pondre &agrave; un questionnaire"); //New for 0.99dev01
define("_TC_EMAILREMIND", "{FIRSTNAME},\n\nVous avez &eacute;t&eacute; invit&eacute; &agrave; participer &agrave; un questionnaire r&eacute;cemment.\n\n"
                         ."Nous avons pris en compte que vous n&#146;vez pas encore complet&eacute; le questionnaire, et nous vous rappelons que celui-ci est toujours disponible si vous souhaitez participer.\n\n"
                         ."Le questionnaire est intitul&eacute;:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
                         ."Pour participer, veuillez cliquer sur le lien ci-dessous.\n\nCordialement,\n\n"
                         ."{ADMINNAME} ({ADMINEMAIL})\n\n"
                         ."----------------------------------------------\n"
                         ."Cliquez ici pour faire le questionnaire:\n"
                         ."{SURVEYURL}"); //New for 0.98rc9 - Email de rappel par defaut
define("_TC_EMAILREGISTER_SUBJ", "Confirmation de l&#146;enregistrement de la participation au questionnaire"); //New for 0.99dev01
define("_TC_EMAILREGISTER", "{FIRSTNAME},\n\n"
                          ."Vous (ou quelqu&#146;un utilisant votre adresse mail) &ecirc;tes enregistr&eacute;s pour "
                          ."participer &agrave; un questionnaire en ligne intitul&eacute;:\n\"{SURVEYNAME}\"\n\n"
                          ."Pour compl&eacute;ter ce questionnaire, cliquez sur le lien suivant:\n\n"
                          ."{SURVEYURL}\n\n"
                          ."Quel que soit votre question &agrave; propos de ce questionnaire, ou si vous "
                          ."ne vous &ecirc;tes pas enregistr&eacute; pour participer &agrave; celui-ci et croyez q&#146;il s&#146;agit "
                          ."d&#146;une erreur, veuillez contacter {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9
define("_TC_EMAILCONFIRM_SUBJ", "Confirmation de r&eacute;ponse au questionnaire"); //New for 0.99dev01
define("_TC_EMAILCONFIRM", "{FIRSTNAME},\n\nCe mail vous confirme que vous avez complet&eacute; le questionnaire intitul&eacute; {SURVEYNAME} "
                          ."et votre r&eacute;ponse &agrave; &eacute;t&eacute; enregistr&eacute;e. Merci.\n\n"
                          ."Si vous avez des questions &agrave; propos de cet email, veuillez contacter {ADMINNAME} ({ADMINEMAIL}).\n\n"
                          ."Cordialement,\n\n"
                          ."{ADMINNAME}"); //New for 0.98rc9 - Confirmation Email

//labels.php
define("_LB_NEWSET", "Cr&eacute;er Nouveau jeu d&#146;&eacute;tiquettes");
define("_LB_EDITSET", "Editer jeu d&#146;&eacute;tiquettes");
define("_LB_FAIL_UPDATESET", "La mise &agrave; jour du jeu d&#146;&eacute;tiquettes a &eacute;chou&eacute;");
define("_LB_FAIL_INSERTSET", "L&#146;insertion du nouveau jeu d&#146;&eacute;tiquettes &agrave; &eacute;chou&eacute;");
define("_LB_FAIL_DELSET", "Impossible de supprimer le jeu d&#146;&eacute;tiquettes - Il y a des questions qui y sont reli&eacute;es. Vous devez supprimer ces questions en premier.");
define("_LB_ACTIVEUSE", "Vous ne pouvez pas changer des codes, ajouter ou supprimer des entr&eacute;es dans ce jeu d&#146;&eacute;tiquettes parce que ceux-ci sont utilis&eacute;s par un questionnaire activ&eacute;.");
define("_LB_TOTALUSE", "Quelques questionnaires utilisent actuellement ce jeu d&#146;&eacute;tiquette. Modifier les codes, ajouter ou supprimer des entr&eacute;es de ce jeu pourrait entrainer des effets ind&eacute;sirables dans d&#146;autres questionnaires.");
//Export Labels
define("_EL_NOLID", "Aucun JID (LID) fourni. Impossible  de vider (Dump) un jeu d&#146;&eacute;tiquettes.");
//Import Labels
define("_IL_GOLABELADMIN", "Retour &agrave; l&#146;administration d&#146;etiquettes");

//PHPSurveyor System Summary
define("_PS_TITLE", "R&eacute;sum&eacute; syst&egrave;me PHPSurveyor");
define("_PS_DBNAME", "Nom de la base de donn&eacute;es");
define("_PS_DEFLANG", "Langue par d&eacute;faut");
define("_PS_CURLANG", "Langage courant");
define("_PS_USERS", "Utilisateurs");
define("_PS_ACTIVESURVEYS", "Questionnaires activ&eacute;s");
define("_PS_DEACTSURVEYS", "D&eacute;sactiver Questionnaires");
define("_PS_ACTIVETOKENS", "Tables d&#146;invitations (Token) activ&eacute;es");
define("_PS_DEACTTOKENS", "D&eacute;sactiver tables invitations");
define("_PS_CHECKDBINTEGRITY", "V&eacute;rifier l&#146;Int&eacute;grit&eacute; Des Donn&eacute;es De PHPSurveyor"); //New with 0.98rc8

//Notification Levels
define("_NT_NONE", "Aucune notification par mail"); //New with 098rc5
define("_NT_SINGLE", "Notification par mail de base"); //New with 098rc5
define("_NT_RESULTS", "Envoyer notification par mail avec des codes r&eacute;sultat"); //New with 098rc5

//CONDITIONS TRANSLATIONS
define("_CD_CONDITIONDESIGNER", "Concepteur de condition"); //New with 098rc9
define("_CD_ONLYSHOW", "Montrer seulement question {QID} SI (IF)"); //New with 098rc9 - {QID} is repleaced leave there
define("_CD_AND", "ET (AND)"); //New with 098rc9
define("_CD_COPYCONDITIONS", "Copier conditions"); //New with 098rc9
define("_CD_CONDITION", "Condition"); //New with 098rc9
define("_CD_ADDCONDITION", "Ajouter condition"); //New with 098rc9
define("_CD_EQUALS", "Egales"); //New with 098rc9
define("_CD_COPYRUSURE", "Etes-vous s&ucirc;r de vouloir copier ces condition(s) aux questions s&eacute;lectionn&eacute;es?"); //New with 098rc9
define("_CD_NODIRECT", "Vous ne pouvez pas &eacute;xecuter directement ce script."); //New with 098rc9
define("_CD_NOSID", "Vous n&#146;avez pas s&eacute;lectionn&eacute; de questionnaire."); //New with 098rc9
define("_CD_NOQID", "Vous n&#146;avez pas s&eacute;lectionn&eacute; de question."); //New with 098rc9
define("_CD_DIDNOTCOPYQ", "Questions non copi&eacute;es"); //New with 098rc9
define("_CD_NOCONDITIONTOCOPY", "Aucune condition &agrave; copier s&eacute;lectionn&eacute;e"); //New with 098rc9
define("_CD_NOQUESTIONTOCOPYTO", "Aucune question s&eacute;lectionn&eacute;e pour copier la condition &agrave;"); //New with 098rc9

//TEMPLATE EDITOR TRANSLATIONS
define("_TP_CREATENEW", "Cr&eacute;er nouveau mod&egrave;le"); //New with 098rc9
define("_TP_NEWTEMPLATECALLED", "Cr&eacute;er nouveau mod&egrave;le nomm&eacute; :"); //New with 098rc9
define("_TP_DEFAULTNEWTEMPLATE", "Nouveau mod&egrave;le"); //New with 098rc9 (default name for new template)
define("_TP_CANMODIFY", "Ce mod&egrave;le peut &ecirc;tre modifi&eacute;"); //New with 098rc9
define("_TP_CANNOTMODIFY", "Ce mod&egrave;le ne peut pas &ecirc;tre modifi&eacute;"); //New with 098rc9
define("_TP_RENAME", "Renommer ce mod&egrave;le");  //New with 098rc9
define("_TP_RENAMETO", "Renommer ce mod&egrave;le en :"); //New with 098rc9
define("_TP_COPY", "Faire une copie de ce mod&egrave;le");  //New with 098rc9
define("_TP_COPYTO", "Cr&eacute;er une copie de ce mod&egrave;le nomm&eacute; :"); //New with 098rc9
define("_TP_COPYOF", "copie_de_"); //New with 098rc9 (prefix to default copy name)
define("_TP_FILECONTROL", "Contr&ocirc;le fichier:"); //New with 098rc9
define("_TP_STANDARDFILES", "Fichiers standards :");  //New with 098rc9
define("_TP_NOWEDITING", "Edition en cours :");  //New with 098rc9
define("_TP_OTHERFILES", "Autres fichiers :"); //New with 098rc9
define("_TP_PREVIEW", "Aper&ccedil;u :"); //New with 098rc9
define("_TP_DELETEFILE", "Supprimer"); //New with 098rc9
define("_TP_UPLOADFILE", "T&eacute;l&eacute;charger (Upload)"); //New with 098rc9
define("_TP_SCREEN", "Ecran :"); //New with 098rc9
define("_TP_WELCOMEPAGE", "Page d&#146;accueil"); //New with 098rc9
define("_TP_QUESTIONPAGE", "Page de question"); //New with 098rc9
define("_TP_SUBMITPAGE", "Envoyer la page");
define("_TP_COMPLETEDPAGE", "Page compl&eacute;t&eacute;e"); //New with 098rc9
define("_TP_CLEARALLPAGE", "Effacer toute la page"); //New with 098rc9
define("_TP_REGISTERPAGE", "Register Page"); //New with 098finalRC1
define("_TP_EXPORT", "Exporter le mod&egrave;le"); //New with 098rc10
define("_TP_LOADPAGE", "Charger la page"); //New with 0.99dev01
define("_TP_SAVEPAGE", "Sauvegarder la page"); //New with 0.99dev01

//Saved Surveys
define("_SV_RESPONSES", "R&eaucte;ponses sauvegard&eacute;es :");
define("_SV_IDENTIFIER", "Identification");
define("_SV_RESPONSECOUNT", "Answered");
define("_SV_IP", "Adresse IP");
define("_SV_DATE", "Date sauvegard&eacute;e");
define("_SV_REMIND", "Remind");
define("_SV_EDIT", "Editer");

//VVEXPORT/IMPORT
define("_VV_IMPORTFILE", "Importer un fichier de questionnaire VV");
define("_VV_EXPORTFILE", "Exporter vers un fichier de questionnaire VV");
define("_VV_FILE", "Fichier :");
define("_VV_SURVEYID", "ID du questionnaire :");
define("_VV_EXCLUDEID", "Exclure les ID enregistr&eacute;s ?");
define("_VV_INSERT", "Quand un enregistrement import&eacute; correspond &agrave; un enregistrement existant (ID): ");
define("_VV_INSERT_ERROR", "Reporter une erreur (et sauter le nouvel enregistrement).");
define("_VV_INSERT_RENUMBER", "Renum&eacute;roter le nouvel enregistrement.");
define("_VV_INSERT_IGNORE", "Ignorer le nouvel enregistrement.");
define("_VV_INSERT_REPLACE", "Remplacer l&#146;enregistement existant.");
define("_VV_DONOTREFRESH", "Note importante:<br />Ne pas ACTUALISER cette page sous peine d&#146;importer de nouveau le fichier et de cr&eacute;er des doublons");
define("_VV_IMPORTNUMBER", "Nombre d&#146;enregistrement import&eacute;s :");
define("_VV_ENTRYFAILED", "Echec de l&#146;importation sur l&#146;enregistrement");
define("_VV_BECAUSE", "parce que");
define("_VV_EXPORTDEACTIVATE", "Exporter et ensuite d&eacute;sactiver le questionnaire");
define("_VV_EXPORTONLY", "Exporter mais laisser le questionnaire actif");
define("_VV_RUSURE", "Si vous choisissez d&#146;exporter et de d&eacute;sactiver le questionnaire, cela renommera votre table de r&eacute,ponses et cela ne sera pas facile de la restaurer. Etes-vous s&ucirc;r ?");

//ASSESSMENTS
define("_AS_TITLE", "Assessments");
define("_AS_DESCRIPTION", "If you create any assessments in this page, for the currently selected survey, the assessment will be performed at the end of the survey after submission");
define("_AS_NOSID", "Pas de SID fourni");
define("_AS_SCOPE", "Scope");
define("_AS_MINIMUM", "Minimum");
define("_AS_MAXIMUM", "Maximum");
define("_AS_GID", "Groupe");
define("_AS_NAME", "Nom/Ent&ecirc;te");
define("_AS_HEADING", "Heading");
define("_AS_MESSAGE", "Message");
define("_AS_URL", "URL");
define("_AS_SCOPE_GROUP", "Groupe");
define("_AS_SCOPE_TOTAL", "Total");
define("_AS_ACTIONS", "Actions");
define("_AS_EDIT", "Editer");
define("_AS_DELETE", "Effacer");
define("_AS_ADD", "Ajouter");
define("_AS_UPDATE", "Mettre &agrave; jour");

//Question Number regeneration
define("_RE_REGENNUMBER", "R&eacute;g&eacute;n&eacute;ration des num&eacute;ros de questions :"); //NEW for release 0.99dev2
define("_RE_STRAIGHT", "Straight"); //NEW for release 0.99dev2
define("_RE_BYGROUP", "par groupe"); //NEW for release 0.99dev2
?>
