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
 * Class for handling current conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Current
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Current extends Zend_Measure_Abstract
{
    public const STANDARD = 'AMPERE';

    public const ABAMPERE             = 'ABAMPERE';
    public const AMPERE               = 'AMPERE';
    public const BIOT                 = 'BIOT';
    public const CENTIAMPERE          = 'CENTIAMPERE';
    public const COULOMB_PER_SECOND   = 'COULOMB_PER_SECOND';
    public const DECIAMPERE           = 'DECIAMPERE';
    public const DEKAAMPERE           = 'DEKAAMPERE';
    public const ELECTROMAGNETIC_UNIT = 'ELECTROMAGNATIC_UNIT';
    public const ELECTROSTATIC_UNIT   = 'ELECTROSTATIC_UNIT';
    public const FRANCLIN_PER_SECOND  = 'FRANCLIN_PER_SECOND';
    public const GAUSSIAN             = 'GAUSSIAN';
    public const GIGAAMPERE           = 'GIGAAMPERE';
    public const GILBERT              = 'GILBERT';
    public const HECTOAMPERE          = 'HECTOAMPERE';
    public const KILOAMPERE           = 'KILOAMPERE';
    public const MEGAAMPERE           = 'MEGAAMPERE';
    public const MICROAMPERE          = 'MICROAMPERE';
    public const MILLIAMPERE          = 'MILLIAMPERE';
    public const NANOAMPERE           = 'NANOAMPERE';
    public const PICOAMPERE           = 'PICOAMPERE';
    public const SIEMENS_VOLT         = 'SIEMENS_VOLT';
    public const STATAMPERE           = 'STATAMPERE';
    public const TERAAMPERE           = 'TERAAMPERE';
    public const VOLT_PER_OHM         = 'VOLT_PER_OHM';
    public const WATT_PER_VOLT        = 'WATT_PER_VOLT';
    public const WEBER_PER_HENRY      = 'WEBER_PER_HENRY';

    /**
     * Calculations for all current units
     *
     * @var array
     */
    protected $_units = [
        'ABAMPERE'             => ['10',           'abampere'],
        'AMPERE'               => ['1',            'A'],
        'BIOT'                 => ['10',           'Bi'],
        'CENTIAMPERE'          => ['0.01',         'cA'],
        'COULOMB_PER_SECOND'   => ['1',            'C/s'],
        'DECIAMPERE'           => ['0.1',          'dA'],
        'DEKAAMPERE'           => ['10',           'daA'],
        'ELECTROMAGNATIC_UNIT' => ['10',           'current emu'],
        'ELECTROSTATIC_UNIT'   => ['3.335641e-10', 'current esu'],
        'FRANCLIN_PER_SECOND'  => ['3.335641e-10', 'Fr/s'],
        'GAUSSIAN'             => ['3.335641e-10', 'G current'],
        'GIGAAMPERE'           => ['1.0e+9',       'GA'],
        'GILBERT'              => ['0.79577472',   'Gi'],
        'HECTOAMPERE'          => ['100',          'hA'],
        'KILOAMPERE'           => ['1000',         'kA'],
        'MEGAAMPERE'           => ['1000000',      'MA'] ,
        'MICROAMPERE'          => ['0.000001',     'µA'],
        'MILLIAMPERE'          => ['0.001',        'mA'],
        'NANOAMPERE'           => ['1.0e-9',       'nA'],
        'PICOAMPERE'           => ['1.0e-12',      'pA'],
        'SIEMENS_VOLT'         => ['1',            'SV'],
        'STATAMPERE'           => ['3.335641e-10', 'statampere'],
        'TERAAMPERE'           => ['1.0e+12',      'TA'],
        'VOLT_PER_OHM'         => ['1',            'V/Ohm'],
        'WATT_PER_VOLT'        => ['1',            'W/V'],
        'WEBER_PER_HENRY'      => ['1',            'Wb/H'],
        'STANDARD'             => 'AMPERE'
    ];
}
