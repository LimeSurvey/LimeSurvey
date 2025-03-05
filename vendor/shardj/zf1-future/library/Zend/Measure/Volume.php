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
 * Class for handling acceleration conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Volume
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Volume extends Zend_Measure_Abstract
{
    public const STANDARD = 'CUBIC_METER';

    public const ACRE_FOOT           = 'ACRE_FOOT';
    public const ACRE_FOOT_SURVEY    = 'ACRE_FOOT_SURVEY';
    public const ACRE_INCH           = 'ACRE_INCH';
    public const BARREL_WINE         = 'BARREL_WINE';
    public const BARREL              = 'BARREL';
    public const BARREL_US_DRY       = 'BARREL_US_DRY';
    public const BARREL_US_FEDERAL   = 'BARREL_US_FEDERAL';
    public const BARREL_US           = 'BARREL_US';
    public const BARREL_US_PETROLEUM = 'BARREL_US_PETROLEUM';
    public const BOARD_FOOT          = 'BOARD_FOOT';
    public const BUCKET              = 'BUCKET';
    public const BUCKET_US           = 'BUCKET_US';
    public const BUSHEL              = 'BUSHEL';
    public const BUSHEL_US           = 'BUSHEL_US';
    public const CENTILTER           = 'CENTILITER';
    public const CORD                = 'CORD';
    public const CORD_FOOT           = 'CORD_FOOT';
    public const CUBIC_CENTIMETER    = 'CUBIC_CENTIMETER';
    public const CUBIC_CUBIT         = 'CUBIC_CUBIT';
    public const CUBIC_DECIMETER     = 'CUBIC_DECIMETER';
    public const CUBIC_DEKAMETER     = 'CUBIC_DEKAMETER';
    public const CUBIC_FOOT          = 'CUBIC_FOOT';
    public const CUBIC_INCH          = 'CUBIC_INCH';
    public const CUBIC_KILOMETER     = 'CUBIC_KILOMETER';
    public const CUBIC_METER         = 'CUBIC_METER';
    public const CUBIC_MILE          = 'CUBIC_MILE';
    public const CUBIC_MICROMETER    = 'CUBIC_MICROMETER';
    public const CUBIC_MILLIMETER    = 'CUBIC_MILLIMETER';
    public const CUBIC_YARD          = 'CUBIC_YARD';
    public const CUP_CANADA          = 'CUP_CANADA';
    public const CUP                 = 'CUP';
    public const CUP_US              = 'CUP_US';
    public const DECILITER           = 'DECILITER';
    public const DEKALITER           = 'DEKALITER';
    public const DRAM                = 'DRAM';
    public const DRUM_US             = 'DRUM_US';
    public const DRUM                = 'DRUM';
    public const FIFTH               = 'FIFTH';
    public const GALLON              = 'GALLON';
    public const GALLON_US_DRY       = 'GALLON_US_DRY';
    public const GALLON_US           = 'GALLON_US';
    public const GILL                = 'GILL';
    public const GILL_US             = 'GILL_US';
    public const HECTARE_METER       = 'HECTARE_METER';
    public const HECTOLITER          = 'HECTOLITER';
    public const HOGSHEAD            = 'HOGSHEAD';
    public const HOGSHEAD_US         = 'HOGSHEAD_US';
    public const JIGGER              = 'JIGGER';
    public const KILOLITER           = 'KILOLITER';
    public const LITER               = 'LITER';
    public const MEASURE             = 'MEASURE';
    public const MEGALITER           = 'MEGALITER';
    public const MICROLITER          = 'MICROLITER';
    public const MILLILITER          = 'MILLILITER';
    public const MINIM               = 'MINIM';
    public const MINIM_US            = 'MINIM_US';
    public const OUNCE               = 'OUNCE';
    public const OUNCE_US            = 'OUNCE_US';
    public const PECK                = 'PECK';
    public const PECK_US             = 'PECK_US';
    public const PINT                = 'PINT';
    public const PINT_US_DRY         = 'PINT_US_DRY';
    public const PINT_US             = 'PINT_US';
    public const PIPE                = 'PIPE';
    public const PIPE_US             = 'PIPE_US';
    public const PONY                = 'PONY';
    public const QUART_GERMANY       = 'QUART_GERMANY';
    public const QUART_ANCIENT       = 'QUART_ANCIENT';
    public const QUART               = 'QUART';
    public const QUART_US_DRY        = 'QUART_US_DRY';
    public const QUART_US            = 'QUART_US';
    public const QUART_UK            = 'QUART_UK';
    public const SHOT                = 'SHOT';
    public const STERE               = 'STERE';
    public const TABLESPOON          = 'TABLESPOON';
    public const TABLESPOON_UK       = 'TABLESPOON_UK';
    public const TABLESPOON_US       = 'TABLESPOON_US';
    public const TEASPOON            = 'TEASPOON';
    public const TEASPOON_UK         = 'TEASPOON_UK';
    public const TEASPOON_US         = 'TEASPOON_US';
    public const YARD                = 'YARD';

    /**
     * Calculations for all volume units
     *
     * @var array
     */
    protected $_units = [
        'ACRE_FOOT'           => ['1233.48185532', 'ac ft'],
        'ACRE_FOOT_SURVEY'    => ['1233.489',      'ac ft'],
        'ACRE_INCH'           => ['102.79015461',  'ac in'],
        'BARREL_WINE'         => ['0.143201835',   'bbl'],
        'BARREL'              => ['0.16365924',    'bbl'],
        'BARREL_US_DRY'       => [['' => '26.7098656608', '/' => '231'], 'bbl'],
        'BARREL_US_FEDERAL'   => ['0.1173477658',  'bbl'],
        'BARREL_US'           => ['0.1192404717',  'bbl'],
        'BARREL_US_PETROLEUM' => ['0.1589872956',  'bbl'],
        'BOARD_FOOT'          => [['' => '6.5411915904', '/' => '2772'], 'board foot'],
        'BUCKET'              => ['0.01818436',    'bucket'],
        'BUCKET_US'           => ['0.018927059',   'bucket'],
        'BUSHEL'              => ['0.03636872',    'bu'],
        'BUSHEL_US'           => ['0.03523907',    'bu'],
        'CENTILITER'          => ['0.00001',       'cl'],
        'CORD'                => ['3.624556416',   'cd'],
        'CORD_FOOT'           => ['0.453069552',   'cd ft'],
        'CUBIC_CENTIMETER'    => ['0.000001',      'cm³'],
        'CUBIC_CUBIT'         => ['0.144',         'cubit³'],
        'CUBIC_DECIMETER'     => ['0.001',         'dm³'],
        'CUBIC_DEKAMETER'     => ['1000',          'dam³'],
        'CUBIC_FOOT'          => [['' => '6.54119159', '/' => '231'],   'ft³'],
        'CUBIC_INCH'          => [['' => '0.0037854118', '/' => '231'], 'in³'],
        'CUBIC_KILOMETER'     => ['1.0e+9',        'km³'],
        'CUBIC_METER'         => ['1',             'm³'],
        'CUBIC_MILE'          => [['' => '0.0037854118', '/' => '231', '*' => '75271680', '*' => '3379200'],
                                       'mi³'],
        'CUBIC_MICROMETER'    => ['1.0e-18',       'µm³'],
        'CUBIC_MILLIMETER'    => ['1.0e-9',        'mm³'],
        'CUBIC_YARD'          => [['' => '0.0037854118', '/' => '231', '*' => '46656'], 'yd³'],
        'CUP_CANADA'          => ['0.0002273045',  'c'],
        'CUP'                 => ['0.00025',       'c'],
        'CUP_US'              => [['' => '0.0037854118', '/' => '16'], 'c'],
        'DECILITER'           => ['0.0001',        'dl'],
        'DEKALITER'           => ['0.001',         'dal'],
        'DRAM'                => [['' => '0.0037854118', '/' => '1024'], 'dr'],
        'DRUM_US'             => ['0.208197649',   'drum'],
        'DRUM'                => ['0.2',           'drum'],
        'FIFTH'               => ['0.00075708236', 'fifth'],
        'GALLON'              => ['0.00454609',    'gal'],
        'GALLON_US_DRY'       => ['0.0044048838',  'gal'],
        'GALLON_US'           => ['0.0037854118',  'gal'],
        'GILL'                => [['' => '0.00454609', '/' => '32'],   'gi'],
        'GILL_US'             => [['' => '0.0037854118', '/' => '32'], 'gi'],
        'HECTARE_METER'       => ['10000',         'ha m'],
        'HECTOLITER'          => ['0.1',           'hl'],
        'HOGSHEAD'            => ['0.28640367',    'hhd'],
        'HOGSHEAD_US'         => ['0.2384809434',  'hhd'],
        'JIGGER'              => [['' => '0.0037854118', '/' => '128', '*' => '1.5'], 'jigger'],
        'KILOLITER'           => ['1',             'kl'],
        'LITER'               => ['0.001',         'l'],
        'MEASURE'             => ['0.0077',        'measure'],
        'MEGALITER'           => ['1000',          'Ml'],
        'MICROLITER'          => ['1.0e-9',        'µl'],
        'MILLILITER'          => ['0.000001',      'ml'],
        'MINIM'               => [['' => '0.00454609', '/' => '76800'],  'min'],
        'MINIM_US'            => [['' => '0.0037854118','/' => '61440'], 'min'],
        'OUNCE'               => [['' => '0.00454609', '/' => '160'],    'oz'],
        'OUNCE_US'            => [['' => '0.0037854118', '/' => '128'],  'oz'],
        'PECK'                => ['0.00909218',    'pk'],
        'PECK_US'             => ['0.0088097676',  'pk'],
        'PINT'                => [['' => '0.00454609', '/' => '8'],   'pt'],
        'PINT_US_DRY'         => [['' => '0.0044048838', '/' => '8'], 'pt'],
        'PINT_US'             => [['' => '0.0037854118', '/' => '8'], 'pt'],
        'PIPE'                => ['0.49097772',    'pipe'],
        'PIPE_US'             => ['0.4769618868',  'pipe'],
        'PONY'                => [['' => '0.0037854118', '/' => '128'], 'pony'],
        'QUART_GERMANY'       => ['0.00114504',    'qt'],
        'QUART_ANCIENT'       => ['0.00108',       'qt'],
        'QUART'               => [['' => '0.00454609', '/' => '4'],   'qt'],
        'QUART_US_DRY'        => [['' => '0.0044048838', '/' => '4'], 'qt'],
        'QUART_US'            => [['' => '0.0037854118', '/' => '4'], 'qt'],
        'QUART_UK'            => ['0.29094976',    'qt'],
        'SHOT'                => [['' => '0.0037854118', '/' => '128'], 'shot'],
        'STERE'               => ['1',             'st'],
        'TABLESPOON'          => ['0.000015',      'tbsp'],
        'TABLESPOON_UK'       => [['' => '0.00454609', '/' => '320'],   'tbsp'],
        'TABLESPOON_US'       => [['' => '0.0037854118', '/' => '256'], 'tbsp'],
        'TEASPOON'            => ['0.000005',      'tsp'],
        'TEASPOON_UK'         => [['' => '0.00454609', '/' => '1280'],    'tsp'],
        'TEASPOON_US'         => [['' => '0.0037854118', '/' => '768'],   'tsp'],
        'YARD'                => [['' => '176.6121729408', '/' => '231'], 'yd'],
        'STANDARD'            => 'CUBIC_METER'
    ];
}
