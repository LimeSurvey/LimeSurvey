<?php

class RemoteControlHandler extends remotecontrol_handle 
{
    /**
    * RPC Routine to get information on user
    *
    * @access public
    * @param string $sSessionKey Auth credentials
    * @return array The information on user (except password)
    */
    public function get_me($sSessionKey)
    {
        if ($this->_checkSessionKey($sSessionKey))
        {
            $oUser=User::model()->find("uid=:uid",array(":uid"=>Yii::app()->session['loginID']));
            if($oUser) // We have surely one, else no sessionkey ....
            {
                $aReturn=$oUser->attributes;
                unset($aReturn['password']);
                return $aReturn;
            }
        }
    }
}
