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
 * @package    Zend_Version
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Class to store and retrieve the version of Zend Framework.
 *
 * @category   Zend
 * @package    Zend_Version
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
final class Zend_Version
{
    /**
     * Zend Framework version identification - see compareVersion()
     */
    public const VERSION = '1.24.2';

    /**
     * The latest stable version Zend Framework available
     *
     * @var string
     */
    protected static $_latestVersion;

    /**
     * Compare the specified Zend Framework version string $version
     * with the current Zend_Version::VERSION of Zend Framework.
     *
     * @param  string  $version  A version string (e.g. "0.7.1").
     * @return int           -1 if the $version is older,
     *                           0 if they are the same,
     *                           and +1 if $version is newer.
     *
     */
    public static function compareVersion($version)
    {
        $version = strtolower($version);
        $version = preg_replace('/(\d)pr(\d?)/', '$1a$2', $version);
        return version_compare($version, strtolower(self::VERSION));
    }

    /**
     * Fetches the version of the latest stable release
     *
     * @link http://framework.zend.com/download/latest
     * @return string
     */
    public static function getLatest()
    {
        if (null === self::$_latestVersion) {
            self::$_latestVersion = 'not available';

            $opts = [
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'User-Agent: PHP'
                    ]
                ]
            ];
            $context = stream_context_create($opts);
            $content = file_get_contents('https://api.github.com/repos/Shardj/zf1-future/releases/latest', false, $context);

            if (false !== $content) {
                $releaseName = explode('-', json_decode($content, true)['name']);

                self::$_latestVersion = array_pop($releaseName);
            }
        }

        return self::$_latestVersion;
    }
}
