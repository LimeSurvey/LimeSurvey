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
 * Abstract class for extension
 */
require_once 'Zend/View/Helper/FormElement.php';


/**
 * Helper to generate a "button" element
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_FormButton extends Zend_View_Helper_FormElement
{
    /**
     * Generates a 'button' element.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     *
     * @param mixed $value The element value.
     *
     * @param array $attribs Attributes for the element tag.
     *
     * @return string The element XHTML.
     */
    public function formButton($name, $value = null, $attribs = null)
    {
        $info    = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, id, value, attribs, options, listsep, disable, escape

        // Get content
        $content = '';
        if (isset($attribs['content'])) {
            $content = $attribs['content'];
            unset($attribs['content']);
        } else {
            $content = $value;
        }

        // Ensure type is sane
        $type = 'button';
        if (isset($attribs['type'])) {
            $attribs['type'] = strtolower($attribs['type']);
            if (in_array($attribs['type'], ['submit', 'reset', 'button'])) {
                $type = $attribs['type'];
            }
            unset($attribs['type']);
        }

        // build the element
        if ($disable) {
            $attribs['disabled'] = 'disabled';
        }

        $content = ($escape) ? $this->view->escape($content) : $content;

        $xhtml = '<button'
                . ' name="' . $this->view->escape($name) . '"'
                . ' id="' . $this->view->escape($id) . '"'
                . ' type="' . $type . '"';

        // add a value if one is given
        if (!empty($value)) {
            $xhtml .= ' value="' . $this->view->escape($value) . '"';
        }

        // add attributes and close start tag
        $xhtml .= $this->_htmlAttribs($attribs) . '>';

        // add content and end tag
        $xhtml .= $content . '</button>';

        return $xhtml;
    }
}
