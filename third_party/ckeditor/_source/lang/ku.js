/*
Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
* @fileOverview
*/

/**#@+
   @type String
   @example
*/

/**
 * Contains the dictionary of language entries.
 * @namespace
 */
CKEDITOR.lang['ku'] =
{
	/**
	 * The language reading direction. Possible values are "rtl" for
	 * Right-To-Left languages (like Arabic) and "ltr" for Left-To-Right
	 * languages (like English).
	 * @default 'ltr'
	 */
	dir : 'rtl',

	/*
	 * Screenreader titles. Please note that screenreaders are not always capable
	 * of reading non-English words. So be careful while translating it.
	 */
	editorTitle : 'ده‌سکاریکه‌ری ناونیشان',
	editorHelp : 'کلیکی ALT له‌گه‌ڵ 0 بکه‌ بۆ یارمه‌تی',

	// ARIA descriptions.
	toolbars	: 'تووڵاەرازی دەسکاریکەر',
	editor		: 'سەرنووسەی دەقی بەپیت',

	// Toolbar buttons without dialogs.
	source			: 'سەرچاوە',
	newPage			: 'پەڕەیەکی نوێ',
	save			: 'پاشکەوتکردن',
	preview			: 'پێشبینین',
	cut				: 'بڕین',
	copy			: 'لەبەرگرنتەوه',
	paste			: 'لکاندن',
	print			: 'چاپکردن',
	underline		: 'ژێرهێڵ',
	bold			: 'قەڵەو',
	italic			: 'لار',
	selectAll		: 'نیشانکردنی هەمووی',
	removeFormat	: 'لابردنی داڕشتەکە',
	strike			: 'لێدان',
	subscript		: 'ژێرنووس',
	superscript		: 'سەرنووس',
	horizontalrule	: 'دانانی هێلی ئاسۆیی',
	pagebreak		: 'دانانی پشووی پەڕە بۆ چاپکردن',
	pagebreakAlt		: 'پشووی پەڕە',
	unlink			: 'لابردنی بەستەر',
	undo			: 'پووچکردنەوه',
	redo			: 'هەڵگەڕاندنەوه',

	// Common messages and labels.
	common :
	{
		browseServer	: 'هێنانی ڕاژە',
		url				: 'ناونیشانی بەستەر',
		protocol		: 'پڕۆتۆکۆڵ',
		upload			: 'بارکردن',
		uploadSubmit	: 'ناردنی بۆ ڕاژە',
		image			: 'وێنە',
		flash			: 'فلاش',
		form			: 'داڕشتە',
		checkbox		: 'خانەی نیشانکردن',
		radio			: 'جێگرەوەی دوگمە',
		textField		: 'خانەی دەق',
		textarea		: 'ڕووبەری دەق',
		hiddenField		: 'شاردنەوی خانە',
		button			: 'دوگمە',
		select			: 'هەڵبژاردەی خانە',
		imageButton		: 'دوگمەی وێنە',
		notSet			: '<هیچ دانەدراوە>',
		id				: 'ناسنامە',
		name			: 'ناو',
		langDir			: 'ئاراستەی زمان',
		langDirLtr		: 'چەپ بۆ ڕاست (LTR)',
		langDirRtl		: 'ڕاست بۆ چەپ (RTL)',
		langCode		: 'هێمای زمان',
		longDescr		: 'پێناسەی درێژی بەستەر',
		cssClass		: 'شێوازی چینی په‌ڕە',
		advisoryTitle	: 'ڕاوێژکاری سەردێڕ',
		cssStyle		: 'شێواز',
		ok				: 'باشە',
		cancel			: 'هەڵوەشاندن',
		close			: 'داخستن',
		preview			: 'پێشبینین',
		generalTab		: 'گشتی',
		advancedTab		: 'په‌ره‌سه‌ندوو',
		validateNumberFailed : 'ئەم نرخە ژمارە نیه، تکایە نرخێکی ژمارە بنووسە.',
		confirmNewPage	: 'سەرجەم گۆڕانکاریەکان و پێکهاتەکانی ناوەووە لەدەست دەدەی گەر بێتوو پاشکەوتی نەکەی یەکەم جار، تۆ هەر دڵنیایی لەکردنەوەی پەنجەرەکی نوێ؟',
		confirmCancel	: 'هەندێك هەڵبژاردە گۆڕدراوە. تۆ دڵنیایی له‌داخستنی ئەم دیالۆگە؟',
		options			: 'هەڵبژاردە',
		target			: 'ئامانج',
		targetNew		: 'پەنجەرەیه‌کی نوێ (_blank)',
		targetTop		: 'لووتکەی پەنجەرە (_top)',
		targetSelf		: 'لەهەمان پەنجەرە (_self)',
		targetParent	: 'پەنجەرەی باوان (_parent)',
		langDirLTR		: 'چەپ بۆ ڕاست (LTR)',
		langDirRTL		: 'ڕاست بۆ چەپ (RTL)',
		styles			: 'شێواز',
		cssClasses		: 'شێوازی چینی پەڕە',
		width			: 'پانی',
		height			: 'درێژی',
		align			: 'ڕێککەرەوە',
		alignLeft		: 'چەپ',
		alignRight		: 'ڕاست',
		alignCenter		: 'ناوەڕاست',
		alignTop		: 'سەرەوە',
		alignMiddle		: 'ناوەند',
		alignBottom		: 'ژێرەوە',
		invalidValue	: 'نرخێکی نادرووست.',
		invalidHeight	: 'درێژی دەبێت ژمارە بێت.',
		invalidWidth	: 'پانی دەبێت ژمارە بێت.',
		invalidCssLength	: 'ئەم نرخەی دراوە بۆ خانەی "%1" دەبێت ژمارەکی درووست بێت یان بێ ناونیشانی ئامرازی (px, %, in, cm, mm, em, ex, pt, یان pc).',
		invalidHtmlLength	: 'ئەم نرخەی دراوە بۆ خانەی "%1" دەبێت ژمارەکی درووست بێت یان بێ ناونیشانی ئامرازی HTML (px یان %).',
		invalidInlineStyle	: 'دانه‌ی نرخی شێوازی ناوهێڵ ده‌بێت پێکهاتبێت له‌یه‌ك یان زیاتری داڕشته‌ "ناو : نرخ", جیاکردنه‌وه‌ی به‌فاریزه‌وخاڵ',
		cssLengthTooltip	: 'ژماره‌یه‌ك بنووسه‌ بۆ نرخی piksel یان ئامرازێکی درووستی CSS (px, %, in, cm, mm, em, ex, pt, یان pc).',

		// Put the voice-only part of the label in the span.
		unavailable		: '%1<span class="cke_accessibility">, ئامادە نیە</span>'
	},

	contextmenu :
	{
		options : 'هەڵبژاردەی لیستەی کلیکی دەستی ڕاست'
	},

	// Special char dialog.
	specialChar		:
	{
		toolbar		: 'دانانەی نووسەی تایبەتی',
		title		: 'هەڵبژاردنی نووسەی تایبەتی',
		options : 'هەڵبژاردەی نووسەی تایبەتی'
	},

	// Link dialog.
	link :
	{
		toolbar		: 'دانان/ڕێکخستنی بەستەر',
		other 		: '<هیتر>',
		menu		: 'چاکسازی بەستەر',
		title		: 'بەستەر',
		info		: 'زانیاری بەستەر',
		target		: 'ئامانج',
		upload		: 'بارکردن',
		advanced	: 'پێشکه‌وتوو',
		type		: 'جۆری به‌سته‌ر',
		toUrl		: 'ناونیشانی به‌سته‌ر',
		toAnchor	: 'به‌سته‌ر بۆ له‌نگه‌ر له‌ ده‌ق',
		toEmail		: 'ئیمه‌یل',
		targetFrame		: '<چووارچێوه>',
		targetPopup		: '<په‌نجه‌ره‌ی سه‌رهه‌ڵده‌ر>',
		targetFrameName	: 'ناوی ئامانجی چووارچێوه',
		targetPopupName	: 'ناوی په‌نجه‌ره‌ی سه‌رهه‌ڵده‌ر',
		popupFeatures	: 'خاسیه‌تی په‌نجه‌ره‌ی سه‌رهه‌ڵده‌ر',
		popupResizable	: 'توانای گۆڕینی قه‌باره‌',
		popupStatusBar	: 'هێڵی دۆخ',
		popupLocationBar: 'هێڵی ناونیشانی به‌سته‌ر',
		popupToolbar	: 'هێڵی تووڵامراز',
		popupMenuBar	: 'هێڵی لیسته',
		popupFullScreen	: 'پڕ به‌پڕی شاشه‌ (IE)',
		popupScrollBars	: 'هێڵی هاتووچۆپێکردن',
		popupDependent	: 'پێوه‌به‌ستراو (Netscape)',
		popupLeft		: 'جێگای چه‌پ',
		popupTop		: 'جێگای سه‌ره‌وه‌',
		id				: 'ناسنامه',
		langDir			: 'ئاراسته‌ی زمان',
		langDirLTR		: 'چه‌پ بۆ ڕاست (LTR)',
		langDirRTL		: 'ڕاست بۆ چه‌پ (RTL)',
		acccessKey		: 'کلیلی ده‌ستپێگه‌یشتن',
		name			: 'ناو',
		langCode			: 'هێمای زمان',
		tabIndex			: 'بازده‌ری تابی  ئیندێکس',
		advisoryTitle		: 'ڕاوێژکاری سه‌ردێڕ',
		advisoryContentType	: 'جۆری ناوه‌ڕۆکی ڕاویژکار',
		cssClasses		: 'شێوازی چینی په‌ڕه‌',
		charset			: 'بەستەری سەرچاوەی نووسه',
		styles			: 'شێواز',
		rel			: 'په‌یوه‌ندی (rel)',
		selectAnchor		: 'هه‌ڵبژاردنی له‌نگه‌رێك',
		anchorName		: 'به‌پێی ناوی له‌نگه‌ر',
		anchorId			: 'به‌پێی ناسنامه‌ی توخم',
		emailAddress		: 'ناونیشانی ئیمه‌یل',
		emailSubject		: 'بابه‌تی نامه',
		emailBody		: 'ناوه‌ڕۆکی نامه',
		noAnchors		: '(هیچ جۆرێکی له‌نگه‌ر ئاماده‌ نیه له‌م په‌ڕه‌یه)',
		noUrl			: 'تکایه‌ ناونیشانی به‌سته‌ر بنووسه',
		noEmail			: 'تکایه‌ ناونیشانی ئیمه‌یل بنووسه'
	},

	// Anchor dialog
	anchor :
	{
		toolbar		: 'دانان/چاکسازی له‌نگه‌ر',
		menu		: 'چاکسازی له‌نگه‌ر',
		title		: 'خاسیه‌تی له‌نگه‌ر',
		name		: 'ناوی له‌نگه‌ر',
		errorName	: 'تکایه‌ ناوی له‌نگه‌ر بنووسه',
		remove		: 'لابردنی له‌نگه‌ر'
	},

	// List style dialog
	list:
	{
		numberedTitle		: 'خاسیه‌تی لیستی ژماره‌یی',
		bulletedTitle		: 'خاسیه‌تی لیستی خاڵی',
		type				: 'جۆر',
		start				: 'ده‌ستپێکردن',
		validateStartNumber				:'ده‌ستپێکه‌ری لیستی ژماره‌یی ده‌بێت ته‌نها ژماره‌ بێت.',
		circle				: 'بازنه',
		disc				: 'په‌پکه',
		square				: 'چووراگۆشه',
		none				: 'هیچ',
		notset				: '<دانه‌ندراوه>',
		armenian			: 'ئاراسته‌ی ژماره‌ی ئه‌رمه‌نی',
		georgian			: 'ئاراسته‌ی ژماره‌ی جۆڕجی (an, ban, gan, وه‌هیتر.)',
		lowerRoman			: 'ژماره‌ی ڕۆمی بچووك (i, ii, iii, iv, v, وه‌هیتر.)',
		upperRoman			: 'ژماره‌ی ڕۆمی گه‌وره (I, II, III, IV, V, وه‌هیتر.)',
		lowerAlpha			: 'ئه‌لفابێی بچووك (a, b, c, d, e, وه‌هیتر.)',
		upperAlpha			: 'ئه‌لفابێی گه‌وره‌ (A, B, C, D, E, وه‌هیتر.)',
		lowerGreek			: 'یۆنانی بچووك (alpha, beta, gamma, وه‌هیتر.)',
		decimal				: 'ژماره (1, 2, 3, وه‌هیتر.)',
		decimalLeadingZero	: 'ژماره‌ سفڕی له‌پێشه‌وه (01, 02, 03, وه‌هیتر.)'
	},

	// Find And Replace Dialog
	findAndReplace :
	{
		title				: 'گه‌ڕان وه‌ له‌بریدانان',
		find				: 'گه‌ڕان',
		replace				: 'له‌بریدانان',
		findWhat			: 'گه‌ڕان به‌دووای:',
		replaceWith			: 'له‌بریدانان به‌:',
		notFoundMsg			: 'هیچ ده‌قه‌ گه‌ڕانێك نه‌دۆزراوه.',
		findOptions			: 'هه‌ڵبژارده‌کانی گه‌ڕان',
		matchCase			: 'جیاکردنه‌وه‌ له‌نێوان پیتی گه‌وره‌و بچووك',
		matchWord			: 'ته‌نها هه‌موو وشه‌که‌',
		matchCyclic			: 'گه‌ڕان له‌هه‌موو په‌ڕه‌که',
		replaceAll			: 'له‌بریدانانی هه‌مووی',
		replaceSuccessMsg	: ' پێشهاته(ی) له‌بری دانرا. %1'
	},

	// Table Dialog
	table :
	{
		toolbar		: 'خشته',
		title		: 'خاسیه‌تی خشته',
		menu		: 'خاسیه‌تی خشته',
		deleteTable	: 'سڕینه‌وه‌ی خشته',
		rows		: 'ڕیز',
		columns		: 'ستوونه‌کان',
		border		: 'گه‌وره‌یی په‌راوێز',
		widthPx		: 'وێنه‌خاڵ - پیکسل',
		widthPc		: 'له‌سه‌دا',
		widthUnit	: 'پانی یه‌که‌',
		cellSpace	: 'بۆشایی خانه',
		cellPad		: 'بۆشایی ناوپۆش',
		caption		: 'سه‌ردێڕ',
		summary		: 'کورته',
		headers		: 'سه‌رپه‌ڕه‌',
		headersNone		: 'هیچ',
		headersColumn	: 'یه‌که‌م ئه‌ستوون',
		headersRow		: 'یه‌که‌م ڕیز',
		headersBoth		: 'هه‌ردووك',
		invalidRows		: 'ژماره‌ی ڕیز ده‌بێت گه‌وره‌تر بێت له‌ژماره‌ی 0.',
		invalidCols		: 'ژماره‌ی ئه‌ستوونی ده‌بێت گه‌وره‌تر بێت له‌ژماره‌ی 0.',
		invalidBorder	: 'ژماره‌ی په‌راوێز ده‌بێت ته‌نها ژماره‌ بێت.',
		invalidWidth	: 'پانی خشته‌ ده‌بێت ته‌نها ژماره‌ بێت.',
		invalidHeight	: 'درێژی خشته ده‌بێت ته‌نها ژماره‌ بێت.',
		invalidCellSpacing	: 'بۆشایی خانه‌ ده‌بێت ژماره‌کی درووست بێت.',
		invalidCellPadding	: 'ناوپۆشی خانه‌ ده‌بێت ژماره‌کی درووست بێت.',

		cell :
		{
			menu			: 'خانه',
			insertBefore	: 'دانانی خانه‌ له‌پێش',
			insertAfter		: 'دانانی خانه له‌پاش',
			deleteCell		: 'سڕینه‌وه‌ی خانه',
			merge			: 'تێکه‌ڵکردنی خانه',
			mergeRight		: 'تێکه‌ڵکردنی له‌گه‌ڵ ڕاست',
			mergeDown		: 'تێکه‌ڵکردنی له‌گه‌ڵ خواره‌وه',
			splitHorizontal	: 'دابه‌شکردنی خانه‌ی ئاسۆیی',
			splitVertical	: 'دابه‌شکردنی خانه‌ی ئه‌ستونی',
			title			: 'خاسیه‌تی خانه',
			cellType		: 'جۆری خانه',
			rowSpan			: 'ماوه‌ی نێوان ڕیز',
			colSpan			: 'بستی ئه‌ستونی',
			wordWrap		: 'پێچانه‌وه‌ی وشه',
			hAlign			: 'ڕیزکردنی ئاسۆیی',
			vAlign			: 'ڕیزکردنی ئه‌ستونی',
			alignBaseline	: 'هێڵه‌بنه‌ڕه‌ت',
			bgColor			: 'ڕه‌نگی پاشبنه‌ما',
			borderColor		: 'ڕه‌نگی په‌راوێز',
			data			: 'داتا',
			header			: 'سه‌رپه‌ڕه‌',
			yes				: 'به‌ڵێ',
			no				: 'نه‌خێر',
			invalidWidth	: 'پانی خانه‌ ده‌بێت به‌ته‌واوی ژماره‌ بێت.',
			invalidHeight	: 'درێژی خانه‌ به‌ته‌واوی ده‌بێت ژماره‌ بێت.',
			invalidRowSpan	: 'ماوه‌ی نێوان ڕیز به‌ته‌واوی ده‌بێت ژماره‌ بێت.',
			invalidColSpan	: 'ماوه‌ی نێوان ئه‌ستونی به‌ته‌واوی ده‌بێت ژماره‌ بێت.',
			chooseColor		: 'هه‌ڵبژاردن'
		},

		row :
		{
			menu			: 'ڕیز',
			insertBefore	: 'دانانی ڕیز له‌پێش',
			insertAfter		: 'دانانی ڕیز له‌پاش',
			deleteRow		: 'سڕینه‌وه‌ی ڕیز'
		},

		column :
		{
			menu			: 'ئه‌ستون',
			insertBefore	: 'دانانی ئه‌ستون له‌پێش',
			insertAfter		: 'دانانی ئه‌ستوون له‌پاش',
			deleteColumn	: 'سڕینه‌وه‌ی ئه‌ستوون'
		}
	},

	// Button Dialog.
	button :
	{
		title		: 'خاسیه‌تی دوگمه',
		text		: '(نرخی) ده‌ق',
		type		: 'جۆر',
		typeBtn		: 'دوگمه‌',
		typeSbm		: 'ناردن',
		typeRst		: 'ڕێکخستنه‌وه'
	},

	// Checkbox and Radio Button Dialogs.
	checkboxAndRadio :
	{
		checkboxTitle : 'خاسیه‌تی چووارگۆشی پشکنین',
		radioTitle	: 'خاسیه‌تی جێگره‌وه‌ی دوگمه',
		value		: 'نرخ',
		selected	: 'هه‌ڵبژاردرا'
	},

	// Form Dialog.
	form :
	{
		title		: 'خاسیه‌تی داڕشته',
		menu		: 'خاسیه‌تی داڕشته',
		action		: 'کردار',
		method		: 'ڕێگه',
		encoding	: 'به‌کۆدکه‌ر'
	},

	// Select Field Dialog.
	select :
	{
		title		: 'هه‌ڵبژارده‌ی خاسیه‌تی خانه',
		selectInfo	: 'زانیاری',
		opAvail		: 'هه‌ڵبژارده‌ی هه‌بوو',
		value		: 'نرخ',
		size		: 'گه‌وره‌یی',
		lines		: 'هێڵه‌کان',
		chkMulti	: 'ڕێدان به‌فره‌ هه‌ڵبژارده',
		opText		: 'ده‌ق',
		opValue		: 'نرخ',
		btnAdd		: 'زیادکردن',
		btnModify	: 'گۆڕانکاری',
		btnUp		: 'سه‌ره‌وه',
		btnDown		: 'خواره‌وه',
		btnSetValue : 'دابنێ وه‌ك نرخێکی هه‌ڵبژێردراو',
		btnDelete	: 'سڕینه‌وه'
	},

	// Textarea Dialog.
	textarea :
	{
		title		: 'خاسیه‌تی ڕووبه‌ری ده‌ق',
		cols		: 'ئه‌ستونیه‌کان',
		rows		: 'ڕیزه‌کان'
	},

	// Text Field Dialog.
	textfield :
	{
		title		: 'خاسیه‌تی خانه‌ی ده‌ق',
		name		: 'ناو',
		value		: 'نرخ',
		charWidth	: 'پانی نووسه',
		maxChars	: 'ئه‌وپه‌ڕی نووسه',
		type		: 'جۆر',
		typeText	: 'ده‌ق',
		typePass	: 'پێپه‌ڕه‌وشه'
	},

	// Hidden Field Dialog.
	hidden :
	{
		title	: 'خاسیه‌تی خانه‌ی شاردراوه',
		name	: 'ناو',
		value	: 'نرخ'
	},

	// Image Dialog.
	image :
	{
		title		: 'خاسیه‌تی وێنه',
		titleButton	: 'خاسیه‌تی دوگمه‌ی وێنه',
		menu		: 'خاسیه‌تی وێنه',
		infoTab		: 'زانیاری وێنه',
		btnUpload	: 'ناردنی بۆ ڕاژه',
		upload		: 'بارکردن',
		alt			: 'جێگره‌وه‌ی ده‌ق',
		lockRatio	: 'داخستنی ڕێژه',
		resetSize	: 'ڕێکخستنه‌وه‌ی قه‌باره',
		border		: 'په‌راوێز',
		hSpace		: 'بۆشایی ئاسۆیی',
		vSpace		: 'بۆشایی ئه‌ستونی',
		alertUrl	: 'تکایه‌ ناونیشانی به‌سته‌ری وێنه‌ بنووسه',
		linkTab		: 'به‌سته‌ر',
		button2Img	: 'تۆ ده‌ته‌وێت دوگمه‌ی وێنه‌ی دیاریکراو بگۆڕیت بۆ وێنه‌کی ئاسایی؟',
		img2Button	: 'تۆ ده‌ته‌وێت وێنه‌ی دیاریکراو بگۆڕیت بۆ دوگمه‌ی وێنه؟',
		urlMissing	: 'سه‌رچاوه‌ی به‌سته‌ری وێنه‌ بزره',
		validateBorder	: 'په‌راوێز ده‌بێت به‌ته‌واوی ته‌نها ژماره‌ بێت.',
		validateHSpace	: 'بۆشایی ئاسۆیی ده‌بێت به‌ته‌واوی ته‌نها ژماره‌ بێت.',
		validateVSpace	: 'بۆشایی ئه‌ستونی ده‌بێت به‌ته‌واوی ته‌نها ژماره‌ بێت.'
	},

	// Flash Dialog
	flash :
	{
		properties		: 'خاسیه‌تی فلاش',
		propertiesTab	: 'خاسیه‌ت',
		title			: 'خاسیه‌تی فلاش',
		chkPlay			: 'پێکردنی یان لێدانی خۆکار',
		chkLoop			: 'گرێ',
		chkMenu			: 'چالاککردنی لیسته‌ی فلاش',
		chkFull			: 'ڕێپێدان به‌ پڕ به‌پڕی شاشه',
 		scale			: 'پێوانه',
		scaleAll		: 'نیشاندانی هه‌موو',
		scaleNoBorder	: 'بێ په‌راوێز',
		scaleFit		: 'به‌وردی بگونجێت',
		access			: 'ده‌ستپێگه‌یشتنی نووسراو',
		accessAlways	: 'هه‌میشه',
		accessSameDomain: 'هه‌مان دۆمه‌ین',
		accessNever		: 'هه‌رگیز',
		alignAbsBottom	: 'له‌ ژێره‌وه',
		alignAbsMiddle	: 'له‌ناوه‌ند',
		alignBaseline	: 'هێڵەبنەڕەت',
		alignTextTop	: 'ده‌ق له‌سه‌ره‌وه',
		quality			: 'جۆرایه‌تی',
		qualityBest		: 'باشترین',
		qualityHigh		: 'به‌رزی',
		qualityAutoHigh	: 'به‌رزی خۆکار',
		qualityMedium	: 'مامناوه‌ند',
		qualityAutoLow	: 'نزمی خۆکار',
		qualityLow		: 'نزم',
		windowModeWindow: 'په‌نجه‌ره',
		windowModeOpaque: 'ناڕوون',
		windowModeTransparent : 'ڕۆشن',
		windowMode		: 'شێوازی په‌نجه‌ره',
		flashvars		: 'گۆڕاوه‌کان بۆ فلاش',
		bgcolor			: 'ڕه‌نگی پاشبنه‌ما',
		hSpace			: 'بۆشایی ئاسۆیی',
		vSpace			: 'بۆشایی ئه‌ستونی',
		validateSrc		: 'ناونیشانی به‌سته‌ر نابێت خاڵی بێت',
		validateHSpace	: 'بۆشایی ئاسۆیی ده‌بێت ژماره‌ بێت.',
		validateVSpace	: 'بۆشایی ئه‌ستونی ده‌بێت ژماره‌ بێت.'
	},

	// Speller Pages Dialog
	spellCheck :
	{
		toolbar			: 'پشکنینی ڕێنووس',
		title			: 'پشکنینی ڕێنووس',
		notAvailable	: 'ببووره‌، له‌مکاته‌دا ڕاژه‌که له‌به‌رده‌ستا نیه.',
		errorLoading	: 'هه‌ڵه‌ له‌هێنانی داخوازینامه‌ی خانه‌خۆێی ڕاژه: %s.',
		notInDic		: 'له‌فه‌رهه‌نگ دانیه',
		changeTo		: 'گۆڕینی بۆ',
		btnIgnore		: 'پشتگوێ کردن',
		btnIgnoreAll	: 'پشتگوێکردنی هه‌مووی',
		btnReplace		: 'له‌بریدانن',
		btnReplaceAll	: 'له‌بریدانانی هه‌مووی',
		btnUndo			: 'پووچکردنه‌وه',
		noSuggestions	: '- هیچ پێشنیارێك -',
		progress		: 'پشکنینی ڕێنووس له‌به‌رده‌وامبوون دایه...',
		noMispell		: 'پشکنینی ڕێنووس کۆتای هات: هیچ هه‌ڵه‌یه‌کی ڕێنووس نه‌دۆزراوه',
		noChanges		: 'پشکنینی ڕێنووس کۆتای هات: هیچ وشه‌یه‌ك نۆگۆڕدرا',
		oneChange		: 'پشکنینی ڕێنووس کۆتای هات: یه‌ك وشه گۆڕدرا',
		manyChanges		: 'پشکنینی ڕێنووس کۆتای هات: له‌سه‌دا %1 ی وشه‌کان گۆڕدرا',
		ieSpellDownload	: 'پشکنینی ڕێنووس دانه‌مزراوه. ده‌ته‌وێت ئێستا دایبگریت?'
	},

	smiley :
	{
		toolbar	: 'زه‌رده‌خه‌نه',
		title	: 'دانانی زه‌رده‌خه‌نه‌یه‌ك',
		options : 'هه‌ڵبژارده‌ی زه‌رده‌خه‌نه'
	},

	elementsPath :
	{
		eleLabel : 'ڕێڕه‌وی توخمه‌کان',
		eleTitle : '%1 توخم'
	},

	numberedlist	: 'دانان/لابردنی ژمارەی لیست',
	bulletedlist	: 'دانان/لابردنی خاڵی لیست',
	indent			: 'زیادکردنی بۆشایی',
	outdent			: 'کەمکردنەوەی بۆشایی',

	justify :
	{
		left	: 'به‌هێڵ کردنی چه‌پ',
		center	: 'ناوه‌ڕاست',
		right	: 'به‌هێڵ کردنی ڕاست',
		block	: 'هاوستوونی'
	},

	blockquote : 'بەربەستکردنی وتەی وەرگیراو',

	clipboard :
	{
		title		: 'لکاندن',
		cutError	: 'پارێزی وێبگەڕەکەت ڕێگه‌نادات بە سەرنووسەکە له‌بڕینی خۆکار. تکایە لەبری ئەمە ئەم فەرمانە بەکاربهێنە بەداگرتنی کلیلی (Ctrl/Cmd+X).',
		copyError	: 'پارێزی وێبگەڕەکەت ڕێگه‌نادات بەسەرنووسەکە لە لکاندنی دەقی خۆکار. تکایە لەبری ئەمە ئەم فەرمانە بەکاربهێنە بەداگرتنی کلیلی (Ctrl/Cmd+C).',
		pasteMsg	: 'تکایه‌ بیلکێنه‌ له‌ناوه‌وه‌ی ئه‌م سنوقه له‌ڕێی ته‌خته‌کلیله‌که‌ت به‌باکارهێنانی کلیلی (<STRONG>Ctrl/Cmd+V</STRONG>) دووای کلیکی باشه‌ بکه.',
		securityMsg	: 'به‌هۆی شێوه‌پێدانی پارێزی وێبگه‌ڕه‌که‌ت، سه‌رنووسه‌که‌ ناتوانێت ده‌ستبگه‌یه‌نێت به‌هه‌ڵگیراوه‌که ڕاسته‌وخۆ. بۆیه‌ پێویسته دووباره‌ بیلکێنیت له‌م په‌نجه‌ره‌یه‌.',
		pasteArea	: 'ناوچه‌ی لکاندن'
	},

	pastefromword :
	{
		confirmCleanup	: 'ئه‌م ده‌قه‌ی به‌ته‌مای بیلکێنی پێده‌چێت له‌ word هێنرابێت. ده‌ته‌وێت پاکی بکه‌یوه‌ پێش ئه‌وه‌ی بیلکێنی؟',
		toolbar			: 'لکاندنی له‌ڕێی Word',
		title			: 'لکاندنی له‌لایه‌ن Word',
		error			: 'هیچ ڕێگه‌یه‌ك نه‌بوو له‌لکاندنی ده‌قه‌که‌ به‌هۆی هه‌ڵه‌کی ناوه‌خۆیی'
	},

	pasteText :
	{
		button	: 'لکاندنی وه‌ك ده‌قی ڕوون',
		title	: 'لکاندنی وه‌ك ده‌قی ڕوون'
	},

	templates :
	{
		button			: 'ڕووکار',
		title			: 'پێکهاته‌ی ڕووکار',
		options : 'هه‌ڵبژارده‌کانی ڕووکار',
		insertOption	: 'له‌شوێن دانانی ئه‌م پێکهاتانه‌ی ئێستا',
		selectPromptMsg	: 'ڕووکارێك هه‌ڵبژێره‌ بۆ کردنه‌وه‌ی له‌ سه‌رنووسه‌ر:',
		emptyListMsg	: '(هیچ ڕووکارێك دیارینه‌کراوه)'
	},

	showBlocks : 'نیشاندانی بەربەستەکان',

	stylesCombo :
	{
		label		: 'شێواز',
		panelTitle	: 'شێوازی ڕازاندنه‌وه',
		panelTitle1	: 'شێوازی خشت',
		panelTitle2	: 'شێوازی ناوهێڵ',
		panelTitle3	: 'شێوازی به‌رکار'
	},

	format :
	{
		label		: 'ڕازاندنه‌وه',
		panelTitle	: 'به‌شی ڕازاندنه‌وه‌',

		tag_p		: 'ئاسایی',
		tag_pre		: 'شێوازکراو',
		tag_address	: 'ناونیشان',
		tag_h1		: 'سه‌رنووسه‌ی ١',
		tag_h2		: 'سه‌رنووسه‌ی ٢',
		tag_h3		: 'سه‌رنووسه‌ی ٣',
		tag_h4		: 'سه‌رنووسه‌ی ٤',
		tag_h5		: 'سه‌رنووسه‌ی ٥',
		tag_h6		: 'سه‌رنووسه‌ی ٦',
		tag_div		: '(DIV)-ی ئاسایی'
	},

	div :
	{
		title				: 'دانانی له‌خۆگری Div',
		toolbar				: 'دانانی له‌خۆگری Div',
		cssClassInputLabel	: 'شێوازی چینی په‌ڕه',
		styleSelectLabel	: 'شێواز',
		IdInputLabel		: 'ناسنامه',
		languageCodeInputLabel	: 'هێمای زمان',
		inlineStyleInputLabel	: 'شێوازی ناوهێڵ',
		advisoryTitleInputLabel	: 'سه‌ردێڕ',
		langDirLabel		: 'ئاراسته‌ی زمان',
		langDirLTRLabel		: 'چه‌پ بۆ ڕاست (LTR)',
		langDirRTLLabel		: 'ڕاست بۆ چه‌پ (RTL)',
		edit				: 'چاکسازی Div',
		remove				: 'لابردنی Div'
  	},

	iframe :
	{
		title		: 'دیالۆگی چووارچێوه',
		toolbar		: 'چووارچێوه',
		noUrl		: 'تکایه‌ ناونیشانی به‌سته‌ر بنووسه‌ بۆ چووارچێوه‌',
		scrolling	: 'چالاککردنی هاتووچۆپێکردن',
		border		: 'نیشاندانی لاکێشه‌ به‌چووارده‌وری چووارچێوه'
	},

	font :
	{
		label		: 'فۆنت',
		voiceLabel	: 'فۆنت',
		panelTitle	: 'ناوی فۆنت'
	},

	fontSize :
	{
		label		: 'گه‌وره‌یی',
		voiceLabel	: 'گه‌وره‌یی فۆنت',
		panelTitle	: 'گه‌وره‌یی فۆنت'
	},

	colorButton :
	{
		textColorTitle	: 'ڕه‌نگی ده‌ق',
		bgColorTitle	: 'ڕه‌نگی پاشبنه‌ما',
		panelTitle		: 'ڕه‌نگه‌کان',
		auto			: 'خۆکار',
		more			: 'ڕه‌نگی زیاتر...'
	},

	colors :
	{
		'000' : 'ڕه‌ش',
		'800000' : 'سۆرو ماڕوونی',
		'8B4513' : 'ماڕوونی',
		'2F4F4F' : 'سه‌وزی تاریك',
		'008080' : 'سه‌وزو شین',
		'000080' : 'شینی تۆخ',
		'4B0082' : 'مۆری تۆخ',
		'696969' : 'ڕه‌ساسی تۆخ',
		'B22222' : 'سۆری تۆخ',
		'A52A2A' : 'قاوه‌یی',
		'DAA520' : 'قاوه‌یی بریسکه‌دار',
		'006400' : 'سه‌وزی تۆخ',
		'40E0D0' : 'شینی ناتۆخی بریسکه‌دار',
		'0000CD' : 'شینی مامناوه‌ند',
		'800080' : 'په‌مبه‌یی',
		'808080' : 'ڕه‌ساسی',
		'F00' : 'سۆر',
		'FF8C00' : 'ناره‌نجی تۆخ',
		'FFD700' : 'زه‌رد',
		'008000' : 'سه‌وز',
		'0FF' : 'شینی ئاسمانی',
		'00F' : 'شین',
		'EE82EE' : 'په‌مه‌یی',
		'A9A9A9' : 'ڕه‌ساسی ناتۆخ',
		'FFA07A' : 'ناره‌نجی ناتۆخ',
		'FFA500' : 'ناره‌نجی',
		'FFFF00' : 'زه‌رد',
		'00FF00' : 'سه‌وز',
		'AFEEEE' : 'شینی ناتۆخ',
		'ADD8E6' : 'شینی زۆر ناتۆخ',
		'DDA0DD' : 'په‌مه‌یی ناتۆخ',
		'D3D3D3' : 'ڕه‌ساسی بریسکه‌دار',
		'FFF0F5' : 'جه‌رگی زۆر ناتۆخ',
		'FAEBD7' : 'جه‌رگی ناتۆخ',
		'FFFFE0' : 'سپی ناتۆخ',
		'F0FFF0' : 'هه‌نگوینی ناتۆخ',
		'F0FFFF' : 'شینێکی زۆر ناتۆخ',
		'F0F8FF' : 'شینێکی ئاسمانی زۆر ناتۆخ',
		'E6E6FA' : 'شیری',
		'FFF' : 'سپی'
	},

	scayt :
	{
		title			: 'پشکنینی نووسه‌ له‌کاتی نووسین',
		opera_title		: 'پشتیوانی نه‌کراوه له‌لایه‌ن Opera',
		enable			: 'چالاککردنی SCAYT',
		disable			: 'ناچالاککردنی SCAYT',
		about			: 'ده‌رباره‌ی SCAYT',
		toggle			: 'گۆڕینی SCAYT',
		options			: 'هه‌ڵبژارده',
		langs			: 'زمانه‌کان',
		moreSuggestions	: 'پێشنیاری زیاتر',
		ignore			: 'پشتگوێخستن',
		ignoreAll		: 'پشتگوێخستنی هه‌مووی',
		addWord			: 'زیادکردنی ووشه',
		emptyDic		: 'ناوی فه‌رهه‌نگ نابێت خاڵی بێت.',
		noSuggestions	: 'No suggestions', // MISSING
		optionsTab		: 'هه‌ڵبژارده',
		allCaps			: 'پشتگوێخستنی وشانه‌ی پێکهاتووه له‌پیتی گه‌وره‌',
		ignoreDomainNames : 'پشتگوێخستنی دۆمه‌ین',
		mixedCase		: 'پشتگوێخستنی وشانه‌ی پێکهاتووه له‌پیتی گه‌وره‌و بچووك',
		mixedWithDigits	: 'پشتگوێخستنی وشانه‌ی پێکهاتووه له‌ژماره',

		languagesTab	: 'زمانه‌کان',

		dictionariesTab	: 'فه‌رهه‌نگه‌کان',
		dic_field_name	: 'ناوی فه‌رهه‌نگ',
		dic_create		: 'درووستکردن',
		dic_restore		: 'گه‌ڕاندنه‌وه',
		dic_delete		: 'سڕینه‌وه',
		dic_rename		: 'گۆڕینی ناو',
		dic_info		: 'له‌بنچینه‌دا فه‌رهه‌نگی به‌کارهێنه‌ر کۆگاکردن کراوه‌ له‌ شه‌کرۆکه Cookie, هه‌رچۆنێك بێت شه‌کۆرکه سنووردار کراوه له‌ قه‌باره کۆگاکردن.کاتێك فه‌رهه‌نگی به‌کارهێنه‌ر گه‌یشته‌ ئه‌م خاڵه‌ی که‌ناتوانرێت زیاتر کۆگاکردن بکرێت له‌ شه‌کرۆکه‌، ئه‌وسا فه‌رهه‌نگه‌که‌ پێویسته‌ کۆگابکرێت له‌ ڕاژه‌که‌ی ئێمه‌.‌ بۆ کۆگاکردنی زانیاری تایبه‌تی فه‌رهه‌نگه‌که‌ له‌ ڕاژه‌که‌ی ئێمه, پێویسته‌ ناوێك هه‌ڵبژێریت بۆ فه‌رهه‌نگه‌که‌. گه‌ر تۆ فه‌رهه‌نگێکی کۆگاکراوت هه‌یه‌, تکایه‌ ناوی فه‌رهه‌نگه‌که‌ بنووسه‌ وه‌ کلیکی دوگمه‌ی گه‌ڕاندنه‌وه‌ بکه.',

		aboutTab		: 'ده‌رباره‌ی'
	},

	about :
	{
		title		: 'ده‌رباره‌ی CKEditor',
		dlgTitle	: 'ده‌رباره‌ی CKEditor',
		help	: 'سه‌یری $1 بکه‌ بۆ یارمه‌تی.',
		userGuide : 'ڕێپیشانده‌ری CKEditors',
		moreInfo	: 'بۆ زانیاری زیاتری مۆڵه‌ت, تکایه‌ سه‌ردانی ماڵپه‌ڕه‌که‌مان بکه:',
		copy		: 'مافی له‌به‌رگرتنه‌وه‌ی &copy; $1. گشتی پارێزراوه.'
	},

	maximize : 'ئەوپه‌ڕی گەورەیی',
	minimize : 'ئەوپەڕی بچووکی',

	fakeobjects :
	{
		anchor		: 'له‌نگه‌ر',
		flash		: 'فلاش',
		iframe		: 'له‌چوارچێوه',
		hiddenfield	: 'شاردنه‌وه‌ی خانه',
		unknown		: 'به‌رکارێکی نه‌ناسراو'
	},

	resize : 'ڕابکێشە بۆ گۆڕینی قەبارەکەی',

	colordialog :
	{
		title		: 'هه‌ڵبژاردنی ڕه‌نگ',
		options	:	'هه‌ڵبژارده‌ی ڕه‌نگه‌کان',
		highlight	: 'نیشانکردن',
		selected	: 'هه‌ڵبژاردرا',
		clear		: 'پاککردنه‌وه'
	},

	toolbarCollapse	: 'شاردنەوی هێڵی تووڵامراز',
	toolbarExpand	: 'نیشاندانی هێڵی تووڵامراز',

	toolbarGroups :
	{
		document : 'په‌ڕه',
		clipboard : 'بڕین/پووچکردنه‌وه',
		editing : 'چاکسازی',
		forms : 'داڕشته',
		basicstyles : 'شێوازی بنچینه‌یی',
		paragraph : 'بڕگه',
		links : 'به‌سته‌ر',
		insert : 'خستنه‌ ناو',
		styles : 'شێواز',
		colors : 'ڕه‌نگه‌کان',
		tools : 'ئامرازه‌کان'
	},

	bidi :
	{
		ltr : 'ئاراسته‌ی نووسه‌ له‌چه‌پ بۆ ڕاست',
		rtl : 'ئاراسته‌ی نووسه‌ له‌ڕاست بۆ چه‌پ'
	},

	docprops :
	{
		label : 'خاسییه‌تی په‌ڕه',
		title : 'خاسییه‌تی په‌ڕه',
		design : 'شێوه‌کار',
		meta : 'زانیاری مێتا',
		chooseColor : '‌هه‌ڵبژێره',
		other : 'هیتر...',
		docTitle :	'سه‌ردێڕی په‌ڕه',
		charset : 	'ده‌سته‌ی نووسه‌ی به‌کۆده‌که‌ر',
		charsetOther : 'ده‌سته‌ی نووسه‌ی به‌کۆده‌که‌ری تر',
		charsetASCII : 'ASCII',
		charsetCE : 'ناوه‌ڕاست ئه‌وروپا',
		charsetCT : 'چینی(Big5)',
		charsetCR : 'سیریلیك',
		charsetGR : 'یۆنانی',
		charsetJP : 'ژاپۆن',
		charsetKR : 'کۆریا',
		charsetTR : 'تورکیا',
		charsetUN : 'Unicode (UTF-8)',
		charsetWE : 'ڕۆژئاوای ئه‌وروپا',
		docType : 'سه‌رپه‌ڕه‌ی جۆری په‌ڕه',
		docTypeOther : 'سه‌رپه‌ڕه‌ی جۆری په‌ڕه‌ی تر',
		xhtmlDec : 'به‌یاننامه‌کانی XHTML له‌گه‌ڵدابێت',
		bgColor : 'ڕه‌نگی پاشبنه‌ما',
		bgImage : 'ناونیشانی به‌سته‌ری وێنه‌ی پاشبنه‌ما',
		bgFixed : 'بێ هاتووچوپێکردنی (چه‌سپاو) پاشبنه‌مای وێنه',
		txtColor : 'ڕه‌نگی ده‌ق',
		margin : 'ته‌نیشت په‌ڕه‌',
		marginTop : 'سه‌ره‌وه',
		marginLeft : 'چه‌پ',
		marginRight : 'ڕاست',
		marginBottom : 'ژێره‌وه',
		metaKeywords : 'به‌ڵگه‌نامه‌ی وشه‌ی کاریگه‌ر(به‌ کۆما لێکیان جیابکه‌وه)',
		metaDescription : 'پێناسه‌ی لاپه‌ڕه',
		metaAuthor : 'نووسه‌ر',
		metaCopyright : 'مافی بڵاوکردنه‌وه‌ی',
		previewHtml : '<p>ئه‌مه‌ وه‌ك نموونه‌ی <strong>ده‌قه</strong>. تۆ به‌کارده‌هێنیت <a href="javascript:void(0)">CKEditor</a>.</p>'
	}
};
