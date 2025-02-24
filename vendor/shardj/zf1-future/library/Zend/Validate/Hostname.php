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
     * Version 2020052000, Last Updated Wed May 20 07:07:01 2020 UTC
     *
     * @see http://data.iana.org/TLD/tlds-alpha-by-domain.txt  List of all TLDs by domain
     * @see http://www.iana.org/domains/root/db/ Official list of supported TLDs
     * @var array
     */
    protected $_validTlds = [
        'aaa',
        'aarp',
        'abarth',
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
        'adac',
        'ads',
        'adult',
        'ae',
        'aeg',
        'aero',
        'aetna',
        'af',
        'afamilycompany',
        'afl',
        'africa',
        'ag',
        'agakhan',
        'agency',
        'ai',
        'aig',
        'aigo',
        'airbus',
        'airforce',
        'airtel',
        'akdn',
        'al',
        'alfaromeo',
        'alibaba',
        'alipay',
        'allfinanz',
        'allstate',
        'ally',
        'alsace',
        'alstom',
        'am',
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
        'avianca',
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
        'bananarepublic',
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
        'bentley',
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
        'budapest',
        'bugatti',
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
        'cancerresearch',
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
        'caseih',
        'cash',
        'casino',
        'cat',
        'catering',
        'catholic',
        'cba',
        'cbn',
        'cbre',
        'cbs',
        'cc',
        'cd',
        'ceb',
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
        'cityeats',
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
        'comcast',
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
        'cookingchannel',
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
        'csc',
        'cu',
        'cuisinella',
        'cv',
        'cw',
        'cx',
        'cy',
        'cymru',
        'cyou',
        'cz',
        'dabur',
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
        'duck',
        'dunlop',
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
        'esurance',
        'et',
        'etisalat',
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
        'fiat',
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
        'foodnetwork',
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
        'frontdoor',
        'frontier',
        'ftr',
        'fujitsu',
        'fujixerox',
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
        'glade',
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
        'goo',
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
        'guardian',
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
        'helsinki',
        'here',
        'hermes',
        'hgtv',
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
        'hoteles',
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
        'intel',
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
        'iveco',
        'jaguar',
        'java',
        'jcb',
        'jcp',
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
        'juniper',
        'kaufen',
        'kddi',
        'ke',
        'kerryhotels',
        'kerrylogistics',
        'kerryproperties',
        'kfh',
        'kg',
        'kh',
        'ki',
        'kia',
        'kim',
        'kinder',
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
        'lancaster',
        'lancia',
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
        'linde',
        'link',
        'lipsy',
        'live',
        'living',
        'lixil',
        'lk',
        'llc',
        'llp',
        'loan',
        'loans',
        'locker',
        'locus',
        'loft',
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
        'lupin',
        'luxe',
        'luxury',
        'lv',
        'ly',
        'ma',
        'macys',
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
        'maserati',
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
        'merckmsd',
        'metlife',
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
        'mutual',
        'mv',
        'mw',
        'mx',
        'my',
        'mz',
        'na',
        'nab',
        'nagoya',
        'name',
        'nationwide',
        'natura',
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
        'newholland',
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
        'northwesternmutual',
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
        'off',
        'office',
        'okinawa',
        'olayan',
        'olayangroup',
        'oldnavy',
        'ollo',
        'om',
        'omega',
        'one',
        'ong',
        'onl',
        'online',
        'onyourside',
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
        'passagens',
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
        'pramerica',
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
        'qvc',
        'racing',
        'radio',
        'raid',
        're',
        'read',
        'realestate',
        'realtor',
        'realty',
        'recipes',
        'red',
        'redstone',
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
        'rightathome',
        'ril',
        'rio',
        'rip',
        'rmit',
        'ro',
        'rocher',
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
        'sca',
        'scb',
        'schaeffler',
        'schmidt',
        'scholarships',
        'school',
        'schule',
        'schwarz',
        'science',
        'scjohnson',
        'scor',
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
        'ses',
        'seven',
        'sew',
        'sex',
        'sexy',
        'sfr',
        'sg',
        'sh',
        'shangrila',
        'sharp',
        'shaw',
        'shell',
        'shia',
        'shiksha',
        'shoes',
        'shop',
        'shopping',
        'shouji',
        'show',
        'showtime',
        'shriram',
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
        'space',
        'sport',
        'spot',
        'spreadbetting',
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
        'swiftcover',
        'swiss',
        'sx',
        'sy',
        'sydney',
        'symantec',
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
        'tiffany',
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
        'travelchannel',
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
        'volkswagen',
        'volvo',
        'vote',
        'voting',
        'voto',
        'voyage',
        'vu',
        'vuelos',
        'wales',
        'walmart',
        'walter',
        'wang',
        'wanggou',
        'watch',
        'watches',
        'weather',
        'weatherchannel',
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
        'wolterskluwer',
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
        'xfinity',
        'xihuan',
        'xin',
        'xn--11b4c3d',
        'xn--1ck2e1b',
        'xn--1qqw23a',
        'xn--2scrj9c',
        'xn--30rr7y',
        'xn--3bst00m',
        'xn--3ds443g',
        'xn--3e0b707e',
        'xn--3hcrj9c',
        'xn--3oq18vl8pn36a',
        'xn--3pxu8k',
        'xn--42c2d9a',
        'xn--45br5cyl',
        'xn--45brj9c',
        'xn--45q11c',
        'xn--4gbrim',
        'xn--54b7fta0cc',
        'xn--55qw42g',
        'xn--55qx5d',
        'xn--5su34j936bgsg',
        'xn--5tzm5g',
        'xn--6frz82g',
        'xn--6qq986b3xl',
        'xn--80adxhks',
        'xn--80ao21a',
        'xn--80aqecdr1a',
        'xn--80asehdb',
        'xn--80aswg',
        'xn--8y0a063a',
        'xn--90a3ac',
        'xn--90ae',
        'xn--90ais',
        'xn--9dbq2a',
        'xn--9et52u',
        'xn--9krt00a',
        'xn--b4w605ferd',
        'xn--bck1b9a5dre4c',
        'xn--c1avg',
        'xn--c2br7g',
        'xn--cck2b3b',
        'xn--cg4bki',
        'xn--clchc0ea0b2g2a9gcd',
        'xn--czr694b',
        'xn--czrs0t',
        'xn--czru2d',
        'xn--d1acj3b',
        'xn--d1alf',
        'xn--e1a4c',
        'xn--eckvdtc9d',
        'xn--efvy88h',
        'xn--fct429k',
        'xn--fhbei',
        'xn--fiq228c5hs',
        'xn--fiq64b',
        'xn--fiqs8s',
        'xn--fiqz9s',
        'xn--fjq720a',
        'xn--flw351e',
        'xn--fpcrj9c3d',
        'xn--fzc2c9e2c',
        'xn--fzys8d69uvgm',
        'xn--g2xx48c',
        'xn--gckr3f0f',
        'xn--gecrj9c',
        'xn--gk3at1e',
        'xn--h2breg3eve',
        'xn--h2brj9c',
        'xn--h2brj9c8c',
        'xn--hxt814e',
        'xn--i1b6b1a6a2e',
        'xn--imr513n',
        'xn--io0a7i',
        'xn--j1aef',
        'xn--j1amh',
        'xn--j6w193g',
        'xn--jlq61u9w7b',
        'xn--jvr189m',
        'xn--kcrx77d1x4a',
        'xn--kprw13d',
        'xn--kpry57d',
        'xn--kpu716f',
        'xn--kput3i',
        'xn--l1acc',
        'xn--lgbbat1ad8j',
        'xn--mgb9awbf',
        'xn--mgba3a3ejt',
        'xn--mgba3a4f16a',
        'xn--mgba7c0bbn0a',
        'xn--mgbaakc7dvf',
        'xn--mgbaam7a8h',
        'xn--mgbab2bd',
        'xn--mgbah1a3hjkrd',
        'xn--mgbai9azgqp6j',
        'xn--mgbayh7gpa',
        'xn--mgbbh1a',
        'xn--mgbbh1a71e',
        'xn--mgbc0a9azcg',
        'xn--mgbca7dzdo',
        'xn--mgbcpq6gpa1a',
        'xn--mgberp4a5d4ar',
        'xn--mgbgu82a',
        'xn--mgbi4ecexp',
        'xn--mgbpl2fh',
        'xn--mgbt3dhd',
        'xn--mgbtx2b',
        'xn--mgbx4cd0ab',
        'xn--mix891f',
        'xn--mk1bu44c',
        'xn--mxtq1m',
        'xn--ngbc5azd',
        'xn--ngbe9e0a',
        'xn--ngbrx',
        'xn--node',
        'xn--nqv7f',
        'xn--nqv7fs00ema',
        'xn--nyqy26a',
        'xn--o3cw4h',
        'xn--ogbpf8fl',
        'xn--otu796d',
        'xn--p1acf',
        'xn--p1ai',
        'xn--pbt977c',
        'xn--pgbs0dh',
        'xn--pssy2u',
        'xn--q7ce6a',
        'xn--q9jyb4c',
        'xn--qcka1pmc',
        'xn--qxa6a',
        'xn--qxam',
        'xn--rhqv96g',
        'xn--rovu88b',
        'xn--rvc1e0am3e',
        'xn--s9brj9c',
        'xn--ses554g',
        'xn--t60b56a',
        'xn--tckwe',
        'xn--tiq49xqyj',
        'xn--unup4y',
        'xn--vermgensberater-ctb',
        'xn--vermgensberatung-pwb',
        'xn--vhquv',
        'xn--vuq861b',
        'xn--w4r85el8fhu5dnra',
        'xn--w4rs40l',
        'xn--wgbh1c',
        'xn--wgbl6a',
        'xn--xhq521b',
        'xn--xkc2al3hye2a',
        'xn--xkc2dl3a5ee0h',
        'xn--y9a3aq',
        'xn--yfro4i67o',
        'xn--ygbi2ammx',
        'xn--zfr164b',
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
        '测试',
        'परीक्षा',
        '佛山',
        '集团',
        '在线',
        '한국',
        'ভারত',
        '八卦',
        'موقع',
        'বাংলা',
        '公益',
        '公司',
        '移动',
        '我爱你',
        'москва',
        'испытание',
        'қаз',
        'онлайн',
        'сайт',
        'срб',
        'бел',
        '테스트',
        'орг',
        '삼성',
        'சிங்கப்பூர்',
        '商标',
        '商城',
        'дети',
        'мкд',
        'טעסט',
        '中文网',
        '中信',
        '中国',
        '中國',
        '谷歌',
        'భారత్',
        'ලංකා',
        '測試',
        'ભારત',
        'भारत',
        'آزمایشی',
        'பரிட்சை',
        'संगठन',
        '网络',
        'укр',
        '香港',
        'δοκιμή',
        'إختبار',
        '台湾',
        '台灣',
        '手机',
        'мон',
        'الجزائر',
        'عمان',
        'ایران',
        'امارات',
        'بازار',
        'پاکستان',
        'الاردن',
        'بھارت',
        'المغرب',
        'السعودية',
        'سودان',
        'عراق',
        'مليسيا',
        'شبكة',
        'გე',
        '机构',
        '组织机构',
        'ไทย',
        'سورية',
        'рус',
        'рф',
        'تونس',
        'みんな',
        'グーグル',
        '世界',
        'ਭਾਰਤ',
        '网址',
        '游戏',
        'vermögensberater',
        'vermögensberatung',
        '企业',
        'مصر',
        'قطر',
        '广东',
        'இலங்கை',
        'இந்தியா',
        'հայ',
        '新加坡',
        'فلسطين',
        'テスト',
        '政务',
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
        'allow' => self::ALLOW_DNS,
        'idn'   => true,
        'tld'   => true,
        'ip'    => null
    ];

    /**
     * Sets validator options
     *
     * @see http://www.iana.org/cctld/specifications-policies-cctlds-01apr02.htm  Technical Specifications for ccTLDs
     * @param array $options Validator options
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

            $origenc = PHP_VERSION_ID < 50600
                        ? iconv_get_encoding('internal_encoding')
                        : ini_get('default_charset');
            if (PHP_VERSION_ID < 50600) {
                iconv_set_encoding('internal_encoding', 'UTF-8');
            } else {
                ini_set('default_charset', 'UTF-8');
            }
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
                            && !in_array($this->_tld, $this->_validTlds)) {
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
                    $regexChars = [0 => '/^[a-z0-9\x2d]{1,63}$/i'];
                    if ($this->_options['idn'] &&  isset($this->_validIdns[strtoupper($this->_tld)])) {
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
                        $isSubDomain = $domainPart != $lastDomainPart;
                        $partRegexChars = $isSubDomain ? ['/^[a-z0-9_\x2d]{1,63}$/i'] + $regexChars : $regexChars;
                        foreach ($partRegexChars as $regexKey => $regexChar) {
                            $status = preg_match($regexChar, $domainPart);
                            if ($status > 0) {
                                $length = 63;
                                if (array_key_exists(strtoupper($this->_tld), $this->_idnLength)
                                    && (array_key_exists($regexKey, $this->_idnLength[strtoupper($this->_tld)]))) {
                                    $length = $this->_idnLength[strtoupper($this->_tld)];
                                }

                                if (iconv_strlen($domainPart, 'UTF-8') > $length) {
                                    $this->_error(self::INVALID_HOSTNAME);
                                    $status = false;
                                } else {
                                    $checked = true;
                                    break;
                                }
                            }
                        }

                        if ($checked) {
                            ++$check;
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

            if (PHP_VERSION_ID < 50600) {
                iconv_set_encoding('internal_encoding', $origenc);
            } else {
                ini_set('default_charset', $origenc);
            }
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
     * @return string
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
}
