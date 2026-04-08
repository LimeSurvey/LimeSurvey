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
 * Class for handling torque conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Torque
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Torque extends Zend_Measure_Abstract
{
    public const STANDARD = 'NEWTON_METER';

    public const DYNE_CENTIMETER     = 'DYNE_CENTIMETER';
    public const GRAM_CENTIMETER     = 'GRAM_CENTIMETER';
    public const KILOGRAM_CENTIMETER = 'KILOGRAM_CENTIMETER';
    public const KILOGRAM_METER      = 'KILOGRAM_METER';
    public const KILONEWTON_METER    = 'KILONEWTON_METER';
    public const KILOPOND_METER      = 'KILOPOND_METER';
    public const MEGANEWTON_METER    = 'MEGANEWTON_METER';
    public const MICRONEWTON_METER   = 'MICRONEWTON_METER';
    public const MILLINEWTON_METER   = 'MILLINEWTON_METER';
    public const NEWTON_CENTIMETER   = 'NEWTON_CENTIMETER';
    public const NEWTON_METER        = 'NEWTON_METER';
    public const OUNCE_FOOT          = 'OUNCE_FOOT';
    public const OUNCE_INCH          = 'OUNCE_INCH';
    public const POUND_FOOT          = 'POUND_FOOT';
    public const POUNDAL_FOOT        = 'POUNDAL_FOOT';
    public const POUND_INCH          = 'POUND_INCH';

    /**
     * Calculations for all torque units
     *
     * @var array
     */
    protected $_units = [
        'DYNE_CENTIMETER'     => ['0.0000001',          'dyncm'],
        'GRAM_CENTIMETER'     => ['0.0000980665',       'gcm'],
        'KILOGRAM_CENTIMETER' => ['0.0980665',          'kgcm'],
        'KILOGRAM_METER'      => ['9.80665',            'kgm'],
        'KILONEWTON_METER'    => ['1000',               'kNm'],
        'KILOPOND_METER'      => ['9.80665',            'kpm'],
        'MEGANEWTON_METER'    => ['1000000',            'MNm'],
        'MICRONEWTON_METER'   => ['0.000001',           'µNm'],
        'MILLINEWTON_METER'   => ['0.001',              'mNm'],
        'NEWTON_CENTIMETER'   => ['0.01',               'Ncm'],
        'NEWTON_METER'        => ['1',                  'Nm'],
        'OUNCE_FOOT'          => ['0.084738622',        'ozft'],
        'OUNCE_INCH'          => [['' => '0.084738622', '/' => '12'], 'ozin'],
        'POUND_FOOT'          => [['' => '0.084738622', '*' => '16'], 'lbft'],
        'POUNDAL_FOOT'        => ['0.0421401099752144', 'plft'],
        'POUND_INCH'          => [['' => '0.084738622', '/' => '12', '*' => '16'], 'lbin'],
        'STANDARD'            => 'NEWTON_METER'
    ];
}
