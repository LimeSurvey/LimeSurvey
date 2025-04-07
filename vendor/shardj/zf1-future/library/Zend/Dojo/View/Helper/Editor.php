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

/** Zend_Dojo_View_Helper_Dijit */
require_once 'Zend/Dojo/View/Helper/Dijit.php';

/** Zend_Json */
require_once 'Zend/Json.php';

/**
 * Dojo Editor dijit
 *
 * @uses       Zend_Dojo_View_Helper_Textarea
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Dojo_View_Helper_Editor extends Zend_Dojo_View_Helper_Dijit
{
    /**
     * @var string Dijit type
     */
    protected $_dijit = 'dijit.Editor';

    /**
     * @var string Dijit module to load
     */
    protected $_module = 'dijit.Editor';

    /**
     * @var array Maps non-core plugin to module basename
     */
    protected $_pluginsModules = [
        'createLink' => 'LinkDialog',
        'insertImage' => 'LinkDialog',
        'fontName' => 'FontChoice',
        'fontSize' => 'FontChoice',
        'formatBlock' => 'FontChoice',
        'foreColor' => 'TextColor',
        'hiliteColor' => 'TextColor',
        'enterKeyHandling' => 'EnterKeyHandling',
        'fullScreen' => 'FullScreen',
        'newPage' => 'NewPage',
        'print' => 'Print',
        'tabIndent' => 'TabIndent',
        'toggleDir' => 'ToggleDir',
        'viewSource' => 'ViewSource'
    ];

    /**
     * JSON-encoded parameters
     * @var array
     */
    protected $_jsonParams = ['captureEvents', 'events', 'plugins', 'extraPlugins'];

    /**
     * dijit.Editor
     *
     * @param  string $id
     * @param  string $value
     * @param  array $params
     * @param  array $attribs
     * @return string
     */
    public function editor($id, $value = null, $params = [], $attribs = [])
    {
        if (isset($params['plugins'])) {
            foreach ($this->_getRequiredModules($params['plugins']) as $module) {
                $this->dojo->requireModule($module);
            }
        }

        // Previous versions allowed specifying "degrade" to allow using a
        // textarea instead of a div -- but this is insecure. Removing the
        // parameter if set to prevent its injection in the dijit.
        if (isset($params['degrade'])) {
            unset($params['degrade']);
        }

        $hiddenName = $id;
        if (array_key_exists('id', $attribs)) {
            $hiddenId = $attribs['id'];
        } else {
            $hiddenId = $hiddenName;
        }
        $hiddenId = $this->_normalizeId($hiddenId);

        $textareaName = $this->_normalizeEditorName($hiddenName);
        $textareaId   = $hiddenId . '-Editor';

        $hiddenAttribs = [
            'id'    => $hiddenId,
            'name'  => $hiddenName,
            'value' => $value,
            'type'  => 'hidden',
        ];
        $attribs['id'] = $textareaId;

        $this->_createGetParentFormFunction();
        $this->_createEditorOnSubmit($hiddenId, $textareaId);

        $attribs = $this->_prepareDijit($attribs, $params, 'textarea');

        $html  = '<div' . $this->_htmlAttribs($attribs) . '>'
               . $value
               . "</div>\n";

        // Embed a textarea in a <noscript> tag to allow for graceful
        // degradation
        $html .= '<noscript>'
               . $this->view->formTextarea($hiddenId, $value, $attribs)
               . '</noscript>';

        $html  .= '<input' . $this->_htmlAttribs($hiddenAttribs) . $this->getClosingBracket();

        return $html;
    }

    /**
     * Generates the list of required modules to include, if any is needed.
     *
     * @param array $plugins plugins to include
     * @return array
     */
    protected function _getRequiredModules(array $plugins)
    {
        $modules = [];
        foreach ($plugins as $commandName) {
            if (isset($this->_pluginsModules[$commandName])) {
                $pluginName = $this->_pluginsModules[$commandName];
                $modules[] = 'dijit._editor.plugins.' . $pluginName;
            }
        }

        return array_unique($modules);
    }

    /**
     * Normalize editor element name
     *
     * @param  string $name
     * @return string
     */
    protected function _normalizeEditorName($name)
    {
        if ('[]' == substr($name, -2)) {
            $name = substr($name, 0, strlen($name) - 2);
            $name .= '[Editor][]';
        } else {
            $name .= '[Editor]';
        }
        return $name;
    }

    /**
     * Create onSubmit binding for element
     *
     * @param  string $hiddenId
     * @param  string $editorId
     * @return void
     */
    protected function _createEditorOnSubmit($hiddenId, $editorId)
    {
        $this->dojo->onLoadCaptureStart();
        echo <<<EOJ
function() {
    var form = zend.findParentForm(dojo.byId('$hiddenId'));
    dojo.connect(form, 'submit', function(e) {
        var value = dijit.byId('$editorId').getValue(false);
        if(dojo.isFF) {
            value = value.replace(/<br _moz_editor_bogus_node="TRUE" \/>/, '');
        }
        dojo.byId('$hiddenId').value = value;
    });
}
EOJ;
        $this->dojo->onLoadCaptureEnd();
    }
}
