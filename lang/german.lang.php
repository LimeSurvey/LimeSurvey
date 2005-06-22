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
define("_OTHER", "Sonstiges"); 
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
define("_PREV", "Zurück"); 
define("_NEXT", "Weiter"); 
define("_LAST", "Abschließen"); 
define("_SUBMIT", "Absenden");


//MESSAGES 
//From QANDA.PHP
define("_CHOOSEONE", "Bitte w&auml;hlen Sie eine Antwort aus"); 
define("_ENTERCOMMENT", "Bitte geben Sie hier Ihren Kommentar ein"); 
define("_NUMERICAL_PS", "In dieses Feld d&uuml;rfen nur Ziffern eingetragen werden"); 
define("_CLEARALL", "Ergebnisse verwerfen und Befragung beenden"); 
define("_MANDATORY", "Bitte beantworten Sie diese Frage"); 
define("_MANDATORY_PARTS", "Bitte beenden Sie alle Bereiche/Teile"); 
define("_MANDATORY_CHECK", "Bitte machen Sie mindestens ein Kreuzchen"); 
define("_MANDATORY_RANK", "Bitte bringen Sie alle Elemente in eine Rangfolge"); 
define("_MANDATORY_POPUP", "Eine oder mehr vorgeschriebene Fragen sind nicht beantwortet worden. Sie k&ouml;nnen nicht fortfahren, bis diese beantwortet wurden!");
define("_VALIDATION", "Diese Frage muss korrekt beantwortet werden");
define("_VALIDATION_POPUP", "Ein oder mehrere Fragen sind nicht korrekt beantwortet worden. Sie k&ouml;nnen nicht fortfahren, bevor Sie dies nicht getan haben.");
define("_DATEFORMAT", "Format: JJJJ-MM-TT"); 
define("_DATEFORMATEG", "(z.B.: 2005-12-24 f&uuml;r Heiligabend)");
define("_REMOVEITEM", "Entfernen Sie bitte dieses Element"); 
define("_RANK_1", "Klicken Sie in der Liste links bitte zuerst das am h&ouml;chsten"); 
define("_RANK_2", "bewertete Element an und machen Sie weiter bis zum niedrigsten."); 
define("_YOURCHOICES", "Ihre Auswahl"); 
define("_YOURRANKING", "Ihre Rangfolge"); 
define("_RANK_3", "Klicken Sie auf die Schere rechts von jedem Element, um das"); 
define("_RANK_4", "zuletzt hinzugef&uuml;gte Element aus der Liste zu entfernen"); 
//From INDEX.PHP 
define("_NOSID", "Sie haben keine Identifikationsnummer f&uuml;r den Fragebogen angegeben.");
define("_CONTACT1", "Bitte kontaktieren Sie"); 
define("_CONTACT2", "f&uuml;r weitere Unterst&uuml;tzung.");
define("_ANSCLEAR", "Antworten verworfen");
define("_RESTART", "Umfrage neu starten");
define("_CLOSEWIN_PS", "Fenster schließen"); 
define("_CONFIRMCLEAR", "Sind Sie sich sicher, dass Sie alle Antworten verwerfen m&ouml;chten?");
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
define("_DIDNOTSAVE2", "Ein unerwarteter Fehler ist aufgetreten. Ihre Antworten k&ouml;nnen leider nicht gespeichert werden."); 
define("_DIDNOTSAVE3", "Ihre Antworten sind nicht verloren gegangen. Sie wurden per eMail an den Administrator dieser Befragung geschickt und werden zu einem sp&auml;teren Zeitpunkt manuell in die Datenbank eingepflegt.");
define("_DNSAVEEMAIL1", "Beim Speichern Ihrer Fragebogen-Zugangsschl&uuml;ssels ist ein Fehler aufgetreten.");
define("_DNSAVEEMAIL2", "EINZUGEBENDE DATEN"); 
define("_DNSAVEEMAIL3", "FEHLERHAFTE SQL ANWEISUNG");
define("_DNSAVEEMAIL4", "FEHLERMELDUNG"); 
define("_DNSAVEEMAIL5", "FEHLER BEIM SPEICHERN"); 
define("_SUBMITAGAIN", "Versuchen Sie das Abschicken erneut"); 
define("_SURVEYNOEXIST", "Es gibt leider keine passende Befragung."); 
define("_NOTOKEN1", "Um an dieser Befragung teilzunehmen ben&ouml;tigen Sie ein passendes Schl&uuml;sselwort."); 
define("_NOTOKEN2", "Falls Sie ein Schl&uuml;sselwort erhalten haben, geben Sie es bitte in das untenstehende Feld ein und klicken Sie auf 'Weiter'."); 
define("_NOTOKEN3", "Das von ihnen angegebene Schl&uuml;sselwort ist entweder ung&uuml;ltig oder wurde bereits verwendet."); 
define("_NOQUESTIONS", "F&uuml;r diese Befragung wurden noch keine Fragen angelegt. Sie kann weder getestet noch abgeschlossen werden.");
define("_FURTHERINFO", "Um weitere Informationen zu erhalten, kontaktieren Sie bitte");
define("_NOTACTIVE", "Diese Befragung ist momentan nicht aktiv. Sie werden sie nicht abschließen k&ouml;nnen.");
define("_SURVEYEXPIRED", "Diese Umfrage ist beendet und kann nicht mehr zur Verf&uuml;gung.");

define("_SURVEYCOMPLETE", "Sie haben diese Umfrage bereits durchgef&uuml;hrt.");

define("_INSTRUCTION_LIST", "Bitte w&auml;hlen Sie einen Punkt aus der Liste aus.");
define("_INSTRUCTION_MULTI", "Bitte w&auml;hlen Sie einen oder mehrere Punkte aus der Liste aus.");

define("_CONFIRMATION_MESSAGE1", "Ihre Umfragedaten wurden &uuml;bermittelt.");
define("_CONFIRMATION_MESSAGE2", "Es sind neue Umfragedaten vorhanden.");
define("_CONFIRMATION_MESSAGE3", "Klicken Sie hier, um die einzelnen Antworten zu sehen:");
define("_CONFIRMATION_MESSAGE4", "Ansichtstatistiken, bitte hier klicken:");

define("_PRIVACY_MESSAGE", "<strong><i>Schutz Ihrer Privatsph&auml;re</i></strong><br />"
                          ."Diese Umfrage ist anonym.<br />"
                          ."Die Daten, die wir aus dieser Umfrage erhalten, enthalten "
                          ."keinerlei Informationen, mit denen man Sie identifizieren k&ouml;nnte,"
                          ."es sei denn, innerhalb der Umfrage wurde danach gefragt. "
                          ."Wenn Sie eine Umfrage beantwortet haben, zu der Sie einen Zugangsschl&uuml;ssel "
                          ."ben&ouml;tigten, k&ouml;nnen wir Ihnen versichern, dass dieser Zugangsschl&uuml;ssel "
                          ."nicht mit Ihren Antworten in einer Tabelle gehalten wird. "
                          ."Alle Zugangsschl&uuml;ssel werden in einer separaten Tabelle gespeichert, "
                          ."und werden nur dazu benutzt, um zu sehen, ob Sie diese Umfrage schon ausgef&uuml;llt haben. "
                          ."Auf keinen Fall werden die Zugangschl&uuml;ssel mit Ihren Daten aus dieser Umfrage in Verbindung gebracht werden.");

define("_THEREAREXQUESTIONS", "Diese Umfrage besteht aus {NUMBEROFQUESTIONS} Fragen."); // Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Diese Umfrage besteht aus einer Frage."); // singular version of above

define ("_RG_REGISTER1", "Sie m&uuml;ssen Sich registrieren, um an dieser Umfrage teilnehmen zu k&ouml;nnen.");
define ("_RG_REGISTER2", "Sie k&ouml;nnen Sich registrieren, wenn Sie an dieser Umfrage teilnehmen m&ouml;chten.<br />\n"
                        ."Bitte f&uuml;llen Sie alle Angaben aus und wir senden Ihnen sofort eine Email,"
                        ."die die n&ouml;tigen Einzelheiten f&uuml;r eine Teilnahme enth&auml;lt.");
define ("_RG_EMAIL", "Email-Addresse");
define ("_RG_FIRSTNAME", "Vorname");
define ("_RG_LASTNAME", "Nachname");
define ("_RG_INVALIDEMAIL", "Die angegebene Email-Adresse ist ung&uuml;ltig. Bitte versuchen Sie es erneut.");
define ("_RG_USEDEMAIL", "Die angegeben Email wurde schon einmal benutzt.");
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} Registrierungsbest&auml;tigung");
define ("_RG_REGISTRATIONCOMPLETE", "Vielen Dank, dass Sie sich f&uuml;r die Teilnahme an dieser Umfrage angemeldet haben.<br /><br />\n"
                                   ."Es wurde eine Email an die von Ihnen angegebene Adresse versandt, die weitere Details f&uuml;r die Teilnahme "
                                   ."an der Umfrage enth&auml;lt. Bitte klicken Sie auf den Link innerhalb der Email um fortzufahren.<br />\n"
                                   ."Mit freundlichen Gr&uuml;ssen<br /><br />\n"
                                   ."{ADMINNAME} ({ADMINEMAIL})");
define("_SM_COMPLETED", "<strong>Vielen Dank!<br /><br />"
                       ."Sie haben nun alle Fragen dieser Umfrage beantwortet.</strong><br /><br />"
                       ."Klicken Sie jetzt auf 'Absenden', um diese Umfrage abzuschliessen und Ihre Antworten endg&uuml;ltig zu speichern.");
define("_SM_REVIEW", "Wenn Sie Ihre Antworten nochmal &uuml;berpr&uuml;fen und/oder &auml;ndern wollen, "
                    ."dann klicken Sie bitte auf den Knopf 'Zur&uuml;ck', um durch Ihre Antworten zu bl&auml;ttern.");


//For the "printable" survey
define("_PS_CHOOSEONE", "Bitte <strong>nur eine Antwort</strong> aus folgenden M&ouml;glichkeiten w&auml;hlen"); //New for 0.98finalRC1
define("_PS_WRITE", "Bitte schreiben Sie Ihre Antwort hier"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Bitte <strong>alle</strong> ausw&auml;hlen, die zutreffen"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Bitte alle ausw&auml;hlen die zutreffen und einen Kommentar dazuschreiben"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Bitte w&auml;hlen Sie die zutreffende Antwort aus"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Bitte Ihre Antwort(en) hierher schreiben"); //New for 0.98finalRC1
define("_PS_DATE", "Bitte ein datum eingeben"); //New for 0.98finalRC1
define("_PS_COMMENT", "Bitte schreiben Sie einen Kommentar zu Ihrer Auswahl"); //New for 0.98finalRC1
define("_PS_RANKING", "Bitte nummerieren Sie jede Box in der Reigenfolge Ihrer Pr&auml;ferenz, beginnen mit 1 bis"); //New for 0.98finalRC1
define("_PS_SUBMIT", "&Uuml;bermittlung Ihres ausgef&uuml;llten Fragebogens:"); //New for 0.98finalRC1
define("_PS_THANKYOU", "Vielen Dank f&uuml;r die Beantwortung des Fragebogens."); //New for 0.98finalRC1
define("_PS_FAXTO", "Bitte faxen Sie den ausgef&uuml;llten Fragebogen an"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Bitte beantworten Sie diese Frage nur,"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "falls ihre Antwort "); //New for 0.98finalRC1
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
define("_SAVEEXPLANATION", "Geben Sie einen Namen und ein Passwort f&uuml;r diese Umfrage ein und klicken Sie auf Speichern.<br />\n"
				  ."Die Umfrage wird dann unter Ihrem Namen und Passwort gespeichert und kann "
				  ."Durch Sie sp&auml;ter fortgesetzt werden, indem Sie erneut Name und Passwort eingeben.<br /><br />\n"
				  ."Wenn Sie eine Emailadresse angeben, wird Ihnen eine Email mit den entsprechenden Details "
				  ."zugesandt.");
define("_SAVESUBMIT", "Jetzt speichern");
define("_SAVENONAME", "Sie m&uuml;ssen einen Namen angeben.");
define("_SAVENOPASS", "Sie m&uuml;ssen ein Passwort angeben.");
define("_SAVENOMATCH", "Die Passw&ouml;rter stimmen nicht &uuml;berein.");
define("_SAVEDUPLICATE", "Dieser Name ist leider schon in Benutzung. Bitte benutzen Sie einen anderen Namen.");
define("_SAVETRYAGAIN", "Bitte versuchen Sie es erneut.");
define("_SAVE_EMAILSUBJECT", "Details Ihrer gespeicherten Umfrage");
define("_SAVE_EMAILTEXT", "Sie oder jemand, der Ihre Email-Adresse benutzt hat, hat eine Umfrage "
						 ."Zwischengespeichert. Sie k&ouml;nnen die folgenden Informationen nutzen "
						 ."um zu der Umfrage zur&uuml;ckzukehren und diese an der Stelle fortzuf&uuml;hren, "
						 ."an der Sie sie verlassen haben.");
define("_SAVE_EMAILURL", "Rufen Sie die Umfrage auf, indem Sie auf folgende URL klicken:");
define("_SAVE_SUCCEEDED", "Ihre Antworten auf diese Umfrage sind erfolgreich gespeichert worden.");
define("_SAVE_FAILED", "Ein Fehler ist aufgetreten, daher wurden Ihre Antworten f&uuml;r diese Umfrage nicht gespeichert.");
define("_SAVE_EMAILSENT", "Eine Email mit den Details &uuml;ber Ihre Umfrage wurde an Sie versandt.");

//Load Messages
define("_LOAD_SAVED", "Zwischengespeicherte Befragung laden");
define("_LOADHEADING", "Laden einer vorher zwischengspeicherten Umfrage");
define("_LOADEXPLANATION", "Von dieser Seite aus k&ouml;nnen Sie eine vorher von Ihnen zwischengespeicherten Fragebogen laden.<br />\n"
			  ."Geben Sie den Namen und das Passwort ein, unter dem sie vorher den Fragebogen gespeichert haben.<br /><br />\n");
define("_LOADNAME", "Gespeicherter Name");
define("_LOADPASSWORD", "Passwort");
define("_LOADSUBMIT", "Jetzt laden");
define("_LOADNONAME", "Sie haben keinen Namen angegeben");
define("_LOADNOPASS", "Sie haben kein Passwort angegeben");
define("_LOADNOMATCH", "Es gibt keine entsprechende Unmfrage.");

define("_ASSESSMENT_HEADING", "Ihre Bewertung");
?>
