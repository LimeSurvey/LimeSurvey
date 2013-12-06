<?php
/**
 * TbExtendedTooltipAction.php
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * Date: 10/18/12
 * Time: 6:23 PM
 */
class TbExtendedTooltipAction extends CAction
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
	 * @var CDbConnection
	 */
	protected $_db;

	/**
	 * CAction run's method
	 */
	public function run()
	{
		$key = yii::app()->request->getParam('name');
		$tooltip = Yii::app()->request->getParam('value');
		if(!$key || !$tooltip)
			throw new CHttpException(404, Yii::t('zii', 'Unauthorized request') );

		if(!$this->getDbConnection()
			->createCommand()
			->update($this->tooltipTable, array('tooltip'=>$tooltip),'tooltip_key=:key', array(':key'=>$key)))
		{
			$this->getDbConnection()
				->createCommand()
				->insert($this->tooltipTable, array('tooltip_key'=>$key, 'tooltip'=>$tooltip));
		}
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
			$this->_db = Yii::app()->getComponent('db');
			if (!$this->_db instanceof CDbConnection)
				throw new CException(Yii::t('zii', 'The "db" application component must be configured to be a CDbConnection object.'));
		}
		return $this->_db;
	}
}