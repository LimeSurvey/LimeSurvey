<?php
/*
    #############################################################
    # >>> PHP Surveyor                                          #
    #############################################################
    # > Author:  Jason Cleeland                                 #
    # > E-mail:  jason@cleeland.org                             #
    # > Mail:    Box 99, Trades Hall, 54 Victoria St,           #
    # >          CARLTON SOUTH 3053, AUSTRALIA                  #
    # > Date:    20 February 2003                               #
    #                                                           #
    # This set of scripts allows you to develop, publish and    #
    # perform data-entry on surveys.                            #
    #############################################################
    #                                                           #
    #   Copyright (C) 2003  Jason Cleeland                      #
    #                                                           #
    # This program is free software; you can redistribute       #
    # it and/or modify it under the terms of the GNU General    #
    # Public License as published by the Free Software          #
    # Foundation; either version 2 of the License, or (at your  #
    # option) any later version.                                #
    #                                                           #
    # This program is distributed in the hope that it will be   #
    # useful, but WITHOUT ANY WARRANTY; without even the        #
    # implied warranty of MERCHANTABILITY or FITNESS FOR A      #
    # PARTICULAR PURPOSE.  See the GNU General Public License   #
    # for more details.                                         #
    #                                                           #
    # You should have received a copy of the GNU General        #
    # Public License along with this program; if not, write to  #
    # the Free Software Foundation, Inc., 59 Temple Place -     #
    # Suite 330, Boston, MA  02111-1307, USA.                   #
    #############################################################
	#															#
	# This language file kindly provided by 					#
	# Gabriele "Pentothal" Carioli								#
	#															#
	#############################################################*/
//SINGLE WORDS
define("_YES", "S&igrave;");
define("_NO", "No");
define("_UNCERTAIN", "Non so");
define("_ADMIN", "Amministratore");
define("_TOKENS", "Token");
define("_FEMALE", "Femmina");
define("_MALE", "Maschio");
define("_NOANSWER", "Nessuna risposta");
define("_NOTAPPLICABLE", "N/A"); //New for 0.98rc5
define("_OTHER", "Altro");
define("_PLEASECHOOSE", "Seleziona");
define("_ERROR_PS", "Errore");
define("_COMPLETE", "Completato");
define("_INCREASE", "Crescente"); //NEW WITH 0.98
define("_SAME", "Uguale"); //NEW WITH 0.98
define("_DECREASE", "Descrescente"); //NEW WITH 0.98
//from questions.php
define("_CONFIRMATION", "Conferma");
define("_TOKEN_PS", "Token");
define("_CONTINUE_PS", "Continua");

//BUTTONS
define("_ACCEPT", "Accetta");
define("_PREV", "indietro");
define("_NEXT", "avanti");
define("_LAST", "fine");
define("_SUBMIT", "invia");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Seleziona");
define("_ENTERCOMMENT", "Scrivi i tuoi commenti");
define("_NUMERICAL_PS", "Inserire solo numeri");
define("_CLEARALL", "Azzera e esci dall&#039;indagine");
define("_MANDATORY", "domanda obbligatoria");
define("_MANDATORY_PARTS", "Si prega di completare tutti i campi");
define("_MANDATORY_CHECK", "Si prega di selezionare almeno un&#039;opzione");
define("_MANDATORY_RANK", "Seleziona tutti i campi");
define("_MANDATORY_POPUP", "Non è stato risposto ad una o più risposte obbligatorie. Non è possibile continuare senza che queste siano state completate"); //NEW in 0.98rc4
define("_VALIDATION", "This question must be answered correctly"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Formato: AAAA-MM-GG");
define("_DATEFORMATEG", "(ex: 2003-12-25 giorno di Natale)");
define("_REMOVEITEM", "Azzera");
define("_RANK_1", "Clicca su un&#039;opzione della lista a sinistra, incominciando");
define("_RANK_2", "dal pi&ugrave; basso al pi&ugrave; alto.");
define("_YOURCHOICES", "Le tue scelte");
define("_YOURRANKING", "La tua classifica");
define("_RANK_3", "Clicca sulle forbici a destra di a ogni articolo");
define("_RANK_4", "per eliminare l&#039; ultimo dato inserito nella classifica");
//From INDEX.PHP
define("_NOSID", "Inserire numero di identificazione dell&#039;indagine");
define("_CONTACT1", "Contattare");
define("_CONTACT2", "per ulteriori informazioni");
define("_ANSCLEAR", "Risposte azzerate");
define("_RESTART", "Avvia di nuovo l&#039;indagine");
define("_CLOSEWIN_PS", "Chiudi finestra");
define("_CONFIRMCLEAR", "Procedere nell&#039;eliminazione di tutte le risposte?");
define("_EXITCLEAR", "azzera ed esci dall&#039;indagine");
//From QUESTION.PHP
define("_BADSUBMIT1", "Impossibile generare risultati - non ci sono risultati da presentare.");
define("_BADSUBMIT2", "L&#039; errore pu&ograve; essere dovuto dal fatto che le risposte sono gi&agrave; state inserite e si &egrave; cliccato il tasto &#039;aggiorna&#039; del proprio browser. Pertanto le risposte sono gi&agrave; state salvate.");
define("_NOTACTIVE1", "Le risposte dell&#039;indagine non sono state salvate. L&#039;indagine non &egrave; ancora attiva.");
define("_CLEARRESP", "Azzera risposte");
define("_THANKS", "Grazie");
define("_SURVEYREC", "le risposte dell&#039;indagine sono state salvate.");
define("_SURVEYCPL", "Indagine completata");
define("_DIDNOTSAVE", "Salvataggio non riuscito");
define("_DIDNOTSAVE2", "Errore. Le risposte non sono state salvate.");
define("_DIDNOTSAVE3", "Le risposte non sono state perse e sono state inviate all&#039;amministratore di sistema. Verranno inserite nel database in un secondo momento.");
define("_DNSAVEEMAIL1", "Errore durante il salvataggio di una risposta per l&#039;ID dell&#039;indagine");
define("_DNSAVEEMAIL2", "DATI DA INSERIRE");
define("_DNSAVEEMAIL3", "CODICE SQL NON RIUSCITO");
define("_DNSAVEEMAIL4", "MESSAGGIO DI ERRORE");
define("_DNSAVEEMAIL5", "ERRORE DURANTE IL SALVATAGGIO");
define("_SUBMITAGAIN", "Ripetere l&#039;inserimento");
define("_SURVEYNOEXIST", "Spiacente. Non &egrave; stato trovato nessuna indagine.");
define("_NOTOKEN1", "Questa indagine &egrave; moderata. Per partecipare &egrave; necessario avere un Token.");
define("_NOTOKEN2", "Inserisci Token nella scatolina in basso e clicca continua.");
define("_NOTOKEN3", "Il Token inserito non &egrave; valido o &egrave; gi&agrave; usato da un altro utente.");
define("_NOQUESTIONS", "L&#039;indagine non contiene domande. Pertanto non pu&ograve; essere avviata o testata.");
define("_FURTHERINFO", "Per ulteriori informazioni contattare");
define("_NOTACTIVE", "L&#039;indagine non &egrave; attiva. Impossibile salvare le risposte.");
define("_SURVEYEXPIRED", "This survey is no longer available."); //NEW for 098rc5

define("_SURVEYCOMPLETE", "Hai gi&agrave; completato il questionario.");

define("_INSTRUCTION_LIST", "Scegliere solo una delle seguenti voci"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Scegli una o pi&ugrave; delle seguenti voci"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Indagine Presentata"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Una nuova risposta è stata inserita per la vostra indagine"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Scatti il seguente collegamento per vedere la risposta specifica:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Statistiche di vista scattandosi qui:"); //NEW for 098rc5

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

//For the "printable" survey
define("_PS_CHOOSEONE", "Please choose <b>only one</b> of the following"); //New for 0.98finalRC1
define("_PS_WRITE", "Please write your answer here"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Please choose <b>all</b> that apply"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Please choose all that apply and provide a comment"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Please choose the appropriate response for each item"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Please write your answer(s) here"); //New for 0.98finalRC1
define("_PS_DATE", "Please enter a date"); //New for 0.98finalRC1
define("_PS_COMMENT", "Make a comment on your choice here"); //New for 0.98finalRC1
define("_PS_RANKING", "Please number each box in order of preference from 1 to"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Submit Your Survey"); //New for 0.98finalRC1
define("_PS_THANKYOU", "Thank you for completing this survey."); //New for 0.98finalRC1
define("_PS_FAXTO", "Please fax your completed survey to:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Only answer this question"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "if you answered"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "and"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "to question"); //New for 0.98finalRC1
define("_PS_CON_OR", "or"); //New for 0.98finalRC2
?>
