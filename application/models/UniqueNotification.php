<?php

/**
* Subclass of Notification, but with unique constraint.
* If a new message is created exactly like another one,
* it will be marked as unread.
 */

class UniqueNotification extends Notification
{
    /**
     * Check for already existing notification and
     * update that. Importance will be set to normal.
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
            $duplicate->status = 'new';
            $duplicate->importance = self::NORMAL_IMPORTANCE;
            $duplicate->update();
        }

    }
}
