// ** I18N

// Calendar EU (basque) language
// Author: Mihai Bazon, <mihai_bazon@yahoo.com>
// Updater: Juan Ezeiza Gutiérrez <jezeiza@axular.ikastola.net>
// Updated: 2007-08-22
// Encoding: utf-8
// Distributed under the same terms as the calendar itself.

// For translators: please use UTF-8 if possible.  We strongly believe that
// Unicode is the answer to a real internationalized world.  Also please
// include your contact information in the header, as can be seen above.

// full day names
Calendar._DN = new Array
("Igandea",
 "Astelehena",
 "Asteartea",
 "Asteazkena",
 "Osteguna",
 "Ostirala",
 "Larunbata",
 "Igandea");

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
("Ig",
 "Al",
 "Ar",
 "Az",
 "Og",
 "Or",
 "Lr",
 "Ig");

// First day of the week. "0" means display Sunday first, "1" means display
// Monday first, etc.
Calendar._FD = 1;

// full month names
Calendar._MN = new Array
("Urtarrila",
 "Otsaila",
 "Martxoa",
 "Apirila",
 "Maiatza",
 "Ekaina",
 "Uztaila",
 "Abuztua",
 "Iraila",
 "Urria",
 "Azaroa",
 "Abendua");

// short month names
Calendar._SMN = new Array
("Urt",
 "Ots",
 "Mar",
 "Api",
 "Mai",
 "Eka",
 "Uzt",
 "Abu",
 "Ira",
 "Urr",
 "Aza",
 "Abe");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "Egutegiari buruz";

Calendar._TT["ABOUT"] =
"Data eta ordua akeratzeko DHTMLa\n" +
"(c) dynarch.com 2002-2005 / Egilea: Mihai Bazon\n" + // don't translate this this ;-)
"Azken bertsioa lortzeko: http://www.dynarch.com/projects/calendar/\n" +
"GNU LGPL lizentziapean banatua. Bisitatu http://gnu.org/licenses/lgpl.html para más detalles." +
"\n\n" +
"Data-aukeraketa:\n" +
"- \xab, \xbb erabili urtea\n aukeratzeko" +
"- Botoi hauek: " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " erabili hilabetea aukeratzeko\n" +
"- Botoi hauetako batean sagua sakatu urte edo hileen aukera-zerrenda azkarra erakusteko.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Ordu-aukeraketa:\n" +
"- Orduaren edozein atalean klikatu aurreratzeko\n" +
"- edo maiuskulak sakatu eta klik egin atzeratzeko\n" +
"- edo klik egin eta sagua arrastaka eraman aukera azkarragoa egiteko.";

Calendar._TT["PREV_YEAR"] = "Aurreko urtea (mantendu zerrenda erakusteko)";
Calendar._TT["PREV_MONTH"] = "Aurreko hilea (mantendu zerrenda erakusteko)";
Calendar._TT["GO_TODAY"] = "Gaurko egunera joan";
Calendar._TT["NEXT_MONTH"] = "Hurrengo hilea (mantendu zerrenda erakusteko)";
Calendar._TT["NEXT_YEAR"] = "Hurrengo urtea (mantendu zerrenda erakusteko)";
Calendar._TT["SEL_DATE"] = "Data aukeratu";
Calendar._TT["DRAG_TO_MOVE"] = "Mugitzeko arrastaka eraman";
Calendar._TT["PART_TODAY"] = " (gaur)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "Asteko lehenengo eguna %s izango da";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Itxi";
Calendar._TT["TODAY"] = "Gaur";
Calendar._TT["TIME_PART"] = "(Maiuskula-) klik edo arrastaka balorea aldatzeko";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y/%m/%d";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e";

Calendar._TT["WK"] = "astea";
Calendar._TT["TIME"] = "Ordua:";
