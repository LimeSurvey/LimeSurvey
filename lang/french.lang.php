<?php
/*
        #################################################################
        # >>> PHP Surveyor                                              #
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
        # and corrected by Pascal Bastien 20/07/2004                    #
        # Version 1.3                                                   #
        #                                                               #
        # Version 1.5.0 - corrected by Sébastien GAUGRY                 #
        # Note for french translators : ' is &#146;                     #
        #                                                               #
        #################################################################
*/
//SINGLE WORDS
define("_YES", "Oui");
define("_NO", "Non");
define("_UNCERTAIN", "Indiff&eacute;rent");
define("_ADMIN", "Administrateur");
define("_TOKENS", "Invitations");
define("_FEMALE", "Femme");
define("_MALE", "Homme");
define("_NOANSWER", "Sans r&eacute;ponse");
define("_NOTAPPLICABLE", "N/A"); //New for 0.98rc5
define("_OTHER", "Autre");
define("_PLEASECHOOSE", "Veuillez choisir");
define("_ERROR_PS", "Erreur");
define("_COMPLETE", "Termin&eacute;");
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
define("_CHOOSEONE", "Veuillez s&eacute;lectionner une r&eacute;ponse ci-dessous");
define("_ENTERCOMMENT", "Veuillez saisir votre commentaire ici");
define("_NUMERICAL_PS", "Seuls les chiffres sont autoris&eacute;s pour ce champ");
define("_CLEARALL", "Sortir et effacer ce questionnaire");
define("_MANDATORY", "Cette question est obligatoire");
define("_MANDATORY_PARTS", "Veuillez compl&eacute;ter toutes les parties SVP");
define("_MANDATORY_CHECK", "Veuillez choisir au moins un &eacute;l&eacute;ment SVP");
define("_MANDATORY_RANK", "Veuillez classer tous les &eacute;l&eacute;ments SVP");
define("_MANDATORY_POPUP", "Vous n'avez pas répondu à une ou plusieurs questions obligatoires. Vous ne pouvez pas enregistrer vos réponses au questionnaire tant que vous n'avez répondu à celles-ci"); //NEW in 0.98rc4
define("_VALIDATION", "Vous devez r&eacute;pondre correctement &agrave; cette question"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "Vous n&#146;avez pas r&eacute;pondu correctement &agrave; une ou plusieurs questions. Vous ne pouvez pas continuer tant que ces r&eacute;ponses ne sont pas valides"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Format : AAAA-MM-JJ");
define("_DATEFORMATEG", "(ex : 2003-12-25 pour No&euml;l)");
define("_REMOVEITEM", "Enlever cet &eacute;l&eacute;ment");
define("_RANK_1", "Cliquez sur un &eacute;l&eacute;ment dans la liste de gauche ci-dessous.");
define("_RANK_2", "Choisissez l&acute;&eacute;l&eacute;ment le plus important pour finir par le moins important.");
define("_YOURCHOICES", "Vos choix");
define("_YOURRANKING", "Votre classement");
define("_RANK_3", "Cliquer sur les ciseaux &agrave; droite de chaque &eacute;l&eacute;ment");
define("_RANK_4", "pour enlever le dernier &eacute;l&eacute;ment de votre classement");
//From INDEX.PHP
define("_NOSID", "Vous n&acute;avez pas fourni d&acute;identifiant de questionnaire");
define("_CONTACT1", "Veuillez contacter");
define("_CONTACT2", "pour plus d&#146;aide");
define("_ANSCLEAR", "R&eacute;ponses effac&eacute;es");
define("_RESTART", "Recommencer ce questionnaire");
define("_CLOSEWIN_PS", "Fermer cette fen&ecirc;tre");
define("_CONFIRMCLEAR", "Etes-vous s&ucirc;r de vouloir effacer toutes les r&eacute;ponses ?");
define("_CONFIRMSAVE", "Etes-vous s&ucirc;r de vouloir sauvegarder vos réeacute;ponses ?");
define("_EXITCLEAR", "Sortir et effacer le questionnaire");
//From QUESTION.PHP
define("_BADSUBMIT1", "Impossible d&#146;envoyer les r&eacute;ponses car il n&#146;y en a aucune (vide).");
define("_BADSUBMIT2", "Cette erreur peut se produire si vous avez d&eacute;j&agrave; envoy&eacute; vos r&eacute;ponses et actualis&eacute; la page de votre naviguateur avec \"Actualiser\". Dans ce cas, vos r&eacute;ponses ont d&eacute;j&agrave; &eacute;t&eacute; sauv&eacute;es.");
define("_NOTACTIVE1", "Vos r&eacute;ponses n&#146;ont pas &eacute;t&eacute; enregistr&eacute;es. Ce questionnaire n&acute;est pas encore activ&eacute;.");
define("_CLEARRESP", "Effacer les r&eacute;ponses");
define("_THANKS", "Merci");
define("_SURVEYREC", "Vos r&eacute;ponses ont &eacute;t&eacute; enregistr&eacute;es.");
define("_SURVEYCPL", "Questionnaire compl&eacute;t&eacute;");
define("_DIDNOTSAVE", "Non sauvegard&eacute;");
define("_DIDNOTSAVE2", "Une erreur non pr&eacute;vue s&#146;est produite et vos r&eacute;ponses n&#146;ont pas pu &ecirc;tre sauv&eacute;es.");
define("_DIDNOTSAVE3", "Vos r&eacute;ponses n&#146;ont pas &eacute;t&eacute; perdues et ont &eacute;t&eacute; mail&eacute;es &agrave; l&#146;administrateur du questionnaire qui les saisira ult&eacute;rieurement dans la base de donn&eacute;es.");
define("_DNSAVEEMAIL1", "Une erreur s&#146;est produite pendant la sauvegarde d&#146;une r&eacute;ponse");
define("_DNSAVEEMAIL2", "DONNEES A SAISIR");
define("_DNSAVEEMAIL3", "CODE SQL QUI A ECHOUE");
define("_DNSAVEEMAIL4", "MESSAGE D&#146;ERREUR");
define("_DNSAVEEMAIL5", "ERREUR DE SAUVEGARDE");
define("_SUBMITAGAIN", "Essayez d&#146;envoyer &agrave; nouveau");
define("_SURVEYNOEXIST", "D&eacute;sol&eacute;. Il n&#146;y a pas de questionnaire correspondant.");
define("_NOTOKEN1", "C&#146;est un questionnaire priv&eacute;. Vous devez avoir une invitation pour y participer.");
define("_NOTOKEN2", "Si vous avez re&ccedil;u une invitation, saisissez-la dans le champ ci-dessous et cliquez sur Continuer.");
define("_NOTOKEN3", "L&#146;invitation que vous avez re&ccedil;ue n&#146;est pas valide, ou a d&eacute;j&agrave; &eacute;t&eacute; utilis&eacute;e.");
define("_NOQUESTIONS", "Ce questionnaire n&#146;a pas encore de question et ne peut &ecirc;tre test&eacute; ou finalis&eacute;.");
define("_FURTHERINFO", "Pour plus d&#146;informations veuillez contacter");
define("_NOTACTIVE", "Ce questionnaire n&#146;est pas activ&eacute;. Vous ne pourrez pas sauver vos r&eacute;ponses.");
define("_SURVEYEXPIRED", "Ce questionnaire n&#146;est plus disponible."); //NEW for 098rc5

define("_SURVEYCOMPLETE", "Vous avez d&eacute;j&agrave; compl&eacute;t&eacute; ce questionnaire.");

define("_INSTRUCTION_LIST", "Veuillez s&eacute;lectionner seulement une r&eacute;ponse ci-dessous"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Cochez la ou les r&eacute;ponses"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Questionnaire envoy&eacute;"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Une nouvelle r&eacute;ponse a &eacute;t&eacute; saisie dans votre questionnaire"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Cliquez sur le lien suivant pour voir votre r&eacute;ponse personnelle"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Visualiser les statistiques en cliquant ici"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE5", "Cliquez sur le lien suivant pour &eacute;diter cette réponse :"); //NEW for 0.99stable

define("_PRIVACY_MESSAGE", "<strong><i>Note sur la protection de la vie priv&eacute;e</i></strong><br />"
                                                  ."Ce questionnaire est anonyme.<br />"
                                                  ."Les enregistrements conserv&eacute;s de votre questionnaire ne contiennent aucune "
                                                  ."information d&#146;identification &agrave; moins bien s&ucirc;r qu&#146;une question  "
                                                  ."sur votre identit&eacute; ait &eacute;t&eacute; pos&eacute;e dans le questionnaire. Si vous avez r&eacute;pondu &agrave; "
                                                  ."un questionnaire utilisant une invitation pour vous permettre d&#146;y acc&eacute;der, "
                                                  ."vous pouvez &ecirc;tre assur&eacute; que cet identifiant. "
                                                  ."n&#146;est pas conserv&eacute; avec vos r&eacute;ponses. Il est ger&eacute; dans une base de donn&eacute;es s&eacute;par&eacute;e "
                                                  ."et ne pourra pas &ecirc;tre mis &agrave; jour pour indiquer que vous "
                                                  ."avez compl&eacute;t&eacute; ce questionnaire. Il n&#146;y a aucun moyen pour faire correspondre "
                                                  ."les invitations avec les r&eacute;ponses au questionnaire."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "Il y a {NUMBEROFQUESTIONS} questions dans ce questionnaire."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Il y a 1 question dans ce questionnaire."); //New for 0.98rc9 - singular version of above

define ("_RG_REGISTER1", "Vous devez &ecirc;tre enregistr&eacute; pour r&eacute;pondre &agrave; ce questionnaire"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Vous devez &ecirc;tre enregistr&eacute; pour ce questionnaire si vous d&eacute;sirez y participer.<br />\n"
                                                ."Saisissez vos coordonn&eacute;es ci-dessous, et un mail contenant le lien pour "
                                                ."participer &agrave; ce questionnaire vous sera imm&eacute;diatement envoy&eacute;."); //NEW for 0.98rc9
define ("_RG_EMAIL", "Adresse mail"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Nom"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Pr&eacute;nom"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "L&#146;adresse mail utilis&eacute;e n&#146;est pas valide. Veuillez re&eacute;ssayer.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "L&#146;adresse mail utilis&eacutee; a d&eacute;j&agrave; &eacute;t&eacute; enregistr&eacute;.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "Confirmation d&#146;enregistrement de {SURVEYNAME}");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Merci de vous enregistrer pour participer &agrave; ce questionnaire.<br /><br />\n"
                                                                   ."Un mail a &eacute;t&eacute; envoy&eacute; &agrave; l&#146;adresse que vous avez fournie dans les d&eacute;tails d&#146;acc&eacute;s "
                                                                   ."pour ce questionnaire. Veuillez suivre le lien dans ce mail pour participer.<br /><br />\n"
                                                                   ."Administrateur du questionnaire {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<strong>Merci<br /><br />"
					   ."Vous venez de r&eacute;pondre &agrave; l&#146;ensemble des questions de ce questionnaire.</strong><br /><br />"
					   ."Veuillez cliquer sur le bouton ["._SUBMIT."], afin de proc&eacute;der &agrave; l&#146;enregistrement de vos r&eacute;ponses."); //New for 0.98finalRC1
define("_SM_REVIEW", "Si vous souhaitez v&eacute;rifier ou changer certaines de vos r&eacute;ponses, "
					."vous pouvez le faire en cliquant sur le bouton ["._PREV."] en bas de cette page, "
					."afin de passer en revue vos r&eacute;ponses.");
					
//For the "printable" survey
define("_PS_CHOOSEONE", "Choisissez <strong>seulement une</strong> des r&eacute;ponses suivantes :"); //New for 0.98finalRC1
define("_PS_WRITE", "Ecrivez votre r&eacute;ponse ici :"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Choisissez <strong>toutes</strong> les r&eacute;ponses qui conviennent :"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Choisissez toutes les r&eacute;ponses qui conviennent et laissez un commentaire :"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Choisissez la r&eacute;ponse appropri&eacute;e pour chaque &eacute;l&eacute;ment :"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Ecrivez votre r&eacute;ponse ici :"); //New for 0.98finalRC1
define("_PS_DATE", "Entrez une date :"); //New for 0.98finalRC1
define("_PS_COMMENT", "Faites le commentaire de votre choix ici :"); //New for 0.98finalRC1
define("_PS_RANKING", "Num&eacute;rotez chaque case dans l&#146;ordre de vos pr&eacute;f&eacute;rences de 1 &agrave;"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Envoyer votre questionnaire."); //New for 0.98finalRC1
define("_PS_THANKYOU", "Merci d&#146;avoir compl&eacute;t&eacute; ce questionnaire."); //New for 0.98finalRC1
define("_PS_FAXTO", "SVP faxez ce questionnaire rempli &agrave; :"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "R&eacute;pondez &agrave; cette question"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "si vous avez r&eacute;pondu"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "et"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "&agrave; la  question"); //New for 0.98finalRC1
define("_PS_CON_OR", "ou"); //New for 0.98finalRC2

//Save Messages
define("_SAVE_AND_RETURN", "Sauvegarder vos r&eacute;ponses et continuer le questionnaire");
define("_SAVEHEADING", "Sauvegarde des r&eacute;ponses partielles");
define("_RETURNTOSURVEY", "Retourner au questionnaire");
define("_SAVENAME", "Nom");
define("_SAVEPASSWORD", "Mot de passe");
define("_SAVEPASSWORDRPT", "R&eacute;p&eacute;tez le mot de passe");
define("_SAVE_EMAIL", "Votre adresse mail");
define("_SAVEEXPLANATION", "Entrez un nom et un mot de passe pour ce questionnaire et cliquez sur Sauvegarder en bas.<br />\n"
				  ."Vos r&eacute;ponses au questionnaire seront sauvegard&eacute;es en utilisant ce nom et ce mot de passe, et pourront "
				  ."&ecirc;tre compl&eacute;t&eacute;es plus tard en vous connectant avec ce nom et ce mot de passe.<br /><br />\n"
				  ."Si vous donnez une adresse mail, un mail contenant ces d&eacute;tails vous sera envoy&eacute;");
define("_SAVESUBMIT", "Sauvegarder maintenant");
define("_SAVENONAME", "Vous devez fournir un nom pour sauvegarder vos r&eacute;ponses &agrave; ce questionnaire.");
define("_SAVENOPASS", "Vous devez fournir un mot de passe pour sauvegarder vos r&eacute;ponses &agrave; ce questionnaire.");
define("_SAVENOMATCH", "Vos mots de passe ne correspondent pas.");
define("_SAVEDUPLICATE", "Ce nom a d&eacute;j&agrave; &eacute;t&eacute; utilis&eacute; pour ce questionnaire. Vous devez en choisir un autre.");
define("_SAVETRYAGAIN", "Essayer encore SVP.");
define("_SAVE_EMAILSUBJECT", "D&eacute;tails sur le questionnaire que vous avez sauvegard&eacute;");
define("_SAVE_EMAILTEXT", "Vous, ou quelqu&#146;un utilisant votre adresse mail, a sauvegard&eacute; "
						 ."ses r&eacute;ponses partielles &agrave; un questionnaire. Les informations suivantes peuvent &ecirc;tre utilis&eacute;es "
						 ."pour retourner &agrave; ce questionnaire et le continuer o&ugrave; vous en &eacute;tiez.");
define("_SAVE_EMAILURL", "Rechargez votre questionnaire en cliquant sur l&#146;URL suivante :");
define("_SAVE_SUCCEEDED", "Vos r&eacute;ponses &agrave; ce questionnaire ont &eacute;t&eacute, sauvegard&eacute;es avec succ&egrave;s");
define("_SAVE_FAILED", "Une erreur est survenue et vos r&eacute;ponses n&#146;ont pas &eacute;t&eacute; sauvegard&eacute;es.");
define("_SAVE_EMAILSENT", "Un mail vous a &eacute;t&eacute; envoy&eacute; avec les d&eacute;tails de ce questionnaire.");

//Load Messages
define("_LOAD_SAVED", "Chargement des r&eacute;ponses d&eacute;j&agrave; enregistr&eacute;es pour ce questionnaire");
define("_LOADHEADING", "Chargement d&#146;un questionnaire pr&eacute;c&eacute;demment sauvegard&eacute;");
define("_LOADEXPLANATION", "Vous pouvez charger un questionnaire que vous avez pr&eacute;c&eacute;demment sauvegard&eacute; depuis cet &eacute;cran.<br />\n"
			  ."Entrez le nom et le mot de passe utilis&eacute;s lors de la sauvegarde.<br /><br />\n");
define("_LOADNAME", "Nom");
define("_LOADPASSWORD", "Mot de passe");
define("_LOADSUBMIT", "Charger maintenant");
define("_LOADNONAME", "Vous n&#146;avez pas fourni de nom");
define("_LOADNOPASS", "Vous n&#146;avez pas fourni de mot de passe");
define("_LOADNOMATCH", "Pas de questionnaire correspondant enregistr&eacute;");

define("_ASSESSMENT_HEADING", "Votre &eacute;valuation");
?>
