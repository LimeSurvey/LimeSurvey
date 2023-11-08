<?php

/**
 * Trait for ConsoleApplication and LSYii_Application
 *
 * @version 0.1.0
 */

trait LSApplicationTrait
{

    /* @var integer| null the current userId for all action */
    private $currentUserId;
    /**
     * get the current id of connected user,
     * check if user exist before return for security
     * @return int|null user id
     */
    public function getCurrentUserId()
    {
        if(empty(App()->session['loginID'])) {
            /**
             * NULL for guest,
             * null by default for CConsoleapplication, but Permission always return true for console
             * Test can update only App()->session['loginID'] to set the user
             */
            return App()->session['loginID'];
        }
        if (!is_null($this->currentUserId) && $this->currentUserId == App()->session['loginID']) {
            return $this->currentUserId;
        }
        /* use App()->session and not App()->user fot easiest unit test */
        $this->currentUserId = App()->session['loginID'];
        if ($this->currentUserId && !User::model()->findByPk($this->currentUserId)) {
            $this->currentUserId = 0;
        }
        return $this->currentUserId;
    }
}
