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
 * Class for handling time conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Time
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Time extends Zend_Measure_Abstract
{
    public const STANDARD = 'SECOND';

    public const ANOMALISTIC_YEAR  = 'ANOMALISTIC_YEAR';
    public const ATTOSECOND        = 'ATTOSECOND';
    public const CENTURY           = 'CENTURY';
    public const DAY               = 'DAY';
    public const DECADE            = 'DECADE';
    public const DRACONIC_YEAR     = 'DRACONTIC_YEAR';
    public const EXASECOND         = 'EXASECOND';
    public const FEMTOSECOND       = 'FEMTOSECOND';
    public const FORTNIGHT         = 'FORTNIGHT';
    public const GAUSSIAN_YEAR     = 'GAUSSIAN_YEAR';
    public const GIGASECOND        = 'GIGASECOND';
    public const GREGORIAN_YEAR    = 'GREGORIAN_YEAR';
    public const HOUR              = 'HOUR';
    public const JULIAN_YEAR       = 'JULIAN_YEAR';
    public const KILOSECOND        = 'KILOSECOND';
    public const LEAPYEAR          = 'LEAPYEAR';
    public const MEGASECOND        = 'MEGASECOND';
    public const MICROSECOND       = 'MICROSECOND';
    public const MILLENIUM         = 'MILLENIUM';
    public const MILLISECOND       = 'MILLISECOND';
    public const MINUTE            = 'MINUTE';
    public const MONTH             = 'MONTH';
    public const NANOSECOND        = 'NANOSECOND';
    public const PETASECOND        = 'PETASECOND';
    public const PICOSECOND        = 'PICOSECOND';
    public const QUARTER           = 'QUARTER';
    public const SECOND            = 'SECOND';
    public const SHAKE             = 'SHAKE';
    public const SIDEREAL_YEAR     = 'SYNODIC_MONTH';
    public const TERASECOND        = 'TERASECOND';
    public const TROPICAL_YEAR     = 'TROPIC_YEAR';
    public const WEEK              = 'WEEK';
    public const YEAR              = 'YEAR';

    /**
     * Calculations for all time units
     *
     * @var array
     */
    protected $_units = [
        'ANOMALISTIC_YEAR'  => ['31558432', 'anomalistic year'],
        'ATTOSECOND'        => ['1.0e-18', 'as'],
        'CENTURY'           => ['3153600000', 'century'],
        'DAY'               => ['86400', 'day'],
        'DECADE'            => ['315360000', 'decade'],
        'DRACONIC_YEAR'     => ['29947974', 'draconic year'],
        'EXASECOND'         => ['1.0e+18', 'Es'],
        'FEMTOSECOND'       => ['1.0e-15', 'fs'],
        'FORTNIGHT'         => ['1209600', 'fortnight'],
        'GAUSSIAN_YEAR'     => ['31558196', 'gaussian year'],
        'GIGASECOND'        => ['1.0e+9', 'Gs'],
        'GREAT_YEAR'        => [['*' => '25700'], 'great year'],
        'GREGORIAN_YEAR'    => ['31536000', 'year'],
        'HOUR'              => ['3600', 'h'],
        'JULIAN_YEAR'       => ['31557600', 'a'],
        'KILOSECOND'        => ['1000', 'ks'],
        'LEAPYEAR'          => ['31622400', 'year'],
        'MEGASECOND'        => ['1000000', 'Ms'],
        'MICROSECOND'       => ['0.000001', 'µs'],
        'MILLENIUM'         => ['31536000000', 'millenium'],
        'MILLISECOND'       => ['0.001', 'ms'],
        'MINUTE'            => ['60', 'min'],
        'MONTH'             => ['2628600', 'month'],
        'NANOSECOND'        => ['1.0e-9', 'ns'],
        'PETASECOND'        => ['1.0e+15', 'Ps'],
        'PICOSECOND'        => ['1.0e-12', 'ps'],
        'QUARTER'           => ['7884000', 'quarter'],
        'SECOND'            => ['1', 's'],
        'SHAKE'             => ['1.0e-9', 'shake'],
        'SIDEREAL_YEAR'     => ['31558149.7676', 'sidereal year'],
        'TERASECOND'        => ['1.0e+12', 'Ts'],
        'TROPICAL_YEAR'     => ['31556925', 'tropical year'],
        'WEEK'              => ['604800', 'week'],
        'YEAR'              => ['31536000', 'year'],
        'STANDARD'          => 'SECOND'
    ];
}
