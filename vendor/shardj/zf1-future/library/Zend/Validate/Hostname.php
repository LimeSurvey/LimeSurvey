<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * @see Zend_Validate_Ip
 */
require_once 'Zend/Validate/Ip.php';

/**
 * Please note there are two standalone test scripts for testing IDN characters due to problems
 * with file encoding.
 *
 * The first is tests/Zend/Validate/HostnameTestStandalone.php which is designed to be run on
 * the command line.
 *
 * The second is tests/Zend/Validate/HostnameTestForm.php which is designed to be run via HTML
 * to allow users to test entering UTF-8 characters in a form.
 *
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Hostname extends Zend_Validate_Abstract
{
    public const CANNOT_DECODE_PUNYCODE  = 'hostnameCannotDecodePunycode';
    public const INVALID                 = 'hostnameInvalid';
    public const INVALID_DASH            = 'hostnameDashCharacter';
    public const INVALID_HOSTNAME        = 'hostnameInvalidHostname';
    public const INVALID_HOSTNAME_SCHEMA = 'hostnameInvalidHostnameSchema';
    public const INVALID_LOCAL_NAME      = 'hostnameInvalidLocalName';
    public const INVALID_URI             = 'hostnameInvalidUri';
    public const IP_ADDRESS_NOT_ALLOWED  = 'hostnameIpAddressNotAllowed';
    public const LOCAL_NAME_NOT_ALLOWED  = 'hostnameLocalNameNotAllowed';
    public const UNDECIPHERABLE_TLD      = 'hostnameUndecipherableTld';
    public const UNKNOWN_TLD             = 'hostnameUnknownTld';

    /**
     * @var array
     */
    protected $_messageTemplates = [
        self::CANNOT_DECODE_PUNYCODE  => "'%value%' appears to be a DNS hostname but the given punycode notation cannot be decoded",
        self::INVALID                 => "Invalid type given. String expected",
        self::INVALID_DASH            => "'%value%' appears to be a DNS hostname but contains a dash in an invalid position",
        self::INVALID_HOSTNAME        => "'%value%' does not match the expected structure for a DNS hostname",
        self::INVALID_HOSTNAME_SCHEMA => "'%value%' appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'",
        self::INVALID_LOCAL_NAME      => "'%value%' does not appear to be a valid local network name",
        self::INVALID_URI             => "'%value%' does not appear to be a valid URI hostname",
        self::IP_ADDRESS_NOT_ALLOWED  => "'%value%' appears to be an IP address, but IP addresses are not allowed",
        self::LOCAL_NAME_NOT_ALLOWED  => "'%value%' appears to be a local network name but local network names are not allowed",
        self::UNDECIPHERABLE_TLD      => "'%value%' appears to be a DNS hostname but cannot extract TLD part",
        self::UNKNOWN_TLD             => "'%value%' appears to be a DNS hostname but cannot match TLD against known list",
    ];

    /**
     * @var array
     */
    protected $_messageVariables = [
        'tld' => '_tld'
    ];

    /**
     * Allows Internet domain names (e.g., example.com)
     */
    public const ALLOW_DNS   = 1;

    /**
     * Allows IP addresses
     */
    public const ALLOW_IP    = 2;

    /**
     * Allows local network names (e.g., localhost, www.localdomain)
     */
    public const ALLOW_LOCAL = 4;

    /**
     * Allows all types of hostnames
     */
    public const ALLOW_URI = 8;

    /**
     * Allows all types of hostnames
     */
    public const ALLOW_ALL = 15;

    /**
     * Array of valid top-level-domains
     *
     * Version 2026110200, Last Updated Mon Nov  2 07:07:01 2026 UTC
     *
     * @see http://data.iana.org/TLD/tlds-alpha-by-domain.txt  List of all TLDs by domain
     * @see http://www.iana.org/domains/root/db/ Official list of supported TLDs
     * @var array
     */
    protected $_validTlds = [
        'aaa',
        'aarp',
        'abb',
        'abbott',
        'abbvie',
        'abc',
        'able',
        'abogado',
        'abudhabi',
        'ac',
        'academy',
        'accenture',
        'accountant',
        'accountants',
        'aco',
        'actor',
        'ad',
        'ads',
        'adult',
        'ae',
        'aeg',
        'aero',
        'aetna',
        'af',
        'afl',
        'africa',
        'ag',
        'agakhan',
        'agency',
        'ai',
        'aig',
        'airbus',
        'airforce',
        'airtel',
        'akdn',
        'al',
        'alibaba',
        'alipay',
        'allfinanz',
        'allstate',
        'ally',
        'alsace',
        'alstom',
        'am',
        'amazon',
        'americanexpress',
        'americanfamily',
        'amex',
        'amfam',
        'amica',
        'amsterdam',
        'analytics',
        'android',
        'anquan',
        'anz',
        'ao',
        'aol',
        'apartments',
        'app',
        'apple',
        'aq',
        'aquarelle',
        'ar',
        'arab',
        'aramco',
        'archi',
        'army',
        'arpa',
        'art',
        'arte',
        'as',
        'asda',
        'asia',
        'associates',
        'at',
        'athleta',
        'attorney',
        'au',
        'auction',
        'audi',
        'audible',
        'audio',
        'auspost',
        'author',
        'auto',
        'autos',
        'aw',
        'aws',
        'ax',
        'axa',
        'az',
        'azure',
        'ba',
        'baby',
        'baidu',
        'banamex',
        'band',
        'bank',
        'bar',
        'barcelona',
        'barclaycard',
        'barclays',
        'barefoot',
        'bargains',
        'baseball',
        'basketball',
        'bauhaus',
        'bayern',
        'bb',
        'bbc',
        'bbt',
        'bbva',
        'bcg',
        'bcn',
        'bd',
        'be',
        'beats',
        'beauty',
        'beer',
        'berlin',
        'best',
        'bestbuy',
        'bet',
        'bf',
        'bg',
        'bh',
        'bharti',
        'bi',
        'bible',
        'bid',
        'bike',
        'bing',
        'bingo',
        'bio',
        'biz',
        'bj',
        'black',
        'blackfriday',
        'blockbuster',
        'blog',
        'bloomberg',
        'blue',
        'bm',
        'bms',
        'bmw',
        'bn',
        'bnpparibas',
        'bo',
        'boats',
        'boehringer',
        'bofa',
        'bom',
        'bond',
        'boo',
        'book',
        'booking',
        'bosch',
        'bostik',
        'boston',
        'bot',
        'boutique',
        'box',
        'br',
        'bradesco',
        'bridgestone',
        'broadway',
        'broker',
        'brother',
        'brussels',
        'bs',
        'bt',
        'build',
        'builders',
        'business',
        'buy',
        'buzz',
        'bv',
        'bw',
        'by',
        'bz',
        'bzh',
        'ca',
        'cab',
        'cafe',
        'cal',
        'call',
        'calvinklein',
        'cam',
        'camera',
        'camp',
        'canon',
        'capetown',
        'capital',
        'capitalone',
        'car',
        'caravan',
        'cards',
        'care',
        'career',
        'careers',
        'cars',
        'casa',
        'case',
        'cash',
        'casino',
        'cat',
        'catering',
        'catholic',
        'cba',
        'cbn',
        'cbre',
        'cc',
        'cd',
        'center',
        'ceo',
        'cern',
        'cf',
        'cfa',
        'cfd',
        'cg',
        'ch',
        'chanel',
        'channel',
        'charity',
        'chase',
        'chat',
        'cheap',
        'chintai',
        'christmas',
        'chrome',
        'church',
        'ci',
        'cipriani',
        'circle',
        'cisco',
        'citadel',
        'citi',
        'citic',
        'city',
        'ck',
        'cl',
        'claims',
        'cleaning',
        'click',
        'clinic',
        'clinique',
        'clothing',
        'cloud',
        'club',
        'clubmed',
        'cm',
        'cn',
        'co',
        'coach',
        'codes',
        'coffee',
        'college',
        'cologne',
        'com',
        'commbank',
        'community',
        'company',
        'compare',
        'computer',
        'comsec',
        'condos',
        'construction',
        'consulting',
        'contact',
        'contractors',
        'cooking',
        'cool',
        'coop',
        'corsica',
        'country',
        'coupon',
        'coupons',
        'courses',
        'cpa',
        'cr',
        'credit',
        'creditcard',
        'creditunion',
        'cricket',
        'crown',
        'crs',
        'cruise',
        'cruises',
        'cu',
        'cuisinella',
        'cv',
        'cw',
        'cx',
        'cy',
        'cymru',
        'cyou',
        'cz',
        'dad',
        'dance',
        'data',
        'date',
        'dating',
        'datsun',
        'day',
        'dclk',
        'dds',
        'de',
        'deal',
        'dealer',
        'deals',
        'degree',
        'delivery',
        'dell',
        'deloitte',
        'delta',
        'democrat',
        'dental',
        'dentist',
        'desi',
        'design',
        'dev',
        'dhl',
        'diamonds',
        'diet',
        'digital',
        'direct',
        'directory',
        'discount',
        'discover',
        'dish',
        'diy',
        'dj',
        'dk',
        'dm',
        'dnp',
        'do',
        'docs',
        'doctor',
        'dog',
        'domains',
        'dot',
        'download',
        'drive',
        'dtv',
        'dubai',
        'dupont',
        'durban',
        'dvag',
        'dvr',
        'dz',
        'earth',
        'eat',
        'ec',
        'eco',
        'edeka',
        'edu',
        'education',
        'ee',
        'eg',
        'email',
        'emerck',
        'energy',
        'engineer',
        'engineering',
        'enterprises',
        'epson',
        'equipment',
        'er',
        'ericsson',
        'erni',
        'es',
        'esq',
        'estate',
        'et',
        'eu',
        'eurovision',
        'eus',
        'events',
        'exchange',
        'expert',
        'exposed',
        'express',
        'extraspace',
        'fage',
        'fail',
        'fairwinds',
        'faith',
        'family',
        'fan',
        'fans',
        'farm',
        'farmers',
        'fashion',
        'fast',
        'fedex',
        'feedback',
        'ferrari',
        'ferrero',
        'fi',
        'fidelity',
        'fido',
        'film',
        'final',
        'finance',
        'financial',
        'fire',
        'firestone',
        'firmdale',
        'fish',
        'fishing',
        'fit',
        'fitness',
        'fj',
        'fk',
        'flickr',
        'flights',
        'flir',
        'florist',
        'flowers',
        'fly',
        'fm',
        'fo',
        'foo',
        'food',
        'football',
        'ford',
        'forex',
        'forsale',
        'forum',
        'foundation',
        'fox',
        'fr',
        'free',
        'fresenius',
        'frl',
        'frogans',
        'frontier',
        'ftr',
        'fujitsu',
        'fun',
        'fund',
        'furniture',
        'futbol',
        'fyi',
        'ga',
        'gal',
        'gallery',
        'gallo',
        'gallup',
        'game',
        'games',
        'gap',
        'garden',
        'gay',
        'gb',
        'gbiz',
        'gd',
        'gdn',
        'ge',
        'gea',
        'gent',
        'genting',
        'george',
        'gf',
        'gg',
        'ggee',
        'gh',
        'gi',
        'gift',
        'gifts',
        'gives',
        'giving',
        'gl',
        'glass',
        'gle',
        'global',
        'globo',
        'gm',
        'gmail',
        'gmbh',
        'gmo',
        'gmx',
        'gn',
        'godaddy',
        'gold',
        'goldpoint',
        'golf',
        'goodyear',
        'goog',
        'google',
        'gop',
        'got',
        'gov',
        'gp',
        'gq',
        'gr',
        'grainger',
        'graphics',
        'gratis',
        'green',
        'gripe',
        'grocery',
        'group',
        'gs',
        'gt',
        'gu',
        'gucci',
        'guge',
        'guide',
        'guitars',
        'guru',
        'gw',
        'gy',
        'hair',
        'hamburg',
        'hangout',
        'haus',
        'hbo',
        'hdfc',
        'hdfcbank',
        'health',
        'healthcare',
        'help',
        'here',
        'hermes',
        'hiphop',
        'hisamitsu',
        'hitachi',
        'hiv',
        'hk',
        'hkt',
        'hm',
        'hn',
        'hockey',
        'holdings',
        'holiday',
        'homedepot',
        'homegoods',
        'homes',
        'homesense',
        'honda',
        'horse',
        'hospital',
        'host',
        'hosting',
        'hot',
        'hotels',
        'hotmail',
        'house',
        'how',
        'hr',
        'hsbc',
        'ht',
        'hu',
        'hughes',
        'hyatt',
        'hyundai',
        'ibm',
        'icbc',
        'ice',
        'icu',
        'id',
        'ie',
        'ieee',
        'ifm',
        'ikano',
        'il',
        'im',
        'imamat',
        'imdb',
        'immo',
        'immobilien',
        'in',
        'inc',
        'industries',
        'infiniti',
        'info',
        'ing',
        'ink',
        'institute',
        'insurance',
        'insure',
        'int',
        'international',
        'intuit',
        'investments',
        'io',
        'ipiranga',
        'iq',
        'ir',
        'irish',
        'is',
        'ismaili',
        'ist',
        'istanbul',
        'it',
        'itau',
        'itv',
        'jaguar',
        'java',
        'jcb',
        'je',
        'jeep',
        'jetzt',
        'jewelry',
        'jio',
        'jll',
        'jm',
        'jmp',
        'jnj',
        'jo',
        'jobs',
        'joburg',
        'jot',
        'joy',
        'jp',
        'jpmorgan',
        'jprs',
        'juegos',
        'kaufen',
        'kddi',
        'ke',
        'kerryhotels',
        'kerryproperties',
        'kfh',
        'kg',
        'kh',
        'ki',
        'kia',
        'kids',
        'kim',
        'kindle',
        'kitchen',
        'kiwi',
        'km',
        'kn',
        'koeln',
        'komatsu',
        'kosher',
        'kp',
        'kpmg',
        'kpn',
        'kr',
        'krd',
        'kred',
        'kuokgroup',
        'kw',
        'ky',
        'kyoto',
        'kz',
        'la',
        'lacaixa',
        'lamborghini',
        'lamer',
        'land',
        'landrover',
        'lanxess',
        'lasalle',
        'lat',
        'latino',
        'latrobe',
        'law',
        'lawyer',
        'lb',
        'lc',
        'lds',
        'lease',
        'leclerc',
        'lefrak',
        'legal',
        'lego',
        'lexus',
        'lgbt',
        'li',
        'lidl',
        'life',
        'lifeinsurance',
        'lifestyle',
        'lighting',
        'like',
        'lilly',
        'limited',
        'limo',
        'lincoln',
        'link',
        'live',
        'living',
        'lk',
        'llc',
        'llp',
        'loan',
        'loans',
        'locker',
        'locus',
        'lol',
        'london',
        'lotte',
        'lotto',
        'love',
        'lpl',
        'lplfinancial',
        'lr',
        'ls',
        'lt',
        'ltd',
        'ltda',
        'lu',
        'lundbeck',
        'luxe',
        'luxury',
        'lv',
        'ly',
        'ma',
        'madrid',
        'maif',
        'maison',
        'makeup',
        'man',
        'management',
        'mango',
        'map',
        'market',
        'marketing',
        'markets',
        'marriott',
        'marshalls',
        'mattel',
        'mba',
        'mc',
        'mckinsey',
        'md',
        'me',
        'med',
        'media',
        'meet',
        'melbourne',
        'meme',
        'memorial',
        'men',
        'menu',
        'merck',
        'merckmsd',
        'mg',
        'mh',
        'miami',
        'microsoft',
        'mil',
        'mini',
        'mint',
        'mit',
        'mitsubishi',
        'mk',
        'ml',
        'mlb',
        'mls',
        'mm',
        'mma',
        'mn',
        'mo',
        'mobi',
        'mobile',
        'moda',
        'moe',
        'moi',
        'mom',
        'monash',
        'money',
        'monster',
        'mormon',
        'mortgage',
        'moscow',
        'moto',
        'motorcycles',
        'mov',
        'movie',
        'mp',
        'mq',
        'mr',
        'ms',
        'msd',
        'mt',
        'mtn',
        'mtr',
        'mu',
        'museum',
        'music',
        'mv',
        'mw',
        'mx',
        'my',
        'mz',
        'na',
        'nab',
        'nagoya',
        'name',
        'navy',
        'nba',
        'nc',
        'ne',
        'nec',
        'net',
        'netbank',
        'netflix',
        'network',
        'neustar',
        'new',
        'news',
        'next',
        'nextdirect',
        'nexus',
        'nf',
        'nfl',
        'ng',
        'ngo',
        'nhk',
        'ni',
        'nico',
        'nike',
        'nikon',
        'ninja',
        'nissan',
        'nissay',
        'nl',
        'no',
        'nokia',
        'norton',
        'now',
        'nowruz',
        'nowtv',
        'np',
        'nr',
        'nra',
        'nrw',
        'ntt',
        'nu',
        'nyc',
        'nz',
        'obi',
        'observer',
        'office',
        'okinawa',
        'olayan',
        'olayangroup',
        'ollo',
        'om',
        'omega',
        'one',
        'ong',
        'onl',
        'online',
        'ooo',
        'open',
        'oracle',
        'orange',
        'org',
        'organic',
        'origins',
        'osaka',
        'otsuka',
        'ott',
        'ovh',
        'pa',
        'page',
        'panasonic',
        'paris',
        'pars',
        'partners',
        'parts',
        'party',
        'pay',
        'pccw',
        'pe',
        'pet',
        'pf',
        'pfizer',
        'pg',
        'ph',
        'pharmacy',
        'phd',
        'philips',
        'phone',
        'photo',
        'photography',
        'photos',
        'physio',
        'pics',
        'pictet',
        'pictures',
        'pid',
        'pin',
        'ping',
        'pink',
        'pioneer',
        'pizza',
        'pk',
        'pl',
        'place',
        'play',
        'playstation',
        'plumbing',
        'plus',
        'pm',
        'pn',
        'pnc',
        'pohl',
        'poker',
        'politie',
        'porn',
        'post',
        'pr',
        'praxi',
        'press',
        'prime',
        'pro',
        'prod',
        'productions',
        'prof',
        'progressive',
        'promo',
        'properties',
        'property',
        'protection',
        'pru',
        'prudential',
        'ps',
        'pt',
        'pub',
        'pw',
        'pwc',
        'py',
        'qa',
        'qpon',
        'quebec',
        'quest',
        'racing',
        'radio',
        're',
        'read',
        'realestate',
        'realtor',
        'realty',
        'recipes',
        'red',
        'redumbrella',
        'rehab',
        'reise',
        'reisen',
        'reit',
        'reliance',
        'ren',
        'rent',
        'rentals',
        'repair',
        'report',
        'republican',
        'rest',
        'restaurant',
        'review',
        'reviews',
        'rexroth',
        'rich',
        'richardli',
        'ricoh',
        'ril',
        'rio',
        'rip',
        'ro',
        'rocks',
        'rodeo',
        'rogers',
        'room',
        'rs',
        'rsvp',
        'ru',
        'rugby',
        'ruhr',
        'run',
        'rw',
        'rwe',
        'ryukyu',
        'sa',
        'saarland',
        'safe',
        'safety',
        'sakura',
        'sale',
        'salon',
        'samsclub',
        'samsung',
        'sandvik',
        'sandvikcoromant',
        'sanofi',
        'sap',
        'sarl',
        'sas',
        'save',
        'saxo',
        'sb',
        'sbi',
        'sbs',
        'sc',
        'scb',
        'schaeffler',
        'schmidt',
        'scholarships',
        'school',
        'schule',
        'schwarz',
        'science',
        'scot',
        'sd',
        'se',
        'search',
        'seat',
        'secure',
        'security',
        'seek',
        'select',
        'sener',
        'services',
        'seven',
        'sew',
        'sex',
        'sexy',
        'sfr',
        'sg',
        'sh',
        'shangrila',
        'sharp',
        'shell',
        'shia',
        'shiksha',
        'shoes',
        'shop',
        'shopping',
        'shouji',
        'show',
        'si',
        'silk',
        'sina',
        'singles',
        'site',
        'sj',
        'sk',
        'ski',
        'skin',
        'sky',
        'skype',
        'sl',
        'sling',
        'sm',
        'smart',
        'smile',
        'sn',
        'sncf',
        'so',
        'soccer',
        'social',
        'softbank',
        'software',
        'sohu',
        'solar',
        'solutions',
        'song',
        'sony',
        'soy',
        'spa',
        'space',
        'sport',
        'spot',
        'sr',
        'srl',
        'ss',
        'st',
        'stada',
        'staples',
        'star',
        'statebank',
        'statefarm',
        'stc',
        'stcgroup',
        'stockholm',
        'storage',
        'store',
        'stream',
        'studio',
        'study',
        'style',
        'su',
        'sucks',
        'supplies',
        'supply',
        'support',
        'surf',
        'surgery',
        'suzuki',
        'sv',
        'swatch',
        'swiss',
        'sx',
        'sy',
        'sydney',
        'systems',
        'sz',
        'tab',
        'taipei',
        'talk',
        'taobao',
        'target',
        'tatamotors',
        'tatar',
        'tattoo',
        'tax',
        'taxi',
        'tc',
        'tci',
        'td',
        'tdk',
        'team',
        'tech',
        'technology',
        'tel',
        'temasek',
        'tennis',
        'teva',
        'tf',
        'tg',
        'th',
        'thd',
        'theater',
        'theatre',
        'tiaa',
        'tickets',
        'tienda',
        'tips',
        'tires',
        'tirol',
        'tj',
        'tjmaxx',
        'tjx',
        'tk',
        'tkmaxx',
        'tl',
        'tm',
        'tmall',
        'tn',
        'to',
        'today',
        'tokyo',
        'tools',
        'top',
        'toray',
        'toshiba',
        'total',
        'tours',
        'town',
        'toyota',
        'toys',
        'tr',
        'trade',
        'trading',
        'training',
        'travel',
        'travelers',
        'travelersinsurance',
        'trust',
        'trv',
        'tt',
        'tube',
        'tui',
        'tunes',
        'tushu',
        'tv',
        'tvs',
        'tw',
        'tz',
        'ua',
        'ubank',
        'ubs',
        'ug',
        'uk',
        'unicom',
        'university',
        'uno',
        'uol',
        'ups',
        'us',
        'uy',
        'uz',
        'va',
        'vacations',
        'vana',
        'vanguard',
        'vc',
        've',
        'vegas',
        'ventures',
        'verisign',
        'versicherung',
        'vet',
        'vg',
        'vi',
        'viajes',
        'video',
        'vig',
        'viking',
        'villas',
        'vin',
        'vip',
        'virgin',
        'visa',
        'vision',
        'viva',
        'vivo',
        'vlaanderen',
        'vn',
        'vodka',
        'volvo',
        'vote',
        'voting',
        'voto',
        'voyage',
        'vu',
        'wales',
        'walmart',
        'walter',
        'wang',
        'wanggou',
        'watch',
        'watches',
        'weather',
        'weatherchannel',
        'web',
        'webcam',
        'weber',
        'website',
        'wed',
        'wedding',
        'weibo',
        'weir',
        'wf',
        'whoswho',
        'wien',
        'wiki',
        'williamhill',
        'win',
        'windows',
        'wine',
        'winners',
        'wme',
        'woodside',
        'work',
        'works',
        'world',
        'wow',
        'ws',
        'wtc',
        'wtf',
        'xbox',
        'xerox',
        'xihuan',
        'xin',
        'xn--11b4c3d',
        'कॉम',
        'xn--1ck2e1b',
        'セール',
        'xn--1qqw23a',
        '佛山',
        'xn--2scrj9c',
        'ಭಾರತ',
        'xn--30rr7y',
        '慈善',
        'xn--3bst00m',
        '集团',
        'xn--3ds443g',
        '在线',
        'xn--3e0b707e',
        '한국',
        'xn--3hcrj9c',
        'ଭାରତ',
        'xn--3pxu8k',
        '点看',
        'xn--42c2d9a',
        'คอม',
        'xn--45br5cyl',
        'ভাৰত',
        'xn--45brj9c',
        'ভারত',
        'xn--45q11c',
        '八卦',
        'xn--4dbrk0ce',
        'ישראל',
        'xn--4gbrim',
        'موقع',
        'xn--54b7fta0cc',
        'বাংলা',
        'xn--55qw42g',
        '公益',
        'xn--55qx5d',
        '公司',
        'xn--5su34j936bgsg',
        '香格里拉',
        'xn--5tzm5g',
        '网站',
        'xn--6frz82g',
        '移动',
        'xn--6qq986b3xl',
        '我爱你',
        'xn--80adxhks',
        'москва',
        'xn--80ao21a',
        'қаз',
        'xn--80aqecdr1a',
        'католик',
        'xn--80asehdb',
        'онлайн',
        'xn--80aswg',
        'сайт',
        'xn--8y0a063a',
        '联通',
        'xn--90a3ac',
        'срб',
        'xn--90ae',
        'бг',
        'xn--90ais',
        'бел',
        'xn--9dbq2a',
        'קום',
        'xn--9et52u',
        '时尚',
        'xn--9krt00a',
        '微博',
        'xn--b4w605ferd',
        '淡马锡',
        'xn--bck1b9a5dre4c',
        'ファッション',
        'xn--c1avg',
        'орг',
        'xn--c2br7g',
        'नेट',
        'xn--cck2b3b',
        'ストア',
        'xn--cckwcxetd',
        'アマゾン',
        'xn--cg4bki',
        '삼성',
        'xn--clchc0ea0b2g2a9gcd',
        'சிங்கப்பூர்',
        'xn--czr694b',
        '商标',
        'xn--czrs0t',
        '商店',
        'xn--czru2d',
        '商城',
        'xn--d1acj3b',
        'дети',
        'xn--d1alf',
        'мкд',
        'xn--e1a4c',
        'ею',
        'xn--eckvdtc9d',
        'ポイント',
        'xn--efvy88h',
        '新闻',
        'xn--fct429k',
        '家電',
        'xn--fhbei',
        'كوم',
        'xn--fiq228c5hs',
        '中文网',
        'xn--fiq64b',
        '中信',
        'xn--fiqs8s',
        '中国',
        'xn--fiqz9s',
        '中國',
        'xn--fjq720a',
        '娱乐',
        'xn--flw351e',
        '谷歌',
        'xn--fpcrj9c3d',
        'భారత్',
        'xn--fzc2c9e2c',
        'ලංකා',
        'xn--fzys8d69uvgm',
        '電訊盈科',
        'xn--g2xx48c',
        '购物',
        'xn--gckr3f0f',
        'クラウド',
        'xn--gecrj9c',
        'ભારત',
        'xn--gk3at1e',
        '通販',
        'xn--h2breg3eve',
        'भारतम्',
        'xn--h2brj9c',
        'भारत',
        'xn--h2brj9c8c',
        'भारोत',
        'xn--hxt814e',
        '网店',
        'xn--i1b6b1a6a2e',
        'संगठन',
        'xn--imr513n',
        '餐厅',
        'xn--io0a7i',
        '网络',
        'xn--j1aef',
        'ком',
        'xn--j1amh',
        'укр',
        'xn--j6w193g',
        '香港',
        'xn--jlq480n2rg',
        '亚马逊',
        'xn--jvr189m',
        '食品',
        'xn--kcrx77d1x4a',
        '飞利浦',
        'xn--kprw13d',
        '台湾',
        'xn--kpry57d',
        '台灣',
        'xn--kput3i',
        '手机',
        'xn--l1acc',
        'мон',
        'xn--lgbbat1ad8j',
        'الجزائر',
        'xn--mgb9awbf',
        'عمان',
        'xn--mgba3a3ejt',
        'ارامكو',
        'xn--mgba3a4f16a',
        'ایران',
        'xn--mgba7c0bbn0a',
        'العليان',
        'xn--mgbaam7a8h',
        'امارات',
        'xn--mgbab2bd',
        'بازار',
        'xn--mgbah1a3hjkrd',
        'موريتانيا',
        'xn--mgbai9azgqp6j',
        'پاکستان',
        'xn--mgbayh7gpa',
        'الاردن',
        'xn--mgbbh1a',
        'بارت',
        'xn--mgbbh1a71e',
        'بھارت',
        'xn--mgbc0a9azcg',
        'المغرب',
        'xn--mgbca7dzdo',
        'ابوظبي',
        'xn--mgbcpq6gpa1a',
        'البحرين',
        'xn--mgberp4a5d4ar',
        'السعودية',
        'xn--mgbgu82a',
        'ڀارت',
        'xn--mgbi4ecexp',
        'كاثوليك',
        'xn--mgbpl2fh',
        'سودان',
        'xn--mgbt3dhd',
        'همراه',
        'xn--mgbtx2b',
        'عراق',
        'xn--mgbx4cd0ab',
        'مليسيا',
        'xn--mix891f',
        '澳門',
        'xn--mk1bu44c',
        '닷컴',
        'xn--mxtq1m',
        '政府',
        'xn--ngbc5azd',
        'شبكة',
        'xn--ngbe9e0a',
        'بيتك',
        'xn--ngbrx',
        'عرب',
        'xn--node',
        'გე',
        'xn--nqv7f',
        '机构',
        'xn--nqv7fs00ema',
        '组织机构',
        'xn--nyqy26a',
        '健康',
        'xn--o3cw4h',
        'ไทย',
        'xn--ogbpf8fl',
        'سورية',
        'xn--otu796d',
        '招聘',
        'xn--p1acf',
        'рус',
        'xn--p1ai',
        'рф',
        'xn--pgbs0dh',
        'تونس',
        'xn--pssy2u',
        '大拿',
        'xn--q7ce6a',
        'ລາວ',
        'xn--q9jyb4c',
        'みんな',
        'xn--qcka1pmc',
        'グーグル',
        'xn--qxa6a',
        'ευ',
        'xn--qxam',
        'ελ',
        'xn--rhqv96g',
        '世界',
        'xn--rovu88b',
        '書籍',
        'xn--rvc1e0am3e',
        'ഭാരതം',
        'xn--s9brj9c',
        'ਭਾਰਤ',
        'xn--ses554g',
        '网址',
        'xn--t60b56a',
        '닷넷',
        'xn--tckwe',
        'コム',
        'xn--tiq49xqyj',
        '天主教',
        'xn--unup4y',
        '游戏',
        'xn--vermgensberater-ctb',
        'vermögensberater',
        'xn--vermgensberatung-pwb',
        'vermögensberatung',
        'xn--vhquv',
        '企业',
        'xn--vuq861b',
        '信息',
        'xn--w4r85el8fhu5dnra',
        '嘉里大酒店',
        'xn--w4rs40l',
        '嘉里',
        'xn--wgbh1c',
        'مصر',
        'xn--wgbl6a',
        'قطر',
        'xn--xhq521b',
        '广东',
        'xn--xkc2al3hye2a',
        'இலங்கை',
        'xn--xkc2dl3a5ee0h',
        'இந்தியா',
        'xn--y9a3aq',
        'հայ',
        'xn--yfro4i67o',
        '新加坡',
        'xn--ygbi2ammx',
        'فلسطين',
        'xn--zfr164b',
        '政务',
        'xxx',
        'xyz',
        'yachts',
        'yahoo',
        'yamaxun',
        'yandex',
        'ye',
        'yodobashi',
        'yoga',
        'yokohama',
        'you',
        'youtube',
        'yt',
        'yun',
        'za',
        'zappos',
        'zara',
        'zero',
        'zip',
        'zm',
        'zone',
        'zuerich',
        'zw',
    ];

    /**
     * @var string
     */
    protected $_tld;

    /**
     * Array for valid Idns
     * @see http://www.iana.org/domains/idn-tables/ Official list of supported IDN Chars
     * (.AC) Ascension Island http://www.nic.ac/pdf/AC-IDN-Policy.pdf
     * (.AR) Argentinia http://www.nic.ar/faqidn.html
     * (.AS) American Samoa http://www.nic.as/idn/chars.cfm
     * (.AT) Austria http://www.nic.at/en/service/technical_information/idn/charset_converter/
     * (.BIZ) International http://www.iana.org/domains/idn-tables/
     * (.BR) Brazil http://registro.br/faq/faq6.html
     * (.BV) Bouvett Island http://www.norid.no/domeneregistrering/idn/idn_nyetegn.en.html
     * (.CA) Canada http://www.iana.org/domains/idn-tables/tables/ca_fr_1.0.html
     * (.CAT) Catalan http://www.iana.org/domains/idn-tables/tables/cat_ca_1.0.html
     * (.CH) Switzerland https://nic.switch.ch/reg/ocView.action?res=EF6GW2JBPVTG67DLNIQXU234MN6SC33JNQQGI7L6#anhang1
     * (.CL) Chile http://www.iana.org/domains/idn-tables/tables/cl_latn_1.0.html
     * (.COM) International http://www.verisign.com/information-services/naming-services/internationalized-domain-names/index.html
     * (.DE) Germany http://www.denic.de/en/domains/idns/liste.html
     * (.DK) Danmark http://www.dk-hostmaster.dk/index.php?id=151
     * (.ES) Spain https://www.nic.es/media/2008-05/1210147705287.pdf
     * (.FI) Finland http://www.ficora.fi/en/index/palvelut/fiverkkotunnukset/aakkostenkaytto.html
     * (.GR) Greece https://grweb.ics.forth.gr/CharacterTable1_en.jsp
     * (.HR) Croatia https://domene.hr/en/portal/files/Odluka_1,2alfanum_dijak_en.pdf
     * (.HU) Hungary http://www.domain.hu/domain/English/szabalyzat/szabalyzat.html
     * (.INFO) International http://www.nic.info/info/idn
     * (.IO) British Indian Ocean Territory http://www.nic.io/IO-IDN-Policy.pdf
     * (.IR) Iran http://www.nic.ir/Allowable_Characters_dot-iran
     * (.IS) Iceland http://www.isnic.is/domain/rules.php
     * (.KR) Korea http://www.iana.org/domains/idn-tables/tables/kr_ko-kr_1.0.html
     * (.LI) Liechtenstein https://nic.switch.ch/reg/ocView.action?res=EF6GW2JBPVTG67DLNIQXU234MN6SC33JNQQGI7L6#anhang1
     * (.LT) Lithuania http://www.domreg.lt/static/doc/public/idn_symbols-en.pdf
     * (.MD) Moldova http://www.register.md/
     * (.MUSEUM) International http://www.iana.org/domains/idn-tables/tables/museum_latn_1.0.html
     * (.NET) International http://www.verisign.com/information-services/naming-services/internationalized-domain-names/index.html
     * (.NO) Norway http://www.norid.no/domeneregistrering/idn/idn_nyetegn.en.html
     * (.NU) Niue http://www.worldnames.net/
     * (.ORG) International http://www.pir.org/index.php?db=content/FAQs&tbl=FAQs_Registrant&id=2
     * (.PE) Peru https://www.nic.pe/nuevas_politicas_faq_2.php
     * (.PL) Poland http://www.dns.pl/IDN/allowed_character_sets.pdf
     * (.PR) Puerto Rico http://www.nic.pr/idn_rules.asp
     * (.PT) Portugal https://online.dns.pt/dns_2008/do?com=DS;8216320233;111;+PAGE(4000058)+K-CAT-CODIGO(C.125)+RCNT(100);
     * (.RU) Russia http://www.iana.org/domains/idn-tables/tables/ru_ru-ru_1.0.html
     * (.RS) Serbia http://www.iana.org/domains/idn-tables/tables/rs_sr-rs_1.0.pdf
     * (.SA) Saudi Arabia http://www.iana.org/domains/idn-tables/tables/sa_ar_1.0.html
     * (.SE) Sweden http://www.iis.se/english/IDN_campaignsite.shtml?lang=en
     * (.SH) Saint Helena http://www.nic.sh/SH-IDN-Policy.pdf
     * (.SJ) Svalbard and Jan Mayen http://www.norid.no/domeneregistrering/idn/idn_nyetegn.en.html
     * (.TH) Thailand http://www.iana.org/domains/idn-tables/tables/th_th-th_1.0.html
     * (.TM) Turkmenistan http://www.nic.tm/TM-IDN-Policy.pdf
     * (.TR) Turkey https://www.nic.tr/index.php
     * (.UA) Ukraine http://www.iana.org/domains/idn-tables/tables/ua_cyrl_1.2.html
     * (.VE) Venice http://www.iana.org/domains/idn-tables/tables/ve_es_1.0.html
     * (.VN) Vietnam http://www.vnnic.vn/english/5-6-300-2-2-04-20071115.htm#1.%20Introduction
     *
     * @var array
     */
    protected $_validIdns = [
        'AC'  => [1 => '/^[\x{002d}0-9a-zà-öø-ÿāăąćĉċčďđēėęěĝġģĥħīįĵķĺļľŀłńņňŋőœŕŗřśŝşšţťŧūŭůűųŵŷźżž]{1,63}$/iu'],
        'AR'  => [1 => '/^[\x{002d}0-9a-zà-ãç-êìíñ-õü]{1,63}$/iu'],
        'AS'  => [1 => '/^[\x{002d}0-9a-zà-öø-ÿāăąćĉċčďđēĕėęěĝğġģĥħĩīĭįıĵķĸĺļľłńņňŋōŏőœŕŗřśŝşšţťŧũūŭůűųŵŷźż]{1,63}$/iu'],
        'AT'  => [1 => '/^[\x{002d}0-9a-zà-öø-ÿœšž]{1,63}$/iu'],
        'BIZ' => 'Hostname/Biz.php',
        'BR'  => [1 => '/^[\x{002d}0-9a-zà-ãçéíó-õúü]{1,63}$/iu'],
        'BV'  => [1 => '/^[\x{002d}0-9a-zàáä-éêñ-ôöøüčđńŋšŧž]{1,63}$/iu'],
        'CA'  => [1 => '/^[\x{002d}0-9a-zàâæçéèêëîïôœùûüÿ\x{00E0}\x{00E2}\x{00E7}\x{00E8}\x{00E9}\x{00EA}\x{00EB}\x{00EE}\x{00EF}\x{00F4}\x{00F9}\x{00FB}\x{00FC}\x{00E6}\x{0153}\x{00FF}]{1,63}$/iu'],
        'CAT' => [1 => '/^[\x{002d}0-9a-z·àç-éíïòóúü]{1,63}$/iu'],
        'CH'  => [1 => '/^[\x{002d}0-9a-zà-öø-ÿœ]{1,63}$/iu'],
        'CL'  => [1 => '/^[\x{002d}0-9a-záéíñóúü]{1,63}$/iu'],
        'CN'  => 'Hostname/Cn.php',
        'COM' => 'Hostname/Com.php',
        'DE'  => [1 => '/^[\x{002d}0-9a-zà-öø-ÿăąāćĉčċďđĕěėęēğĝġģĥħĭĩįīıĵķĺľļłńňņŋŏőōœĸŕřŗśŝšşťßţŧŭůűũųūŵŷźžż]{1,63}$/iu'],
        'DK'  => [1 => '/^[\x{002d}0-9a-zäéöüæøå]{1,63}$/iu'],
        'ES'  => [1 => '/^[\x{002d}0-9a-zàáçèéíïñòóúü·]{1,63}$/iu'],
        'EU'  => [1 => '/^[\x{002d}0-9a-zà-öø-ÿ]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-zāăąćĉċčďđēĕėęěĝğġģĥħĩīĭįıĵķĺļľŀłńņňŉŋōŏőœŕŗřśŝšťŧũūŭůűųŵŷźżž]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-zșț]{1,63}$/iu',
            4 => '/^[\x{002d}0-9a-zΐάέήίΰαβγδεζηθικλμνξοπρςστυφχψωϊϋόύώ]{1,63}$/iu',
            5 => '/^[\x{002d}0-9a-zабвгдежзийклмнопрстуфхцчшщъыьэюя]{1,63}$/iu',
            6 => '/^[\x{002d}0-9a-zἀ-ἇἐ-ἕἠ-ἧἰ-ἷὀ-ὅὐ-ὗὠ-ὧὰ-ὼώᾀ-ᾇᾐ-ᾗᾠ-ᾧᾰ-ᾴᾶᾷῂῃῄῆῇῐ-ῒΐῖῗῠ-ῧῲῳῴῶῷ]{1,63}$/iu'],
        'FI'  => [1 => '/^[\x{002d}0-9a-zäåö]{1,63}$/iu'],
        'GR'  => [1 => '/^[\x{002d}0-9a-zΆΈΉΊΌΎ-ΡΣ-ώἀ-ἕἘ-Ἕἠ-ὅὈ-Ὅὐ-ὗὙὛὝὟ-ώᾀ-ᾴᾶ-ᾼῂῃῄῆ-ῌῐ-ΐῖ-Ίῠ-Ῥῲῳῴῶ-ῼ]{1,63}$/iu'],
        'HK'  => 'Hostname/Cn.php',
        'HR'  => [1 => '/^[\x{002d}0-9a-zčćžšđ]{1,63}$/iu'],
        'HU'  => [1 => '/^[\x{002d}0-9a-záéíóöúüőű]{1,63}$/iu'],
        'IL'  => [1 => '/^[\x{002d}0-9\x{05D0}-\x{05EA}]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-z]{1,63}$/i'],
        'INFO'=> [1 => '/^[\x{002d}0-9a-zäåæéöøü]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-záéíóöúüőű]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-záæéíðóöúýþ]{1,63}$/iu',
            4 => '/^[\x{AC00}-\x{D7A3}]{1,17}$/iu',
            5 => '/^[\x{002d}0-9a-zāčēģīķļņōŗšūž]{1,63}$/iu',
            6 => '/^[\x{002d}0-9a-ząčėęįšūųž]{1,63}$/iu',
            7 => '/^[\x{002d}0-9a-zóąćęłńśźż]{1,63}$/iu',
            8 => '/^[\x{002d}0-9a-záéíñóúü]{1,63}$/iu'],
        'IO'  => [1 => '/^[\x{002d}0-9a-zà-öø-ÿăąāćĉčċďđĕěėęēğĝġģĥħĭĩįīıĵķĺľļłńňņŋŏőōœĸŕřŗśŝšşťţŧŭůűũųūŵŷźžż]{1,63}$/iu'],
        'IS'  => [1 => '/^[\x{002d}0-9a-záéýúíóþæöð]{1,63}$/iu'],
        'IT'  => [1 => '/^[\x{002d}0-9a-zàâäèéêëìîïòôöùûüæœçÿß-]{1,63}$/iu'],
        'JP'  => 'Hostname/Jp.php',
        'KR'  => [1 => '/^[\x{AC00}-\x{D7A3}]{1,17}$/iu'],
        'LI'  => [1 => '/^[\x{002d}0-9a-zà-öø-ÿœ]{1,63}$/iu'],
        'LT'  => [1 => '/^[\x{002d}0-9ąčęėįšųūž]{1,63}$/iu'],
        'MD'  => [1 => '/^[\x{002d}0-9ăâîşţ]{1,63}$/iu'],
        'MUSEUM' => [1 => '/^[\x{002d}0-9a-zà-öø-ÿāăąćċčďđēėęěğġģħīįıķĺļľłńņňŋōőœŕŗřśşšţťŧūůűųŵŷźżžǎǐǒǔ\x{01E5}\x{01E7}\x{01E9}\x{01EF}ə\x{0292}ẁẃẅỳ]{1,63}$/iu'],
        'NET' => 'Hostname/Com.php',
        'NO'  => [1 => '/^[\x{002d}0-9a-zàáä-éêñ-ôöøüčđńŋšŧž]{1,63}$/iu'],
        'NU'  => 'Hostname/Com.php',
        'NZ'  => [1 => '/^[a-z0-9āēīōū\x2d]{1,63}$/iu'],
        'ORG' => [1 => '/^[\x{002d}0-9a-záéíñóúü]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-zóąćęłńśźż]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-záäåæéëíðóöøúüýþ]{1,63}$/iu',
            4 => '/^[\x{002d}0-9a-záéíóöúüőű]{1,63}$/iu',
            5 => '/^[\x{002d}0-9a-ząčėęįšūųž]{1,63}$/iu',
            6 => '/^[\x{AC00}-\x{D7A3}]{1,17}$/iu',
            7 => '/^[\x{002d}0-9a-zāčēģīķļņōŗšūž]{1,63}$/iu'],
        'PE'  => [1 => '/^[\x{002d}0-9a-zñáéíóúü]{1,63}$/iu'],
        'PL'  => [1 => '/^[\x{002d}0-9a-zāčēģīķļņōŗšūž]{1,63}$/iu',
            2 => '/^[\x{002d}а-ик-ш\x{0450}ѓѕјљњќџ]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-zâîăşţ]{1,63}$/iu',
            4 => '/^[\x{002d}0-9а-яё\x{04C2}]{1,63}$/iu',
            5 => '/^[\x{002d}0-9a-zàáâèéêìíîòóôùúûċġħż]{1,63}$/iu',
            6 => '/^[\x{002d}0-9a-zàäåæéêòóôöøü]{1,63}$/iu',
            7 => '/^[\x{002d}0-9a-zóąćęłńśźż]{1,63}$/iu',
            8 => '/^[\x{002d}0-9a-zàáâãçéêíòóôõúü]{1,63}$/iu',
            9 => '/^[\x{002d}0-9a-zâîăşţ]{1,63}$/iu',
            10=> '/^[\x{002d}0-9a-záäéíóôúýčďĺľňŕšťž]{1,63}$/iu',
            11=> '/^[\x{002d}0-9a-zçë]{1,63}$/iu',
            12=> '/^[\x{002d}0-9а-ик-шђјљњћџ]{1,63}$/iu',
            13=> '/^[\x{002d}0-9a-zćčđšž]{1,63}$/iu',
            14=> '/^[\x{002d}0-9a-zâçöûüğış]{1,63}$/iu',
            15=> '/^[\x{002d}0-9a-záéíñóúü]{1,63}$/iu',
            16=> '/^[\x{002d}0-9a-zäõöüšž]{1,63}$/iu',
            17=> '/^[\x{002d}0-9a-zĉĝĥĵŝŭ]{1,63}$/iu',
            18=> '/^[\x{002d}0-9a-zâäéëîô]{1,63}$/iu',
            19=> '/^[\x{002d}0-9a-zàáâäåæçèéêëìíîïðñòôöøùúûüýćčłńřśš]{1,63}$/iu',
            20=> '/^[\x{002d}0-9a-zäåæõöøüšž]{1,63}$/iu',
            21=> '/^[\x{002d}0-9a-zàáçèéìíòóùú]{1,63}$/iu',
            22=> '/^[\x{002d}0-9a-zàáéíóöúüőű]{1,63}$/iu',
            23=> '/^[\x{002d}0-9ΐά-ώ]{1,63}$/iu',
            24=> '/^[\x{002d}0-9a-zàáâåæçèéêëðóôöøüþœ]{1,63}$/iu',
            25=> '/^[\x{002d}0-9a-záäéíóöúüýčďěňřšťůž]{1,63}$/iu',
            26=> '/^[\x{002d}0-9a-z·àçèéíïòóúü]{1,63}$/iu',
            27=> '/^[\x{002d}0-9а-ъьюя\x{0450}\x{045D}]{1,63}$/iu',
            28=> '/^[\x{002d}0-9а-яёіў]{1,63}$/iu',
            29=> '/^[\x{002d}0-9a-ząčėęįšūųž]{1,63}$/iu',
            30=> '/^[\x{002d}0-9a-záäåæéëíðóöøúüýþ]{1,63}$/iu',
            31=> '/^[\x{002d}0-9a-zàâæçèéêëîïñôùûüÿœ]{1,63}$/iu',
            32=> '/^[\x{002d}0-9а-щъыьэюяёєіїґ]{1,63}$/iu',
            33=> '/^[\x{002d}0-9א-ת]{1,63}$/iu'],
        'PR'  => [1 => '/^[\x{002d}0-9a-záéíóúñäëïüöâêîôûàèùæçœãõ]{1,63}$/iu'],
        'PT'  => [1 => '/^[\x{002d}0-9a-záàâãçéêíóôõú]{1,63}$/iu'],
        'RS'  => [1 => '/^[\x{002D}\x{0030}-\x{0039}\x{0061}-\x{007A}\x{0107}\x{010D}\x{0111}\x{0161}\x{017E}]{1,63}$/iu)'],
        'RU'  => [1 => '/^[\x{002d}0-9а-яё]{1,63}$/iu'],
        'SA'  => [1 => '/^[\x{002d}.0-9\x{0621}-\x{063A}\x{0641}-\x{064A}\x{0660}-\x{0669}]{1,63}$/iu'],
        'SE'  => [1 => '/^[\x{002d}0-9a-zäåéöü]{1,63}$/iu'],
        'SH'  => [1 => '/^[\x{002d}0-9a-zà-öø-ÿăąāćĉčċďđĕěėęēğĝġģĥħĭĩįīıĵķĺľļłńňņŋŏőōœĸŕřŗśŝšşťţŧŭůűũųūŵŷźžż]{1,63}$/iu'],
        'SI'  => [
            1 => '/^[\x{002d}0-9a-zà-öø-ÿ]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-zāăąćĉċčďđēĕėęěĝğġģĥħĩīĭįıĵķĺļľŀłńņňŉŋōŏőœŕŗřśŝšťŧũūŭůűųŵŷźżž]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-zșț]{1,63}$/iu'],
        'SJ'  => [1 => '/^[\x{002d}0-9a-zàáä-éêñ-ôöøüčđńŋšŧž]{1,63}$/iu'],
        'TH'  => [1 => '/^[\x{002d}0-9a-z\x{0E01}-\x{0E3A}\x{0E40}-\x{0E4D}\x{0E50}-\x{0E59}]{1,63}$/iu'],
        'TM'  => [1 => '/^[\x{002d}0-9a-zà-öø-ÿāăąćĉċčďđēėęěĝġģĥħīįĵķĺļľŀłńņňŋőœŕŗřśŝşšţťŧūŭůűųŵŷźżž]{1,63}$/iu'],
        'TW'  => 'Hostname/Cn.php',
        'TR'  => [1 => '/^[\x{002d}0-9a-zğıüşöç]{1,63}$/iu'],
        'UA'  => [1 => '/^[\x{002d}0-9a-zабвгдежзийклмнопрстуфхцчшщъыьэюяѐёђѓєѕіїјљњћќѝўџґӂʼ]{1,63}$/iu'],
        'VE'  => [1 => '/^[\x{002d}0-9a-záéíóúüñ]{1,63}$/iu'],
        'VN'  => [1 => '/^[ÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚÝàáâãèéêìíòóôõùúýĂăĐđĨĩŨũƠơƯư\x{1EA0}-\x{1EF9}]{1,63}$/iu'],
        'мон' => [1 => '/^[\x{002d}0-9\x{0430}-\x{044F}]{1,63}$/iu'],
        'срб' => [1 => '/^[\x{002d}0-9а-ик-шђјљњћџ]{1,63}$/iu'],
        'сайт' => [1 => '/^[\x{002d}0-9а-яёіїѝйўґг]{1,63}$/iu'],
        'онлайн' => [1 => '/^[\x{002d}0-9а-яёіїѝйўґг]{1,63}$/iu'],
        '中国' => 'Hostname/Cn.php',
        '中國' => 'Hostname/Cn.php',
        'ලංකා' => [1 => '/^[\x{0d80}-\x{0dff}]{1,63}$/iu'],
        '香港' => 'Hostname/Cn.php',
        '台湾' => 'Hostname/Cn.php',
        '台灣' => 'Hostname/Cn.php',
        'امارات'   => [1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'],
        'الاردن'    => [1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'],
        'السعودية' => [1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'],
        'ไทย' => [1 => '/^[\x{002d}0-9a-z\x{0E01}-\x{0E3A}\x{0E40}-\x{0E4D}\x{0E50}-\x{0E59}]{1,63}$/iu'],
        'рф' => [1 => '/^[\x{002d}0-9а-яё]{1,63}$/iu'],
        'تونس' => [1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'],
        'مصر' => [1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'],
        'இலங்கை' => [1 => '/^[\x{0b80}-\x{0bff}]{1,63}$/iu'],
        'فلسطين' => [1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'],
        'شبكة'  => [1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'],
    ];

    protected $_idnLength = [
        'BIZ' => [5 => 17, 11 => 15, 12 => 20],
        'CN'  => [1 => 20],
        'COM' => [3 => 17, 5 => 20],
        'HK'  => [1 => 15],
        'INFO'=> [4 => 17],
        'KR'  => [1 => 17],
        'NET' => [3 => 17, 5 => 20],
        'ORG' => [6 => 17],
        'TW'  => [1 => 20],
        'ایران' => [1 => 30],
        '中国' => [1 => 20],
        '公司' => [1 => 20],
        '网络' => [1 => 20],
    ];

    protected $_options = [
        'allow'  => self::ALLOW_DNS,
        'idn'    => true,
        'tld'    => true,
        'tldDns' => true,
        'ip'     => null
    ];

    /**
     * Result of the DNS lookup per TLD, shared by all instances
     *
     * @var array
     */
    protected static $_tldDnsCache = [];

    /**
     * Sets validator options
     *
     * @see http://www.iana.org/cctld/specifications-policies-cctlds-01apr02.htm  Technical Specifications for ccTLDs
     * @param array|int $options Validator options
     */
    public function __construct($options = [])
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            $options = func_get_args();
            $temp['allow'] = array_shift($options);
            if (!empty($options)) {
                $temp['idn'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['tld'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['ip'] = array_shift($options);
            }

            $options = $temp;
        }

        $options += $this->_options;
        $this->setOptions($options);
    }

    /**
     * Returns all set options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Sets the options for this validator
     *
     * @param array $options
     * @return Zend_Validate_Hostname
     */
    public function setOptions($options)
    {
        if (array_key_exists('allow', $options)) {
            $this->setAllow($options['allow']);
        }

        if (array_key_exists('idn', $options)) {
            $this->setValidateIdn($options['idn']);
        }

        if (array_key_exists('tld', $options)) {
            $this->setValidateTld($options['tld']);
        }

        if (array_key_exists('tldDns', $options)) {
            $this->setValidateTldDns($options['tldDns']);
        }

        if (array_key_exists('ip', $options)) {
            $this->setIpValidator($options['ip']);
        }

        return $this;
    }

    /**
     * Returns the set ip validator
     *
     * @return Zend_Validate_Ip
     */
    public function getIpValidator()
    {
        return $this->_options['ip'];
    }

    /**
     * @param Zend_Validate_Ip $ipValidator OPTIONAL
     * @return Zend_Validate_Hostname
     */
    public function setIpValidator(?Zend_Validate_Ip $ipValidator = null)
    {
        if ($ipValidator === null) {
            $ipValidator = new Zend_Validate_Ip();
        }

        $this->_options['ip'] = $ipValidator;
        return $this;
    }

    /**
     * Returns the allow option
     *
     * @return integer
     */
    public function getAllow()
    {
        return $this->_options['allow'];
    }

    /**
     * Sets the allow option
     *
     * @param  integer $allow
     * @return $this
     */
    public function setAllow($allow)
    {
        $this->_options['allow'] = $allow;
        return $this;
    }

    /**
     * Returns the set idn option
     *
     * @return boolean
     */
    public function getValidateIdn()
    {
        return $this->_options['idn'];
    }

    /**
     * Set whether IDN domains are validated
     *
     * This only applies when DNS hostnames are validated
     *
     * @param boolean $allowed Set allowed to true to validate IDNs, and false to not validate them
     * @return $this
     */
    public function setValidateIdn ($allowed)
    {
        $this->_options['idn'] = (bool) $allowed;
        return $this;
    }

    /**
     * Returns the set tld option
     *
     * @return boolean
     */
    public function getValidateTld()
    {
        return $this->_options['tld'];
    }

    /**
     * Set whether the TLD element of a hostname is validated
     *
     * This only applies when DNS hostnames are validated
     *
     * @param boolean $allowed Set allowed to true to validate TLDs, and false to not validate them
     * @return $this
     */
    public function setValidateTld ($allowed)
    {
        $this->_options['tld'] = (bool) $allowed;
        return $this;
    }

    /**
     * Returns the set tldDns option
     *
     * @return boolean
     */
    public function getValidateTldDns()
    {
        return $this->_options['tldDns'];
    }

    /**
     * Set whether a TLD missing from the list of known TLDs is looked up in DNS
     *
     * This only applies when TLDs are validated. A TLD delegated after the
     * list was last updated is accepted when it has an NS record in the root zone.
     *
     * @param boolean $allowed Set allowed to true to look up unknown TLDs in DNS, and false to reject them
     * @return $this
     */
    public function setValidateTldDns($allowed)
    {
        $this->_options['tldDns'] = (bool) $allowed;
        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the $value is a valid hostname with respect to the current allow option
     *
     * @param  string $value
     * @throws Zend_Validate_Exception if a fatal error occurs for validation process
     * @return boolean
     */
    public function isValid($value)
    {
        if (!is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $this->_setValue($value);
        // Check input against IP address schema
        if (preg_match('/^[0-9a-f:.]*$/i', $value) &&
            $this->_options['ip']->setTranslator($this->getTranslator())->isValid($value)) {
            if (!($this->_options['allow'] & self::ALLOW_IP)) {
                $this->_error(self::IP_ADDRESS_NOT_ALLOWED);
                return false;
            } else {
                return true;
            }
        }

        // RFC3986 3.2.2 states:
        //
        //     The rightmost domain label of a fully qualified domain name
        //     in DNS may be followed by a single "." and should be if it is
        //     necessary to distinguish between the complete domain name and
        //     some local domain.
        //
        // (see ZF-6363)

        // Local hostnames are allowed to be partitial (ending '.')
        if ($this->_options['allow'] & self::ALLOW_LOCAL) {
            if (substr($value, -1) === '.') {
                $value = substr($value, 0, -1);
                if (substr($value, -1) === '.') {
                    // Empty hostnames (ending '..') are not allowed
                    $this->_error(self::INVALID_LOCAL_NAME);
                    return false;
                }
            }
        }

        $domainParts = explode('.', $value);

        // Prevent partitial IP V4 adresses (ending '.')
        if ((count($domainParts) == 4) && preg_match('/^[0-9.a-e:.]*$/i', $value) &&
            $this->_options['ip']->setTranslator($this->getTranslator())->isValid($value)) {
            $this->_error(self::INVALID_LOCAL_NAME);
        }

        // Check input against DNS hostname schema
        if ((count($domainParts) > 1) && (strlen($value) >= 4) && (strlen($value) <= 254)) {
            $status = false;

            $origenc = ini_get('default_charset');
            ini_set('default_charset', 'UTF-8');

            do {
                // First check TLD
                $matches = [];
                if (preg_match('/([^.]{2,63})$/iu', end($domainParts), $matches)
                    || (array_key_exists(end($domainParts), $this->_validIdns))) {
                    reset($domainParts);

                    // Hostname characters are: *(label dot)(label dot label); max 254 chars
                    // label: id-prefix [*ldh{61} id-prefix]; max 63 chars
                    // id-prefix: alpha / digit
                    // ldh: alpha / digit / dash

                    // Match TLD against known list
                    $removedTld = false;
                    $this->_tld = $matches[1];
                    if ($this->_options['tld']) {
                        if (!in_array(strtolower($this->_tld), $this->_validTlds)
                            && !in_array($this->_tld, $this->_validTlds)
                            && !($this->_options['tldDns'] && $this->_tldExistsInDns($this->_tld))
                        ) {
                            $this->_error(self::UNKNOWN_TLD);
                            $status = false;
                            break;
                        }
                        // We have already validated that the TLD is fine. We don't want it to go through the below
                        // checks as new UTF-8 TLDs will incorrectly fail if there is no IDN regex for it.
                        array_pop($domainParts);
                        $removedTld = true;
                    }

                    /**
                     * Match against IDN hostnames
                     * Note: Keep label regex short to avoid issues with long patterns when matching IDN hostnames
                     * @see Zend_Validate_Hostname_Interface
                     */
                    $regexChars  = [0 => '/^[a-z0-9\x2d]{1,63}$/i'];
                    $hasIdnTable = isset($this->_validIdns[strtoupper($this->_tld)]);
                    // An internationalized TLD without a character table of its own is validated with IDNA
                    $useIdna = $this->_options['idn'] && !$hasIdnTable && preg_match('/[\x80-\xff]/', $this->_tld);
                    if ($this->_options['idn'] && $hasIdnTable) {
                        if (is_string($this->_validIdns[strtoupper($this->_tld)])) {
                            $regexChars += include($this->_validIdns[strtoupper($this->_tld)]);
                        } else {
                            $regexChars += $this->_validIdns[strtoupper($this->_tld)];
                        }
                    }

                    // Check each hostname part
                    $check = 0;
                    $lastDomainPart = end($domainParts);
                    if (! $removedTld) {
                        $lastDomainPart = prev($domainParts);
                    }
                    foreach ($domainParts as $domainPart) {
                        // If some domain part is empty (i.e. zend..com), it's invalid
                        if (empty($domainPart) && $domainPart !== '0') {
                            $this->_error(self::INVALID_HOSTNAME);
                            return false;
                        }

                        // Decode Punycode domainnames to IDN
                        if (strpos($domainPart, 'xn--') === 0) {
                            $domainPart = $this->decodePunycode(substr($domainPart, 4));
                            if ($domainPart === false) {
                                return false;
                            }
                        }

                        // Check dash (-) does not start, end or appear in 3rd and 4th positions
                        if ((strpos($domainPart, '-') === 0)
                            || ((strlen($domainPart) > 2) && (strpos($domainPart, '-', 2) == 2) && (strpos($domainPart, '-', 3) == 3))
                            || (strpos($domainPart, '-') === (strlen($domainPart) - 1))) {
                                $this->_error(self::INVALID_DASH);
                            $status = false;
                            break 2;
                        }

                        // Check each domain part
                        $checked = false;
                        $tooLong = false;
                        $isSubDomain = $domainPart != $lastDomainPart;
                        $partRegexChars = $isSubDomain ? ['/^[a-z0-9_\x2d]{1,63}$/i'] + $regexChars : $regexChars;
                        foreach ($partRegexChars as $regexKey => $regexChar) {
                            $status = preg_match($regexChar, $domainPart);
                            if ($status > 0) {
                                $length = 63;
                                if (array_key_exists(strtoupper($this->_tld), $this->_idnLength)
                                    && (array_key_exists($regexKey, $this->_idnLength[strtoupper($this->_tld)]))) {
                                    $length = $this->_idnLength[strtoupper($this->_tld)][$regexKey];
                                }

                                if (iconv_strlen($domainPart, 'UTF-8') > $length) {
                                    $tooLong = true;
                                    $status = false;
                                } else {
                                    $checked = true;
                                    break;
                                }
                            }
                        }

                        if (!$checked && $useIdna && $this->_isIdnaLabel($domainPart)) {
                            $checked = true;
                            $status  = true;
                        }

                        if ($checked) {
                            ++$check;
                        } elseif ($tooLong) {
                            $this->_error(self::INVALID_HOSTNAME);
                        }
                    }

                    // If one of the labels doesn't match, the hostname is invalid
                    if ($check !== count($domainParts)) {
                        $this->_error(self::INVALID_HOSTNAME_SCHEMA);
                        $status = false;
                    }
                } else {
                    // Hostname not long enough
                    $this->_error(self::UNDECIPHERABLE_TLD);
                    $status = false;
                }
            } while (false);

            ini_set('default_charset', $origenc);

            // If the input passes as an Internet domain name, and domain names are allowed, then the hostname
            // passes validation
            if ($status && ($this->_options['allow'] & self::ALLOW_DNS)) {
                return true;
            }
        } else if ($this->_options['allow'] & self::ALLOW_DNS) {
            $this->_error(self::INVALID_HOSTNAME);
        }

        // Check for URI Syntax (RFC3986)
        if ($this->_options['allow'] & self::ALLOW_URI) {
            if (preg_match("/^([a-zA-Z0-9-._~!$&\'()*+,;=]|%[[:xdigit:]]{2}){1,254}$/i", $value)) {
                return true;
            } else {
                $this->_error(self::INVALID_URI);
            }
        }

        // Check input against local network name schema; last chance to pass validation
        $regexLocal = '/^(([a-zA-Z0-9\x2d]{1,63}\x2e)*[a-zA-Z0-9\x2d]{1,63}[\x2e]{0,1}){1,254}$/';
        $status = @preg_match($regexLocal, $value);

        // If the input passes as a local network name, and local network names are allowed, then the
        // hostname passes validation
        $allowLocal = $this->_options['allow'] & self::ALLOW_LOCAL;
        if ($status && $allowLocal) {
            return true;
        }

        // If the input does not pass as a local network name, add a message
        if (!$status) {
            $this->_error(self::INVALID_LOCAL_NAME);
        }

        // If local network names are not allowed, add a message
        if ($status && !$allowLocal) {
            $this->_error(self::LOCAL_NAME_NOT_ALLOWED);
        }

        return false;
    }

    /**
     * Decodes a punycode encoded string to it's original utf8 string
     * In case of a decoding failure the original string is returned
     *
     * @param  string $encoded Punycode encoded string to decode
     * @return string|false
     */
    protected function decodePunycode($encoded)
    {
        if (!preg_match('/^[a-z0-9-]+$/i', $encoded)) {
            // no punycode encoded string
            $this->_error(self::CANNOT_DECODE_PUNYCODE);
            return false;
        }

        $decoded = [];
        $separator = strrpos($encoded, '-');
        if ($separator > 0) {
            for ($x = 0; $x < $separator; ++$x) {
                // prepare decoding matrix
                $decoded[] = ord($encoded[$x]);
            }
        }

        $lengthd = count($decoded);
        $lengthe = strlen($encoded);

        // decoding
        $init  = true;
        $base  = 72;
        $index = 0;
        $char  = 0x80;

        for ($indexe = ($separator) ? ($separator + 1) : 0; $indexe < $lengthe; ++$lengthd) {
            for ($old_index = $index, $pos = 1, $key = 36; 1 ; $key += 36) {
                $hex   = ord($encoded[$indexe++]);
                $digit = ($hex - 48 < 10) ? $hex - 22
                       : (($hex - 65 < 26) ? $hex - 65
                       : (($hex - 97 < 26) ? $hex - 97
                       : 36));

                $index += $digit * $pos;
                $tag    = ($key <= $base) ? 1 : (($key >= $base + 26) ? 26 : ($key - $base));
                if ($digit < $tag) {
                    break;
                }

                $pos = (int) ($pos * (36 - $tag));
            }

            $delta   = (int)($init ? (($index - $old_index) / 700) : (($index - $old_index) / 2));
            $delta  += (int)($delta / ($lengthd + 1));
            for ($key = 0; $delta > 910 / 2; $key += 36) {
                $delta = (int)($delta / 35);
            }

            $base   = (int)($key + 36 * $delta / ($delta + 38));
            $init   = false;
            $char  += (int) ($index / ($lengthd + 1));
            $index %= ($lengthd + 1);
            if ($lengthd > 0) {
                for ($i = $lengthd; $i > $index; $i--) {
                    $decoded[$i] = $decoded[($i - 1)];
                }
            }

            $decoded[$index++] = $char;
        }

        // convert decoded ucs4 to utf8 string
        foreach ($decoded as $key => $value) {
            if ($value < 128) {
                $decoded[$key] = chr($value);
            } elseif ($value < (1 << 11)) {
                $decoded[$key]  = chr(192 + ($value >> 6));
                $decoded[$key] .= chr(128 + ($value & 63));
            } elseif ($value < (1 << 16)) {
                $decoded[$key]  = chr(224 + ($value >> 12));
                $decoded[$key] .= chr(128 + (($value >> 6) & 63));
                $decoded[$key] .= chr(128 + ($value & 63));
            } elseif ($value < (1 << 21)) {
                $decoded[$key]  = chr(240 + ($value >> 18));
                $decoded[$key] .= chr(128 + (($value >> 12) & 63));
                $decoded[$key] .= chr(128 + (($value >> 6) & 63));
                $decoded[$key] .= chr(128 + ($value & 63));
            } else {
                $this->_error(self::CANNOT_DECODE_PUNYCODE);
                return false;
            }
        }

        return implode('', $decoded);
    }

    /**
     * Returns true if the TLD is delegated in the DNS root zone
     *
     * The lookup asks for the NS records of the TLD itself, so the result does
     * not depend on the host having an A record. A non-ASCII TLD needs the intl
     * extension to be converted to punycode; without it the TLD is rejected.
     * Results are cached for the lifetime of the process.
     *
     * @param string $tld
     * @return boolean
     */
    protected function _tldExistsInDns($tld)
    {
        $tld = strtolower($tld);
        if (array_key_exists($tld, self::$_tldDnsCache)) {
            return self::$_tldDnsCache[$tld];
        }

        $ascii = $tld;
        if (preg_match('/[^a-z0-9-]/', $tld)) {
            if (!function_exists('idn_to_ascii')) {
                return self::$_tldDnsCache[$tld] = false;
            }

            $ascii = idn_to_ascii($tld, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46);
            if ($ascii === false) {
                return self::$_tldDnsCache[$tld] = false;
            }
        }

        // the trailing dot keeps the resolver from appending a search domain
        return self::$_tldDnsCache[$tld] = $this->_checkDnsRecords($ascii . '.', 'NS');
    }

    /**
     * Checks a non-ASCII label against IDNA 2008 (UTS #46) with the intl extension
     *
     * The label is converted with idn_to_ascii(); it is valid when the
     * conversion reports no error and the ASCII form fits into 63 octets.
     * Invisible characters (Unicode category C) are not accepted even where
     * UTS #46 would just drop them, except the zero width (non-)joiner, which
     * is part of words in some scripts. Without intl, or for an ASCII label,
     * nothing is accepted here.
     *
     * @param string $label
     * @return boolean
     */
    protected function _isIdnaLabel($label)
    {
        if (!function_exists('idn_to_ascii') || !preg_match('/[\x80-\xff]/', $label)) {
            return false;
        }

        if (preg_match('/(?![\x{200C}\x{200D}])\p{C}/u', $label)) {
            return false;
        }

        $info  = [];
        $ascii = idn_to_ascii(
            $label,
            IDNA_NONTRANSITIONAL_TO_ASCII | IDNA_CHECK_BIDI | IDNA_CHECK_CONTEXTJ | IDNA_USE_STD3_RULES,
            INTL_IDNA_VARIANT_UTS46,
            $info
        );

        return $ascii !== false && empty($info['errors']) && strlen($ascii) <= 63;
    }

    /**
     * Looks up DNS records, separated to be replaceable in tests
     *
     * @param string $host
     * @param string $type
     * @return boolean
     */
    protected function _checkDnsRecords($host, $type)
    {
        return checkdnsrr($host, $type);
    }

}
