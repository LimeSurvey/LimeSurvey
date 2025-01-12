<?php

/**
 * This is the model class for table "{{notifications}}".
 *
 * The following are the available columns in table '{{notifications}}':
 * @property integer $id
 * @property string $entity survey or user
 * @property string $entity_id survey ID or user id
 * @property string $title
 * @property string $message
 * @property string $status new, read
 * @property integer $importance 1 or 3. 3 will show popup on page load, 2 is reserved for future bell animation.
 * @property string $display_class warning, danger, success
 * @property string $hash
 * @property string $created When the notification was created
 * @property string $first_read When the notification was read
 */
class Notification extends LSActiveRecord
{
    const NORMAL_IMPORTANCE   = 1; // Just notification in admin menu
    const NAG_ONCE_IMPORTANCE = 2; // Like 3 but always only shown once.
    const HIGH_IMPORTANCE     = 3; // Popup on page load

    /**
     * See example usage at manual page: https://www.gitit-tech.com/manual/Notifications#Examples
     * @param array<string, mixed>|string|null $options If string then scenario
     */
    public function __construct($options = null)
    {
        // Don't do anything if this is called from self::model()
        if (is_string($options) || is_null($options)) {
            parent::__construct($options); // $options = scenario in this case
            return;
        } else {
            // Why not Zoidberg? (\/) (°,,,°) (\/)
            parent::__construct();
        }

        $options = $this->checkShortcuts($options);

        $this->checkMandatoryFields($options, array(
            'entity',
            'entity_id',
            'title',
            'message',
        ));

        // Only allow 'survey' or 'user' as entity
        if ($options['entity'] != 'survey' && $options['entity'] != 'user') {
            throw new InvalidArgumentException('Invalid entity: ' . $options['entity']);
        }

        // Default to 'default' display class
        if (!isset($options['display_class'])) {
            $options['display_class'] = 'default';
        }

        // Default to 'log' notification importance
        if (!isset($options['importance'])) {
            $options['importance'] = self::NORMAL_IMPORTANCE;
        }

        // importance must be between 1 and 3
        if ($options['importance'] < 1 && $options['importance'] > 3) {
            throw new InvalidArgumentException('Invalid importance: ' . $options['importance']);
        }

        // Set everything up
        $this->entity = $options['entity'];
        $this->entity_id = $options['entity_id'];
        $this->title = $options['title'];
        $this->message = $options['message'];
        $this->display_class = $options['display_class'];
        $this->importance = $options['importance'];
        $this->status = 'new';
        $this->created = date('Y-m-d H:i:s', time());
        $this->first_read = null;
    }

    /**
     * Some shortcuts for easier use
     * @param array<string, mixed>
     * @return mixed
     */
    protected function checkShortcuts($options)
    {
        // Shortcuts for entity id
        if (isset($options['survey_id'])) {
            $options['entity'] = 'survey';
            $options['entity_id'] = $options['survey_id'];
        } elseif (isset($options['user_id'])) {
            $options['entity'] = 'user';
            $options['entity_id'] = $options['user_id'];
        }

        return $options;
    }

    /**
     * Check so all mandatory fields are defined when constructing
     * a new notification.
     * @param array<string, string> $options
     * @param string[] $mandatory
     * @return void
     * @throws InvalidArgumentException
     */
    protected function checkMandatoryFields(array $options, array $mandatory)
    {
        foreach ($mandatory as $mand) {
            if (!isset($options[$mand]) || $options[$mand] == '') {
                throw new InvalidArgumentException('Field ' . $mand . ' is mandatory for notification');
            }
        }
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{notifications}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'id';
    }

    /** @inheritdoc */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('entity_id', 'numerical', 'integerOnly' => true),
            array('entity', 'length', 'max' => 64),
            array('title', 'length', 'max' => 255),
            array('message, created, first_read', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, entity, entity_id, message, importance, created, first_read, status, title', 'safe', 'on' => 'search'),
        );
    }

    /** @inheritdoc */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return array(
            'id' => gT('ID'),
            'entity' => gT('Entity'),
            'entity_id' => gT('Entity'),
            'message' => gT('Message'),
            'importance' => gT('Importance'),
            'created' => gT('Created'),
            'first_read' => gT('Read'),
            'status' => gT('Status'),
            'title' => gT('Title'),
        );
    }

    /** @inheritdoc */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria = new CDbCriteria();

        $criteria->compare('id', $this->id);
        $criteria->compare('entity', $this->entity, true);
        $criteria->compare('entity_id', $this->entity_id);
        $criteria->compare('message', $this->message, true);
        $criteria->compare('importance', $this->importance);
        $criteria->compare('created', $this->created, true);
        $criteria->compare('first_read', $this->first_read);
        $criteria->compare('status', $this->status, true);
        $criteria->compare('title', $this->title, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
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
            'admin/notification',
            array(
                'sa' => 'getNotificationAsJSON',
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
            'admin/notification',
            array(
                'sa' => 'notificationRead',
                'notId' => $this->id
            )
        );
    }

    /**
     * Mark notification as read NOW()
     * @return boolean Result of update
     */
    public function markAsRead()
    {
        $this->first_read = date('Y-m-d H:i:s', time());
        $this->status = 'read';
        $result = $this->update();
        return $result;
    }

    /**
     * Url to fetch the complete notification menu widget
     * @param int|null $surveyId
     * @return string
     */
    public static function getUpdateUrl($surveyId = null)
    {
        $params = array(
            'sa' => 'actionGetMenuWidget',
        );
        if ($surveyId) {
            $params['surveyId'] = $surveyId;
        }
        return Yii::app()->createUrl(
            'admin/notification',
            $params
        );
    }

    /**
     * @inheritdoc
     * @return Notification the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /**
     * Get latest notifications to show in the menu
     * @param int|null $surveyId
     * @return Notification[]
     */
    public static function getNotifications($surveyId)
    {
        // TODO : via survey relations
        $criteria = self::getCriteria($surveyId);
        $nots = self::model()->findAll($criteria);
        return $nots;
    }

    /**
     * Get notifications of importance HIGH_IMPORTANCE
     * @param int|null $surveyId
     * @return Notification[]
     */
    public static function getImportantNotifications($surveyId)
    {
        // TODO this should be in Survey model (relations?)
        $criteria = self::getCriteria($surveyId);
        $criteria2 = new CDbCriteria();
        $criteria2->addCondition('importance = ' . self::HIGH_IMPORTANCE);
        $criteria->mergeWith($criteria2, 'AND');

        return self::model()->findAll($criteria);
    }

    /**
     * Count how many notifications we have
     * @param int|null $surveyId
     * @return string
     */
    public static function countNotifications($surveyId)
    {
        // TODO this should be in Survey model
        $criteria = self::getCriteria($surveyId);
        $nr = self::model()->count($criteria);
        return $nr;
    }

    /**
     * Returns number of notifications with status 'new'
     * @param int|null $surveyId
     * @return string
     */
    public static function countNewNotifications($surveyId)
    {
        $criteria = self::getCriteria($surveyId);

        $criteria2 = new CDbCriteria();
        $criteria2->addCondition('status = \'new\''); // TODO: Check first_read = null instead?
        $criteria->mergeWith($criteria2, 'AND');

        $nr = self::model()->count($criteria);
        return $nr;
    }

    /**
     * Count important notifications
     * @param int|null $surveyId
     * @return string
     */
    public static function countImportantNotifications($surveyId)
    {
        $criteria = self::getCriteria($surveyId);
        $criteria2 = new CDbCriteria();
        $criteria2->addCondition('importance = ' . self::HIGH_IMPORTANCE . ' OR ' . 'importance = ' . self::NAG_ONCE_IMPORTANCE);
        $criteria->mergeWith($criteria2, 'AND');

        return self::model()->count($criteria);
    }

    /**
     * Criteria to fetch all notifications for this survey and this user
     * @param int|null $surveyId
     * @return CDbCriteria
     */
    protected static function getCriteria($surveyId = null)
    {
        $criteria = new CDbCriteria();
        $params = array();
        // Only fetch survey specific notifications if user is viewing a survey
        if (!empty($surveyId)) {
            $criteria->addCondition('entity =:sentity AND entity_id=:sentity_id');
            $params[':sentity'] = 'survey';
            $params[':sentity_id'] = $surveyId;
        }
        // User notifications
        $criteria->addCondition('entity =:uentity AND entity_id=:uentity_id', 'OR');
        $params[':uentity'] = 'user';
        $params[':uentity_id'] = Yii::app()->user->id;

        // Only get new notifications
        //$criteria3 = new CDbCriteria();
        //$criteria3->addCondition('status = \'new\'');  // TODO: read = null?
        //$criteria->mergeWith($criteria3, 'AND');

        $criteria->params = $params;
        $criteria->order = 'id DESC';
        $criteria->limit = 50;

        return $criteria;
    }

    /**
     * Broadcast a message to all users
     * See example usage at manual page: https://www.gitit-tech.com/manual/Notifications#Examples
     * @param array $options
     * @param array $users
     */
    public static function broadcast(array $options, array $users = null)
    {
        // Get all users if no $users were given
        if ($users === null) {
            $users = User::model()->findAll();
        }

        foreach ($users as $user) {
            $options['user_id'] = $user->uid;
            $not = new Notification($options);
            $not->save();
        }
    }
}
