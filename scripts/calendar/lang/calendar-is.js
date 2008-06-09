// ** I18N
Calendar._DN = new Array
("Sunnudagur",
 "Mánudagur",
 "Þriðjudagur",
 "Miðvikudagur",
 "Fimmtudagur",
 "Föstudagur",
 "Laugardagur",
 "Sunnudagur");

Calendar._SDN_len = 2;

Calendar._MN = new Array
("Janúar",
 "Febrúar",
 "Mars",
 "Apríl",
 "Maí",
 "Júni",
 "Júlí",
 "Ágúst",
 "September",
 "Október",
 "Nóvember",
 "Desember");

// First day of the week. "0" means display Sunday first, "1" means display
// Monday first, etc.
Calendar._FD = 0;

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "Upplýsingar";

Calendar._TT["ABOUT"] =
"DHTML Dagsetningar-/Tíma-val\n" +
"(c) dynarch.com 2002-2005 / Höfundur: Mihai Bazon\n" +
"Nýjasta útgáfan fæst hér: http://www.dynarch.com/projects/calendar/\n" +
"Dreift under GNU LGPL.  Sjá nánar á http://gnu.org/licenses/lgpl.html" +
"\n\n" +
"Val á dagsetningu:\n" +
"- Notið \xab \xbb hnappana til að velja ár\n" +
"- Notið " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " hnappana til að velja mánuð\n" +
"- Haldið músahnappinum niðri á hnöppunum til að fá flýtival.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Val á tíma:\n" +
"- Smellið á tímasetninguna til að seinka henni\n" +
"- eða shift-smellið til að flýta henni\n" +
"- eða haldið músahnappinum niðri og dragið til að breyta hraðar.";

Calendar._TT["PREV_YEAR"] = "Fyrra ár (haldið til að fá valmynd)";
Calendar._TT["PREV_MONTH"] = "Fyrri mánuður (haldið til að fá valmynd)";
Calendar._TT["GO_TODAY"] = "Fara að deginum í dag";
Calendar._TT["NEXT_MONTH"] = "Næsti mánuður (haldið til að fá valmynd)";
Calendar._TT["NEXT_YEAR"] = "Næsta ár (haldið til að fá valmynd)";
Calendar._TT["SEL_DATE"] = "Veljið dagsetningu";
Calendar._TT["DRAG_TO_MOVE"] = "Dragið til að færa";
Calendar._TT["PART_TODAY"] = " (í dag)";

Calendar._TT["DAY_FIRST"] = "Sýna %s fyrst";

Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Loka";
Calendar._TT["TODAY"] = "(í dag)";
Calendar._TT["TIME_PART"] = "(shift-)smellið eða dragið til að breyta gildi";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%d-%m-%Y";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %e %b %Y";

Calendar._TT["WK"] = "vika";
Calendar._TT["TIME"] = "Tími:";