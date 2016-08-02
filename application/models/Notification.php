<?php

/**
 * This is the model class for table "{{notifications}}".
 *
 * The followings are the available columns in table '{{notifications}}':
 * @property integer $id
 * @property string $entity survey or user
 * @property string $entity_id survey id or user id
 * @property string $title
 * @property string $message
 * @property string $type success, warning, danger
 * @property string $status new, read
 * @property DateTime $created When the notification was created
 * @property DateTime $read When the notification was read
 */
class Notification extends LSActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{notifications}}';
	}

    public function primaryKey() {
        return 'id';
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('entity_id', 'numerical', 'integerOnly'=>true),
			array('entity', 'length', 'max'=>64),
			array('type, status', 'length', 'max'=>63),
			array('title', 'length', 'max'=>255),
			array('message, created, read', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, entity, entity_id, message, type, created, read, status, title', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'entity' => 'Entity',
			'entity_id' => 'Entity',
			'message' => 'Message',
			'type' => 'Type',
			'created' => 'Created',
			'read' => 'Read',
			'status' => 'Status',
			'title' => 'Title',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('entity',$this->entity,true);
		$criteria->compare('entity_id',$this->entity_id);
		$criteria->compare('message',$this->message,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('created',$this->created,true);
		$criteria->compare('read',$this->read,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('title',$this->title,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /**
     * Returns a URL to the Ajax action in notification controller
     * Used when user clicks on a notification link
     * @return string
     */
    public function getAjaxUrl()
    {
        return Yii::app()->createUrl(
            'notification/getNotificationAsJSON',
            array(
                'notId' => $this->id
            )
        );
    }

    /**
     * Url that is used to mark that user read the message
     * @return string
     */
    public function getReadUrl()
    {
        return Yii::app()->createUrl(
            'notification/notificationRead',
            array(
                'notId' => $this->id
            )
        );
    }

    /**
     * Url to fetch the complete notification menu widget
     * @param int|null $surveyId
     * @return string
     */
    public function getUpdateUrl($surveyId = null) {
        return Yii::app()->createUrl(
            'notification/getMenuWidget',
            array(
                'surveyId' => $surveyId
            )
        );
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Notifications the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * Get latest notifications
     *
     * @param int|null $surveyId
     * @return Notification[]
     */
    public static function getNotifications($surveyId)
    {
        $criteria = new CDbCriteria();

        // Only fetch survey specific notifications if user is viewing a survey
        if ($surveyId !== null)
        {
            $criteria->addCondition('entity = \'survey\'');
            $criteria->addCondition('entity_id = ' . $surveyId);  // TODO: Escape survey id
        }

        // User notifications
        $criteria2 = new CDbCriteria();
        $criteria2->addCondition('entity = \'user\'');
        $criteria2->addCondition('entity_id = ' . Yii::app()->user->id);  // TODO: Escape

        // Only get new notifications
        $criteria3 = new CDbCriteria();
        $criteria3->addCondition('status = \'new\'');  // TODO: read = null

        $criteria->mergeWith($criteria2, 'OR');
        $criteria->mergeWith($criteria3, 'AND');
        $criteria->mergeWith(array(
            'order' => 'id DESC',
            'limit' => 10
        ));

        $nots = self::model()->findAll($criteria);

        return $nots;
    }

    /**
     * 
     */
    public static function addNotification(Notification $not) {
    }
}
