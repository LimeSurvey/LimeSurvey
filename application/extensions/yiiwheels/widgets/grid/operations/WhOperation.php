<?php
/**
 *
 * WhOperation class
 *
 * Abstract class where all types of column operations extend from
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.grid.operations
 */
abstract class WhOperation extends CWidget
{
	/**
	 * @var string $template the template to display label and value of the operation at the summary
	 */
	public $template = '{label}: {value}';

	/**
	 * @var int $value the resulted value of operation
	 */
	public $value = 0;

	/**
	 * @var string $label the label of the calculated value
	 */
	public $label;

	/**
	 * @var WhDataColumn $column
	 */
	public $column;

	/**
	 * Widget initialization
	 * @throws CException
	 */
	public function init()
	{
		if (null == $this->column) {
			throw new CException(Yii::t(
				'zii',
				'"{attribute}" attribute must be defined',
				array('{attribute}' => 'column')
			));
		}

		$this->attachBehavior('ywplugin', array('class' => 'yiiwheels.behaviors.WhPlugin'));
	}

	/**
	 * Widget's run method
	 */
	public function run()
	{
		$this->displaySummary();
	}

	/**
	 * Process the row data value
	 * @param $value
	 * @return mixed
	 */
	abstract public function processValue($value);

	/**
	 * Displays the resulting summary
	 * @return mixed
	 */
	abstract public function displaySummary();

}