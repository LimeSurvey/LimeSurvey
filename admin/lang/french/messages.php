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
    #  File was originally provided by Pascal BASTIEN 20/07/2004    #
    #  Maintained and updated by Sébastien GAUGRY                   #
    #  IMPORTANT for translation : for ' use &#039;                 #
    #  Some corrections by Pascal BASTIEN - Ver1.36                 #
    #  for PhpSurveyor 0.991 (be careful at 679 & 828 line)         #
    #  Edit this file with an UTF-8 capable editor only!            #
    #                                                               #
    #################################################################
*/


//BUTTON BAR TITLES
define("_ADMINISTRATION", "Administration");
define("_SURVEY", "Questionnaire");
define("_GROUP", "Groupe");
define("_QUESTION", "Question");
define("_ANSWERS", "Réponses");
define("_CONDITIONS", "Conditions");
define("_HELP", "Aide");
define("_USERCONTROL", "Contrôle utilisateur");
define("_ACTIVATE", "Activer le questionnaire");
define("_DEACTIVATE", "Désactiver le questionnaire");
define("_CHECKFIELDS", "Contrôle des champs de la base de données");
define("_CREATEDB", "Créer la base de données");
define("_CREATESURVEY", "Créer le questionnaire"); //New for 0.98rc4
define("_SETUP", "Paramètres de PHPSurveyor");
define("_DELETESURVEY", "Supprimer le questionnaire");
define("_EXPORTQUESTION", "Exporter la question");
define("_EXPORTSURVEY", "Exporter le questionnaire");
define("_EXPORTLABEL", "Exporter le jeu d&#039;étiquettes");
define("_IMPORTQUESTION", "Importer la question");
define("_IMPORTGROUP", "Importer le groupe"); //New for 0.98rc5
define("_IMPORTSURVEY", "Importer le questionnaire");
define("_IMPORTLABEL", "Importer le jeu d&#039;étiquettes");
define("_EXPORTRESULTS", "Exporter les réponses");
define("_BROWSERESPONSES", "Parcourir les réponses");
define("_BROWSESAVED", "Parcourir les réponses sauvegardées");
define("_STATISTICS", "Statistiques flash");
define("_VIEWRESPONSE", "Visualisation de la réponse d&#039;ID ");
define("_VIEWCONTROL", "Navigation dans les données ");
define("_DATAENTRY", "Entrée de données");
define("_TOKENCONTROL", "Contrôle des invitations");
define("_TOKENDBADMIN", "Options d&#039;administration de la table d&#039;invitations ");
define("_DROPTOKENS", "Suppression de la table des invitations");
define("_EMAILINVITE", "Invitation par mail");
define("_EMAILREMIND", "Rappel par mail");
define("_TOKENIFY", "Créer les invitations");
define("_UPLOADCSV", "Envoyer le fichier CSV");
define("_LABELCONTROL", "Administration des jeux d&#039;étiquettes"); //NEW with 0.98rc3
define("_LABELSET", "Jeu d&#039;étiquettes"); //NEW with 0.98rc3
define("_LABELANS", "Etiquettes"); //NEW with 0.98rc3
define("_OPTIONAL", "Optionnel"); //NEW with 0.98finalRC1

//DROPDOWN HEADINGS
define("_SURVEYS", "Questionnaires");
define("_GROUPS", "Groupes");
define("_QUESTIONS", "Questions");
define("_QBYQ", "Question par question");
define("_GBYG", "Groupe par groupe");
define("_SBYS", "Tout sur la même page");
define("_LABELSETS", "Jeux d&#039;étiquettes"); //New with 0.98rc3

//BUTTON MOUSEOVERS
//administration bar
define("_A_HOME_BT", "Page d&#039;administration générale");
define("_A_SECURITY_BT", "Modifier les paramètres de Sécurité");
define("_A_BADSECURITY_BT", "Activer la sécurité");
define("_A_CHECKDB_BT", "Vérifier la base de données");
define("_A_DELETE_BT", "Supprimer tout le questionnaire");
define("_A_ADDSURVEY_BT", "Créer ou importer un nouveau questionnaire");
define("_A_HELP_BT", "Aide");
define("_A_CHECKSETTINGS", "Vérifier les paramètres");
define("_A_BACKUPDB_BT", "Sauvegarder entiérement la base de données"); //New for 0.98rc10
define("_A_TEMPLATES_BT", "Editeur de modèles"); //New for 0.98rc9
//Survey bar
define("_S_ACTIVE_BT", "Ce questionnaire est actuellement activé");
define("_S_INACTIVE_BT", "Ce questionnaire est actuellement désactivé");
define("_S_ACTIVATE_BT", "Activer ce questionnaire");
define("_S_DEACTIVATE_BT", "Désactiver ce questionnaire");
define("_S_CANNOTACTIVATE_BT", "Impossible d&#039;activer ce questionnaire");
define("_S_DOSURVEY_BT", "Tester le questionnaire");
define("_S_DATAENTRY_BT", "Ecran de saisie de données pour le questionnaire");
define("_S_PRINTABLE_BT", "Version imprimable du questionnaire");
define("_S_EDIT_BT", "Editer le questionnaire courant");
define("_S_DELETE_BT", "Supprimer le questionnaire courant");
define("_S_EXPORT_BT", "Exporter ce questionnaire");
define("_S_BROWSE_BT", "Parcourir les réponses pour ce questionnaire");
define("_S_TOKENS_BT", "Activer/Editer les invitations pour ce questionnaire");
define("_S_ADDGROUP_BT", "Ajouter un nouveau groupe au questionnaire");
define("_S_MINIMISE_BT", "Masquer les détails de ce questionnaire");
define("_S_MAXIMISE_BT", "Afficher les détails de ce questionnaire");
define("_S_CLOSE_BT", "Fermer ce questionnaire");
define("_S_SAVED_BT", "Voir les réponses enregistrées mais non validées"); //New in 0.99dev01
define("_S_ASSESSMENT_BT", "Définir des régles d&#039;évaluation"); //New in  0.99dev01
//Group bar
define("_G_EDIT_BT", "Editer le groupe en cours");
define("_G_EXPORT_BT", "Exporter le groupe en cours"); //New in 0.98rc5
define("_G_DELETE_BT", "Supprimer le groupe en cours");
define("_G_ADDQUESTION_BT", "Ajouter une nouvelle question au groupe");
define("_G_MINIMISE_BT", "Masquer les détails de ce groupe");
define("_G_MAXIMISE_BT", "Afficher les détails de ce groupe");
define("_G_CLOSE_BT", "Fermer ce groupe");
//Question bar
define("_Q_EDIT_BT", "Editer la question en cours");
define("_Q_COPY_BT", "Copier la question en cours"); //New in 0.98rc4
define("_Q_DELETE_BT", "Supprimer la question en cours");
define("_Q_EXPORT_BT", "Exporter cette question");
define("_Q_CONDITIONS_BT", "Affecter des conditions à cette question");
define("_Q_ANSWERS_BT", "Editer/Ajouter des réponses pour cette question");
define("_Q_LABELS_BT", "Editer/Ajouter des jeux d&#039;étiquettes");
define("_Q_MINIMISE_BT", "Masquer les détails de cette question");
define("_Q_MAXIMISE_BT", "Afficher les détails de cette question");
define("_Q_CLOSE_BT", "Fermer cette question");
//Browse Button Bar
define("_B_ADMIN_BT", "Retourner à l&#039;écran d&#039;administration générale");
define("_B_SUMMARY_BT", "Afficher un résumé");
define("_B_ALL_BT", "Afficher les réponses");
define("_B_LAST_BT", "Afficher les 50 dernières réponses");
define("_B_STATISTICS_BT", "Donner les statistiques de ces réponses");
define("_B_EXPORT_BT", "Exporter les résultats vers une application");
define("_B_BACKUP_BT", "Sauvegarder vers un fichier SQL la table de résultats");
define("_B_IMPORTOLDRESULTS_BT","Importer les réponses depuis la table d&#039;un questionnaire désactivé");

//Tokens Button Bar
define("_T_ALL_BT", "Afficher les invitations");
define("_T_ADD_BT", "Ajouter une nouvelle invitation");
define("_T_IMPORT_BT", "Importer les invitations à partir d&#039;un fichier CSV");
define("_T_EXPORT_BT", "Exporter des invitations vers un fichier CSV"); //New for 0.98rc7
define("_T_INVITE_BT", "Envoyer les invitations par mail");
define("_T_REMIND_BT", "Envoyer les rappels par mail");
define("_T_TOKENIFY_BT", "Générer les codes d&#039;invitations");
define("_T_KILL_BT", "Effacer la table des invitations");
//Labels Button Bar
define("_L_ADDSET_BT", "Ajouter un nouveau jeu d&#039;étiquettes");
define("_L_EDIT_BT", "Editer un jeu d&#039;étiquettes");
define("_L_DEL_BT", "Supprimer un jeu d&#039;eacute;tiquettes");
//Datacontrols
define("_D_BEGIN", "Montrer le début...");
define("_D_BACK", "Montrer le précédant...");
define("_D_FORWARD", "Montrer le suivant...");
define("_D_END", "Montrer la fin...");

//DATA LABELS
//surveys
define("_SL_TITLE", "Titre :");
define("_SL_SURVEYURL", "URL du questionnaire :"); //new in 0.98rc5
define("_SL_DESCRIPTION", "Description :");
define("_SL_WELCOME", "Message de bienvenue :");
define("_SL_ADMIN", "Administrateur :");
define("_SL_EMAIL", "Mail de l&#039;administrateur :");
define("_SL_FAXTO", "Fax à :");
define("_SL_ANONYMOUS", "Anonyme :");
define("_SL_EXPIRYDATE", "Date limite de réponse :");
define("_SL_FORMAT", "Format :");
define("_SL_DATESTAMP", "Réponses datées :");
define("_SL_IPADDRESS", "Adresse IP :"); //New with 0.991
define("_SL_TEMPLATE", "Modèle :");
define("_SL_LANGUAGE", "Langue :");
define("_SL_LINK", "Lien :");
define("_SL_URL", "URL de fin :");
define("_SL_URLDESCRIP", "Description de l&#039;URL :");
define("_SL_STATUS", "Status :");
define("_SL_SELSQL", "Sélectionner un fichier SQL :");
define("_SL_USECOOKIES", "Utiliser des cookies ?"); //NEW with 098rc3
define("_SL_NOTIFICATION", "Notification :"); //New with 098rc5
define("_SL_ALLOWREGISTER", "Permettre l&#039;enregistrement public ?"); //New with 0.98rc9
define("_SL_ATTRIBUTENAMES", "Nom des champs supplémentaires pour l&#039;enregistrement public :"); //New with 0.98rc9
define("_SL_EMAILINVITE_SUBJ", "Objet du mail d&#039;invitation :"); //New with 0.99dev01
define("_SL_EMAILINVITE", "Invitation par mail :"); //New with 0.98rc9
define("_SL_EMAILREMIND_SUBJ", "Objet du mail de rappel :"); //New with 0.99dev01
define("_SL_EMAILREMIND", "Rappel par mail :"); //New with 0.98rc9
define("_SL_EMAILREGISTER_SUBJ", "Objet du mail d&#039;enregistrement public :"); //New with 0.99dev01
define("_SL_EMAILREGISTER", "Mail de l&#039;enregistrement public :"); //New with 0.98rc9
define("_SL_EMAILCONFIRM_SUBJ", "Objet du mail de confirmation :"); //New with 0.99dev01
define("_SL_EMAILCONFIRM", "Confirmation par mail :"); //New with 0.98rc9
define("_SL_REPLACEOK", "Cela remplacera le texte existant. Continuer ?"); //New with 0.98rc9
define("_SL_ALLOWSAVE", "Autoriser les sauvegardes ?"); //New with 0.99dev01
define("_SL_AUTONUMBER", "Démarrer la numérotation des ID à :"); //New with 0.99dev01
define("_SL_AUTORELOAD", "Charger automatiquement l&#039;URL quand le questionnaire est terminé ?"); //New with 0.99dev01
define("_SL_ALLOWPREV", "Montrer le bouton [<< Préc]"); //New with 0.99dev01
define("_SL_USE_DEFAULT","Utiliser les défauts");
define("_SL_UPD_SURVEY","Mise à jour du questionnaire");

//groups
define("_GL_TITLE", "Titre :");
define("_GL_DESCRIPTION", "Description :");
define("_GL_EDITGROUP","Edition du groupe pour le questionnaire ID"); // New with 0.99dev02
define("_GL_UPDATEGROUP","Mise à jour du groupe"); // New with 0.99dev02
//questions
define("_QL_EDITQUESTION", "Editer la question");// New with 0.99dev02
define("_QL_UPDATEQUESTION", "Mise à jour de la question");// New with 0.99dev02
define("_QL_CODE", "Code :");
define("_QL_QUESTION", "Question :");
define("_QL_VALIDATION", "Validation :"); //New in VALIDATION VERSION
define("_QL_HELP", "Aide :");
define("_QL_TYPE", "Type :");
define("_QL_GROUP", "Groupe :");
define("_QL_MANDATORY", "Obligatoire :");
define("_QL_OTHER", "Autre :");
define("_QL_LABELSET", "Jeu d&#039;étiquettes :");
define("_QL_COPYANS", "Copier les réponses ?"); //New in 0.98rc3
define("_QL_QUESTIONATTRIBUTES", "Attributs de la question"); //New in 0.99dev01
define("_QL_COPYATT", "Copier les attributs ?"); //New in 0.99dev01
//answers
define("_AL_CODE", "Code");
define("_AL_ANSWER", "Réponse");
define("_AL_DEFAULT", "Défaut");
define("_AL_MOVE", "Déplacer");
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
define("_UL_PASSWORD", "Mot de passe");
define("_UL_SECURITY", "Protection");
define("_UL_ACTION", "Action");
define("_UL_EDIT", "Editer");
define("_UL_DEL", "Supprimer");
define("_UL_ADD", "Ajout");
define("_UL_TURNOFF", "Désactiver la protection");

//tokens
define("_TL_FIRST", "Nom");
define("_TL_LAST", "Prénom");
define("_TL_EMAIL", "Mail");
define("_TL_TOKEN", "Code de l&#039;invitation");
define("_TL_INVITE", "Invitation envoyée ?");
define("_TL_DONE", "Complété ?");
define("_TL_ACTION", "Actions");
define("_TL_ATTR1", "Attribut_1"); //New for 0.98rc7
define("_TL_ATTR2", "Attribut_2"); //New for 0.98rc7
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
define("_LIST_DROPDOWN", "Liste déroulante"); //New with 0.99dev01
define("_LISTWC", "Liste déroulante avec commentaire");
define("_MULTO", "Case à cocher");
define("_MULTOC", "Case à cocher avec commentaire");
define("_MULTITEXT", "Zone de texte court multilignes");
define("_NUMERICAL", "Entrée numérique");
define("_RANK", "Classement");
define("_STEXT", "Zone de texte court");
define("_LTEXT", "Zone de commentaire");
define("_HTEXT", "Zone de texte long"); //New with 0.99dev01
define("_YESNO", "Oui/Non");
define("_ARR5", "Ligne de 5 boutons radio");
define("_ARR10", "Ligne de 10 boutons radio");
define("_ARRYN", "Ligne (Oui/Non/Indifférent)");
define("_ARRMV", "Ligne (Augmenter, Sans changement, Diminuer)");
define("_ARRFL", "Ligne de boutons radio (Etiquettes personnalisées)"); //Release 0.98rc3
define("_ARRFLC", "Ligne de boutons radio (Etiquettes personnalisées en colonne)"); //Release 0.98rc8
define("_SINFL", "Simple (Etiquettes personnalisées)"); //(FOR LATER RELEASE)
define("_EMAIL", "Adresse mail"); //FOR LATER RELEASE
define("_BOILERPLATE", "Texte fixe"); //New in 0.98rc6
define("_LISTFL_DROPDOWN", "Liste déroulante et étiquettes personnaliées"); //New in 0.99dev01
define("_LISTFL_RADIO", "Bouton radio et étiquettes personnalisées"); //New in 0.99dev01
define("_SLIDER", "Curseur"); //New for slider mod

//GENERAL WORDS AND PHRASES
define("_AD_YES", "Oui");
define("_AD_NO", "Non");
define("_AD_CANCEL", "Annuler");
define("_AD_CHOOSE", "Sélectionnez...");
define("_AD_OR", "OU"); //New in 0.98rc4
define("_ERROR", "Erreur");
define("_SUCCESS", "Succés");
define("_REQ", "*Requis");
define("_ADDS", "Ajouter un questionnaire");
define("_ADDG", "Ajouter un groupe");
define("_ADDQ", "Ajouter une question");
define("_ADDA", "Ajouter une réponse"); //New in 0.98rc4
define("_COPYQ", "Copier une question"); //New in 0.98rc4
define("_ADDU", "Ajouter un utilisateur");
define("_SEARCH", "Chercher"); //New in 0.98rc4
define("_SAVE", "Sauver les modifications");
define("_NONE", "Rien de sélectionné"); //as in "Do not display anything", "or none chosen";
define("_GO_ADMIN", "Ecran principal d&#039;administration"); //text to display to return/display main administration screen
define("_CONTINUE", "Continuer");
define("_WARNING", "Avertissement");
define("_USERNAME", "Nom d&#039;utilisateur");
define("_PASSWORD", "Mot de passe");
define("_DELETE", "Supprimer");
define("_CLOSEWIN", "Fermer la fenêtre");
define("_TOKEN", "Invitations");
define("_DATESTAMP", "Délai de réponse"); //Referring to the datestamp or time response submitted
define("_IPADDRESS", "Adresse IP"); //Referring to the ip address of the submitter - New with 0.991
define("_COMMENT", "Commentaire");
define("_FROM", "De"); //For emails
define("_SUBJECT", "Objet"); //For emails
define("_MESSAGE", "Message"); //For emails
define("_RELOADING", "Actualisation de l&#039;écran. Veuillez patienter.");
define("_ADD", "Ajouter");
define("_UPDATE", "Mise à jour");
define("_BROWSE", "Parcourir"); //New in 098rc5
define("_AND", "et"); //New with 0.98rc8
define("_SQL", "SQL"); //New with 0.98rc8
define("_PERCENTAGE", "Pourcentage"); //New with 0.98rc8
define("_COUNT", "Décompte"); //New with 0.98rc8

//SURVEY STATUS MESSAGES (new in 0.98rc3)
define("_SS_NOGROUPS", "Nombre de groupe(s) dans le questionnaire :"); //NEW for release 0.98rc3
define("_SS_NOQUESTS", "Nombre de question(s) dans le questionnaire :"); //NEW for release 0.98rc3
define("_SS_ANONYMOUS", "Ce questionnaire est anonyme."); //NEW for release 0.98rc3
define("_SS_TRACKED", "Ce questionnaire n&#039;EST PAS anonyme."); //NEW for release 0.98rc3
define("_SS_DATESTAMPED", "Les réponses seront datées"); //NEW for release 0.98rc3
define("_SS_IPADDRESS", "Les adresses IP seront enregistrées"); //New with 0.991
define("_SS_COOKIES", "Utilisation des cookies pour le contrôle d&#039;accès."); //NEW for release 0.98rc3
define("_SS_QBYQ", "Présentation : une question par page."); //NEW for release 0.98rc3
define("_SS_GBYG", "Présentation : un groupe de questions par page."); //NEW for release 0.98rc3
define("_SS_SBYS", "Présentation : une page unique."); //NEW for release 0.98rc3
define("_SS_ACTIVE", "Questionnaire en cours (activé)."); //NEW for release 0.98rc3
define("_SS_NOTACTIVE", "Questionnaire inactivé."); //NEW for release 0.98rc3
define("_SS_SURVEYTABLE", "Nom de la table du questionnaire :"); //NEW for release 0.98rc3
define("_SS_CANNOTACTIVATE", "Impossible d&#039;activer le questionnaire maintenant."); //NEW for release 0.98rc3
define("_SS_ADDGROUPS", "Vous devez ajouter des groupes"); //NEW for release 0.98rc3
define("_SS_ADDQUESTS", "Vous devez ajouter des questions"); //NEW for release 0.98rc3
define("_SS_ALLOWREGISTER", "Si les invitations sont utilisées, les destinataires doivent être enregistrés pour remplir ce questionnaire"); //NEW for release 0.98rc9
define("_SS_ALLOWSAVE", "Les participants peuvent sauvegarder un remplissage partiel du questionnaire"); //NEW for release 0.99dev01

//QUESTION STATUS MESSAGES (new in 0.98rc4)
define("_QS_MANDATORY", "Question obligatoire"); //New for release 0.98rc4
define("_QS_OPTIONAL", "Question optionnelle"); //New for release 0.98rc4
define("_QS_NOANSWERS", "Vous devez ajouter des réponses à cette question"); //New for release 0.98rc4
define("_QS_NOLID", "Vous devez choisir un jeu d&#039;étiquettes pour cette question"); //New for release 0.98rc4
define("_QS_COPYINFO", "Note : vous devez OBLIGATOIREMENT saisir un nouveau code pour la question"); //New for release 0.98rc4

//General Setup Messages
define("_ST_NODB1", "La base de données de PHPSurveyor n&#039;existe pas");
define("_ST_NODB2", "Soit votre base de données n&#039;a pas été créée soit il y a un probléme pour y accéder.");
define("_ST_NODB3", "PHPSurveyor peut tenter de créer la base de données pour vous.");
define("_ST_NODB4", "Le nom de votre base de données sera :");
define("_ST_CREATEDB", "Créer la base de données");

//USER CONTROL MESSAGES
define("_UC_CREATE", "Créer le fichier htaccess par défaut");
define("_UC_NOCREATE", "Impossible de créer le fichier htaccess. Vérifiez votre config.php sous \$homedir, et que vous avez les permissions d&#039;écriture dans le bon répertoire.");
define("_UC_SEC_DONE", "Le niveau de sécurité est maintenant configuré !");
define("_UC_CREATE_DEFAULT", "Créer les utilisateurs par défaut");
define("_UC_UPDATE_TABLE", "Mise à jour de la table des utilisateurs");
define("_UC_HTPASSWD_ERROR", "Une erreur s&#039;est produite lors de la création du fichier htpasswd");
define("_UC_HTPASSWD_EXPLAIN", "Si vous utilisez un serveur Windows il est recommandé de copier le fichier apache sous votre répertoire d&#039;administration pour que cette fonction fonctionne correctement. Ce fichier se trouve généralement sous /apache group/apache/bin/");
define("_UC_SEC_REMOVE", "Enlever les paramètres de sécurité");
define("_UC_ALL_REMOVED", "Les fichiers de permissions, de mots de passe et d&#039;utilisateurs ont été effacés");
define("_UC_ADD_USER", "Ajouter un utilisateur");
define("_UC_ADD_MISSING", "Impossible d&#039;ajouter l&#039;utilisateur. Le nom d&#039;utilisateur et/ou le mot de passe n&#039;étaient pas renseignés");
define("_UC_DEL_USER", "Supprimer un utilisateur");
define("_UC_DEL_MISSING", "Impossible de supprimer l&#039;utilisateur. Le nom d&#039;utilisateur n&#039;était pas rempli.");
define("_UC_MOD_USER", "Modification de l&#039;utilisateur");
define("_UC_MOD_MISSING", "Impossible de modifier l&#039;utilisateur. Le nom d&#039;utilisateur et/ou le mot de passe n&#039;étaient pas renseignés");
define("_UC_TURNON_MESSAGE1", "Vous n&#039;avez pas encore initialisé les paramètres de sécurité pour votre systéme de questionnaire et en conséquence il n&#039;y a pas de restriction d&#039;accés.</p>\nSi vous cliquez sur le bouton INITIALISER LA SECURITE ci-dessous, les paramètres de sécurité standard d&#039;Apache seront ajoutés au répertoire d&#039;administration de ce script. Vous aurez alors besoin d&#039;utiliser le nom d&#039;utilisateur et le mot de passe par défaut pour accéder à l&#039;administration et aux scripts de saisie de données.");
define("_UC_TURNON_MESSAGE2", "Il est fortement recommandé, une fois votre systéme de sécurité initialisé, de changer le mot de passe par défaut.");
define("_UC_INITIALISE", "Initialiser la sécurité");
define("_UC_NOUSERS", "Aucun utilisateur dans la table. Nous vous recommandons de DESACTIVER LA SECURITE et de la REACTIVER ensuite.");
define("_UC_TURNOFF", "Désactiver la sécurité");

//Activate and deactivate messages
define("_AC_MULTI_NOANSWER", "Cette question est à réponses multiples mais n&#039;a aucune réponse de définie.");
define("_AC_NOTYPE", "Cette question n&#039;a pas de type de question paramètré.");
define("_AC_NOLID", "Un jeu d&#039;étiquettes est requis pour cette question. Aucun n&#039;est saisi."); //New for 0.98rc8
define("_AC_CON_OUTOFORDER", "Cette question a une condition paramètrée, toutefois la condition est basée sur une question qui apparaît aprés elle.");
define("_AC_FAIL", "Le questionnaire n&#039;est pas validé par le contrôle de cohérence");
define("_AC_PROBS", "Le probléme suivant a été rencontré :");
define("_AC_CANNOTACTIVATE", "Le questionnaire ne peut pas être activé jusqu&#039;à ce que ces problémes soient résolus");
define("_AC_READCAREFULLY", "LIRE CECI ATTENTIVEMENT AVANT DE POURSUIVRE");
define("_AC_ACTIVATE_MESSAGE1", "Vous devriez activer un questionnaire seulement si vous êtes absolument certain que votre questionnaire est correctement paramètré/terminé et n&#039;aura pas besoin d&#039;être modifié.");
define("_AC_ACTIVATE_MESSAGE2", "Un fois qu&#039;un questionnaire est activé vous ne pouvez plus :<ul><li>Ajouter ou supprimer des groupes</li><li>Ajouter ou enlever des réponses aux questions à réponses multiples</li><li>Ajouter ou supprimer des questions</li></ul>");
define("_AC_ACTIVATE_MESSAGE3", "Cependant vous pouvez toujours :<ul><li>Editer les codes de vos questions, le texte ou le type </li><li>Editer les noms de vos groupes</li><li>Ajouter, Enlever ou Editer les réponses des questions prédéfinies (à l&#039;exception des questions à réponses multiples)</li><li>Changer le nom du questionnaire ou sa description</li></ul>");
define("_AC_ACTIVATE_MESSAGE4", "Une fois que les données sont saisies dans votre questionnaire, si vous voulez ajouter ou enlever des groupes ou questions, vous devez désactiver ce questionnaire, ce qui déplacera toutes les données qui ont déjà été saisies dans une table d&#039;archivage séparée.");
define("_AC_ACTIVATE", "Activer");
define("_AC_ACTIVATED", "Le questionnaire a été activé. La table des réponses a été créée avec succés.");
define("_AC_NOTACTIVATED", "Le questionnaire ne peut pas être activé.");
define("_AC_NOTPRIVATE", "Ce n&#039;est pas un questionnaire anonyme. Une table d&#039;invitations doit donc être créée.");
define("_AC_REGISTRATION", "Ce questionnaire permet les enregistrements publics. Une table d&#039;invitations doit aussi être créée."); //New for 0.98finalRC1
define("_AC_CREATETOKENS", "Initialiser les invitations");
define("_AC_SURVEYACTIVE", "Ce questionnaire est maintenant activé, et les réponses peuvent être enregistrées.");
define("_AC_DEACTIVATE_MESSAGE1", "Dans un questionnaire activé, une table est créée pour stocker toutes les données saisies.");
define("_AC_DEACTIVATE_MESSAGE2", "Lorsque vous désactivez un questionnaire, toutes les données saisies dans la table originale seront déplacées ailleurs, ainsi lorsque vous réactiverez le questionnaire la table sera vide. Vous ne pourrez plus accéder à  ces données avec PHPSurveyor.");
define("_AC_DEACTIVATE_MESSAGE3", "Seul un administrateur systéme peut accéder aux données d&#039;un questionnaire désactivé en utilisant un gestionnaire de bases de données MySQL tel que PhpMyAdmin par exemple. Si votre questionnaire utilise des invitations, cette table sera également renommée et seul un administrateur systéme y aura accés.");
define("_AC_DEACTIVATE_MESSAGE4", "Votre table de réponses sera renommée en :");
define("_AC_DEACTIVATE_MESSAGE5", "Vous devriez exporter vos réponses avant de désactiver. Cliquez sur \"Annuler\" pour retourner à l&#039;écran principal d&#039;administration sans désactiver ce questionnaire.");
define("_AC_DEACTIVATE", "Désactiver");
define("_AC_DEACTIVATED_MESSAGE1", "La table réponses a été renommée en : ");
define("_AC_DEACTIVATED_MESSAGE2", "Les réponses à ce questionnaire ne sont plus disponibles via PHPSurveyor.");
define("_AC_DEACTIVATED_MESSAGE3", "Vous devriez noter le nom de cette table dans le cas où vous auriez besoin d&#039;y accéder ultérieurement.");
define("_AC_DEACTIVATED_MESSAGE4", "La table d&#039;invitations liée à ce questionnaire a été renommée en : ");

//CHECKFIELDS
define("_CF_CHECKTABLES", "Vérification pour s&#039;assurer que toutes les tables existent");
define("_CF_CHECKFIELDS", "Vérification pour s&#039;assurer que tous les champs existent");
define("_CF_CHECKING", "Vérification");
define("_CF_TABLECREATED", "Table créée");
define("_CF_FIELDCREATED", "Champ créé");
define("_CF_OK", "OK");
define("_CFT_PROBLEM", "Il semble que quelques tables ou champs soient absents de votre base de données.");

//CREATE DATABASE (createdb.php)
define("_CD_DBCREATED", "Base de données créée.");
define("_CD_POPULATE_MESSAGE", "Veuillez cliquer ci-dessous pour peupler la base de données");
define("_CD_POPULATE", "Peupler la base de données");
define("_CD_NOCREATE", "Impossible de créer la base de données");
define("_CD_NODBNAME", "Les informations de la base de données ne sont pas fournies. Ce script doit être éxécuté à partir d&#039;admin.php seulement.");

//DATABASE MODIFICATION MESSAGES
define("_DB_FAIL_GROUPNAME", "Le groupe ne peut pas être ajouté: nom du groupe obligatoire absent.");
define("_DB_FAIL_GROUPUPDATE", "Le groupe ne peut pas être mis à jour");
define("_DB_FAIL_GROUPDELETE", "Le groupe ne peut pas être supprimé");
define("_DB_FAIL_NEWQUESTION", "La question ne peut pas être crée.");
define("_DB_FAIL_QUESTIONTYPECONDITIONS", "La question ne peut pas être mise à jour. Il y a des conditions pour d&#039;autres questions qui se fondent sur les réponses à cette question et changer le type poserait des problèmes. Vous devez supprimer ces conditions avant de pouvoir changer le type de cette question.");
define("_DB_FAIL_QUESTIONUPDATE", "La question ne peut pas être mise à jour");
define("_DB_FAIL_QUESTIONDELCONDITIONS", "La question ne peut pas être supprimée. Il y a des conditions qui se fondent sur cette question.  Vous ne pourrez pas supprimer cette question tant que ces conditions ne sont pas enlevées");
define("_DB_FAIL_QUESTIONDELETE", "La question ne peut pas être supprimée");
define("_DB_FAIL_NEWANSWERMISSING", "La réponse ne peut pas être ajoutée. Vous devez inclure un code et une réponse");
define("_DB_FAIL_NEWANSWERDUPLICATE", "La réponse ne peut pas être ajoutée. Il y a déjà une réponse avec ce code");
define("_DB_FAIL_ANSWERUPDATEMISSING", "La réponse ne peut pas être mise à jour. Vous devez inclure un code et une réponse");
define("_DB_FAIL_ANSWERUPDATEDUPLICATE", "La réponse ne peut pas être mise à jour. Il y a déjà une réponse avec ce code");
define("_DB_FAIL_ANSWERUPDATECONDITIONS", "La réponse ne peut pas être mise à jour. Vous avez modifié le code de réponse, mais il y a des conditions pour d&#039;autres questions qui dépendent de l&#039;ancien code de réponse de cette question.  Vous devez supprimer ces conditions avant de pouvoir modifier le code de cette réponse.");
define("_DB_FAIL_ANSWERDELCONDITIONS", "La réponse ne peut pas être supprimée. Il y a des conditions pour d&#039;autres questions qui dépendent de cette réponse.  Vous ne pouvez pas supprimer cette réponse jusqu&#039;à ce que ces conditions soient enlevées");
define("_DB_FAIL_NEWSURVEY_TITLE", "Le questionnaire ne peut pas être crée parce qu&#039;il n&#039;a pas de titre court");
define("_DB_FAIL_NEWSURVEY", "Le questionnaire ne peut pas être crée");
define("_DB_FAIL_SURVEYUPDATE", "Le questionnaire ne peut pas être mis à jour");
define("_DB_FAIL_SURVEYDELETE", "Le questionnaire ne peut pas être supprimé");

//DELETE SURVEY MESSAGES
define("_DS_NOSID", "Vous n&#039;avez pas sélectionné de questionnaire à supprimer");
define("_DS_DELMESSAGE1", "Vous êtes sur le point de supprimer ce questionnaire");
define("_DS_DELMESSAGE2", "Cette procédure supprimera ce questionnaire, tous ses groupes associés, ses réponses des questions ainsi que ses conditions.");
define("_DS_DELMESSAGE3", "Il est recommandé avant de supprimer ce questionnaire de l&#039;exporter entiérement à partir de l&#039;écran principal d&#039;administration.");
define("_DS_SURVEYACTIVE", "Ce questionnaire est activé et une table des réponses existe. Si vous supprimez ce questionnaire, ses réponses seront supprimées. Il est recommandé d&#039;exporter les réponses avant de supprimer ce questionnaire.");
define("_DS_SURVEYTOKENS", "Ce questionnaire a une table d&#039;invitations associée. Si vous supprimez ce questionnaire cette table d&#039;invitations sera supprimée. Il est recommandé d&#039;exporter ou de faire une sauvegarde de ces invitations avant de supprimer ce questionnaire.");
define("_DS_DELETED", "Ce questionnaire a été supprimé.");

//DELETE QUESTION AND GROUP MESSAGES
define("_DG_RUSURE", "Supprimer ce groupe supprimera également toutes les questions et réponses qu&#039;il contient. Etes-vous sûr de vouloir continuer ?"); //New for 098rc5
define("_DQ_RUSURE", "Supprimer cette question supprimera également toutes les réponses qu&#039;elle contient. Etes-vous sûr de vouloir continuer ?"); //New for 098rc5

//EXPORT MESSAGES
define("_EQ_NOQID", "Aucun QID n&#039;a été fourni. Impossible d&#039;exporter la question.");
define("_ES_NOSID", "Aucun QID n&#039;a été fourni. Impossible d&#039;exporter le questionnaire.");

//EXPORT RESULTS
define("_EX_FROMSTATS", "Filtré par le script des statistiques");
define("_EX_HEADINGS", "Questions");
define("_EX_ANSWERS", "Réponses");
define("_EX_FORMAT", "Format");
define("_EX_HEAD_ABBREV", "Entête  abrégés");
define("_EX_HEAD_FULL", "Entête complet");
define("_EX_HEAD_CODES", "Codes de questions");
define("_EX_ANS_ABBREV", "Codes des réponses");
define("_EX_ANS_FULL", "Réponses compléte");
define("_EX_FORM_WORD", "Microsoft Word");
define("_EX_FORM_EXCEL", "Microsoft Excel");
define("_EX_FORM_CSV", "CSV-Texte (séparateur : virgule)");
define("_EX_EXPORTDATA", "Exporter les données");
define("_EX_COLCONTROLS", "Titre de la colonne"); //New for 0.98rc7
define("_EX_TOKENCONTROLS", "Contrôle des invitations"); //New for 0.98rc7
define("_EX_COLSELECT", "Choisir les colonnes"); //New for 0.98rc7
define("_EX_COLOK", "Choisissez les colonnes que vous voulez exporter. Ne rien toucher pour les exporter toutes."); //New for 0.98rc7
define("_EX_COLNOTOK", "Votre questionnaire contient plus de 255 colonnes de réponses. Les tableurs comme Excel sont limités à 255. Sélectionnez les colonnes à exporter dans la liste ci-dessous.."); //New for 0.98rc7
define("_EX_TOKENMESSAGE", "Votre questionnaire peut exporter les données des invitations associés avec chaque réponse. Sélectionnez tous les champs additionnels à exporter."); //New for 0.98rc7
define("_EX_TOKSELECT", "Choisir les champs d&#039;invitations"); //New for 0.98rc7

//IMPORT SURVEY MESSAGES
define("_IS_FAILUPLOAD", "Une erreur s&#039;est produite durant la transmission de votre fichier.  Ceci peut être provoqué par des permissions incorrectes dans votre dossier admin.");
define("_IS_OKUPLOAD", "Fichier transmis avec succès.");
define("_IS_READFILE", "Lecture du fichier..");
define("_IS_WRONGFILE", "Ce fichier n&#039;est pas un fichier de questionnaire PHPSurveyor. L&#039;importation a échoué.");
define("_IS_IMPORTSUMMARY", "Résumé de l&#039;importation du questionnaire");
define("_IS_SUCCESS", "L&#039;importation du questionnaire est terminée.");
define("_IS_IMPFAILED", "L&#039;importation de ce fichier questionnaire a échoué");
define("_IS_FILEFAILS", "Mauvais format de données dans le fichier de données PHPSurveyor.");

//IMPORT GROUP MESSAGES
define("_IG_IMPORTSUMMARY", "Résumé de l&#039;importation de groupe");
define("_IG_SUCCESS", "L&#039;importation du groupe est terminée.");
define("_IG_IMPFAILED", "L&#039;importation de ce groupe a échoué");
define("_IG_WRONGFILE", "Ce fichier n&#039;est pas un fichier de groupe PHPSurveyor. L&#039;importation a échoué.");

//IMPORT QUESTION MESSAGES
define("_IQ_NOSID", "Aucun SID (Questionnaire) n&#039;a été fourni. Impossible d&#039;importer une question.");
define("_IQ_NOGID", "Aucun GID (Groupe) n&#039;a été fourni. Impossible d&#039;importer une question.");
define("_IQ_WRONGFILE", "Ce fichier n&#039;est pas un fichier de question PHPSurveyor. L&#039;importation a échoué.");
define("_IQ_IMPORTSUMMARY", "Résumé de l&#039;importation de question");
define("_IQ_SUCCESS", "L&#039;importation de question(s) est terminée");

//IMPORT LABELSET MESSAGES
define("_IL_DUPLICATE", "Il y a un doublon dans les jeux d&#039;étiquettes donc ce jeu n&#039;a pas été importé. Le doublon sera utlisé à la place.");

//BROWSE RESPONSES MESSAGES
define("_BR_NOSID", "Vous n&#039;avez pas sélectionné de questionnaire à parcourir.");
define("_BR_NOTACTIVATED", "Ce questionnaire n&#039;a pas été activé. Aucun résultat à parcourir.");
define("_BR_NOSURVEY", "Il n&#039;y a pas de questionnaire associé.");
define("_BR_EDITRESPONSE", "Editer cette réponse");
define("_BR_DELRESPONSE", "Supprimer cette réponse");
define("_BR_DISPLAYING", "Enregistrements affichés :");
define("_BR_STARTING", "A partir de :");
define("_BR_SHOW", "Afficher");
define("_DR_RUSURE", "Est-vous sûr de vouloir supprimer cette réponse ?"); //New for 0.98rc6

//STATISTICS MESSAGES
define("_ST_FILTERSETTINGS", "Paramètres de filtre");
define("_ST_VIEWALL", "Visualiser le résumé de tous les champs disponibles"); //New with 0.98rc8
define("_ST_SHOWRESULTS", "Visualiser les statistiques"); //New with 0.98rc8
define("_ST_CLEAR", "Effacer la sélection"); //New with 0.98rc8
define("_ST_RESPONECONT", "Réponses contenant "); //New with 0.98rc8
define("_ST_NOGREATERTHAN", "Nombre plus grand que "); //New with 0.98rc8
define("_ST_NOLESSTHAN", "Nombre plus petit que "); //New with 0.98rc8
define("_ST_DATEEQUALS", "Date (AAAA-MM-JJ) égale"); //New with 0.98rc8
define("_ST_ORBETWEEN", "OU entre"); //New with 0.98rc8
define("_ST_RESULTS", "Résultats"); //New with 0.98rc8 (Plural)
define("_ST_RESULT", "Résultat"); //New with 0.98rc8 (Singular)
define("_ST_RECORDSRETURNED", "Nombre d&#039;enregistrement(s) pour ce filtre "); //New with 0.98rc8
define("_ST_TOTALRECORDS", "Nombre total d&#039;enregistrements dans le questionnaire "); //New with 0.98rc8
define("_ST_PERCENTAGE", "Pourcentage du total "); //New with 0.98rc8
define("_ST_FIELDSUMMARY", "Résumé de champs pour"); //New with 0.98rc8
define("_ST_CALCULATION", "Calcul"); //New with 0.98rc8
define("_ST_SUM", "Somme"); //New with 0.98rc8 - Mathematical
define("_ST_STDEV", "Ecart type"); //New with 0.98rc8 - Mathematical
define("_ST_AVERAGE", "Moyenne"); //New with 0.98rc8 - Mathematical
define("_ST_MIN", "Minimum"); //New with 0.98rc8 - Mathematical
define("_ST_MAX", "Maximum"); //New with 0.98rc8 - Mathematical
define("_ST_Q1", "1er Quartile (Q1)"); //New with 0.98rc8 - Mathematical
define("_ST_Q2", "2ème Quartile (Médiane)"); //New with 0.98rc8 - Mathematical
define("_ST_Q3", "3ème Quartile (Q3)"); //New with 0.98rc8 - Mathematical
define("_ST_NULLIGNORED", "*Des valeurs nulles sont ignorées dans les calculs"); //New with 0.98rc8
define("_ST_QUARTMETHOD", "*Q1 et Q3 ont été calculés avec <a href=`http://mathforum.org/library/drmath/view/60969.html` target=`_blank`>la méthode MINITAB</a>"); //New with 0.98rc8

//DATA ENTRY MESSAGES
define("_DE_NOMODIFY", "Ne peut pas être modifié");
define("_DE_UPDATE", "Mettre à jour la réponse");
define("_DE_NOSID", "Vous n&#039;avez pas sélectionné de questionnaire pour la saisie des données.");
define("_DE_NOEXIST", "Le questionnaire que vous avez sélectionné n&#039;éxiste pas");
define("_DE_NOTACTIVE", "Ce questionnaire n&#039;est pas encore activé. Votre réponse ne peut pas être sauvegardée");
define("_DE_INSERT", "Insertion de données");
define("_DE_RECORD", "L&#039;entrée était assignée à l&#039;ID de l&#039;enregistrement suivant : ");
define("_DE_ADDANOTHER", "Ajouter un autre enregistrement");
define("_DE_VIEWTHISONE", "Visualiser cet enregistrement");
define("_DE_BROWSE", "Parcourir les réponses");
define("_DE_DELRECORD", "Enregistrement supprimé");
define("_DE_UPDATED", "L&#039;enregistrement a été mis à jour.");
define("_DE_EDITING", "Editer une réponse");
define("_DE_QUESTIONHELP", "Aide sur cette question");
define("_DE_CONDITIONHELP1", "Répondre seulement à cette question si les conditions suivantes sont réunies :"); 
define("_DE_CONDITIONHELP2", "à la question {QUESTION}, vous avez répondu {ANSWER}"); //This will be a tricky one depending on your languages syntax. {ANSWER} is replaced with ALL ANSWERS, separated by _DE_OR (OR).
define("_DE_AND", "ET (AND)");
define("_DE_OR", "OU (OR)");
define("_DE_SAVEENTRY", "Sauvegarder les réponses partielles au questionnaire"); //New in 0.99dev01
define("_DE_SAVEID", "Identification :"); //New in 0.99dev01
define("_DE_SAVEPW", "Mot de passe :"); //New in 0.99dev01
define("_DE_SAVEPWCONFIRM", "Confirmer le mot de passe :"); //New in 0.99dev01
define("_DE_SAVEEMAIL", "Mail :"); //New in 0.99dev01

//TOKEN CONTROL MESSAGES
define("_TC_TOTALCOUNT", "Nombre total d&#039;enregistrement(s) dans cette table d&#039;invitations :"); //New in 0.98rc4
define("_TC_NOTOKENCOUNT", "Nombre total d&#039;invitations sans code unique :"); //New in 0.98rc4
define("_TC_INVITECOUNT", "Nombre total d&#039;invitations envoyée(s) :"); //New in 0.98rc4
define("_TC_COMPLETEDCOUNT", "Nombre total de questionnaire(s) terminé(s) :"); //New in 0.98rc4
define("_TC_NOSID", "Vous n&#039;avez pas sélectionné de questionnaire");
define("_TC_DELTOKENS", "Au sujet de la suppression de la table des invitations pour ce questionnaire.");
define("_TC_DELTOKENSINFO", "Si vous supprimez cette table, des invitations ne seront plus requises pour accéder à ce questionnaire. Une sauvegarde de cette table sera effectuée si vous la supprimez. Votre administrateur systéme pourra accéder à cette table.");
define("_TC_DELETETOKENS", "Supprimer les invitations");
define("_TC_TOKENSGONE", "La table des invitations a été enlevée maintenant et des invitations ne sont plus requises pour accéder à ce questionnaire. Une sauvegarde de cette table a été effectuée. L&#039;administrateur systéme pourra y accéder.");
define("_TC_NOTINITIALISED", "Aucune invitation n&#039;a été initialisée pour ce questionnaire.");
define("_TC_INITINFO", "Si vous initialisez des invitations pour ce questionnaire, seuls les utilisateurs ayant une invitation pourront y accéder.");
define("_TC_INITQ", "Voulez-vous créer des invitations pour ce questionnaire ?");
define("_TC_INITTOKENS", "Initialiser les invitations");
define("_TC_CREATED", "Une table des invitations a été créée pour ce questionnaire.");
define("_TC_DELETEALL", "Supprimer toutes les invitations");
define("_TC_DELETEALL_RUSURE", "Etes-vous sûr de vouloir supprimer TOUTES les invitations?");
define("_TC_ALLDELETED", "Toutes les invitations ont été supprimées");
define("_TC_CLEARINVITES", "Mettre toutes les invitations à NON envoyées");
define("_TC_CLEARINV_RUSURE", "Etes-vous sûr de vouloir réinitialiser le statut de l&#039;envoi de toutes les invitations à NON envoyées ?");
define("_TC_CLEARTOKENS", "Supprimer tous les codes des invitations");
define("_TC_CLEARTOKENS_RUSURE", "Etes-vous sûr de vouloir supprimer tous les codes des invitations?");
define("_TC_TOKENSCLEARED", "Tous les codes des invitations ont été enlevés");
define("_TC_INVITESCLEARED", "Tous les statuts d&#039;envoi des invitations ont été définis à N");
define("_TC_EDIT", "Editer l&#039;invitation");
define("_TC_DEL", "Supprimer l&#039;invitation");
define("_TC_DO", "Faire le questionnaire");
define("_TC_VIEW", "Voir les réponses");
define("_TC_UPDATE", "Mettre à jour la réponse"); // New with 0.99 stable
define("_TC_INVITET", "Envoyer une invitation par mail à cette entrée");
define("_TC_REMINDT", "Envoyer un rappel par mail à cette entrée");
define("_TC_INVITESUBJECT", "Invitation pour répondre au questionnaire {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSUBJECT", "Rappel pour répondre au questionnaire {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSTARTAT", "Commencer à l&#039;IID (TID) No :");
define("_TC_REMINDTID", "Envoyé à l&#039;IID (TID) No :");
define("_TC_CREATETOKENSINFO", "Cliquer sur OUI va générer des invitations pour ceux de la liste d&#039;invitations qui n&#039;en ont pas reçues. Etes-vous d&#039;accord ?");
define("_TC_TOKENSCREATED", "{TOKENCOUNT} invitations ont été créées"); //Leave {TOKENCOUNT} for replacement in script with the number of tokens created
define("_TC_TOKENDELETED", "Une invitation a été supprimée.");
define("_TC_SORTBY", "Tri par : ");
define("_TC_ADDEDIT", "Ajouter ou éditer une invitation");
define("_TC_TOKENCREATEINFO", "Vous pouvez laisser cela à blanc et générer automatiquement des invitations avec `Générer les codes d&#039;invitations`");
define("_TC_TOKENADDED", "Ajouter une nouvelle invitation");
define("_TC_TOKENUPDATED", "Mise à jour de l&#039;invitation");
define("_TC_UPLOADINFO", "Le fichier doit être un fichier CSV standard (délimiteur: virgule) sans guillements. La premiére ligne doit contenir des informations d&#039;entête (elle sera enlevée). Les données devront être triées par \"Nom, Prénom, mail, [token], [attribute1], [attribute2]\".");
define("_TC_UPLOADFAIL", "Fichier téléchargé non trouvé. Vérifier vos permissions et le chemin du répertoire de téléchargement (upload)"); //New for 0.98rc5
define("_TC_IMPORT", "Importation du fichier CSV");
define("_TC_CREATE", "Création des entrées des invitations");
define("_TC_TOKENS_CREATED", "{TOKENCOUNT} enregistrements crées");
define("_TC_NONETOSEND", "Il n&#039;y avait aucun mail éligible à envoyer : aucun n&#039;a satisfait les critères - mail valide, invitation déjà envoyée, questionnaire déjà completé et invitation obtenue.");
define("_TC_NOREMINDERSTOSEND", "Il n&#039;y avait aucun mail éligible à envoyer : aucun n&#039;a satisfait les critères - mail valide, invitation envoyée mais questionnaire pas encore completé.");
define("_TC_NOEMAILTEMPLATE", "Modèle d&#039;invitation non trouvé. Ce fichier doit exister dans le répertoire  par défaut des modèles (Templates).");
define("_TC_NOREMINDTEMPLATE", "Modèle de rappel non trouvé. Ce fichier doit exister dans le répertoire  par défaut des modèles (Templates).");
define("_TC_SENDEMAIL", "Envoyer invitations");
define("_TC_SENDINGEMAILS", "Envoi invitations");
define("_TC_SENDINGREMINDERS", "Envoi rappels");
define("_TC_EMAILSTOGO", "Il y a plus de mail en suspens qui peuvent être envoyés en groupe (batch). Continuez d&#039;envoyer des mails en cliquant ci-dessous.");
define("_TC_EMAILSREMAINING", "Il y a encore {EMAILCOUNT} à envoyer."); //Leave {EMAILCOUNT} for replacement in script by number of emails remaining
define("_TC_SENDREMIND", "Envoyer rappels");
define("_TC_INVITESENTTO", "Invitation envoyée à :"); //is followed by token name
define("_TC_REMINDSENTTO", "Rappel envoyé à :"); //is followed by token name
define("_TC_UPDATEDB", "Mettre à jour la table d&#039;invitations avec des nouveaux champs"); //New for 0.98rc7
define("_TC_MAILTOFAILED", "Le mail à {FIRSTNAME} {LASTNAME} ({EMAIL})a échoué"); //New for 0.991
define("_TC_EMAILINVITE_SUBJ", "Invitation à participer à un questionnaire"); //New for 0.99dev01
define("_TC_EMAILINVITE", "{FIRSTNAME},\n\nVous avez été invité à participer à un questionnaire.\n\n"
                         ."Celui-ci est intitulé:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
                         ."Pour participer, veuillez cliquer sur le lien ci-dessous.\n\nCordialement,\n\n"
                         ."{ADMINNAME} ({ADMINEMAIL})\n\n"
                         ."----------------------------------------------\n"
                         ."Cliquer ici pour faire le questionnaire :\n"
                         ."{SURVEYURL}"); //New for 0.98rc9 - Email d`Invitation par défaut
define("_TC_EMAILREMIND_SUBJ", "Rappel pour répondre à un questionnaire"); //New for 0.99dev01
define("_TC_EMAILREMIND", "{FIRSTNAME},\n\nVous avez été invité à participer à un questionnaire récemment.\n\n"
                         ."Nous avons pris en compte que vous n&#039;avez pas encore completé le questionnaire, et nous vous rappelons que celui-ci est toujours disponible si vous souhaitez participer.\n\n"
                         ."Le questionnaire est intitulé:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
                         ."Pour participer, veuillez cliquer sur le lien ci-dessous.\n\nCordialement,\n\n"
                         ."{ADMINNAME} ({ADMINEMAIL})\n\n"
                         ."----------------------------------------------\n"
                         ."Cliquez ici pour faire le questionnaire:\n"
                         ."{SURVEYURL}"); //New for 0.98rc9 - Email de rappel par defaut
define("_TC_EMAILREGISTER_SUBJ", "Confirmation de l'enregistrement de la participation au questionnaire"); //New for 0.99dev01
define("_TC_EMAILREGISTER", "{FIRSTNAME},\n\n"
                          ."Vous (ou quelqu&#039;un utilisant votre adresse mail) êtes enregistré pour "
                          ."participer à un questionnaire en ligne intitulé:\n\"{SURVEYNAME}\"\n\n"
                          ."Pour compléter ce questionnaire, cliquez sur le lien suivant:\n\n"
                          ."{SURVEYURL}\n\n"
                          ."Quel que soit votre question à propos de ce questionnaire, ou si vous "
                          ."ne vous êtes pas enregistré pour participer à celui-ci et croyez qu&#039;il s&#039;agit "
                          ."d&#039;une erreur, veuillez contacter {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9
define("_TC_EMAILCONFIRM_SUBJ", "Confirmation de réponse à un questionnaire"); //New for 0.99dev01
define("_TC_EMAILCONFIRM", "{FIRSTNAME},\n\nCe mail vous confirme que vous avez completé le questionnaire intitulé {SURVEYNAME} "
                          ."et que votre réponse à été enregistrée. Merci.\n\n"
                          ."Si vous avez des questions à propos de ce mail, veuillez contacter {ADMINNAME} ({ADMINEMAIL}).\n\n"
                          ."Cordialement,\n\n"
                          ."{ADMINNAME}"); //New for 0.98rc9 - Confirmation Email

//labels.php
define("_LB_NEWSET", "Créer un nouveau jeu d&#039;étiquettes");
define("_LB_EDITSET", "Editer un jeu d&#039;étiquettes");
define("_LB_FAIL_UPDATESET", "La mise à jour du jeu d&#039;étiquettes a échoué");
define("_LB_FAIL_INSERTSET", "L&#039;insertion du nouveau jeu d&#039;étiquettes a échoué");
define("_LB_FAIL_DELSET", "Impossible de supprimer le jeu d&#039;étiquettes - Il y a des questions qui y sont reliées. Vous devez supprimer ces questions en premier.");
define("_LB_ACTIVEUSE", "Vous ne pouvez pas changer des codes, ajouter ou supprimer des entrées dans ce jeu d&#039;étiquettes parce que celles-ci sont utilisées par un questionnaire activé.");
define("_LB_TOTALUSE", "Quelques questionnaires utilisent actuellement ce jeu d&#039;étiquettes. Modifier les codes, ajouter ou supprimer des entrées de ce jeu pourrait entrainer des effets indésirables dans d&#039;autres questionnaires.");
//Export Labels
define("_EL_NOLID", "Aucun JID (LID) fourni. Impossible d&#039;exporter ce jeu d&#039;étiquettes.");
//Import Labels
define("_IL_GOLABELADMIN", "Retour à l&#039;administration des jeux d&#039;etiquettes");

//PHPSurveyor System Summary
define("_PS_TITLE", "Résumé système PHPSurveyor");
define("_PS_DBNAME", "Nom de la base de données");
define("_PS_DEFLANG", "Langue par défaut");
define("_PS_CURLANG", "Langue courante");
define("_PS_USERS", "Utilisateurs");
define("_PS_ACTIVESURVEYS", "Questionnaire(s) activé(s)");
define("_PS_DEACTSURVEYS", "Questionnaire(s) désactivé(s)");
define("_PS_ACTIVETOKENS", "Table(s) d&#039;invitation(s) activée(s)");
define("_PS_DEACTTOKENS", "Table(s) d&#039;invitation(s) désactivée(s)");
define("_PS_CHECKDBINTEGRITY", "Vérifier l&#039;intégrité des données de PHPSurveyor"); //New with 0.98rc8

//Notification Levels
define("_NT_NONE", "Aucune notification par mail"); //New with 098rc5
define("_NT_SINGLE", "Notification simple par mail"); //New with 098rc5
define("_NT_RESULTS", "Notification par mail avec les codes des réponses"); //New with 098rc5

//CONDITIONS TRANSLATIONS
define("_CD_CONDITIONDESIGNER", "Concepteur de conditions"); //New with 098rc9
define("_CD_ONLYSHOW", "Montrer la question {QID} seulement SI (IF)"); //New with 098rc9 - {QID} is repleaced leave there
define("_CD_AND", "ET (AND)"); //New with 098rc9
define("_CD_COPYCONDITIONS", "Copier les conditions"); //New with 098rc9
define("_CD_CONDITION", "Condition"); //New with 098rc9
define("_CD_ADDCONDITION", "Ajouter une condition"); //New with 098rc9
define("_CD_EQUALS", "Egales"); //New with 098rc9
define("_CD_COPYRUSURE", "Etes-vous sûr de vouloir copier cette(ces) condition(s) aux questions sélectionnées?"); //New with 098rc9
define("_CD_NODIRECT", "Vous ne pouvez pas éxecuter directement ce script."); //New with 098rc9
define("_CD_NOSID", "Vous n&#039;avez pas sélectionné de questionnaire."); //New with 098rc9
define("_CD_NOQID", "Vous n&#039;avez pas sélectionné de question."); //New with 098rc9
define("_CD_DIDNOTCOPYQ", "Questions non copiées"); //New with 098rc9
define("_CD_NOCONDITIONTOCOPY", "Aucune condition à copier sélectionnée"); //New with 098rc9
define("_CD_NOQUESTIONTOCOPYTO", "Aucune question sélectionnée pour copier la condition à "); //New with 098rc9
define("_CD_COPYTO", "copier à "); //New with 0.991

//TEMPLATE EDITOR TRANSLATIONS
define("_TP_CREATENEW", "Créer un nouveau modèle"); //New with 098rc9
define("_TP_NEWTEMPLATECALLED", "Créer un nouveau modèle nommé :"); //New with 098rc9
define("_TP_DEFAULTNEWTEMPLATE", "Nouveau modèle"); //New with 098rc9 (default name for new template)
define("_TP_CANMODIFY", "Ce modèle peut être modifié"); //New with 098rc9
define("_TP_CANNOTMODIFY", "Ce modèle ne peut pas être modifié"); //New with 098rc9
define("_TP_RENAME", "Renommer ce modèle");  //New with 098rc9
define("_TP_RENAMETO", "Renommer ce modèle en :"); //New with 098rc9
define("_TP_COPY", "Faire une copie de ce modèle");  //New with 098rc9
define("_TP_COPYTO", "Créer une copie de ce modèle nommé :"); //New with 098rc9
define("_TP_COPYOF", "copie_de_"); //New with 098rc9 (prefix to default copy name)
define("_TP_FILECONTROL", "Contrôle des fichiers :"); //New with 098rc9
define("_TP_STANDARDFILES", "Fichiers standards :");  //New with 098rc9
define("_TP_NOWEDITING", "Edition en cours :");  //New with 098rc9
define("_TP_OTHERFILES", "Autres fichiers :"); //New with 098rc9
define("_TP_PREVIEW", "Aperçu :"); //New with 098rc9
define("_TP_DELETEFILE", "Supprimer"); //New with 098rc9
define("_TP_UPLOADFILE", "Télécharger (Upload)"); //New with 098rc9
define("_TP_SCREEN", "Ecran :"); //New with 098rc9
define("_TP_WELCOMEPAGE", "Page d&#039;accueil"); //New with 098rc9
define("_TP_QUESTIONPAGE", "Page de question"); //New with 098rc9
define("_TP_SUBMITPAGE", "Envoyer la page");
define("_TP_COMPLETEDPAGE", "Page complétée"); //New with 098rc9
define("_TP_CLEARALLPAGE", "Effacer toute la page"); //New with 098rc9
define("_TP_REGISTERPAGE", "Enregistrer la page"); //New with 098finalRC1
define("_TP_EXPORT", "Exporter le modèle"); //New with 098rc10
define("_TP_LOADPAGE", "Charger la page"); //New with 0.99dev01
define("_TP_SAVEPAGE", "Sauvegarder la page"); //New with 0.99dev01

//Saved Surveys
define("_SV_RESPONSES", "Réponse(s) sauvegardée(s) :");
define("_SV_IDENTIFIER", "Identification");
define("_SV_RESPONSECOUNT", "Nombre de réponses déjà données");
define("_SV_IP", "Adresse IP");
define("_SV_DATE", "Date de sauvegarde");
define("_SV_REMIND", "Rappel");
define("_SV_EDIT", "Editer");

//VVEXPORT/IMPORT
define("_VV_IMPORTFILE", "Importer un fichier VV");
define("_VV_EXPORTFILE", "Exporter vers un fichier VV");
define("_VV_FILE", "Fichier :");
define("_VV_SURVEYID", "ID du questionnaire :");
define("_VV_EXCLUDEID", "Exclure les ID enregistrés ?");
define("_VV_INSERT", "Quand un enregistrement importé correspond à un enregistrement existant (ID): ");
define("_VV_INSERT_ERROR", "Reporter une erreur (et sauter le nouvel enregistrement).");
define("_VV_INSERT_RENUMBER", "Renuméroter le nouvel enregistrement.");
define("_VV_INSERT_IGNORE", "Ignorer le nouvel enregistrement.");
define("_VV_INSERT_REPLACE", "Remplacer l&#039;enregistement existant.");
define("_VV_DONOTREFRESH", "Note importante:<br />Ne pas ACTUALISER cette page sous peine d&#039;importer de nouveau le fichier et de créer des doublons");
define("_VV_IMPORTNUMBER", "Nombre d&#039;enregistrement(s) importé(s) :");
define("_VV_ENTRYFAILED", "Echec de l&#039;importation sur l&#039;enregistrement");
define("_VV_BECAUSE", "parce que");
define("_VV_EXPORTDEACTIVATE", "Exporter et ensuite désactiver le questionnaire");
define("_VV_EXPORTONLY", "Exporter mais laisser le questionnaire activé");
define("_VV_RUSURE", "Si vous choisissez d&#039;exporter et de désactiver le questionnaire, cela renommera votre table de réponses et cela ne sera pas facile de la restaurer. Etes-vous sûr ?");

//SPSS Export
define("_SPSS_EXPORTFILE", "Exporter les résultats vers un fichier de commandes SPSS");

//ASSESSMENTS
define("_AS_TITLE", "Evaluations");
define("_AS_DESCRIPTION", "Si vous créez des évaluations sur cette page, pour le questionnaire sélectionné, celles-ci auront lieu à la fin du questionnaire après l&#039;envoi définitif des réponses");
define("_AS_NOSID", "Pas de SID (ID de questionnaire) fourni");
define("_AS_SCOPE", "Portée");
define("_AS_MINIMUM", "Minimum");
define("_AS_MAXIMUM", "Maximum");
define("_AS_GID", "Groupe");
define("_AS_NAME", "Nom/Entête");
define("_AS_HEADING", "Titre");
define("_AS_MESSAGE", "Message");
define("_AS_URL", "URL");
define("_AS_SCOPE_GROUP", "Groupes");
define("_AS_SCOPE_TOTAL", "Questionnaire complet");
define("_AS_ACTIONS", "Actions");
define("_AS_EDIT", "Editer");
define("_AS_DELETE", "Effacer");
define("_AS_ADD", "Ajouter");
define("_AS_UPDATE", "Mettre à jour");

//Question Number regeneration
define("_RE_REGENNUMBER", "Régénération de la numérotation des questions :"); //NEW for release 0.99dev2
define("_RE_STRAIGHT", "Complet"); //NEW for release 0.99dev2
define("_RE_BYGROUP", "Par groupe"); //NEW for release 0.99dev2

// Database Consistency Check
define ("_DC_TITLE", "Contrôle de cohérence de données<br /><font size='1'>Si des erreurs apparaissent, il faudra relancer ce script plusieurs fois. </font>"); // New with 0.99stable
define ("_DC_QUESTIONSOK", "Toutes les questions sont cohérentes"); // New with 0.99stable
define ("_DC_ANSWERSOK", "Toutes les réponses sont cohérentes"); // New with 0.99stable
define ("_DC_CONDITIONSSOK", "Toutes les conditions sont cohérentes"); // New with 0.99stable
define ("_DC_GROUPSOK", "Tous les groupes sont cohérents"); // New with 0.99stable
define ("_DC_NOACTIONREQUIRED", "Pas d&#039;action à faire sur la base de données"); // New with 0.99stable
define ("_DC_QUESTIONSTODELETE", "Les questions suivantes peuvent être effacées"); // New with 0.99stable
define ("_DC_ANSWERSTODELETE", "Les réponses suivantes peuvent être effacées"); // New with 0.99stable
define ("_DC_CONDITIONSTODELETE", "Les conditions suivantes peuvent être effacées"); // New with 0.99stable
define ("_DC_GROUPSTODELETE", "Les groupes suivants peuvent être effacés"); // New with 0.99stable
define ("_DC_ASSESSTODELETE", "Les évaluations suivantes peuvent être effacées"); // New with 0.99stable
define ("_DC_QATODELETE", "Les attributs de question qui suivent peuvent être effacés"); // New with 0.99stable
define ("_DC_QAOK", "Les attributs des questions sont cohérents"); // New with 0.99stable
define ("_DC_ASSESSOK", "Toutes les évaluations sont cohérentes"); // New with 0.99stable

// Import old Responses dialogue

define ("_IORD_TITLE", "Importation de la table des réponses d&#039;un ancien (désactivé) questionnaire dans un questionnaire activé"); // New with 0.991stable
define ("_IORD_TARGETID", "ID du questionnaire cible"); // New with 0.991stable
define ("_IORD_BTIMPORT", "Importer les réponses"); // New with 0.991stable
?>
