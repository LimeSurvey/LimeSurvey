<?php
/*
    #############################################################
    # >>> PHP Surveyor                                          #
    #############################################################
    # > Author:  Jason Cleeland                                 #
    # > E-mail:  jason@cleeland.org                             #
    # > Mail:    Box 99, Trades Hall, 54 Victoria St,           #
    # >          CARLTON SOUTH 3053, AUSTRALIA                  #
    # > Date:      20 February 2003                             #
    #                                                           #
    # This set of scripts allows you to develop, publish and    #
    # perform data-entry on surveys.                            #
    #############################################################
    #                                                           #
    #    Copyright (C) 2003  Jason Cleeland                     #
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
    #                                                           #
    # This language file kindly provided by Frederik Kunz, and  #
    # corrections/updates by Ralph Kampmann, Carsten Schmitz    #  
    # corrections, changes to german-informal by                #
    # Edith Schicho & Peter Sereinigg                           #
    #############################################################
*/

//SINGLE WORDS 
define("_YES", "Ja"); 
define("_NO", "Nein"); 
define("_UNCERTAIN", "Unsicher"); 
define("_ADMIN", "Administrator"); 
define("_TOKENS", "Zugangsschl&uuml;ssel");
define("_FEMALE", "Weiblich"); 
define("_MALE", "M&auml;nnlich"); 
define("_NOANSWER", "Keine Antwort"); 
define("_NOTAPPLICABLE", "Nicht zutreffend");
define("_OTHER", "Sonstige"); 
define("_PLEASECHOOSE", "Bitte w&auml;hlen"); 
define("_ERROR_PS", "Fehler"); 
define("_COMPLETE", "vollst&auml;ndig");
define("_INCREASE", "Zunahme");
define("_SAME", "Unver&auml;ndert");
define("_DECREASE", "Abnahme");
define("_REQUIRED", "<font color='red'>*</font>");
//from questions.php
define("_CONFIRMATION", "Best&auml;tigung"); 
define("_TOKEN_PS", "Schl&uuml;sselwort"); 
define("_CONTINUE_PS", "Weiter");

//BUTTONS 
define("_ACCEPT", "Annehmen");
define("_PREV", "zurück"); 
define("_NEXT", "weiter"); 
define("_LAST", "abschliessen"); 
define("_SUBMIT", "absenden");



//MESSAGES From QANDA.PHP
define("_CHOOSEONE", "Bitte w&auml;hle eine Antwort aus"); 
define("_ENTERCOMMENT", "Bitte gib hier deinen Kommentar ein"); 
define("_NUMERICAL_PS", "In dieses Feld d&uuml;rfen nur Ziffern eingetragen werden"); 
define("_CLEARALL", "Ergebnisse verwerfen und Befragung beenden"); 
define("_MANDATORY", "Bitte beantworte diese Frage"); 
define("_MANDATORY_PARTS", "Bitte beende alle Bereiche/Teile"); 
define("_MANDATORY_CHECK", "Bitte mache mindestens ein Kreuzchen"); 
define("_MANDATORY_RANK", "Bitte bringe alle Elemente in eine Rangfolge"); 
define("_MANDATORY_POPUP", "Eine oder mehr vorgeschriebene Fragen sind nicht beantwortet worden. Du kannst nicht fortfahren, bis diese beantwortet wurden!");
define("_VALIDATION", "Diese Frage muss korrekt beantwortet werden");
define("_VALIDATION_POPUP", "Ein oder mehrere Fragen sind nicht korrekt beantwortet worden. Sie k&ouml;nnen nicht fortfahren, bevor Sie dies nicht getan haben.");
define("_DATEFORMAT", "Format: JJJJ-MM-TT"); 
define("_DATEFORMATEG", "(z.B.: 2003-12-24 f&uuml;r Heiligabend)");
define("_REMOVEITEM", "Entferne bitte dieses Element"); 
define("_RANK_1", "Klicke in der Liste links bitte zuerst das am h&ouml;chsten"); 
define("_RANK_2", "bewertete Element an und mache weiter bis zum niedrigsten."); 
define("_YOURCHOICES", "Deine Auswahl"); 
define("_YOURRANKING", "Deine Rangfolge"); 
define("_RANK_3", "Klicke auf die Schere rechts von jedem Element, um das"); 
define("_RANK_4", "zuletzt hinzugef&uuml;gte Element aus der Liste zu entfernen"); 

//From INDEX.PHP 
define("_NOSID", "du hast keine Identifikationsnummer f&uuml;r den Fragebogen angegeben.");
define("_CONTACT1", "Bitte kontaktiere"); 
define("_CONTACT2", "f&uuml;r weitere Unterst&uuml;tzung.");
define("_ANSCLEAR", "Antworten verworfen");
define("_RESTART", "Umfrage neu starten");
define("_CLOSEWIN_PS", "Fenster schliessen"); 
define("_CONFIRMCLEAR", "Bist du sicher, dass Sie alle Antworten verwerfen m&ouml;chten?");
define("_CONFIRMSAVE", "Sind Sie sicher, dass Sie Ihre Antworten speichern wollen?");
define("_EXITCLEAR", "Umfrage beenden und verwerfen");

//From QUESTION.PHP 
define("_BADSUBMIT1", "Kann keine Ergebnisse abschicken, da keine vorhanden sind.");
define("_BADSUBMIT2", "Dieser Fehler kann auftreten, wenn du deine Antworten bereits abgeschickt hast und in deinem Browser auf 'aktualisieren' geklickt hast. In diesem Fall wurden Deine Fragen bereits gespeichert."); 
define("_NOTACTIVE1", "Ihre Antworten wurden nicht gespeichert. Diese Befragung ist noch nicht aktiv."); 
define("_CLEARRESP", "Antworten verwerfen"); 
define("_THANKS", "Vielen Dank"); 
define("_SURVEYREC", "Deine Antworten wurden gespeichert."); 
define("_SURVEYCPL", "Befragung beendet"); 
define("_DIDNOTSAVE", "Wurde nicht gespeichert"); 
define("_DIDNOTSAVE2", "Ein unerwarteter Fehler ist aufgetreten. Deine Antworten k&ouml;nnen leider nicht gespeichert werden."); 
define("_DIDNOTSAVE3", "Deine Antworten sind nicht verloren gegangen. Sie wurden per eMail an den Administrator dieser Befragung geschickt und werden zu einem sp&auml;teren Zeitpunkt manuell in die Datenbank eingepflegt.");
define("_DNSAVEEMAIL1", "Beim Speichern Deiner Fragebogen-Zugangsschl&uuml;ssels ist ein Fehler aufgetreten.");
define("_DNSAVEEMAIL2", "EINZUGEBENDE DATEN"); 
define("_DNSAVEEMAIL3", "FEHLERHAFTE SQL ANWEISUNG");
define("_DNSAVEEMAIL4", "FEHLERMELDUNG"); 
define("_DNSAVEEMAIL5", "FEHLER BEIM SPEICHERN"); 
define("_SUBMITAGAIN", "Versuche das Abschicken/Speichern erneut"); 
define("_SURVEYNOEXIST", "Es gibt leider keine passende Befragung."); 
define("_NOTOKEN1", "Um an dieser Befragung teilzunehmen ben&ouml;tigst du ein passendes Schl&uuml;sselwort."); 
define("_NOTOKEN2", "Falls du ein Schl&uuml;sselwort erhalten hast, gib es bitte in das untenstehende Feld ein und klicke auf 'Weiter'."); 
define("_NOTOKEN3", "Das von dir angegebene Schl&uuml;sselwort ist entweder ung&uuml;ltig oder wurde bereits verwendet."); 
define("_NOQUESTIONS", "F&uuml;r diese Befragung wurden noch keine Fragen angelegt. Sie kann weder getestet noch abgeschlossen werden.");
define("_FURTHERINFO", "Um weitere Informationen zu erhalten, kontaktiere bitte");
define("_NOTACTIVE", "Diese Befragung ist momentan nicht aktiv. Du wirst sie nicht abschliessen k&ouml;nnen.");
define("_SURVEYEXPIRED", "Diese Umfrage ist beendet und kann nicht mehr zur Verf&uuml;gung.");

define("_SURVEYCOMPLETE", "du hast diese Umfrage bereits durchgef&uuml;hrt.");

define("_INSTRUCTION_LIST", "Bitte w&auml;hle einen Punkt aus der Liste aus.");
define("_INSTRUCTION_MULTI", "Bitte w&auml;hle einen oder mehrere Punkte aus der Liste aus.");

define("_CONFIRMATION_MESSAGE1", "Deine Umfragedaten wurden &uuml;bermittelt.");
define("_CONFIRMATION_MESSAGE2", "Es sind neue Umfragedaten vorhanden.");
define("_CONFIRMATION_MESSAGE3", "Klicke hier, um die einzelnen Antworten zu sehen:");
define("_CONFIRMATION_MESSAGE4", "Ansichtstatistiken, bitte hier klicken:");

define("_PRIVACY_MESSAGE", "<strong><i>Schutz deiner Privatsph&auml;re</i></strong><br />"
                          ."Diese Umfrage ist anonym.<br />"
                          ."Die Daten, die wir aus dieser Umfrage erhalten, enthalten "
                          ."keinerlei Informationen, mit denen man Dich identifizieren k&ouml;nnte,"
                          ."es sei denn, innerhalb der Umfrage wurde danach gefragt. "
                          ."Wenn du eine Umfrage beantwortet habst, zu der du einen Zugangsschl&uuml;ssel "
                          ."ben&ouml;tigst, k&ouml;nnen wir dir versichern, dass dieser Zugangsschl&uuml;ssel "
                          ."nicht mit deinen Antworten in einer Tabelle gehalten wird. "
                          ."Alle Zugangsschl&uuml;ssel werden in einer separaten Tabelle gespeichert, "
                          ."und werden nur dazu benutzt, um zu sehen, ob du diese Umfrage schon ausgef&uuml;llt hast. "
                          ."Auf keinen Fall werden die Zugangschl&uuml;ssel mit deinen Daten aus dieser Umfrage in Verbindung gebracht werden.");

define("_THEREAREXQUESTIONS", "Diese Umfrage besteht aus {NUMBEROFQUESTIONS} Fragen."); // Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Diese Umfrage besteht aus einer Frage."); // singular version of above

define ("_RG_REGISTER1", "Du musst dich registrieren, um an dieser Umfrage teilnehmen zu k&ouml;nnen.");
define ("_RG_REGISTER2", "Du kannst dich registrieren, wenn du an dieser Umfrage teilnehmen m&ouml;chtest.<br />\n"
                        ."Bitte f&uuml;lle alle Angaben aus und wir senden dir sofort eine Email,"
                        ."die die n&ouml;tigen Einzelheiten f&uuml;r eine Teilnahme enth&auml;lt."); //NEW for 0.98rc9
define ("_RG_EMAIL", "Email-Addresse");
define ("_RG_FIRSTNAME", "Vorname");
define ("_RG_LASTNAME", "Nachname");
define ("_RG_INVALIDEMAIL", "Die angegebene Email-Adresse ist ung&uuml;ltig. Bitte versuche es erneut.");
define ("_RG_USEDEMAIL", "Die angegeben Email wurde schon einmal benutzt.");
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} Registrierungsbest&auml;tigung");
define ("_RG_REGISTRATIONCOMPLETE", "Vielen Dank, dass du dich f&uuml;r die Teilnahme an dieser Umfrage angemeldet hast.<br /><br />\n"
                                   ."Es wurde eine Email an die von dir angegebene Adresse versandt, die weitere Details f&uuml;r die Teilnahme "
                                   ."an der Umfrage enth&auml;lt. Bitte klicke auf den Link innerhalb der Email um fortzufahren.<br />\n"
                                   ."Mit freundlichen Grüßen<br /><br />\n"
                                   ."{ADMINNAME} ({ADMINEMAIL})");
define("_SM_COMPLETED", "<strong>Vielen Dank!<br /><br />"
                       ."du hast nun alle Fragen dieser Umfrage beantwortet.</strong><br /><br />"
                       ."Klicke jetzt auf 'Absenden', um diese Umfrage abzuschliesen und deine Antworten endg&uuml;ltig zu speichern.");
define("_SM_REVIEW", "Wenn du deine Antworten nochmal &uuml;berpr&uuml;fen und/oder &auml;ndern willst, "
                    ."dann klicke bitte auf den Knopf 'Zur&uuml;ck', um durch deine Antworten zu bl&auml;ttern.");


//For the "printable" survey
define("_PS_CHOOSEONE", "Bitte <strong>nur eine Antwort</strong> aus folgenden M&ouml;glichkeiten w&auml;hlen"); //New for 0.98finalRC1
define("_PS_WRITE", "Bitte schreibe deine Antwort hier"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Bitte <strong>alle</strong> ausw&auml;hlen, die zutreffen"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Bitte alle ausw&auml;hlen die zutreffen und einen Kommentar dazuschreiben"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Bitte w&auml;hle die zutreffende Antwort aus"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Bitte deine Antwort(en) hierher schreiben"); //New for 0.98finalRC1
define("_PS_DATE", "Bitte ein Datum eingeben"); //New for 0.98finalRC1
define("_PS_COMMENT", "Bitte schreibe einen Kommentar zu Deiner Auswahl"); //New for 0.98finalRC1
define("_PS_RANKING", "Bitte nummeriere jede Box in der Reigenfolge deiner Pr&auml;ferenz, beginnen mit 1 bis"); //New for 0.98finalRC1
define("_PS_SUBMIT", "&Uuml;bermittle deinen Fragebogen"); //New for 0.98finalRC1
define("_PS_THANKYOU", "Danke f&uuml;r die Beantwortung des Fragebogens."); //New for 0.98finalRC1
define("_PS_FAXTO", "Bitte faxe deinen ausgef&uuml;llten Fragebogen an"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Bitte beantworte diese Frage nur,"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "falls deine Antwort "); //New for 0.98finalRC1
define("_PS_CON_JOINER", "und"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", " war bei der Frage"); //New for 0.98finalRC1
define("_PS_CON_OR", "oder"); 

//Save Messages
define("_SAVE_AND_RETURN", "Bisherige Antworten speichern");
define("_SAVEHEADING", "Sichern der nicht abgeschlossenen Umfrage");
define("_RETURNTOSURVEY", "Zur Umfrage zur&uuml;ckkehren");
define("_SAVENAME", "Name");
define("_SAVEPASSWORD", "Passwort");
define("_SAVEPASSWORDRPT", "Passwort wiederholen");
define("_SAVE_EMAIL", "Ihre Emailadresse");
define("_SAVEEXPLANATION", "Bitte gebe einen Namen und ein Passwort f&uuml;r diese Umfrage ein und klicken Sie auf Speichern.<br />\n"
				  ."Die Umfrage wird dann unter Deinem Namen und Passwort gespeichert und kann "
				  ."durch Dich sp&auml;ter fortgesetzt werden, indem Du erneut Name und Passwort eingibst.<br /><br />\n"
				  ."Wenn Du eine Emailadresse angibst, wird Dir eine Email mit den entsprechenden Details "
				  ."zugesandt.");
define("_SAVESUBMIT", "Jetzt speichern");
define("_SAVENONAME", "Du musst einen Namen angeben.");
define("_SAVENOPASS", "Du musst ein Passwort angeben.");
define("_SAVENOMATCH", "Die Passw&ouml;rter stimmen nicht &uuml;berein.");
define("_SAVEDUPLICATE", "Dieser Name ist leider schon in Benutzung. Bitte benutze einen anderen Namen.");
define("_SAVETRYAGAIN", "Bitte versuche es erneut.");
define("_SAVE_EMAILSUBJECT", "Details Deiner gespeicherten Umfrage");
define("_SAVE_EMAILTEXT", "Du oder jemand, der Deine Email-Adresse benutzt hat, hat eine Umfrage "
						 ."zwischengespeichert. Du kannst die folgenden Informationen nutzen "
						 ."um zu der Umfrage zur&uuml;ckzukehren und diese an der Stelle fortzuf&uuml;hren, "
						 ."an der Du diese verlassen hast.");
define("_SAVE_EMAILURL", "Rufe die Umfrage auf, indem Du auf folgende URL klickst:");
define("_SAVE_SUCCEEDED", "Deine Antworten auf diese Umfrage sind erfolgreich gespeichert worden.");
define("_SAVE_FAILED", "Ein Fehler ist aufgetreten, daher wurden Deine Antworten f&uuml;r diese Umfrage nicht gespeichert.");
define("_SAVE_EMAILSENT", "Eine Email mit den Details &uuml;ber Deine Umfrage wurde an Dich versandt.");

//Load Messages
define("_LOAD_SAVED", "Zwischengespeicherte Befragung laden");
define("_LOADHEADING", "Laden einer vorher zwischengspeicherten Umfrage");
define("_LOADEXPLANATION", "Von dieser Seite aus kannst Du eine vorher von Dir zwischengespeicherten Fragebogen laden.<br />\n"
			  ."Gib einen Namen und das Passwort ein, unter dem Du vorher den Fragebogen gespeichert hast.<br /><br />\n");
define("_LOADNAME", "Gespeicherter Name");
define("_LOADPASSWORD", "Passwort");
define("_LOADSUBMIT", "Jetzt laden");
define("_LOADNONAME", "Du hast keinen Namen angegeben");
define("_LOADNOPASS", "Du hast kein Passwort angegeben");
define("_LOADNOMATCH", "Es gibt keine entsprechende Unmfrage.");

define("_ASSESSMENT_HEADING", "Deine Bewertung");
?>
