<?php
/*
        #################################################################
        # >>> PHPSurveyor                                               #
        #################################################################
        # > Author:  Jason Cleeland                                     #
        # > E-mail:  jason@cleeland.org                                 #
        # > Mail:    Box 99, Trades Hall, 54 Victoria St,               #
        # >          CARLTON SOUTH 3053, AUSTRALIA                      #
        # > Date:        20 February 2003                               #
        #                                                               #
        # This set of scripts allows you to develop, publish and        #
        # perform data-entry on surveys.                                #
        #################################################################
        #       Copyright (C) 2003  Jason Cleeland                      #
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
        # This language file kindly provided by François Tissandier     #
        # and corrected by Pascal Bastien 20/07/2004 - Version 1.3      #
        # Version 1.5.0 - corrected by Sébastien GAUGRY                 #
        # Version 1.5.2 for phpsurveyor 0.991 some corrections by       #
        # Pascal BASTIEN                                                #
        #                                                               #
        #################################################################
*/
//SINGLE WORDS
define("_YES", "Oui");
define("_NO", "Non");
define("_UNCERTAIN", "Indifférent");
define("_ADMIN", "Administrateur");
define("_TOKENS", "Invitations");
define("_FEMALE", "Femme");
define("_MALE", "Homme");
define("_NOANSWER", "Sans réponse");
define("_NOTAPPLICABLE", "N/A"); //New for 0.98rc5
define("_OTHER", "Autre");
define("_PLEASECHOOSE", "Veuillez choisir");
define("_ERROR_PS", "Erreur");
define("_COMPLETE", "Terminé");
define("_INCREASE", "Augmenter"); //NEW WITH 0.98
define("_SAME", "Sans Changement"); //NEW WITH 0.98
define("_DECREASE", "Diminuer"); //NEW WITH 0.98
define("_REQUIRED", "<font color='red'>* </font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Confirmation");
define("_TOKEN_PS", "Invitation");
define("_CONTINUE_PS", "Continuer");

//BUTTONS
define("_ACCEPT", "Accepter");
define("_PREV", "Précédent");
define("_NEXT", "Suivant");
define("_LAST", "Fin");
define("_SUBMIT", "Envoyer");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Veuillez sélectionner une réponse ci-dessous");
define("_ENTERCOMMENT", "Veuillez saisir votre commentaire ici");
define("_NUMERICAL_PS", "Seuls les chiffres sont autorisés pour ce champ");
define("_CLEARALL", "Sortir et effacer les réponses");
define("_MANDATORY", "Cette question est obligatoire");
define("_MANDATORY_PARTS", "Veuillez compléter toutes les parties SVP");
define("_MANDATORY_CHECK", "Veuillez choisir au moins un élément SVP");
define("_MANDATORY_RANK", "Veuillez classer tous les éléments SVP");
define("_MANDATORY_POPUP", "Vous n'avez pas répondu à une ou plusieurs questions obligatoires. Vous ne pouvez pas enregistrer vos réponses au questionnaire tant que vous n'aurez pas répondu à celles-ci"); //NEW in 0.98rc4
define("_VALIDATION", "Vous devez répondre correctement à cette question"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "Vous n'avez pas répondu correctement à une ou plusieurs questions. Vous ne pouvez pas continuer tant que ces réponses ne sont pas valides"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Format : AAAA-MM-JJ");
define("_DATEFORMATEG", "(ex : 2003-12-25 pour Noël)");
define("_REMOVEITEM", "Enlever cet élément");
define("_RANK_1", "Cliquez sur un élément dans la liste de gauche ci-dessous.");
define("_RANK_2", "Choisissez l'élément le plus important pour finir par le moins important.");
define("_YOURCHOICES", "Vos choix");
define("_YOURRANKING", "Votre classement");
define("_RANK_3", "Cliquer sur les ciseaux à droite de chaque élément");
define("_RANK_4", "pour enlever le dernier élément de votre classement");
//From INDEX.PHP
define("_NOSID", "Vous n'avez pas fourni d'identifiant de questionnaire");
define("_CONTACT1", "Veuillez contacter");
define("_CONTACT2", "pour plus d'aide");
define("_ANSCLEAR", "Réponses effacées");
define("_RESTART", "Recommencer ce questionnaire");
define("_CLOSEWIN_PS", "Fermer cette fenêtre");
define("_CONFIRMCLEAR", "Etes-vous sûr de vouloir effacer toutes les réponses ?");
define("_CONFIRMSAVE", "Etes-vous sûr de vouloir sauvegarder vos réponses ?");
define("_EXITCLEAR", "Sortir et effacer les réponses");
//From QUESTION.PHP
define("_BADSUBMIT1", "Impossible d'envoyer les réponses car il n'y en a aucune (vide).");
define("_BADSUBMIT2", "Cette erreur peut se produire si vous avez déjà envoyé vos réponses et actualisé la page de votre naviguateur avec \"Actualiser\". Dans ce cas, vos réponses ont déjà été sauvées.");
define("_NOTACTIVE1", "Vos réponses n'ont pas été enregistrées. Ce questionnaire n'est pas encore activé.");
define("_CLEARRESP", "Effacer les réponses");
define("_THANKS", "Merci");
define("_SURVEYREC", "Vos réponses ont été enregistrées.");
define("_SURVEYCPL", "Questionnaire complété");
define("_DIDNOTSAVE", "Non sauvegardé");
define("_DIDNOTSAVE2", "Une erreur non prévue s'est produite et vos réponses n'ont pas pu être sauvées.");
define("_DIDNOTSAVE3", "Vos réponses n'ont pas été perdues et ont été mailées à l'administrateur du questionnaire qui les saisira ultérieurement dans la base de données.");
define("_DNSAVEEMAIL1", "Une erreur s'est produite pendant la sauvegarde d'une réponse");
define("_DNSAVEEMAIL2", "DONNEES A SAISIR");
define("_DNSAVEEMAIL3", "CODE SQL QUI A ECHOUE");
define("_DNSAVEEMAIL4", "MESSAGE D'ERREUR");
define("_DNSAVEEMAIL5", "ERREUR DE SAUVEGARDE");
define("_SUBMITAGAIN", "Essayez d'envoyer à nouveau");
define("_SURVEYNOEXIST", "Désolé. Il n'y a pas de questionnaire correspondant.");
define("_NOTOKEN1", "C'est un questionnaire privé. Vous devez avoir une invitation pour y participer.");
define("_NOTOKEN2", "Si vous avez reçu une invitation, saisissez-la dans le champ ci-dessous et cliquez sur Continuer.");
define("_NOTOKEN3", "L'invitation que vous avez reçue n'est pas valide, ou a déjà été utilisée.");
define("_NOQUESTIONS", "Ce questionnaire n'a pas encore de question et ne peut être testé ou finalisé.");
define("_FURTHERINFO", "Pour plus d'informations veuillez contacter");
define("_NOTACTIVE", "Ce questionnaire n'est pas activé. Vous ne pourrez pas sauver vos réponses.");
define("_SURVEYEXPIRED", "Ce questionnaire n'est plus disponible."); //NEW for 098rc5

define("_SURVEYCOMPLETE", "Vous avez déjà complété ce questionnaire.");

define("_INSTRUCTION_LIST", "Veuillez sélectionner seulement une réponse ci-dessous"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Cochez la ou les réponses"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Questionnaire envoyé"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Une nouvelle réponse a été saisie dans votre questionnaire"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Cliquez sur le lien suivant pour voir votre réponse personnelle"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Visualiser les statistiques en cliquant ici"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE5", "Cliquez sur le lien suivant pour éditer cette réponse :"); //NEW for 0.99stable

define("_PRIVACY_MESSAGE", "<strong><i>Note sur la protection de la vie privée</i></strong><br />"
                                                  ."Ce questionnaire est anonyme.<br />"
                                                  ."Les enregistrements conservés de votre questionnaire ne contiennent aucune "
                                                  ."information d'identification à moins bien sûr qu'une question  "
                                                  ."sur votre identité ait été posée dans le questionnaire. Si vous avez répondu à "
                                                  ."un questionnaire utilisant une invitation pour vous permettre d'y accéder, "
                                                  ."vous pouvez être assuré que cet identifiant. "
                                                  ."n'est pas conservé avec vos réponses. Il est geré dans une base de données séparée "
                                                  ."et ne pourra pas être mis à jour pour indiquer que vous "
                                                  ."avez complété ce questionnaire. Il n'y a aucun moyen pour faire correspondre "
                                                  ."les invitations avec les réponses au questionnaire."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "Il y a {NUMBEROFQUESTIONS} questions dans ce questionnaire."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Il y a 1 question dans ce questionnaire."); //New for 0.98rc9 - singular version of above

define ("_RG_REGISTER1", "Vous devez être enregistré pour répondre à ce questionnaire"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Vous devez être enregistré pour ce questionnaire si vous désirez y participer.<br />\n"
                                                ."Saisissez vos coordonnées ci-dessous, et un mail contenant le lien pour "
                                                ."participer à ce questionnaire vous sera immédiatement envoyé."); //NEW for 0.98rc9
define ("_RG_EMAIL", "Adresse mail"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Prénom"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Nom"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "L'adresse mail utilisée n'est pas valide. Veuillez reéssayer.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "L'adresse mail utilisée a déjà été enregistrée.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "Confirmation d'enregistrement de {SURVEYNAME}");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Merci de vous enregistrer pour participer à ce questionnaire.<br /><br />\n"
                                                                   ."Un mail a été envoyé à l'adresse que vous avez fournie dans les détails d'accés "
                                                                   ."pour ce questionnaire. Veuillez suivre le lien dans ce mail pour participer.<br /><br />\n"
                                                                   ."Administrateur du questionnaire {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<strong>Merci<br /><br />"
					   ."Vous venez de répondre à l'ensemble des questions de ce questionnaire.</strong><br /><br />"
					   ."Veuillez cliquer sur le bouton ["._SUBMIT."], afin de procéder à l'enregistrement de vos réponses."); //New for 0.98finalRC1
define("_SM_REVIEW", "Si vous souhaitez vérifier ou changer certaines de vos réponses, "
					."vous pouvez le faire en cliquant sur le bouton ["._PREV."] en bas de cette page, "
					."afin de passer en revue vos réponses.");
					
//For the "printable" survey
define("_PS_CHOOSEONE", "Choisissez <strong>seulement une</strong> des réponses suivantes :"); //New for 0.98finalRC1
define("_PS_WRITE", "Ecrivez votre réponse ici :"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Choisissez <strong>toutes</strong> les réponses qui conviennent :"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Choisissez toutes les réponses qui conviennent et laissez un commentaire :"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Choisissez la réponse appropriée pour chaque élément :"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Ecrivez votre réponse ici :"); //New for 0.98finalRC1
define("_PS_DATE", "Entrez une date :"); //New for 0.98finalRC1
define("_PS_COMMENT", "Faites le commentaire de votre choix ici :"); //New for 0.98finalRC1
define("_PS_RANKING", "Numérotez chaque case dans l'ordre de vos préférences de 1 à"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Envoyer votre questionnaire."); //New for 0.98finalRC1
define("_PS_THANKYOU", "Merci d'avoir complété ce questionnaire."); //New for 0.98finalRC1
define("_PS_FAXTO", "SVP faxez ce questionnaire rempli à :"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Répondez à cette question"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "si vous avez répondu"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "et"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "à la  question"); //New for 0.98finalRC1
define("_PS_CON_OR", "ou"); //New for 0.98finalRC2

//Save Messages
define("_SAVE_AND_RETURN", "Sauvegarder vos réponses et continuer le questionnaire");
define("_SAVEHEADING", "Sauvegarde des réponses partielles");
define("_RETURNTOSURVEY", "Retourner au questionnaire");
define("_SAVENAME", "Nom");
define("_SAVEPASSWORD", "Mot de passe");
define("_SAVEPASSWORDRPT", "Répétez le mot de passe");
define("_SAVE_EMAIL", "Votre adresse mail");
define("_SAVEEXPLANATION", "Entrez un nom et un mot de passe pour ce questionnaire et cliquez sur Sauvegarder en bas.<br />\n"
				  ."Vos réponses au questionnaire seront sauvegardées en utilisant ce nom et ce mot de passe, et pourront "
				  ."être complétées plus tard en vous connectant avec ce nom et ce mot de passe.<br /><br />\n"
				  ."Si vous donnez une adresse mail, un mail contenant ces détails vous sera envoyé");
define("_SAVESUBMIT", "Sauvegarder maintenant");
define("_SAVENONAME", "Vous devez fournir un nom pour sauvegarder vos réponses à ce questionnaire.");
define("_SAVENOPASS", "Vous devez fournir un mot de passe pour sauvegarder vos réponses à ce questionnaire.");
define("_SAVENOPASS2", "Vous devez entrer de nouveau le mot de passe pour cette sauvegarde.");
define("_SAVENOMATCH", "Vos mots de passe ne correspondent pas.");
define("_SAVEDUPLICATE", "Ce nom a déjà été utilisé pour ce questionnaire. Vous devez en choisir un autre.");
define("_SAVETRYAGAIN", "Essayer encore SVP.");
define("_SAVE_EMAILSUBJECT", "Détails sur le questionnaire que vous avez sauvegardé");
define("_SAVE_EMAILTEXT", "Vous, ou quelqu'un utilisant votre adresse mail, a sauvegardé "
						 ."ses réponses partielles à un questionnaire. Les informations suivantes peuvent être utilisées "
						 ."pour retourner à ce questionnaire et le continuer où vous en étiez.");
define("_SAVE_EMAILURL", "Rechargez votre questionnaire en cliquant sur l'URL suivante :");
define("_SAVE_SUCCEEDED", "Vos réponses à ce questionnaire ont été sauvegardées avec succès");
define("_SAVE_FAILED", "Une erreur est survenue et vos réponses n'ont pas été sauvegardées.");
define("_SAVE_EMAILSENT", "Un mail vous a été envoyé avec les détails de ce questionnaire.");

//Load Messages
define("_LOAD_SAVED", "Chargement des réponses déjà enregistrées pour ce questionnaire");
define("_LOADHEADING", "Chargement d'un questionnaire précédemment sauvegardé");
define("_LOADEXPLANATION", "Vous pouvez charger un questionnaire que vous avez précédemment sauvegardé depuis cet écran.<br />\n"
			  ."Entrez le nom et le mot de passe utilisés lors de la sauvegarde.<br /><br />\n");
define("_LOADNAME", "Nom");
define("_LOADPASSWORD", "Mot de passe");
define("_LOADSUBMIT", "Charger maintenant");
define("_LOADNONAME", "Vous n'avez pas fourni de nom");
define("_LOADNOPASS", "Vous n'avez pas fourni de mot de passe");
define("_LOADNOMATCH", "Pas de questionnaire correspondant enregistré");

define("_ASSESSMENT_HEADING", "Votre évaluation");
?>
