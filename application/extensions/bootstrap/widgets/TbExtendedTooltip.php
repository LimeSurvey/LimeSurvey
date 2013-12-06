<?php
/**
 * TbExtendedTooltip class
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * Date: 10/18/12
 * Time: 5:53 PM
 */
class TbExtendedTooltip extends CWidget
{
	/**
	 * @var string the name of the table for keeping applied migration information.
	 * This table will be automatically created if not exists. Defaults to 'tbl_tooltip'.
	 * The table structure is: (key varchar(255) primary key, tooltip varchar(255))
	 */
	public $tooltipTable = 'tbl_tooltip';

	/**
	 * @var string the application component ID that specifies the database connection for
	 * storing tooltip information. Defaults to 'db'.
	 */
	public $connectionID = 'db';

	/**
	 * @var string the tooltip
	 */
	public $key;

	/**
	 * @var string the text to display on the tooltip if no value has been found
	 */
	public $emptyTooltipText = 'empty';

	/**
	 * @var bool whether the tooltip should be editable or not
	 */
	public $editable = true;

	/**
	 * @var string the type of editable form. Possible values
	 */
	public $editableType = 'textarea';

	/**
	 * @var string the popup placement of the tooltip editor. Possible values: right | left | top | bottom.
	 */
	public $editablePopupPlacement = 'right';

	/**
	 * @var string the url to call
	 */
	public $url;

	/**
	 * @var CDbConnection
	 */
	private $_db;

	/**
	 * Widget's initialization
	 * @throws CException
	 */
	public function init()
	{
		if ($this->key === null)
			throw new CException(Yii::t('zii', '"{attribute}" cannnot be null', array('{attribute}' => 'key')));
		if ($this->url === null && $this->editable)
			throw new CException(Yii::t('zii', '"url" cannot be null if tooltip is required to be edited'));
	}

	/**
	 * Widget's run
	 */
	public function run()
	{
		$this->renderContent();
		$this->registerClientScript();
	}

	/**
	 * Renders the HTML tag element that renders
	 */
	protected function renderContent()
	{
		echo CHtml::openTag('span', array('rel' => 'editable-tooltip', 'title' => $this->getTooltip($this->key), 'name' => $this->key));
		if (!$this->editable)
		{
			// the bootstrap-editable-tooltip plugin, renders the icon automatically
			echo '<i class="icon-info-sign"></i>';
		}
		echo '</span>';
	}

	/**
	 * Registers the
	 */
	protected function registerClientScript()
	{
		// register common javascript
		// any AJAX updated content with editable tooltips should be handled by the coder
		// coding a call

		// if not editable, just render the tooltip
		if (!$this->editable)
		{
			// not editable, just make the tooltip
			$js = "$('span[name=\"{$this->key}\"]').tooltip();";
		} else
		{
			// editable, make use of bootstrap-editable-tooltip plugin
			Yii::app()->bootstrap->registerAssetCss('bootstrap-editable-tooltip.css');
			Yii::app()->bootstrap->registerAssetJs('bootstrap-editable-tooltip.js');
			$options = CJavaScript::encode(array(
				'send' => 'always',
				'url' => $this->url,
				'placement' => $this->editablePopupPlacement
			));

			$js = "$('span[name=\"{$this->key}\"]').editableTooltip($options);";
		}
		Yii::app()->clientScript->registerScript(__CLASS__.'#'.$this->getId(), $js);
	}

	/**
	 * Returns the tooltip stored at the database.
	 * @param $key
	 * @return mixed|string emptyTool
	 */
	protected function getTooltip($key)
	{
		$db = $this->getDbConnection();
		if ($db->schema->getTable($this->tooltipTable) === null)
		{
			$this->createTooltipsTable();
			return $this->emptyTooltipText;
		}
		$tip = $db->createCommand()
			->select('tooltip')
			->from($this->tooltipTable)
			->where('tooltip_key=:key', array(':key' => $key))
			->queryScalar();

		return !$tip ? $this->emptyTooltipText : $tip;
	}

	/**
	 * Returns the currently active database connection.
	 * By default, the 'db' application component will be returned and activated.
	 * You can call {@link setDbConnection} to switch to a different database connection.
	 * Methods such as {@link insert}, {@link createTable} will use this database connection
	 * to perform DB queries.
	 * @return CDbConnection the currently active database connection
	 */
	protected function getDbConnection()
	{
		if ($this->_db === null)
		{
			$this->_db = Yii::app()->getComponent($this->connectionID);
			if (!$this->_db instanceof CDbConnection)
				throw new CException(Yii::t('zii', 'The "db" application component must be configured to be a CDbConnection object.'));
		}
		return $this->_db;
	}

	/**
	 * Creates the database table to store all edited tooltips
	 */
	protected function createTooltipsTable()
	{
		$db = $this->getDbConnection();

		$db->createCommand()->createTable($this->tooltipTable, array(
			'tooltip_key' => 'string NOT NULL PRIMARY KEY',
			'tooltip' => 'string',
		));
	}
}