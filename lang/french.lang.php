<?php
/*
	#############################################################
	# >>> PHP Surveyor  										#
	#############################################################
	# > Author:  Jason Cleeland									#
	# > E-mail:  jason@cleeland.org								#
	# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
	# >          CARLTON SOUTH 3053, AUSTRALIA					#
	# > Date: 	 20 February 2003								#
	#															#
	# This set of scripts allows you to develop, publish and	#
	# perform data-entry on surveys.							#
	#############################################################
	#															#
	#	Copyright (C) 2003  Jason Cleeland						#
	#															#
	# This program is free software; you can redistribute 		#
	# it and/or modify it under the terms of the GNU General 	#
	# Public License as published by the Free Software 			#
	# Foundation; either version 2 of the License, or (at your 	#
	# option) any later version.								#
	#															#
	# This program is distributed in the hope that it will be 	#
	# useful, but WITHOUT ANY WARRANTY; without even the 		#
	# implied warranty of MERCHANTABILITY or FITNESS FOR A 		#
	# PARTICULAR PURPOSE.  See the GNU General Public License 	#
	# for more details.											#
	#															#
	# You should have received a copy of the GNU General 		#
	# Public License along with this program; if not, write to 	#
	# the Free Software Foundation, Inc., 59 Temple Place - 	#
	# Suite 330, Boston, MA  02111-1307, USA.					#
	#############################################################
	#															#
	# This language file kindly provided by François Tissandier	#
	#															#
	#############################################################
*/
//SINGLE WORDS
define("_YES", "Oui");
define("_NO", "Non");
define("_UNCERTAIN", "Je ne sais pas");
define("_ADMIN", "Administrateur");
define("_TOKENS", "Invitations");
define("_FEMALE", "Femme");
define("_MALE", "Homme");
define("_NOANSWER", "Pas de réponse");
define("_NOTAPPLICABLE", "N/A"); //New for 0.98rc5
define("_OTHER", "Autre");
define("_PLEASECHOOSE", "Choisissez s'il vous plaît");
define("_ERROR_PS", "Erreur");
define("_COMPLETE", "Terminé");
define("_INCREASE", "Augmentation"); //NEW WITH 0.98 BABELFISH TRANSLATION
define("_SAME", "Mêmes"); //NEW WITH 0.98 BABELFISH TRANSLATION
define("_DECREASE", "Diminution"); //NEW WITH 0.98 BABELFISH TRANSLATION
//from questions.php
define("_CONFIRMATION", "Confirmation");
define("_TOKEN_PS", "Invitation");
define("_CONTINUE_PS", "Continuer");

//BUTTONS
define("_ACCEPT", "Accepter");
define("_PREV", "Précédent");
define("_NEXT", "Suivant");
define("_LAST", "Dernier");
define("_SUBMIT", "Envoyer");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Choisissez une des réponses suivantes SVP");
define("_ENTERCOMMENT", "Ajoutez votre commentaire ici SVP");
define("_NUMERICAL_PS", "Seuls les chiffres sont autorisés pour ce champ");
define("_CLEARALL", "Sortir et effacer ce sondage");
define("_MANDATORY", "Cette question est obligatoire");
define("_MANDATORY_PARTS", "Complétez toutes les parties SVP");
define("_MANDATORY_CHECK", "Veuillez choisir au moins un élément SVP");
define("_MANDATORY_RANK", "Veuillez classer tous les éléments SVP");
define("_MANDATORY_POPUP", "Une ou plusieurs questions obligatoires n'ont pas été répondues. Vous ne pouvez pas procéder jusqu'à ce que ceux-ci aient été accomplis"); //NEW in 0.98rc4
define("_DATEFORMAT", "Format: AAAA-MM-JJ");
define("_DATEFORMATEG", "(ex: 2003-12-25 pour Noël)");
define("_REMOVEITEM", "Enlever cet élément");
define("_RANK_1", "Cliquez sur un élément dans la liste de gauche, en commençant par");
define("_RANK_2", "l'élément le plus important pour finir par le moins important.");
define("_YOURCHOICES", "Vos choix");
define("_YOURRANKING", "Votre classement");
define("_RANK_3", "Cliquez sur les ciseaux à droite de chaque élément");
define("_RANK_4", "pour enlever le dernier élément de votre classement");
//From INDEX.PHP
define("_NOSID", "Vous n'avez pas fourni d'identifiant de sondage");
define("_CONTACT1", "Veuillez contacter");
define("_CONTACT2", "pour avoir plus d'aide");
define("_ANSCLEAR", "Réponses effacées");
define("_RESTART", "Recommencer ce sondage");
define("_CLOSEWIN_PS", "Fermer cette fenêtre");
define("_CONFIRMCLEAR", "Etes-vous sûr de vouloir effacer toutes les réponses?");
define("_EXITCLEAR", "Sortir et effacer le sondage");
//From QUESTION.PHP
define("_BADSUBMIT1", "Réponses non envoyées car vides.");
define("_BADSUBMIT2", "Cette erreur peut se produire si vous avez déjà envoyé vos réponses et pressé le bouton \"Rafraîchir\" de votre naviguateur. Dans ce cas, vos réponses ont déjà été sauvées.");
define("_NOTACTIVE1", "Vos réponses n'ont pas été enregistrées. Ce sondage n'est pas encore actif.");
define("_CLEARRESP", "Effacer les réponses");
define("_THANKS", "Merci");
define("_SURVEYREC", "Vos réponses ont été enregistrées.");
define("_SURVEYCPL", "Sondage complété");
define("_DIDNOTSAVE", "Non sauvegardé");
define("_DIDNOTSAVE2", "Une erreur non prévue s'est produite et vos réponses n'ont pas pu être sauvées.");
define("_DIDNOTSAVE3", "Vos réponses n'ont pas été perdues et ont été emailées à l'administrateur du sondage qui les entrera dans la base de données ultérieurement.");
define("_DNSAVEEMAIL1", "Une erreur s'est produit pendant la sauvegarde d'une réponse");
define("_DNSAVEEMAIL2", "DONNEES A ENTRER");
define("_DNSAVEEMAIL3", "CODE SQL QUI A ECHOUE");
define("_DNSAVEEMAIL4", "MESSAGE D'ERREUR");
define("_DNSAVEEMAIL5", "ERREUR DE SAUVEGARDE");
define("_SUBMITAGAIN", "Essayez d'envoyer à nouveau");
define("_SURVEYNOEXIST", "Désolé. Il n'y a pas de sondage correspondant.");
define("_NOTOKEN1", "C'est un sondage privé. Vous devez avoir une invitation pour y participer.");
define("_NOTOKEN2", "Si vous avez reçu une invitation, entrez la dans le champ ci-dessous et cliquez sur Continuer.");
define("_NOTOKEN3", "L'invitation que vous avez reçue n'est pas valide, ou a déjà été utilisée.");
define("_NOQUESTIONS", "Ce sondage n'a plus de questions et ne peut être testé ou finalisé.");
define("_FURTHERINFO", "Pour plus d'informations veuillez contacter");
define("_NOTACTIVE", "Ce sondage n'est pas actif. Vous ne pourrez pas sauver vos réponses.");
define("_SURVEYEXPIRED", "This survey is no longer available."); //NEW for 098rc5

define("_SURVEYCOMPLETE", "Vous avez déjà accompli cet aperçu.");

define("_INSTRUCTION_LIST", "Choisissez seulement un du suivant"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "En vérifiez qui s'appliquent"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "L'Aperçu A soumis"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Une nouvelle réponse a été écrite pour votre aperçu"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Cliquetez le lien suivant pour voir la réponse individuelle:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Statistiques de vue en cliquetant ici:"); //NEW for 098rc5

define("_PRIVACY_MESSAGE", "<b><i>A Note On Privacy</i></b><br />"
						  ."This survey is anonymous.<br />"
						  ."The record kept of your survey responses does not contain any "
						  ."identifying information about you unless a specific question "
						  ."in the survey has asked for this. If you have responded to a "
						  ."survey that used an identifying token to allow you to access "
						  ."the survey, you can rest assured that the identifying token "
						  ."is not kept with your responses. It is managed in a seperate "
						  ."database, and will only be updated to indicate that you have "
						  ."(or haven't) completed this survey. There is no way of matching "
						  ."identification tokens with survey responses in this survey."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "There are {NUMBEROFQUESTIONS} questions in this survey."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "There is 1 question in this survey."); //New for 0.98rc9 - singular version of above

define ("_RG_REGISTER1", "You must be registered to complete this survey"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "You may register for this survey if you wish to take part.<br />\n"
						."Enter your details below, and an email containing the link to "
						."participate in this survey will be sent immediately."); //NEW for 0.98rc9
define ("_RG_EMAIL", "Email Address"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "First Name"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Last Name"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "The email you used is not valid. Please try again.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "The email you used has already been registered.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} Registration Confirmation");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Thank you for registering to participate in this survey.<br /><br />\n"
								   ."An email has been sent to the address you provided with access details "
								   ."for this survey. Please follow the link in that email to proceed.<br /><br />\n"
								   ."Survey Administrator {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<b>Thank You<br /><br />"
					   ."You have completed answering the questions in this survey.</b><br /><br />"
					   ."Click on ["._SUBMIT."] now to complete the process and save your answers."); //New for 0.98finalRC1
define("_SM_REVIEW", "If you want to check any of the answers you have made, and/or change them, "
					."you can do that now by clicking on the [<< "._PREV."] button and browsing "
					."through your responses."); //New for 0.98finalRC1
?>