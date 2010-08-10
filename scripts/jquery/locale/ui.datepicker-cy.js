/* Welsh/UK initialisation for the jQuery UI date picker plugin. */
/* Alan Davies */
jQuery(function($){
	$.datepicker.regional['cy'] = {
	closeText: 'Iawn',
	prevText: 'Cynt',
	nextText: 'Nesaf',
	currentText: 'Heddiw',
	monthNames: ['Ionawr','Chwefror','Mawrth','Ebrill','Mai','Mehefin',
	'Gorffennaf','Awst','Medi','Hydref','Tachwedd','Rhagfyr'],
	monthNamesShort: ['Ion', 'Chwe', 'Maw', 'Ebr', 'Mai', 'Meh',
	'Gor', 'Aws', 'Med', 'Hyd', 'Tach', 'Rhag'],
	dayNames: ['Dydd Sul', 'Dydd Llun', 'Dydd Mawrth', 'Dydd Mercher', 'Dydd Iau', 'Dydd Gwener', 'Dydd Sadwrn'],
	dayNamesShort: ['Sul', 'Llun', 'Maw', 'Mer', 'Iau', 'Gwe', 'Sad'],
	dayNamesMin: ['Su','Ll','Ma','Me','Ia','Gw','Sa'],
	weekHeader: 'Wy.',
	dateFormat: 'dd/mm/yy',
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ''};
});