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
 * Class for handling pressure conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Pressure
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Pressure extends Zend_Measure_Abstract
{
    public const STANDARD = 'NEWTON_PER_SQUARE_METER';

    public const ATMOSPHERE                           = 'ATMOSPHERE';
    public const ATMOSPHERE_TECHNICAL                 = 'ATMOSPHERE_TECHNICAL';
    public const ATTOBAR                              = 'ATTOBAR';
    public const ATTOPASCAL                           = 'ATTOPASCAL';
    public const BAR                                  = 'BAR';
    public const BARAD                                = 'BARAD';
    public const BARYE                                = 'BARYE';
    public const CENTIBAR                             = 'CENTIBAR';
    public const CENTIHG                              = 'CENTIHG';
    public const CENTIMETER_MERCURY_0C                = 'CENTIMETER_MERCURY_0C';
    public const CENTIMETER_WATER_4C                  = 'CENTIMETER_WATER_4C';
    public const CENTIPASCAL                          = 'CENTIPASCAL';
    public const CENTITORR                            = 'CENTITORR';
    public const DECIBAR                              = 'DECIBAR';
    public const DECIPASCAL                           = 'DECIPASCAL';
    public const DECITORR                             = 'DECITORR';
    public const DEKABAR                              = 'DEKABAR';
    public const DEKAPASCAL                           = 'DEKAPASCAL';
    public const DYNE_PER_SQUARE_CENTIMETER           = 'DYNE_PER_SQUARE_CENTIMETER';
    public const EXABAR                               = 'EXABAR';
    public const EXAPASCAL                            = 'EXAPASCAL';
    public const FEMTOBAR                             = 'FEMTOBAR';
    public const FEMTOPASCAL                          = 'FEMTOPASCAL';
    public const FOOT_AIR_0C                          = 'FOOT_AIR_0C';
    public const FOOT_AIR_15C                         = 'FOOT_AIR_15C';
    public const FOOT_HEAD                            = 'FOOT_HEAD';
    public const FOOT_MERCURY_0C                      = 'FOOT_MERCURY_0C';
    public const FOOT_WATER_4C                        = 'FOOT_WATER_4C';
    public const GIGABAR                              = 'GIGABAR';
    public const GIGAPASCAL                           = 'GIGAPASCAL';
    public const GRAM_FORCE_SQUARE_CENTIMETER         = 'GRAM_FORCE_SQUARE_CENTIMETER';
    public const HECTOBAR                             = 'HECTOBAR';
    public const HECTOPASCAL                          = 'HECTOPASCAL';
    public const INCH_AIR_0C                          = 'INCH_AIR_0C';
    public const INCH_AIR_15C                         = 'INCH_AIR_15C';
    public const INCH_MERCURY_0C                      = 'INCH_MERCURY_0C';
    public const INCH_WATER_4C                        = 'INCH_WATER_4C';
    public const KILOBAR                              = 'KILOBAR';
    public const KILOGRAM_FORCE_PER_SQUARE_CENTIMETER = 'KILOGRAM_FORCE_PER_SQUARE_CENTIMETER';
    public const KILOGRAM_FORCE_PER_SQUARE_METER      = 'KILOGRAM_FORCE_PER_SQUARE_METER';
    public const KILOGRAM_FORCE_PER_SQUARE_MILLIMETER = 'KILOGRAM_FORCE_PER_SQUARE_MILLIMETER';
    public const KILONEWTON_PER_SQUARE_METER          = 'KILONEWTON_PER_SQUARE_METER';
    public const KILOPASCAL                           = 'KILOPASCAL';
    public const KILOPOND_PER_SQUARE_CENTIMETER       = 'KILOPOND_PER_SQUARE_CENTIMETER';
    public const KILOPOND_PER_SQUARE_METER            = 'KILOPOND_PER_SQUARE_METER';
    public const KILOPOND_PER_SQUARE_MILLIMETER       = 'KILOPOND_PER_SQUARE_MILLIMETER';
    public const KIP_PER_SQUARE_FOOT                  = 'KIP_PER_SQUARE_FOOT';
    public const KIP_PER_SQUARE_INCH                  = 'KIP_PER_SQUARE_INCH';
    public const MEGABAR                              = 'MEGABAR';
    public const MEGANEWTON_PER_SQUARE_METER          = 'MEGANEWTON_PER_SQUARE_METER';
    public const MEGAPASCAL                           = 'MEGAPASCAL';
    public const METER_AIR_0C                         = 'METER_AIR_0C';
    public const METER_AIR_15C                        = 'METER_AIR_15C';
    public const METER_HEAD                           = 'METER_HEAD';
    public const MICROBAR                             = 'MICROBAR';
    public const MICROMETER_MERCURY_0C                = 'MICROMETER_MERCURY_0C';
    public const MICROMETER_WATER_4C                  = 'MICROMETER_WATER_4C';
    public const MICRON_MERCURY_0C                    = 'MICRON_MERCURY_0C';
    public const MICROPASCAL                          = 'MICROPASCAL';
    public const MILLIBAR                             = 'MILLIBAR';
    public const MILLIHG                              = 'MILLIHG';
    public const MILLIMETER_MERCURY_0C                = 'MILLIMETER_MERCURY_0C';
    public const MILLIMETER_WATER_4C                  = 'MILLIMETER_WATER_4C';
    public const MILLIPASCAL                          = 'MILLIPASCAL';
    public const MILLITORR                            = 'MILLITORR';
    public const NANOBAR                              = 'NANOBAR';
    public const NANOPASCAL                           = 'NANOPASCAL';
    public const NEWTON_PER_SQUARE_METER              = 'NEWTON_PER_SQUARE_METER';
    public const NEWTON_PER_SQUARE_MILLIMETER         = 'NEWTON_PER_SQUARE_MILLIMETER';
    public const OUNCE_PER_SQUARE_INCH                = 'OUNCE_PER_SQUARE_INCH';
    public const PASCAL                               = 'PASCAL';
    public const PETABAR                              = 'PETABAR';
    public const PETAPASCAL                           = 'PETAPASCAL';
    public const PICOBAR                              = 'PICOBAR';
    public const PICOPASCAL                           = 'PICOPASCAL';
    public const PIEZE                                = 'PIEZE';
    public const POUND_PER_SQUARE_FOOT                = 'POUND_PER_SQUARE_FOOT';
    public const POUND_PER_SQUARE_INCH                = 'POUND_PER_SQUARE_INCH';
    public const POUNDAL_PER_SQUARE_FOOT              = 'POUNDAL_PER_SQUARE_FOOT';
    public const STHENE_PER_SQUARE_METER              = 'STHENE_PER_SQUARE_METER';
    public const TECHNICAL_ATMOSPHERE                 = 'TECHNICAL_ATMOSPHERE';
    public const TERABAR                              = 'TERABAR';
    public const TERAPASCAL                           = 'TERAPASCAL';
    public const TON_PER_SQUARE_FOOT                  = 'TON_PER_SQUARE_FOOT';
    public const TON_PER_SQUARE_FOOT_SHORT            = 'TON_PER_SQUARE_FOOT_SHORT';
    public const TON_PER_SQUARE_INCH                  = 'TON_PER_SQUARE_INCH';
    public const TON_PER_SQUARE_INCH_SHORT            = 'TON_PER_SQUARE_INCH_SHORT';
    public const TON_PER_SQUARE_METER                 = 'TON_PER_SQUARE_METER';
    public const TORR                                 = 'TORR';
    public const WATER_COLUMN_CENTIMETER              = 'WATER_COLUMN_CENTIMETER';
    public const WATER_COLUMN_INCH                    = 'WATER_COLUMN_INCH';
    public const WATER_COLUMN_MILLIMETER              = 'WATER_COLUMN_MILLIMETER';
    public const YOCTOBAR                             = 'YOCTOBAR';
    public const YOCTOPASCAL                          = 'YOCTOPASCAL';
    public const YOTTABAR                             = 'YOTTABAR';
    public const YOTTAPASCAL                          = 'YOTTAPASCAL';
    public const ZEPTOBAR                             = 'ZEPTOBAR';
    public const ZEPTOPASCAL                          = 'ZEPTOPASCAL';
    public const ZETTABAR                             = 'ZETTABAR';
    public const ZETTAPASCAL                          = 'ZETTAPASCAL';

    /**
     * Calculations for all pressure units
     *
     * @var array
     */
    protected $_units = [
        'ATMOSPHERE'            => ['101325.01', 'atm'],
        'ATMOSPHERE_TECHNICAL'  => ['98066.5',   'atm'],
        'ATTOBAR'               => ['1.0e-13',   'ab'],
        'ATTOPASCAL'            => ['1.0e-18',   'aPa'],
        'BAR'                   => ['100000',    'b'],
        'BARAD'                 => ['0.1',       'barad'],
        'BARYE'                 => ['0.1',       'ba'],
        'CENTIBAR'              => ['1000',      'cb'],
        'CENTIHG'               => ['1333.2239', 'cHg'],
        'CENTIMETER_MERCURY_0C' => ['1333.2239', 'cm mercury (0°C)'],
        'CENTIMETER_WATER_4C'   => ['98.0665',   'cm water (4°C)'],
        'CENTIPASCAL'           => ['0.01',      'cPa'],
        'CENTITORR'             => ['1.3332237', 'cTorr'],
        'DECIBAR'               => ['10000',     'db'],
        'DECIPASCAL'            => ['0.1',       'dPa'],
        'DECITORR'              => ['13.332237', 'dTorr'],
        'DEKABAR'               => ['1000000',   'dab'],
        'DEKAPASCAL'            => ['10',        'daPa'],
        'DYNE_PER_SQUARE_CENTIMETER' => ['0.1',  'dyn/cm²'],
        'EXABAR'                => ['1.0e+23',   'Eb'],
        'EXAPASCAL'             => ['1.0e+18',   'EPa'],
        'FEMTOBAR'              => ['1.0e-10',   'fb'],
        'FEMTOPASCAL'           => ['1.0e-15',   'fPa'],
        'FOOT_AIR_0C'           => ['3.8640888', 'ft air (0°C)'],
        'FOOT_AIR_15C'          => ['3.6622931', 'ft air (15°C)'],
        'FOOT_HEAD'             => ['2989.0669', 'ft head'],
        'FOOT_MERCURY_0C'       => ['40636.664', 'ft mercury (0°C)'],
        'FOOT_WATER_4C'         => ['2989.0669', 'ft water (4°C)'],
        'GIGABAR'               => ['1.0e+14',   'Gb'],
        'GIGAPASCAL'            => ['1.0e+9',    'GPa'],
        'GRAM_FORCE_SQUARE_CENTIMETER' => ['98.0665', 'gf'],
        'HECTOBAR'              => ['1.0e+7',    'hb'],
        'HECTOPASCAL'           => ['100',       'hPa'],
        'INCH_AIR_0C'           => [['' => '3.8640888', '/' => '12'], 'in air (0°C)'],
        'INCH_AIR_15C'          => [['' => '3.6622931', '/' => '12'], 'in air (15°C)'],
        'INCH_MERCURY_0C'       => [['' => '40636.664', '/' => '12'], 'in mercury (0°C)'],
        'INCH_WATER_4C'         => [['' => '2989.0669', '/' => '12'], 'in water (4°C)'],
        'KILOBAR'               => ['1.0e+8',    'kb'],
        'KILOGRAM_FORCE_PER_SQUARE_CENTIMETER' => ['98066.5', 'kgf/cm²'],
        'KILOGRAM_FORCE_PER_SQUARE_METER'      => ['9.80665', 'kgf/m²'],
        'KILOGRAM_FORCE_PER_SQUARE_MILLIMETER' => ['9806650', 'kgf/mm²'],
        'KILONEWTON_PER_SQUARE_METER'          => ['1000',    'kN/m²'],
        'KILOPASCAL'            => ['1000',      'kPa'],
        'KILOPOND_PER_SQUARE_CENTIMETER' => ['98066.5', 'kp/cm²'],
        'KILOPOND_PER_SQUARE_METER'      => ['9.80665', 'kp/m²'],
        'KILOPOND_PER_SQUARE_MILLIMETER' => ['9806650', 'kp/mm²'],
        'KIP_PER_SQUARE_FOOT'   => [['' => '430.92233', '/' => '0.009'],   'kip/ft²'],
        'KIP_PER_SQUARE_INCH'   => [['' => '62052.81552', '/' => '0.009'], 'kip/in²'],
        'MEGABAR'               => ['1.0e+11',    'Mb'],
        'MEGANEWTON_PER_SQUARE_METER' => ['1000000', 'MN/m²'],
        'MEGAPASCAL'            => ['1000000',    'MPa'],
        'METER_AIR_0C'          => ['12.677457',  'm air (0°C)'],
        'METER_AIR_15C'         => ['12.015397',  'm air (15°C)'],
        'METER_HEAD'            => ['9804.139432', 'm head'],
        'MICROBAR'              => ['0.1',        'µb'],
        'MICROMETER_MERCURY_0C' => ['0.13332239', 'µm mercury (0°C)'],
        'MICROMETER_WATER_4C'   => ['0.00980665', 'µm water (4°C)'],
        'MICRON_MERCURY_0C'     => ['0.13332239', 'µ mercury (0°C)'],
        'MICROPASCAL'           => ['0.000001',   'µPa'],
        'MILLIBAR'              => ['100',        'mb'],
        'MILLIHG'               => ['133.32239',  'mHg'],
        'MILLIMETER_MERCURY_0C' => ['133.32239',  'mm mercury (0°C)'],
        'MILLIMETER_WATER_4C'   => ['9.80665',    'mm water (0°C)'],
        'MILLIPASCAL'           => ['0.001',      'mPa'],
        'MILLITORR'             => ['0.13332237', 'mTorr'],
        'NANOBAR'               => ['0.0001',     'nb'],
        'NANOPASCAL'            => ['1.0e-9',     'nPa'],
        'NEWTON_PER_SQUARE_METER'      => ['1',   'N/m²'],
        'NEWTON_PER_SQUARE_MILLIMETER' => ['1000000',   'N/mm²'],
        'OUNCE_PER_SQUARE_INCH'        => ['430.92233', 'oz/in²'],
        'PASCAL'                => ['1',          'Pa'],
        'PETABAR'               => ['1.0e+20',    'Pb'],
        'PETAPASCAL'            => ['1.0e+15',    'PPa'],
        'PICOBAR'               => ['0.0000001',  'pb'],
        'PICOPASCAL'            => ['1.0e-12',    'pPa'],
        'PIEZE'                 => ['1000',       'pz'],
        'POUND_PER_SQUARE_FOOT' => [['' => '430.92233', '/' => '9'], 'lb/ft²'],
        'POUND_PER_SQUARE_INCH' => ['6894.75728', 'lb/in²'],
        'POUNDAL_PER_SQUARE_FOOT' => ['1.4881639', 'pdl/ft²'],
        'STHENE_PER_SQUARE_METER' => ['1000',     'sn/m²'],
        'TECHNICAL_ATMOSPHERE'  => ['98066.5',    'at'],
        'TERABAR'               => ['1.0e+17',    'Tb'],
        'TERAPASCAL'            => ['1.0e+12',    'TPa'],
        'TON_PER_SQUARE_FOOT'   => [['' => '120658.2524', '/' => '1.125'],      't/ft²'],
        'TON_PER_SQUARE_FOOT_SHORT' => [['' => '430.92233', '/' => '0.0045'],   't/ft²'],
        'TON_PER_SQUARE_INCH'   => [['' => '17374788.3456', '/' => '1.125'],    't/in²'],
        'TON_PER_SQUARE_INCH_SHORT' => [['' => '62052.81552', '/' => '0.0045'], 't/in²'],
        'TON_PER_SQUARE_METER'  => ['9806.65',    't/m²'],
        'TORR'                  => ['133.32237',  'Torr'],
        'WATER_COLUMN_CENTIMETER' => ['98.0665',  'WC (cm)'],
        'WATER_COLUMN_INCH'       => [['' => '2989.0669', '/' => '12'], 'WC (in)'],
        'WATER_COLUMN_MILLIMETER' => ['9.80665',  'WC (mm)'],
        'YOCTOBAR'              => ['1.0e-19',    'yb'],
        'YOCTOPASCAL'           => ['1.0e-24',    'yPa'],
        'YOTTABAR'              => ['1.0e+29',    'Yb'],
        'YOTTAPASCAL'           => ['1.0e+24',    'YPa'],
        'ZEPTOBAR'              => ['1.0e-16',    'zb'],
        'ZEPTOPASCAL'           => ['1.0e-21',    'zPa'],
        'ZETTABAR'              => ['1.0e+26',    'Zb'],
        'ZETTAPASCAL'           => ['1.0e+21',    'ZPa'],
        'STANDARD'              => 'NEWTON_PER_SQUARE_METER'
    ];
}
