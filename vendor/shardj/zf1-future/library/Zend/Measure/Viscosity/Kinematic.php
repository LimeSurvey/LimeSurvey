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
 * @subpackage Zend_Measure_Viscosity_Kinematic
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Viscosity_Kinematic extends Zend_Measure_Abstract
{
    public const STANDARD = 'SQUARE_METER_PER_SECOND';

    public const CENTISTOKES                     = 'CENTISTOKES';
    public const LENTOR                          = 'LENTOR';
    public const LITER_PER_CENTIMETER_DAY        = 'LITER_PER_CENTIMETER_DAY';
    public const LITER_PER_CENTIMETER_HOUR       = 'LITER_PER_CENTIMETER_HOUR';
    public const LITER_PER_CENTIMETER_MINUTE     = 'LITER_PER_CENTIMETER_MINUTE';
    public const LITER_PER_CENTIMETER_SECOND     = 'LITER_PER_CENTIMETER_SECOND';
    public const POISE_CUBIC_CENTIMETER_PER_GRAM = 'POISE_CUBIC_CENTIMETER_PER_GRAM';
    public const SQUARE_CENTIMETER_PER_DAY       = 'SQUARE_CENTIMETER_PER_DAY';
    public const SQUARE_CENTIMETER_PER_HOUR      = 'SQUARE_CENTIMETER_PER_HOUR';
    public const SQUARE_CENTIMETER_PER_MINUTE    = 'SQUARE_CENTIMETER_PER_MINUTE';
    public const SQUARE_CENTIMETER_PER_SECOND    = 'SQUARE_CENTIMETER_PER_SECOND';
    public const SQUARE_FOOT_PER_DAY             = 'SQUARE_FOOT_PER_DAY';
    public const SQUARE_FOOT_PER_HOUR            = 'SQUARE_FOOT_PER_HOUR';
    public const SQUARE_FOOT_PER_MINUTE          = 'SQUARE_FOOT_PER_MINUTE';
    public const SQUARE_FOOT_PER_SECOND          = 'SQUARE_FOOT_PER_SECOND';
    public const SQUARE_INCH_PER_DAY             = 'SQUARE_INCH_PER_DAY';
    public const SQUARE_INCH_PER_HOUR            = 'SQUARE_INCH_PER_HOUR';
    public const SQUARE_INCH_PER_MINUTE          = 'SQUARE_INCH_PER_MINUTE';
    public const SQUARE_INCH_PER_SECOND          = 'SQUARE_INCH_PER_SECOND';
    public const SQUARE_METER_PER_DAY            = 'SQUARE_METER_PER_DAY';
    public const SQUARE_METER_PER_HOUR           = 'SQUARE_METER_PER_HOUR';
    public const SQUARE_METER_PER_MINUTE         = 'SQUARE_METER_PER_MINUTE';
    public const SQUARE_METER_PER_SECOND         = 'SQUARE_METER_PER_SECOND';
    public const SQUARE_MILLIMETER_PER_DAY       = 'SQUARE_MILLIMETER_PER_DAY';
    public const SQUARE_MILLIMETER_PER_HOUR      = 'SQUARE_MILLIMETER_PER_HOUR';
    public const SQUARE_MILLIMETER_PER_MINUTE    = 'SQUARE_MILLIMETER_PER_MINUTE';
    public const SQUARE_MILLIMETER_PER_SECOND    = 'SQUARE_MILLIMETER_PER_SECOND';
    public const STOKES                          = 'STOKES';

    /**
     * Calculations for all kinematic viscosity units
     *
     * @var array
     */
    protected $_units = [
        'CENTISTOKES'                  => ['0.000001',        'cSt'],
        'LENTOR'                       => ['0.0001',          'lentor'],
        'LITER_PER_CENTIMETER_DAY'     => [['' => '1', '/' => '864000'], 'l/cm day'],
        'LITER_PER_CENTIMETER_HOUR'    => [['' => '1', '/' => '36000'],  'l/cm h'],
        'LITER_PER_CENTIMETER_MINUTE'  => [['' => '1', '/' => '600'],    'l/cm m'],
        'LITER_PER_CENTIMETER_SECOND'  => ['0.1',             'l/cm s'],
        'POISE_CUBIC_CENTIMETER_PER_GRAM' => ['0.0001',       'P cm³/g'],
        'SQUARE_CENTIMETER_PER_DAY'    => [['' => '1', '/' => '864000000'],'cm²/day'],
        'SQUARE_CENTIMETER_PER_HOUR'   => [['' => '1', '/' => '36000000'],'cm²/h'],
        'SQUARE_CENTIMETER_PER_MINUTE' => [['' => '1', '/' => '600000'],'cm²/m'],
        'SQUARE_CENTIMETER_PER_SECOND' => ['0.0001',          'cm²/s'],
        'SQUARE_FOOT_PER_DAY'          => ['0.0000010752667', 'ft²/day'],
        'SQUARE_FOOT_PER_HOUR'         => ['0.0000258064',    'ft²/h'],
        'SQUARE_FOOT_PER_MINUTE'       => ['0.001548384048',  'ft²/m'],
        'SQUARE_FOOT_PER_SECOND'       => ['0.09290304',      'ft²/s'],
        'SQUARE_INCH_PER_DAY'          => ['7.4671296e-9',    'in²/day'],
        'SQUARE_INCH_PER_HOUR'         => ['0.00000017921111', 'in²/h'],
        'SQUARE_INCH_PER_MINUTE'       => ['0.000010752667',  'in²/m'],
        'SQUARE_INCH_PER_SECOND'       => ['0.00064516',      'in²/s'],
        'SQUARE_METER_PER_DAY'         => [['' => '1', '/' => '86400'], 'm²/day'],
        'SQUARE_METER_PER_HOUR'        => [['' => '1', '/' => '3600'],  'm²/h'],
        'SQUARE_METER_PER_MINUTE'      => [['' => '1', '/' => '60'],    'm²/m'],
        'SQUARE_METER_PER_SECOND'      => ['1',               'm²/s'],
        'SQUARE_MILLIMETER_PER_DAY'    => [['' => '1', '/' => '86400000000'], 'mm²/day'],
        'SQUARE_MILLIMETER_PER_HOUR'   => [['' => '1', '/' => '3600000000'],  'mm²/h'],
        'SQUARE_MILLIMETER_PER_MINUTE' => [['' => '1', '/' => '60000000'],    'mm²/m'],
        'SQUARE_MILLIMETER_PER_SECOND' => ['0.000001',        'mm²/s'],
        'STOKES'                       => ['0.0001',          'St'],
        'STANDARD'                     => 'SQUARE_METER_PER_SECOND'
    ];
}
