<?php
/**
 * WhVideoCarousel widget class
 *
 * Renders a video carousel of blueimp Gallery
 * @see http://blueimp.github.io/Gallery/
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.gallery
 * @uses YiiStrap.helpers.TbArray
 * @uses YiiStrap.helpers.TbHtml
 */
Yii::import('yiiwheels.widgets.gallery.WhCarousel');

class WhVideoCarousel extends WhCarousel
{
	/**
	 * Widget's initialization
	 */
	public function init()
	{
		parent::init();
		$this->pluginOptions['carousel'] = true;
		$this->pluginOptions['container'] = '#' . $this->htmlOptions['id'] . '-videocarousel';
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
		$this->renderTemplate();
		$this->registerClientScript();
	}

	/**
	 * Renders the gallery template
	 */
	public function renderTemplate()
	{
		$options = array(
			'id' => $this->htmlOptions['id'] . '-videocarousel',
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
	 * Registers the script
	 */
	public function registerClientScript() {
		$this->registerGalleryScriptFiles();

		$items = CJavaScript::encode($this->items);
		$options = CJavaScript::encode($this->pluginOptions);
		$js = ";blueimp.Gallery({$items}, {$options});";

		Yii::app()->clientScript->registerScript(__CLASS__.'#'.$this->getId(), $js);
	}
}