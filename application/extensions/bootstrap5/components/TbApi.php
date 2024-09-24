<?php
/**
 * TbApi class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.components
 * @version 1.2.0
 */

/**
 * Bootstrap API component.
 */
class TbApi extends CApplicationComponent
{
    // Bootstrap plugins
    const PLUGIN_AFFIX = 'affix';
    const PLUGIN_ALERT = 'alert';
    const PLUGIN_BUTTON = 'button';
    const PLUGIN_CAROUSEL = 'carousel';
    const PLUGIN_COLLAPSE = 'collapse';
    const PLUGIN_DROPDOWN = 'dropdown';
    const PLUGIN_MODAL = 'modal';
    const PLUGIN_POPOVER = 'popover';
    const PLUGIN_SCROLLSPY = 'scrollspy';
    const PLUGIN_TAB = 'tab';
    const PLUGIN_TOOLTIP = 'tooltip';
    const PLUGIN_TRANSITION = 'transition';
    const PLUGIN_TYPEAHEAD = 'typeahead';

    /**
     * @var int static counter, used for determining script identifiers.
     */
    public static $counter = 0;

    /**
     * @var string path to Bootstrap assets (will default to node_modules/bootstrap/dist).
     */
    public $bootstrapPath;

    /**
     * @var bool whether we should copy the asset file or directory even if it is already published before.
     */
    public $forceCopyAssets = false;

    /**
     * @var string base URL to Bootstrap CDN - set this if you wish to use Bootstrap though a CDN.
     * @see http://getbootstrap.com/getting-started/#download-cdn
     */
    public $cdnUrl;

    private $_assetsUrl;
    private $_bootstrapUrl;

    /**
     * Initializes this component.
     */
    public function init()
    {
        parent::init();
        if ($this->bootstrapPath === null) {
            $this->bootstrapPath = Yii::getPathOfAlias('vendor.twbs.bootstrap.dist');
        }
    }

    /**
     * Registers the Bootstrap CSS.
     * @param string $url the URL to the CSS file to register.
     * @param string $media the media type (defaults to 'screen').
     */
    public function registerCoreCss($url = null, $media = 'screen')
    {
        if ($url === null) {
            $fileName = YII_DEBUG ? 'bootstrap.css' : 'bootstrap.min.css';
            $url = $this->getBootstrapUrl() . '/css/' . $fileName;
        }
        Yii::app()->getClientScript()->registerCssFile($url, $media);
    }

    /**
     * Registers the Bootstrap theme CSS.
     * @param string $url the URL to the CSS file to register.
     * @param string $media the media type (defaults to 'screen').
     */
    public function registerThemeCss($url = null, $media = 'screen')
    {
        if ($url === null) {
            $fileName = YII_DEBUG ? 'bootstrap-theme.css' : 'bootstrap-theme.min.css';
            $url = $this->getBootstrapUrl() . '/css/' . $fileName;
        }
        Yii::app()->getClientScript()->registerCssFile($url, $media);
    }

    /**
     * Registers the Yiistrap CSS.
     * @param string $url the URL to the CSS file to register.
     * @param string $media the media type (default to 'screen').
     */
    public function registerYiistrapCss($url = null, $media = 'screen')
    {
        if ($url === null) {
            $fileName = YII_DEBUG ? 'yiistrap.css' : 'yiistrap.min.css';
            $url = $this->getAssetsUrl() . '/css/' . $fileName;
        }
        Yii::app()->getClientScript()->registerCssFile($url, $media);
    }

    /**
     * Fixes panning and zooming on mobile devices.
     * @see http://getbootstrap.com/css/#overview-mobile
     */
    public function fixPanningAndZooming()
    {
        Yii::app()->getClientScript()->registerMetaTag('width=device-width, initial-scale=1.0', 'viewport');
    }

    /**
     * Registers all Bootstrap CSS files.
     */
    public function registerAllCss()
    {
        $this->registerCoreCss();
        $this->registerYiistrapCss();
        $this->fixPanningAndZooming();
    }

    /**
     * Registers jQuery and Bootstrap JavaScript.
     * @param string $url the URL to the JavaScript file to register.
     * @param int $position the position of the JavaScript code.
     * 20210322: GJ - Commenting the whole method implementation. JS and CSS are not needed 
     * and by commenting them, we don't need to mantain them.
     */
    public function registerCoreScripts($url = null, $position = CClientScript::POS_END)
    {
        /*if ($url === null) {
            $fileName = YII_DEBUG ? 'bootstrap.js' : 'bootstrap.min.js';
            $url = $this->getBootstrapUrl() . '/js/' . $fileName;
        }*/
        /** @var CClientScript $cs */
        /*$cs = Yii::app()->getClientScript();
        $cs->registerCoreScript('jquery');
        $cs->registerScriptFile($url, $position);*/
    }

    /**
     * Registers the Tooltip and Popover plugins.
     */
    public function registerTooltipAndPopover()
    {
        $this->registerPopover();
        $this->registerTooltip();
    }

    /**
     * Registers all Bootstrap JavaScript files.
     */
    public function registerAllScripts()
    {
        $this->registerCoreScripts();
        $this->registerTooltipAndPopover();
    }

    /**
     * Registers all assets.
     */
    public function register()
    {
        $this->registerAllCss();
        $this->registerAllScripts();
    }

    /**
     * Registers the Bootstrap Popover plugin.
     * @param string $selector the CSS selector.
     * @param array $options the JavaScript options for the plugin.
     * @see http://twitter.github.com/bootstrap/javascript.html#popover
     */
    public function registerPopover($selector = 'body', $options = array())
    {
        TbArray::defaultValue('selector', 'a[rel=popover]', $options);
        $this->registerPlugin(self::PLUGIN_POPOVER, $selector, $options);
    }

    /**
     * Registers the Bootstrap Tooltip plugin.
     * @param string $selector the CSS selector.
     * @param array $options the JavaScript options for the plugin.
     * @see http://twitter.github.com/bootstrap/javascript.html#tooltip
     */
    public function registerTooltip($selector = 'body', $options = array())
    {
        TbArray::defaultValue('selector', 'a[rel=tooltip]', $options);
        $this->registerPlugin(self::PLUGIN_TOOLTIP, $selector, $options);
    }

    /**
     * Registers a specific Bootstrap plugin using the given selector and options.
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
     * Registers events using the given selector.
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
     * Returns the url to the published Bootstrap folder, or the CDN if applicable.
     * @return string the url.
     * @throws Exception
     */
    protected function getBootstrapUrl()
    {
        if (!isset($this->_bootstrapUrl)) {
            if (isset($this->cdnUrl)) {
                $this->_bootstrapUrl = $this->cdnUrl;
            } else {
                if (($path = Yii::getPathOfAlias($this->bootstrapPath)) !== false) {
                    $this->bootstrapPath = $path;
                } else if ($this->bootstrapPath === false) {
                    throw new Exception("Invalid Bootstrap path and CDN URL not set. Set vendor.twbs.bootstrap.dist alias or cdnUrl parameter in the configuration file.");
                }
                $this->_bootstrapUrl = Yii::app()->assetManager->publish($this->bootstrapPath, false, -1, $this->forceCopyAssets);
            }
        }
        return $this->_bootstrapUrl;
    }

    /**
     * Returns the url to the published folder that contains the assets for this extension.
     * @return string the url.
     */
    protected function getAssetsUrl()
    {
        if (!isset($this->_assetsUrl)) {
            $assetPath = dirname(__DIR__) . '/assets';
            $this->_assetsUrl = Yii::app()->assetManager->publish($assetPath, false, -1, $this->forceCopyAssets);
        }
        return $this->_assetsUrl;
    }
}
