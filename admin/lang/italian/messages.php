<?php
// New translation and maintenance by Mario Marani - IRRE Puglia - Bari//

//BUTTON BAR TITLES
define("_ADMINISTRATION", "Amministrazione");
define("_SURVEY", "Indagine");
define("_GROUP", "Gruppo");
define("_QUESTION", "Domanda");
define("_ANSWERS", "Risposte");
define("_CONDITIONS", "Condizioni");
define("_HELP", "Guida in linea");
define("_USERCONTROL", "Gestione utenti");
define("_ACTIVATE", "Attivazione indagine");
define("_DEACTIVATE", "Disattivazione indagine");
define("_CHECKFIELDS", "Verifica campi nel database");
define("_CREATEDB", "Crea database");
define("_CREATESURVEY", "Crea nuova indagine"); //New for 0.98rc4
define("_SETUP", "Impostazioni PHPSurveyor");
define("_DELETESURVEY", "Elimina indagine");
define("_EXPORTQUESTION", "Esporta domanda");
define("_EXPORTSURVEY", "Esporta indagine");
define("_EXPORTLABEL", "Esporta gruppo etichette");
define("_IMPORTQUESTION", "Importa domanda");
define("_IMPORTGROUP", "Importa gruppo"); //New for 0.98rc5
define("_IMPORTSURVEY", "Importa indagine");
define("_IMPORTLABEL", "Importa etichetta");
define("_EXPORTRESULTS", "Esporta risposte");
define("_BROWSERESPONSES", "Sfoglia risposte");
define("_BROWSESAVED", "Sfoglia le risposte salvate");
define("_STATISTICS", "Statistiche");
define("_VIEWRESPONSE", "Visualizza risposta");
define("_VIEWCONTROL", "Visualizzazione dati");
define("_DATAENTRY", "Data entry");
define("_TOKENCONTROL", "Gestione identificativi");
define("_TOKENDBADMIN", "Amministrazione Database identificativi");
define("_DROPTOKENS", "Elimina tabella identificativi");
define("_EMAILINVITE", "Invia invito");
define("_EMAILREMIND", "Invia sollecita");
define("_TOKENIFY", "Genera identificativi");
define("_UPLOADCSV", "Fai Upload del File CSV");
define("_LABELCONTROL", "Gestione etichette"); //NEW with 0.98rc3
define("_LABELSET", "Gruppo di etichette"); //NEW with 0.98rc3
define("_LABELANS", "Etichette"); //NEW with 0.98rc3
define("_OPTIONAL", "Opzionale"); //NEW with 0.98finalRC1

//DROPDOWN HEADINGS
define("_SURVEYS", "Indagini");
define("_GROUPS", "Gruppi");
define("_QUESTIONS", "Domande");
define("_QBYQ", "Visualizza per domanda");
define("_GBYG", "Visualizza per gruppo");
define("_SBYS", "Visualizza tutto");
define("_LABELSETS", "Gruppo"); //New with 0.98rc3

//BUTTON MOUSEOVERS
//administration bar
define("_A_HOME_BT", "Amministrazione generale");
define("_A_SECURITY_BT", "Modifica le impostazioni di sicurezza");
define("_A_BADSECURITY_BT", "Configura sicurezza");
define("_A_CHECKDB_BT", "Verifica database");
define("_A_DELETE_BT", "Elimina indagine");
define("_A_ADDSURVEY_BT", "Crea o importa una nuova indagine");
define("_A_HELP_BT", "Guida in linea");
define("_A_CHECKSETTINGS", "Verifica impostazioni");
define("_A_BACKUPDB_BT", "Backup dell&#039;intero Database"); //New for 0.98rc10
define("_A_TEMPLATES_BT", "Editor dei Template"); //New for 0.98rc9
//Survey bar
define("_S_ACTIVE_BT", "Indagine attiva");
define("_S_INACTIVE_BT", "Indagine non attiva");
define("_S_ACTIVATE_BT", "Attiva indagine");
define("_S_DEACTIVATE_BT", "Disattiva indagine");
define("_S_CANNOTACTIVATE_BT", "Impossibile attivare indagine");
define("_S_DOSURVEY_BT", "Prova indagine");
define("_S_DATAENTRY_BT", "Data entry");
define("_S_PRINTABLE_BT", "Stampa indagine");
define("_S_EDIT_BT", "Modifica indagine");
define("_S_DELETE_BT", "Elimina indagine");
define("_S_EXPORT_BT", "Esporta indagine");
define("_S_BROWSE_BT", "Sfoglia risposte");
define("_S_TOKENS_BT", "Genera identificativi");
define("_S_ADDGROUP_BT", "Aggiungi gruppo");
define("_S_MINIMISE_BT", "Nascondi dettagli indagine");
define("_S_MAXIMISE_BT", "Mostra dettagli indagine");
define("_S_CLOSE_BT", "Chiudi indagine");
define("_S_SAVED_BT", "Visualizza le risposte salvate ma non inviate"); //New in 0.99dev01
define("_S_ASSESSMENT_BT", "Stabilisci le regole di valutazione"); //New in  0.99dev01
//Group bar
define("_G_EDIT_BT", "Modifica gruppo");
define("_G_EXPORT_BT", "Esporta gruppo"); //New in 0.98rc5
define("_G_DELETE_BT", "Elimina gruppo");
define("_G_ADDQUESTION_BT", "Aggiungi domanda");
define("_G_MINIMISE_BT", "Nascondi dettagli di questo gruppo");
define("_G_MAXIMISE_BT", "Mostra dettagli di questo gruppo");
define("_G_CLOSE_BT", "Chiudi gruppo");
//Question bar
define("_Q_EDIT_BT", "Modifica domanda");
define("_Q_COPY_BT", "Copia domanda corrente"); //New in 0.98rc4
define("_Q_DELETE_BT", "Elimina domanda");
define("_Q_EXPORT_BT", "Esporta domanda");
define("_Q_CONDITIONS_BT", "Imposta condizioni");
define("_Q_ANSWERS_BT", "Modifica/Aggiungi risposte a questa domanda");
define("_Q_LABELS_BT", "Modifica/Aggiungi etichette");
define("_Q_MINIMISE_BT", "Nascondi i dettagli di questa domanda");
define("_Q_MAXIMISE_BT", "Mostra i dettagli di questa domanda");
define("_Q_CLOSE_BT", "Chiudi domanda");
//Browse Button Bar
define("_B_ADMIN_BT", "Amministrazione generale");
define("_B_SUMMARY_BT", "Riepilogo");
define("_B_ALL_BT", "Mostra risposte");
define("_B_LAST_BT", "Ultime 50 risposte");
define("_B_STATISTICS_BT", "Statistiche");
define("_B_EXPORT_BT", "Esporta risultati");
define("_B_BACKUP_BT", "Backup dei risultati in file SQL");
//Tokens Button Bar
define("_T_ALL_BT", "Mostra gli identificativi");
define("_T_ADD_BT", "Aggiungi un identificativo");
define("_T_IMPORT_BT", "Importa file CSV");
define("_T_EXPORT_BT", "Esporta gli identificativi come file CSV"); //New for 0.98rc7
define("_T_INVITE_BT", "Invia invito");
define("_T_REMIND_BT", "Invia sollectito");
define("_T_TOKENIFY_BT", "Genera password di identificazione");
define("_T_KILL_BT", "Elimina le tabelle degli identificativi");
//Labels Button Bar
define("_L_ADDSET_BT", "Aggiungi nuovo gruppo di etichette");
define("_L_EDIT_BT", "Modifica gruppo etichette");
define("_L_DEL_BT", "Cancella gruppo etichette");
//Datacontrols
define("_D_BEGIN", "Inizio");
define("_D_BACK", "Indietro");
define("_D_FORWARD", "Avanti");
define("_D_END", "Fine");

//DATA LABELS
//surveys
define("_SL_TITLE", "Titolo:");
define("_SL_SURVEYURL", "URL dell&#039;indagine:"); //new in 0.98rc5
define("_SL_DESCRIPTION", "Descrizione:");
define("_SL_WELCOME", "Benvenuto:");
define("_SL_ADMIN", "Amministratore:");
define("_SL_EMAIL", "e-mail Amministratore:");
define("_SL_FAXTO", "Fax:");
define("_SL_ANONYMOUS", "Anonimo");
define("_SL_EXPIRES", "Scadenza:");
define("_SL_FORMAT", "Formato:");
define("_SL_DATESTAMP", "Data invio questionario completato");
define("_SL_TEMPLATE", "Template:");
define("_SL_LANGUAGE", "Lingua:");
define("_SL_LINK", "Link:");
define("_SL_URL", "Rinvia a URL:");
define("_SL_URLDESCRIP", "Testo URL:");
define("_SL_STATUS", "Stato:");
define("_SL_SELSQL", "Seleziona File SQL:");
define("_SL_USECOOKIES", "Uso dei cookie"); //NEW with 098rc3
define("_SL_NOTIFICATION", "Notifica:"); //New with 098rc5
define("_SL_ALLOWREGISTER", "Permetti una registrazione pubblica"); //New with 0.98rc9
define("_SL_ATTRIBUTENAMES", "Nomi dei campi degli attributi degli identificativi:"); //New with 0.98rc9
define("_SL_EMAILINVITE_SUBJ", "Oggetto della e-mail di invito:"); //New with 0.99dev01
define("_SL_EMAILINVITE", "Testo della e-mail di invito:"); //New with 0.98rc9
define("_SL_EMAILREMIND_SUBJ", "Oggetto della e-mail di sollecito:"); //New with 0.99dev01
define("_SL_EMAILREMIND", "Testo della e-mail di sollecito:"); //New with 0.98rc9
define("_SL_EMAILREGISTER_SUBJ", "Oggetto della e-mail di risposta alla registrazione pubblica:"); //New with 0.99dev01
define("_SL_EMAILREGISTER", "Testo della e-mail di risposta alla registrazione pubblica:"); //New with 0.98rc9
define("_SL_EMAILCONFIRM_SUBJ", "Oggetto della e-mail di conferma completamento indagine"); //New with 0.99dev01
define("_SL_EMAILCONFIRM", "Testo e-mail di conferma completamento indagine"); //New with 0.98rc9
define("_SL_REPLACEOK", "Questo rimpiazzer&agrave; il testo esistente. Vuoi preseguire?"); //New with 0.98rc9
define("_SL_ALLOWSAVE", "Permetti il salvataggio"); //New with 0.99dev01
define("_SL_AUTONUMBER", "Fai partire gli ID dal numero:"); //New with 0.99dev01
define("_SL_AUTORELOAD", "Rinvia automaticamente ad URL"); //New with 0.99dev01
define("_SL_ALLOWPREV", "Mostra il pulsante [Indietro]"); //New with 0.99dev01
define("_SL_USE_DEFAULT","Usa il default");
define("_SL_UPD_SURVEY","Aggiorna l&#039;indagine");

//groups
define("_GL_TITLE", "Titolo:");
define("_GL_DESCRIPTION", "Descrizione:");
define("_GL_EDITGROUP","Modifica Gruppo per l&#039;indagine ID numero:"); // New with 0.99dev02
define("_GL_UPDATEGROUP","Aggiorna gruppo"); // New with 0.99dev02
//questions
define("_QL_EDITQUESTION", "Modifica domanda");// New with 0.99dev02
define("_QL_UPDATEQUESTION", "Aggiorna domanda");// New with 0.99dev02
define("_QL_CODE", "Codice:");
define("_QL_QUESTION", "Domanda:");
define("_QL_VALIDATION", "Convalida:"); //New in VALIDATION VERSION
define("_QL_HELP", "Nota di spiegazione:");
define("_QL_TYPE", "Tipologia:");
define("_QL_GROUP", "Gruppo:");
define("_QL_MANDATORY", "Obbligatorio:");
define("_QL_OTHER", "Altro:");
define("_QL_LABELSET", "Gruppo di etichette:");
define("_QL_COPYANS", "Copiare risposte?"); //New in 0.98rc3
define("_QL_QUESTIONATTRIBUTES", "Attributi delle domande:"); //New in 0.99dev01
define("_QL_COPYATT", "Copia attributi?"); //New in 0.99dev01
//answers
define("_AL_CODE", "Codice");
define("_AL_ANSWER", "Risposta");
define("_AL_DEFAULT", "Default");
define("_AL_MOVE", "Sposta");
define("_AL_ACTION", "Attiva");
define("_AL_UP", "Su");
define("_AL_DN", "Gi&ugrave;");
define("_AL_SAVE", "Salva");
define("_AL_DEL", "Elimina");
define("_AL_ADD", "Aggiungi");
define("_AL_FIXSORT", "Ordina");
define("_AL_SORTALPHA", "Ordina risposte alfabeticamente"); //New in 0.98rc8 - Sort Answers Alphabetically
//users
define("_UL_USER", "Utente");
define("_UL_PASSWORD", "Password");
define("_UL_SECURITY", "Sicurezza");
define("_UL_ACTION", "Attiva");
define("_UL_EDIT", "Crea");
define("_UL_DEL", "Elimina");
define("_UL_ADD", "Aggiungi");
define("_UL_TURNOFF", "Disattiva sicurezza");
//tokens
define("_TL_FIRST", "Nome");
define("_TL_LAST", "Cognome");
define("_TL_EMAIL", "e-mail");
define("_TL_TOKEN", "Identificativo");
define("_TL_INVITE", "Invito spedito?");
define("_TL_DONE", "Completato?");
define("_TL_ACTION", "Funzioni");
define("_TL_ATTR1", "Attributo_1"); //New for 0.98rc7
define("_TL_ATTR2", "Attributo_2"); //New for 0.98rc7
define("_TL_MPID", "MPID"); //New for 0.98rc7
//labels
define("_LL_NAME", "Nome gruppo"); //NEW with 098rc3
define("_LL_CODE", "Codice"); //NEW with 098rc3
define("_LL_ANSWER", "Titolo"); //NEW with 098rc3
define("_LL_SORTORDER", "Ordine"); //NEW with 098rc3
define("_LL_ACTION", "Azione"); //New with 098rc3

//QUESTION TYPES
define("_5PT", "Attribuzione punteggio (1-5)");
define("_DATE", "Data");
define("_GENDER", "Genere");
define("_LIST", "Lista (Radio)"); //Changed with 0.99dev01
define("_LIST_DROPDOWN", "Lista (Dropdown)"); //New with 0.99dev01
define("_LISTWC", "Lista con commento");
define("_MULTO", "Scelta multipla");
define("_MULTOC", "Scelta multipla con commenti");
define("_MULTITEXT", "Testo breve multiplo");
define("_NUMERICAL", "Inserimento numerico");
define("_RANK", "Classifica/Ordinamento");
define("_STEXT", "Testo libero breve");
define("_LTEXT", "Testo libero lungo");
define("_HTEXT", "Testo libero maxi"); //New with 0.99dev01
define("_YESNO", "SI/NO");
define("_ARR5", "Scelta (punteggio 1-5)");
define("_ARR10", "Scelta (punteggio 1-10)");
define("_ARRYN", "Scelta (SI/NO/NON SO)");
define("_ARRMV", "Scelta (in aumento, costante, in diminuzione)");
define("_ARRFL", "Scelta (Etichetta variabile)"); //(FOR LATER RELEASE)
define("_ARRFLC", "Scelta (Etichetta variabile per colonna)"); //Release 0.98rc8
define("_SINFL", "Campo unico (Flessibile)"); //(FOR LATER RELEASE)
define("_EMAIL", "Indirizzo e-mail"); //FOR LATER RELEASE
define("_BOILERPLATE", "Domande Boilerplate"); //New in 0.98rc6
define("_LISTFL_DROPDOWN", "Lista (Etichetta variabile) (Dropdown)"); //New in 0.99dev01
define("_LISTFL_RADIO", "Lista (Etichetta variabile) (Radio)"); //New in 0.99dev01

//GENERAL WORDS AND PHRASES
define("_AD_YES", "SI");
define("_AD_NO", "NO");
define("_AD_CANCEL", "Chiudi");
define("_AD_CHOOSE", "Seleziona");
define("_AD_OR", "Oppure"); //New in 0.98rc4
define("_ERROR", "Errore");
define("_SUCCESS", "Complimenti");
define("_REQ", "*Obbligatorio");
define("_ADDS", "Aggiungi indagine");
define("_ADDG", "Aggiungi gruppo");
define("_ADDQ", "Aggiungi domanda");
define("_ADDA", "Aggiungi risposta"); //New in 0.98rc4
define("_COPYQ", "Copia domanda"); //New in 0.98rc4
define("_ADDU", "Aggiungi utente");
define("_SEARCH", "Cerca"); //New in 0.98rc4
define("_SAVE", "Salva modifiche");
define("_NONE", "Niente/Nessuno"); //as in "Vuoto", "Nessuna scelta";
define("_GO_ADMIN", "Torna alla pagina di amministrazione generale"); //text to display to return/display main administration screen
define("_CONTINUE", "Continua");
define("_WARNING", "Attenzione");
define("_USERNAME", "Nome utente");
define("_PASSWORD", "Password");
define("_DELETE", "Elimina indagine");
define("_CLOSEWIN", "Chiudi finestra");
define("_TOKEN", "Identificativo");
define("_DATESTAMP", "Data invio questionario completato"); //Referring to the datestamp or time response submitted
define("_COMMENT", "Commento");
define("_FROM", "Da"); //For emails
define("_SUBJECT", "Oggetto"); //For emails
define("_MESSAGE", "Messaggio"); //For emails
define("_RELOADING", "Aggiornamento in corso. Attendere prego.");
define("_ADD", "Aggiungi");
define("_UPDATE", "Aggiorna");
define("_BROWSE", "Sfoglia"); //New in 098rc5
define("_AND", "AND"); //New with 0.98rc8
define("_SQL", "SQL"); //New with 0.98rc8
define("_PERCENTAGE", "Percentuale"); //New with 0.98rc8
define("_COUNT", "Conta"); //New with 0.98rc8

//SURVEY STATUS MESSAGES (new in 0.98rc3)
define("_SS_NOGROUPS", "Numero di gruppi in questa indagine:"); //NEW for release 0.98rc3
define("_SS_NOQUESTS", "Numero di domande in questa indagine:"); //NEW for release 0.98rc3
define("_SS_ANONYMOUS", "Questa indagine &egrave; anonima."); //NEW for release 0.98rc3
define("_SS_TRACKED", "Questa indagine NON &egrave; anonima."); //NEW for release 0.98rc3
define("_SS_DATESTAMPED", "Le risposte verranno datate"); //NEW for release 0.98rc3
define("_SS_COOKIES", "Utilizza i cookie per il controllo degli accessi."); //NEW for release 0.98rc3
define("_SS_QBYQ", "Viene visualizzata domanda per domanda."); //NEW for release 0.98rc3
define("_SS_GBYG", "Viene visualizzata gruppo per gruppo."); //NEW for release 0.98rc3
define("_SS_SBYS", "Viene visualizzata in una sola pagina."); //NEW for release 0.98rc3
define("_SS_ACTIVE", "L&#039;indagine &egrave; attiva."); //NEW for release 0.98rc3
define("_SS_NOTACTIVE", "L&#039;indagine non &egrave; al momento attiva."); //NEW for release 0.98rc3
define("_SS_SURVEYTABLE", "Il nome della tabella per l&#039;indagine &egrave;:"); //NEW for release 0.98rc3
define("_SS_CANNOTACTIVATE", "L&#039;indagine non pu&ograve; essere ancora attivata."); //NEW for release 0.98rc3
define("_SS_ADDGROUPS", "E&#039; necessario aggiungere dei gruppi"); //NEW for release 0.98rc3
define("_SS_ADDQUESTS", "E&#039; necessario aggiungere delle domande"); //NEW for release 0.98rc3
define("_SS_ALLOWREGISTER", "Se sono stati usati degli identificativi, il pubblico pu&ograve; registrarsi per questa indagine"); //NEW for release 0.98rc9
define("_SS_ALLOWSAVE", "I partecipanti possono salvare questionari completati parzialmente "); //NEW for release 0.99dev01

//QUESTION STATUS MESSAGES (new in 0.98rc4)
define("_QS_MANDATORY", "Domanda obbligatoria"); //New for release 0.98rc4
define("_QS_OPTIONAL", "Domanda opzionale"); //New for release 0.98rc4
define("_QS_NOANSWERS", "E&#039; necessario inserire delle risposte per questa domanda"); //New for release 0.98rc4
define("_QS_NOLID", "E&#039; necessario inserire un gruppo di etichette per questa domanda"); //New for release 0.98rc4
define("_QS_COPYINFO", "Nota: &egrave; obbligatorio inserire un nuovo codice domanda"); //New for release 0.98rc4

//General Setup Messages
define("_ST_NODB1", "Il database di questa indagine &egrave; inesistente");
define("_ST_NODB2", "Il database selezionato non &egrave; stato ancora creato oppure &egrave; impossibile accedervi.");
define("_ST_NODB3", "PHPSurveyor tenter&agrave; di creare il database.");
define("_ST_NODB4", "Il database selezionato si chiama:");
define("_ST_CREATEDB", "Crea Database");

//USER CONTROL MESSAGES
define("_UC_CREATE", "Crea file htaccess di default");
define("_UC_NOCREATE", "Impossibile creare file htaccess. Verifica il config.php per la configurazione di \$homedir, e assicurarsi che glio identificativi siano stati inseriti nella directory giusta.");
define("_UC_SEC_DONE", "Le impostazioni di sicurezza sono attive!");
define("_UC_CREATE_DEFAULT", "Crea utenti di default");
define("_UC_UPDATE_TABLE", "Aggiorna tavole utenti");
define("_UC_HTPASSWD_ERROR", "Errore durante la creazione del file htpasswd");
define("_UC_HTPASSWD_EXPLAIN", "Per un corretto funzionamento si consiglia di copiare il file apache htpasswd.exe nella cartella di admin se si utilizza un server windows. Di solito questo file si trova in /apache group/apache/bin/");
define("_UC_SEC_REMOVE", "Elimina le impostazioni di sicurezza");
define("_UC_ALL_REMOVED", "Il file di accesso, il password file e lo user database sono stati eliminati");
define("_UC_ADD_USER", "Aggiungi utente");
define("_UC_ADD_MISSING", "Impossibile aggiungere utente. Il nome utente e/o la password non sono stati inseriti");
define("_UC_DEL_USER", "Elimina utente");
define("_UC_DEL_MISSING", "Impossibile eliminare utente. Il nome utente non &egrave; stato inserito.");
define("_UC_MOD_USER", "Modifica utente");
define("_UC_MOD_MISSING", "Impossibile modificare utente. Il nome utente e/o la password non sono stati inseriti");
define("_UC_TURNON_MESSAGE1", "Le impostazioni di sicurezza non sono attive, pertanto non ci sono restrizioni sull&#039;accesso.</p>\nClicca su &#039;Inizializza sicurezza&#039;, per impostare gli standard di sicurezza del web server APACHE nella directory di amministrazione di questo script. Utilizzare lo username e password di default per accedere all&#039;amministrazione e agli script per l&#039;inserimento dei dati.");
define("_UC_TURNON_MESSAGE2", "Si consiglia di modificare la password di default una volta attivate le impostazioni di sicurezza.");
define("_UC_INITIALISE", "Inizializza sicurezza");
define("_UC_NOUSERS", "Non esistono utenti nella tabella. Si consiglia di &#039;disattivare&#039; la sicurezza. Si pu&ograve;; &#039;riattivare&#039; in un secondo momento.");
define("_UC_TURNOFF", "Disattiva sicurezza");

//Activate and deactivate messages
define("_AC_MULTI_NOANSWER", "Questa domanda &egrave; una domanda a scelta multipla senza risposte.");
define("_AC_NOTYPE", "Questa domanda non contiene il parametro &#039;tipologia&#039;.");
define("_AC_NOLID", "Questa domanda richiede un set di etichette. Nessuna etichetta &egrave; stata impostata."); //New for 0.98rc8
define("_AC_CON_OUTOFORDER", "Domanda condizionata. La condizione dipende da una domanda successiva.");
define("_AC_FAIL", "L&#039;indagine non &egrave; coerente");
define("_AC_PROBS", "Sono stati riscontrati i seguenti problemi:");
define("_AC_CANNOTACTIVATE", "L&#039;indagine non pu&ograve; essere attivata prima di risolvere i problemi emersi");
define("_AC_READCAREFULLY", "LEGGERE ATTENTAMENTE PRIMA DI PROCEDERE");
define("_AC_ACTIVATE_MESSAGE1", "Attivare un&#039;indagine solo dopo aver inserito tutti i dati e quando non sono necessarie ulteriori modifiche.");
define("_AC_ACTIVATE_MESSAGE2", "Una volta attivata l&#039;indagine non &egrave; pi&ugrave; possibile:<ul><li>Aggiungere o eliminare gruppi</li><li>Aggiungere o eliminare risposte a domande a scelta multipla</li><li>Aggiungere o eliminare domande</li></ul>");
define("_AC_ACTIVATE_MESSAGE3", "E&#039; possibile:<ul><li>Modificare il codice, il testo o la tipologia della domanda</li><li>Modificare i nomi dei gruppi</li><li>Aggiungere, eliminare o modificare domande a risposta pre-definita (ad esclusione delle domande a scelta multipla)</li><li>Modificare il nome o la descrizione dell&#039;indagine</li></ul>");
define("_AC_ACTIVATE_MESSAGE4", "Una volta inseriti tutti i dati, per eliminare o aggiungere nuovi gruppi, &egrave; necessario disattivare l&#039;indagine. Tutti i dati inseriti verranno esportati in un altro file.");
define("_AC_ACTIVATE", "Attiva");
define("_AC_ACTIVATED", "L&#039;indagine &egrave; stata attivata. Le tabelle dei risultati sono state create.");
define("_AC_NOTACTIVATED", "Impossibile attivare l&#039;indagine.");
define("_AC_NOTPRIVATE", "Questa indagine &egrave; anonima. Crea una tabella degli identificativi.");
define("_AC_REGISTRATION", "Questa indagine consente una registrazione pubblica. E&#039; necessario creare una tabella degli identificativi.");
define("_AC_CREATETOKENS", "Genera gli identificativi");
define("_AC_SURVEYACTIVE", "Indagine attiva. Si pu&ograve; procedere con l&#039;inserimento delle domande.");
define("_AC_DEACTIVATE_MESSAGE1", "Un&#039;indagine attiva contiene un database dove vengono memorizzati tutti i dati.");
define("_AC_DEACTIVATE_MESSAGE2", "Disattivando l&#039;indagine tutti i dati inseriti verranno spostati altrove. Il database verr&agrave; svuotato attivando di nuovo l&#039;indagine. Non sar&agrave; pi&ugrave; possibile accedere a queste informazioni tramite PHPSurveyor.");
define("_AC_DEACTIVATE_MESSAGE3", "Le informazioni di un&#039;indagine disattivata possono essere visualizzate solo dall&#039;amministratore mediante uno strumento MySQL come PhpMyAdmin. Se l&#039;indagine utilizza identificativi, la tabella delle risposte verr&agrave; rinominata e sar&agrave; accessibile solo dall&#039;amministratore.");
define("_AC_DEACTIVATE_MESSAGE4", "La tabella delle risposte verr&agrave; rinominata:");
define("_AC_DEACTIVATE_MESSAGE5", "Si consiglia di esportare le risposte prima di disattivare l&#039;indagine. Clicca su Chiudi per ritornare alla schermata principale dell&#039;amministrazione senza disattivare questa indagine.");
define("_AC_DEACTIVATE", "Disattiva");
define("_AC_DEACTIVATED_MESSAGE1", "Il database delle risposte &egrave; stato rinominato: ");
define("_AC_DEACTIVATED_MESSAGE2", "Le risposte di questa indagine non sono pi&ugrave; disponibili tramite PHPSurveyor.");
define("_AC_DEACTIVATED_MESSAGE3", "Non dimenticare il nome di questo database per poter accedere in seguito alle informazioni in esso contenute.");
define("_AC_DEACTIVATED_MESSAGE4", "Le tabelle degli identificativi associate a questa indagine sono state rinominate: ");

//CHECKFIELDS
define("_CF_CHECKTABLES", "Verifica delle seguenti tabelle");
define("_CF_CHECKFIELDS", "Verifica dei seguenti campi");
define("_CF_CHECKING", "Verifica");
define("_CF_TABLECREATED", "La tabella &egrave; stata creata");
define("_CF_FIELDCREATED", "I campi sono stati creati");
define("_CF_OK", "OK");
define("_CFT_PROBLEM", "Alcune tabelle o campi non sono presenti nel database.");

//CREATE DATABASE (createdb.php)
define("_CD_DBCREATED", "Il database &egrave; stato creato.");
define("_CD_POPULATE_MESSAGE", "Fai clic sul seguente link per l&#039;inserimento dei dati nel database");
define("_CD_POPULATE", "Inserisci dati nel Database");
define("_CD_NOCREATE", "Impossibile creare database");
define("_CD_NODBNAME", "Non ci sono dati nel database. Questo script pu&ograve; essere caricato solo da admin.php.");

//DATABASE MODIFICATION MESSAGES
define("_DB_FAIL_GROUPNAME", "Impossibile aggiungere gruppo. Inserire il nome del gruppo nel campo obbligatorio nome del gruppo");
define("_DB_FAIL_GROUPUPDATE", "Impossibile aggiornare gruppo");
define("_DB_FAIL_GROUPDELETE", "Impossibile eliminare gruppo");
define("_DB_FAIL_NEWQUESTION", "Impossibile creare domande.");
define("_DB_FAIL_QUESTIONTYPECONDITIONS", "Impossibile aggiornare domande. Le condizioni di alcune domande dipendono dalla risposta di questa domanda. Cambiare il tipo di domanda causer&agrave; dei problemi al sistema. Elimina le condizioni prima di modificare il tipo di domanda.");
define("_DB_FAIL_QUESTIONUPDATE", "Impossibile aggiornare domanda");
define("_DB_FAIL_QUESTIONDELCONDITIONS", "Impossibile eliminare la domanda. Alcune domande dipendono dalle condizioni imposte a questa domanda. Elimina le condizioni prima di eliminare la domanda");
define("_DB_FAIL_QUESTIONDELETE", "Impossibile eliminare la domanda");
define("_DB_FAIL_NEWANSWERMISSING", "Impossibile aggiungere la domanda. Inserire Codice e Risposta");
define("_DB_FAIL_NEWANSWERDUPLICATE", "Impossibile aggiungere la domanda. Domanda con codice gi&agrave; esistente");
define("_DB_FAIL_ANSWERUPDATEMISSING", "Impossibile aggiornare la domanda. Inserire Codice e Risposta");
define("_DB_FAIL_ANSWERUPDATEDUPLICATE", "Impossibile aggiornare la domanda. Domanda ha un codice gi&agrave; esistente");
define("_DB_FAIL_ANSWERUPDATECONDITIONS", "Impossibile aggiornare la domanda. Il codice della domande &egrave; stato modificato. Alcune domande dipendono dalle condizioni associate alla domanda con il vecchio codice. Elimina le condizioni prima di modifcare il codice di questa domanda.");
define("_DB_FAIL_ANSWERDELCONDITIONS", "Impossibile eliminare la risposta. Alcune domande dipendono dalle condizioni imposte a questa risposta. Elimina le condizioni prima di eliminare questa risposta");
define("_DB_FAIL_NEWSURVEY_TITLE", "Impossibile creare l&#039;indagine. Inserire un titolo");
define("_DB_FAIL_NEWSURVEY", "Impossibile creare l&#039;indagine");
define("_DB_FAIL_SURVEYUPDATE", "Impossibile aggiornare l&#039;indagine");
define("_DB_FAIL_SURVEYDELETE", "Impossibile eliminare l&#039;indagine");

//DELETE SURVEY MESSAGES
define("_DS_NOSID", "Non &egrave; stato selezionata alcuna indagine da eliminare");
define("_DS_DELMESSAGE1", "Eliminare questa indagine?");
define("_DS_DELMESSAGE2", "Questa operazione eliminer&agrave; l&#039;indagine e tutti i relativi gruppi, domande, risposte e condizioni.");
define("_DS_DELMESSAGE3", "Si consiglia di esportare l&#039;indagine dalla pagina di amministrazione generale prima di eliminarla.");
define("_DS_SURVEYACTIVE", "Questa indagine &egrave; attiva ed esiste un database delle risposte. Eliminando l&#039;indagine si eliminano anche le risposte. Si consiglia di esportare le risposte prima di eliminare l&#039;indagine.");
define("_DS_SURVEYTOKENS", "Questa indagine &egrave; associata ad una tabella di identificativi. Eliminando l&#039;indagine si eliminano anche tutti gli identificativi. Si consiglia di esportare o fare il backup degli identificativi prima di eliminare l&#039;indagine.");
define("_DS_DELETED", "L&#039;indagine &egrave; stata eliminata.");

//DELETE QUESTION AND GROUP MESSAGES
define("_DG_RUSURE", "Cancellando questo gruppo si cancelleranno anche le domande e le risposte che contiene. Sei sicuro di voler continuare?"); //New for 098rc5
define("_DQ_RUSURE", "Cancellando questa domanda si cancelleranno tutte le risposte che include. Sei sicuro di voler continuare?"); //New for 098rc5

//EXPORT MESSAGES
define("_EQ_NOQID", "L&#039;ID delle domande (QID) non &egrave; stato inserito. Impossibile fare il dump della domanda.");
define("_ES_NOSID", "L&#039;ID dell&#039;indagine (SID) non &egrave; stato inserito. Impossibile fare il dump dell&#039;indagine");

//EXPORT RESULTS
define("_EX_FROMSTATS", "Filtra Script delle Statistiche");
define("_EX_HEADINGS", "Domande");
define("_EX_ANSWERS", "Risposte");
define("_EX_FORMAT", "Formato");
define("_EX_HEAD_ABBREV", "Titoli abbreviati");
define("_EX_HEAD_FULL", "Titoli completi");
define("_EX_ANS_ABBREV", "Codice risposte");
define("_EX_ANS_FULL", "Risposte complete");
define("_EX_FORM_WORD", "Microsoft Word");
define("_EX_FORM_EXCEL", "Microsoft Excel");
define("_EX_FORM_CSV", "CSV delimitato da virgole");
define("_EX_EXPORTDATA", "Esporta dati");
define("_EX_COLCONTROLS", "Controllo di colonna"); //New for 0.98rc7
define("_EX_TOKENCONTROLS", "Controllo degli identificativi"); //New for 0.98rc7
define("_EX_COLSELECT", "Scegli colonne"); //New for 0.98rc7
define("_EX_COLOK", "Scegli le colonne che desideri esportare. Non selezionare nulla per esportare tutte le colonne."); //New for 0.98rc7
define("_EX_COLNOTOK", "La tua indagine contiene pi&ugrave; di 255 colonne di risposte. I Fogli ci Calcolo come Excel non ne prevedono pi&ugrave; di 255. Seleziona le colonne che desideri esportare nella lista qui sotto"); //New for 0.98rc7
define("_EX_TOKENMESSAGE", "La tua indagine pu&ograve; esportare gli identificativi associati a ciascuna risposta. Seleziona gli altri campi che desideri esportare."); //New for 0.98rc7
define("_EX_TOKSELECT", "Scegli i campi degli identificativi"); //New for 0.98rc7

//IMPORT SURVEY MESSAGES
define("_IS_FAILUPLOAD", "Errore durante l&#039;upload del file. Il problema pu&ograve; essere dovuto a identificativi errati nella cartella admin.");
define("_IS_OKUPLOAD", "Upload del file completato.");
define("_IS_READFILE", "Lettura del file..");
define("_IS_WRONGFILE", "Il file non &egrave; un file PHPSurveyor. Importazione non riuscita.");
define("_IS_IMPORTSUMMARY", "Riepilogo importazione indagine");
define("_IS_SUCCESS", "Importazione indagine completata.");
define("_IS_IMPFAILED", "Importazione indagine non riuscita");
define("_IS_FILEFAILS", "Il file non contiene dati PHPSurveyor nel formato corretto.");

//IMPORT GROUP MESSAGES
define("_IG_IMPORTSUMMARY", "Riepilogo importazione gruppo");
define("_IG_SUCCESS", "Importazione gruppo completata.");
define("_IG_IMPFAILED", "Importazione gruppo non riuscita");
define("_IG_WRONGFILE", "Il file non contiene dati PHPSurveyor nel formato corretto.");

//IMPORT QUESTION MESSAGES
define("_IQ_NOSID", "IL SID (ID Indagine) non &egrave; stato inserito. Impossibile importare la domanda.");
define("_IQ_NOGID", "Il GID (ID Gruppo) non &egrave; stato inserito. Impossibile importare la domanda.");
define("_IQ_WRONGFILE", "Il file non &egrave; un file per le domande PHPSurveyor. L&#039;importazione non &egrave; riuscita.");
define("_IQ_IMPORTSUMMARY", "Riepilogo importazione domande");
define("_IQ_SUCCESS", "Importazione delle domande completata");

//IMPORT LABELSET MESSAGES
define("_IL_DUPLICATE", "E&#039; presente un labelset duplicato, per questo motivo questo insieme non &egrave; stato importato. Verr&agrave; usato il duplicato.");

//BROWSE RESPONSES MESSAGES
define("_BR_NOSID", "L&#039;indagine non &egrave; stata selezionata.");
define("_BR_NOTACTIVATED", "L&#039;indagine non &egrave; attiva. Non ci sono dati da visionare.");
define("_BR_NOSURVEY", "L&#039;indagine non esiste.");
define("_BR_EDITRESPONSE", "Modifica risposta");
define("_BR_DELRESPONSE", "Elimina risposta");
define("_BR_DISPLAYING", "Record visualizzati:");
define("_BR_STARTING", "Inizio da:");
define("_BR_SHOW", "Mostra");
define("_DR_RUSURE", "Sei sicuro di voler cancellare quanto hai inserito?"); //New for 0.98rc6

//STATISTICS MESSAGES
define("_ST_FILTERSETTINGS", "Imposta filtro");
define("_ST_VIEWALL", "Vista delle Stats di tutte le domande disponibili"); //New with 0.98rc8
define("_ST_SHOWRESULTS", "Vista delle statistiche"); //New with 0.98rc8
define("_ST_CLEAR", "Annulla"); //New with 0.98rc8
define("_ST_RESPONECONT", "Risposte contenenti"); //New with 0.98rc8
define("_ST_NOGREATERTHAN", "Numero pi&ugrave; grande di"); //New with 0.98rc8
define("_ST_NOLESSTHAN", "Numero pi&ugrave; piccolo di"); //New with 0.98rc8
define("_ST_DATEEQUALS", "Data (YYYY-MM-DD) eguaglia"); //New with 0.98rc8
define("_ST_ORBETWEEN", "OR tra"); //New with 0.98rc8
define("_ST_RESULTS", "Risultati"); //New with 0.98rc8 (Plural)
define("_ST_RESULT", "Risultato"); //New with 0.98rc8 (Singular)
define("_ST_RECORDSRETURNED", "Numero di record in questa query"); //New with 0.98rc8
define("_ST_TOTALRECORDS", "Record totali nell&#039;indagine"); //New with 0.98rc8
define("_ST_PERCENTAGE", "Percentuale del totale"); //New with 0.98rc8
define("_ST_FIELDSUMMARY", "Campo sommario per"); //New with 0.98rc8
define("_ST_CALCULATION", "Calcolo"); //New with 0.98rc8
define("_ST_SUM", "Somma"); //New with 0.98rc8 - Mathematical
define("_ST_STDEV", "Deviazione Standard "); //New with 0.98rc8 - Mathematical
define("_ST_AVERAGE", "Media"); //New with 0.98rc8 - Mathematical
define("_ST_MIN", "Minimo"); //New with 0.98rc8 - Mathematical
define("_ST_MAX", "Massimo"); //New with 0.98rc8 - Mathematical
define("_ST_Q1", "Primo Quartile (Q1)"); //New with 0.98rc8 - Mathematical
define("_ST_Q2", "Secondo Quartile (Mediana)"); //New with 0.98rc8 - Mathematical
define("_ST_Q3", "Terzo Quartile (Q3)"); //New with 0.98rc8 - Mathematical
define("_ST_NULLIGNORED", "*Valori nulli sono ignorati nei calcoli"); //New with 0.98rc8
define("_ST_QUARTMETHOD", "*Q1 and Q3 sono calcolati utilizzando il <a href=&#039;http://mathforum.org/library/drmath/view/60969.html&#039; target=&#039;_blank&#039;>metodo minitab</a>"); //New with 0.98rc8

//DATA ENTRY MESSAGES
define("_DE_NOMODIFY", "Impossibile modificare");
define("_DE_UPDATE", "Aggiorna");
define("_DE_NOSID", "Non &egrave; stata selezionata nessuna indagine per l&#039;inserimento dei dati.");
define("_DE_NOEXIST", "L&#039;indagine selezionata &egrave; inesistente");
define("_DE_NOTACTIVE", "L&#039;indagine non &egrave; attiva. Impossibile salvare le risposte");
define("_DE_INSERT", "Inserisci dati");
define("_DE_RECORD", "E&#039; stato assegnato il seguente ID: ");
define("_DE_ADDANOTHER", "Aggiungi dati");
define("_DE_VIEWTHISONE", "Mostra dati");
define("_DE_BROWSE", "Sfoglia risposte");
define("_DE_DELRECORD", "Dati eliminati");
define("_DE_UPDATED", "Dati aggiornati.");
define("_DE_EDITING", "Crea risposte");
define("_DE_QUESTIONHELP", "Note su questa domanda");
define("_DE_CONDITIONHELP1", "Rispondi solo se le seguenti condizioni sono rispettate:");
define("_DE_CONDITIONHELP2", "Alla domanda {QUESTION}, hai risposto {ANSWER}"); //This will be a tricky one depending on your languages syntax. {ANSWER} is replaced with ALL ANSWERS, separated by _DE_OR (OR).
define("_DE_AND", "E");
define("_DE_OR", "O");
define("_DE_SAVEENTRY", "Salva come un&#039;indagine parzialmente completata"); //New in 0.99dev01
define("_DE_SAVEID", "Identificatore:"); //New in 0.99dev01
define("_DE_SAVEPW", "Password:"); //New in 0.99dev01
define("_DE_SAVEPWCONFIRM", "Conferma Password:"); //New in 0.99dev01
define("_DE_SAVEEMAIL", "e-mail:"); //New in 0.99dev01


//TOKEN CONTROL MESSAGES
define("_TC_TOTALCOUNT", "Numero totale record in questa tabella degli identificativi:"); //New in 0.98rc4
define("_TC_NOTOKENCOUNT", "Totale senza identificativo unico:"); //New in 0.98rc4
define("_TC_INVITECOUNT", "Totale inviti inviati:"); //New in 0.98rc4
define("_TC_COMPLETEDCOUNT", "Totale questionari completati:"); //New in 0.98rc4
define("_TC_NOSID", "Nessuna indagine selezionata");
define("_TC_DELTOKENS", "Elimina identificativi per questa indagine.");
define("_TC_DELTOKENSINFO", "L&#039;eliminanazione della tabella degli identificativi, render&agrave; l&#039;indagine aperta a tutti. Verr&agrave; eseguito un backup dei dati a cui solo l&#039;amministratore di sistema avr&agrave; accesso.");
define("_TC_DELETETOKENS", "Elimina tabella degli identificativi");
define("_TC_TOKENSGONE", "La tabella degli identificativi &egrave; stata eliminata e non &egrave; pi&ugrave; obbligatorio avere un identificativo di ingresso per accedere all&#039;indagine. Se si procede verr&agrave; eseguito un backup dei dati al quale solo l&#039;amministratore di sistema avr&agrave; accesso.");
define("_TC_NOTINITIALISED", "Gli identificativi non sono stati attivati.");
define("_TC_INITINFO", "Attivando gli identificativi per questa indagine, l&#039;accesso all&#039;indagine sar&agrave; riservata solo agli utenti con un identificativo.");
define("_TC_INITQ", "Creare una tabella degli identificativi per questa indagine?");
define("_TC_INITTOKENS", "Genera identificativi");
define("_TC_CREATED", "E&#039; stata creata una tabella degli identificativi per questa indagine.");
define("_TC_DELETEALL", "Azzera gli identificativi");
define("_TC_DELETEALL_RUSURE", "Procedere nell&#039;eliminazione delle voci di TUTTI gli identificativi?");
define("_TC_ALLDELETED", "Tutti gli identificativi sono stati azzerati");
define("_TC_CLEARINVITES", "Non spedire gli inviti");
define("_TC_CLEARINV_RUSURE", "Annullare la spedizione di tutti gli inviti?");
define("_TC_CLEARTOKENS", "Elimina codici associati agli identificativi");
define("_TC_CLEARTOKENS_RUSURE", "Elimina tutti i codici associati agli identificativi?");
define("_TC_TOKENSCLEARED", "I codici associati agli identificativi sono stati eliminati");
define("_TC_INVITESCLEARED", "Gli inviti non verranno spediti");
define("_TC_EDIT", "Genera idenficativi");
define("_TC_DEL", "Elimina identificativi");
define("_TC_DO", "Prova Indagine");
define("_TC_VIEW", "Mostra Risposta");
define("_TC_UPDATE", "Update Response"); // New with 0.99 stable
define("_TC_INVITET", "Invia invito a questo utente");
define("_TC_REMINDT", "Invia sollecito a questo utente");
define("_TC_INVITESUBJECT", "Invito a partecipare all&#039;indagine {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSUBJECT", "Sollecito a partecipare all&#039;indagine {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSTARTAT", "Incomincia dall&#039;ID dell&#039;identificativo n.:");
define("_TC_REMINDTID", "Invia a ID dell&#039;identificativo n.:");
define("_TC_CREATETOKENSINFO", "Facendo clic su SI verranno generati automaticamente i codici e le password di identificazione per tutti gli utenti che ne sono ancora sprovvisti. Procedere?");
define("_TC_TOKENSCREATED", "{TOKENCOUNT} identificativi sono stati creati"); //Leave {TOKENCOUNT} for replacement in script with the number of tokens created
define("_TC_TOKENDELETED", "Identificativo eliminato.");
define("_TC_SORTBY", "Ordina: ");
define("_TC_ADDEDIT", "Aggiungi o genera identificativi");
define("_TC_TOKENCREATEINFO", "Si pu&ograve; scegliere di lasciare questo campo vuoto e generare i identificativi automaticamente usando &#039;Genera identificativi&#039;");
define("_TC_TOKENADDED", "Un nuovo identificativo &egrave; stato creato");
define("_TC_TOKENUPDATED", "Aggiorna identificativi");
define("_TC_UPLOADINFO", "Il file deve essere un file CSV standard (delimitato da virgole) senza apici. La prima riga deve contenere i dati dell&#039;header (che verr&agrave; rimossa). I dati devono seguire il seguente ordine &#039;nome, cognome, email, [Token], [Attribute1], [Attribute2]&#039;.");
define("_TC_UPLOADFAIL", "Lista di upload non trovata. Controlla i tuoi permessi ed il percorso per vedere se c&#039;&egrave; l&#039;indice di upload"); //New for 0.98rc5 (babelfish translation)
define("_TC_IMPORT", "Importazione File CSV");
define("_TC_CREATE", "Crea voce identificativi");
define("_TC_TOKENS_CREATED", "{TOKENCOUNT} voci sono state create");
define("_TC_NONETOSEND", "Nessun messaggio e-mail da inviare. I messaggi non soddisfano i seguenti criteri - indirizzo e-mail valido, non &egrave; stato ancora inviato un invito, l&#039;indagine &egrave; gi&agrave; stata completata, l&#039;utente ha gi&agrave; un identificativo");
define("_TC_NOREMINDERSTOSEND", "Nessun messaggio e-mail da inviare. I messaggi non soddisfano i seguenti criteri - indirizzo e-mail valido, un invito &egrave; gi&agrave; stato inviato ma l&#039;indagine non &egrave; stata completata.");
define("_TC_NOEMAILTEMPLATE", "Impossibile trovare il template degli inviti. Il file dovrebbe essere nella cartella template di default.");
define("_TC_NOREMINDTEMPLATE", "Promemoria Impossibile trovare Template degli inviti. Il file dovrebbe essere nella cartella template di default.");
define("_TC_SENDEMAIL", "Invia inviti");
define("_TC_SENDINGEMAILS", "Invio degli inviti in corso");
define("_TC_SENDINGREMINDERS", "Invia solleciti");
define("_TC_EMAILSTOGO", "Troppe e-mail in attesa di essere spedite. Impossibile inviarle tutte insieme. Clicca sul seguente link per continuare a inviare i messaggi.");
define("_TC_EMAILSREMAINING", "Ci sono ancora {EMAILCOUNT} e-mail in attesa di invio."); //Leave {EMAILCOUNT} for replacement in script by number of emails remaining
define("_TC_SENDREMIND", "Invia solleciti");
define("_TC_INVITESENTTO", "Invito inviato a:"); //is followed by token name
define("_TC_REMINDSENTTO", "Sollecito inviato a:"); //is followed by token name
define("_TC_UPDATEDB", "Aggiorna la tabella degli identificativi con nuovi campi"); //New for 0.98rc7
define("_TC_EMAILINVITE_SUBJ", "Invito a partecipare ad una indagine on line"); //New for 0.99dev01
define("_TC_EMAILINVITE", "Caro {FIRSTNAME},\n\n sei invitato a partecipare ad una indagine on line.\n\n"
						 ."L'indagine &egrave; intitolata:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
						 ."Per partecipare fai clic sul link qui sotto e rispondi alle domande del questionario.\n\nCordiali saluti,\n\n"
						 ."{ADMINNAME} ({ADMINEMAIL})\n\n"
						 ."----------------------------------------------\n"
						 ."Fai clic qui per accedere all'indagine:\n"
						 ."{SURVEYURL}"); //New for 0.98rc9 - default Email Invitation
define("_TC_EMAILREMIND_SUBJ", "Sollecito a partecipare all'indagine on line"); //New for 0.99dev01
define("_TC_EMAILREMIND", "Caro {FIRSTNAME},\n\nRecentemente ti abbiamo invitato  a partecipare ad una indagine on line.\n\n"
						 ."Abbiamo notato che non hai ancora completato il questionario. Con l'occasione ti ricordiamo che il questionario &egrave; ancora disponibile.\n\n"
						 ."L'indagine &egrave; intitolata:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
						 ."Per partecipare fai clic sul link qui sotto.\n\nCordiali saluti,\n\n"
						 ."{ADMINNAME} ({ADMINEMAIL})\n\n"
						 ."----------------------------------------------\n"
						 ."Fai clic qui per accedere all'indagine:\n"
						 ."{SURVEYURL}"); //New for 0.98rc9 - default Email Reminder


define("_TC_EMAILREGISTER_SUBJ", "Conferma registrazione all'indagine on line"); //New for 0.99dev01
define("_TC_EMAILREGISTER", "Caro {FIRSTNAME},\n\n"
						  ."Abbiamo ricevuto la tua registrazione per la partecipazione  "
						  ."all'indagine on line intitolata {SURVEYNAME}.\n\n"
						  ."Per completare il relativo questionario, fai clic sul seguente indirizzo:\n"
						  ."{SURVEYURL}\n\n"
						  ."Se hai qualche domanda da fare circa l'indagine, o se non ti sei "
						  ."affatto registrato e ritieni che questa e-mail sia errata "
						  ."sei pregato di contattare {ADMINNAME} al seguente indirizzo {ADMINEMAIL}.");//NEW for 0.98rc9
define("_TC_EMAILCONFIRM_SUBJ", "Conferma del completamento dell'indagine on line"); //New for 0.99dev01
define("_TC_EMAILCONFIRM", "Caro {FIRSTNAME},\n\nQuesta e-mail ti &egrave; stata inviata per confermarti che hai completato corretamente l'indagine initolata {SURVEYNAME} "
						  ." e che le tue risposte sono state salvate. Grazie per la partecipazione.\n\n"
						  ."Se hai ulteriori domande circa questo messaggio, contatta {ADMINNAME} all&#039;indirizzo e-mail {ADMINEMAIL}.\n\n"
						  ."Cordiali saluti\n\n"
						  ."{ADMINNAME}"); //New for 0.98rc9 - Confirmation Email

//labels.php
define("_LB_NEWSET", "Crea un nuovo gruppo di etichette");
define("_LB_EDITSET", "Modifica gruppo di etichette");
define("_LB_FAIL_UPDATESET", "Aggiornamento del gruppo di etichette non riuscito");
define("_LB_FAIL_INSERTSET", "Inserimento del nuovo gruppo di etichette non riuscito");
define("_LB_FAIL_DELSET", "Impossibile cancellare il gruppo di etichette - Ci sono delle domande che dipendono da esso. E&#039; necessario eliminare prima queste domande.");
define("_LB_ACTIVEUSE", "Impossibile modificare i codici, aggiungere o eliminare elementi in questo gruppo di etichette perch&egrave; &egrave; utilizzato da un&#039;indagine attiva.");
define("_LB_TOTALUSE", "Alcune indagini usano questo gruppo di etichette. Modificare i codici, aggiungere o eliminare elementi in questo gruppo di etichette pu&ograve; portare a risultati non desiderati sulle altre indagini.");

//Export Labels
define("_EL_NOLID", "Non &egrave; stato fornito un identificatore di etichetta (LID). Impossibile salvare il gruppo di etichette.");
//Import Labels
define("_IL_GOLABELADMIN", "Ritorna alla gestione delle etichette");

//PHPSurveyor System Summary
define("_PS_TITLE", "Riepilogo di sistema di PHPSurveyor");
define("_PS_DBNAME", "Nome database");
define("_PS_DEFLANG", "Lingua di default");
define("_PS_CURLANG", "Lingua corrente");
define("_PS_USERS", "Utenti");
define("_PS_ACTIVESURVEYS", "Indagini attive");
define("_PS_DEACTSURVEYS", "Indagini disattivate");
define("_PS_ACTIVETOKENS", "Tabelle degli identificativi attivi");
define("_PS_DEACTTOKENS", "Tabelle degli identificativi disattivati");
define("_PS_CHECKDBINTEGRITY", "Controlla l&#039;integrit&agrave; dei dati di PHPSurveyor"); //New with 0.98rc8

//Notification Levels
define("_NT_NONE", "Nessuna e-mail di notifica"); //New with 098rc5
define("_NT_SINGLE", "e-mail di notifica standard"); //New with 098rc5
define("_NT_RESULTS", "e-mail di notifica con risposte"); //New with 098rc5

//CONDITIONS TRANSLATIONS
define("_CD_CONDITIONDESIGNER", "Definizione delle Condizioni"); //New with 098rc9
define("_CD_ONLYSHOW", "Mostra questa domanda con codice   {QID}   solo SE:"); //New with 098rc9 - {QID} is repleaced leave there
define("_CD_AND", "AND"); //New with 098rc9
define("_CD_COPYCONDITIONS", "Copia condizioni"); //New with 098rc9
define("_CD_CONDITION", "Condizione"); //New with 098rc9
define("_CD_ADDCONDITION", "Aggiungi condizione"); //New with 098rc9
define("_CD_EQUALS", "Uguaglia"); //New with 098rc9
define("_CD_COPYRUSURE", "Sei sicuro di voler copiare questa(e) condizione(i) alle domande che hai selezionato?"); //New with 098rc9
define("_CD_NODIRECT", "Non puoi lanciare questo script direttamente."); //New with 098rc9
define("_CD_NOSID", "Non hai selezionato alcuna indagine."); //New with 098rc9
define("_CD_NOQID", "Non hai selezionato alcuna domanda."); //New with 098rc9
define("_CD_DIDNOTCOPYQ", "Non hai copiato domande"); //New with 098rc9
define("_CD_NOCONDITIONTOCOPY", "Nessuna condizione da selezionata per copiarla"); //New with 098rc9
define("_CD_NOQUESTIONTOCOPYTO", "Nessuna domanda selezionata per copiare le condizioni a "); //New with 098rc9







//TEMPLATE EDITOR TRANSLATIONS
define("_TP_CREATENEW", "Crea nuovo template"); //New with 098rc9
define("_TP_NEWTEMPLATECALLED", "Crea nuovo template con nome:"); //New with 098rc9
define("_TP_DEFAULTNEWTEMPLATE", "NewTemplate"); //New with 098rc9 (default name for new template)
define("_TP_CANMODIFY", "Questo template pu&ograve; essere modificato"); //New with 098rc9
define("_TP_CANNOTMODIFY", "Questo template non pu&ograve; essere modificato"); //New with 098rc9
define("_TP_RENAME", "Rinomina questo template");  //New with 098rc9
define("_TP_RENAMETO", "Rinomina questo template come:"); //New with 098rc9
define("_TP_COPY", "Fai una copia di questo template");  //New with 098rc9
define("_TP_COPYTO", "Crea una copia di questo template con nome:"); //New with 098rc9
define("_TP_COPYOF", "copia_di_"); //New with 098rc9 (prefix to default copy name)
define("_TP_FILECONTROL", "Controllo File:"); //New with 098rc9
define("_TP_STANDARDFILES", "Standard File:");  //New with 098rc9
define("_TP_NOWEDITING", "Modifica ora:");  //New with 098rc9
define("_TP_OTHERFILES", "Altri File:"); //New with 098rc9
define("_TP_PREVIEW", "Anteprima:"); //New with 098rc9
define("_TP_DELETEFILE", "Cancella"); //New with 098rc9
define("_TP_UPLOADFILE", "Upload"); //New with 098rc9
define("_TP_SCREEN", "Finestra:"); //New with 098rc9
define("_TP_WELCOMEPAGE", "Pagina Benvenuto"); //New with 098rc9
define("_TP_QUESTIONPAGE", "Pagina Domanda"); //New with 098rc9
define("_TP_SUBMITPAGE", "Pagina Invio");
define("_TP_COMPLETEDPAGE", "Pagina Completata"); //New with 098rc9
define("_TP_CLEARALLPAGE", "Pagina Azzera"); //New with 098rc9
define("_TP_REGISTERPAGE", "Pagina Registra "); //New with 098finalRC1
define("_TP_EXPORT", "Esporta Template"); //New with 098rc10
define("_TP_LOADPAGE", "Carica Pagina"); //New with 0.99dev01
define("_TP_SAVEPAGE", "Salva Pagina"); //New with 0.99dev01

//Saved Surveys
define("_SV_RESPONSES", "Risposte salvate:");
define("_SV_IDENTIFIER", "Identificatore");
define("_SV_RESPONSECOUNT", "Risposto");
define("_SV_IP", "Indirizzo IP");
define("_SV_DATE", "Data salvata");
define("_SV_REMIND", "Ricorda");
define("_SV_EDIT", "Modifica");

//VVEXPORT/IMPORT
define("_VV_IMPORTFILE", "Importa file VV indagine");
define("_VV_EXPORTFILE", "Esporta file VV indagine");
define("_VV_FILE", "File:");
define("_VV_SURVEYID", "ID dell&#039;indagine:");
define("_VV_EXCLUDEID", "Escludi ID dei record");
define("_VV_INSERT", "Quando un record importato &egrave; simile ad un record esistente con ID:");
define("_VV_INSERT_ERROR", "Segnala un errore (e salta ad un nuovo record).");
define("_VV_INSERT_RENUMBER", "Rinumera il nuovo record.");
define("_VV_INSERT_IGNORE", "Ignora il nuovo record.");
define("_VV_INSERT_REPLACE", "Sostituisci il record esistente.");
define("_VV_DONOTREFRESH", "Nota importante:<br />NON AGGIORNARE questa pagina, pech&egrave; questo comporta l&#039;importazione di un nuovo file e  produrr&agrave; duplicati");
define("_VV_IMPORTNUMBER", "Totale dei record importati:");
define("_VV_ENTRYFAILED", "Importazione fallita sul record");
define("_VV_BECAUSE", "perch&egrave;");
define("_VV_EXPORTDEACTIVATE", "Esporta, quindi disattiva l&#039;indagine");
define("_VV_EXPORTONLY", "Esporta ma lascia attiva l&#039;indagine");
define("_VV_RUSURE", "Se hai scelto di esportare disattivando l'indagine la tabella corrente delle risposte verrÃ  rinominata e non sar&agrave; facile ripristinarla. Sei sicuro di voler procedere?");

//ASSESSMENTS
define("_AS_TITLE", "Valutazioni");
define("_AS_DESCRIPTION", "Se crei una valutazione per l&#039;indagine corrente questa sar&agrave; aggiornata alla fine dell&#039;indagine dopo il suo invio");
define("_AS_NOSID", "Nessun SID (ID dell&#039;indagine) fornito");
define("_AS_SCOPE", "Campo applicazione");
define("_AS_MINIMUM", "Minimo");
define("_AS_MAXIMUM", "Massimo");
define("_AS_GID", "Gruppo");
define("_AS_NAME", "Nome/Header");
define("_AS_HEADING", "Intestazione");
define("_AS_MESSAGE", "Messaggio");
define("_AS_URL", "URL");
define("_AS_SCOPE_GROUP", "Gruppo");
define("_AS_SCOPE_TOTAL", "Totale");
define("_AS_ACTIONS", "Azioni");
define("_AS_EDIT", "Modifica");
define("_AS_DELETE", "Cancella");
define("_AS_ADD", "Aggiungi/Modifica");
define("_AS_UPDATE", "Aggiorna");

//Question Number regeneration
define("_RE_REGENNUMBER", "Rigenera numeri domande:"); 
define("_RE_STRAIGHT", "Tutti"); 
define("_RE_BYGROUP", "Per gruppo");

// Database Consistency Check
define ("_DC_TITLE", "Controllo consistenza dati<br /><font size='1'>Se vengono indicati degli errori si dovrebbe eseguire nuovamente questo script. </font>"); // New with 0.99stable
define ("_DC_QUESTIONSOK", "Tutte le domande soddisfano gli standard di consistenza"); // New with 0.99stable
define ("_DC_ANSWERSOK", "Tutte le risposte soddisfano gli standard di consistenza"); // New with 0.99stable
define ("_DC_CONDITIONSSOK", "Tutte le condizioni soddisfano gli standard di consistenza"); // New with 0.99stable
define ("_DC_GROUPSOK", "Tutti i gruppi soddisfano gli standard di consistenza"); // New with 0.99stable
define ("_DC_NOACTIONREQUIRED", "Non si richiede alcuna azione sul database"); // New with 0.99stable
define ("_DC_QUESTIONSTODELETE", "Le seguenti domande dovrebbero essere eliminate"); // New with 0.99stable
define ("_DC_ANSWERSTODELETE", "Le seguenti risposte dovrebbero essere eliminate"); // New with 0.99stable
define ("_DC_CONDITIONSTODELETE", "Le seguenti condizioni dovrebbero essere eliminate"); // New with 0.99stable
define ("_DC_GROUPSTODELETE", "I seguenti gruppi dovrebbero essere eliminati"); // New with 0.99stable
define ("_DC_ASSESSTODELETE", "Le seguenti valutazioni-punteggi dovrebbero essere eliminate"); // New with 0.99stable
define ("_DC_QATODELETE", "I seguenti attributi delle domande devorebbero essere eliminati"); // New with 0.99stable
define ("_DC_QAOK", "Tutti gli attributi delle domande soddisfano gli standard di consistenza"); // New with 0.99stable
define ("_DC_ASSESSOK", "Tutte la valutazioni-punteggi soddisfano gli standard di consistenza"); // New with 0.99stable

?>
