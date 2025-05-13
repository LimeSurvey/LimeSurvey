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
 * @category   Zend
 * @package    Zend_Validate
 * @subpackage Sitemap
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * Validates whether a given value is valid as a sitemap <changefreq> value
 *
 * @link       http://www.sitemaps.org/protocol.php Sitemaps XML format
 *
 * @category   Zend
 * @package    Zend_Validate
 * @subpackage Sitemap
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Sitemap_Changefreq extends Zend_Validate_Abstract
{
    /**
     * Validation key for not valid
     *
     */
    public const NOT_VALID = 'sitemapChangefreqNotValid';
    public const INVALID   = 'sitemapChangefreqInvalid';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::NOT_VALID => "'%value%' is not a valid sitemap changefreq",
        self::INVALID   => "Invalid type given. String expected",
    ];

    /**
     * Valid change frequencies
     *
     * @var array
     */
    protected $_changeFreqs = [
        'always',  'hourly', 'daily', 'weekly',
        'monthly', 'yearly', 'never'
    ];

    /**
     * Validates if a string is valid as a sitemap changefreq
     *
     * @link http://www.sitemaps.org/protocol.php#changefreqdef <changefreq>
     *
     * @param  string  $value  value to validate
     * @return boolean
     */
    public function isValid($value)
    {
        if (!is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $this->_setValue($value);
        if (!is_string($value)) {
            return false;
        }

        if (!in_array($value, $this->_changeFreqs, true)) {
            $this->_error(self::NOT_VALID);
            return false;
        }

        return true;
    }
}
