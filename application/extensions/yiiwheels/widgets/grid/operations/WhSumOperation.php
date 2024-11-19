<?php
/**
 *
 * WhSumOperation class
 *
 * Displays a total of specified column name
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.grid.operations
 * @uses YiiWheels.widgets.grid.operations.WhOperation
 */
Yii::import('yiiwheels.widgets.grid.operations.WhOperation');

class WhSumOperation extends WhOperation
{
	/**
	 * @var float $total the total sum
	 */
	protected $total;

	/**
	 * @var array $supportedTypes the supported type of values
	 */
	protected $supportedTypes = array('raw', 'text', 'ntext', 'number');


	/**
	 * Widget's initialization method
	 * @throws CException
	 */
	public function init()
	{
		parent::init();

		if (!in_array($this->column->type, $this->supportedTypes)) {
			throw new CException(Yii::t(
				'zii',
				'Unsupported column type. Supported column types are: "{types}"',
				array(
					'{types}' => implode(', ', $this->supportedTypes)
				)
			));
		}
	}

	/**
	 * Displays the summary
	 * @return mixed|void
	 */
	public function displaySummary()
	{
		echo strtr(
			$this->template,
			array(
				'{label}' => $this->label,
				'{value}' => $this->total === null ? '' : Yii::app()->format->format($this->total, $this->column->type)
			)
		);
	}

	/**
	 * Process the value to calculate
	 * @param $value
	 * @return mixed|void
	 */
	public function processValue($value)
	{
		// remove html tags as we cannot access renderDataCellContent from the column
		$clean = strip_tags((string) $value);
		$this->total += ((float)$this->extractNumber($clean));
	}

	/**
	 * Extracts the digital part of the calculated value.
	 * @param int $value
	 * @return bool
	 */
	protected function extractNumber($value)
	{
		preg_match_all('/([+-]?[0-9]+[,\.]?)+/', $value, $matches);
		return !empty($matches[0]) && @$matches[0][0] ? $matches[0][0] : 0;
	}
}