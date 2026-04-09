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
 * Class for handling flow volume conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Frequency
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Frequency extends Zend_Measure_Abstract
{
    public const STANDARD = 'HERTZ';

    public const ONE_PER_SECOND        = 'ONE_PER_SECOND';
    public const CYCLE_PER_SECOND      = 'CYCLE_PER_SECOND';
    public const DEGREE_PER_HOUR       = 'DEGREE_PER_HOUR';
    public const DEGREE_PER_MINUTE     = 'DEGREE_PER_MINUTE';
    public const DEGREE_PER_SECOND     = 'DEGREE_PER_SECOND';
    public const GIGAHERTZ             = 'GIGAHERTZ';
    public const HERTZ                 = 'HERTZ';
    public const KILOHERTZ             = 'KILOHERTZ';
    public const MEGAHERTZ             = 'MEGAHERTZ';
    public const MILLIHERTZ            = 'MILLIHERTZ';
    public const RADIAN_PER_HOUR       = 'RADIAN_PER_HOUR';
    public const RADIAN_PER_MINUTE     = 'RADIAN_PER_MINUTE';
    public const RADIAN_PER_SECOND     = 'RADIAN_PER_SECOND';
    public const REVOLUTION_PER_HOUR   = 'REVOLUTION_PER_HOUR';
    public const REVOLUTION_PER_MINUTE = 'REVOLUTION_PER_MINUTE';
    public const REVOLUTION_PER_SECOND = 'REVOLUTION_PER_SECOND';
    public const RPM                   = 'RPM';
    public const TERRAHERTZ            = 'TERRAHERTZ';

    /**
     * Calculations for all frequency units
     *
     * @var array
     */
    protected $_units = [
        'ONE_PER_SECOND'        => ['1',             '1/s'],
        'CYCLE_PER_SECOND'      => ['1',             'cps'],
        'DEGREE_PER_HOUR'       => [['' => '1', '/' => '1296000'], '°/h'],
        'DEGREE_PER_MINUTE'     => [['' => '1', '/' => '21600'],   '°/m'],
        'DEGREE_PER_SECOND'     => [['' => '1', '/' => '360'],     '°/s'],
        'GIGAHERTZ'             => ['1000000000',    'GHz'],
        'HERTZ'                 => ['1',             'Hz'],
        'KILOHERTZ'             => ['1000',          'kHz'],
        'MEGAHERTZ'             => ['1000000',       'MHz'],
        'MILLIHERTZ'            => ['0.001',         'mHz'],
        'RADIAN_PER_HOUR'       => [['' => '1', '/' => '22619.467'], 'rad/h'],
        'RADIAN_PER_MINUTE'     => [['' => '1', '/' => '376.99112'], 'rad/m'],
        'RADIAN_PER_SECOND'     => [['' => '1', '/' => '6.2831853'], 'rad/s'],
        'REVOLUTION_PER_HOUR'   => [['' => '1', '/' => '3600'], 'rph'],
        'REVOLUTION_PER_MINUTE' => [['' => '1', '/' => '60'],   'rpm'],
        'REVOLUTION_PER_SECOND' => ['1',             'rps'],
        'RPM'                   => [['' => '1', '/' => '60'], 'rpm'],
        'TERRAHERTZ'            => ['1000000000000', 'THz'],
        'STANDARD'              =>'HERTZ'
    ];
}
