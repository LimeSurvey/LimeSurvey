<?php

/**
* Subclass of Notification, but with unique constraint.
* If a new message is created exactly like another one,
* it will be marked as unread.
 */
class UniqueNotification extends Notification
{

    /**
     * Whether or not this message should be marked as unread ('new')
     * second time it's saved.
     * @var boolean $markAsNew
     */
    protected $markAsNew = true;

    /**
     * Whether or not the importance should be set to normal
     * second time it's saved.
     * @var boolean $setNormalImportance
     */
    protected $setNormalImportance = true;

    /**
     * As parent constructor but with markAsUndread
     * @inheritdoc
     * @return UniqueNotification
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        if (isset($options['markAsNew'])) {
            $this->markAsNew = $options['markAsNew'];
        }

        if (isset($options['setNormalImportance'])) {
            $this->setNormalImportance = $options['setNormalImportance'];
        }
    }

    /**
     * Check for already existing notification and
     * update that. Importance will be set to normal.
     * @inheritdoc
     * @return void
     */
    public function save($runValidation = true, $attributes = null)
    {
        $toHash = $this->entity.$this->entity_id.$this->title.$this->message;
        $this->hash = hash('sha256', $toHash);

        $duplicate = self::model()->findByAttributes(array(
            'hash' => $this->hash
        ));

        if (empty($duplicate)) {
            parent::save($runValidation, $attributes);
        } else {

            if ($this->markAsNew) {
                $duplicate->status = 'new';
            }

            if ($this->setNormalImportance) {
                $duplicate->importance = self::NORMAL_IMPORTANCE;
            }

            $duplicate->update();
        }

    }

    /** @inheritdoc */
    public static function broadcast(array $options, array $users = null)
    {
        // Get all users if no $users were given
        if ($users === null) {
            $users = User::model()->findAll();
        }

        foreach ($users as $user) {
            $options['user_id'] = $user->uid;
            $not = new UniqueNotification($options);
            $not->save();
        }
    }
}
