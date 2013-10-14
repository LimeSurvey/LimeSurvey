/*
Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @fileOverview Defines the {@link CKEDITOR.lang} object, for the
 * Latvian language.
 */

/**#@+
   @type String
   @example
*/

/**
 * Contains the dictionary of language entries.
 * @namespace
 */
CKEDITOR.lang['lv'] =
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
	editorTitle : 'Bagātinātā teksta redaktors, %1',
	editorHelp : 'Palīdzībai, nospiediet ALT 0 ',

	// ARIA descriptions.
	toolbars	: 'Redaktora rīkjoslas',
	editor		: 'Bagātinātā teksta redaktors',

	// Toolbar buttons without dialogs.
	source			: 'HTML kods',
	newPage			: 'Jauna lapa',
	save			: 'Saglabāt',
	preview			: 'Priekšskatīt',
	cut				: 'Izgriezt',
	copy			: 'Kopēt',
	paste			: 'Ielīmēt',
	print			: 'Drukāt',
	underline		: 'Pasvītrots',
	bold			: 'Treknināts',
	italic			: 'Kursīvs',
	selectAll		: 'Iezīmēt visu',
	removeFormat	: 'Noņemt stilus',
	strike			: 'Pārsvītrots',
	subscript		: 'Apakšrakstā',
	superscript		: 'Augšrakstā',
	horizontalrule	: 'Ievietot horizontālu Atdalītājsvītru',
	pagebreak		: 'Ievietot lapas pārtraukumu drukai',
	pagebreakAlt		: 'Lapas pārnesums',
	unlink			: 'Noņemt hipersaiti',
	undo			: 'Atcelt',
	redo			: 'Atkārtot',

	// Common messages and labels.
	common :
	{
		browseServer	: 'Skatīt servera saturu',
		url				: 'URL',
		protocol		: 'Protokols',
		upload			: 'Augšupielādēt',
		uploadSubmit	: 'Nosūtīt serverim',
		image			: 'Attēls',
		flash			: 'Flash',
		form			: 'Forma',
		checkbox		: 'Izvēles rūtiņa',
		radio			: 'Radio poga',
		textField		: 'Teksta rinda',
		textarea		: 'Teksta laukums',
		hiddenField		: 'Paslēpts lauks',
		button			: 'Poga',
		select			: 'Iezīmēšanas lauks',
		imageButton		: 'Attēlpoga',
		notSet			: '<nav iestatīts>',
		id				: 'Id',
		name			: 'Nosaukums',
		langDir			: 'Valodas lasīšanas virziens',
		langDirLtr		: 'No kreisās uz labo (LTR)',
		langDirRtl		: 'No labās uz kreiso (RTL)',
		langCode		: 'Valodas kods',
		longDescr		: 'Gara apraksta Hipersaite',
		cssClass		: 'Stilu saraksta klases',
		advisoryTitle	: 'Konsultatīvs virsraksts',
		cssStyle		: 'Stils',
		ok				: 'Apstiprināt',
		cancel			: 'Atcelt',
		close			: 'Aizvērt',
		preview			: 'Priekšskatījums',
		generalTab		: 'Vispārīgi',
		advancedTab		: 'Izvērstais',
		validateNumberFailed : 'Šī vērtība nav skaitlis',
		confirmNewPage	: 'Jebkuras nesaglabātās izmaiņas tiks zaudētas. Vai tiešām vēlaties atvērt jaunu lapu?',
		confirmCancel	: 'Daži no uzstādījumiem ir mainīti. Vai tiešām vēlaties aizvērt šo dialogu?',
		options			: 'Uzstādījumi',
		target			: 'Mērķis',
		targetNew		: 'Jauns logs (_blank)',
		targetTop		: 'Virsējais logs (_top)',
		targetSelf		: 'Tas pats logs (_self)',
		targetParent	: 'Avota logs (_parent)',
		langDirLTR		: 'Kreisais uz Labo (LTR)',
		langDirRTL		: 'Labais uz Kreiso (RTL)',
		styles			: 'Stils',
		cssClasses		: 'Stilu klases',
		width			: 'Platums',
		height			: 'Augstums',
		align			: 'Līdzinājums',
		alignLeft		: 'Pa kreisi',
		alignRight		: 'Pa labi',
		alignCenter		: 'Centrēti',
		alignTop		: 'Augšā',
		alignMiddle		: 'Pa vidu',
		alignBottom		: 'Apakšā',
		invalidValue	: 'Nekorekta vērtība',
		invalidHeight	: 'Augstumam jābūt skaitlim.',
		invalidWidth	: 'Platumam jābūt skaitlim',
		invalidCssLength	: 'Laukam "%1" norādītajai vērtībai jābūt pozitīvam skaitlim ar vai bez korektām CSS mērvienībām (px, %, in, cm, mm, em, ex, pt, vai pc).',
		invalidHtmlLength	: 'Laukam "%1" norādītajai vērtībai jābūt pozitīvam skaitlim ar vai bez korektām HTML mērvienībām (px vai %).',
		invalidInlineStyle	: 'Iekļautajā stilā norādītajai vērtībai jāsastāv no viena vai vairākiem pāriem pēc forma\'ta "nosaukums: vērtība", atdalītiem ar semikolu.',
		cssLengthTooltip	: 'Ievadiet vērtību pikseļos vai skaitli ar derīgu CSS mērvienību (px, %, in, cm, mm, em, ex, pt, vai pc).',

		// Put the voice-only part of the label in the span.
		unavailable		: '%1<span class="cke_accessibility">, nav pieejams</span>'
	},

	contextmenu :
	{
		options : 'Uznirstošās izvēlnes uzstādījumi'
	},

	// Special char dialog.
	specialChar		:
	{
		toolbar		: 'Ievietot speciālo simbolu',
		title		: 'Ievietot īpašu simbolu',
		options : 'Speciālo simbolu uzstādījumi'
	},

	// Link dialog.
	link :
	{
		toolbar		: 'Ievietot/Labot hipersaiti',
		other 		: '<cits>',
		menu		: 'Labot hipersaiti',
		title		: 'Hipersaite',
		info		: 'Hipersaites informācija',
		target		: 'Mērķis',
		upload		: 'Augšupielādēt',
		advanced	: 'Izvērstais',
		type		: 'Hipersaites tips',
		toUrl		: 'Adrese',
		toAnchor	: 'Iezīme šajā lapā',
		toEmail		: 'E-pasts',
		targetFrame		: '<ietvars>',
		targetPopup		: '<uznirstošā logā>',
		targetFrameName	: 'Mērķa ietvara nosaukums',
		targetPopupName	: 'Uznirstošā loga nosaukums',
		popupFeatures	: 'Uznirstošā loga nosaukums īpašības',
		popupResizable	: 'Mērogojams',
		popupStatusBar	: 'Statusa josla',
		popupLocationBar: 'Atrašanās vietas josla',
		popupToolbar	: 'Rīku josla',
		popupMenuBar	: 'Izvēlnes josla',
		popupFullScreen	: 'Pilnā ekrānā (IE)',
		popupScrollBars	: 'Ritjoslas',
		popupDependent	: 'Atkarīgs (Netscape)',
		popupLeft		: 'Kreisā koordināte',
		popupTop		: 'Augšējā koordināte',
		id				: 'ID',
		langDir			: 'Valodas lasīšanas virziens',
		langDirLTR		: 'No kreisās uz labo (LTR)',
		langDirRTL		: 'No labās uz kreiso (RTL)',
		acccessKey		: 'Pieejas taustiņš',
		name			: 'Nosaukums',
		langCode			: 'Valodas kods',
		tabIndex			: 'Ciļņu indekss',
		advisoryTitle		: 'Konsultatīvs virsraksts',
		advisoryContentType	: 'Konsultatīvs satura tips',
		cssClasses		: 'Stilu saraksta klases',
		charset			: 'Pievienotā resursa kodējums',
		styles			: 'Stils',
		rel			: 'Relācija',
		selectAnchor		: 'Izvēlēties iezīmi',
		anchorName		: 'Pēc iezīmes nosaukuma',
		anchorId			: 'Pēc elementa ID',
		emailAddress		: 'E-pasta adrese',
		emailSubject		: 'Ziņas tēma',
		emailBody		: 'Ziņas saturs',
		noAnchors		: '(Šajā dokumentā nav iezīmju)',
		noUrl			: 'Lūdzu norādi hipersaiti',
		noEmail			: 'Lūdzu norādi e-pasta adresi'
	},

	// Anchor dialog
	anchor :
	{
		toolbar		: 'Ievietot/Labot iezīmi',
		menu		: 'Labot iezīmi',
		title		: 'Iezīmes uzstādījumi',
		name		: 'Iezīmes nosaukums',
		errorName	: 'Lūdzu norādiet iezīmes nosaukumu',
		remove		: 'Noņemt iezīmi'
	},

	// List style dialog
	list:
	{
		numberedTitle		: 'Numurēta saraksta uzstādījumi',
		bulletedTitle		: 'Vienkārša saraksta uzstādījumi',
		type				: 'Tips',
		start				: 'Sākt',
		validateStartNumber				:'Saraksta sākuma numuram jābūt veselam skaitlim',
		circle				: 'Aplis',
		disc				: 'Disks',
		square				: 'Kvadrāts',
		none				: 'Nekas',
		notset				: '<nav norādīts>',
		armenian			: 'Armēņu skaitļi',
		georgian			: 'Gruzīņu skaitļi (an, ban, gan, utt)',
		lowerRoman			: 'Mazie romāņu (i, ii, iii, iv, v, utt)',
		upperRoman			: 'Lielie romāņu (I, II, III, IV, V, utt)',
		lowerAlpha			: 'Mazie alfabēta (a, b, c, d, e, utt)',
		upperAlpha			: 'Lielie alfabēta (A, B, C, D, E, utt)',
		lowerGreek			: 'Mazie grieķu (alfa, beta, gamma, utt)',
		decimal				: 'Decimālie (1, 2, 3, utt)',
		decimalLeadingZero	: 'Decimālie ar nulli (01, 02, 03, utt)'
	},

	// Find And Replace Dialog
	findAndReplace :
	{
		title				: 'Meklēt un aizvietot',
		find				: 'Meklēt',
		replace				: 'Nomainīt',
		findWhat			: 'Meklēt:',
		replaceWith			: 'Nomainīt uz:',
		notFoundMsg			: 'Norādītā frāze netika atrasta.',
		findOptions			: 'Meklēt uzstādījumi',
		matchCase			: 'Reģistrjūtīgs',
		matchWord			: 'Jāsakrīt pilnībā',
		matchCyclic			: 'Sakrist cikliski',
		replaceAll			: 'Aizvietot visu',
		replaceSuccessMsg	: '%1 gadījums(i) aizvietoti'
	},

	// Table Dialog
	table :
	{
		toolbar		: 'Tabula',
		title		: 'Tabulas īpašības',
		menu		: 'Tabulas īpašības',
		deleteTable	: 'Dzēst tabulu',
		rows		: 'Rindas',
		columns		: 'Kolonnas',
		border		: 'Rāmja izmērs',
		widthPx		: 'pikseļos',
		widthPc		: 'procentuāli',
		widthUnit	: 'platuma mērvienība',
		cellSpace	: 'Rūtiņu atstatums',
		cellPad		: 'Rūtiņu nobīde',
		caption		: 'Leģenda',
		summary		: 'Anotācija',
		headers		: 'Virsraksti',
		headersNone		: 'Nekas',
		headersColumn	: 'Pirmā kolona',
		headersRow		: 'Pirmā rinda',
		headersBoth		: 'Abi',
		invalidRows		: 'Rindu skaitam jābūt lielākam par 0',
		invalidCols		: 'Kolonu skaitam jābūt lielākam par 0',
		invalidBorder	: 'Rāmju izmēram jābūt skaitlim',
		invalidWidth	: 'Tabulas platumam jābūt skaitlim',
		invalidHeight	: 'Tabulas augstumam jābūt skaitlim',
		invalidCellSpacing	: 'Šūnu atstarpēm jābūt pozitīvam skaitlim',
		invalidCellPadding	: 'Šūnu atkāpēm jābūt pozitīvam skaitlim',

		cell :
		{
			menu			: 'Šūna',
			insertBefore	: 'Pievienot šūnu pirms',
			insertAfter		: 'Pievienot šūnu pēc',
			deleteCell		: 'Dzēst rūtiņas',
			merge			: 'Apvienot rūtiņas',
			mergeRight		: 'Apvieno pa labi',
			mergeDown		: 'Apvienot uz leju',
			splitHorizontal	: 'Sadalīt šūnu horizontāli',
			splitVertical	: 'Sadalīt šūnu vertikāli',
			title			: 'Šūnas uzstādījumi',
			cellType		: 'Šūnas tips',
			rowSpan			: 'Apvienotas rindas',
			colSpan			: 'Apvienotas kolonas',
			wordWrap		: 'Vārdu pārnese',
			hAlign			: 'Horizontālais novietojums',
			vAlign			: 'Vertikālais novietojums',
			alignBaseline	: 'Pamatrinda',
			bgColor			: 'Fona krāsa',
			borderColor		: 'Rāmja krāsa',
			data			: 'Dati',
			header			: 'Virsraksts',
			yes				: 'Jā',
			no				: 'Nē',
			invalidWidth	: 'Šūnas platumam jābūt skaitlim',
			invalidHeight	: 'Šūnas augstumam jābūt skaitlim',
			invalidRowSpan	: 'Apvienojamo rindu skaitam jābūt veselam skaitlim',
			invalidColSpan	: 'Apvienojamo kolonu skaitam jābūt veselam skaitlim',
			chooseColor		: 'Izvēlēties'
		},

		row :
		{
			menu			: 'Rinda',
			insertBefore	: 'Ievietot rindu pirms',
			insertAfter		: 'Ievietot rindu pēc',
			deleteRow		: 'Dzēst rindas'
		},

		column :
		{
			menu			: 'Kolonna',
			insertBefore	: 'Ievietot kolonu pirms',
			insertAfter		: 'Ievieto kolonu pēc',
			deleteColumn	: 'Dzēst kolonnas'
		}
	},

	// Button Dialog.
	button :
	{
		title		: 'Pogas īpašības',
		text		: 'Teksts (vērtība)',
		type		: 'Tips',
		typeBtn		: 'Poga',
		typeSbm		: 'Nosūtīt',
		typeRst		: 'Atcelt'
	},

	// Checkbox and Radio Button Dialogs.
	checkboxAndRadio :
	{
		checkboxTitle : 'Atzīmēšanas kastītes īpašības',
		radioTitle	: 'Izvēles poga īpašības',
		value		: 'Vērtība',
		selected	: 'Iezīmēts'
	},

	// Form Dialog.
	form :
	{
		title		: 'Formas īpašības',
		menu		: 'Formas īpašības',
		action		: 'Darbība',
		method		: 'Metode',
		encoding	: 'Kodējums'
	},

	// Select Field Dialog.
	select :
	{
		title		: 'Iezīmēšanas lauka īpašības',
		selectInfo	: 'Informācija',
		opAvail		: 'Pieejamās iespējas',
		value		: 'Vērtība',
		size		: 'Izmērs',
		lines		: 'rindas',
		chkMulti	: 'Atļaut vairākus iezīmējumus',
		opText		: 'Teksts',
		opValue		: 'Vērtība',
		btnAdd		: 'Pievienot',
		btnModify	: 'Veikt izmaiņas',
		btnUp		: 'Augšup',
		btnDown		: 'Lejup',
		btnSetValue : 'Noteikt kā iezīmēto vērtību',
		btnDelete	: 'Dzēst'
	},

	// Textarea Dialog.
	textarea :
	{
		title		: 'Teksta laukuma īpašības',
		cols		: 'Kolonnas',
		rows		: 'Rindas'
	},

	// Text Field Dialog.
	textfield :
	{
		title		: 'Teksta rindas  īpašības',
		name		: 'Nosaukums',
		value		: 'Vērtība',
		charWidth	: 'Simbolu platums',
		maxChars	: 'Simbolu maksimālais daudzums',
		type		: 'Tips',
		typeText	: 'Teksts',
		typePass	: 'Parole'
	},

	// Hidden Field Dialog.
	hidden :
	{
		title	: 'Paslēptās teksta rindas īpašības',
		name	: 'Nosaukums',
		value	: 'Vērtība'
	},

	// Image Dialog.
	image :
	{
		title		: 'Attēla īpašības',
		titleButton	: 'Attēlpogas īpašības',
		menu		: 'Attēla īpašības',
		infoTab		: 'Informācija par attēlu',
		btnUpload	: 'Nosūtīt serverim',
		upload		: 'Augšupielādēt',
		alt			: 'Alternatīvais teksts',
		lockRatio	: 'Nemainīga Augstuma/Platuma attiecība',
		resetSize	: 'Atjaunot sākotnējo izmēru',
		border		: 'Rāmis',
		hSpace		: 'Horizontālā telpa',
		vSpace		: 'Vertikālā telpa',
		alertUrl	: 'Lūdzu norādīt attēla hipersaiti',
		linkTab		: 'Hipersaite',
		button2Img	: 'Vai vēlaties pārveidot izvēlēto attēla pogu uz attēla?',
		img2Button	: 'Vai vēlaties pārveidot izvēlēto attēlu uz attēla pogas?',
		urlMissing	: 'Trūkst attēla atrašanās adrese.',
		validateBorder	: 'Apmalei jābūt veselam skaitlim',
		validateHSpace	: 'HSpace jābūt veselam skaitlim',
		validateVSpace	: 'VSpace jābūt veselam skaitlim'
	},

	// Flash Dialog
	flash :
	{
		properties		: 'Flash īpašības',
		propertiesTab	: 'Uzstādījumi',
		title			: 'Flash īpašības',
		chkPlay			: 'Automātiska atskaņošana',
		chkLoop			: 'Nepārtraukti',
		chkMenu			: 'Atļaut Flash izvēlni',
		chkFull			: 'Pilnekrāns',
 		scale			: 'Mainīt izmēru',
		scaleAll		: 'Rādīt visu',
		scaleNoBorder	: 'Bez rāmja',
		scaleFit		: 'Precīzs izmērs',
		access			: 'Skripta pieeja',
		accessAlways	: 'Vienmēr',
		accessSameDomain: 'Tas pats domēns',
		accessNever		: 'Nekad',
		alignAbsBottom	: 'Absolūti apakšā',
		alignAbsMiddle	: 'Absolūti vertikāli centrēts',
		alignBaseline	: 'Pamatrindā',
		alignTextTop	: 'Teksta augšā',
		quality			: 'Kvalitāte',
		qualityBest		: 'Labākā',
		qualityHigh		: 'Augsta',
		qualityAutoHigh	: 'Automātiski Augsta',
		qualityMedium	: 'Vidēja',
		qualityAutoLow	: 'Automātiski Zema',
		qualityLow		: 'Zema',
		windowModeWindow: 'Logs',
		windowModeOpaque: 'Necaurspīdīgs',
		windowModeTransparent : 'Caurspīdīgs',
		windowMode		: 'Loga režīms',
		flashvars		: 'Flash mainīgie',
		bgcolor			: 'Fona krāsa',
		hSpace			: 'Horizontālā telpa',
		vSpace			: 'Vertikālā telpa',
		validateSrc		: 'Lūdzu norādi hipersaiti',
		validateHSpace	: 'Hspace jābūt skaitlim',
		validateVSpace	: 'Vspace jābūt skaitlim'
	},

	// Speller Pages Dialog
	spellCheck :
	{
		toolbar			: 'Pareizrakstības pārbaude',
		title			: 'Pārbaudīt gramatiku',
		notAvailable	: 'Atvainojiet, bet serviss šobrīd nav pieejams.',
		errorLoading	: 'Kļūda ielādējot aplikācijas servisa adresi: %s.',
		notInDic		: 'Netika atrasts vārdnīcā',
		changeTo		: 'Nomainīt uz',
		btnIgnore		: 'Ignorēt',
		btnIgnoreAll	: 'Ignorēt visu',
		btnReplace		: 'Aizvietot',
		btnReplaceAll	: 'Aizvietot visu',
		btnUndo			: 'Atcelt',
		noSuggestions	: '- Nav ieteikumu -',
		progress		: 'Notiek pareizrakstības pārbaude...',
		noMispell		: 'Pareizrakstības pārbaude pabeigta: kļūdas netika atrastas',
		noChanges		: 'Pareizrakstības pārbaude pabeigta: nekas netika labots',
		oneChange		: 'Pareizrakstības pārbaude pabeigta: 1 vārds izmainīts',
		manyChanges		: 'Pareizrakstības pārbaude pabeigta: %1 vārdi tika mainīti',
		ieSpellDownload	: 'Pareizrakstības pārbaudītājs nav pievienots. Vai vēlaties to lejupielādēt tagad?'
	},

	smiley :
	{
		toolbar	: 'Smaidiņi',
		title	: 'Ievietot smaidiņu',
		options : 'Smaidiņu uzstādījumi'
	},

	elementsPath :
	{
		eleLabel : 'Elementa ceļš',
		eleTitle : '%1 elements'
	},

	numberedlist	: 'Numurēts saraksts',
	bulletedlist	: 'Pievienot/Noņemt vienkāršu sarakstu',
	indent			: 'Palielināt atkāpi',
	outdent			: 'Samazināt atkāpi',

	justify :
	{
		left	: 'Izlīdzināt pa kreisi',
		center	: 'Izlīdzināt pret centru',
		right	: 'Izlīdzināt pa labi',
		block	: 'Izlīdzināt malas'
	},

	blockquote : 'Bloka citāts',

	clipboard :
	{
		title		: 'Ievietot',
		cutError	: 'Jūsu pārlūkprogrammas drošības iestatījumi nepieļauj redaktoram automātiski veikt izgriezšanas darbību.  Lūdzu, izmantojiet (Ctrl/Cmd+X), lai veiktu šo darbību.',
		copyError	: 'Jūsu pārlūkprogrammas drošības iestatījumi nepieļauj redaktoram automātiski veikt kopēšanas darbību.  Lūdzu, izmantojiet (Ctrl/Cmd+C), lai veiktu šo darbību.',
		pasteMsg	: 'Lūdzu, ievietojiet tekstu šajā laukumā, izmantojot klaviatūru (<STRONG>Ctrl/Cmd+V</STRONG>) un apstipriniet ar <STRONG>Darīts!</STRONG>.',
		securityMsg	: 'Jūsu pārlūka drošības uzstādījumu dēļ, nav iespējams tieši piekļūt jūsu starpliktuvei. Jums jāielīmē atkārtoti šajā logā.',
		pasteArea	: 'Ielīmēšanas zona'
	},

	pastefromword :
	{
		confirmCleanup	: 'Teksts, kuru vēlaties ielīmēt, izskatās ir nokopēts no Word. Vai vēlaties to iztīrīt pirms ielīmēšanas?',
		toolbar			: 'Ievietot no Worda',
		title			: 'Ievietot no Worda',
		error			: 'Iekšējas kļūdas dēļ, neizdevās iztīrīt ielīmētos datus.'
	},

	pasteText :
	{
		button	: 'Ievietot kā vienkāršu tekstu',
		title	: 'Ievietot kā vienkāršu tekstu'
	},

	templates :
	{
		button			: 'Sagataves',
		title			: 'Satura sagataves',
		options : 'Sagataves uzstādījumi',
		insertOption	: 'Aizvietot pašreizējo saturu',
		selectPromptMsg	: 'Lūdzu, norādiet sagatavi, ko atvērt editorā<br>(patreizējie dati tiks zaudēti):',
		emptyListMsg	: '(Nav norādītas sagataves)'
	},

	showBlocks : 'Parādīt blokus',

	stylesCombo :
	{
		label		: 'Stils',
		panelTitle	: 'Formatēšanas stili',
		panelTitle1	: 'Bloka stili',
		panelTitle2	: 'iekļautie stili',
		panelTitle3	: 'Objekta stili'
	},

	format :
	{
		label		: 'Formāts',
		panelTitle	: 'Formāts',

		tag_p		: 'Normāls teksts',
		tag_pre		: 'Formatēts teksts',
		tag_address	: 'Adrese',
		tag_h1		: 'Virsraksts 1',
		tag_h2		: 'Virsraksts 2',
		tag_h3		: 'Virsraksts 3',
		tag_h4		: 'Virsraksts 4',
		tag_h5		: 'Virsraksts 5',
		tag_h6		: 'Virsraksts 6',
		tag_div		: 'Rindkopa (DIV)'
	},

	div :
	{
		title				: 'Izveidot div konteineri',
		toolbar				: 'Izveidot div konteineri',
		cssClassInputLabel	: 'Stilu klases',
		styleSelectLabel	: 'Stils',
		IdInputLabel		: 'Id',
		languageCodeInputLabel	: 'Valodas kods',
		inlineStyleInputLabel	: 'Iekļautais stils',
		advisoryTitleInputLabel	: 'Konsultatīvs virsraksts',
		langDirLabel		: 'Valodas virziens',
		langDirLTRLabel		: 'Kreisais uz Labo (LTR)',
		langDirRTLLabel		: 'Labais uz kreiso (RTL)',
		edit				: 'Labot Div',
		remove				: 'Noņemt Div'
  	},

	iframe :
	{
		title		: 'IFrame uzstādījumi',
		toolbar		: 'IFrame',
		noUrl		: 'Norādiet iframe adresi',
		scrolling	: 'Atļaut ritjoslas',
		border		: 'Rādīt rāmi'
	},

	font :
	{
		label		: 'Šrifts',
		voiceLabel	: 'Fonts',
		panelTitle	: 'Šrifts'
	},

	fontSize :
	{
		label		: 'Izmērs',
		voiceLabel	: 'Fonta izmeŗs',
		panelTitle	: 'Izmērs'
	},

	colorButton :
	{
		textColorTitle	: 'Teksta krāsa',
		bgColorTitle	: 'Fona krāsa',
		panelTitle		: 'Krāsa',
		auto			: 'Automātiska',
		more			: 'Plašāka palete...'
	},

	colors :
	{
		'000' : 'Melns',
		'800000' : 'Sarkanbrūns',
		'8B4513' : 'Sedlu brūns',
		'2F4F4F' : 'Tumšas tāfeles pelēks',
		'008080' : 'Zili-zaļš',
		'000080' : 'Jūras',
		'4B0082' : 'Indigo',
		'696969' : 'Tumši pelēks',
		'B22222' : 'Ķieģeļsarkans',
		'A52A2A' : 'Brūns',
		'DAA520' : 'Zelta',
		'006400' : 'Tumši zaļš',
		'40E0D0' : 'Tirkīzs',
		'0000CD' : 'Vidēji zils',
		'800080' : 'Purpurs',
		'808080' : 'Pelēks',
		'F00' : 'Sarkans',
		'FF8C00' : 'Tumši oranžs',
		'FFD700' : 'Zelta',
		'008000' : 'Zaļš',
		'0FF' : 'Tumšzils',
		'00F' : 'Zils',
		'EE82EE' : 'Violets',
		'A9A9A9' : 'Pelēks',
		'FFA07A' : 'Gaiši laškrāsas',
		'FFA500' : 'Oranžs',
		'FFFF00' : 'Dzeltens',
		'00FF00' : 'Laima',
		'AFEEEE' : 'Gaiši tirkīza',
		'ADD8E6' : 'Gaiši zils',
		'DDA0DD' : 'Plūmju',
		'D3D3D3' : 'Gaiši pelēks',
		'FFF0F5' : 'Lavandas sārts',
		'FAEBD7' : 'Antīki balts',
		'FFFFE0' : 'Gaiši dzeltens',
		'F0FFF0' : 'Meduspile',
		'F0FFFF' : 'Debesszils',
		'F0F8FF' : 'Alises zils',
		'E6E6FA' : 'Lavanda',
		'FFF' : 'Balts'
	},

	scayt :
	{
		title			: 'Pārbaudīt gramatiku rakstot',
		opera_title		: 'Opera neatbalsta',
		enable			: 'Ieslēgt SCAYT',
		disable			: 'Atslēgt SCAYT',
		about			: 'Par SCAYT',
		toggle			: 'Pārslēgt SCAYT',
		options			: 'Uzstādījumi',
		langs			: 'Valodas',
		moreSuggestions	: 'Vairāk ieteikumi',
		ignore			: 'Ignorēt',
		ignoreAll		: 'Ignorēt visu',
		addWord			: 'Pievienot vārdu',
		emptyDic		: 'Vārdnīcas nosaukums nevar būt tukšs.',
		noSuggestions	: 'No suggestions', // MISSING
		optionsTab		: 'Uzstādījumi',
		allCaps			: 'Ignorēt vārdus ar lielajiem burtiem',
		ignoreDomainNames : 'Ignorēt domēnu nosaukumus',
		mixedCase		: 'Ignorēt vārdus ar jauktu reģistru burtiem',
		mixedWithDigits	: 'Ignorēt vārdus ar skaitļiem',

		languagesTab	: 'Valodas',

		dictionariesTab	: 'Vārdnīcas',
		dic_field_name	: 'Vārdnīcas nosaukums',
		dic_create		: 'Izveidot',
		dic_restore		: 'Atjaunot',
		dic_delete		: 'Dzēst',
		dic_rename		: 'Pārsaukt',
		dic_info		: 'Sākumā lietotāja vārdnīca tiek glabāta Cookie. Diemžēl, Cookie ir ierobežots izmērs. Kad vārdnīca sasniegs izmēru, ka to vairs nevar glabāt Cookie, tā tiks noglabāta uz servera. Lai saglabātu personīgo vārdnīcu uz jūsu servera, jums jānorāda tās nosaukums. Ja jūs jau esiet noglabājuši vārdnīcu, lūdzu ierakstiet tās nosaukum un nospiediet Atjaunot pogu.',

		aboutTab		: 'Par'
	},

	about :
	{
		title		: 'Par CKEditor',
		dlgTitle	: 'Par CKEditor',
		help	: 'Pārbaudiet $1 palīdzībai.',
		userGuide : 'CKEditor Lietotāja pamācība',
		moreInfo	: 'Informācijai par licenzēšanu apmeklējiet mūsu mājas lapu:',
		copy		: 'Kopēšanas tiesības &copy; $1. Visas tiesības rezervētas.'
	},

	maximize : 'Maksimizēt',
	minimize : 'Minimizēt',

	fakeobjects :
	{
		anchor		: 'Iezīme',
		flash		: 'Flash animācija',
		iframe		: 'Iframe',
		hiddenfield	: 'Slēpts lauks',
		unknown		: 'Nezināms objekts'
	},

	resize : 'Velciet lai mērogotu',

	colordialog :
	{
		title		: 'Izvēlies krāsu',
		options	:	'Krāsas uzstādījumi',
		highlight	: 'Paraugs',
		selected	: 'Izvēlētā krāsa',
		clear		: 'Notīrīt'
	},

	toolbarCollapse	: 'Aizvērt rīkjoslu',
	toolbarExpand	: 'Atvērt rīkjoslu',

	toolbarGroups :
	{
		document : 'Dokuments',
		clipboard : 'Starpliktuve/Atcelt',
		editing : 'Labošana',
		forms : 'Formas',
		basicstyles : 'Pamata stili',
		paragraph : 'Paragrāfs',
		links : 'Saites',
		insert : 'Ievietot',
		styles : 'Stili',
		colors : 'Krāsas',
		tools : 'Rīki'
	},

	bidi :
	{
		ltr : 'Teksta virziens no kreisās uz labo',
		rtl : 'Teksta virziens no labās uz kreiso'
	},

	docprops :
	{
		label : 'Dokumenta īpašības',
		title : 'Dokumenta īpašības',
		design : 'Dizains',
		meta : 'META dati',
		chooseColor : 'Izvēlēties',
		other : '<cits>',
		docTitle :	'Dokumenta virsraksts <Title>',
		charset : 	'Simbolu kodējums',
		charsetOther : 'Cits simbolu kodējums',
		charsetASCII : 'ASCII',
		charsetCE : 'Centrāleiropas',
		charsetCT : 'Ķīniešu tradicionālā (Big5)',
		charsetCR : 'Kirilica',
		charsetGR : 'Grieķu',
		charsetJP : 'Japāņu',
		charsetKR : 'Korejiešu',
		charsetTR : 'Turku',
		charsetUN : 'Unikods (UTF-8)',
		charsetWE : 'Rietumeiropas',
		docType : 'Dokumenta tips',
		docTypeOther : 'Cits dokumenta tips',
		xhtmlDec : 'Ietvert XHTML deklarācijas',
		bgColor : 'Fona krāsa',
		bgImage : 'Fona attēla hipersaite',
		bgFixed : 'Fona attēls ir fiksēts',
		txtColor : 'Teksta krāsa',
		margin : 'Lapas robežas',
		marginTop : 'Augšā',
		marginLeft : 'Pa kreisi',
		marginRight : 'Pa labi',
		marginBottom : 'Apakšā',
		metaKeywords : 'Dokumentu aprakstoši atslēgvārdi (atdalīti ar komatu)',
		metaDescription : 'Dokumenta apraksts',
		metaAuthor : 'Autors',
		metaCopyright : 'Autortiesības',
		previewHtml : '<p>Šis ir <strong>parauga teksts</strong>. Jūs izmantojiet <a href="javascript:void(0)">CKEditor</a>.</p>'
	}
};
