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
 * Class for handling binary conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Binary
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Binary extends Zend_Measure_Abstract
{
    public const STANDARD = 'BYTE';

    public const BIT               = 'BIT';
    public const CRUMB             = 'CRUMB';
    public const NIBBLE            = 'NIBBLE';
    public const BYTE              = 'BYTE';
    public const KILOBYTE          = 'KILOBYTE';
    public const KIBIBYTE          = 'KIBIBYTE';
    public const KILO_BINARY_BYTE  = 'KILO_BINARY_BYTE';
    public const KILOBYTE_SI       = 'KILOBYTE_SI';
    public const MEGABYTE          = 'MEGABYTE';
    public const MEBIBYTE          = 'MEBIBYTE';
    public const MEGA_BINARY_BYTE  = 'MEGA_BINARY_BYTE';
    public const MEGABYTE_SI       = 'MEGABYTE_SI';
    public const GIGABYTE          = 'GIGABYTE';
    public const GIBIBYTE          = 'GIBIBYTE';
    public const GIGA_BINARY_BYTE  = 'GIGA_BINARY_BYTE';
    public const GIGABYTE_SI       = 'GIGABYTE_SI';
    public const TERABYTE          = 'TERABYTE';
    public const TEBIBYTE          = 'TEBIBYTE';
    public const TERA_BINARY_BYTE  = 'TERA_BINARY_BYTE';
    public const TERABYTE_SI       = 'TERABYTE_SI';
    public const PETABYTE          = 'PETABYTE';
    public const PEBIBYTE          = 'PEBIBYTE';
    public const PETA_BINARY_BYTE  = 'PETA_BINARY_BYTE';
    public const PETABYTE_SI       = 'PETABYTE_SI';
    public const EXABYTE           = 'EXABYTE';
    public const EXBIBYTE          = 'EXBIBYTE';
    public const EXA_BINARY_BYTE   = 'EXA_BINARY_BYTE';
    public const EXABYTE_SI        = 'EXABYTE_SI';
    public const ZETTABYTE         = 'ZETTABYTE';
    public const ZEBIBYTE          = 'ZEBIBYTE';
    public const ZETTA_BINARY_BYTE = 'ZETTA_BINARY_BYTE';
    public const ZETTABYTE_SI      = 'ZETTABYTE_SI';
    public const YOTTABYTE         = 'YOTTABYTE';
    public const YOBIBYTE          = 'YOBIBYTE';
    public const YOTTA_BINARY_BYTE = 'YOTTA_BINARY_BYTE';
    public const YOTTABYTE_SI      = 'YOTTABYTE_SI';

    /**
     * Calculations for all binary units
     *
     * @var array
     */
    protected $_units = [
        'BIT'              => ['0.125',                     'b'],
        'CRUMB'            => ['0.25',                      'crumb'],
        'NIBBLE'           => ['0.5',                       'nibble'],
        'BYTE'             => ['1',                         'B'],
        'KILOBYTE'         => ['1024',                      'kB'],
        'KIBIBYTE'         => ['1024',                      'KiB'],
        'KILO_BINARY_BYTE' => ['1024',                      'KiB'],
        'KILOBYTE_SI'      => ['1000',                      'kB.'],
        'MEGABYTE'         => ['1048576',                   'MB'],
        'MEBIBYTE'         => ['1048576',                   'MiB'],
        'MEGA_BINARY_BYTE' => ['1048576',                   'MiB'],
        'MEGABYTE_SI'      => ['1000000',                   'MB.'],
        'GIGABYTE'         => ['1073741824',                'GB'],
        'GIBIBYTE'         => ['1073741824',                'GiB'],
        'GIGA_BINARY_BYTE' => ['1073741824',                'GiB'],
        'GIGABYTE_SI'      => ['1000000000',                'GB.'],
        'TERABYTE'         => ['1099511627776',             'TB'],
        'TEBIBYTE'         => ['1099511627776',             'TiB'],
        'TERA_BINARY_BYTE' => ['1099511627776',             'TiB'],
        'TERABYTE_SI'      => ['1000000000000',             'TB.'],
        'PETABYTE'         => ['1125899906842624',          'PB'],
        'PEBIBYTE'         => ['1125899906842624',          'PiB'],
        'PETA_BINARY_BYTE' => ['1125899906842624',          'PiB'],
        'PETABYTE_SI'      => ['1000000000000000',          'PB.'],
        'EXABYTE'          => ['1152921504606846976',       'EB'],
        'EXBIBYTE'         => ['1152921504606846976',       'EiB'],
        'EXA_BINARY_BYTE'  => ['1152921504606846976',       'EiB'],
        'EXABYTE_SI'       => ['1000000000000000000',       'EB.'],
        'ZETTABYTE'        => ['1180591620717411303424',    'ZB'],
        'ZEBIBYTE'         => ['1180591620717411303424',    'ZiB'],
        'ZETTA_BINARY_BYTE'=> ['1180591620717411303424',    'ZiB'],
        'ZETTABYTE_SI'     => ['1000000000000000000000',    'ZB.'],
        'YOTTABYTE'        => ['1208925819614629174706176', 'YB'],
        'YOBIBYTE'         => ['1208925819614629174706176', 'YiB'],
        'YOTTA_BINARY_BYTE'=> ['1208925819614629174706176', 'YiB'],
        'YOTTABYTE_SI'     => ['1000000000000000000000000', 'YB.'],
        'STANDARD'         => 'BYTE'
    ];
}
