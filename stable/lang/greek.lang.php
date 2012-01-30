<?php
/*
        #############################################################
        # >>> Tranlsation file for PHPSurveyor                      #
        #############################################################
        # > Author:  Jason Cleeland                                 #
        # > E-mail:  jason@cleeland.org                             #
        # > Mail:    Box 99, Trades Hall, 54 Victoria St,           #
        # >          CARLTON SOUTH 3053, AUSTRALIA                  #
        # > Date:    20 February 2003                               #
        # > Last Update: 2007/01/31                                 #
        #                                                           #
        # This set of scripts allows you to develop, publish and    #
        # perform data-entry on surveys.                            #
        #############################################################
        #                                                           #
        #       PHPSurveyor Copyright (C) 2003  Jason Cleeland      #
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
*/



//SINGLE WORDS
define("_YES", "Ναι");
define("_NO", "Όχι");
define("_UNCERTAIN", "Uncertain");
define("_ADMIN", "Διαχειριστής");
define("_TOKENS", "Tokens");
define("_FEMALE", "Γυναίκα");
define("_MALE", "Άνδρας");
define("_NOANSWER", "Καμία απάντηση");
define("_NOTAPPLICABLE", "Μη εφαρμόσιμο"); //New for 0.98rc5
define("_OTHER", "Άλλο");
define("_PLEASECHOOSE", "Παρακαλώ επιλέξτε");
define("_ERROR_PS", "Σφάλμα");
define("_COMPLETE", "πλήρης");
define("_INCREASE", "Αύξησε"); //NEW WITH 0.98
define("_SAME", "Ίδιο"); //NEW WITH 0.98
define("_DECREASE", "Μείωσε"); //NEW WITH 0.98
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Επιβεβαίωση");
define("_TOKEN_PS", "Συμβολικός");
define("_CONTINUE_PS", "Συνέχεια");

//BUTTONS
define("_ACCEPT", "Αποδοχή");
define("_PREV", "Προηγούμενη");
define("_NEXT", "Επόμενη");
define("_LAST", "Τελευταία");
define("_SUBMIT", "Υποβολή");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Παρακαλώ επιλέξτε ένα από τα παρακάτω");
define("_ENTERCOMMENT", "Παρακαλώ καταχωρήστε τα σχόλιά σας εδώ");
define("_NUMERICAL_PS", "Μόνο αριθμητικές τιμές επιτρέπονται σε αυτό το πεδίο");
define("_CLEARALL", "Έξοδος και αρχικοποίηση Έρευνας");
define("_MANDATORY", "Η ερώτηση αυτή είναι υποχρεωτική");
define("_MANDATORY_PARTS", "Παρακαλώ συμπληρώστε όλα τα μέρη");
define("_MANDATORY_CHECK", "Παρακαλώ επιλέξτε τουλάχιστον ένα στοιχείο");
define("_MANDATORY_RANK", "Παρακαλώ ταξινομήστε όλα τα στοιχεία");
define("_MANDATORY_POPUP", "Μια ή περισσότερες υποχρεωτικές ερωτήσεις δεν έχουν απαντηθεί. Δεν μπορείτε να προχωρήσετε μέχρι να απαντηθούν!"); //NEW in 0.98rc4
define("_VALIDATION", "Αυτή η ερώτηση πρέπει να απαντηθεί με σωστό τρόπο."); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "Μια ή περισσότερες ερωτήσεις έχουν απαντηθεί με λάθος τρόπο. Δεν μπορείτε να προχωρήσετε μέχρι οι ερωτήσεις αυτές να απαντηθούν με σωστό τρόπο."); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Μορφή: ΕΕΕΕ-ΜΜ-ΗΗ");
define("_DATEFORMATEG", "(πχ: 2003-12-25 για την ημέρα των Χριστουγέννων)");
define("_REMOVEITEM", "Αφαίρεση αυτού του στοιχείου");
define("_RANK_1", "Επιλέξτε τα αντικείμενα στην λίστα αριστερά, ξεκινώντας από αυτό ");
define("_RANK_2", "με την μεγαλύτερη δική σας βαθμολόγηση/αξιολόγηση  μέχρι να φτάσετε σε αυτό με την χαμηλότερη.");
define("_YOURCHOICES", "Οι επιλογές σας");
define("_YOURRANKING", "Η ταξινόμησή σας");
define("_RANK_3", "Πατήστε στο ψαλίδι στα δεξιά των αντικειμένων");
define("_RANK_4", "για να αφαιρέσετε την τελευταία εισαγωγή στην λίστα αξιολόγησης");
//From INDEX.PHP
define("_NOSID", "Δεν έχει προσδιορισθεί το ID της έρευνας");
define("_CONTACT1", "Παρακαλώ επικοινωνήστε");
define("_CONTACT2", "για περεταίρω βοήθεια");
define("_ANSCLEAR", "Οι απαντήσεις διαγράφησαν");
define("_RESTART", "Επανεκκίνηση της Έρευνας");
define("_CLOSEWIN_PS", "Κλείσιμο Παραθύρου");
define("_CONFIRMCLEAR", "Είστε σίγουροι ότι επιθυμείτε την διαγραφή όλων των απαντήσεών σας?");
define("_CONFIRMSAVE", "Είστε σίγουροι ότι επιθυμείτε την αποθήκευση των απαντήσεών σας?");
define("_EXITCLEAR", "Έξοδος και αρχικοποίηση Έρευνας");
//From QUESTION.PHP
define("_BADSUBMIT1", "Η υποβολή δεν είναι εφικτή - δεν υπάρχει τίποτα να υποβληθεί.");
define("_BADSUBMIT2", "Το σφάλμα αυτό μπορεί να προκύψει εάν έχετε ήδη υποβάλει τις απαντήσεις σας και πατήσατε στο κουμπί «Ανανέωση» του φυλλομετρητή σας. Στην περίπτωση αυτή οι απαντήσεις σας έχουν ήδη αποθηκευτεί.<br /><br />Εάν λάβετε αυτό το μήνυμα κατά τη διάρκεια συμπλήρωσης της Έρευνας, πατήστε το κουμπί «Πίσω» του φυλλομετρητή σας και στη συνέχεια Ανανέωση/Επαναφόρτωση της προηγούμενης σελίδας. Με τον τρόπο αυτό, θα χάσετε τις απαντήσεις της τελευταίας σελίδας αλλά όλες οι άλλες θα συνεχίσουν να υπάρχουν. Το πρόβλημα αυτό μπορεί να προκύψει στην περίπτωση που ο διακομιστής έχει υπερφορτωθεί. Ζητούμε συγνώμη για το πρόβλημα αυτό.");
define("_NOTACTIVE1", "Οι απαντήσεις της Έρευνάς σας δεν καταγράφηκαν. Η παρούσα Έρευνα δεν είναι ενεργή ακόμη.");
define("_CLEARRESP", "Διαγραφή Απαντήσεων");
define("_THANKS", "Σας ευχαριστούμε");
define("_SURVEYREC", "Οι απαντήσεις σας στην έρευνα έχουν καταχωρηθεί.");
define("_SURVEYCPL", "Η έρευνα ολοκληρώθηκε");
define("_DIDNOTSAVE", "Δεν αποθηκεύτηκε");
define("_DIDNOTSAVE2", "Ένα απρόσμενο σφάλμα συνέβη και οι απαντήσεις σας δε μπορούν να αποθηκευτούν.");
define("_DIDNOTSAVE3", "Οι απαντήσεις σας δεν χάθηκαν και εστάλησαν μέσω ηλεκτρονικού ταχυδρομείου στον διαχειριστή της Έρευνας προκειμένου να καταχωρηθούν στην βάση δεδομένων μας στη συνέχεια.");
define("_DNSAVEEMAIL1", "Παρουσιάστηκε σφάλμα κατά την αποθήκευση μιας απάντησης στον κωδικό της Έρευνας");
define("_DNSAVEEMAIL2", "ΔΕΔΟΜΕΝΑ ΠΡΟΣ ΕΙΣΑΓΩΓΗ");
define("_DNSAVEEMAIL3", "ΚΩΔΙΚΑΣ SQL ΠΟΥ ΑΠΕΤΥΧΕ");
define("_DNSAVEEMAIL4", "ΜΗΝΥΜΑ ΣΦΑΛΜΑΤΟΣ");
define("_DNSAVEEMAIL5", "Σφάλμα κατά την καταχώρηση των αποτελεσμάτων της έρευνας στην Βάση Δεδομένων");
define("_SUBMITAGAIN", "Προσπαθήστε την υποβολή ξανά");
define("_SURVEYNOEXIST", "Λυπούμαστε. Δεν υπάρχει Έρευνα που να ταιριάζει.");
define("_NOTOKEN1", "Πρόκειται για ελεγχόμενη Έρευνα στην οποία θα χρειαστείτε έγκυρα στοιχεία για να συμμετέχετε.");
define("_NOTOKEN2", "Στην περίπτωση που δεν σας έχουν αποδοθεί στοιχεία, παρακαλείσθε να τα καταχωρήσετε στο παρακάτω πλαίσιο και να πατήσετε το κουμπί Συνέχεια.");
define("_NOTOKEN3", "Τα στοιχεία που δώσατε είτε είναι μη-έγκυρα είτε έχουν ήδη χρησιμοποιηθεί.");
define("_NOQUESTIONS", "Η Έρευνα αυτή δεν περιέχει ερωτήσεις και επομένως δεν μπορεί να δοκιμαστεί ή να συμπληρωθεί.");
define("_FURTHERINFO", "Για περαιτέρω πληροφορίες επικοινωνήστε μαζί μας.");
define("_NOTACTIVE", "Η Έρευνα αυτή δεν είναι ενεργή. Δεν θα μπορέσετε να αποθηκεύσετε τις απαντήσεις σας.");
define("_SURVEYEXPIRED", "Η Έρευνα αυτή δεν είναι πλέον διαθέσιμη.");

define("_SURVEYCOMPLETE", "Έχετε ήδη συμπληρώσει αυτήν την έρευνα."); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "Επιλέξτε μόνο ένα από τα παρακάτω"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Επιλέξτε όποιο ισχύει"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Έρευνα καταχωρήθηκε"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Μια νέα απάντηση εισήχθη για την έρευνά σας"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Κάντε κλικ στην ακόλουθη σύνδεση για να δείτε τη μεμονωμένη απάντηση:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Κάντε κλικ εδώ για προβολή στατιστικών:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE5", "Κάντε κλικ στην ακόλουθη σύνδεση για να εκδώσετε τη μεμονωμένη απάντηση:"); //NEW for 0.99stable

define("_PRIVACY_MESSAGE", "<strong><i>Μια Παρατήρηση Ιδιωτικότητας</i></strong><br />"
						  ."Η παρούσα Έρευνα είναι ανώνυμη.<br />"
						  ."Η καταγραφή των απαντήσεων σας δεν περιέχει καμία "
						  ."πληροφορία ταυτοποίησης σας εκτός και εάν σας ζητήθηκε από κάποια συγκεκριμένη ερώτηση "
						  ."της Έρευνας. Στην περίπτωση που απαντήσατε σε μια Έρευνα με χρήση διαπιστευτηρίου, για την μετέπειτα "
						  ."πρόσβαση σε αυτή, σας διαβεβαιώνουμε πως το διαπιστευτήριο ταυτοποίησης δεν αποθηκεύεται μαζί με τις απαντήσεις σας. Παραμένει σε ξεχωριστή Βάση Δεδομένων, "
						  ."και θα ενημερώνεται μόνο για την υπόδειξη πως ολοκληρώσατε (ή δεν ολοκληρώσατε) "
						  ."την Έρευνα. Δεν υπάρχει τρόπος αντιστοίχησης "
						  ."των διαπιστευτηρίων με τις απαντήσεις στην Έρευνα αυτή."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "Υπάρχουν {NUMBEROFQUESTIONS} ερωτήσεις σε αυτήν την έρευνα."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Υπάρχει 1 ερώτηση σε αυτήν την έρευνα."); //New for 0.98rc9 - singular version of above
						  
define ("_RG_REGISTER1", "Πρέπει να είστε εγγεγραμμένο μέλος για να συμπληρώσετε την παρούσα Έρευνα."); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Μπορείτε να εγγραφείτε για την Έρευνα αυτή εάν επιθυμείτε να λάβετε μέρος σε αυτή.<br />\n"
						."Καταχωρίστε τα στοιχεία σας παρακάτω και θα σας σταλεί άμεσα "
						."email με τον απαραίτητο σύνδεσμο προκειμένου να συμμετέχετε στην Έρευνα."); //NEW for 0.98rc9

define ("_RG_EMAIL", "Διεύθυνση Email"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Όνομα"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Επίθετο"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "Το e-mail που δώσατε δεν είναι έγκυρο. Παρακαλώ προσπαθήστε ξανά.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "Το e-mail που δώσατε χρησιμοποιείται ήδη.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} Επιβεβαίωση εγγραφής");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Σας ευχαριστούμε για την εγγραφή και συμμετοχή σε αυτήν την έρευνα.<br /><br />\n"
								   ."Ένα e-mail σας έχει σταλεί στην διεύθυνση που δώσατε με περεταίρω πληροφορίες για την πρόσβαση "
								   ."σε αυτήν την έρευνα. Παρακαλώ ακολουθήστε τον σύνδεσμο που θα βρείτε στο e-mail για να προχωρήσετε.<br /><br />\n"
								   ."Διαχειριστής έρευνας {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<strong>Σας Ευχαριστούμε<br /><br />"
					   ."Ολοκληρώσατε τις απαντήσεις σας σε αυτή την Έρευνα.</strong><br /><br />"
					   ."Πατήστε στο ["._SUBMIT."] τώρα για να ολοκληρώσετε την διαδικασία και να αποθηκεύσετε τις απαντήσεις σας.");

define("_SM_REVIEW", "Εάν θέλετε να ελέγξετε τις απαντήσεις σας ή/και να τις αλλάξετε, "
					."μπορείτε να το κάνετε τώρα πατώντας στο κουμπί [<< "._PREV."] και ανατρέχοντας "
					."σε αυτές.");

//For the "printable" survey
define("_PS_CHOOSEONE", "Παρακαλώ επιλέξτε <strong>μόνο ένα</strong> από τα επόμενα:"); //New for 0.98finalRC1
define("_PS_WRITE", "Παρακαλώ γράψτε την απάντηση εδώ:"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Παρακαλώ επιλέξτε <strong>όλα</strong> όσα ισχύουν:"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Παρακαλώ επιλέξτε όλα όσα ισχύουν και αφήστε κάποιο σχόλιο:"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Παρακαλώ επιλέξτε την κατάλληλη απάντηση για κάθε στοιχείο"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Παρακαλώ γράψτε την/τις απάντηση(εις) εδώ:"); //New for 0.98finalRC1
define("_PS_DATE", "Παρακαλώ καταχωρήστε ημερομηνία:"); //New for 0.98finalRC1
define("_PS_COMMENT", "Σχολιάστε την επιλογή σας εδώ:"); //New for 0.98finalRC1
define("_PS_RANKING", "Παρακαλώ αριθμήστε κάθε κελί με σειρά προτίμησης από το 1 μέχρι"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Υποβάλετε τις απαντήσεις σας."); //New for 0.98finalRC1
define("_PS_THANKYOU", "Ευχαριστούμε που συμπληρώσατε αυτήν την έρευνα."); //New for 0.98finalRC1
define("_PS_FAXTO", "Παρακαλώ να αποστείλετε με φαξ την συμπληρωμένη έρευνα στο:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Απαντήστε μόνο αυτήν την ερώτηση"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "Αν απαντήσατε"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "και"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "στην ερώτηση"); //New for 0.98finalRC1
define("_PS_CON_OR", "ή"); //New for 0.98finalRC2

//Save Messages
define("_SAVE_AND_RETURN", "Αποθηκεύστε τις απαντήσεις σας έως τώρα");
define("_SAVEHEADING", "Αποθήκευση της μη-ολοκληρωμένης έρευνας");
define("_RETURNTOSURVEY", "Επιστροφή στην έρευνα");
define("_SAVENAME", "Όνομα");
define("_SAVEPASSWORD", "Κωδικός:");
define("_SAVEPASSWORDRPT", "Επιβεβαίωση κωδικού:");
define("_SAVE_EMAIL", "Το e-mail σας:");
define("_SAVEEXPLANATION", " Δώστε ένα Όνομα και ένα Συνθηματικό για την παρούσα Έρευνα και πατήστε το κουμπί Αποθήκευση που βρίσκεται από κάτω.<br />\n"
				  ."Η Έρευνα αυτή, θα αποθηκευτεί ώστε να μπορείτε να το συμπληρώσετε αργότερα αφού πρώτα συνδεθείτε με το ίδιο όνομα και κωδικό."
				  ."<br>Αν συμπληρώσετε την ηλεκτρονική σας διεύθυνση, θα σας αποσταλεί ένα email με όλες τις σχετικές λεπτομέρειες.");
define("_SAVESUBMIT", "Αποθήκευση τώρα");
define("_SAVENONAME", "Πρέπει να δώσετε ένα όνομα για αυτή την αποθηκευμένη συνεδρία.");
define("_SAVENOPASS", "Πρέπει να δώσετε ένα συνθηματικό για αυτή την αποθηκευμένη συνεδρία.");
define("_SAVENOPASS2", "Πρέπει να ξαναδώσετε ένα συνθηματικό για αυτή την αποθηκευμένη συνεδρία.");
define("_SAVENOMATCH", "Τα συνθηματικά σας δεν ταιριάζουν μεταξύ τους.");
define("_SAVEDUPLICATE", "Το όνομα αυτό έχει ήδη χρησιμοποιηθεί για αυτή την Έρευνα. Πρέπει να χρησιμοποιήσετε ένα μοναδικό όνομα αποθήκευσης.");
define("_SAVETRYAGAIN", "Παρακαλώ δοκιμάστε ξανά.");
define("_SAVE_EMAILSUBJECT", "Λεπτομέρειες Αποθηκευμένης Έρευνας");
define("_SAVE_EMAILTEXT", "Εσείς ή κάποιος που χρησιμοποιεί την ηλεκτρονική σας διεύθυνση έχει αποθηκεύσει "
						 ."μια Έρευνα που βρίσκεται σε εξέλιξη. Μπορείτε να χρησιμοποιήσετε τα παρακάτω στοιχεία "
						 ."για να επιστρέψετε στην Έρευνα αυτή και να συνεχίσετε από εκεί που την είχατε αφήσει");

define("_SAVE_EMAILURL", "Επαναφορτώστε την Έρευνά σας πατώντας στο παρακάτω URL:");
define("_SAVE_SUCCEEDED", "Οι απαντήσεις της Έρευνάς σας αποθηκεύτηκαν με επιτυχία");
define("_SAVE_FAILED", "Παρουσιάστηκε σφάλμα και οι απαντήσεις της Έρευνάς σας δεν αποθηκεύτηκαν.");
define("_SAVE_EMAILSENT", "Σας απεστάλη email με τις πληροφορίες για την αποθηκευμένη Έρευνά σας.");

//Load Messages
define("_LOAD_SAVED", "Φόρτωση Ημιτελής Έρευνας");
define("_LOADHEADING", "Φόρτωση Αποθηκευμένης Έρευνας");
define("_LOADEXPLANATION", "Μπορείτε να φορτώσετε μια Έρευνα που αποθηκεύσατε προηγουμένως από αυτή την οθόνη.<br />\n"
			  ."Πληκτρολογήστε το όνομα και το συνθηματικό που χρησιμοποιήσατε για την αποθήκευση.<br /><br />\n");
define("_LOADNAME", "Αποθηκευμένο όνομα");
define("_LOADPASSWORD", "Κωδικός");
define("_LOADSUBMIT", "Φόρτωσε τώρα");
define("_LOADNONAME", "Δεν παρείχατε όνομα");
define("_LOADNOPASS", "Δεν παρείχατε κωδικό");
define("_LOADNOMATCH", "Δεν υπάρχει αποθηκευμένη Έρευνα που να ταιριάζει");

define("_ASSESSMENT_HEADING", "Η αξιολόγησή σας");
?>
