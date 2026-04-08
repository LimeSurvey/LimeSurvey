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
 * Class for handling force conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Force
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Force extends Zend_Measure_Abstract
{
    public const STANDARD = 'NEWTON';

    public const ATTONEWTON      = 'ATTONEWTON';
    public const CENTINEWTON     = 'CENTINEWTON';
    public const DECIGRAM_FORCE  = 'DECIGRAM_FORCE';
    public const DECINEWTON      = 'DECINEWTON';
    public const DEKAGRAM_FORCE  = 'DEKAGRAM_FORCE';
    public const DEKANEWTON      = 'DEKANEWTON';
    public const DYNE            = 'DYNE';
    public const EXANEWTON       = 'EXANEWTON';
    public const FEMTONEWTON     = 'FEMTONEWTON';
    public const GIGANEWTON      = 'GIGANEWTON';
    public const GRAM_FORCE      = 'GRAM_FORCE';
    public const HECTONEWTON     = 'HECTONEWTON';
    public const JOULE_PER_METER = 'JOULE_PER_METER';
    public const KILOGRAM_FORCE  = 'KILOGRAM_FORCE';
    public const KILONEWTON      = 'KILONEWTON';
    public const KILOPOND        = 'KILOPOND';
    public const KIP             = 'KIP';
    public const MEGANEWTON      = 'MEGANEWTON';
    public const MEGAPOND        = 'MEGAPOND';
    public const MICRONEWTON     = 'MICRONEWTON';
    public const MILLINEWTON     = 'MILLINEWTON';
    public const NANONEWTON      = 'NANONEWTON';
    public const NEWTON          = 'NEWTON';
    public const OUNCE_FORCE     = 'OUNCE_FORCE';
    public const PETANEWTON      = 'PETANEWTON';
    public const PICONEWTON      = 'PICONEWTON';
    public const POND            = 'POND';
    public const POUND_FORCE     = 'POUND_FORCE';
    public const POUNDAL         = 'POUNDAL';
    public const STHENE          = 'STHENE';
    public const TERANEWTON      = 'TERANEWTON';
    public const TON_FORCE_LONG  = 'TON_FORCE_LONG';
    public const TON_FORCE       = 'TON_FORCE';
    public const TON_FORCE_SHORT = 'TON_FORCE_SHORT';
    public const YOCTONEWTON     = 'YOCTONEWTON';
    public const YOTTANEWTON     = 'YOTTANEWTON';
    public const ZEPTONEWTON     = 'ZEPTONEWTON';
    public const ZETTANEWTON     = 'ZETTANEWTON';

    /**
     * Calculations for all force units
     *
     * @var array
     */
    protected $_units = [
        'ATTONEWTON'      => ['1.0e-18',     'aN'],
        'CENTINEWTON'     => ['0.01',        'cN'],
        'DECIGRAM_FORCE'  => ['0.000980665', 'dgf'],
        'DECINEWTON'      => ['0.1',         'dN'],
        'DEKAGRAM_FORCE'  => ['0.0980665',   'dagf'],
        'DEKANEWTON'      => ['10',          'daN'],
        'DYNE'            => ['0.00001',     'dyn'],
        'EXANEWTON'       => ['1.0e+18',     'EN'],
        'FEMTONEWTON'     => ['1.0e-15',     'fN'],
        'GIGANEWTON'      => ['1.0e+9',      'GN'],
        'GRAM_FORCE'      => ['0.00980665',  'gf'],
        'HECTONEWTON'     => ['100',         'hN'],
        'JOULE_PER_METER' => ['1',           'J/m'],
        'KILOGRAM_FORCE'  => ['9.80665',     'kgf'],
        'KILONEWTON'      => ['1000',        'kN'],
        'KILOPOND'        => ['9.80665',     'kp'],
        'KIP'             => ['4448.2216',   'kip'],
        'MEGANEWTON'      => ['1000000',     'Mp'],
        'MEGAPOND'        => ['9806.65',     'MN'],
        'MICRONEWTON'     => ['0.000001',    'µN'],
        'MILLINEWTON'     => ['0.001',       'mN'],
        'NANONEWTON'      => ['0.000000001', 'nN'],
        'NEWTON'          => ['1',           'N'],
        'OUNCE_FORCE'     => ['0.27801385',  'ozf'],
        'PETANEWTON'      => ['1.0e+15',     'PN'],
        'PICONEWTON'      => ['1.0e-12',     'pN'],
        'POND'            => ['0.00980665',  'pond'],
        'POUND_FORCE'     => ['4.4482216',   'lbf'],
        'POUNDAL'         => ['0.13825495',  'pdl'],
        'STHENE'          => ['1000',        'sn'],
        'TERANEWTON'      => ['1.0e+12',     'TN'],
        'TON_FORCE_LONG'  => ['9964.016384', 'tnf'],
        'TON_FORCE'       => ['9806.65',     'tnf'],
        'TON_FORCE_SHORT' => ['8896.4432',   'tnf'],
        'YOCTONEWTON'     => ['1.0e-24',     'yN'],
        'YOTTANEWTON'     => ['1.0e+24',     'YN'],
        'ZEPTONEWTON'     => ['1.0e-21',     'zN'],
        'ZETTANEWTON'     => ['1.0e+21',     'ZN'],
        'STANDARD'        => 'NEWTON'
    ];
}
