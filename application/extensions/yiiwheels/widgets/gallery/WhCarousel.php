<?php
/**
 * WhCarousel widget class
 *
 * Renders a carousel of blueimp Gallery
 * @see http://blueimp.github.io/Gallery/
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.gallery
 * @uses YiiStrap.helpers.TbArray
 * @uses YiiStrap.helpers.TbHtml
 */
Yii::import('yiistrap_fork.helpers.TbHtml');
Yii::import('yiistrap_fork.helpers.TbArray');
Yii::import('yiiwheels.widgets.gallery.WhGallery');

class WhCarousel extends WhGallery
{

	/**
	 * Widget's initialization
	 */
	public function init()
	{
		parent::init();
		$this->pluginOptions['carousel'] = true;
		$this->pluginOptions['container'] = '#' . $this->htmlOptions['id'] . '-carousel';
	}

	/**
	 * Renders the links
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
	 * Renders the template
	 */
	public function renderTemplate()
	{
		$options = array(
			'id' => $this->htmlOptions['id'] . '-carousel',
			'class' => 'blueimp-gallery blueimp-gallery-carousel'
		);
		if($this->displayControls) {
			TbHtml::addCssClass('blueimp-gallery-controls', $options);
		}
		echo CHtml::openTag('div', $options);
		echo '<div class="slides"></div>
		<h3 class="title"></h3>
		<a class="prev">‹</a>
		<a class="next">›</a>
		<a class="play-pause"></a>
		<ol class="indicator"></ol>';
		echo CHtml::closeTag('div');
	}

	/**
	 * Registers the client script
	 */
	public function registerClientScript()
	{
		$this->registerGalleryScriptFiles();
		$selector = $this->htmlOptions['id'];
		$options = CJavaScript::encode($this->pluginOptions);

		$js = ";$('#{$selector} a').hide();blueimp.Gallery($('#{$selector}')[0].getElementsByTagName('a'),{$options});";

		Yii::app()->clientScript->registerScript(__CLASS__.'#'.$this->getId(), $js);
	}

}
