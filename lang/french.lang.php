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
define("_OTHER", "Autre");
define("_PLEASECHOOSE", "Choisissez s'il vous plaît");
define("_ERROR", "Erreur");
define("_COMPLETE", "terminé");
//from questions.php
define("_CONFIRMATION", "Confirmation");
define("_TOKEN", "Invitation");
define("_CONTINUE", "Continuer");

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
define("_NUMERICAL", "Seuls les chiffres sont autorisés pour ce champ");
define("_CLEARALL", "Sortir et effacer ce sondage");
define("_MANDATORY", "Cette question est obligatoire");
define("_MANDATORY_PARTS", "Compléter toutes les parties SVP");
define("_MANDATORY_CHECK", "Veuillez choisir au moins un élément SVP");
define("_MANDATORY_RANK", "Veuillez classer tous les éléments SVP");
define("_DATEFORMAT", "Format: AAAA-MM-JJ");
define("_DATEFORMATEG", "(ex: 2003-12-25 pour Noël)");
define("_REMOVEITEM", "Enlever cet élément");
define("_RANK_1", "Cliquer sur un élément dans la liste de gauche, en commençant par");
define("_RANK_2", "l'élément le plus important pour finir par le moins important.");
define("_YOURCHOICES", "Vos choix");
define("_YOURRANKING", "Votre classement");
define("_RANK_3", "Cliquer sur les ciseaux à droite de chaque élément");
define("_RANK_4", "pour enlever le dernier élément de votre classement");
//From INDEX.PHP
define("_NOSID", "Vous n'avez pas fourni d'identifiant de sondage");
define("_CONTACT1", "Veuillez contacter");
define("_CONTACT2", "pour avoir plus d'aide");
define("_ANSCLEAR", "Réponses effacées");
define("_RESTART", "Recommencer ce sondage");
define("_CLOSEWIN", "Fermer cette fenêtre");
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
define("_DNSAVEEMAIL2", "INFORMATION A RENSEIGNER");
define("_DNSAVEEMAIL3", "CODE SQL QUI A ECHOUE");
define("_DNSAVEEMAIL4", "MESSAGE D'ERREUR");
define("_DNSAVEEMAIL5", "ERREUR DE SAUVERGARDE");
define("_SUBMITAGAIN", "Essayez d'envoyer à nouveau");
define("_SURVEYNOEXIST", "Désolé. Il n'y a pas de sondage correspondant.");
define("_NOTOKEN1", "C'est un sondage privé. Vous devez avoir une invitation pour y participer.");
define("_NOTOKEN2", "Si vous avez reçu une invitation, entrez là dans le champ ci-dessous et cliquez sur Continuer.");
define("_NOTOKEN3", "L'invitation que vous avez reçu n'est pas valide, ou a déjà été utilisée.");
define("_NOQUESTIONS", "Ce sondage n'a plus de questions et ne peut être testé ou finalisé.");
define("_FURTHERINFO", "Pour plus d'information veuillez contacter");
define("_NOTACTIVE", "Ce sondage n'est pas actif. Vous ne pourrez pas sauver vos réponses.");

?>
