// ** I18N

// Calendar SR language Serbian (Latin) 
// Author: Mihai Bazon, <mishoo@infoiasi.ro>
// Translation: Nenad Nikolic <shone@europe.com>
// Encoding: UTF-8
// Feel free to use / redistribute under the GNU LGPL.

// full day names
Calendar._DN = new Array
("Nedelja",
 "Ponedeljak",
 "Utorak",
 "Sreda",
 "Četvrtak",
 "Petak",
 "Subota",
 "Nedelja");

// Please note that the following array of short day names (and the same goes
// for short month names, _SMN) isn't absolutely necessary.  We give it here
// for exemplification on how one can customize the short day names, but if
// they are simply the first N letters of the full name you can simply say:
//
//   Calendar._SDN_len = N; // short day name length
//   Calendar._SMN_len = N; // short month name length
//
// If N = 3 then this is not needed either since we assume a value of 3 if not
// present, to be compatible with translation files that were written before
// this feature.

// short day names
Calendar._SDN = new Array
("Ned",
 "Pon",
 "Uto",
 "Sre",
 "Čet",
 "Pet",
 "Sub",
 "Ned");

// First day of the week. "0" means display Sunday first, "1" means display
// Monday first, etc.
Calendar._FD = 0;

// full month names
Calendar._MN = new Array
("Januar",
 "Februar",
 "Mart",
 "April",
 "Maj",
 "Jun",
 "Jul",
 "Avgust",
 "Septembar",
 "Oktobar",
 "Novembar",
 "Decembar");

// short month names
Calendar._SMN = new Array
("Jan",
 "Feb",
 "Mar",
 "Apr",
 "Maj",
 "Jun",
 "Jul",
 "Avg",
 "Sep",
 "Okt",
 "Nov",
 "Dec");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "O kalendaru";

Calendar._TT["ABOUT"] =
"DHTML Kalendar\n" +
"(c) dynarch.com 2002-2003\n" + // don't translate this this ;-)
"Najnovija verzija kontrole nalazi se http://dynarch.com/mishoo/calendar.epl\n" +
"Distribuirano po GNU LGPL licencom.  Za detalje pogledaj http://gnu.org/licenses/lgpl.html." +
"\n\n" +
"Izbor datuma:\n" +
"- Koristi dugmiće \xab, \xbb za izbor godine\n" +
"- Koristi dugmiće " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " za izbor meseca\n" +
"- Za brži izbor, držati pritisnut taster miša iznad bilo kog od pomenutih dugmića";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Izbor vremena:\n" +
"- Kliktaj na sate ili minute povećava njihove vrednosti\n" +
"- Shift-klik smanjuje njihove vrednosti\n" +
"- klikni i vuci za brži izbor.";

Calendar._TT["PREV_YEAR"] = "Prethodna godina (dugi pritisak za meni)";
Calendar._TT["PREV_MONTH"] = "Prethodni mesec (dugi pritisak za meni)";
Calendar._TT["GO_TODAY"] = "Idi na današnji dan";
Calendar._TT["NEXT_MONTH"] = "Sledeći mesec (dugi pritisak za meni)";
Calendar._TT["NEXT_YEAR"] = "Sledeća godina (dugi pritisak za meni)";
Calendar._TT["SEL_DATE"] = "Izaberi datum";
Calendar._TT["DRAG_TO_MOVE"] = "Pritisni i vuci za promenu pozicije";
Calendar._TT["PART_TODAY"] = " (danas)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "%s kao prvi dan u nedelji"; 

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Zatvori";
Calendar._TT["TODAY"] = "Danas";
Calendar._TT["TIME_PART"] = "(Shift-)klikni i vuci za promenu vrednosti";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%d-%m-%Y";
Calendar._TT["TT_DATE_FORMAT"] = "%A, %B %e";

Calendar._TT["WK"] = "wk";
Calendar._TT["TIME"] = "Time:";
