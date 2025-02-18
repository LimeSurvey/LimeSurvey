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
 * @package    Zend_Form
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Form_Decorator_Abstract */
require_once 'Zend/Form/Decorator/Abstract.php';

/**
 * Zend_Form_Decorator_ViewScript
 *
 * Render a view script as a decorator
 *
 * Accepts the options:
 * - separator: separator to use between view script content and provided content (defaults to PHP_EOL)
 * - placement: whether to append or prepend view script content to provided content (defaults to prepend)
 * - viewScript: view script to use
 * - viewModule: module that view script is in (optional)
 *
 * The view script is rendered as a partial; the element being decorated is
 * passed in as the 'element' variable:
 * <code>
 * // in view script:
 * echo $this->element->getLabel();
 * </code>
 *
 * Any options other than separator, placement, viewScript, and viewModule are passed to
 * the partial as local variables.
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Form_Decorator_ViewScript extends Zend_Form_Decorator_Abstract
{
    /**
     * Default placement: append
     * @var string
     */
    protected $_placement = 'APPEND';

    /**
     * View script to render
     * @var string
     */
    protected $_viewScript;

    /**
     * View script module
     * @var string
     */
    protected $_viewModule;

    /**
     * Set view script
     *
     * @param  string $script
     * @return Zend_Form_Decorator_ViewScript
     */
    public function setViewScript($script)
    {
        $this->_viewScript = (string) $script;
        return $this;
    }

    /**
     * Get view script
     *
     * @return string|null
     */
    public function getViewScript()
    {
        if (null === $this->_viewScript) {
            if (null !== ($element = $this->getElement())) {
                if (null !== ($viewScript = $element->getAttrib('viewScript'))) {
                    $this->setViewScript($viewScript);
                    return $viewScript;
                }
            }

            if (null !== ($viewScript = $this->getOption('viewScript'))) {
                $this->setViewScript($viewScript)
                     ->removeOption('viewScript');
            }
        }

        return $this->_viewScript;
    }

    /**
     * Set view script module
     *
     * @param  string $module
     * @return Zend_Form_Decorator_ViewScript
     */
    public function setViewModule($viewModule)
    {
        $this->_viewModule = (string) $viewModule;
        return $this;
    }

    /**
     * Get view script module
     *
     * @return string|null
     */
    public function getViewModule()
    {
        if (null === $this->_viewModule) {
            if (null !== ($element = $this->getElement())) {
                if (null !== ($viewModule = $element->getAttrib('viewModule'))) {
                    $this->setViewModule($viewModule);
                    return $viewModule;
                }
            }

            if (null !== ($viewModule = $this->getOption('viewModule'))) {
                $this->setViewModule($viewModule)
                     ->removeOption('viewModule');
            }
        }

        return $this->_viewModule;
    }

    /**
     * Render a view script
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $view    = $element->getView();
        if (null === $view) {
            return $content;
        }

        $viewScript = $this->getViewScript();
        if (empty($viewScript)) {
            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('No view script registered with ViewScript decorator');
        }

        $separator = $this->getSeparator();
        $placement = $this->getPlacement();

        $vars              = $this->getOptions();
        $vars['element']   = $element;
        $vars['content']   = $content;
        $vars['decorator'] = $this;

        $viewModule = $this->getViewModule();
        if (empty($viewModule)) {
            $renderedContent = $view->partial($viewScript, $vars);
        } else {
            $renderedContent = $view->partial($viewScript, $viewModule, $vars);
        }

        // Get placement again to see if it has changed
        $placement = $this->getPlacement();

        switch ($placement) {
            case self::PREPEND:
                return $renderedContent . $separator . $content;
            case self::APPEND:
                return $content . $separator . $renderedContent;
            default:
                return $renderedContent;
        }
    }
}
