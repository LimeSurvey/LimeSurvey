/*
Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @fileOverview Defines the {@link CKEDITOR.lang} object, for the
 * Mongolian language.
 */

/**#@+
   @type String
   @example
*/

/**
 * Contains the dictionary of language entries.
 * @namespace
 */
CKEDITOR.lang['mn'] =
{
	/**
	 * The language reading direction. Possible values are "rtl" for
	 * Right-To-Left languages (like Arabic) and "ltr" for Left-To-Right
	 * languages (like English).
	 * @default 'ltr'
	 */
	dir : 'ltr',

	/*
	 * Screenreader titles. Please note that screenreaders are not always capable
	 * of reading non-English words. So be careful while translating it.
	 */
	editorTitle : 'Rich text editor, %1', // MISSING
	editorHelp : 'Press ALT 0 for help', // MISSING

	// ARIA descriptions.
	toolbars	: 'Болосруулагчийн хэрэгслийн самбар',
	editor		: 'Хэлбэрт бичвэр боловсруулагч',

	// Toolbar buttons without dialogs.
	source			: 'Код',
	newPage			: 'Шинэ хуудас',
	save			: 'Хадгалах',
	preview			: 'Уридчлан харах',
	cut				: 'Хайчлах',
	copy			: 'Хуулах',
	paste			: 'Буулгах',
	print			: 'Хэвлэх',
	underline		: 'Доогуур нь зураастай болгох',
	bold			: 'Тод бүдүүн',
	italic			: 'Налуу',
	selectAll		: 'Бүгдийг нь сонгох',
	removeFormat	: 'Параргафын загварыг авч хаях',
	strike			: 'Дундуур нь зураастай болгох',
	subscript		: 'Суурь болгох',
	superscript		: 'Зэрэг болгох',
	horizontalrule	: 'Хөндлөн зураас оруулах',
	pagebreak		: 'Хуудас тусгаарлагч оруулах',
	pagebreakAlt		: 'Page Break', // MISSING
	unlink			: 'Холбоос авч хаях',
	undo			: 'Хүчингүй болгох',
	redo			: 'Өмнөх үйлдлээ сэргээх',

	// Common messages and labels.
	common :
	{
		browseServer	: 'Сервер харуулах',
		url				: 'URL',
		protocol		: 'Протокол',
		upload			: 'Хуулах',
		uploadSubmit	: 'Үүнийг сервэррүү илгээ',
		image			: 'Зураг',
		flash			: 'Флаш',
		form			: 'Форм',
		checkbox		: 'Чекбокс',
		radio			: 'Радио товч',
		textField		: 'Техт талбар',
		textarea		: 'Техт орчин',
		hiddenField		: 'Нууц талбар',
		button			: 'Товч',
		select			: 'Сонгогч талбар',
		imageButton		: 'Зурагтай товч',
		notSet			: '<Оноохгүй>',
		id				: 'Id',
		name			: 'Нэр',
		langDir			: 'Хэлний чиглэл',
		langDirLtr		: 'Зүүнээс баруун (LTR)',
		langDirRtl		: 'Баруунаас зүүн (RTL)',
		langCode		: 'Хэлний код',
		longDescr		: 'URL-ын тайлбар',
		cssClass		: 'Stylesheet классууд',
		advisoryTitle	: 'Зөвлөлдөх гарчиг',
		cssStyle		: 'Загвар',
		ok				: 'OK',
		cancel			: 'Болих',
		close			: 'Хаах',
		preview			: 'Preview', // MISSING
		generalTab		: 'Ерөнхий',
		advancedTab		: 'Нэмэлт',
		validateNumberFailed : 'This value is not a number.', // MISSING
		confirmNewPage	: 'Any unsaved changes to this content will be lost. Are you sure you want to load new page?', // MISSING
		confirmCancel	: 'Some of the options have been changed. Are you sure to close the dialog?', // MISSING
		options			: 'Сонголт',
		target			: 'Бай',
		targetNew		: 'New Window (_blank)', // MISSING
		targetTop		: 'Topmost Window (_top)', // MISSING
		targetSelf		: 'Same Window (_self)', // MISSING
		targetParent	: 'Parent Window (_parent)', // MISSING
		langDirLTR		: 'Зүүн талаас баруун тийшээ (LTR)',
		langDirRTL		: 'Баруун талаас зүүн тийшээ (RTL)',
		styles			: 'Загвар',
		cssClasses		: 'Stylesheet Classes', // MISSING
		width			: 'Өргөн',
		height			: 'Өндөр',
		align			: 'Тулгах тал',
		alignLeft		: 'Зүүн',
		alignRight		: 'Баруун',
		alignCenter		: 'Төвд',
		alignTop		: 'Дээд талд',
		alignMiddle		: 'Дунд талд',
		alignBottom		: 'Доод талд',
		invalidValue	: 'Invalid value.', // MISSING
		invalidHeight	: 'Өндөр нь тоо байх ёстой.',
		invalidWidth	: 'Өргөн нь тоо байх ёстой.',
		invalidCssLength	: 'Value specified for the "%1" field must be a positive number with or without a valid CSS measurement unit (px, %, in, cm, mm, em, ex, pt, or pc).', // MISSING
		invalidHtmlLength	: 'Value specified for the "%1" field must be a positive number with or without a valid HTML measurement unit (px or %).', // MISSING
		invalidInlineStyle	: 'Value specified for the inline style must consist of one or more tuples with the format of "name : value", separated by semi-colons.', // MISSING
		cssLengthTooltip	: 'Enter a number for a value in pixels or a number with a valid CSS unit (px, %, in, cm, mm, em, ex, pt, or pc).', // MISSING

		// Put the voice-only part of the label in the span.
		unavailable		: '%1<span class="cke_accessibility">, unavailable</span>' // MISSING
	},

	contextmenu :
	{
		options : 'Context Menu Options' // MISSING
	},

	// Special char dialog.
	specialChar		:
	{
		toolbar		: 'Онцгой тэмдэгт оруулах',
		title		: 'Онцгой тэмдэгт сонгох',
		options : 'Special Character Options' // MISSING
	},

	// Link dialog.
	link :
	{
		toolbar		: 'Холбоос',
		other 		: '<other>', // MISSING
		menu		: 'Холбоос засварлах',
		title		: 'Холбоос',
		info		: 'Холбоосын тухай мэдээлэл',
		target		: 'Байрлал',
		upload		: 'Хуулах',
		advanced	: 'Нэмэлт',
		type		: 'Линкийн төрөл',
		toUrl		: 'цахим хуудасны хаяг (URL)',
		toAnchor	: 'Энэ бичвэр дэх зангуу руу очих холбоос',
		toEmail		: 'Э-захиа',
		targetFrame		: '<Агуулах хүрээ>',
		targetPopup		: '<popup цонх>',
		targetFrameName	: 'Очих фремын нэр',
		targetPopupName	: 'Popup цонхны нэр',
		popupFeatures	: 'Popup цонхны онцлог',
		popupResizable	: 'Resizable', // MISSING
		popupStatusBar	: 'Статус хэсэг',
		popupLocationBar: 'Location хэсэг',
		popupToolbar	: 'Багажны самбар',
		popupMenuBar	: 'Цэсний самбар',
		popupFullScreen	: 'Цонх дүүргэх (Internet Explorer)',
		popupScrollBars	: 'Скрол хэсэгүүд',
		popupDependent	: 'Хамаатай (Netscape)',
		popupLeft		: 'Зүүн байрлал',
		popupTop		: 'Дээд байрлал',
		id				: 'Id', // MISSING
		langDir			: 'Хэлний чиглэл',
		langDirLTR		: 'Зүүнээс баруун (LTR)',
		langDirRTL		: 'Баруунаас зүүн (RTL)',
		acccessKey		: 'Холбох түлхүүр',
		name			: 'Нэр',
		langCode			: 'Хэлний код',
		tabIndex			: 'Tab индекс',
		advisoryTitle		: 'Зөвлөлдөх гарчиг',
		advisoryContentType	: 'Зөвлөлдөх төрлийн агуулга',
		cssClasses		: 'Stylesheet классууд',
		charset			: 'Тэмдэгт оноох нөөцөд холбогдсон',
		styles			: 'Загвар',
		rel			: 'Relationship', // MISSING
		selectAnchor		: 'Нэг зангууг сонгоно уу',
		anchorName		: 'Зангуугийн нэрээр',
		anchorId			: 'Элемэнтйн Id нэрээр',
		emailAddress		: 'Э-шуудангийн хаяг',
		emailSubject		: 'Зурвасны гарчиг',
		emailBody		: 'Зурвасны их бие',
		noAnchors		: '(Баримт бичиг зангуугүй байна)',
		noUrl			: 'Холбоосны URL хаягийг шивнэ үү',
		noEmail			: 'Э-шуудангий хаягаа шивнэ үү'
	},

	// Anchor dialog
	anchor :
	{
		toolbar		: 'Зангуу',
		menu		: 'Зангууг болосруулах',
		title		: 'Зангуугийн шинж чанар',
		name		: 'Зангуугийн нэр',
		errorName	: 'Зангуугийн нэрийг оруулна уу',
		remove		: 'Зангууг устгах'
	},

	// List style dialog
	list:
	{
		numberedTitle		: 'Numbered List Properties', // MISSING
		bulletedTitle		: 'Bulleted List Properties', // MISSING
		type				: 'Төрөл',
		start				: 'Start', // MISSING
		validateStartNumber				:'List start number must be a whole number.', // MISSING
		circle				: 'Circle', // MISSING
		disc				: 'Disc', // MISSING
		square				: 'Square', // MISSING
		none				: 'None', // MISSING
		notset				: '<not set>', // MISSING
		armenian			: 'Armenian numbering', // MISSING
		georgian			: 'Georgian numbering (an, ban, gan, etc.)', // MISSING
		lowerRoman			: 'Lower Roman (i, ii, iii, iv, v, etc.)', // MISSING
		upperRoman			: 'Upper Roman (I, II, III, IV, V, etc.)', // MISSING
		lowerAlpha			: 'Lower Alpha (a, b, c, d, e, etc.)', // MISSING
		upperAlpha			: 'Upper Alpha (A, B, C, D, E, etc.)', // MISSING
		lowerGreek			: 'Lower Greek (alpha, beta, gamma, etc.)', // MISSING
		decimal				: 'Decimal (1, 2, 3, etc.)', // MISSING
		decimalLeadingZero	: 'Decimal leading zero (01, 02, 03, etc.)' // MISSING
	},

	// Find And Replace Dialog
	findAndReplace :
	{
		title				: 'Хайж орлуулах',
		find				: 'Хайх',
		replace				: 'Орлуулах',
		findWhat			: 'Хайх үг/үсэг:',
		replaceWith			: 'Солих үг:',
		notFoundMsg			: 'Хайсан бичвэрийг олсонгүй.',
		findOptions			: 'Хайх сонголтууд',
		matchCase			: 'Тэнцэх төлөв',
		matchWord			: 'Тэнцэх бүтэн үг',
		matchCyclic			: 'Match cyclic', // MISSING
		replaceAll			: 'Бүгдийг нь солих',
		replaceSuccessMsg	: '%1 occurrence(s) replaced.' // MISSING
	},

	// Table Dialog
	table :
	{
		toolbar		: 'Хүснэгт',
		title		: 'Хүснэгт',
		menu		: 'Хүснэгт',
		deleteTable	: 'Хүснэгт устгах',
		rows		: 'Мөр',
		columns		: 'Багана',
		border		: 'Хүрээний хэмжээ',
		widthPx		: 'цэг',
		widthPc		: 'хувь',
		widthUnit	: 'өргөний нэгж',
		cellSpace	: 'Нүх хоорондын зай (spacing)',
		cellPad		: 'Нүх доторлох(padding)',
		caption		: 'Тайлбар',
		summary		: 'Тайлбар',
		headers		: 'Headers', // MISSING
		headersNone		: 'None', // MISSING
		headersColumn	: 'First column', // MISSING
		headersRow		: 'First Row', // MISSING
		headersBoth		: 'Both', // MISSING
		invalidRows		: 'Number of rows must be a number greater than 0.', // MISSING
		invalidCols		: 'Number of columns must be a number greater than 0.', // MISSING
		invalidBorder	: 'Border size must be a number.', // MISSING
		invalidWidth	: 'Хүснэгтийн өргөн нь тоо байх ёстой.',
		invalidHeight	: 'Table height must be a number.', // MISSING
		invalidCellSpacing	: 'Cell spacing must be a positive number.', // MISSING
		invalidCellPadding	: 'Cell padding must be a positive number.', // MISSING

		cell :
		{
			menu			: 'Нүх/зай',
			insertBefore	: 'Нүх/зай өмнө нь оруулах',
			insertAfter		: 'Нүх/зай дараа нь оруулах',
			deleteCell		: 'Нүх устгах',
			merge			: 'Нүх нэгтэх',
			mergeRight		: 'Баруун тийш нэгтгэх',
			mergeDown		: 'Доош нэгтгэх',
			splitHorizontal	: 'Нүх/зайг босоогоор нь тусгаарлах',
			splitVertical	: 'Нүх/зайг хөндлөнгөөр нь тусгаарлах',
			title			: 'Cell Properties', // MISSING
			cellType		: 'Cell Type', // MISSING
			rowSpan			: 'Rows Span', // MISSING
			colSpan			: 'Columns Span', // MISSING
			wordWrap		: 'Word Wrap', // MISSING
			hAlign			: 'Хэвтээд тэгшлэх арга',
			vAlign			: 'Босоод тэгшлэх арга',
			alignBaseline	: 'Baseline', // MISSING
			bgColor			: 'Дэвсгэр өнгө',
			borderColor		: 'Хүрээний өнгө',
			data			: 'Data', // MISSING
			header			: 'Header', // MISSING
			yes				: 'Тийм',
			no				: 'Үгүй',
			invalidWidth	: 'Нүдний өргөн нь тоо байх ёстой.',
			invalidHeight	: 'Cell height must be a number.', // MISSING
			invalidRowSpan	: 'Rows span must be a whole number.', // MISSING
			invalidColSpan	: 'Columns span must be a whole number.', // MISSING
			chooseColor		: 'Сонгох'
		},

		row :
		{
			menu			: 'Мөр',
			insertBefore	: 'Мөр өмнө нь оруулах',
			insertAfter		: 'Мөр дараа нь оруулах',
			deleteRow		: 'Мөр устгах'
		},

		column :
		{
			menu			: 'Багана',
			insertBefore	: 'Багана өмнө нь оруулах',
			insertAfter		: 'Багана дараа нь оруулах',
			deleteColumn	: 'Багана устгах'
		}
	},

	// Button Dialog.
	button :
	{
		title		: 'Товчны шинж чанар',
		text		: 'Тэкст (Утга)',
		type		: 'Төрөл',
		typeBtn		: 'Товч',
		typeSbm		: 'Submit',
		typeRst		: 'Болих'
	},

	// Checkbox and Radio Button Dialogs.
	checkboxAndRadio :
	{
		checkboxTitle : 'Чекбоксны шинж чанар',
		radioTitle	: 'Радио товчны шинж чанар',
		value		: 'Утга',
		selected	: 'Сонгогдсон'
	},

	// Form Dialog.
	form :
	{
		title		: 'Форм шинж чанар',
		menu		: 'Форм шинж чанар',
		action		: 'Үйлдэл',
		method		: 'Арга',
		encoding	: 'Encoding' // MISSING
	},

	// Select Field Dialog.
	select :
	{
		title		: 'Согогч талбарын шинж чанар',
		selectInfo	: 'Мэдээлэл',
		opAvail		: 'Идвэхтэй сонголт',
		value		: 'Утга',
		size		: 'Хэмжээ',
		lines		: 'Мөр',
		chkMulti	: 'Олон зүйл зэрэг сонгохыг зөвшөөрөх',
		opText		: 'Тэкст',
		opValue		: 'Утга',
		btnAdd		: 'Нэмэх',
		btnModify	: 'Өөрчлөх',
		btnUp		: 'Дээш',
		btnDown		: 'Доош',
		btnSetValue : 'Сонгогдсан утга оноох',
		btnDelete	: 'Устгах'
	},

	// Textarea Dialog.
	textarea :
	{
		title		: 'Текст орчны шинж чанар',
		cols		: 'Багана',
		rows		: 'Мөр'
	},

	// Text Field Dialog.
	textfield :
	{
		title		: 'Текст талбарын шинж чанар',
		name		: 'Нэр',
		value		: 'Утга',
		charWidth	: 'Тэмдэгтын өргөн',
		maxChars	: 'Хамгийн их тэмдэгт',
		type		: 'Төрөл',
		typeText	: 'Текст',
		typePass	: 'Нууц үг'
	},

	// Hidden Field Dialog.
	hidden :
	{
		title	: 'Нууц талбарын шинж чанар',
		name	: 'Нэр',
		value	: 'Утга'
	},

	// Image Dialog.
	image :
	{
		title		: 'Зураг',
		titleButton	: 'Зурган товчны шинж чанар',
		menu		: 'Зураг',
		infoTab		: 'Зурагны мэдээлэл',
		btnUpload	: 'Үүнийг сервэррүү илгээ',
		upload		: 'Хуулах',
		alt			: 'Зургийг орлох бичвэр',
		lockRatio	: 'Радио түгжих',
		resetSize	: 'хэмжээ дахин оноох',
		border		: 'Хүрээ',
		hSpace		: 'Хөндлөн зай',
		vSpace		: 'Босоо зай',
		alertUrl	: 'Зурагны URL-ын төрлийн сонгоно уу',
		linkTab		: 'Холбоос',
		button2Img	: 'Do you want to transform the selected image button on a simple image?', // MISSING
		img2Button	: 'Do you want to transform the selected image on a image button?', // MISSING
		urlMissing	: 'Зургийн эх сурвалжийн хаяг (URL) байхгүй байна.',
		validateBorder	: 'Border must be a whole number.', // MISSING
		validateHSpace	: 'HSpace must be a whole number.', // MISSING
		validateVSpace	: 'VSpace must be a whole number.' // MISSING
	},

	// Flash Dialog
	flash :
	{
		properties		: 'Флаш шинж чанар',
		propertiesTab	: 'Properties', // MISSING
		title			: 'Флаш  шинж чанар',
		chkPlay			: 'Автоматаар тоглох',
		chkLoop			: 'Давтах',
		chkMenu			: 'Флаш цэс идвэхжүүлэх',
		chkFull			: 'Allow Fullscreen', // MISSING
 		scale			: 'Өргөгтгөх',
		scaleAll		: 'Бүгдийг харуулах',
		scaleNoBorder	: 'Хүрээгүй',
		scaleFit		: 'Яг тааруулах',
		access			: 'Script Access', // MISSING
		accessAlways	: 'Онцлогууд',
		accessSameDomain: 'Байнга',
		accessNever		: 'Хэзээ ч үгүй',
		alignAbsBottom	: 'Abs доод талд',
		alignAbsMiddle	: 'Abs Дунд талд',
		alignBaseline	: 'Baseline',
		alignTextTop	: 'Текст дээр',
		quality			: 'Quality', // MISSING
		qualityBest		: 'Best', // MISSING
		qualityHigh		: 'High', // MISSING
		qualityAutoHigh	: 'Auto High', // MISSING
		qualityMedium	: 'Medium', // MISSING
		qualityAutoLow	: 'Auto Low', // MISSING
		qualityLow		: 'Low', // MISSING
		windowModeWindow: 'Window', // MISSING
		windowModeOpaque: 'Opaque', // MISSING
		windowModeTransparent : 'Transparent', // MISSING
		windowMode		: 'Window mode', // MISSING
		flashvars		: 'Variables for Flash', // MISSING
		bgcolor			: 'Дэвсгэр өнгө',
		hSpace			: 'Хөндлөн зай',
		vSpace			: 'Босоо зай',
		validateSrc		: 'Линк URL-ээ төрөлжүүлнэ үү',
		validateHSpace	: 'HSpace must be a number.', // MISSING
		validateVSpace	: 'VSpace must be a number.' // MISSING
	},

	// Speller Pages Dialog
	spellCheck :
	{
		toolbar			: 'Үгийн дүрэх шалгах',
		title			: 'Spell Check', // MISSING
		notAvailable	: 'Sorry, but service is unavailable now.', // MISSING
		errorLoading	: 'Error loading application service host: %s.', // MISSING
		notInDic		: 'Толь бичиггүй',
		changeTo		: 'Өөрчлөх',
		btnIgnore		: 'Зөвшөөрөх',
		btnIgnoreAll	: 'Бүгдийг зөвшөөрөх',
		btnReplace		: 'Солих',
		btnReplaceAll	: 'Бүгдийг Дарж бичих',
		btnUndo			: 'Буцаах',
		noSuggestions	: '- Тайлбаргүй -',
		progress		: 'Дүрэм шалгаж байгаа үйл явц...',
		noMispell		: 'Дүрэм шалгаад дууссан: Алдаа олдсонгүй',
		noChanges		: 'Дүрэм шалгаад дууссан: үг өөрчлөгдөөгүй',
		oneChange		: 'Дүрэм шалгаад дууссан: 1 үг өөрчлөгдсөн',
		manyChanges		: 'Дүрэм шалгаад дууссан: %1 үг өөрчлөгдсөн',
		ieSpellDownload	: 'Дүрэм шалгагч суугаагүй байна. Татаж авахыг хүсч байна уу?'
	},

	smiley :
	{
		toolbar	: 'Тодорхойлолт',
		title	: 'Тодорхойлолт оруулах',
		options : 'Smiley Options' // MISSING
	},

	elementsPath :
	{
		eleLabel : 'Elements path', // MISSING
		eleTitle : '%1 element' // MISSING
	},

	numberedlist	: 'Дугаарлагдсан жагсаалт',
	bulletedlist	: 'Цэгтэй жагсаалт',
	indent			: 'Догол мөр хасах',
	outdent			: 'Догол мөр нэмэх',

	justify :
	{
		left	: 'Зүүн талд тулгах',
		center	: 'Голлуулах',
		right	: 'Баруун талд тулгах',
		block	: 'Тэгшлэх'
	},

	blockquote : 'Ишлэл хэсэг',

	clipboard :
	{
		title		: 'Буулгах',
		cutError	: 'Таны browser-ын хамгаалалтын тохиргоо editor-д автоматаар хайчлах үйлдэлийг зөвшөөрөхгүй байна. (Ctrl/Cmd+X) товчны хослолыг ашиглана уу.',
		copyError	: 'Таны browser-ын хамгаалалтын тохиргоо editor-д автоматаар хуулах үйлдэлийг зөвшөөрөхгүй байна. (Ctrl/Cmd+C) товчны хослолыг ашиглана уу.',
		pasteMsg	: '(<strong>Ctrl/Cmd+V</strong>) товчийг ашиглан paste хийнэ үү. Мөн <strong>OK</strong> дар.',
		securityMsg	: 'Таны үзүүлэгч/browser/-н хамгаалалтын тохиргооноос болоод editor clipboard өгөгдөлрүү шууд хандах боломжгүй. Энэ цонход дахин paste хийхийг оролд.',
		pasteArea	: 'Paste Area' // MISSING
	},

	pastefromword :
	{
		confirmCleanup	: 'The text you want to paste seems to be copied from Word. Do you want to clean it before pasting?', // MISSING
		toolbar			: 'Word-оос буулгах',
		title			: 'Word-оос буулгах',
		error			: 'It was not possible to clean up the pasted data due to an internal error' // MISSING
	},

	pasteText :
	{
		button	: 'Энгийн бичвэрээр буулгах',
		title	: 'Энгийн бичвэрээр буулгах'
	},

	templates :
	{
		button			: 'Загварууд',
		title			: 'Загварын агуулга',
		options : 'Template Options', // MISSING
		insertOption	: 'Одоогийн агууллагыг дарж бичих',
		selectPromptMsg	: 'Загварыг нээж editor-рүү сонгож оруулна уу<br />(Одоогийн агууллагыг устаж магадгүй):',
		emptyListMsg	: '(Загвар тодорхойлогдоогүй байна)'
	},

	showBlocks : 'Хавтангуудыг харуулах',

	stylesCombo :
	{
		label		: 'Загвар',
		panelTitle	: 'Загвар хэлбэржүүлэх',
		panelTitle1	: 'Block Styles', // MISSING
		panelTitle2	: 'Inline Styles', // MISSING
		panelTitle3	: 'Object Styles' // MISSING
	},

	format :
	{
		label		: 'Параргафын загвар',
		panelTitle	: 'Параргафын загвар',

		tag_p		: 'Хэвийн',
		tag_pre		: 'Formatted',
		tag_address	: 'Хаяг',
		tag_h1		: 'Гарчиг 1',
		tag_h2		: 'Гарчиг 2',
		tag_h3		: 'Гарчиг 3',
		tag_h4		: 'Гарчиг 4',
		tag_h5		: 'Гарчиг 5',
		tag_h6		: 'Гарчиг 6',
		tag_div		: 'Paragraph (DIV)'
	},

	div :
	{
		title				: 'Div гэдэг хэсэг бий болгох',
		toolbar				: 'Div гэдэг хэсэг бий болгох',
		cssClassInputLabel	: 'Stylesheet Classes', // MISSING
		styleSelectLabel	: 'Style', // MISSING
		IdInputLabel		: 'Id', // MISSING
		languageCodeInputLabel	: ' Language Code', // MISSING
		inlineStyleInputLabel	: 'Inline Style', // MISSING
		advisoryTitleInputLabel	: 'Advisory Title', // MISSING
		langDirLabel		: 'Language Direction', // MISSING
		langDirLTRLabel		: 'Зүүн талаас баруун тишээ (LTR)',
		langDirRTLLabel		: 'Баруун талаас зүүн тишээ (RTL)',
		edit				: 'Edit Div', // MISSING
		remove				: 'Remove Div' // MISSING
  	},

	iframe :
	{
		title		: 'IFrame Properties', // MISSING
		toolbar		: 'IFrame', // MISSING
		noUrl		: 'Please type the iframe URL', // MISSING
		scrolling	: 'Enable scrollbars', // MISSING
		border		: 'Show frame border' // MISSING
	},

	font :
	{
		label		: 'Үсгийн хэлбэр',
		voiceLabel	: 'Үгсийн хэлбэр',
		panelTitle	: 'Үгсийн хэлбэрийн нэр'
	},

	fontSize :
	{
		label		: 'Хэмжээ',
		voiceLabel	: 'Үсгийн хэмжээ',
		panelTitle	: 'Үсгийн хэмжээ'
	},

	colorButton :
	{
		textColorTitle	: 'Бичвэрийн өнгө',
		bgColorTitle	: 'Дэвсгэр өнгө',
		panelTitle		: 'Өнгөнүүд',
		auto			: 'Автоматаар',
		more			: 'Нэмэлт өнгөнүүд...'
	},

	colors :
	{
		'000' : 'Хар',
		'800000' : 'Хүрэн',
		'8B4513' : 'Saddle Brown', // MISSING
		'2F4F4F' : 'Dark Slate Gray', // MISSING
		'008080' : 'Teal', // MISSING
		'000080' : 'Navy', // MISSING
		'4B0082' : 'Indigo', // MISSING
		'696969' : 'Dark Gray', // MISSING
		'B22222' : 'Fire Brick', // MISSING
		'A52A2A' : 'Brown', // MISSING
		'DAA520' : 'Golden Rod', // MISSING
		'006400' : 'Dark Green', // MISSING
		'40E0D0' : 'Turquoise', // MISSING
		'0000CD' : 'Medium Blue', // MISSING
		'800080' : 'Purple', // MISSING
		'808080' : 'Саарал',
		'F00' : 'Улаан',
		'FF8C00' : 'Dark Orange', // MISSING
		'FFD700' : 'Алт',
		'008000' : 'Ногоон',
		'0FF' : 'Цэнхэр',
		'00F' : 'Хөх',
		'EE82EE' : 'Ягаан',
		'A9A9A9' : 'Dim Gray', // MISSING
		'FFA07A' : 'Light Salmon', // MISSING
		'FFA500' : 'Улбар шар',
		'FFFF00' : 'Шар',
		'00FF00' : 'Lime', // MISSING
		'AFEEEE' : 'Pale Turquoise', // MISSING
		'ADD8E6' : 'Light Blue', // MISSING
		'DDA0DD' : 'Plum', // MISSING
		'D3D3D3' : 'Цайвар саарал',
		'FFF0F5' : 'Lavender Blush', // MISSING
		'FAEBD7' : 'Antique White', // MISSING
		'FFFFE0' : 'Light Yellow', // MISSING
		'F0FFF0' : 'Honeydew', // MISSING
		'F0FFFF' : 'Azure', // MISSING
		'F0F8FF' : 'Alice Blue', // MISSING
		'E6E6FA' : 'Lavender', // MISSING
		'FFF' : 'Цагаан'
	},

	scayt :
	{
		title			: 'Spell Check As You Type', // MISSING
		opera_title		: 'Not supported by Opera', // MISSING
		enable			: 'Enable SCAYT', // MISSING
		disable			: 'Disable SCAYT', // MISSING
		about			: 'About SCAYT', // MISSING
		toggle			: 'Toggle SCAYT', // MISSING
		options			: 'Сонголт',
		langs			: 'Хэлүүд',
		moreSuggestions	: 'More suggestions', // MISSING
		ignore			: 'Ignore', // MISSING
		ignoreAll		: 'Ignore All', // MISSING
		addWord			: 'Add Word', // MISSING
		emptyDic		: 'Dictionary name should not be empty.', // MISSING
		noSuggestions	: 'No suggestions', // MISSING
		optionsTab		: 'Сонголт',
		allCaps			: 'Ignore All-Caps Words', // MISSING
		ignoreDomainNames : 'Ignore Domain Names', // MISSING
		mixedCase		: 'Ignore Words with Mixed Case', // MISSING
		mixedWithDigits	: 'Ignore Words with Numbers', // MISSING

		languagesTab	: 'Хэлүүд',

		dictionariesTab	: 'Толь бичгүүд',
		dic_field_name	: 'Dictionary name', // MISSING
		dic_create		: 'Бий болгох',
		dic_restore		: 'Restore', // MISSING
		dic_delete		: 'Устгах',
		dic_rename		: 'Нэрийг солих',
		dic_info		: 'Initially the User Dictionary is stored in a Cookie. However, Cookies are limited in size. When the User Dictionary grows to a point where it cannot be stored in a Cookie, then the dictionary may be stored on our server. To store your personal dictionary on our server you should specify a name for your dictionary. If you already have a stored dictionary, please type its name and click the Restore button.', // MISSING

		aboutTab		: 'About' // MISSING
	},

	about :
	{
		title		: 'About CKEditor', // MISSING
		dlgTitle	: 'About CKEditor', // MISSING
		help	: 'Check $1 for help.', // MISSING
		userGuide : 'CKEditor User\'s Guide', // MISSING
		moreInfo	: 'For licensing information please visit our web site:', // MISSING
		copy		: 'Copyright &copy; $1. All rights reserved.' // MISSING
	},

	maximize : 'Дэлгэц дүүргэх',
	minimize : 'Цонхыг багсгаж харуулах',

	fakeobjects :
	{
		anchor		: 'Зангуу',
		flash		: 'Flash Animation', // MISSING
		iframe		: 'IFrame', // MISSING
		hiddenfield	: 'Hidden Field', // MISSING
		unknown		: 'Unknown Object' // MISSING
	},

	resize : 'Drag to resize', // MISSING

	colordialog :
	{
		title		: 'Select color', // MISSING
		options	:	'Color Options', // MISSING
		highlight	: 'Highlight', // MISSING
		selected	: 'Selected Color', // MISSING
		clear		: 'Clear' // MISSING
	},

	toolbarCollapse	: 'Collapse Toolbar', // MISSING
	toolbarExpand	: 'Expand Toolbar', // MISSING

	toolbarGroups :
	{
		document : 'Document', // MISSING
		clipboard : 'Clipboard/Undo', // MISSING
		editing : 'Editing', // MISSING
		forms : 'Forms', // MISSING
		basicstyles : 'Basic Styles', // MISSING
		paragraph : 'Paragraph', // MISSING
		links : 'Холбоосууд',
		insert : 'Оруулах',
		styles : 'Загварууд',
		colors : 'Онгөнүүд',
		tools : 'Хэрэгслүүд'
	},

	bidi :
	{
		ltr : 'Зүүнээс баруун тийш бичлэг',
		rtl : 'Баруунаас зүүн тийш бичлэг'
	},

	docprops :
	{
		label : 'Баримт бичиг шинж чанар',
		title : 'Баримт бичиг шинж чанар',
		design : 'Design', // MISSING
		meta : 'Meta өгөгдөл',
		chooseColor : 'Сонгох',
		other : '<other>',
		docTitle :	'Хуудасны гарчиг',
		charset : 	'Encoding тэмдэгт',
		charsetOther : 'Encoding-д өөр тэмдэгт оноох',
		charsetASCII : 'ASCII', // MISSING
		charsetCE : 'Төв европ',
		charsetCT : 'Хятадын уламжлалт (Big5)',
		charsetCR : 'Крил',
		charsetGR : 'Гред',
		charsetJP : 'Япон',
		charsetKR : 'Солонгос',
		charsetTR : 'Tурк',
		charsetUN : 'Юникод (UTF-8)',
		charsetWE : 'Баруун европ',
		docType : 'Баримт бичгийн төрөл Heading',
		docTypeOther : 'Бусад баримт бичгийн төрөл Heading',
		xhtmlDec : 'XHTML-ийн мэдээллийг агуулах',
		bgColor : 'Фоно өнгө',
		bgImage : 'Фоно зурагны URL',
		bgFixed : 'Гүйдэггүй фоно',
		txtColor : 'Фонтны өнгө',
		margin : 'Хуудасны захын зай',
		marginTop : 'Дээд тал',
		marginLeft : 'Зүүн тал',
		marginRight : 'Баруун тал',
		marginBottom : 'Доод тал',
		metaKeywords : 'Баримт бичгийн индекс түлхүүр үг (таслалаар тусгаарлагдана)',
		metaDescription : 'Баримт бичгийн тайлбар',
		metaAuthor : 'Зохиогч',
		metaCopyright : 'Зохиогчийн эрх',
		previewHtml : '<p>This is some <strong>sample text</strong>. You are using <a href="javascript:void(0)">CKEditor</a>.</p>' // MISSING
	}
};
