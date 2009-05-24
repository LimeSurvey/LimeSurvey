// ** I18N

// Calendar EN language
// Author: Mihai Bazon, <mihai_bazon@yahoo.com>
// Encoding: any
// Distributed under the same terms as the calendar itself.

// For translators: please use UTF-8 if possible.  We strongly believe that
// Unicode is the answer to a real internationalized world.  Also please
// include your contact information in the header, as can be seen above.

// full day names
Calendar._DN = new Array
("रविवार",
 "सोमवार",
 "मंगलवार",
 "बुधवार",
 "गुरुवार",
 "शुक्रवार",
 "शनिवार",
 "रविवार");

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
("रवि",
 "सोम",
 "मंगल",
 "बुध",
 "गुरु",
 "शुक्र",
 "शनि",
 "रवि");

// First day of the week. "0" means display Sunday first, "1" means display
// Monday first, etc.
Calendar._FD = 0;

// full month names
Calendar._MN = new Array
("जनवरी",
 "फ़रवरी",
 "मार्च",
 "अप्रैल",
 "मई",
 "जून",
 "जुलाई",
 "अगस्त",
 "सितंबर",
 "अक्‍तूबर",
 "नवंबर",
 "दिसंबर");

// short month names
Calendar._SMN = new Array
("जनवरी",
 "फ़रवरी",
 "मार्च",
 "अप्रैल",
 "मई",
 "जून",
 "जुलाई",
 "अगस्त",
 "सितंबर",
 "अक्‍तूबर",
 "नवंबर",
 "दिसंबर");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "कैलेण्डर के बारे में";

Calendar._TT["ABOUT"] =
//"DHTML Date/Time Selector\n" +
//"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + // don't translate this this ;-)
//"For latest version visit: http://www.dynarch.com/projects/calendar/\n" +
//"Distributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details." +
//"\n\n" +
"Date selection:\n" +
"- Use the \xab, \xbb buttons to select year\n" +
"- Use the " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " buttons to select month\n" +
"- Hold mouse button on any of the above buttons for faster selection.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Time selection:\n" +
"- Click on any of the time parts to increase it\n" +
"- or Shift-click to decrease it\n" +
"- or click and drag for faster selection.";

Calendar._TT["PREV_YEAR"] = "पिछला साल (hold for menu)";
Calendar._TT["PREV_MONTH"] = "पिछला महीना (hold for menu)";
Calendar._TT["GO_TODAY"] = "आज पर जाये";
Calendar._TT["NEXT_MONTH"] = "अगला महीना (hold for menu)";
Calendar._TT["NEXT_YEAR"] = "अगला साल (hold for menu)";
Calendar._TT["SEL_DATE"] = "तारीख चुने";
Calendar._TT["DRAG_TO_MOVE"] = "Drag to move";
Calendar._TT["PART_TODAY"] = " (आज)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "दिखाये %s पहले";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "बंद करें";
Calendar._TT["TODAY"] = "आज";
Calendar._TT["TIME_PART"] = "(Shift-)Click or drag to change value";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e";

Calendar._TT["WK"] = "सप्ताह";
Calendar._TT["TIME"] = "समय:";
