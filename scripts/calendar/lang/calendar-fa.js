// ** I18N

// Calendar fa locale
// Encoding: utf-8
// Translation: Hooman Mesgary <info@mesgary.com>
// Distributed under the same terms as the calendar itself.

// For translators: please use UTF-8 if possible.  We strongly believe that
// Unicode is the answer to a real internationalized world.  Also please
// include your contact information in the header, as can be seen above.

// full day names
Calendar._DN = new Array
("یکشنبه",
"دوشنبه",
"سه‌شنبه",
"چهارشنبه",
"پنجشنبه",
"جمعه",
"شنبه");

// Please note that the following array of short day names (and the same goes
// for short month names, _SMN) isn't absolutely necessary.  We give it here
// for exemplification on how one can customize the short day names, but if
// they are simply the first N letters of the full name you can simply say:
//
Calendar._SDN_len = 1; // short day name length
//   Calendar._SMN_len = N; // short month name length
//
// If N = 3 then this is not needed either since we assume a value of 3 if not
// present, to be compatible with translation files that were written before
// this feature.

// short day names
Calendar._SDN = new Array
("ی",
"د",
"س",
"چ",
"پ",
"ج",
"ش");

// First day of the week. "0" means display Sunday first, "1" means display
// Monday first, etc.
Calendar._FD = 6;

// full month names
Calendar._MN = new Array
("ژانویه",
"فوریه",
"مارس",
"آوریل",
"می",
"جون",
"جولی",
"آگوست",
"سپتامبر",
"اکتبر",
"نوامبر",
"دسامبر");

// short month names
Calendar._SMN = new Array
("ژانویه",
"فوریه",
"مارس",
"آوریل",
"می",
"جون",
"جولی",
"آگوست",
"سپتامبر",
"اکتبر",
"نوامبر",
"دسامبر");

// tooltips
Calendar._TT_en = Calendar._TT = {};
Calendar._TT["INFO"] = "دربارهٔ تقویم";

Calendar._TT["ABOUT"] =
"DHTML Date/Time Selector\n" +
"(c) dynatech.com 2002-2005\n" + // don't translate this this ;-)
"برای اطلاع از آخرین نسخه به آدرس http://www.dynarch.com/projects/calendar/ مراجعه کنید.\n" +
"توزیع شده تحت مجوز LGPL گنو. برای جزئیات بیشتر به آدرس http://gnu.org/licenses/lgpl.html مراجعه کنید.\n" +
"\n\n" +
"انتخاب تاریخ:\n" +
"- از دکمه‌های \xab و \xbb برای انتخاب سال استفاده کنید.\n" +
"- از دکمه‌های " + String.fromCharCode(0x2039) + " و " + String.fromCharCode(0x203a) + " برای انتخاب ماه استفاده کنید.\n" +
"- برای انتخاب سریع‌تر دکمهٔ ماوس را بر روی موارد فوق نگه دارید.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"انتخاب زمان:\n" +
"- با کلیک بر روی هر قسمتی از زمان می‌توانید آن را افزایش دهید\n" +
"- و یا برای کاهش از کلید مبدل و کلیک کردن استفاده کنید\n" +
"- و برای انتخاب سریع‌تر کلیک کنید و بکشید.";

Calendar._TT["PREV_YEAR"] = "سال قبل (برای مشاهدهٔ سال قبل نگه‌دارید)";
Calendar._TT["PREV_MONTH"] = "ماه قبل (برای مشاهدهٔ سال قبل نگه‌دارید)";
Calendar._TT["GO_TODAY"] = "امروز";
Calendar._TT["NEXT_MONTH"] = "ماه بعد (برای مشاهدهٔ سال قبل نگه‌دارید)";
Calendar._TT["NEXT_YEAR"] = "سال بعد (برای مشاهدهٔ سال قبل نگه‌دارید)";
Calendar._TT["SEL_DATE"] = "انتخاب تاریخ";
Calendar._TT["DRAG_TO_MOVE"] = "برای جابجایی بکشید";
Calendar._TT["PART_TODAY"] = " (امروز)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "ابتدا %s نمایش داده شود";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "4,5";

Calendar._TT["CLOSE"] = "تعطیل";
Calendar._TT["TODAY"] = "امروز";
Calendar._TT["TIME_PART"] = "برای تغییر مقدار کلید مبدل را گرفته و کلیک کنید (و یا بکشید)";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y/%m/%e";
Calendar._TT["TT_DATE_FORMAT"] = "%A, %e %B, %Y";

Calendar._TT["WK"] = "هفته";
Calendar._TT["TIME"] = "زمان:";

Calendar._TT["E_RANGE"] = "خارج از محدوده";
