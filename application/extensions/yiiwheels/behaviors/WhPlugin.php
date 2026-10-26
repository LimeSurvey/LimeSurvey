<?php
/**
 * WhPlugin class file.
 * Extends the plugins with common shared methods.
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; Antonio Ramirez 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package yiiwheels.behaviors
 */
class WhPlugin extends CBehavior
{
    /** @var int Static counter for generating unique script IDs. */
    public static $counter = 0;

    protected $_assetsUrl;

    protected static $_wheels;

    /**
     * Returns the asset URL, publishing from $path if not yet published.
     * @param string $path
     * @return string
     */
    public function getAssetsUrl($path)
    {
        if (isset($this->_assetsUrl)) {
            return $this->_assetsUrl;
        }
        $assetsUrl = Yii::app()->assetManager->publish($path, false, -1, null);
        return $this->_assetsUrl = $assetsUrl;
    }

    /**
     * Returns self so widgets can call $this->getApi()->registerPlugin() unchanged.
     * @return $this
     */
    public function getApi()
    {
        return $this;
    }

    /**
     * Registers a jQuery plugin call via clientScript.
     * @param string $name the plugin name.
     * @param string $selector the CSS selector.
     * @param array $options the JavaScript options for the plugin.
     * @param int $position the position of the JavaScript code.
     */
    public function registerPlugin($name, $selector, $options = array(), $position = CClientScript::POS_END)
    {
        $options = !empty($options) ? CJavaScript::encode($options) : '';
        $script = "jQuery('{$selector}').{$name}({$options});";
        $id = __CLASS__ . '#Plugin' . self::$counter++;
        Yii::app()->clientScript->registerScript($id, $script, $position);
    }

    /**
     * Registers jQuery event handlers via clientScript.
     * @param string $selector the CSS selector.
     * @param string[] $events the JavaScript event configuration (name=>handler).
     * @param int $position the position of the JavaScript code.
     */
    public function registerEvents($selector, $events, $position = CClientScript::POS_END)
    {
        if (!empty($events)) {
            $script = '';
            foreach ($events as $name => $handler) {
                if (!$handler instanceof CJavaScriptExpression) {
                    $handler = new CJavaScriptExpression($handler);
                }
                $script .= "jQuery('{$selector}').on('{$name}', {$handler});";
            }
            $id = __CLASS__ . '#Events' . self::$counter++;
            Yii::app()->clientScript->registerScript($id, $script, $position);
        }
    }

    /**
     * Returns the main YiiWheels component.
     * @return YiiWheels
     */
    public function getYiiWheels()
    {
        if (self::$_wheels === null) {
            self::$_wheels = Yii::app()->getComponent('yiiwheels');
        }
        return self::$_wheels;
    }
}
