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
     * @var boolean
     */
    protected $markAsNew = true;

    /**
     * Wheather or not the importance should be set to normal
     * second time it's saved.
     * @var boolean
     */
    protected $setNormalImportance = true;

    /**
     * As parent constructor but with markAsUndread
     * @return UniqueNotification
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        if (isset($options['markAsNew']))
        {
            $this->markAsNew = $options['markAsNew'];
        }

        if (isset($options['setNormalImportance']))
        {
            $this->setNormalImportance = $options['setNormalImportance'];
        }
    }

    /**
     * Check for already existing notification and
     * update that. Importance will be set to normal.
     * @param boolean $runValidation Yii
     * @param ? $attributes Yii
     * @return void
     */
    public function save($runValidation = true, $attributes = null)
    {
        $toHash = $this->entity . $this->entity_id . $this->title . $this->message;
        $this->hash = hash('sha256', $toHash);

        $duplicate = self::model()->findByAttributes(array(
            'hash' => $this->hash
        ));

        if (empty($duplicate))
        {
            parent::save($runValidation, $attributes);
        }
        else
        {

            if ($this->markAsNew)
            {
                $duplicate->status = 'new';
            }

            if ($this->setNormalImportance)
            {
                $duplicate->importance = self::NORMAL_IMPORTANCE;
            }

            $duplicate->update();
        }

    }
}
