<?php
/**
 * WhDateTimePicker widget class
 * A simple implementation for date range picker for Twitter Bootstrap
 * @see <http://www.dangrossman.info/2012/08/20/a-date-range-picker-for-twitter-bootstrap/>
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.datetimepicker
 * @uses YiiStrap.helpers.TbArray
 */
Yii::import('bootstrap.helpers.TbArray');

class WhDateTimePicker extends CInputWidget
{

	/**
	 * @var string $selector if provided, then no input field will be rendered. It will write the JS code for the
	 * specified selector.
	 */
	public $selector;

	/**
	 * @var string the date format.
	 */
	public $format = 'dd/MM/yyyy hh:mm:ss';

	/**
	 * @var string the icon to display when selecting times
	 */
	public $iconTime = 'icon-time';

	/**
	 * @var string the icon to display when selecting dates
	 */
	public $iconDate = 'icon-calendar';

	/**
	 * @var array pluginOptions to be passed to datetimepicker plugin. Defaults are:
	 *
	 * - maskInput: true, disables the text input mask
	 * - pickDate: true,  disables the date picker
	 * - pickTime: true,  disables de time picker
	 * - pick12HourFormat: false, enables the 12-hour format time picker
	 * - pickSeconds: true, disables seconds in the time picker
	 * - startDate: -Infinity, set a minimum date
	 * - endDate: Infinityset a maximum date
	 */
	public $pluginOptions = array();

	/**
	 * @var string[] the JavaScript event handlers.
	 */
	public $events = array();

	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		$this->attachBehavior('ywplugin', array('class' => 'yiiwheels.behaviors.WhPlugin'));
		$this->htmlOptions['id'] = TbArray::getValue('id', $this->htmlOptions, $this->getId());
		$this->htmlOptions['data-format'] = $this->format;
	}

	/**
	 * Runs the widget.
	 */
	public function run()
	{
		$this->renderField();
		$this->registerClientScript();
	}

	/**
	 * Renders the field if no selector has been provided
	 */
	public function renderField()
	{
		if (null === $this->selector) {
			$options = array();

			list($name, $id) = $this->resolveNameID();

			$options['id'] = $id . '_datetimepicker';
			TbHtml::addCssClass('input-append', $options);

			echo TbHtml::openTag('div', $options);
			if ($this->hasModel()) {
				echo TbHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
			} else {
				echo TbHtml::textField($name, $this->value, $this->htmlOptions);
			}
			echo TbHtml::openTag('span', array('class' => 'add-on'));
			echo '<i data-time-icon="' . $this->iconTime . '" data-date-icon="' . $this->iconDate . '"></i>';
			echo TbHtml::closeTag('span');
			echo TbHtml::closeTag('div');
		}
	}


	/**
	 *
	 * Registers required css js files
	 */
	public function registerClientScript()
	{
		/* publish assets dir */
		$path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
		$assetsUrl = $this->getAssetsUrl($path);

		/* @var $cs CClientScript */
		$cs = Yii::app()->getClientScript();

		$cs->registerCssFile($assetsUrl . '/css/bootstrap-datetimepicker.min.css');
		$cs->registerScriptFile($assetsUrl . '/js/bootstrap-datetimepicker.min.js', CClientScript::POS_END);
		if (isset($this->pluginOptions['language'])) {
			$cs->registerScriptFile(
				$assetsUrl . '/js/locales/bootstrap-datetimepicker.' . $this->pluginOptions['language'] . '.js'
			, CClientScript::POS_END);
		}
		/* initialize plugin */
		/* initialize plugin */
		$selector = null === $this->selector
			? '#' . TbArray::getValue('id', $this->htmlOptions, $this->getId()) . '_datetimepicker'
			: $this->selector;

		$this->getApi()->registerPlugin('datetimepicker', $selector, $this->pluginOptions);

		if($this->events)
		{

			$this->getApi()->registerEvents($selector, $this->events);
		}
	}
}
