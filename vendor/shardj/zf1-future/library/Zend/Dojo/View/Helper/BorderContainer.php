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
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Dojo_View_Helper_DijitContainer */
require_once 'Zend/Dojo/View/Helper/DijitContainer.php';

/**
 * Dojo BorderContainer dijit
 *
 * @uses       Zend_Dojo_View_Helper_DijitContainer
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
  */
class Zend_Dojo_View_Helper_BorderContainer extends Zend_Dojo_View_Helper_DijitContainer
{
    /**
     * Dijit being used
     * @var string
     */
    protected $_dijit  = 'dijit.layout.BorderContainer';

    /**
     * Dojo module to use
     * @var string
     */
    protected $_module = 'dijit.layout.BorderContainer';

    /**
     * Ensure style is only registered once
     * @var bool
     */
    protected $_styleIsRegistered = false;

    /**
     * dijit.layout.BorderContainer
     *
     * @param  string $id
     * @param  string $content
     * @param  array $params  Parameters to use for dijit creation
     * @param  array $attribs HTML attributes
     * @return string
     */
    public function borderContainer($id = null, $content = '', array $params = [], array $attribs = [])
    {
        if (0 === func_num_args()) {
            return $this;
        }

        // this will ensure that the border container is viewable:
        if (!$this->_styleIsRegistered) {
            $this->view->headStyle()->appendStyle('html, body { height: 100%; width: 100%; margin: 0; padding: 0; }');
            $this->_styleIsRegistered = true;
        }

        // and now we create it:
        return $this->_createLayoutContainer($id, $content, $params, $attribs);
    }
}
