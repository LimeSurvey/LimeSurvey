<?php

//namespace ReactEditor\editorEnableService;

use SettingsUser;

class EditorEnableService
{
    const STG_NAME_REACT_EDITOR = "editorEnabled";
    private int $optIn;

    /**
     * @param int $optIn  1, if the user wants to enable the react editor, 0 otherwise
     */
        public function __construct($optIn) {
            $this->optIn = $optIn;
        }

    /**
     * @return bool
     */
        public function activateDeactivateEditor(): bool {
            $success = false;
            if($this->optIn === 1 || $this->optIn === 0) {
                $userId =App()->user->id;
                $userSetting = SettingsUser::model()->findByAttributes(
                    [
                        'uid' => $userId,
                        "stg_name" => self::STG_NAME_REACT_EDITOR
                    ]
                );
                if ($userSetting === null) {
                    //default value from config was used, create a new entry for the user
                    $userSetting = new SettingsUser();
                    $userSetting->uid = $userId;
                    $userSetting->stg_name = self::STG_NAME_REACT_EDITOR;
                    $userSetting->stg_value = $this->optIn;
                } else {
                    //here we can simply update the value
                    $userSetting->stg_value = $this->optIn;
                }
                $success = $userSetting->save();
            }

            return $success;
        }
}
