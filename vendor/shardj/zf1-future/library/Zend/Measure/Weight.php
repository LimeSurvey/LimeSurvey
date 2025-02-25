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
 * @category  Zend
 * @package   Zend_Measure
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id$
 */

/**
 * Implement needed classes
 */
require_once 'Zend/Measure/Abstract.php';
require_once 'Zend/Locale.php';

/**
 * Class for handling weight conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Weigth
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Weight extends Zend_Measure_Abstract
{
    public const STANDARD = 'KILOGRAM';

    public const ARRATEL                 = 'ARRATEL';
    public const ARTEL                   = 'ARTEL';
    public const ARROBA_PORTUGUESE       = 'ARROBA_PORTUGUESE';
    public const ARROBA                  = 'ARROBA';
    public const AS_                     = 'AS_';
    public const ASS                     = 'ASS';
    public const ATOMIC_MASS_UNIT_1960   = 'ATOMIC_MASS_UNIT_1960';
    public const ATOMIC_MASS_UNIT_1973   = 'ATOMIC_MASS_UNIT_1973';
    public const ATOMIC_MASS_UNIT_1986   = 'ATOMIC_MASS_UNIT_1986';
    public const ATOMIC_MASS_UNIT        = 'ATOMIC_MASS_UNIT';
    public const AVOGRAM                 = 'AVOGRAM';
    public const BAG                     = 'BAG';
    public const BAHT                    = 'BAHT';
    public const BALE                    = 'BALE';
    public const BALE_US                 = 'BALE_US';
    public const BISMAR_POUND            = 'BISMAR_POUND';
    public const CANDY                   = 'CANDY';
    public const CARAT_INTERNATIONAL     = 'CARAT_INTERNATIONAL';
    public const CARAT                   = 'CARAT';
    public const CARAT_UK                = 'CARAT_UK';
    public const CARAT_US_1913           = 'CARAT_US_1913';
    public const CARGA                   = 'CARGA';
    public const CATTI                   = 'CATTI';
    public const CATTI_JAPANESE          = 'CATTI_JAPANESE';
    public const CATTY                   = 'CATTY';
    public const CATTY_JAPANESE          = 'CATTY_JAPANESE';
    public const CATTY_THAI              = 'CATTY_THAI';
    public const CENTAL                  = 'CENTAL';
    public const CENTIGRAM               = 'CENTIGRAM';
    public const CENTNER                 = 'CENTNER';
    public const CENTNER_RUSSIAN         = 'CENTNER_RUSSIAN';
    public const CHALDER                 = 'CHALDER';
    public const CHALDRON                = 'CHALDRON';
    public const CHIN                    = 'CHIN';
    public const CHIN_JAPANESE           = 'CHIN_JAPANESE';
    public const CLOVE                   = 'CLOVE';
    public const CRITH                   = 'CRITH';
    public const DALTON                  = 'DALTON';
    public const DAN                     = 'DAN';
    public const DAN_JAPANESE            = 'DAN_JAPANESE';
    public const DECIGRAM                = 'DECIGRAM';
    public const DECITONNE               = 'DECITONNE';
    public const DEKAGRAM                = 'DEKAGRAM';
    public const DEKATONNE               = 'DEKATONNE';
    public const DENARO                  = 'DENARO';
    public const DENIER                  = 'DENIER';
    public const DRACHME                 = 'DRACHME';
    public const DRAM                    = 'DRAM';
    public const DRAM_APOTHECARIES       = 'DRAM_APOTHECARIES';
    public const DYNE                    = 'DYNE';
    public const ELECTRON                = 'ELECTRON';
    public const ELECTRONVOLT            = 'ELECTRONVOLT';
    public const ETTO                    = 'ETTO';
    public const EXAGRAM                 = 'EXAGRAM';
    public const FEMTOGRAM               = 'FEMTOGRAM';
    public const FIRKIN                  = 'FIRKIN';
    public const FLASK                   = 'FLASK';
    public const FOTHER                  = 'FOTHER';
    public const FOTMAL                  = 'FOTMAL';
    public const FUNT                    = 'FUNT';
    public const FUNTE                   = 'FUNTE';
    public const GAMMA                   = 'GAMMA';
    public const GIGAELECTRONVOLT        = 'GIGAELECTRONVOLT';
    public const GIGAGRAM                = 'GIGAGRAM';
    public const GIGATONNE               = 'GIGATONNE';
    public const GIN                     = 'GIN';
    public const GIN_JAPANESE            = 'GIN_JAPANESE';
    public const GRAIN                   = 'GRAIN';
    public const GRAM                    = 'GRAM';
    public const GRAN                    = 'GRAN';
    public const GRANO                   = 'GRANO';
    public const GRANI                   = 'GRANI';
    public const GROS                    = 'GROS';
    public const HECTOGRAM               = 'HECTOGRAM';
    public const HUNDRETWEIGHT           = 'HUNDRETWEIGHT';
    public const HUNDRETWEIGHT_US        = 'HUNDRETWEIGHT_US';
    public const HYL                     = 'HYL';
    public const JIN                     = 'JIN';
    public const JUPITER                 = 'JUPITER';
    public const KATI                    = 'KATI';
    public const KATI_JAPANESE           = 'KATI_JAPANESE';
    public const KEEL                    = 'KEEL';
    public const KEG                     = 'KEG';
    public const KILODALTON              = 'KILODALTON';
    public const KILOGRAM                = 'KILOGRAM';
    public const KILOGRAM_FORCE          = 'KILOGRAM_FORCE';
    public const KILOTON                 = 'KILOTON';
    public const KILOTON_US              = 'KILOTON_US';
    public const KILOTONNE               = 'KILOTONNE';
    public const KIN                     = 'KIN';
    public const KIP                     = 'KIP';
    public const KOYAN                   = 'KOYAN';
    public const KWAN                    = 'KWAN';
    public const LAST_GERMANY            = 'LAST_GERMANY';
    public const LAST                    = 'LAST';
    public const LAST_WOOL               = 'LAST_WOOL';
    public const LB                      = 'LB';
    public const LBS                     = 'LBS';
    public const LIANG                   = 'LIANG';
    public const LIBRA_ITALIAN           = 'LIBRE_ITALIAN';
    public const LIBRA_SPANISH           = 'LIBRA_SPANISH';
    public const LIBRA_PORTUGUESE        = 'LIBRA_PORTUGUESE';
    public const LIBRA_ANCIENT           = 'LIBRA_ANCIENT';
    public const LIBRA                   = 'LIBRA';
    public const LIVRE                   = 'LIVRE';
    public const LONG_TON                = 'LONG_TON';
    public const LOT                     = 'LOT';
    public const MACE                    = 'MACE';
    public const MAHND                   = 'MAHND';
    public const MARC                    = 'MARC';
    public const MARCO                   = 'MARCO';
    public const MARK                    = 'MARK';
    public const MARK_GERMAN             = 'MARK_GERMANY';
    public const MAUND                   = 'MAUND';
    public const MAUND_PAKISTAN          = 'MAUND_PAKISTAN';
    public const MEGADALTON              = 'MEGADALTON';
    public const MEGAGRAM                = 'MEGAGRAM';
    public const MEGATONNE               = 'MEGATONNE';
    public const MERCANTILE_POUND        = 'MERCANTILE_POUND';
    public const METRIC_TON              = 'METRIC_TON';
    public const MIC                     = 'MIC';
    public const MICROGRAM               = 'MICROGRAM';
    public const MILLIDALTON             = 'MILLIDALTON';
    public const MILLIER                 = 'MILLIER';
    public const MILLIGRAM               = 'MILLIGRAM';
    public const MILLIMASS_UNIT          = 'MILLIMASS_UNIT';
    public const MINA                    = 'MINA';
    public const MOMME                   = 'MOMME';
    public const MYRIAGRAM               = 'MYRIAGRAM';
    public const NANOGRAM                = 'NANOGRAM';
    public const NEWTON                  = 'NEWTON';
    public const OBOL                    = 'OBOL';
    public const OBOLOS                  = 'OBOLOS';
    public const OBOLUS                  = 'OBOLUS';
    public const OBOLOS_ANCIENT          = 'OBOLOS_ANCIENT';
    public const OBOLUS_ANCIENT          = 'OBOLUS_ANCIENT';
    public const OKA                     = 'OKA';
    public const ONCA                    = 'ONCA';
    public const ONCE                    = 'ONCE';
    public const ONCIA                   = 'ONCIA';
    public const ONZA                    = 'ONZA';
    public const ONS                     = 'ONS';
    public const OUNCE                   = 'OUNCE';
    public const OUNCE_FORCE             = 'OUNCE_FORCE';
    public const OUNCE_TROY              = 'OUNCE_TROY';
    public const PACKEN                  = 'PACKEN';
    public const PENNYWEIGHT             = 'PENNYWEIGHT';
    public const PETAGRAM                = 'PETAGRAM';
    public const PFUND                   = 'PFUND';
    public const PICOGRAM                = 'PICOGRAM';
    public const POINT                   = 'POINT';
    public const POND                    = 'POND';
    public const POUND                   = 'POUND';
    public const POUND_FORCE             = 'POUND_FORCE';
    public const POUND_METRIC            = 'POUND_METRIC';
    public const POUND_TROY              = 'POUND_TROY';
    public const PUD                     = 'PUD';
    public const POOD                    = 'POOD';
    public const PUND                    = 'PUND';
    public const QIAN                    = 'QIAN';
    public const QINTAR                  = 'QINTAR';
    public const QUARTER                 = 'QUARTER';
    public const QUARTER_US              = 'QUARTER_US';
    public const QUARTER_TON             = 'QUARTER_TON';
    public const QUARTERN                = 'QUARTERN';
    public const QUARTERN_LOAF           = 'QUARTERN_LOAF';
    public const QUINTAL_FRENCH          = 'QUINTAL_FRENCH';
    public const QUINTAL                 = 'QUINTAL';
    public const QUINTAL_PORTUGUESE      = 'QUINTAL_PORTUGUESE';
    public const QUINTAL_SPAIN           = 'QUINTAL_SPAIN';
    public const REBAH                   = 'REBAH';
    public const ROTL                    = 'ROTL';
    public const ROTEL                   = 'ROTEL';
    public const ROTTLE                  = 'ROTTLE';
    public const RATEL                   = 'RATEL';
    public const SACK                    = 'SACK';
    public const SCRUPLE                 = 'SCRUPLE';
    public const SEER                    = 'SEER';
    public const SEER_PAKISTAN           = 'SEER_PAKISTAN';
    public const SHEKEL                  = 'SHEKEL';
    public const SHORT_TON               = 'SHORT_TON';
    public const SLINCH                  = 'SLINCH';
    public const SLUG                    = 'SLUG';
    public const STONE                   = 'STONE';
    public const TAEL                    = 'TAEL';
    public const TAHIL_JAPANESE          = 'TAHIL_JAPANESE';
    public const TAHIL                   = 'TAHIL';
    public const TALENT                  = 'TALENT';
    public const TAN                     = 'TAN';
    public const TECHNISCHE_MASS_EINHEIT = 'TECHNISCHE_MASS_EINHEIT';
    public const TERAGRAM                = 'TERAGRAM';
    public const TETRADRACHM             = 'TETRADRACHM';
    public const TICAL                   = 'TICAL';
    public const TOD                     = 'TOD';
    public const TOLA                    = 'TOLA';
    public const TOLA_PAKISTAN           = 'TOLA_PAKISTAN';
    public const TON_UK                  = 'TON_UK';
    public const TON                     = 'TON';
    public const TON_US                  = 'TON_US';
    public const TONELADA_PORTUGUESE     = 'TONELADA_PORTUGUESE';
    public const TONELADA                = 'TONELADA';
    public const TONNE                   = 'TONNE';
    public const TONNEAU                 = 'TONNEAU';
    public const TOVAR                   = 'TOVAR';
    public const TROY_OUNCE              = 'TROY_OUNCE';
    public const TROY_POUND              = 'TROY_POUND';
    public const TRUSS                   = 'TRUSS';
    public const UNCIA                   = 'UNCIA';
    public const UNZE                    = 'UNZE';
    public const VAGON                   = 'VAGON';
    public const YOCTOGRAM               = 'YOCTOGRAM';
    public const YOTTAGRAM               = 'YOTTAGRAM';
    public const ZENTNER                 = 'ZENTNER';
    public const ZEPTOGRAM               = 'ZEPTOGRAM';
    public const ZETTAGRAM               = 'ZETTAGRAM';

    /**
     * Calculations for all weight units
     *
     * @var array
     */
    protected $_units = [
        'ARRATEL'               => ['0.5',            'arratel'],
        'ARTEL'                 => ['0.5',            'artel'],
        'ARROBA_PORTUGUESE'     => ['14.69',          'arroba'],
        'ARROBA'                => ['11.502',         '@'],
        'AS_'                   => ['0.000052',       'as'],
        'ASS'                   => ['0.000052',       'ass'],
        'ATOMIC_MASS_UNIT_1960' => ['1.6603145e-27',  'amu'],
        'ATOMIC_MASS_UNIT_1973' => ['1.6605655e-27',  'amu'],
        'ATOMIC_MASS_UNIT_1986' => ['1.6605402e-27',  'amu'],
        'ATOMIC_MASS_UNIT'      => ['1.66053873e-27', 'amu'],
        'AVOGRAM'               => ['1.6605402e-27',  'avogram'],
        'BAG'                   => ['42.63768278',    'bag'],
        'BAHT'                  => ['0.015',          'baht'],
        'BALE'                  => ['326.5865064',    'bl'],
        'BALE_US'               => ['217.7243376',    'bl'],
        'BISMAR_POUND'          => ['5.993',          'bismar pound'],
        'CANDY'                 => ['254',            'candy'],
        'CARAT_INTERNATIONAL'   => ['0.0002',         'ct'],
        'CARAT'                 => ['0.0002',         'ct'],
        'CARAT_UK'              => ['0.00025919564',  'ct'],
        'CARAT_US_1913'         => ['0.0002053',      'ct'],
        'CARGA'                 => ['140',            'carga'],
        'CATTI'                 => ['0.604875',       'catti'],
        'CATTI_JAPANESE'        => ['0.594',          'catti'],
        'CATTY'                 => ['0.5',            'catty'],
        'CATTY_JAPANESE'        => ['0.6',            'catty'],
        'CATTY_THAI'            => ['0.6',            'catty'],
        'CENTAL'                => ['45.359237',      'cH'],
        'CENTIGRAM'             => ['0.00001',        'cg'],
        'CENTNER'               => ['50',             'centner'],
        'CENTNER_RUSSIAN'       => ['100',            'centner'],
        'CHALDER'               => ['2692.52',        'chd'],
        'CHALDRON'              => ['2692.52',        'chd'],
        'CHIN'                  => ['0.5',            'chin'],
        'CHIN_JAPANESE'         => ['0.6',            'chin'],
        'CLOVE'                 => ['3.175',          'clove'],
        'CRITH'                 => ['0.000089885',    'crith'],
        'DALTON'                => ['1.6605402e-27',  'D'],
        'DAN'                   => ['50',             'dan'],
        'DAN_JAPANESE'          => ['60',             'dan'],
        'DECIGRAM'              => ['0.0001',         'dg'],
        'DECITONNE'             => ['100',            'dt'],
        'DEKAGRAM'              => ['0.01',           'dag'],
        'DEKATONNE'             => ['10000',          'dat'],
        'DENARO'                => ['0.0011',         'denaro'],
        'DENIER'                => ['0.001275',       'denier'],
        'DRACHME'               => ['0.0038',         'drachme'],
        'DRAM'                  => [['' => '0.45359237', '/' => '256'], 'dr'],
        'DRAM_APOTHECARIES'     => ['0.0038879346',   'dr'],
        'DYNE'                  => ['1.0197162e-6',   'dyn'],
        'ELECTRON'              => ['9.109382e-31',   'e−'],
        'ELECTRONVOLT'          => ['1.782662e-36',   'eV'],
        'ETTO'                  => ['0.1',            'hg'],
        'EXAGRAM'               => ['1.0e+15',        'Eg'],
        'FEMTOGRAM'             => ['1.0e-18',        'fg'],
        'FIRKIN'                => ['25.40117272',    'fir'],
        'FLASK'                 => ['34.7',           'flask'],
        'FOTHER'                => ['979.7595192',    'fother'],
        'FOTMAL'                => ['32.65865064',    'fotmal'],
        'FUNT'                  => ['0.4095',         'funt'],
        'FUNTE'                 => ['0.4095',         'funte'],
        'GAMMA'                 => ['0.000000001',    'gamma'],
        'GIGAELECTRONVOLT'      => ['1.782662e-27',   'GeV'],
        'GIGAGRAM'              => ['1000000',        'Gg'],
        'GIGATONNE'             => ['1.0e+12',        'Gt'],
        'GIN'                   => ['0.6',            'gin'],
        'GIN_JAPANESE'          => ['0.594',          'gin'],
        'GRAIN'                 => ['0.00006479891',  'gr'],
        'GRAM'                  => ['0.001',          'g'],
        'GRAN'                  => ['0.00082',        'gran'],
        'GRANO'                 => ['0.00004905',     'grano'],
        'GRANI'                 => ['0.00004905',     'grani'],
        'GROS'                  => ['0.003824',       'gros'],
        'HECTOGRAM'             => ['0.1',            'hg'],
        'HUNDRETWEIGHT'         => ['50.80234544',    'cwt'],
        'HUNDRETWEIGHT_US'      => ['45.359237',      'cwt'],
        'HYL'                   => ['9.80665',        'hyl'],
        'JIN'                   => ['0.5',            'jin'],
        'JUPITER'               => ['1.899e+27',      'jupiter'],
        'KATI'                  => ['0.5',            'kati'],
        'KATI_JAPANESE'         => ['0.6',            'kati'],
        'KEEL'                  => ['21540.19446656', 'keel'],
        'KEG'                   => ['45.359237',      'keg'],
        'KILODALTON'            => ['1.6605402e-24',  'kD'],
        'KILOGRAM'              => ['1',              'kg'],
        'KILOGRAM_FORCE'        => ['1',              'kgf'],
        'KILOTON'               => ['1016046.9088',   'kt'],
        'KILOTON_US'            => ['907184.74',      'kt'],
        'KILOTONNE'             => ['1000000',        'kt'],
        'KIN'                   => ['0.6',            'kin'],
        'KIP'                   => ['453.59237',      'kip'],
        'KOYAN'                 => ['2419',           'koyan'],
        'KWAN'                  => ['3.75',           'kwan'],
        'LAST_GERMANY'          => ['2000',           'last'],
        'LAST'                  => ['1814.36948',     'last'],
        'LAST_WOOL'             => ['1981.29147216',  'last'],
        'LB'                    => ['0.45359237',     'lb'],
        'LBS'                   => ['0.45359237',     'lbs'],
        'LIANG'                 => ['0.05',           'liang'],
        'LIBRE_ITALIAN'         => ['0.339',          'lb'],
        'LIBRA_SPANISH'         => ['0.459',          'lb'],
        'LIBRA_PORTUGUESE'      => ['0.459',          'lb'],
        'LIBRA_ANCIENT'         => ['0.323',          'lb'],
        'LIBRA'                 => ['1',              'lb'],
        'LIVRE'                 => ['0.4895',         'livre'],
        'LONG_TON'              => ['1016.0469088',   't'],
        'LOT'                   => ['0.015',          'lot'],
        'MACE'                  => ['0.003778',       'mace'],
        'MAHND'                 => ['0.9253284348',   'mahnd'],
        'MARC'                  => ['0.24475',        'marc'],
        'MARCO'                 => ['0.23',           'marco'],
        'MARK'                  => ['0.2268',         'mark'],
        'MARK_GERMANY'          => ['0.2805',         'mark'],
        'MAUND'                 => ['37.3242',        'maund'],
        'MAUND_PAKISTAN'        => ['40',             'maund'],
        'MEGADALTON'            => ['1.6605402e-21',  'MD'],
        'MEGAGRAM'              => ['1000',           'Mg'],
        'MEGATONNE'             => ['1.0e+9',         'Mt'],
        'MERCANTILE_POUND'      => ['0.46655',        'lb merc'],
        'METRIC_TON'            => ['1000',           't'],
        'MIC'                   => ['1.0e-9',         'mic'],
        'MICROGRAM'             => ['1.0e-9',         '�g'],
        'MILLIDALTON'           => ['1.6605402e-30',  'mD'],
        'MILLIER'               => ['1000',           'millier'],
        'MILLIGRAM'             => ['0.000001',       'mg'],
        'MILLIMASS_UNIT'        => ['1.6605402e-30',  'mmu'],
        'MINA'                  => ['0.499',          'mina'],
        'MOMME'                 => ['0.00375',        'momme'],
        'MYRIAGRAM'             => ['10',             'myg'],
        'NANOGRAM'              => ['1.0e-12',        'ng'],
        'NEWTON'                => ['0.101971621',    'N'],
        'OBOL'                  => ['0.0001',         'obol'],
        'OBOLOS'                => ['0.0001',         'obolos'],
        'OBOLUS'                => ['0.0001',         'obolus'],
        'OBOLOS_ANCIENT'        => ['0.0005',         'obolos'],
        'OBOLUS_ANCIENT'        => ['0.00057',        'obolos'],
        'OKA'                   => ['1.28',           'oka'],
        'ONCA'                  => ['0.02869',        'onca'],
        'ONCE'                  => ['0.03059',        'once'],
        'ONCIA'                 => ['0.0273',         'oncia'],
        'ONZA'                  => ['0.02869',        'onza'],
        'ONS'                   => ['0.1',            'ons'],
        'OUNCE'                 => [['' => '0.45359237', '/' => '16'],    'oz'],
        'OUNCE_FORCE'           => [['' => '0.45359237', '/' => '16'],    'ozf'],
        'OUNCE_TROY'            => [['' => '65.31730128', '/' => '2100'], 'oz'],
        'PACKEN'                => ['490.79',         'packen'],
        'PENNYWEIGHT'           => [['' => '65.31730128', '/' => '42000'], 'dwt'],
        'PETAGRAM'              => ['1.0e+12',        'Pg'],
        'PFUND'                 => ['0.5',            'pfd'],
        'PICOGRAM'              => ['1.0e-15',        'pg'],
        'POINT'                 => ['0.000002',       'pt'],
        'POND'                  => ['0.5',            'pond'],
        'POUND'                 => ['0.45359237',     'lb'],
        'POUND_FORCE'           => ['0.4535237',      'lbf'],
        'POUND_METRIC'          => ['0.5',            'lb'],
        'POUND_TROY'            => [['' => '65.31730128', '/' => '175'], 'lb'],
        'PUD'                   => ['16.3',           'pud'],
        'POOD'                  => ['16.3',           'pood'],
        'PUND'                  => ['0.5',            'pund'],
        'QIAN'                  => ['0.005',          'qian'],
        'QINTAR'                => ['50',             'qintar'],
        'QUARTER'               => ['12.70058636',    'qtr'],
        'QUARTER_US'            => ['11.33980925',    'qtr'],
        'QUARTER_TON'           => ['226.796185',     'qtr'],
        'QUARTERN'              => ['1.587573295',    'quartern'],
        'QUARTERN_LOAF'         => ['1.81436948',     'quartern-loaf'],
        'QUINTAL_FRENCH'        => ['48.95',          'q'],
        'QUINTAL'               => ['100',            'q'],
        'QUINTAL_PORTUGUESE'    => ['58.752',         'q'],
        'QUINTAL_SPAIN'         => ['45.9',           'q'],
        'REBAH'                 => ['0.2855',         'rebah'],
        'ROTL'                  => ['0.5',            'rotl'],
        'ROTEL'                 => ['0.5',            'rotel'],
        'ROTTLE'                => ['0.5',            'rottle'],
        'RATEL'                 => ['0.5',            'ratel'],
        'SACK'                  => ['165.10762268',   'sack'],
        'SCRUPLE'               => [['' => '65.31730128', '/' => '50400'], 's'],
        'SEER'                  => ['0.933105',       'seer'],
        'SEER_PAKISTAN'         => ['1',              'seer'],
        'SHEKEL'                => ['0.01142',        'shekel'],
        'SHORT_TON'             => ['907.18474',      'st'],
        'SLINCH'                => ['175.126908',     'slinch'],
        'SLUG'                  => ['14.593903',      'slug'],
        'STONE'                 => ['6.35029318',     'st'],
        'TAEL'                  => ['0.03751',        'tael'],
        'TAHIL_JAPANESE'        => ['0.03751',        'tahil'],
        'TAHIL'                 => ['0.05',           'tahil'],
        'TALENT'                => ['30',             'talent'],
        'TAN'                   => ['50',             'tan'],
        'TECHNISCHE_MASS_EINHEIT' => ['9.80665',      'TME'],
        'TERAGRAM'              => ['1.0e+9',         'Tg'],
        'TETRADRACHM'           => ['0.014',          'tetradrachm'],
        'TICAL'                 => ['0.0164',         'tical'],
        'TOD'                   => ['12.70058636',    'tod'],
        'TOLA'                  => ['0.0116638125',   'tola'],
        'TOLA_PAKISTAN'         => ['0.0125',         'tola'],
        'TON_UK'                => ['1016.0469088',   't'],
        'TON'                   => ['1000',           't'],
        'TON_US'                => ['907.18474',      't'],
        'TONELADA_PORTUGUESE'   => ['793.15',         'tonelada'],
        'TONELADA'              => ['919.9',          'tonelada'],
        'TONNE'                 => ['1000',           't'],
        'TONNEAU'               => ['979',            'tonneau'],
        'TOVAR'                 => ['128.8',          'tovar'],
        'TROY_OUNCE'            => [['' => '65.31730128', '/' => '2100'], 'troy oz'],
        'TROY_POUND'            => [['' => '65.31730128', '/' => '175'],  'troy lb'],
        'TRUSS'                 => ['25.40117272',    'truss'],
        'UNCIA'                 => ['0.0272875',      'uncia'],
        'UNZE'                  => ['0.03125',        'unze'],
        'VAGON'                 => ['10000',          'vagon'],
        'YOCTOGRAM'             => ['1.0e-27',        'yg'],
        'YOTTAGRAM'             => ['1.0e+21',        'Yg'],
        'ZENTNER'               => ['50',             'Ztr'],
        'ZEPTOGRAM'             => ['1.0e-24',        'zg'],
        'ZETTAGRAM'             => ['1.0e+18',        'Zg'],
        'STANDARD'              => 'KILOGRAM'
    ];
}
