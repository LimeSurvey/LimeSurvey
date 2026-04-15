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
 * Class for handling temperature conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Lightness
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Lightness extends Zend_Measure_Abstract
{
    public const STANDARD = 'CANDELA_PER_SQUARE_METER';

    public const APOSTILB                          = 'APOSTILB';
    public const BLONDEL                           = 'BLONDEL';
    public const CANDELA_PER_SQUARE_CENTIMETER     = 'CANDELA_PER_SQUARE_CENTIMETER';
    public const CANDELA_PER_SQUARE_FOOT           = 'CANDELA_PER_SQUARE_FOOT';
    public const CANDELA_PER_SQUARE_INCH           = 'CANDELA_PER_SQUARE_INCH';
    public const CANDELA_PER_SQUARE_METER          = 'CANDELA_PER_SQUARE_METER';
    public const FOOTLAMBERT                       = 'FOOTLAMBERT';
    public const KILOCANDELA_PER_SQUARE_CENTIMETER = 'KILOCANDELA_PER_SQUARE_CENTIMETER';
    public const KILOCANDELA_PER_SQUARE_FOOT       = 'KILOCANDELA_PER_SQUARE_FOOT';
    public const KILOCANDELA_PER_SQUARE_INCH       = 'KILOCANDELA_PER_SQUARE_INCH';
    public const KILOCANDELA_PER_SQUARE_METER      = 'KILOCANDELA_PER_SQUARE_METER';
    public const LAMBERT                           = 'LAMBERT';
    public const MILLILAMBERT                      = 'MILLILAMBERT';
    public const NIT                               = 'NIT';
    public const STILB                             = 'STILB';

    /**
     * Calculations for all lightness units
     *
     * @var array
     */
    protected $_units = [
        'APOSTILB'                      => ['0.31830989',   'asb'],
        'BLONDEL'                       => ['0.31830989',   'blondel'],
        'CANDELA_PER_SQUARE_CENTIMETER' => ['10000',        'cd/cm²'],
        'CANDELA_PER_SQUARE_FOOT'       => ['10.76391',     'cd/ft²'],
        'CANDELA_PER_SQUARE_INCH'       => ['1550.00304',   'cd/in²'],
        'CANDELA_PER_SQUARE_METER'      => ['1',            'cd/m²'],
        'FOOTLAMBERT'                   => ['3.4262591',    'ftL'],
        'KILOCANDELA_PER_SQUARE_CENTIMETER' => ['10000000', 'kcd/cm²'],
        'KILOCANDELA_PER_SQUARE_FOOT'   => ['10763.91',     'kcd/ft²'],
        'KILOCANDELA_PER_SQUARE_INCH'   => ['1550003.04',   'kcd/in²'],
        'KILOCANDELA_PER_SQUARE_METER'  => ['1000',         'kcd/m²'],
        'LAMBERT'                       => ['3183.0989',    'L'],
        'MILLILAMBERT'                  => ['3.1830989',    'mL'],
        'NIT'                           => ['1',            'nt'],
        'STILB'                         => ['10000',        'sb'],
        'STANDARD'                      => 'CANDELA_PER_SQUARE_METER'
    ];
}
