<?php
/**
 * TbProgress class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 * @since 0.9.10
 */

/**
 * Bootstrap progress bar widget.
 * @see http://twitter.github.com/bootstrap/components.html#progress
 */
class TbProgress extends CWidget
{
	// Progress bar types.
	const TYPE_INFO = 'info';
	const TYPE_SUCCESS = 'success';
	const TYPE_WARNING = 'warning';
	const TYPE_DANGER = 'danger';

	/**
	 * @var string the bar type. Valid values are 'info', 'success', and 'danger'.
	 */
	public $type;
	/**
	 * @var boolean indicates whether the bar is striped.
	 */
	public $striped = false;
	/**
	 * @var boolean indicates whether the bar is animated.
	 */
	public $animated = false;
	/**
	 * @var integer the amount of progress in percent.
	 */
	public $percent = 0;
	/**
	 * @var array the HTML attributes for the widget container.
	 */
	public $htmlOptions = array();
	/**
	 * @var string div content
	 */
	public $content;
	/**
	 * @var array $stacked set to an array of progress bar values to display stacked progress bars
	 * <pre>
	 *  'stacked'=>array(
	 *      array('type' => 'info|success|warning|danger', 'percent'=>'30', 'htmlOptions'=>array('class'=>'custom')),
	 *      array('type' => 'info|success|warning|danger', 'percent'=>'30'),
	 *  )
	 * </pre>
	 * @since 9/21/12 8:14 PM antonio ramirez <antonio@clevertech.biz>
	 */
	public $stacked;

	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		$classes = array('progress');
		if(empty($this->stacked))
		{
			$validTypes = array(self::TYPE_INFO, self::TYPE_SUCCESS, self::TYPE_WARNING, self::TYPE_DANGER);

			if (isset($this->type) && in_array($this->type, $validTypes))
				$classes[] = 'progress-'.$this->type;

			if ($this->striped)
				$classes[] = 'progress-striped';

			if ($this->animated)
				$classes[] = 'active';

			if ($this->percent < 0)
				$this->percent = 0;
			else if ($this->percent > 100)
				$this->percent = 100;
		}

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
	 * @since  9/21/12 8:13 PM  antonio ramirez <antonio@clevertech.biz>
	 * Updated to use stacked progress bars
	 */
	public function run()
	{
		echo CHtml::openTag('div', $this->htmlOptions);
		if(empty($this->stacked))
		{
			echo '<div class="bar" style="width: '.$this->percent.'%;">'.$this->content.'</div>';
		}
		elseif (is_array($this->stacked))
		{
			foreach($this->stacked as $bar)
			{
				$options = isset($bar['htmlOptions'])? $bar['htmlOptions'] : array();
				$options['style'] = 'width: ' . $bar['percent']. '%';
				$options['class'] = 'bar bar-'.$bar['type'];
				echo '<div '.CHtml::renderAttributes($options).'>'.@$bar['content'].'</div>';
			}
		}
		echo '</div>';
	}
}
