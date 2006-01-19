<?php
/*
    #############################################################
    # >>> PHPSurveyor                                          #
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
    # This language file kindly provided by Frederik Kunz,      #
    # corrections/updates by Ralph Kampmann, Carsten Schmitz    #
    #############################################################
*/

//SINGLE WORDS 
define("_YES", "Ja"); 
define("_NO", "Nein"); 
define("_UNCERTAIN", "Unsicher"); 
define("_ADMIN", "Administrator"); 
define("_TOKENS", "Zugangsschlüssel");
define("_FEMALE", "Weiblich"); 
define("_MALE", "Männlich"); 
define("_NOANSWER", "Keine Antwort"); 
define("_NOTAPPLICABLE", "Nicht zutreffend");
define("_OTHER", "Sonstiges"); 
define("_PLEASECHOOSE", "Bitte wählen"); 
define("_ERROR_PS", "Fehler"); 
define("_COMPLETE", "vollständig");
define("_INCREASE", "Zunahme");
define("_SAME", "Unverändert");
define("_DECREASE", "Abnahme");
define("_REQUIRED", "<font color='red'>*</font>");
//from questions.php
define("_CONFIRMATION", "Bestätigung"); 
define("_TOKEN_PS", "Schlüsselwort"); 
define("_CONTINUE_PS", "Weiter");

//BUTTONS 
define("_ACCEPT", "Annehmen");
define("_PREV", "Zurück"); 
define("_NEXT", "Weiter"); 
define("_LAST", "Abschließen"); 
define("_SUBMIT", "Absenden");


//MESSAGES 
//From QANDA.PHP
define("_CHOOSEONE", "Bitte wählen Sie eine Antwort aus"); 
define("_ENTERCOMMENT", "Bitte geben Sie hier Ihren Kommentar ein"); 
define("_NUMERICAL_PS", "In dieses Feld dürfen nur Ziffern eingetragen werden"); 
define("_CLEARALL", "Ergebnisse verwerfen und Befragung beenden"); 
define("_MANDATORY", "Bitte beantworten Sie diese Frage"); 
define("_MANDATORY_PARTS", "Bitte beenden Sie alle Bereiche/Teile"); 
define("_MANDATORY_CHECK", "Bitte machen Sie mindestens ein Kreuz"); 
define("_MANDATORY_RANK", "Bitte bringen Sie alle Elemente in eine Rangfolge"); 
define("_MANDATORY_POPUP", "Eine oder mehr vorgeschriebene Fragen sind nicht beantwortet worden. Sie können nicht fortfahren, bis diese beantwortet wurden!");
define("_VALIDATION", "Diese Frage muss korrekt beantwortet werden");
define("_VALIDATION_POPUP", "Ein oder mehrere Fragen sind nicht korrekt beantwortet worden. Sie können nicht fortfahren, bevor Sie dies nicht getan haben.");
define("_DATEFORMAT", "Format: JJJJ-MM-TT"); 
define("_DATEFORMATEG", "(z.B.: 2005-12-24 für Heiligabend)");
define("_REMOVEITEM", "Entfernen Sie bitte dieses Element"); 
define("_RANK_1", "Klicken Sie in der Liste links bitte zuerst das am höchsten"); 
define("_RANK_2", "bewertete Element an und machen Sie weiter bis zum niedrigsten."); 
define("_YOURCHOICES", "Ihre Auswahl"); 
define("_YOURRANKING", "Ihre Rangfolge"); 
define("_RANK_3", "Klicken Sie auf die Schere rechts von jedem Element, um das"); 
define("_RANK_4", "zuletzt hinzugefügte Element aus der Liste zu entfernen"); 
//From INDEX.PHP 
define("_NOSID", "Sie haben keine Identifikationsnummer für den Fragebogen angegeben.");
define("_CONTACT1", "Bitte kontaktieren Sie"); 
define("_CONTACT2", "für weitere Unterstützung.");
define("_ANSCLEAR", "Antworten verworfen");
define("_RESTART", "Umfrage neu starten");
define("_CLOSEWIN_PS", "Fenster schließen"); 
define("_CONFIRMCLEAR", "Sind Sie sich sicher, dass Sie alle Antworten verwerfen möchten?");
define("_CONFIRMSAVE", "Sind Sie sicher, dass Sie Ihre Antworten speichern wollen?");
define("_EXITCLEAR", "Umfrage beenden und verwerfen");
//From QUESTION.PHP 
define("_BADSUBMIT1", "Kann keine Ergebnisse abschicken, da keine vorhanden sind.");
define("_BADSUBMIT2", "Dieser Fehler kann auftreten, wenn Sie Ihre Antworten bereits abgeschickt haben und in Ihrem Browser auf 'aktualisieren' geklickt haben. In diesem Fall wurden Ihre Fragen bereits gespeichert."); 
define("_NOTACTIVE1", "Ihre Antworten wurden nicht gespeichert. Diese Befragung ist noch nicht aktiv."); 
define("_CLEARRESP", "Antworten verwerfen"); 
define("_THANKS", "Vielen Dank"); 
define("_SURVEYREC", "Ihre Antworten wurden gespeichert."); 
define("_SURVEYCPL", "Befragung beendet"); 
define("_DIDNOTSAVE", "Wurde nicht gespeichert"); 
define("_DIDNOTSAVE2", "Ein unerwarteter Fehler ist aufgetreten. Ihre Antworten können leider nicht gespeichert werden."); 
define("_DIDNOTSAVE3", "Ihre Antworten sind nicht verloren gegangen. Sie wurden per eMail an den Administrator dieser Befragung geschickt und werden zu einem späteren Zeitpunkt manuell in die Datenbank eingepflegt.");
define("_DNSAVEEMAIL1", "Beim Speichern Ihrer Fragebogen-Zugangsschlüssels ist ein Fehler aufgetreten.");
define("_DNSAVEEMAIL2", "EINZUGEBENDE DATEN"); 
define("_DNSAVEEMAIL3", "FEHLERHAFTE SQL ANWEISUNG");
define("_DNSAVEEMAIL4", "FEHLERMELDUNG"); 
define("_DNSAVEEMAIL5", "FEHLER BEIM SPEICHERN"); 
define("_SUBMITAGAIN", "Versuchen Sie das Abschicken erneut"); 
define("_SURVEYNOEXIST", "Es gibt leider keine passende Befragung."); 
define("_NOTOKEN1", "Um an dieser Befragung teilzunehmen benötigen Sie ein passendes Schlüsselwort."); 
define("_NOTOKEN2", "Falls Sie ein Schlüsselwort erhalten haben, geben Sie es bitte in das untenstehende Feld ein und klicken Sie auf 'Weiter'."); 
define("_NOTOKEN3", "Das von ihnen angegebene Schlüsselwort ist entweder ungültig oder wurde bereits verwendet."); 
define("_NOQUESTIONS", "Für diese Befragung wurden noch keine Fragen angelegt. Sie kann weder getestet noch abgeschlossen werden.");
define("_FURTHERINFO", "Um weitere Informationen zu erhalten, kontaktieren Sie bitte");
define("_NOTACTIVE", "Diese Befragung ist momentan nicht aktiv. Sie werden sie nicht abschließen können.");
define("_SURVEYEXPIRED", "Diese Umfrage ist beendet und kann nicht mehr zur Verfügung.");

define("_SURVEYCOMPLETE", "Sie haben diese Umfrage bereits durchgeführt.");

define("_INSTRUCTION_LIST", "Bitte wählen Sie einen Punkt aus der Liste aus.");
define("_INSTRUCTION_MULTI", "Bitte wählen Sie einen oder mehrere Punkte aus der Liste aus.");

define("_CONFIRMATION_MESSAGE1", "Umfrage wurde ausgefüllt");
define("_CONFIRMATION_MESSAGE2", "Es sind neue Daten in Ihrer Umfrage vorhanden.");
define("_CONFIRMATION_MESSAGE3", "Klicken Sie hier, um die einzelnen Antworten zu sehen:");
define("_CONFIRMATION_MESSAGE4", "Klicken Sie hier, um die Gesamtstatistik zu sehen:");
define("_CONFIRMATION_MESSAGE5", "Klicken Sie auf den folgenden Link um die Antwortdaten direkt zu editieren:"); //NEW for 0.99stable

define("_PRIVACY_MESSAGE", "<strong><i>Schutz Ihrer Privatsphäre</i></strong><br />"
                          ."Diese Umfrage ist anonym.<br />"
                          ."Die Daten, die wir aus dieser Umfrage erhalten, enthalten "
                          ."keinerlei Informationen, mit denen man Sie identifizieren könnte,"
                          ."es sei denn, innerhalb der Umfrage wurde danach gefragt. "
                          ."Wenn Sie eine Umfrage beantwortet haben, zu der Sie einen Zugangsschlüssel "
                          ."benötigten, können wir Ihnen versichern, dass dieser Zugangsschlüssel "
                          ."nicht mit Ihren Antworten in einer Tabelle gehalten wird. "
                          ."Alle Zugangsschlüssel werden in einer separaten Tabelle gespeichert, "
                          ."und werden nur dazu benutzt, um zu sehen, ob Sie diese Umfrage schon ausgefüllt haben. "
                          ."Auf keinen Fall werden die Zugangschlüssel mit Ihren Daten aus dieser Umfrage in Verbindung gebracht werden.");

define("_THEREAREXQUESTIONS", "Diese Umfrage besteht aus {NUMBEROFQUESTIONS} Fragen."); // Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Diese Umfrage besteht aus einer Frage."); // singular version of above

define ("_RG_REGISTER1", "Sie müssen Sich registrieren, um an dieser Umfrage teilnehmen zu können.");
define ("_RG_REGISTER2", "Sie können Sich registrieren, wenn Sie an dieser Umfrage teilnehmen möchten.<br />\n"
                        ."Bitte füllen Sie alle Angaben aus und wir senden Ihnen sofort eine Email,"
                        ."die die nötigen Einzelheiten für eine Teilnahme enthält.");
define ("_RG_EMAIL", "Email-Addresse");
define ("_RG_FIRSTNAME", "Vorname");
define ("_RG_LASTNAME", "Nachname");
define ("_RG_INVALIDEMAIL", "Die angegebene Email-Adresse ist ungültig. Bitte versuchen Sie es erneut.");
define ("_RG_USEDEMAIL", "Die angegeben Email wurde schon einmal benutzt.");
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} Registrierungsbestätigung");
define ("_RG_REGISTRATIONCOMPLETE", "Vielen Dank, dass Sie sich für die Teilnahme an dieser Umfrage angemeldet haben.<br /><br />\n"
                                   ."Es wurde eine Email an die von Ihnen angegebene Adresse versandt, die weitere Details für die Teilnahme "
                                   ."an der Umfrage enthält. Bitte klicken Sie auf den Link innerhalb der Email um fortzufahren.<br />\n"
                                   ."Mit freundlichen Grüßen<br /><br />\n"
                                   ."{ADMINNAME} ({ADMINEMAIL})");
define("_SM_COMPLETED", "<strong>Vielen Dank!<br /><br />"
                       ."Sie haben nun alle Fragen dieser Umfrage beantwortet.</strong><br /><br />"
                       ."Klicken Sie jetzt auf 'Absenden', um diese Umfrage abzuschließen und Ihre Antworten endgültig zu speichern.");
define("_SM_REVIEW", "Wenn Sie Ihre Antworten nochmal überprüfen und/oder ändern wollen, "
                    ."dann klicken Sie bitte auf den Knopf 'Zurück', um durch Ihre Antworten zu blättern.");


//For the "printable" survey
define("_PS_CHOOSEONE", "Bitte <strong>nur eine Antwort</strong> aus folgenden Möglichkeiten wählen"); //New for 0.98finalRC1
define("_PS_WRITE", "Bitte schreiben Sie Ihre Antwort hier"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Bitte <strong>alle</strong> auswählen, die zutreffen"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Bitte alle auswählen die zutreffen und einen Kommentar dazuschreiben"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Bitte wählen Sie die zutreffende Antwort aus"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Bitte Ihre Antwort(en) hierher schreiben"); //New for 0.98finalRC1
define("_PS_DATE", "Bitte ein datum eingeben"); //New for 0.98finalRC1
define("_PS_COMMENT", "Bitte schreiben Sie einen Kommentar zu Ihrer Auswahl"); //New for 0.98finalRC1
define("_PS_RANKING", "Bitte nummerieren Sie jede Box in der Reigenfolge Ihrer Präferenz, beginnen mit 1 bis"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Übermittlung Ihres ausgefüllten Fragebogens:"); //New for 0.98finalRC1
define("_PS_THANKYOU", "Vielen Dank für die Beantwortung des Fragebogens."); //New for 0.98finalRC1
define("_PS_FAXTO", "Bitte faxen Sie den ausgefüllten Fragebogen an"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Bitte beantworten Sie diese Frage nur,"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "falls ihre Antwort "); //New for 0.98finalRC1
define("_PS_CON_JOINER", "und"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", " war bei der Frage"); //New for 0.98finalRC1
define("_PS_CON_OR", "oder"); 

//Save Messages
define("_SAVE_AND_RETURN", "Bisherige Antworten speichern");
define("_SAVEHEADING", "Sichern der nicht abgeschlossenen Umfrage");
define("_RETURNTOSURVEY", "Zur Umfrage zurückkehren");
define("_SAVENAME", "Name");
define("_SAVEPASSWORD", "Passwort");
define("_SAVEPASSWORDRPT", "Passwort wiederholen");
define("_SAVE_EMAIL", "Ihre Emailadresse");
define("_SAVEEXPLANATION", "Geben Sie einen Namen und ein Passwort für diese Umfrage ein und klicken Sie auf Speichern.<br />\n"
				  ."Die Umfrage wird dann unter Ihrem Namen und Passwort gespeichert und kann "
				  ."Durch Sie später fortgesetzt werden, indem Sie erneut Name und Passwort eingeben.<br /><br />\n"
				  ."Wenn Sie eine Emailadresse angeben, wird Ihnen eine Email mit den entsprechenden Details "
				  ."zugesandt.");
define("_SAVESUBMIT", "Jetzt speichern");
define("_SAVENONAME", "Sie müssen einen Namen angeben.");
define("_SAVENOPASS", "Sie müssen ein Passwort angeben.");
define("_SAVENOMATCH", "Die Passwörter stimmen nicht überein.");
define("_SAVEDUPLICATE", "Dieser Name ist leider schon in Benutzung. Bitte benutzen Sie einen anderen Namen.");
define("_SAVETRYAGAIN", "Bitte versuchen Sie es erneut.");
define("_SAVE_EMAILSUBJECT", "Details Ihrer gespeicherten Umfrage");
define("_SAVE_EMAILTEXT", "Sie oder jemand, der Ihre Email-Adresse benutzt hat, hat eine Umfrage "
						 ."zwischengespeichert. Sie können die folgenden Informationen nutzen "
						 ."um zu der Umfrage zuröckzukehren und diese an der Stelle fortzuführen, "
						 ."an der Sie sie verlassen haben.");
define("_SAVE_EMAILURL", "Rufen Sie die Umfrage auf, indem Sie auf folgende URL klicken:");
define("_SAVE_SUCCEEDED", "Ihre Antworten auf diese Umfrage sind erfolgreich gespeichert worden.");
define("_SAVE_FAILED", "Ein Fehler ist aufgetreten, daher wurden Ihre Antworten für diese Umfrage nicht gespeichert.");
define("_SAVE_EMAILSENT", "Eine Email mit den Details über Ihre Umfrage wurde an Sie versandt.");

//Load Messages
define("_LOAD_SAVED", "Zwischengespeicherte Befragung laden");
define("_LOADHEADING", "Laden einer vorher zwischengspeicherten Umfrage");
define("_LOADEXPLANATION", "Von dieser Seite aus können Sie eine vorher von Ihnen zwischengespeicherten Fragebogen laden.<br />\n"
			  ."Geben Sie den Namen und das Passwort ein, unter dem sie vorher den Fragebogen gespeichert haben.<br /><br />\n");
define("_LOADNAME", "Gespeicherter Name");
define("_LOADPASSWORD", "Passwort");
define("_LOADSUBMIT", "Jetzt laden");
define("_LOADNONAME", "Sie haben keinen Namen angegeben");
define("_LOADNOPASS", "Sie haben kein Passwort angegeben");
define("_LOADNOMATCH", "Es gibt keine entsprechende Unmfrage.");

define("_ASSESSMENT_HEADING", "Ihre Bewertung");
?>
