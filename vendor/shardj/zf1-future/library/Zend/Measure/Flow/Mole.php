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
 * Class for handling flow mole conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Flow_Mole
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Flow_Mole extends Zend_Measure_Abstract
{
    public const STANDARD = 'MOLE_PER_SECOND';

    public const CENTIMOLE_PER_DAY    = 'CENTIMOLE_PER_DAY';
    public const CENTIMOLE_PER_HOUR   = 'CENTIMOLE_PER_HOUR';
    public const CENTIMOLE_PER_MINUTE = 'CENTIMOLE_PER_MINUTE';
    public const CENTIMOLE_PER_SECOND = 'CENTIMOLE_PER_SECOND';
    public const MEGAMOLE_PER_DAY     = 'MEGAMOLE_PER_DAY';
    public const MEGAMOLE_PER_HOUR    = 'MEGAMOLE_PER_HOUR';
    public const MEGAMOLE_PER_MINUTE  = 'MEGAMOLE_PER_MINUTE';
    public const MEGAMOLE_PER_SECOND  = 'MEGAMOLE_PER_SECOND';
    public const MICROMOLE_PER_DAY    = 'MICROMOLE_PER_DAY';
    public const MICROMOLE_PER_HOUR   = 'MICROMOLE_PER_HOUR';
    public const MICROMOLE_PER_MINUTE = 'MICROMOLE_PER_MINUTE';
    public const MICROMOLE_PER_SECOND = 'MICROMOLE_PER_SECOND';
    public const MILLIMOLE_PER_DAY    = 'MILLIMOLE_PER_DAY';
    public const MILLIMOLE_PER_HOUR   = 'MILLIMOLE_PER_HOUR';
    public const MILLIMOLE_PER_MINUTE = 'MILLIMOLE_PER_MINUTE';
    public const MILLIMOLE_PER_SECOND = 'MILLIMOLE_PER_SECOND';
    public const MOLE_PER_DAY         = 'MOLE_PER_DAY';
    public const MOLE_PER_HOUR        = 'MOLE_PER_HOUR';
    public const MOLE_PER_MINUTE      = 'MOLE_PER_MINUTE';
    public const MOLE_PER_SECOND      = 'MOLE_PER_SECOND';

    /**
     * Calculations for all flow mole units
     *
     * @var array
     */
    protected $_units = [
        'CENTIMOLE_PER_DAY'    => [['' => '0.01', '/' => '86400'],     'cmol/day'],
        'CENTIMOLE_PER_HOUR'   => [['' => '0.01', '/' => '3600'],      'cmol/h'],
        'CENTIMOLE_PER_MINUTE' => [['' => '0.01', '/' => '60'],        'cmol/m'],
        'CENTIMOLE_PER_SECOND' => ['0.01',     'cmol/s'],
        'MEGAMOLE_PER_DAY'     => [['' => '1000000', '/' => '86400'],  'Mmol/day'],
        'MEGAMOLE_PER_HOUR'    => [['' => '1000000', '/' => '3600'],   'Mmol/h'],
        'MEGAMOLE_PER_MINUTE'  => [['' => '1000000', '/' => '60'],     'Mmol/m'],
        'MEGAMOLE_PER_SECOND'  => ['1000000',  'Mmol/s'],
        'MICROMOLE_PER_DAY'    => [['' => '0.000001', '/' => '86400'], 'µmol/day'],
        'MICROMOLE_PER_HOUR'   => [['' => '0.000001', '/' => '3600'],  'µmol/h'],
        'MICROMOLE_PER_MINUTE' => [['' => '0.000001', '/' => '60'],    'µmol/m'],
        'MICROMOLE_PER_SECOND' => ['0.000001', 'µmol/s'],
        'MILLIMOLE_PER_DAY'    => [['' => '0.001', '/' => '86400'],    'mmol/day'],
        'MILLIMOLE_PER_HOUR'   => [['' => '0.001', '/' => '3600'],     'mmol/h'],
        'MILLIMOLE_PER_MINUTE' => [['' => '0.001', '/' => '60'],       'mmol/m'],
        'MILLIMOLE_PER_SECOND' => ['0.001',    'mmol/s'],
        'MOLE_PER_DAY'         => [['' => '1', '/' => '86400'],        'mol/day'],
        'MOLE_PER_HOUR'        => [['' => '1', '/' => '3600'],         'mol/h'],
        'MOLE_PER_MINUTE'      => [['' => '1', '/' => '60'],           'mol/m'],
        'MOLE_PER_SECOND'      => ['1',        'mol/s'],
        'STANDARD'             => 'MOLE_PER_SECOND'
    ];
}
