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
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_View_Helper_HtmlObject
 */
require_once 'Zend/View/Helper/HtmlObject.php';

/**
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_HtmlPage extends Zend_View_Helper_HtmlObject
{
    /**
     * Default file type for html
     *
     */
    public const TYPE = 'text/html';

    /**
     * Object classid
     *
     */
    public const ATTRIB_CLASSID  = 'clsid:25336920-03F9-11CF-8FD0-00AA00686F13';

    /**
     * Default attributes
     *
     * @var array
     */
    protected $_attribs = ['classid' => self::ATTRIB_CLASSID];

    /**
     * Output a html object tag
     *
     * @param string $data The html url
     * @param array  $attribs Attribs for the object tag
     * @param array  $params Params for in the object tag
     * @param string $content Alternative content
     * @return string
     */
    public function htmlPage($data, array $attribs = [], array $params = [], $content = null)
    {
        // Attrs
        $attribs = array_merge($this->_attribs, $attribs);

        // Params
        $params = array_merge(['data' => $data], $params);

        return $this->htmlObject($data, self::TYPE, $attribs, $params, $content);
    }
}
