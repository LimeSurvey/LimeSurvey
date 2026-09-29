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
 * @subpackage Zend_Measure_Viscosity_Dynamic
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Viscosity_Dynamic extends Zend_Measure_Abstract
{
    public const STANDARD = 'KILOGRAM_PER_METER_SECOND';

    public const CENTIPOISE                              = 'CENTIPOISE';
    public const DECIPOISE                               = 'DECIPOISE';
    public const DYNE_SECOND_PER_SQUARE_CENTIMETER       = 'DYNE_SECOND_PER_SQUARE_CENTIMETER';
    public const GRAM_FORCE_SECOND_PER_SQUARE_CENTIMETER = 'GRAM_FORCE_SECOND_PER_SQUARE_CENTIMETER';
    public const GRAM_PER_CENTIMETER_SECOND              = 'GRAM_PER_CENTIMETER_SECOND';
    public const KILOGRAM_FORCE_SECOND_PER_SQUARE_METER  = 'KILOGRAM_FORCE_SECOND_PER_SQUARE_METER';
    public const KILOGRAM_PER_METER_HOUR                 = 'KILOGRAM_PER_METER_HOUR';
    public const KILOGRAM_PER_METER_SECOND               = 'KILOGRAM_PER_METER_SECOND';
    public const MILLIPASCAL_SECOND                      = 'MILLIPASCAL_SECOND';
    public const MILLIPOISE                              = 'MILLIPOISE';
    public const NEWTON_SECOND_PER_SQUARE_METER          = 'NEWTON_SECOND_PER_SQUARE_METER';
    public const PASCAL_SECOND                           = 'PASCAL_SECOND';
    public const POISE                                   = 'POISE';
    public const POISEUILLE                              = 'POISEUILLE';
    public const POUND_FORCE_SECOND_PER_SQUARE_FEET      = 'POUND_FORCE_SECOND_PER_SQUARE_FEET';
    public const POUND_FORCE_SECOND_PER_SQUARE_INCH      = 'POUND_FORCE_SECOND_PER_SQUARE_INCH';
    public const POUND_PER_FOOT_HOUR                     = 'POUND_PER_FOOT_HOUR';
    public const POUND_PER_FOOT_SECOND                   = 'POUND_PER_FOOT_SECOND';
    public const POUNDAL_HOUR_PER_SQUARE_FOOT            = 'POUNDAL_HOUR_PER_SQUARE_FOOT';
    public const POUNDAL_SECOND_PER_SQUARE_FOOT          = 'POUNDAL_SECOND_PER_SQUARE_FOOT';
    public const REYN                                    = 'REYN';
    public const SLUG_PER_FOOT_SECOND                    = 'SLUG_PER_FOOT_SECOND';
    public const LBFS_PER_SQUARE_FOOT                    = 'LBFS_PER_SQUARE_FOOT';
    public const NS_PER_SQUARE_METER                     = 'NS_PER_SQUARE_METER';
    public const WATER_20C                               = 'WATER_20C';
    public const WATER_40C                               = 'WATER_40C';
    public const HEAVY_OIL_20C                           = 'HEAVY_OIL_20C';
    public const HEAVY_OIL_40C                           = 'HEAVY_OIL_40C';
    public const GLYCERIN_20C                            = 'GLYCERIN_20C';
    public const GLYCERIN_40C                            = 'GLYCERIN_40C';
    public const SAE_5W_MINUS18C                         = 'SAE_5W_MINUS18C';
    public const SAE_10W_MINUS18C                        = 'SAE_10W_MINUS18C';
    public const SAE_20W_MINUS18C                        = 'SAE_20W_MINUS18C';
    public const SAE_5W_99C                              = 'SAE_5W_99C';
    public const SAE_10W_99C                             = 'SAE_10W_99C';
    public const SAE_20W_99C                             = 'SAE_20W_99C';

    /**
     * Calculations for all dynamic viscosity units
     *
     * @var array
     */
    protected $_units = [
        'CENTIPOISE'          => ['0.001',      'cP'],
        'DECIPOISE'           => ['0.01',       'dP'],
        'DYNE_SECOND_PER_SQUARE_CENTIMETER'       => ['0.1',     'dyn s/cm²'],
        'GRAM_FORCE_SECOND_PER_SQUARE_CENTIMETER' => ['98.0665', 'gf s/cm²'],
        'GRAM_PER_CENTIMETER_SECOND'              => ['0.1',     'g/cm s'],
        'KILOGRAM_FORCE_SECOND_PER_SQUARE_METER'  => ['9.80665', 'kgf s/m²'],
        'KILOGRAM_PER_METER_HOUR'    => [['' => '1', '/' => '3600'], 'kg/m h'],
        'KILOGRAM_PER_METER_SECOND'  => ['1',   'kg/ms'],
        'MILLIPASCAL_SECOND'  => ['0.001',      'mPa s'],
        'MILLIPOISE'          => ['0.0001',     'mP'],
        'NEWTON_SECOND_PER_SQUARE_METER' => ['1', 'N s/m²'],
        'PASCAL_SECOND'       => ['1',          'Pa s'],
        'POISE'               => ['0.1',        'P'],
        'POISEUILLE'          => ['1',          'Pl'],
        'POUND_FORCE_SECOND_PER_SQUARE_FEET' => ['47.880259',  'lbf s/ft²'],
        'POUND_FORCE_SECOND_PER_SQUARE_INCH' => ['6894.75729', 'lbf s/in²'],
        'POUND_PER_FOOT_HOUR' => ['0.00041337887',             'lb/ft h'],
        'POUND_PER_FOOT_SECOND'          => ['1.4881639',      'lb/ft s'],
        'POUNDAL_HOUR_PER_SQUARE_FOOT'   => ['0.00041337887',  'pdl h/ft²'],
        'POUNDAL_SECOND_PER_SQUARE_FOOT' => ['1.4881639',      'pdl s/ft²'],
        'REYN'                => ['6894.75729', 'reyn'],
        'SLUG_PER_FOOT_SECOND'=> ['47.880259',  'slug/ft s'],
        'WATER_20C'           => ['0.001',      'water (20°)'],
        'WATER_40C'           => ['0.00065',    'water (40°)'],
        'HEAVY_OIL_20C'       => ['0.45',       'oil (20°)'],
        'HEAVY_OIL_40C'       => ['0.11',       'oil (40°)'],
        'GLYCERIN_20C'        => ['1.41',       'glycerin (20°)'],
        'GLYCERIN_40C'        => ['0.284',      'glycerin (40°)'],
        'SAE_5W_MINUS18C'     => ['1.2',        'SAE 5W (-18°)'],
        'SAE_10W_MINUS18C'    => ['2.4',        'SAE 10W (-18°)'],
        'SAE_20W_MINUS18C'    => ['9.6',        'SAE 20W (-18°)'],
        'SAE_5W_99C'          => ['0.0039',     'SAE 5W (99°)'],
        'SAE_10W_99C'         => ['0.0042',     'SAE 10W (99°)'],
        'SAE_20W_99C'         => ['0.0057',     'SAE 20W (99°)'],
        'STANDARD'            => 'KILOGRAM_PER_METER_SECOND'
    ];
}
