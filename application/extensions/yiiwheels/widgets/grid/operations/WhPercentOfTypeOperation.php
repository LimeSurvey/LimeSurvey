<?php
/**
 *
 * WhPercentOfTypeOperation class
 *
 * Renders a summary based on the percent count of specified types. For example, if a value has a type 'blue', this class will
 * count the percentage number of times the value 'blue' has on that column.
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.grid.operations
 * @uses YiiWheels.widgets.grid.operations.WhCountOfTypeOperation
 */
Yii::import('yiiwheels.widgets.grid.operations.WhCountOfTypeOperation');

class WhPercentOfTypeOperation extends WhCountOfTypeOperation
{
	/**
	 * @var string $typeTemplate
	 * @see TbCountOfTypeOperation
	 */
	public $typeTemplate = '{label}({value}%)';

	/**
	 * @var integer $_total holds the total sum of the values. Required to get the percentage.
	 */
	protected $_total;


	/**
	 * @return mixed|void
	 * @see TbOperation
	 */
	public function displaySummary()
	{
		$typesResults = array();

		foreach ($this->types as $type) {
			if (!isset($type['value'])) {
				$type['value'] = 0;
			}

			$type['value'] = $this->getTotal() ? number_format((float)($type['value'] / $this->getTotal()) * 100, 1)
				: 0;
			$typesResults[] = strtr(
				$this->typeTemplate,
				array('{label}' => $type['label'], '{value}' => $type['value'])
			);
		}

		echo strtr($this->template, array('{label}' => $this->label, '{types}' => implode(' ', $typesResults)));
	}

	/**
	 * Returns the total of types
	 * @return int holds
	 */
	protected function getTotal()
	{
		if (null == $this->_total) {
			$this->_total = 0;
			foreach ($this->types as $type) {
				if (isset($type['value'])) {
					$this->_total += $type['value'];
				}
			}
		}
		return $this->_total;
	}
}
