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
//from questions.php
define("_CONFIRMATION", "Confirmation");
define("_TOKEN_PS", "Invitation");
define("_CONTINUE_PS", "Continuer");

//BUTTONS
define("_ACCEPT", "Accepter");
define("_PREV", "précédent");
define("_NEXT", "suivant");
define("_LAST", "fin");
define("_SUBMIT", "envoyer");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Veuillez s&eacute;lectionner une r&eacute;ponse ci-dessous");
define("_ENTERCOMMENT", "Veuillez saisir votre commentaire ici");
define("_NUMERICAL_PS", "Seuls les chiffres sont autoris&eacute;s pour ce champ");
define("_CLEARALL", "Sortir et effacer ce questionnaire");
define("_MANDATORY", "Cette question est obligatoire");
define("_MANDATORY_PARTS", "Veuillez Compl&eacute;ter toutes les parties SVP");
define("_MANDATORY_CHECK", "Veuillez choisir au moins un &eacute;l&eacute;ment SVP");
define("_MANDATORY_RANK", "Veuillez classer tous les &eacute;l&eacute;ments SVP");
define("_MANDATORY_POPUP", "Vous n&acute;avez pas r&eacute;pondu &agrave; une ou plusieurs questions obligatoires. Vous ne pouvez pas r&eacute;pondre au questionnaire tant que vous n&acute;avez r&eacute;pondu &agrave; celles-ci"); //NEW in 0.98rc4
define("_DATEFORMAT", "Format: AAAA-MM-JJ");
define("_DATEFORMATEG", "(ex: 2003-12-25 pour Noël)");
define("_REMOVEITEM", "Enlever cet &eacute;l&eacute;ment");
define("_RANK_1", "Cliquez sur un &eacute;l&eacute;ment dans la liste de gauche ci-dessous.");
define("_RANK_2", "Choisissez l&acute;&eacute;l&eacute;ment le plus important pour finir par le moins important.");
define("_YOURCHOICES", "Vos choix");
define("_YOURRANKING", "Votre classement");
define("_RANK_3", "Cliquer sur les ciseaux &agrave; droite de chaque &eacute;l&eacute;ment");
define("_RANK_4", "pour enlever le dernier &eacute;l&eacute;ment de votre classement");
//From INDEX.PHP
define("_NOSID", "Vous n&acute;avez pas fourni d&acute;identifiant de sondage");
define("_CONTACT1", "Veuillez contacter");
define("_CONTACT2", "pour plus d&acute;aide");
define("_ANSCLEAR", "R&eacute;ponses effac&eacute;es");
define("_RESTART", "Recommencer ce sondage");
define("_CLOSEWIN_PS", "Fermer cette fen&ecirc;tre");
define("_CONFIRMCLEAR", "Etes-vous s&ucirc;r de vouloir effacer toutes les r&eacute;ponses?");
define("_EXITCLEAR", "Sortir et effacer le questionnaire");
//From QUESTION.PHP
define("_BADSUBMIT1", "Impossible d&acute;envoyer les R&eacute;ponses car il n&acute;y en a aucune (vides).");
define("_BADSUBMIT2", "Cette erreur peut se produire si vous avez d&eacute;j&agrave; envoy&eacute; vos r&eacute;ponses et actualis&eacute; la page de votre naviguateur avec \"Actualiser\". Dans ce cas, vos r&eacute;ponses ont d&eacute;j&agrave; &eacute;t&eacute; sauv&eacute;es.");
define("_NOTACTIVE1", "Vos r&eacute;ponses n&acute;ont pas &eacute;t&eacute; enregistr&eacute;es. Ce questionnaire n&acute;est pas encore activ&eacute;.");
define("_CLEARRESP", "Effacer les r&eacute;ponses");
define("_THANKS", "Merci");
define("_SURVEYREC", "Vos r&eacute;ponses ont &eacute;t&eacute; enregistr&eacute;es.");
define("_SURVEYCPL", "Sondage compl&eacute;t&eacute;");
define("_DIDNOTSAVE", "Non sauvegard&eacute;");
define("_DIDNOTSAVE2", "Une erreur non pr&eacute;vue s&acute;est produite et vos r&eacute;ponses n&acute;ont pas pu &ecirc;tre sauv&eacute;es.");
define("_DIDNOTSAVE3", "Vos r&eacute;ponses n&acute;ont pas &eacute;t&eacute; perdues et ont &eacute;t&eacute; email&eacute;es &agrave; l&acute;administrateur du questionnaire qui les saisira ult&eacute;rieurement dans la base de donn&eacute;es.");
define("_DNSAVEEMAIL1", "Une erreur s&acute;est produit pendant la sauvegarde d&acute;une r&eacute;ponse");
define("_DNSAVEEMAIL2", "DONNEES A SAISIR");
define("_DNSAVEEMAIL3", "CODE SQL QUI A ECHOUE");
define("_DNSAVEEMAIL4", "MESSAGE D&acute;ERREUR");
define("_DNSAVEEMAIL5", "ERREUR DE SAUVEGARDE");
define("_SUBMITAGAIN", "Essayez d&acute;envoyer &agrave; nouveau");
define("_SURVEYNOEXIST", "D&eacute;sol&eacute;. Il n&acute;y a pas de sondage correspondant.");
define("_NOTOKEN1", "C&acute;est un sondage priv&eacute;. Vous devez avoir une invitation pour y participer.");
define("_NOTOKEN2", "Si vous avez re&ccedil;u une invitation, saisissez-la dans le champ ci-dessous et cliquez sur Continuer.");
define("_NOTOKEN3", "L&acute;invitation que vous avez re&ccedil;ue n&acute;est pas valide, ou a d&eacute;j&agrave; &eacute;t&eacute; utilis&eacute;e.");
define("_NOQUESTIONS", "Ce questionnaire n&acute;a pas encore de questions et ne peut &ecirc;tre test&eacute; ou finalis&eacute;.");
define("_FURTHERINFO", "Pour plus d&acute;informations veuillez contacter");
define("_NOTACTIVE", "Ce sondage n&acute;est pas activ&eacute;. Vous ne pourrez pas sauver vos r&eacute;ponses.");
define("_SURVEYEXPIRED", "Ce questionnaire n&acute;est plus disponible."); //NEW for 098rc5

define("_SURVEYCOMPLETE", "Vous avez d&eacute;j&agrave; complet&eacute; ce questionnaire.");

define("_INSTRUCTION_LIST", "Veuillez s&eacute;lectionner seulement une r&eacute;ponse ci-dessous"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Cochez la (ou les) r&eacute;ponse(s)"); //NEW for 098rc3
define("_CONFIRMATION_MESSAGE1", "Questionnaire envoy&eacute;"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Une nouvelle r&eacute;ponse a &eacute;t&eacute; saisie dans votre questionnaire"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Cliquez sur le lien suivant pour voir votre r&eacute;ponse personnelle:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Visualiser les Statistiques cliquant ici:"); //NEW for 098rc5

define("_PRIVACY_MESSAGE", "<b><i>Note sur la protection de la vie priv&eacute;e</i></b><br />"
                                                  ."Ce questionnaire est anonyme.<br />"
                                                  ."Les enregistrements conserv&eacute;s de votre questionnaire ne contiennent aucune "
                                                  ."information d&acute;identification &agrave; moins bien s&ucirc;r qu&acute;un question  "
                                                  ."sur votre identit&eacute; ai &eacute;t&eacute; pos&eacute;e dans le questionnaire. Si vous avez r&eacute;pondu &agrave; "
                                                  ."un questionnaire utilisant une invitation pour vous permettre d&acute;acc&eacute;der au "
                                                  ."questionnaire, vous pouvez &ecirc;tre assur&eacute;s que cet identifiant. "
                                                  ."n&acute;est pas conserv&eacute; avec vos r&eacute;ponses. Il est ger&eacute; dans une base de donn&eacute;es s&eacute;par&eacute;e "
                                                  ."et ne pourra pas &ecirc;tre mis &agrave; jour pour indiquer que vous "
                                                  ."avez complet&eacute; ce questionnaire. Il n&acute;y a aucun moyen pour faire correspondre "
                                                  ."les invitations avec les r&eacute;ponses au questionnaire."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "Il y a {NUMBEROFQUESTIONS} questions dans ce questionnaire."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Il y a 1 question dans ce questionnaire."); //New for 0.98rc9 - singular version of above

define ("_RG_REGISTER1", "Vous devez &ecirc;tre enregistr&eacute; pour r&eacute;pondre &agrave; ce questionnaire"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Vous devez &ecirc;tre enregistr&eacute; pour ce questionnaire si vous d&eacute;sirez y participer.<br />\n"
                                                ."Saisissez vos coordonn&eacute;es ci-dessous, et un email contenant le lien pour "
                                                ."participer &agrave; ce questionnaire vous sera imm&eacute;diatement envoy&eacute;."); //NEW for 0.98rc9
define ("_RG_EMAIL", "Addresse Email"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Nom"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Pr&eacute;nom"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "L&acute;email utilis&eacute; n&acute;est pas valide. Veuillez re&eacute;ssayer.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "L&acute;email utilis&eacute; a d&eacute;j&agrave; &eacute;t&eacute; enregistr&eacute;.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "Confirmation d&acute;enregistrement de {SURVEYNAME}");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Merci de vous enregistre pour participer &agrave; ce questionnaire.<br /><br />\n"
                                                                   ."Un email a &eacute;t&eacute; envoy&eacute; &agrave; l&acute;adresse que vous avez fournie dans les d&eacute;tails d&acute;acc&eacute;s "
                                                                   ."pour ce questionnaire. Veuillez suivre ce lien dans cet email pour participer.<br /><br />\n"
                                                                   ."Administrateur du questionnaire {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9
?>
