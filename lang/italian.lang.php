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
	# Mario Marani - IRRE Puglia - Bari							#
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
define("_ERROR_PS", "ERRORE");
define("_COMPLETE", "Completato");
define("_INCREASE", "In aumento"); //NEW WITH 0.98
define("_SAME", "Uguale"); //NEW WITH 0.98
define("_DECREASE", "In diminuzione"); //NEW WITH 0.98
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Conferma");
define("_TOKEN_PS", "Identificativi");
define("_CONTINUE_PS", "Continua");

//BUTTONS
define("_ACCEPT", "Accetta");
define("_PREV", "Indietro");
define("_NEXT", "Avanti");
define("_LAST", "Fine");
define("_SUBMIT", "Invia");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Seleziona");
define("_ENTERCOMMENT", "Scrivi i tuoi commenti");
define("_NUMERICAL_PS", "Inserire solo numeri");
define("_CLEARALL", "Azzera e esci dall&#039;indagine");
define("_MANDATORY", "Domanda obbligatoria");
define("_MANDATORY_PARTS", "Si prega di completare tutti i campi");
define("_MANDATORY_CHECK", "Si prega di selezionare almeno una opzione");
define("_MANDATORY_RANK", "Seleziona tutti i campi");
define("_MANDATORY_POPUP", "Non hai risposto ad una o più domande obbligatorie. Non è possibile continuare senza che queste siano state completate"); //NEW in 0.98rc4
define("_VALIDATION", "Devi rispondere correttamente a questa domanda"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "Non hai risposto in modo valido ad una o più domande. Non è possibile continuare finchè queste risposte non siano valide"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Formato: AAAA-MM-GG");
define("_DATEFORMATEG", "(ad es.: 2004-12-25 giorno di Natale)");
define("_REMOVEITEM", "Azzera");
define("_RANK_1", "Fai clic su una opzione della lista a sinistra, incominciando");
define("_RANK_2", "dal pi&ugrave; basso al pi&ugrave; alto.");
define("_YOURCHOICES", "Le tue scelte");
define("_YOURRANKING", "La tua classifica");
define("_RANK_3", "Fai clic sulla icona delle forbici a destra di ogni articolo");
define("_RANK_4", "per eliminare l&#039;ultimo dato inserito nella classifica");
//From INDEX.PHP
define("_NOSID", "Inserire il numero di identificazione dell&#039;indagine.");
define("_CONTACT1", "Contattare");
define("_CONTACT2", "per ulteriori informazioni");
define("_ANSCLEAR", "Risposte azzerate");
define("_RESTART", "Avvia di nuovo l&#039;indagine");
define("_CLOSEWIN_PS", "Chiudi finestra");
define("_CONFIRMCLEAR", "Procedere nell&#039;eliminazione di tutte le risposte?");
define("_CONFIRMSAVE", "Sei sicuro di voler salvare le tue risposte?");
define("_EXITCLEAR", "Azzera ed esci dall&#039;indagine");
//From QUESTION.PHP
define("_BADSUBMIT1", "Impossibile generare risultati - non ci sono risultati da presentare.");
define("_BADSUBMIT2", "L&#039; errore pu&ograve; essere dovuto al fatto che le risposte sono gi&agrave; state inserite e si &egrave; cliccato il tasto &#039;aggiorna&#039; del proprio browser. Pertanto le risposte sono gi&agrave; state salvate.");
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
define("_SURVEYNOEXIST", "Spiacente. Non &egrave; stata trovata alcuna indagine.");
define("_NOTOKEN1", "Questa indagine &egrave; moderata. Per partecipare &egrave; necessario avere un Identificativo.");
define("_NOTOKEN2", "Inserisci il tuo Identificativo  nella campo in basso e clicca continua.");
define("_NOTOKEN3", "L&#039Identificativo inserito non &egrave; valido o &egrave; gi&agrave; usato da un altro utente.");
define("_NOQUESTIONS", "L&#039;indagine non contiene domande. Pertanto non pu&ograve; essere avviata o testata.");
define("_FURTHERINFO", "Per ulteriori informazioni contattare");
define("_NOTACTIVE", "L&#039;indagine non &egrave; attiva. Impossibile salvare le risposte.");
define("_SURVEYEXPIRED", "Questa indagine non &egrave; pi&ugrave; disponibile."); //NEW for 098rc5

define("_SURVEYCOMPLETE", "Hai gi&agrave; completato il questionario.");

define("_INSTRUCTION_LIST", "Scegliere solo una delle seguenti voci"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Scegli una o pi&ugrave; delle seguenti voci"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Indagine presentata"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Una nuova risposta &egrave; stata inserita per la tua indagine"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Fai clic sul seguente collegamento per vedere la risposta specifica:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Fai clic qui per vedere le statistiche:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE5", "Click the following link to edit the individual response:"); //NEW for 0.99stable

define("_PRIVACY_MESSAGE", "<b><i>Note sulla Privacy</i></b><br />"
						  ."Questa indagine &egrave; anonima.<br />"
						  ."I dati che stai fornendo verranno trattati unicamente a fini"
						  ."di ricerca "
						  ."e nel rispetto della privacy. Se fai clic "
						  ."sul pulsante Invia accetti queste condizioni altrimenti fai clic su 'Azzera ed esci dall'indagine' "
						  ."per abbandonare il questionario."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "Vi sono {NUMBEROFQUESTIONS} domande in questa indagine."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Vi &egrave; 1 domanda in questa indagine."); //New for 0.98rc9 - singular version of above

define ("_RG_REGISTER1", "Devi essere registrato per partecipare a questa indagine"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Ti puoi registrare se vuoi partecipare a questa indagine.<br />\n"
						."Scrivi i tuoi dati qui sotto, e ti verr&agrave; immediatamente inviata una e-mail contenente il collegamento "
						."per partecipare a questa indagine."); //NEW for 0.98rc9
define ("_RG_EMAIL", "Indirizzo e-mail"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Nome"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Cognome"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "L&#039;indirizzo e-mail che hai inserito non è corretto. Prova di nuovo.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "L&#039;indirizzo e-mail che hai inserito &egrave; gi&agrave; registrato.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} Conferma della Registrazione");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Grazie per esserti registrato per participare a questa indagine.<br /><br />\n"
								   ."&Egrave; stata inviata una e-mail al tuo indirizzo con le indicazioni per accedere "
								   ."al questionario. Fai clic sul link presente nella e-mail per continuare.<br /><br />\n"
								   ."L&#039;Amministratore dell&#039;indagine {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<b>Grazie,<br /><br />"
					   ."per aver risposto alle domande del questionario.</b><br /><br />"
					   ."Fai clic sul pulsante ["._SUBMIT."] per completare il questionario e salvare le risposte fornite."); //New for 0.98finalRC1
define("_SM_REVIEW", "Se vuoi controllare le risposte date e, eventualmente, cambiarne qualcuna "
					."fai clic sul pulsante [<< "._PREV."]"); //New for 0.98finalRC1

//For the "printable" survey
define("_PS_CHOOSEONE", "Scegli <b>solo una</b> delle seguenti:"); //New for 0.98finalRC1
define("_PS_WRITE", "Scrivi le tue risposte qui:"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Scegli <b>tutte</b> quelle che corrispondono:"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Scegli tutte quelle che corrispondono e fornisci un commento:"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Scegli la risposta appropriata per ciascun item:"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Scrivi la(e) tua(e) risposta(e) qui:"); //New for 0.98finalRC1
define("_PS_DATE", "Inserisci una data:"); //New for 0.98finalRC1
define("_PS_COMMENT", "Inserisci un commento sulla tua scelta qui:"); //New for 0.98finalRC1
define("_PS_RANKING", "Numera ciascun box in ordine di preferenza da 1 a"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Invia il tuo questionario."); //New for 0.98finalRC1
define("_PS_THANKYOU", "Grazie per aver completato il questionario."); //New for 0.98finalRC1
define("_PS_FAXTO", "Invia un fax del questionario completato a:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Rispondi solo a questa domanda"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "se hai risposto"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "e"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "alla domanda"); //New for 0.98finalRC1
define("_PS_CON_OR", "o"); //New for 0.98finalRC2

//Save Messages
define("_SAVE_AND_RETURN", "Salva le risposte fin qui fornite");
define("_SAVEHEADING", "Salva il tuo questionario non terminato");
define("_RETURNTOSURVEY", "Ritorna al questionario");
define("_SAVENAME", "Nome");
define("_SAVEPASSWORD", "Password");
define("_SAVEPASSWORDRPT", "Ripeti la password");
define("_SAVE_EMAIL", "Indirizzo e-mail");
define("_SAVEEXPLANATION", "Inserisci un nome ed una password per questo questionario, quindi fai clic sul pulsante Salva riportato pi&ugrave; sotto.<br />\n"
				  ."Il tuo questionario verr&agrave; salvato utilizzando quel nome e quella password, e potr&agrave; "
				  ."essere completato successivamente dopo aver fatto un login con il nome e la password indicate.<br /><br />\n"
				  ."Se hai fornito un indirizzo e-mail, ti verrà inviata un messaggio contenente "
				  ."tutti i dettagli del caso.");
define("_SAVESUBMIT", "Salva");
define("_SAVENONAME", "Devi fornire un nome per questa sessione da salvare.");
define("_SAVENOPASS", "Devi fornire una password per questa sessione da salvare.");
define("_SAVENOMATCH", "Le password non coincidono.");
define("_SAVEDUPLICATE", "Questo nome &egrave; gi&agrave; utilizzato in questa indagine. Bisogna utilizzare un nome univoco.");
define("_SAVETRYAGAIN", "Prova di nuovo.");
define("_SAVE_EMAILSUBJECT", "Questionario on line. Messaggio contenente username e password salvate");
define("_SAVE_EMAILTEXT", "Tu, o qualcun altro che ha usato il tuo indirizzo e-mail, ha salvato "
						 ."un questionario on line non ancora completato. I seguenti dati possono essere utilizzati "
						 ."per riprendere il questionario e completarlo.");
define("_SAVE_EMAILURL", "Riprendi il questionario che non hai ancora completato facendo clic sulla seguente URL:");
define("_SAVE_SUCCEEDED", "Le tue risposte sono state salvate con successo");
define("_SAVE_FAILED", "Si &egrave; verificato un errore. Le tue risposte non sono state salvate.");
define("_SAVE_EMAILSENT", "Un messaggio e-mail è stato inviato con i dati con i quali hai salvato il tuo questionario.");

//Load Messages
define("_LOAD_SAVED", "Carica il questionario non terminato");
define("_LOADHEADING", "Riprendi il questionario non completato e precedentemente salvato");
define("_LOADEXPLANATION", "Puoi riprendere il questionario, precedentemente salvato, da questa schermata.<br />\n"
			  ."Inserisci il nome e la password che hai utilizzato per salvare il questionario.<br /><br />\n");
define("_LOADNAME", "Nome salvato");
define("_LOADPASSWORD", "Password");
define("_LOADSUBMIT", "Riprendi adesso");
define("_LOADNONAME", "Non hai inserito il nome");
define("_LOADNOPASS", "Non hai inserito la password");
define("_LOADNOMATCH", "Non vi &egrave; alcun questionario salvato corrispondente ai dati inseriti");

define("_ASSESSMENT_HEADING", "La tua valutazione");
?>
