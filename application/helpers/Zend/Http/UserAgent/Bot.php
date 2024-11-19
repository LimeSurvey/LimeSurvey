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
 * @package    Zend_Http
 * @subpackage UserAgent
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Http/UserAgent/AbstractDevice.php';

/**
 * Bot browser type matcher
 *
 * @category   Zend
 * @package    Zend_Http
 * @subpackage UserAgent
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class Zend_Http_UserAgent_Bot extends Zend_Http_UserAgent_AbstractDevice
{

    /**
     * User Agent Signatures
     *
     * @var array
     */
    protected static $_uaSignatures = array(
        // The most common ones.
        'googlebot',
        'msnbot',
        'slurp',
        'yahoo',

        // The rest, alphabetically.
        'alexa',
        'appie',
        'archiver',
        'ask jeeves',
        'baiduspider',
        'bot',
        'crawl',
        'crawler',
        'curl',
        'eventbox',
        'facebookexternal',
        'fast',
        'feedfetcher-google',
        'firefly',
        'froogle',
        'gigabot',
        'girafabot',
        'google',
        'htdig',
        'infoseek',
        'inktomi',
        'java',
        'larbin',
        'looksmart',
        'mechanize',
        'mediapartners-google',
        'monitor',
        'nambu',
        'nationaldirectory',
        'novarra',
        'pear',
        'perl',
        'python',
        'rabaz',
        'radian',
        'rankivabot',
        'scooter',
        'sogou web spider',
        'spade',
        'sphere',
        'spider',
        'technoratisnoop',
        'tecnoseek',
        'teoma',
        'toolbar',
        'transcoder',
        'twitt',
        'url_spider_sql',
        'webalta crawler',
        'webbug',
        'webfindbot',
        'wordpress',
        'www.galaxy.com',
        'yahoo! searchmonkey',
        'yahoo! slurp',
        'yandex',
        'zyborg',
    );

    /**
     * Comparison of the UserAgent chain and browser signatures
     *
     * @param  string $userAgent User Agent chain
     * @param  array $server $_SERVER like param
     * @return bool
     */
    public static function match($userAgent, $server)
    {
        return self::_matchAgentAgainstSignatures($userAgent, self::$_uaSignatures);
    }

    /**
     * Gives the current browser type
     *
     * @return string
     */
    public function getType()
    {
        return 'bot';
    }
}
