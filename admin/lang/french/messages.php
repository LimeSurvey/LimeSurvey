<?php


//BUTTON BAR TITLES
define("_ADMINISTRATION", "Administration");
define("_SURVEY", "Questionnaire");
define("_GROUP", "Groupe");
define("_QUESTION", "Question");
define("_ANSWERS", "Réponses");
define("_CONDITIONS", "Conditions");
define("_HELP", "Aide");
define("_USERCONTROL", "Controle Utilisateur");
define("_ACTIVATE", "Activer le Questionnaire");
define("_DEACTIVATE", "Désactiver le Questionnaire");
define("_CHECKFIELDS", "Contrôle des Champs de la Base de données");
define("_CREATEDB", "Créer la Base de données");
define("_CREATESURVEY", "Créer le Questionnaire"); //New for 0.98rc4
define("_SETUP", "Paramètres de PHPSurveyor");
define("_DELETESURVEY", "Supprimer le Questionnaire");
define("_EXPORTQUESTION", "Exporter la Question");
define("_EXPORTSURVEY", "Exporter le Questionnaire");
define("_EXPORTLABEL", "Exporter le jeu d'Etiquettes");
define("_IMPORTQUESTION", "Importer la Question");
define("_IMPORTGROUP", "Importer le Groupe"); //New for 0.98rc5
define("_IMPORTSURVEY", "Importer le Questionnaire");
define("_IMPORTLABEL", "Importer le jeu d'Etiquette");
define("_EXPORTRESULTS", "Exporter les réponses");
define("_BROWSERESPONSES", "Parcourir les réponses");
define("_BROWSESAVED", "Browse Saved Responses");
define("_STATISTICS", "Statistiques flash");
define("_VIEWRESPONSE", "Voir Réponse");
define("_VIEWCONTROL", "Contrôle de la Visualisation des données");
define("_DATAENTRY", "Entrée données");
define("_TOKENCONTROL", "Contrôle Invitation");
define("_TOKENDBADMIN", "Options d'administration de la Base de données Invitation");
define("_DROPTOKENS", "Suppression de la Table des Invitations");
define("_EMAILINVITE", "Invitation par EMail");
define("_EMAILREMIND", "Rappel Email");
define("_TOKENIFY", "Créer les Invitations");
define("_UPLOADCSV", "Uploader le fichier CSV");
define("_LABELCONTROL", "Administration des jeux d'Etiquettes"); //NEW with 0.98rc3
define("_LABELSET", "Jeu d'Etiquette"); //NEW with 0.98rc3
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
define("_A_HOME_BT", "Page dAdministration par Défaut");
define("_A_SECURITY_BT", "Modifier les paramètres de Sécurité");
define("_A_BADSECURITY_BT", "Activer la Sécurité");
define("_A_CHECKDB_BT", "Vérifier la Base de Données");
define("_A_DELETE_BT", "Supprimer tout le Questionnaire");
define("_A_ADDSURVEY_BT", "Créer ou Importer un Nouveau Questionnaire");
define("_A_HELP_BT", "Aide");
define("_A_CHECKSETTINGS", "Vérifier les Paramètres");
define("_A_BACKUPDB_BT", "Backup Entire Database"); //New for 0.98rc10
define("_A_TEMPLATES_BT", "Editeur de Modèles"); //New for 0.98rc9
//Survey bar
define("_S_ACTIVE_BT", "Ce Questionnaire est actuellement activé");
define("_S_INACTIVE_BT", "Ce Questionnaire est actuellement Désactivé");
define("_S_ACTIVATE_BT", "Activer ce Questionnaire");
define("_S_DEACTIVATE_BT", "Désactiver cet Questionnaire");
define("_S_CANNOTACTIVATE_BT", "Impossible d'activer ce Questionnaire");
define("_S_DOSURVEY_BT", "Exécuter (tester) le Questionnaire");
define("_S_DATAENTRY_BT", "Ecran de Saisie de Données pour le Questionnaire");
define("_S_PRINTABLE_BT", "Version imprimable du Questionnaire");
define("_S_EDIT_BT", "Editer le Questionnaire Courant");
define("_S_DELETE_BT", "Supprimer le Questionnaire Courant");
define("_S_EXPORT_BT", "Exporter ce Questionnaire");
define("_S_BROWSE_BT", "Parcourir les Réponses pour ce Questionnaire");
define("_S_TOKENS_BT", "Activer/Editer les Invitations pour ce Questionnaire");
define("_S_ADDGROUP_BT", "Ajouter un Nouveau Groupe au Questionnaire");
define("_S_MINIMISE_BT", "Masquer les Détails de ce Questionnaire");
define("_S_MAXIMISE_BT", "Afficher les Détails de ce Questionnaire");
define("_S_CLOSE_BT", "Fermer ce Questionnaire");
define("_S_SAVED_BT", "View Saved but not submitted Responses"); //New in 0.99dev01
define("_S_ASSESSMENT_BT", "Set assessment rules"); //New in  0.99dev01
//Group bar
define("_G_EDIT_BT", "Editer le Groupe en Cours");
define("_G_EXPORT_BT", "Exporter le Groupe en Cours"); //New in 0.98rc5
define("_G_DELETE_BT", "Supprimer le Groupe en Cours");
define("_G_ADDQUESTION_BT", "Ajouter une nouvelle Question au Groupe");
define("_G_MINIMISE_BT", "Masquer les Détails de ce Groupe");
define("_G_MAXIMISE_BT", "Afficher les Détails de ce Groupe");
define("_G_CLOSE_BT", "Fermer ce Groupe");
//Question bar
define("_Q_EDIT_BT", "Editer la Question en cours");
define("_Q_COPY_BT", "Copier la Question en Cours"); //New in 0.98rc4
define("_Q_DELETE_BT", "Supprimer la Question en Cours");
define("_Q_EXPORT_BT", "Exporter cette Question");
define("_Q_CONDITIONS_BT", "Affecter des Conditions pour Cette Question");
define("_Q_ANSWERS_BT", "Editer/Ajouter des Réponses pour cette Question");
define("_Q_LABELS_BT", "Edit./Aj. jeux Etiquette");
define("_Q_MINIMISE_BT", "Masquer les Détails de cette Question");
define("_Q_MAXIMISE_BT", "Afficher les Détails de cette Question");
define("_Q_CLOSE_BT", "Fermer cette Question");
//Browse Button Bar
define("_B_ADMIN_BT", "Retourner à l'Administration du Questionnaire");
define("_B_SUMMARY_BT", "Montrer l'Information du Sommaire");
define("_B_ALL_BT", "Afficher les Réponses");
define("_B_LAST_BT", "Afficher les 50 dernières Réponses");
define("_B_STATISTICS_BT", "Donner les Statistiques de ces Réponses");
define("_B_EXPORT_BT", "Exporter les Résultats vers une Application");
define("_B_BACKUP_BT", "Sauvegarder vers un fichier SQL la Table de Résultats");
//Tokens Button Bar
define("_T_ALL_BT", "Afficher les Invitations");
define("_T_ADD_BT", "Ajouter une nouvelle entrée/Invitation");
define("_T_IMPORT_BT", "Importer des Invitations à partir d'un Fichier CSV");
define("_T_EXPORT_BT", "Exporter des Invitations vers un Fichier CSV"); //New for 0.98rc7
define("_T_INVITE_BT", "Envoyer une invitation par Email");
define("_T_REMIND_BT", "Envoyer un rappel par EMail");
define("_T_TOKENIFY_BT", "Générer des Invitations");
define("_T_KILL_BT", "Effacer la table des Invitations");
//Labels Button Bar
define("_L_ADDSET_BT", "Ajouter un Nouveau jeu d'Etiquette");
define("_L_EDIT_BT", "Editer un jeu d'Etiquette");
define("_L_DEL_BT", "Supprimer un Jeu d'Etiquette");
//Datacontrols
define("_D_BEGIN", "Montrer le Début..");
define("_D_BACK", "Montrer le Précédant..");
define("_D_FORWARD", "Montrer le Suivant..");
define("_D_END", "Montrer la Fin..");

//DATA LABELS
//surveys
define("_SL_TITLE", "Titre:");
define("_SL_SURVEYURL", "URL du Questionnaire:"); //new in 0.98rc5
define("_SL_DESCRIPTION", "Description:");
define("_SL_WELCOME", "Bienvenue:");
define("_SL_ADMIN", "Administrateur:");
define("_SL_EMAIL", "Email de l'Administrateur:");
define("_SL_FAXTO", "Fax à:");
define("_SL_ANONYMOUS", "Anonyme?");
define("_SL_EXPIRES", "Expire:");
define("_SL_FORMAT", "Format:");
define("_SL_DATESTAMP", "Date Stamp?");
define("_SL_TEMPLATE", "Modèle:");
define("_SL_LANGUAGE", "Langue:");
define("_SL_LINK", "Lien:");
define("_SL_URL", "URL de Fin:");
define("_SL_URLDESCRIP", "Description de l'URL:");
define("_SL_STATUS", "Status:");
define("_SL_SELSQL", "Sélectionner un fichier SQL:");
define("_SL_USECOOKIES", "Utiliser des Cookies?"); //NEW with 098rc3
define("_SL_NOTIFICATION", "Notification:"); //New with 098rc5
define("_SL_ALLOWREGISTER", "Permettre l'enregistrement publique?"); //New with 0.98rc9
define("_SL_ATTRIBUTENAMES", "Noms Attribué àl'Invitation:"); //New with 0.98rc9
define("_SL_EMAILINVITE", "Invitation par Email:"); //New with 0.98rc9
define("_SL_EMAILREMIND", "Rappel par Email:"); //New with 0.98rc9
define("_SL_EMAILREGISTER", "Enregistrement de l'Email Publique:"); //New with 0.98rc9
define("_SL_EMAILCONFIRM", "Confirmation par Email"); //New with 0.98rc9
define("_SL_REPLACEOK", "Cela remplacera le texte existant. Continuer?"); //New with 0.98rc9
define("_SL_ALLOWSAVE", "Allow Saves?"); //New with 0.99dev01
define("_SL_AUTONUMBER", "Start ID numbers at:"); //New with 0.99dev01
define("_SL_AUTORELOAD", "Automatically load URL when survey complete?"); //New with 0.99dev01

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
define("_QL_LABELSET", "Jeu d'Etiquette:");
define("_QL_COPYANS", "Copier les Réponses?"); //New in 0.98rc3
define("_QL_QUESTIONATTRIBUTES", "Question Attributes:"); //New in 0.99dev01
define("_QL_COPYATT", "Copy Attributes?"); //New in 0.99dev01
//answers
define("_AL_CODE", "Code");
define("_AL_ANSWER", "Réponse");
define("_AL_DEFAULT", "Defaut");
define("_AL_MOVE", "Déplacer");
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
define("_UL_TURNOFF", "Désactiver la Protection");
//tokens
define("_TL_FIRST", "Prénom");
define("_TL_LAST", "Nom");
define("_TL_EMAIL", "Email");
define("_TL_TOKEN", "Invitation");
define("_TL_INVITE", "Envoyer l'Invitation?");
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
define("_LIST", "Liste (Radio)"); //Changed with 0.99dev01
define("_LIST_DROPDOWN", "Liste (Dropdown)"); //New with 0.99dev01
define("_LISTWC", "Liste déroulante avec Commentaire");
define("_MULTO", "Case à Cocher");
define("_MULTOC", "Case à Cocher avec Commentaires");
define("_MULTITEXT", "Zones de Texte Court");
define("_NUMERICAL", "Entrée Numérique");
define("_RANK", "Classement");
define("_STEXT", "Texte Libre Court");
define("_LTEXT", "Texte Libre Long");
define("_HTEXT", "Huge free text"); //New with 0.99dev01
define("_YESNO", "Oui/Non");
define("_ARR5", "Ligne de 5 Boutons Radio");
define("_ARR10", "Ligne de 10 Boutons Radio");
define("_ARRYN", "Ligne (Oui/Non/Incertain)");
define("_ARRMV", "Ligne (Augmenter, Sans changement, Diminuer)");
define("_ARRFL", "Ligne de Bouton Radio (Etiquettes Personnalisées)"); //Release 0.98rc3
define("_ARRFLC", "Ligne de Bouton Radio (Etiquettes Personnalisées en colonne"); //Release 0.98rc8
define("_SINFL", "Simple (Etiquettes Personnalisées)"); //(FOR LATER RELEASE)
define("_EMAIL", "Adresse Email"); //FOR LATER RELEASE
define("_BOILERPLATE", "Texte Fixe"); //New in 0.98rc6
define("_LISTFL_DROPDOWN", "List (Flexible Labels) (Dropdown)"); //New in 0.99dev01
define("_LISTFL_RADIO", "List (Flexible Labels) (Radio)"); //New in 0.99dev01

//GENERAL WORDS AND PHRASES
define("_AD_YES", "Oui");
define("_AD_NO", "Non");
define("_AD_CANCEL", "Annuler");
define("_AD_CHOOSE", "Sélectionner..");
define("_AD_OR", "OU"); //New in 0.98rc4
define("_ERROR", "Erreur");
define("_SUCCESS", "Succés");
define("_REQ", "*Requis");
define("_ADDS", "Ajouter un Questionnaire");
define("_ADDG", "Ajouter un Groupe");
define("_ADDQ", "Ajouter une Question");
define("_ADDA", "Ajouter une Réponse"); //New in 0.98rc4
define("_COPYQ", "Copier une Question"); //New in 0.98rc4
define("_ADDU", "Ajouter un utilisateurr");
define("_SEARCH", "Chercher"); //New in 0.98rc4
define("_SAVE", "Sauver les Modifications");
define("_NONE", "Rien"); //as in "Do not display anything", "or none chosen";
define("_GO_ADMIN", "Ecran Principal d'Admin"); //text to display to return/display main administration screen
define("_CONTINUE", "Continuer");
define("_WARNING", "Avertissement");
define("_USERNAME", "Nom d'Utilisateur");
define("_PASSWORD", "Mot de Passe");
define("_DELETE", "Supprimer");
define("_CLOSEWIN", "Fermer la Fenêtre");
define("_TOKEN", "Invitation");
define("_DATESTAMP", "Date de Réponse"); //Referring to the datestamp or time response submitted
define("_COMMENT", "Commentaire");
define("_FROM", "De"); //For emails
define("_SUBJECT", "Objet"); //For emails
define("_MESSAGE", "Message"); //For emails
define("_RELOADING", "Actualiser l'Ecran. Veuillez patienter.");
define("_ADD", "Ajouter");
define("_UPDATE", "Mise à Jour");
define("_BROWSE", "Parcourir"); //New in 098rc5
define("_AND", "et"); //New with 0.98rc8
define("_SQL", "SQL"); //New with 0.98rc8
define("_PERCENTAGE", "Pourcentage"); //New with 0.98rc8
define("_COUNT", "Decompte"); //New with 0.98rc8

//SURVEY STATUS MESSAGES (new in 0.98rc3)
define("_SS_NOGROUPS", "Nombre de Groupes dans le Questionnaire:"); //NEW for release 0.98rc3
define("_SS_NOQUESTS", "Nombre de Questions dans le Questionnaire:"); //NEW for release 0.98rc3
define("_SS_ANONYMOUS", "Ce Questionnaire est Anonyme."); //NEW for release 0.98rc3
define("_SS_TRACKED", "Ce questionnaire n'EST PAS anonyme."); //NEW for release 0.98rc3
define("_SS_DATESTAMPED", "Les Réponses auront une date de Cloture"); //NEW for release 0.98rc3
define("_SS_COOKIES", "Utilisation des Cookies pour le Contrôle d'Accées."); //NEW for release 0.98rc3
define("_SS_QBYQ", "Présentation: une question par Page."); //NEW for release 0.98rc3
define("_SS_GBYG", "Présentation: un Groupe de Questions par Page."); //NEW for release 0.98rc3
define("_SS_SBYS", "Présentation: une Page Simple."); //NEW for release 0.98rc3
define("_SS_ACTIVE", "Questionnaire en Cours."); //NEW for release 0.98rc3
define("_SS_NOTACTIVE", "Questionnaire Inactif."); //NEW for release 0.98rc3
define("_SS_SURVEYTABLE", "Nom de la Table du Questionnaire:"); //NEW for release 0.98rc3
define("_SS_CANNOTACTIVATE", "Impossible d'Activer le Questionnaire Maintenant."); //NEW for release 0.98rc3
define("_SS_ADDGROUPS", "Vous devez Ajouter des Groupes"); //NEW for release 0.98rc3
define("_SS_ADDQUESTS", "Vous devez Ajouter des Questions"); //NEW for release 0.98rc3
define("_SS_ALLOWREGISTER", "Si les Invitations sont utilisées, les destinataires doivent être enregistrés pour ce Questionnaire"); //NEW for release 0.98rc9
define("_SS_ALLOWSAVE", "Participants can save partially finished surveys"); //NEW for release 0.99dev01

//QUESTION STATUS MESSAGES (new in 0.98rc4)
define("_QS_MANDATORY", "Question Obligatoire"); //New for release 0.98rc4
define("_QS_OPTIONAL", "Question optionnelle"); //New for release 0.98rc4
define("_QS_NOANSWERS", "Vous devez ajouter des réponses à cette Question"); //New for release 0.98rc4
define("_QS_NOLID", "Vous devez choisir un jeu d'Etiquettes pour cette Question"); //New for release 0.98rc4
define("_QS_COPYINFO", "Note: vous devez OBLIGATOIREMENT saisir un nouveau Code pour la Question"); //New for release 0.98rc4

//General Setup Messages
define("_ST_NODB1", "La Base de Données du Questionnaire Définit n'existe pas");
define("_ST_NODB2", "Soit votre Base de Données n'a pas été crée, soit il y a un probléme pour y accéder.");
define("_ST_NODB3", "PHPSurveyor peut tenter de créer la Base de Données pour vous.");
define("_ST_NODB4", "Le Nom de votre Base de Données Sélectionnée est:");
define("_ST_CREATEDB", "Créer la Base de Données");

//USER CONTROL MESSAGES
define("_UC_CREATE", "Créer le fichier htaccess par defaut");
define("_UC_NOCREATE", "Impossible de Créer le fichier htaccess. Vérifiez votre config.php sous \$homedir, et que vous avez les permissions d'écriture dans le bon répertoire.");
define("_UC_SEC_DONE", "Le niveau de sécurité est maintenant configuré!");
define("_UC_CREATE_DEFAULT", "Créer les utilisateurs par Défaut");
define("_UC_UPDATE_TABLE", "Mise à jour de la table des Utilisateurs (users)");
define("_UC_HTPASSWD_ERROR", "Une erreure s'est produite lors de la création du fichier htpasswd");
define("_UC_HTPASSWD_EXPLAIN", "Si vous utilisez un serveur Windows il est recommandé de copier le fichier apache sous votre répertoire d'administration pour que cette fonction fonctionne correctement. Ce fichier se trouve généralement sous /apache group/apache/bin/");
define("_UC_SEC_REMOVE", "Enlever les paramétres de Sécurité");
define("_UC_ALL_REMOVED", "Access file, password file and user database deleted");
define("_UC_ADD_USER", "Ajout d'utilisateur");
define("_UC_ADD_MISSING", "Impossible d'ajouter un utilisateur. Le Nom d'utilisateur et/ou le mot de passe n'étaient pas renseignés");
define("_UC_DEL_USER", "Supprimer l'utilisateur");
define("_UC_DEL_MISSING", "Impossible de supprimer l'utilisateur. Le Nom d'utilisateur n'était pas remplis.");
define("_UC_MOD_USER", "Modification de l'utilisateur");
define("_UC_MOD_MISSING", "Impossible de modifier l'utilisateur. Le Nom d'utilisateur et/ou le mot de passe n'étaient pas renseignés");
define("_UC_TURNON_MESSAGE1", "Vous n'avez pas encore initialisés les paramétres de sécurité pour votre systéme de Questionnaire et en conséquence il n'y a pas de restrictions d'accés.</p>\nSi vous cliquez sur le bouton 'initialiser la Sécurité' ci-dessous, les paramétres de sécurité standard d'Apache seront ajoutés au répertoire d'administration de ce script. Vous aurez alors besoin d'utiliser le Nom d'utilisateur et le mot de passe par défaut pour accéder à l'Administration et aux scripts de saisie de données.");
define("_UC_TURNON_MESSAGE2", "Il est fortement recommandé, qu'une fois votre systéme de sécurité initialisé, de changer le mot de passe par défaut.");
define("_UC_INITIALISE", "Initialiser la Securité");
define("_UC_NOUSERS", "Aucun utilisateur dans la table. Nous vous recommandons de 'désactiver' la sécurité ET de la 'réactiver' ensuite.");
define("_UC_TURNOFF", "Désactiver la sécurité");

//Activate and deactivate messages
define("_AC_MULTI_NOANSWER", "Cette question est à réponses multiples mais n'a aucune réponses définie.");
define("_AC_NOTYPE", "Cette question n'a pas de 'type' paramétré.");
define("_AC_NOLID", "Un jeu d'Etiquette est requis pour cette question. Aucun n'est saisis."); //New for 0.98rc8
define("_AC_CON_OUTOFORDER", "Cette question à une condition paramétrée, toutefois la condition est basée sur une question qui apparait aprés elle.");
define("_AC_FAIL", "Le Questionnaire n'est pas validé par le contrôle de cohérence");
define("_AC_PROBS", "Le probléme suivant a été rencontré:");
define("_AC_CANNOTACTIVATE", "Le Questionnaire ne peut pas être activé jusqu'à ce que ces problémes soient résolus");
define("_AC_READCAREFULLY", "LIRE CECI ATTENTIVEMENT AVANT DE POURSUIVRE");
define("_AC_ACTIVATE_MESSAGE1", "Vous devriez activer un Questionnaire seulement si vous êtes absolument certain que votre Questionnaire est correctement paramétré/terminéeet n'aura pas besoin d'être modifié.");
define("_AC_ACTIVATE_MESSAGE2", "Un fois qu'un Questionnaire est activé vous ne pouvez plus:<ul><li>Ajouter ou supprimer des groupes</li><li>Ajouter ou enlever des Réponses aux questions à réponses multiples</li><li>Ajouter ou supprimer des questions</li></ul>");
define("_AC_ACTIVATE_MESSAGE3", "Cependant vous pouvez toujours:<ul><li>Editer (Modifier) les codes de vos questions, le texte ou le type </li><li>Editer (Modifier) les noms de vos Groupes</li><li>Ajouter, Enlever ou Editer les réponses des questions prédéfinies (à l'exception des questions à réponses multiples)</li><li>Changer le nom du Questionnaire ou sa description</li></ul>");
define("_AC_ACTIVATE_MESSAGE4", "Une fois que les données sont saisies dans votre Questionnaire, si vous voulez ajouter ou enlever des groupes ou questions, vous devez désactiver ce questionnaire, ce qui déplacera toutes les données qui ont déjà été saisies dans une table archivée séparée.");
define("_AC_ACTIVATE", "Activer");
define("_AC_ACTIVATED", "Le Questionnaire a été activé. La table résultat a été crée avec succés.");
define("_AC_NOTACTIVATED", "Le Questionnaire ne peut pas être activé.");
define("_AC_NOTPRIVATE", "Ce n'est pas un questionnaire anonyme. Une table Invitation doit donc être crée.");
define("_AC_REGISTRATION", "This survey allows public registration. A token table must also be created."); //New for 0.98finalRC1
define("_AC_CREATETOKENS", "Initialiser les Invitations");
define("_AC_SURVEYACTIVE", "Ce questionnaire est maintenant activé, et les réponses peuvent être enregistrées.");
define("_AC_DEACTIVATE_MESSAGE1", "Dans un questionnaire activé, une table est crée pour stocker toutes les données saisies enregistrées.");
define("_AC_DEACTIVATE_MESSAGE2", "Lorsque vous désactiver un questionnaire toute les données saisie dans la table original seront déplacée ailleurs, et lorsque vous réactivez le questionnaire la table est vide. Vous ne pourrez plus accéder à ces données avec PHPSurveyor.");
define("_AC_DEACTIVATE_MESSAGE3", "Seul un administrateur systéme peut accéder aux données d'un questionnaire désactivé en utilisant un gestionnaire de bases MySQL comme phpmyadmin. Si votre questionnaire utilise des Invitations, cette table sera également renommée et seul un administrateur systéme y aura accés.");
define("_AC_DEACTIVATE_MESSAGE4", "Votre table de réponse sera renommée en:");
define("_AC_DEACTIVATE_MESSAGE5", "Vous devriez exporter vos réponses avant de désactiver. Cliquer \"Annuler\" pour retourner à l'écran principal d'administration sans désactiver ce questionnaire.");
define("_AC_DEACTIVATE", "Désactiver");
define("_AC_DEACTIVATED_MESSAGE1", "La table réponses a été renommée en: ");
define("_AC_DEACTIVATED_MESSAGE2", "Les réponses à ce questionnaire ne sont plus disponibles via PHPSurveyor.");
define("_AC_DEACTIVATED_MESSAGE3", "Vous devriez noter le nom de cette table dans le cas où vous auriez besoin d'y accéder ultérieurement.");
define("_AC_DEACTIVATED_MESSAGE4", "La table d'Invitations liée à ce questionnaire a été renommée en: ");

//CHECKFIELDS
define("_CF_CHECKTABLES", "Vérification pour s'assurer qut toute les tables existent");
define("_CF_CHECKFIELDS", "Vérification pour s'assurer que tous les champs existent");
define("_CF_CHECKING", "Vérification");
define("_CF_TABLECREATED", "Table Crée");
define("_CF_FIELDCREATED", "Champ Crée");
define("_CF_OK", "OK");
define("_CFT_PROBLEM", "Il semble que quelques tables ou champs soient absents de votre base de données.");

//CREATE DATABASE (createdb.php)
define("_CD_DBCREATED", "Base de données crée.");
define("_CD_POPULATE_MESSAGE", "Veuillez cliquer ci-dessous pour peupler la base de données");
define("_CD_POPULATE", "Peupler la base de données");
define("_CD_NOCREATE", "Impossible de créer la base de données");
define("_CD_NODBNAME", "Les informations de la Base de données ne sont pas fournies. Ce script doit être éxécuté à partir d'admin.php seulement.");

//DATABASE MODIFICATION MESSAGES
define("_DB_FAIL_GROUPNAME", "Le Groupe ne peut pas être ajouté:Nom du groupe obligatoire absent.");
define("_DB_FAIL_GROUPUPDATE", "Le Groupe ne peut pas être mis à jour");
define("_DB_FAIL_GROUPDELETE", "Le Groupe ne peut pas être supprimer");
define("_DB_FAIL_NEWQUESTION", "La Question ne peut pas être crée.");
define("_DB_FAIL_QUESTIONTYPECONDITIONS", "La Question ne peut pas être mise à jour. Il y a des conditions pour d'autres questions qui se fondent sur les réponses à cette question et changer le type poserait des problèmes. Vous devez supprimer ces conditions avant de pouvoir changer le type de cette question.");
define("_DB_FAIL_QUESTIONUPDATE", "La Question ne peut pas être mise à jour");
define("_DB_FAIL_QUESTIONDELCONDITIONS", "La Question ne peut pas être supprimée. qui se fondent sur cette question.  Vous ne pouvez pas supprimer cette question jusqu'à ce que ces conditions soient enlevées");
define("_DB_FAIL_QUESTIONDELETE", "La Question ne peut pas être supprimée");
define("_DB_FAIL_NEWANSWERMISSING", "La Réponse ne peut pas être ajoutée. Vous devez inclure un code et une réponse");
define("_DB_FAIL_NEWANSWERDUPLICATE", "La Réponse ne peut pas être ajoutée. Il y a déjà une réponse avec ce code");
define("_DB_FAIL_ANSWERUPDATEMISSING", "La Réponse ne peut pas être mise à jour. Vous devez inclure un code et une réponse");
define("_DB_FAIL_ANSWERUPDATEDUPLICATE", "La Réponse ne peut pas être mise à jour. Il y a déjà une réponse avec ce code");
define("_DB_FAIL_ANSWERUPDATECONDITIONS", "La Réponse ne peut pas être mise à jour. Vous avez modifié le code de réponse, mais il y a des conditions à d'autres questions qui dépendent de l'ancien code de réponse de cette question.  Vous devez supprimer ces conditions avant de pouvoir modifier le code de cette réponse.");
define("_DB_FAIL_ANSWERDELCONDITIONS", "La Réponse ne peut pas être supprimée. Il y a des conditions pour d'autres questions qui se fondent sur cette réponse.  Vous ne pouvez pas supprimer cette réponse jusqu'à ce que ces conditions soient enlevées");
define("_DB_FAIL_NEWSURVEY_TITLE", "Le questionnaire ne peut pas être crée parce qu'il n'a pas de titre court");
define("_DB_FAIL_NEWSURVEY", "Le questionnaire ne peut pas être crée");
define("_DB_FAIL_SURVEYUPDATE", "Le questionnaire ne peut pas être mis à jour");
define("_DB_FAIL_SURVEYDELETE", "Le questionnaire ne peut pas être supprimé");

//DELETE SURVEY MESSAGES
define("_DS_NOSID", "Vous n'avez pas sélectionné de questionnaire à supprimer");
define("_DS_DELMESSAGE1", "Vous êtes sur le point de supprimer ce questionnaire");
define("_DS_DELMESSAGE2", "Cette procédure supprimera ce questionnaire, tous les groupes associés, les réponses des Questions ainsi que les conditions.");
define("_DS_DELMESSAGE3", "Il est recommandé avant de supprimer ce questionnaire d'exporter entiérement ce questionnaire à partir de l'écran principal d'administration.");
define("_DS_SURVEYACTIVE", "Ce questionnaire est activé et une table des réponses existe. Si vous supprimez ce questionnaire, ces réponses seront supprimées. Il est recommandé d'exporter les réponses les réponses avant de supprimer ce questionnaire.");
define("_DS_SURVEYTOKENS", "Ce questionnaire a une table d'invitation associée. Si vous supprimez ce questionnaire cette table d'invitations sera supprimée. Il est recommandé d'exporter ou faire une une sauvegarde de ces invitations avant de supprimer ce questionnaire.");
define("_DS_DELETED", "Ce questionnaire a été supprimé.");

//DELETE QUESTION AND GROUP MESSAGES
define("_DG_RUSURE", "Supprimer ce groupe supprimera également toute les questions et réponses qu'il contient. Etes-vous sûr de vouloir continuer?"); //New for 098rc5
define("_DQ_RUSURE", "Supprimer cette question supprimera également toutes les réponses qu'elle inclut. Etes-vous sûr de vouloir continuer"); //New for 098rc5

//EXPORT MESSAGES
define("_EQ_NOQID", "Aucun QID n'a été fourni. Impossible de vider la question.");
define("_ES_NOSID", "Aucun QID n'a été fourni. Impossible de vider le questionnaire");

//EXPORT RESULTS
define("_EX_FROMSTATS", "Filtré par le script des statistiques");
define("_EX_HEADINGS", "Questions");
define("_EX_ANSWERS", "Réponses");
define("_EX_FORMAT", "Format");
define("_EX_HEAD_ABBREV", "Entête  abrégés");
define("_EX_HEAD_FULL", "Entête complet");
define("_EX_ANS_ABBREV", "Codes de Réponse");
define("_EX_ANS_FULL", "Réponses compléte (full answers)");
define("_EX_FORM_WORD", "Microsoft Word");
define("_EX_FORM_EXCEL", "Microsoft Excel");
define("_EX_FORM_CSV", "CSV-Texte (séparateur: virgule)");
define("_EX_EXPORTDATA", "Exporter les données");
define("_EX_COLCONTROLS", "Titre de la colonne(Column Control)"); //New for 0.98rc7
define("_EX_TOKENCONTROLS", "Contrôle Invitation"); //New for 0.98rc7
define("_EX_COLSELECT", "Choisir les colonnes"); //New for 0.98rc7
define("_EX_COLOK", "Choisir les colonnes que vous voulez exporter.Ne rien Sélectionner pour exporter toute les colonnes."); //New for 0.98rc7
define("_EX_COLNOTOK", "Votre questionnaire contient plus de 255 colonnes de réponses. Les tableurs comme Excel sont limités à 255. Sélectionner les colonnes à exporter dans la liste ci-dessous.."); //New for 0.98rc7
define("_EX_TOKENMESSAGE", "Votre questionnaire peut exporter les données des Invitations associés avec chaque réponse. Sélectionnez tous les champs additionnels que vous voudriez exporter."); //New for 0.98rc7
define("_EX_TOKSELECT", "Choisir les Champs d'Invitation"); //New for 0.98rc7

//IMPORT SURVEY MESSAGES
define("_IS_FAILUPLOAD", "Une erreur s'est produite durant la transmission de votre fichier.  Ceci peut être provoqué par des permissions incorrectes dans votre dossier admin.");
define("_IS_OKUPLOAD", "Fichier transmis avec succés.");
define("_IS_READFILE", "Lecture du fichier..");
define("_IS_WRONGFILE", "Ce fichier n'est pas fichier de questionnaire PHPSurveyor. L'importation a échoué.");
define("_IS_IMPORTSUMMARY", "Sommaire de l'importation du questionnaire");
define("_IS_SUCCESS", "L'importation du questionnaire est terminée.");
define("_IS_IMPFAILED", "L'importation de ce fichier questionnaire a échoué");
define("_IS_FILEFAILS", "Mauvais format de données dans le fichier de données PHPSurveyor.");

//IMPORT GROUP MESSAGES
define("_IG_IMPORTSUMMARY", "Sommaire de l'importation de Groupe");
define("_IG_SUCCESS", "L'importation du groupe est terminée.");
define("_IG_IMPFAILED", "L'importation de ce groupe a échoué");
define("_IG_WRONGFILE", "Ce fichier n'est pas un fichier de groupe PHPSurveyor.L'importation a échoué.");

//IMPORT QUESTION MESSAGES
define("_IQ_NOSID", "Aucun SID (Questionnaire) n'a été fournis. Impossible d'importer une question.");
define("_IQ_NOGID", "Aucun GID (Groupe) n'a été fournis. Impossible d'importer une question");
define("_IQ_WRONGFILE", "Ce fichier n'est pas un fichier de question PHPSurveyor.L'importation a échoué.");
define("_IQ_IMPORTSUMMARY", "Sommaire de l'importation de question");
define("_IQ_SUCCESS", "L'importation de Question est terminée");

//IMPORT LABELSET MESSAGES
define("_IL_DUPLICATE", "There was a duplicate labelset, so this set was not imported. The duplicate will be used instead.");

//BROWSE RESPONSES MESSAGES
define("_BR_NOSID", "Vous n'avez pas Sélectionné de questionnaire à parcourir.");
define("_BR_NOTACTIVATED", "Ce questionnaire n'a pas été activé. Aucun résultats à parcourir.");
define("_BR_NOSURVEY", "Il n'y a pas de questionnaire associé.");
define("_BR_EDITRESPONSE", "Editer cette saisie (entry)");
define("_BR_DELRESPONSE", "Supprimer cette saise");
define("_BR_DISPLAYING", "Enregistrements affichés:");
define("_BR_STARTING", "A partir de:");
define("_BR_SHOW", "Afficher");
define("_DR_RUSURE", "Est-vous sûr de vouloir supprimer cette saisie?"); //New for 0.98rc6

//STATISTICS MESSAGES
define("_ST_FILTERSETTINGS", "Paramétres de Filtre");
define("_ST_VIEWALL", "Visualiser le sommaire de tous les champs disponibles"); //New with 0.98rc8
define("_ST_SHOWRESULTS", "Visualiser les Stats"); //New with 0.98rc8
define("_ST_CLEAR", "Effacer sélection"); //New with 0.98rc8
define("_ST_RESPONECONT", "Réponses Contenant"); //New with 0.98rc8
define("_ST_NOGREATERTHAN", "Nombre supérieur que"); //New with 0.98rc8
define("_ST_NOLESSTHAN", "Nombre inférieur à"); //New with 0.98rc8
define("_ST_DATEEQUALS", "Date (AAAA-MM-JJ) égale"); //New with 0.98rc8
define("_ST_ORBETWEEN", "OU entre"); //New with 0.98rc8
define("_ST_RESULTS", "Resultats"); //New with 0.98rc8 (Plural)
define("_ST_RESULT", "Resultat"); //New with 0.98rc8 (Singular)
define("_ST_RECORDSRETURNED", "Aucun enregistrement dans cette requête"); //New with 0.98rc8
define("_ST_TOTALRECORDS", "Nombre d'Enregistrements Total dans un questionnaire"); //New with 0.98rc8
define("_ST_PERCENTAGE", "Pourcentage du total"); //New with 0.98rc8
define("_ST_FIELDSUMMARY", "Sommaire de champs pour"); //New with 0.98rc8
define("_ST_CALCULATION", "Calcul"); //New with 0.98rc8
define("_ST_SUM", "Somme"); //New with 0.98rc8 - Mathematical
define("_ST_STDEV", "Écart type"); //New with 0.98rc8 - Mathematical
define("_ST_AVERAGE", "Moyenne"); //New with 0.98rc8 - Mathematical
define("_ST_MIN", "Minimum"); //New with 0.98rc8 - Mathematical
define("_ST_MAX", "Maximum"); //New with 0.98rc8 - Mathematical
define("_ST_Q1", "1er Quartile (Q1)"); //New with 0.98rc8 - Mathematical
define("_ST_Q2", "2ème Quartile (Median)"); //New with 0.98rc8 - Mathematical
define("_ST_Q3", "3ème Quartile (Q3)"); //New with 0.98rc8 - Mathematical
define("_ST_NULLIGNORED", "*Des valeurs nulles sont ignorées dans les calculs"); //New with 0.98rc8
define("_ST_QUARTMETHOD", "*Q1 and Q3 a été calculé avec <a href='http://mathforum.org/library/drmath/view/60969.html' target='_blank'>minitab method</a>"); //New with 0.98rc8

//DATA ENTRY MESSAGES
define("_DE_NOMODIFY", "Ne peut pas être modifié");
define("_DE_UPDATE", "Mettre à jour la saisie (Entry)");
define("_DE_NOSID", "Vous n'avez pas sélectionné de questionnaire pour la saisie des données.");
define("_DE_NOEXIST", "Le questionnaire que vous avez sélectionné n'éxiste pas");
define("_DE_NOTACTIVE", "Ce questionnaire n'est pas encore activé. Votre réponse ne peut pas être sauvegardée");
define("_DE_INSERT", "Insertion de donnée");
define("_DE_RECORD", "L'entrée était assignée à l'Id de l'Enregistrement suivant: ");
define("_DE_ADDANOTHER", "Ajouter un autre Enregistrement");
define("_DE_VIEWTHISONE", "Visualiser cet Enregistrement");
define("_DE_BROWSE", "Parcourir les Réponses");
define("_DE_DELRECORD", "Enregistrement Supprimé");
define("_DE_UPDATED", "L'Enregistrement a été mis à jour.");
define("_DE_EDITING", "Editer une Réponse");
define("_DE_QUESTIONHELP", "Aide sur cette question");
define("_DE_CONDITIONHELP1", "Répondez seulement à ceci si les conditions suivantes sont réunies:"); 
define("_DE_CONDITIONHELP2", "à la question {QUESTION}, vous avez répondu {ANSWER}"); //This will be a tricky one depending on your languages syntax. {ANSWER} is replaced with ALL ANSWERS, seperated by _DE_OR (OR).
define("_DE_AND", "ET (AND)");
define("_DE_OR", "OU (OR)");
define("_DE_SAVEENTRY", "Save as a partially completed survey"); //New in 0.99dev01
define("_DE_SAVEID", "Identifier:"); //New in 0.99dev01
define("_DE_SAVEPW", "Password:"); //New in 0.99dev01
define("_DE_SAVEPWCONFIRM", "Confirm Password:"); //New in 0.99dev01
define("_DE_SAVEEMAIL", "Email:"); //New in 0.99dev01

//TOKEN CONTROL MESSAGES
define("_TC_TOTALCOUNT", "Totale d'enregistrements dans cette table Invitation:"); //New in 0.98rc4
define("_TC_NOTOKENCOUNT", "Total sans Invitation Unique:"); //New in 0.98rc4
define("_TC_INVITECOUNT", "Total d'invitations envoyées:"); //New in 0.98rc4
define("_TC_COMPLETEDCOUNT", "Total de Questionnaire terminés:"); //New in 0.98rc4
define("_TC_NOSID", "Vous n'avez pas sélectionné de Questionnaire");
define("_TC_DELTOKENS", "Au sujet de la suppression de la table Invitation pour ce questionnaire.");
define("_TC_DELTOKENSINFO", "Si vous supprimez cette table des Invitations ne seront plus requises pour accéder à ce questionnaire. Une sauvegarde de cette table sera effectué si vous la supprimez. Votre administrateur systéme pourra accéder à cette table.");
define("_TC_DELETETOKENS", "Supprimer Invitations");
define("_TC_TOKENSGONE", "La table d'invitations a été enlevée maintenant et des invitations ne sont plus requises pour accéder à ce questionnaire. Une sauvegarde de cette table a été effectuée. L'administrateur systéme pourra y accéder.");
define("_TC_NOTINITIALISED", "Aucune invitations n'a été initialisée pour ce questionnaire.");
define("_TC_INITINFO", "Si vous initialisez des invitations pour ce questionnaire, seul les utilisateurs ayant une invitation pourront y accéder.");
define("_TC_INITQ", "Voulez-vous créer des invitations pour ce questionnaire??");
define("_TC_INITTOKENS", "Initialiser Invitations");
define("_TC_CREATED", "Une table d'invitation a été crée pour ce questionnaire.");
define("_TC_DELETEALL", "Supprimer toutes les d'invitations");
define("_TC_DELETEALL_RUSURE", "Etes-vous sur de vouloir supprimer TOUTES les invitations?");
define("_TC_ALLDELETED", "Toutes les invitations ont été supprimées");
define("_TC_CLEARINV_RUSURE", "Est-vous sûr de vouloir réinitialiser tous les enregistrements d'invitation à NON?");
define("_TC_CLEARTOKENS", "Supprimer tous les nombres uniques des invitations (All unique token numbers)");
define("_TC_CLEARTOKENS_RUSURE", "Etes-vous sûr de vouloir supprimer tous les nombres uniques des invitations?");
define("_TC_TOKENSCLEARED", "Tous les nombres uniques des invitations ont été enlevés");
define("_TC_INVITESCLEARED", "Toute les entrés des invitations ont été définies à N");
define("_TC_EDIT", "Editer Invitation (Token Entry)");
define("_TC_DEL", "Supprimer Invitation");
define("_TC_DO", "Faire un Questionnaire");
define("_TC_VIEW", "Voir Réponse");
define("_TC_INVITET", "Envoyer une invitation par email à cette entrée");
define("_TC_REMINDT", "Envoyer un rappel par email pour cette entrée");
define("_TC_INVITESUBJECT", "Invitation pour répondre au questionnaire {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSUBJECT", "Rappel pour répondre au questionnaire {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSTARTAT", "Commencer à l'IID (TID) No:");
define("_TC_REMINDTID", "envoyé àl'IID (TID) No:");
define("_TC_CREATETOKENSINFO", "Cliquer sur Oui va générer des invitations pour ceux de la liste d'invitation qui n'en ont pas reçu. Etes-vous d'accord??");
define("_TC_TOKENSCREATED", "{TOKENCOUNT} invitations ont été crées"); //Leave {TOKENCOUNT} for replacement in script with the number of tokens created
define("_TC_TOKENDELETED", "Une Invitation a été supprimée.");
define("_TC_SORTBY", "Tri par: ");
define("_TC_ADDEDIT", "Ajouter ou Editer une Invitation");
define("_TC_TOKENCREATEINFO", "Vous pouvez laisser cela à blanc et générer automatiquement des invitations avec 'Créer Invitations'");
define("_TC_TOKENADDED", "Ajouter Nouvelle Invitation");
define("_TC_TOKENUPDATED", "Mise à jour Invitation");
define("_TC_UPLOADINFO", "Le fichier doit être un fichier standard CSV (délimiteur: virgule) sans quotes. La premiére ligne doit contenir une informations d'en-tête (qui sera enlevée). Les données devront être triées par \"Nom, Prénom, email, [token], [attribute1], [attribute2]\".");
define("_TC_UPLOADFAIL", "Fichier téléchargé non trouvé. Vérifier vos permissions et le chemin du répertoire de téléchargement (upload)"); //New for 0.98rc5
define("_TC_IMPORT", "Importation du fichier CSV");
define("_TC_CREATE", "Création des Entrées des Invitations");
define("_TC_TOKENS_CREATED", "{TOKENCOUNT} Enregistrements crées");
define("_TC_NONETOSEND", "Il n'y avait aucun emails éligibles à envoyer: aucun n'a satisfait les critères - email valide, invitation déjà envoyée, Questionnaire déjà completé et Invitation obtenue.");
define("_TC_NOREMINDERSTOSEND", "Il n'y avait aucun emails éligibles à envoyer: aucun n'a satisfait les critères - email valide, invitation envoyée mais Questionnaire pas encore completé.");
define("_TC_NOEMAILTEMPLATE", "Modèle d'Invitation non trouvé. Ce fichier doit exister dans le répertoire  Modèle (Template) par défaut.");
define("_TC_NOREMINDTEMPLATE", "Modèle Rappel non trouvé. Ce fichier doit exister dans le répertoire  Modèle (Template) par défaut.");
define("_TC_SENDEMAIL", "Envoyer Invitations");
define("_TC_SENDINGEMAILS", "Envoi Invitations");
define("_TC_SENDINGREMINDERS", "Envoi Rappels");
define("_TC_EMAILSTOGO", "Il y a plus d'email en suspens qui peuvent être envoyés en groupe (batch).  Continuez d'envoyer des email en cliquant ci-dessous.");
define("_TC_EMAILSREMAINING", "Il y a encore {EMAILCOUNT} à envoyer."); //Leave {EMAILCOUNT} for replacement in script by number of emails remaining
define("_TC_SENDREMIND", "Envoyer Rappels");
define("_TC_INVITESENTTO", "Invitation envoyé à:"); //is followed by token name
define("_TC_REMINDSENTTO", "Rappel envoyé à:"); //is followed by token name
define("_TC_UPDATEDB", "Mettre à jour la table d'invitation (Tokens) avec des nouveaux champs"); //New for 0.98rc7
define("_TC_EMAILINVITE", "{FIRSTNAME},\n\nVous avez été invité à participer à un questionnaire.\n\n"
						 ."Celui-ci est intitulé:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
						 ."Pour participer, veuillez cliquer sur le lien ci-dessous.\n\nCordialement,\n\n"
						 ."{ADMINNAME} ({ADMINEMAIL})\n\n"
						 ."----------------------------------------------\n"
						 ."Cliquer ici pour faire le questionnaire:\n"
						 ."{SURVEYURL}"); //New for 0.98rc9 - Email d'Invitation par défaut
define("_TC_EMAILREMIND", "{FIRSTNAME},\n\nVous avez été inviter à participer à un questionnaire récemment.\n\n"
						 ."Nous avons pris en compte que vous n'avez pas encore completé le questionnaire, et nous vous rappelons que celui-ci est toujours disponible si vous souhaitez participer.\n\n"
						 ."Le questionnaire est intitulé:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
						 ."Pour participer, veuillez cliquer sur le lien ci-dessous.\n\nCordialement,\n\n"
						 ."{ADMINNAME} ({ADMINEMAIL})\n\n"
						 ."----------------------------------------------\n"
						 ."Cliquez ici pour faire le questionnaire:\n"
						 ."{SURVEYURL}"); //New for 0.98rc9 - Email de rappel par defaut
define("_TC_EMAILREGISTER", "Dear {FIRSTNAME},\n\n"
						  ."Vous (ou quelqu'un utilisant votre adresse email) êtes enregistrés pour "
						  ."participer à un questionnaire en ligne intitulé {SURVEYNAME}.\n\n"
						  ."Pour compléter ce questionnaire, cliquez sur l'URL suivante:\n\n"
						  ."{SURVEYURL}\n\n"
						  ."Quel que soit votre question à propos de ce questionnaire, ou si vous "
						  ."n'avez pas été enregistré pour participer à celui-ci et croyez qu'il s'agit "
						  ."d'une erreur, veuillez contacter {ADMINNAME} :  {ADMINEMAIL}.");//NEW for 0.98rc9
define("_TC_EMAILCONFIRM", "{FIRSTNAME},\n\nCet email vous confirme que vous avez completé le questionnaire intitulé {SURVEYNAME} "
						  ."et votre réponse à été enregistrée. Merci d'avoir participé.\n\n"
						  ."Si vous avez d'autres questions à propos de cet email, veuillez contacter {ADMINNAME} : {ADMINEMAIL}.\n\n"
						  ."Cordialement,\n\n"
						  ."{ADMINNAME}"); //New for 0.98rc9 - Confirmation Email

//labels.php
define("_LB_NEWSET", "Créer Nouveau Jeu d'Etiquette");
define("_LB_EDITSET", "Editer Jeu d'Etiquette");
define("_LB_FAIL_UPDATESET", "La Mise à jour du Jeu d'Etiquette a échoué");
define("_LB_FAIL_INSERTSET", "L'Insertion du nouveau Jeu d'Etiquette à échoué");
define("_LB_FAIL_DELSET", "Impossible de supprimer le Jeu d'Etiquette - Il y a des questions qui y est relié. Vous devez supprimer ces questions en premier..");
define("_LB_ACTIVEUSE", "Vous ne pouvez pas changer des codes, ajouter ou supprimer des entrées dans ce jeu d'étiquettes parce que ceux-ci sont utilisés par un questionnaire activé.");
define("_LB_TOTALUSE", "Quelques questionnaires utilisent actuellement ce jeu d'étiquette. Modifier les codes, ajouter ou supprimer des entrées de ce jeu pourrait entrainer des effets indésirables dans d'autres questionnaires.");
//Export Labels
define("_EL_NOLID", "Aucun JID (LID) fournis. Impossible  de vider (Dump) un jeu d'étiquette.");
//Import Labels
define("_IL_GOLABELADMIN", "Retour à l'Administration d'Etiquettes");

//PHPSurveyor System Summary
define("_PS_TITLE", "Résumé système PHPSurveyor");
define("_PS_DBNAME", "Nom Base de données");
define("_PS_DEFLANG", "Langage par défaut");
define("_PS_CURLANG", "Langage courant");
define("_PS_USERS", "Utilisateurs");
define("_PS_ACTIVESURVEYS", "Questionnaires activés");
define("_PS_DEACTSURVEYS", "Désactiver Questionnaires");
define("_PS_ACTIVETOKENS", "Tables d'Invitation (Token) activées");
define("_PS_DEACTTOKENS", "Désactiver Tables Invitation");
define("_PS_CHECKDBINTEGRITY", "Vérifier l'Intégrité Des Données De PHPSurveyor"); //New with 0.98rc8

//Notification Levels
define("_NT_NONE", "Aucune notification par email"); //New with 098rc5
define("_NT_SINGLE", "Notification par email de base"); //New with 098rc5
define("_NT_RESULTS", "Envoyer notification par email avec des codes résultat"); //New with 098rc5

//CONDITIONS TRANSLATIONS
define("_CD_CONDITIONDESIGNER", "Concepteur De Condition"); //New with 098rc9
define("_CD_ONLYSHOW", "Montrer seulement question {QID} SI (IF)"); //New with 098rc9 - {QID} is repleaced leave there
define("_CD_AND", "ET (AND)"); //New with 098rc9
define("_CD_COPYCONDITIONS", "Copier Conditions"); //New with 098rc9
define("_CD_CONDITION", "Condition"); //New with 098rc9
define("_CD_ADDCONDITION", "Ajouter Condition"); //New with 098rc9
define("_CD_EQUALS", "Egales"); //New with 098rc9
define("_CD_COPYRUSURE", "Etes-vous sûr de vouloir copier ces condition(s) aux questions sélectionnées?"); //New with 098rc9
define("_CD_NODIRECT", "Vous ne pouvez pas éxecuter directement ce script."); //New with 098rc9
define("_CD_NOSID", "Vous n'avez pas sélectionné de Questionnaire."); //New with 098rc9
define("_CD_NOQID", "Vous n'avez pas sélectionné de Question."); //New with 098rc9
define("_CD_DIDNOTCOPYQ", "Questions non copiées"); //New with 098rc9
define("_CD_NOCONDITIONTOCOPY", "Aucune condition à copier sélectionnée"); //New with 098rc9
define("_CD_NOQUESTIONTOCOPYTO", "Aucune question sélectionnée pour copier la condition à"); //New with 098rc9

//TEMPLATE EDITOR TRANSLATIONS
define("_TP_CREATENEW", "Créer Nouveau Modèle"); //New with 098rc9
define("_TP_NEWTEMPLATECALLED", "Créer nouveau modèle nommé:"); //New with 098rc9
define("_TP_DEFAULTNEWTEMPLATE", "Nouveau Modèle"); //New with 098rc9 (default name for new template)
define("_TP_CANMODIFY", "Ce modèle peut être modifié"); //New with 098rc9
define("_TP_CANNOTMODIFY", "Ce modèle ne peut pas être modifié"); //New with 098rc9
define("_TP_RENAME", "Renommer ce modèle");  //New with 098rc9
define("_TP_RENAMETO", "Renommer ce modèle en:"); //New with 098rc9
define("_TP_COPY", "Faire une copie de ce modèle");  //New with 098rc9
define("_TP_COPYTO", "Créer une copie de ce modèle nommé:"); //New with 098rc9
define("_TP_COPYOF", "copie_de_"); //New with 098rc9 (prefix to default copy name)
define("_TP_FILECONTROL", "Contrôle Fichier:"); //New with 098rc9
define("_TP_STANDARDFILES", "Fichiers Standards:");  //New with 098rc9
define("_TP_NOWEDITING", "Edition en cours:");  //New with 098rc9
define("_TP_OTHERFILES", "Autres fichiers:"); //New with 098rc9
define("_TP_PREVIEW", "Aperçu:"); //New with 098rc9
define("_TP_DELETEFILE", "Supprimer"); //New with 098rc9
define("_TP_UPLOADFILE", "Télecharger (Upload)"); //New with 098rc9
define("_TP_SCREEN", "Ecran:"); //New with 098rc9
define("_TP_WELCOMEPAGE", "Page d'Accueil"); //New with 098rc9
define("_TP_QUESTIONPAGE", "Page de Question"); //New with 098rc9
define("_TP_SUBMITPAGE", "Envoyer Page");
define("_TP_COMPLETEDPAGE", "Page Complétée"); //New with 098rc9
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
?>