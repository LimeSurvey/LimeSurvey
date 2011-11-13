<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Survey extends CActiveRecord
{
	/**
	 * Returns the table's name
	 *
	 * @access public
	 * @return string
	 */
	public function tableName()
	{
		return '{{surveys}}';
	}

	/**
	 * Returns the table's primary key
	 *
	 * @access public
	 * @return string
	 */
	public function primaryKey()
	{
		return 'sid';
	}

	/**
	 * Return the static model for this table
	 *
	 * @static
	 * @access public
	 * @return CActiveRecord
	 */
	public static function model()
	{
		return parent::model(__CLASS__);
	}

	/**
	 * Returns this model's relations
	 *
	 * @access public
	 * @return array
	 */
	public function relations()
	{
		return array(
			'languagesettings' => array(self::HAS_MANY, 'Surveys_languagesettings', '',
				'on' => 't.sid = languagesettings.surveyls_survey_id AND t.language = languagesettings.surveyls_language'),
			'owner' => array(self::BELONGS_TO, 'User', '', 'on' => 't.owner_id = owner.uid'),
		);
	}

	/**
	 * Returns this model's scopes
	 *
	 * @access public
	 * @return array
	 */
	public function scopes()
	{
		return array(
			'active' => array(
				'condition' => 'active = "Y"',
			),
		);
	}

	/**
	 * permission scope for this model
	 *
	 * @access public
	 * @param int $loginID
	 * @return CActiveRecord
	 */
	public function permission($loginID)
	{
		$loginID = (int) $loginID;
		$criteria = $this->getDBCriteria();
		$criteria->mergeWith(array(
			'condition' => 'sid IN (SELECT sid FROM {{survey_permissions}} WHERE uid = :uid AND permission = :permission AND read_p = 1)',
		));
		$criteria->params[':uid'] = $loginID;
		$criteria->params[':permission'] = 'survey';

		return $this;
	}
}