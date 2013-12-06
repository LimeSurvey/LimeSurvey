<?php
/**
 * TbCarousel class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 * @since 0.9.10
 */

/**
 * Bootstrap carousel widget.
 * @see http://twitter.github.com/bootstrap/javascript.html#carousel
 */
class TbCarousel extends CWidget
{
	/**
	 * @var string the previous button label. Defaults to '&lsaquo;'.
	 */
	public $prevLabel = '&lsaquo;';
	/**
	 * @var string the next button label. Defaults to '&rsaquo;'.
	 */
	public $nextLabel = '&rsaquo;';
	/**
	 * @var boolean indicates whether the carousel should slide items.
	 */
	public $slide = true;
	/**
	 * @var boolean indicates whether to display the previous and next links.
	 */
	public $displayPrevAndNext = true;
	/**
	 * @var array the carousel items configuration.
	 */
	public $items = array();
	/**
	 * @var array the options for the Bootstrap Javascript plugin.
	 */
	public $options = array();
	/**
	 * @var string[] the Javascript event handlers.
	 */
	public $events = array();
	/**
	 * @var array the HTML attributes for the widget container.
	 */
	public $htmlOptions = array();

	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		if (!isset($this->htmlOptions['id']))
			$this->htmlOptions['id'] = $this->getId();

		$classes = array('carousel');

		if ($this->slide === true)
			$classes[] = 'slide';

		if (!empty($classes))
		{
			$classes = implode(' ', $classes);
			if (isset($this->htmlOptions['class']))
				$this->htmlOptions['class'] .= ' '.$classes;
			else
				$this->htmlOptions['class'] = $classes;
		}
	}

	/**
	 * Runs the widget.
	 */
	public function run()
	{
		$id = $this->htmlOptions['id'];

		echo CHtml::openTag('div', $this->htmlOptions);
		echo '<div class="carousel-inner">';
		$this->renderItems($this->items);

		if ($this->displayPrevAndNext)
		{
			echo '</div>';
			echo '<a class="carousel-control left" href="#'.$id.'" data-slide="prev">'.$this->prevLabel.'</a>';
			echo '<a class="carousel-control right" href="#'.$id.'" data-slide="next">'.$this->nextLabel.'</a>';
			echo '</div>';
		}
		else
		{
			echo '</div>';
			echo '</div>';
		}

		/** @var CClientScript $cs */
		$cs = Yii::app()->getClientScript();
		$options = !empty($this->options) ? CJavaScript::encode($this->options) : '';
		$cs->registerScript(__CLASS__.'#'.$id, "jQuery('#{$id}').carousel({$options});");

		foreach ($this->events as $name => $handler)
		{
			$handler = CJavaScript::encode($handler);
			$cs->registerScript(__CLASS__.'#'.$id.'_'.$name, "jQuery('#{$id}').on('{$name}', {$handler});");
		}
	}

	/**
	 * Renders the carousel items.
	 * @param array $items the item configuration.
	 */
	protected function renderItems($items)
	{
		foreach ($items as $i => $item)
		{
			if (!is_array($item))
				continue;

			if (isset($item['visible']) && $item['visible'] === false)
				continue;

			if (!isset($item['itemOptions']))
				$item['itemOptions'] = array();

			$classes = array('item');

			if ($i === 0)
				$classes[] = 'active';

			if (!empty($classes))
			{
				$classes = implode(' ', $classes);
				if (isset($item['itemOptions']['class']))
					$item['itemOptions']['class'] .= ' '.$classes;
				else
					$item['itemOptions']['class'] = $classes;
			}

			echo CHtml::openTag('div', $item['itemOptions']);

			if (isset($item['image']))
			{
				if (!isset($item['alt']))
					$item['alt'] = '';

				if (!isset($item['imageOptions']))
					$item['imageOptions'] = array();

				echo CHtml::image($item['image'], $item['alt'], $item['imageOptions']);
			}

			if (!empty($item['caption']) && (isset($item['label']) || isset($item['caption'])))
			{
				if (!isset($item['captionOptions']))
					$item['captionOptions'] = array();

				if (isset($item['captionOptions']['class']))
					$item['captionOptions']['class'] .= ' carousel-caption';
				else
					$item['captionOptions']['class'] = 'carousel-caption';

				echo CHtml::openTag('div', $item['captionOptions']);

				if (isset($item['label']))
					echo '<h4>'.$item['label'].'</h4>';

				if (isset($item['caption']))
					echo '<p>'.$item['caption'].'</p>';

				echo '</div>';
			}

			echo '</div>';
		}
	}
}
