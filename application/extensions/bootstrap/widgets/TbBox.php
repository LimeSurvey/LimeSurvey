<?php
/**
 * TbBox widget class
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * @copyright Copyright &copy; Clevertech 2012-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiBooster bootstrap.widgets
 */
class TbBox extends CWidget
{
	/**
	 * Box title
	 * If set to false, a box with no title is rendered
	 * @var mixed
	 */
	public $title = '';

	/**
	 * The class icon to display in the header title of the box.
	 * @see http://twitter.github.com/bootstrap/base-css.html#icon
	 * @var string
	 */
	public $headerIcon;


	/**
	 * Box Content
	 * optional, the content of this attribute is echoed as the box content
	 * @var string
	 */
	public $content = '';

	/**
	 * box HTML additional attributes
	 * @var array
	 */
	public $htmlOptions = array();

	/**
	 * box header HTML additional attributes
	 * @var array
	 */
	public $htmlHeaderOptions = array();

	/**
	 * box content HTML additional attributes
	 * @var array
	 */
	public $htmlContentOptions = array();

	/**
	 * @var array the configuration for additional header buttons. Each array element specifies a single button
	 * which has the following format:
	 * <pre>
	 *     array(
	 *        array(
	 *          'class' => 'bootstrap.widgets.TbButton',
	 *          'label' => '...',
	 *          'size' => '...',
	 *          ...
	 *        ),
	 *      array(
	 *          'class' => 'bootstrap.widgets.TbButtonGroup',
	 *          'buttons' => array( ... ),
	 *          'size' => '...',
	 *        ),
	 *      ...
	 * )
	 * </pre>
	 */
	public $headerButtons = array();

	/**
	 * Widget initialization
	 */
	public function init()
	{
		if (isset($this->htmlOptions['class']))
			$this->htmlOptions['class'] = 'bootstrap-widget ' . $this->htmlOptions['class'];
		else
			$this->htmlOptions['class'] = 'bootstrap-widget';

		if (isset($this->htmlContentOptions['class']))
			$this->htmlContentOptions['class'] = 'bootstrap-widget-content ' . $this->htmlContentOptions['class'];
		else
			$this->htmlContentOptions['class'] = 'bootstrap-widget-content';

		if (!isset($this->htmlContentOptions['id']))
			$this->htmlContentOptions['id'] = $this->getId();

		if (isset($this->htmlHeaderOptions['class']))
			$this->htmlHeaderOptions['class'] = 'bootstrap-widget-header ' . $this->htmlHeaderOptions['class'];
		else
			$this->htmlHeaderOptions['class'] = 'bootstrap-widget-header';

		echo CHtml::openTag('div', $this->htmlOptions);

		$this->registerClientScript();
		$this->renderHeader();
		$this->renderContentBegin();
	}

	/**
	 * Widget run - used for closing procedures
	 */
	public function run()
	{
		$this->renderContentEnd();
		echo CHtml::closeTag('div') . "\n";
	}

	/**
	 * Renders the header of the box with the header control (button to show/hide the box)
	 */
	public function renderHeader()
	{
		if ($this->title !== false || $this->headerCtrl !== false)
		{
			echo CHtml::openTag('div', $this->htmlHeaderOptions);
			if ($this->title)
			{
				$this->title = '<h3>' . $this->title . '</h3>';

				if ($this->headerIcon)
				{
					$this->title = '<i class="' . $this->headerIcon . '"></i>' . $this->title;
				}

				echo $this->title;
				$this->renderButtons();
			}

			echo CHtml::closeTag('div');
		}
	}

	/**
	 * Renders a header buttons to display the configured actions
	 */
	public function renderButtons()
	{
		if (empty($this->headerButtons))
			return;

		echo '<div class="bootstrap-toolbar pull-right">';

		if(!empty($this->headerButtons) && is_array($this->headerButtons))
		{
			foreach($this->headerButtons as $button)
			{
				$options = $button;
				$button = $options['class'];
				unset($options['class']);

				if(strpos($button, 'TbButton') === false)
					throw new CException('message');

				if(!isset($options['htmlOptions']))
					$options['htmlOptions'] = array();

				$class = isset($options['htmlOptions']['class']) ? $options['htmlOptions']['class'] : '';
				$options['htmlOptions']['class'] = $class .' pull-right';

				$this->controller->widget($button, $options);
			}
		}

		echo '</div>';
	}

	/*
	  * Renders the opening of the content element and the optional content
	  */
	public function renderContentBegin()
	{
		echo CHtml::openTag('div', $this->htmlContentOptions);
		if (!empty($this->content))
			echo $this->content;
	}

	/*
	 * Closes the content element
	 */
	public function renderContentEnd()
	{
		echo CHtml::closeTag('div');
	}

	/**
	 * Registers required script files (CSS in this case)
	 */
	public function registerClientScript()
	{
		Yii::app()->bootstrap->registerAssetCss('bootstrap-box.css');

	}
}