<?php
/**
 *
 * WhCountOfTypeOperation class
 *
 * Renders a summary based on the count of specified types. For example, if a value has a type 'blue', this class will
 * count the number of times the value 'blue' has on that column.
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.grid.operations
 * @uses YiiWheels.widgets.grid.operations.WhOperation
 */
Yii::import('yiiwheels.widgets.grid.operations.WhOperation');

class WhCountOfTypeOperation extends WhOperation
{
	/**
	 * @var string $template
	 * @see parent class
	 */
	public $template = '{label}: {types}';

	/**
	 * @var string $typeTemplate holds the template of each calculated type
	 */
	public $typeTemplate = '{label}({value})';

	/**
	 * @var array $types hold the configuration of types to calculate. The configuration is set by an array which keys
	 * are the value types to count. You can set their 'label' independently.
	 *
	 * <pre>
	 *  'types' => array(
	 *      '0' => array('label' => 'zeros'),
	 *      '1' => array('label' => 'ones'),
	 *      '2' => array('label' => 'twos')
	 * </pre>
	 */
	public $types = array();


	/**
	 * Widget's initialization
	 * @throws CException
	 */
	public function init()
	{
		if (empty($this->types)) {
			throw new CException(Yii::t(
				'zii',
				'"{attribute}" attribute must be defined',
				array('{attribute}' => 'types')
			));
		}
		foreach ($this->types as $type) {
			if (!isset($type['label'])) {
				throw new CException(Yii::t('zii', 'The "label" of a type must be defined.'));
			}
		}
		parent::init();
	}

	/**
	 * (no phpDoc)
	 * @see TbOperation
	 *
	 * @param $value
	 *
	 * @return mixed|void
	 */
	public function processValue($value)
	{
		$clean = strip_tags((string) $value);

		if (array_key_exists($clean, $this->types)) {
			if (!isset($this->types[$clean]['value'])) {
				$this->types[$clean]['value'] = 0;
			}
			$this->types[$clean]['value'] += 1;
		}
	}

	/**
	 * (no phpDoc)
	 * @see TbOperation
	 * @return mixed|void
	 */
	public function displaySummary()
	{
		$typesResults = array();
		foreach ($this->types as $type) {
			if (!isset($type['value'])) {
				$type['value'] = 0;
			}

			$typesResults[] = strtr(
				$this->typeTemplate,
				array('{label}' => $type['label'], '{value}' => $type['value'])
			);
		}
		echo strtr($this->template, array('{label}' => $this->label, '{types}' => implode(' ', $typesResults)));
	}
}
