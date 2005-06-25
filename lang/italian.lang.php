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
define("_TOKENS", "Identificativi");
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
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Conferma");
define("_TOKEN_PS", "Identificativo");
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
define("_MANDATORY_CHECK", "Si prega di selezionare almeno un&#039;opzione");
define("_MANDATORY_RANK", "Seleziona tutti i campi");
define("_MANDATORY_POPUP", "Non si &egrave; risposto ad una o più domande obbligatorie. Non è possibile continuare senza che queste siano state completate"); //NEW in 0.98rc4
define("_VALIDATION", "&egrave; necessario rispondere correttamente."); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "Non si &egrave; risposto in maniera valida ad una o pi&ugrave; domande. Non &egrave; possibile procedere fino a quando le risposte non saranno valide."); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Formato: AAAA-MM-GG");
define("_DATEFORMATEG", "(es. 2003-12-25 giorno di Natale)");
define("_REMOVEITEM", "Azzera");
define("_RANK_1", "Fai clic su un&#039;opzione della lista a sinistra, incominciando");
define("_RANK_2", "dal pi&ugrave; basso al pi&ugrave; alto.");
define("_YOURCHOICES", "Le tue scelte");
define("_YOURRANKING", "La tua classifica");
define("_RANK_3", "Fai clic sulle forbici a destra di ogni articolo");
define("_RANK_4", "per eliminare l&#039; ultimo dato inserito nella classifica");
//From INDEX.PHP
define("_NOSID", "Inserire numero di identificazione dell&#039;indagine");
define("_CONTACT1", "Contattare");
define("_CONTACT2", "Per ulteriori informazioni");
define("_ANSCLEAR", "Risposte azzerate");
define("_RESTART", "Avvia di nuovo l&#039;indagine");
define("_CLOSEWIN_PS", "Chiudi finestra");
define("_CONFIRMCLEAR", "Procedere nell&#039;eliminazione di tutte le risposte?");
define("_CONFIRMSAVE", "Sei sicuro di volersalvare le risposte date?");
define("_EXITCLEAR", "Azzera ed esci dall&#039;indagine");
//From QUESTION.PHP
define("_BADSUBMIT1", "Impossibile generare risultati - non ci sono risultati da presentare.");
define("_BADSUBMIT2", "L&#039; errore pu&ograve; essere dovuto dal fatto che le risposte sono gi&agrave; state inserite e si &egrave; cliccato il tasto &#039;aggiorna&#039; del proprio browser. Pertanto le risposte sono gi&agrave; state salvate.");
define("_NOTACTIVE1", "Le risposte dell&#039;indagine non sono state salvate. L&#039;indagine non &egrave; ancora attiva.");
define("_CLEARRESP", "Azzera risposte");
define("_THANKS", "Grazie");
define("_SURVEYREC", "Le risposte dell&#039;indagine sono state salvate.");
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
define("_SURVEYNOEXIST", "Spiacente. Non &egrave; stato trovata nessuna indagine.");
define("_NOTOKEN1", "Questa indagine &egrave; moderata. Per partecipare &egrave; necessario avere un Token.");
define("_NOTOKEN2", "Inserisci l'identificativo nel campo in basso, quindi fai clic su  continua.");
define("_NOTOKEN3", "L&#039;identificativo  inserito non &egrave; valido o &egrave; gi&agrave; usato da un altro utente.");
define("_NOQUESTIONS", "L&#039;indagine non contiene domande. Pertanto non pu&ograve; essere avviata o testata.");
define("_FURTHERINFO", "Per ulteriori informazioni contattare");
define("_NOTACTIVE", "L&#039;indagine non &egrave; attiva. Impossibile salvare le risposte.");
define("_SURVEYEXPIRED", "Questa indagine non è pi&ugrave; disponibile."); //NEW for 098rc5

define("_SURVEYCOMPLETE", "Hai gi&agrave; completato il questionario.");

define("_INSTRUCTION_LIST", "Scegliere solo una delle seguenti voci"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Scegli una o pi&ugrave; delle seguenti voci"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Indagine inviata"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Una nuova risposta è stata inserita nella indagine"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Fai clic sul seguente collegamento per vedere la risposta specifica:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Fai clic qui per vedere le staistiche:"); //NEW for 098rc5

define("_PRIVACY_MESSAGE", "<strong><i>Nota sulla Privacy</i></strong><br />"
        ."Questa indagine &egrave; anonima.<br />"
						  ."Le registrazioni delle tue risposte non contengono alcuna "
						  ."informazione che possa identificarti a meno che non vi siano specifiche domande "
						  ."nel questionario che lo facciano. Se hai "
						  ."utilizzato un identificativo per accedere "
						  ."al questionario esso non verrà associato in alcun "
						  ."modo alle risposte fornite. Gli identificativi sono gestiti in un database separato. "
        				  ."In questo programma non vi &egrave; alcun modo di confrontare "
						  ."gli identificativi con le risposte."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "Ci sono {NUMBEROFQUESTIONS} domande in questa indagine."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "&egrave; presente 1 domanda in questa indagine."); //New for 0.98rc9 - singular version of above

define ("_RG_REGISTER1", "Ti devi registrare per completare questa indagine"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Ti puoi registrare se desideri prendere parte a questa indagine.<br />\n"
						."Inserisci i tuoi dati qui sotto, ti verr&agrave; inviata immediatamente una e-mail con il link "
						."per participare a questa indagine."); //NEW for 0.98rc9
define ("_RG_EMAIL", "Indirizzo e-mail"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Nome"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Cognome"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "L&#039;indirizzo e-mail che hai usato non &egrave; valido. Prova di nuovo.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "L&#039;indirizzo e-mail che hai usato &egrave; stato gi&agrave; utilizzato.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} Conferma della registrazione");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Grazie per esserti registrato a questa indagine.<br /><br />\n"
								   ."&Egrave; stata inviata una e-mail al tuo indirizzo con le indicazioni per accedervi. "
								   ."Fai clic sul link presente nella e-mail per continuare.<br /><br />\n"
								   ."L&#039;Amministratore dell&#039;indagine {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc

define("_SM_COMPLETED", "<strong>Grazie<br /><br />"
                        ."per aver risposto alle domande di questa indagine.</b><br /><br />"
					   ."Fai clic su ["._SUBMIT."] per completare la procedura e salvare le tue risposte."); //New for 0.98finalRC1
define("_SM_REVIEW", "Se vuoi controllare le risposte date, e/o cambiarle, "
					."puoi farlo adesso facendo clic sul pulsante [<< "._PREV."] and ricercando"
					."le risposte da corregger."); //New for 0.98finalRC1

//For the "printable" survey
define("_PS_CHOOSEONE", "Scegli <strong>solo una</strong> delle seguenti:"); //New for 0.98finalRC1
define("_PS_WRITE", "Scrivi qui l atua risposta:"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Scegli <strong>tutte</strong> quelle desiderate:"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Scegli tutte quelle desiderate e fornisci un commento:"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Scegli la risposta pi&ugrave; appropriata per ciascun item:"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Scrivi qui la/le tua/tue  risposta/e:"); //New for 0.98finalRC1
define("_PS_DATE", "Inserisci una data:"); //New for 0.98finalRC1
define("_PS_COMMENT", "Commenta qui la tua scelta:"); //New for 0.98finalRC1
define("_PS_RANKING", "Numera ciascun box in ordine di preferenza da 1 a"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Invia le tue risposte."); //New for 0.98finalRC1
define("_PS_THANKYOU", "Grazie per aver completato questa indagine."); //New for 0.98finalRC1
define("_PS_FAXTO", "Invia un fax per indicare che hai completqato l'indagine a:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Rispondi solo a questa domanda"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "ise hai risposto"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "e"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "alla domanda"); //New for 0.98finalRC1
define("_PS_CON_OR", "o"); //New for 0.98finalRC2

//Save Messages
define("_SAVE_AND_RETURN", "Salva le risposte date sino ad adesso");
define("_SAVEHEADING", "Salva il tuo questionario non completato");
define("_RETURNTOSURVEY", "Ritorna all'indagine");
define("_SAVENAME", "Nome");
define("_SAVEPASSWORD", "Password");
define("_SAVEPASSWORDRPT", "Ripeti la Password");
define("_SAVE_EMAIL", "La tua e-mail");
define("_SAVEEXPLANATION", "Inserisci un nome ed una password per questa indagine e fai clic su Salva qui sotto.<br />\n"
				  ."Le tue risposte saranno salvate utilizzando quel nome e quella password, e potranno essere "
				  ."completate in seguito tramite un login con quel nome e quella pasaword.<br /><br />\n"
				  ."Se fornisci un indirizzo e-mail, ti sar&agrave; inviata una e-mail contenente "
				  ."questi dati.");
define("_SAVESUBMIT", "Salva adesso");
define("_SAVENONAME", "Devi fornire un nome per rientrare in questa sessione salvata.");
define("_SAVENOPASS", "Devi fornire una password per rientrare in questa sessione salvata.");
define("_SAVENOMATCH", "Le tue password non coincidono.");
define("_SAVEDUPLICATE", "Questo nome &egrave; stato gi&agrave; usato in questsa indagine. Scegline un alro.");
define("_SAVETRYAGAIN", "Prova di nuovo.");
define("_SAVE_EMAILSUBJECT", "Dati salvati dell&#039;indagine");
define("_SAVE_EMAILTEXT", "Tu, o qualcuno che ha usato il tuo indirizzo e-mail, hai salvato "
						 ."un questionario non completato. I seguenti dati possone essere usati "
						 ."per riaprire ilquestionario e continuare da dove &egrave; stato "
						 ."interrotto.");
define("_SAVE_EMAILURL", "Ricarica il questionario int4errotto facendo clic sul seguente indirizzo:");
define("_SAVE_SUCCEEDED", "Le tue risposte sono state salvate con successo");
define("_SAVE_FAILED", "Si &egrave; verificato un errore e le tue risposte non soso state salvate.");
define("_SAVE_EMAILSENT", "&egrave; stata spedita una e-mail con i dati relativi al questionario salvato.");

//Load Messages
define("_LOAD_SAVED", "Carica il questionario non completato");
define("_LOADHEADING", "Carica un questionario salvato in precedenza");
define("_LOADEXPLANATION", "Puoi caricare un questionario gi&agrave; salvato a partire da questa schermata.<br />\n"
			  ."Inserisci il 'nome' e la password che hai usato per salvare il questionario.<br /><br />\n");
define("_LOADNAME", "Nome salvato");
define("_LOADPASSWORD", "Password");
define("_LOADSUBMIT", "Carica adesso");
define("_LOADNONAME", "Non hai inserito un nome");
define("_LOADNOPASS", "Non hai inserito una password");
define("_LOADNOMATCH", "Non esiste alcun questionario corrispondente a questi dati");

define("_ASSESSMENT_HEADING", "La tua valutazione");
?>

