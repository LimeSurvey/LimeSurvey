// ** I18N

// Calendar TR language
// Author: Murad BAYRAM, <muradbayram@gmail.com>
// Encoding: any
// Distributed under the same terms as the calendar itself.

// For translators: please use UTF-8 if possible.  We strongly believe that
// Unicode is the answer to a real internationalized world.  Also please
// include your contact information in the header, as can be seen above.

// full day names
Calendar._DN = new Array
("Pazar",
 "Pazartesi",
 "Salı",
 "Çarşamba",
 "Perşembe",
 "Cuma",
 "Cumartesi",
 "Pazar");

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
("Paz",
 "Pzt",
 "Sal",
 "Çar",
 "Per",
 "Cum",
 "Cmt",
 "Paz");

// First day of the week. "0" means display Sunday first, "1" means display
// Monday first, etc.
Calendar._FD = 1;

// full month names
Calendar._MN = new Array
("Ocak",
 "Şubat",
 "Mart",
 "Nisan",
 "Mayıs",
 "Haziran",
 "Temmuz",
 "Ağustos",
 "Eylül",
 "Ekim",
 "Kasım",
 "Aralık");

// short month names
Calendar._SMN = new Array
("Oca",
 "Şub",
 "Mar",
 "Nis",
 "May",
 "Haz",
 "Tem",
 "Ağu",
 "Eyl",
 "Eki",
 "Kas",
 "Ara");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "Takvim hakkında";

Calendar._TT["ABOUT"] =
"DHTML Tarih/Zaman Seçimi\n" +
"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + // don't translate this this ;-)
"Son versiyon için şu site bakınız: http://www.dynarch.com/projects/calendar/\n" +
"GNU LGPL koruması altında dağıtılmıştır.  Detaylı bilgi için http://gnu.org/licenses/lgpl.html adresine bakınız." +
"\n\n" +
"Tarih Seçimi:\n" +
"- Yıl seçmek için \xab, \xbb düğmelerine basınız\n" +
"- Ay seçmek için " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " düğmelerine basınız\n" +
"- Hızlı seçim için yukarıdaki düğmeler üzerinde farenizi uzun süre basılı tutun.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Zaman Seçici:\n" +
"- Zamanı artırmak için herhangi bir zaman parçasına tıklayınız\n" +
"- veya azaltmak için Shift'e basarak tıklayınız\n" +
"- veya hızlı seçim için tıklayıp sürükleyiniz.";

Calendar._TT["PREV_YEAR"] = "Önceki yıl (menü için basılı tutun)";
Calendar._TT["PREV_MONTH"] = "Önceki ay (menü için basılı tutun)";
Calendar._TT["GO_TODAY"] = "Bugün";
Calendar._TT["NEXT_MONTH"] = "Sonraki ay (menü için basılı tutun)";
Calendar._TT["NEXT_YEAR"] = "Sonraki yıl (menü için basılı tutun)";
Calendar._TT["SEL_DATE"] = "Tarih seçiniz";
Calendar._TT["DRAG_TO_MOVE"] = "Hareket ettirmek için sürükleyiniz";
Calendar._TT["PART_TODAY"] = " (bugün)";

// Aşağıdaki "%s" gününün haftanın ilk günü olduğunu belirtir
// %s gün ismiyle değişecektir.
Calendar._TT["DAY_FIRST"] = "%s gününü ilk gün olarak göster";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Kapat";
Calendar._TT["TODAY"] = "Bugün";
Calendar._TT["TIME_PART"] = "Değeri değiştirmek için(Shift-)ile tıklayın veya sürükleyin";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] = "%e %b %Y, %a";

Calendar._TT["WK"] = "hft";
Calendar._TT["TIME"] = "Zaman:";
