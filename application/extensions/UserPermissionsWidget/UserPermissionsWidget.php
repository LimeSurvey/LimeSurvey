<?php

class UserPermissionsWidget extends CWidget
{
    /** @var array[] */
    public $aPermissions;

    /**
     * @todo Classes instead of switch.
     */
    public function run()
    {
        if(empty($this->aPermissions)) {
            return;
        }
        $this->render('table', ['aPermissions' => $this->aPermissions]);
    }
}
