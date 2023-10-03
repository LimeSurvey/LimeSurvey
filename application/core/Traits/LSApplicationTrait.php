<?php

/**
 * Trait for ConsoleApplication and LSYii_Application
 *
 * @version 0.1.0
 */

trait LSApplicationTrait
{
    /**
     * get the current id of connected user,
     * check if user exist before return for security
     * @return int|null user id
     */
    public function getCurrentUserId()
    {
        if(empty(App()->session['loginID'])) {
            // NULL for guest
            return App()->session['loginID'];
        }
        if (!is_null($this->currentUserId) && $this->currentUserId == App()->session['loginID']) {
            return $this->currentUserId;
        }
        $this->currentUserId = App()->session['loginID'];
        if ($this->currentUserId && !User::model()->findByPk($this->currentUserId)) {
            $this->currentUserId = 0;
        }
        return $this->currentUserId;
    }
}
