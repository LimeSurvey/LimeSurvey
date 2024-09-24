<?php
/**
 * WhGallery widget class
 *
 * Renders a gallery of blueimp Gallery
 * @see http://blueimp.github.io/Gallery/
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.gallery
 * @uses YiiStrap.helpers.TbArray
 * @uses YiiStrap.helpers.TbHtml
 */
Yii::import('yiistrap_fork.helpers.TbArray');

class WhGallery extends CWidget
{
	/**
	 * @var array
	 * box HTML additional attributes
	 */
	public $htmlOptions = array();

	/**
	 * @var array $options the blueimp gallery js configuration options
	 * @see https://github.com/blueimp/Gallery/blob/master/README.md#options
	 */
	public $pluginOptions = array();

	/**
	 * The array of items that compound the gallery. The syntax is as follows:
	 *
	 * <pre>
	 *  'items' => array(
	 *        array(
	 *            'url' => 'big image',
	 *            'src' => 'source image (thumb)',
	 *            'options' => array(...) // link options
	 *        )
	 * )
	 * </pre>
	 * @var array
	 */
	public $items = array();

	/**
	 * @var bool whether to display the controls on initialization
	 */
	public $displayControls = false;

	/**
	 * Widget's initialization
	 */
	public function init()
	{
		$this->htmlOptions['id'] = TbArray::getValue('id', $this->htmlOptions, $this->getId());
		$this->pluginOptions['container'] = '#' . $this->htmlOptions['id'] . '-gallery';
		$this->attachBehavior('ywplugin', array('class' => 'yiiwheels.behaviors.WhPlugin'));
		parent::init();
	}

	/**
	 * Renders widget
	 * @return null|void
	 */
	public function run()
	{
		if (empty($this->items)) {
			return null;
		}
		$this->renderLinks();
		$this->renderTemplate();
		$this->registerClientScript();
	}

	/**
	 * Renders links
	 */
	public function renderLinks()
	{
		echo CHtml::openTag('div', $this->htmlOptions);
		foreach ($this->items as $item) {
			$url = TbArray::getValue('url', $item, '#');
			$src = TbArray::getValue('src', $item, '#');
			$options = TbArray::getValue('options', $item );
			echo CHtml::link(CHtml::image($src), $url, $options);
		}
		echo CHtml::closeTag('div');
	}

	/**
	 * Renders gallery template
	 */
	public function renderTemplate()
	{
		$options = array(
			'id' => $this->htmlOptions['id'] . '-gallery',
			'class' => 'blueimp-gallery'
		);
		if($this->displayControls) {
			TbHtml::addCssClass('blueimp-gallery-controls', $options);
		}
		echo CHtml::openTag('div', $options);
		echo '<div class="slides"></div>
		<h3 class="title"></h3>
		<a class="prev">‹</a>
		<a class="next">›</a>
		<a class="close">×</a>
		<a class="play-pause"></a>
		<ol class="indicator"></ol>';
		echo CHtml::closeTag('div');
	}

	/**
	 * Registers gallery script files
	 */
	public function registerGalleryScriptFiles()
	{
		/* publish assets dir */
		$path      = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
		$assetsUrl = $this->getAssetsUrl($path);

		/* @var $cs CClientScript */
		$cs = Yii::app()->getClientScript();

		$cs->registerScriptFile($assetsUrl . '/js/blueimp-gallery.min.js', CClientScript::POS_END);
		$cs->registerScriptFile($assetsUrl . '/js/blueimp-gallery-indicator.js', CClientScript::POS_END);
		$cs->registerCssFile($assetsUrl . '/css/blueimp-gallery.min.css');
	}

	/**
	 * Registers client script
	 */
	public function registerClientScript()
	{
		$this->registerGalleryScriptFiles();
		$selector = $this->htmlOptions['id'];

		$options = CJavaScript::encode($this->pluginOptions);
		$js = "
;var galleryLinks = [];
$(document).on('click', '#{$selector} a', function(e){
	var links = $(this).parent()[0].getElementsByTagName('a');
	var options = {$options};
	options.index = $(this)[0];
	blueimp.Gallery(links, options);
	return false;
});
		";

		Yii::app()->clientScript->registerScript(__CLASS__.'#'.$this->getId(), $js);
	}

}
