<?php

/**
 * Abstracted user model for TFA admin view.
 * Incorporating an alternative seach method.
 *
 * @inheritDoc
 */

class TFAUser extends User
{
    public function attributeLabels()
    {
        return [
            'authType' => gT('Two-factor authentication method'),
            'secretKey' => gT('Secret base key'),
            'uid' => gT('User ID'),
            'firstLogin' => gT('Logged in with 2FA'),
            'forceNewFirstLogin' => gT('Force to enable 2FA')
        ];
    }

    /**
    * @inheritDoc
    */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /** @inheritDoc */
    public function relations()
    {
        return array(
            'userkeys'  => array(self::HAS_ONE, 'TFAUserKey', array('uid')),
        );
    }

    /** @inheritDoc */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['users_name', 'safe', 'on'=>'search'];
        $rules[] = ['email', 'safe', 'on'=>'search'];
        $rules[] = ['full_name', 'safe', 'on'=>'search'];
        return $rules;
    }

    /**
     * Returns the action columsn buttons
     *
     * @return string
     */
    public function getButtons()
    {
        $hasAuthSet = $this->getHasAuthSet();
        $dropdownItems = [];
        $dropdownItems[] = [
            'title'            => gT('Delete 2FA key'),
            'linkClass'        => 'TFA--management--action-deleteToken',
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'enabledCondition' => $hasAuthSet,
            'linkAttributes'   => [
                'data-bs-toggle'   => "modal",
                'data-confirmtext' => gT("Are you sure you want to delete this 2FA key?"),
                'data-buttons'     => json_encode (['confirm_cancel' => gT("Cancel"), 'confirm_ok' => gT('Delete')]),
                'data-href'        => App()->createUrl("plugins/direct/plugin/TwoFactorAdminLogin/function/directCallDeleteKey"),
                'data-uid'         => $this->uid,
                'data-errortext'   => gT('An error has happened, and the key could not be deleted.'),
            ]
        ];

        return App()->getController()->widget('ext.admin.grid.GridActionsWidget.GridActionsWidget', ['dropdownItems' => $dropdownItems], true);
    }

    /**
     * Return the related descriptive UserKey value for this auth type
     *
     * @return string
     */
    public function getAuthTypeDescription()
    {
        if ($this->hasAuthSet) {
            return TFAUserKey::$authTypeOptions[$this->userkeys->authType];
        }
        return '';
    }

    /**
     * Returns a check value of if the user has a bound 2FA secret key
     *
     * @return bool
     */
    public function getHasAuthSet()
    {
        return $this->userkeys != null;
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function getColumns()
    {
        $cols = [
            [
                "name"   => 'users_name',
                "header" => gT("Username"),
            ],
            [
                "name"   => 'full_name',
                "header" => gT("Full name"),
            ],
            [
                "name"   => 'email',
                "header" => gT("Email"),
            ],
            [
                "name"   => 'hasAuthSet',
                "header" => gT("2FA enabled"),
                "value"  => '$data->hasAuthSet==1?gT("Yes"):gT("No")',
                "filter" => TbHtml::dropDownList('userkeys_secretKey', Yii::app()->request->getParam('userkeys_secretKey'), [
                    ''  => '',
                    '1' => gT('Yes'),
                    '0' => gT('No'),
                ]),
            ],
            [
                "name"   => 'userkeys.authType',
                "header" => gT("2FA method"),
                "value"  => '$data->hasAuthSet==1 ? TFAUserKey::$authTypeOptions[$data->userkeys->authType] : ""',
                "filter" => TbHtml::dropDownList('userkeys_authType', Yii::app()->request->getParam('userkeys_authType'), array_merge(['' => ''], TFAUserKey::$authTypeOptions)),
            ],
            [
                "name"              => 'buttons',
                "type"              => 'raw',
                "filter"            => false,
                "header"            => gT("Action"),
                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                'filterHtmlOptions' => ['class' => 'ls-sticky-column'],
                'htmlOptions'       => ['class' => 'ls-sticky-column']
            ],
        ];
        return $cols;
    }

    /** @inheritDoc */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.
        $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);

        $criteria = new CDbCriteria;
        $criteria->with = 'userkeys';
        $criteria->compare('users_name', $this->users_name);
        $criteria->compare('full_name', $this->full_name);
        $criteria->compare('email', $this->email);
        $criteria->compare('authType', Yii::app()->request->getParam('userkeys_authType'));
        $paramHasAuthSet = Yii::app()->request->getParam('userkeys_secretKey', '');
        if ($paramHasAuthSet === '1') {
            $criteria->addCondition('secretKey IS NOT NULL');
        }
        if ($paramHasAuthSet === '0') {
            $criteria->addCondition('secretKey IS NULL');
        }
         
        $oDataProvider =  new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
            'pagination' => [
                'pageSize' => $pageSize
            ],
            'sort' => [
                 'attributes' => [
                    'users_name' => [
                        'asc' => 'users_name',
                        'desc' => 'users_name desc',
                    ],
                    'full_name' => [
                        'asc' => 'full_name asc',
                        'desc' => 'full_name desc',
                    ],
                    'email' => [
                        'asc' => 'email asc',
                        'desc' => 'email desc',
                    ],
                    'userkeys.authType' => [
                        'asc' => 'userkeys.authType asc',
                        'desc' => 'userkeys.authType desc',
                    ],
                    'hasAuthSet' => [
                        'asc' => 'userkeys.secretKey asc',
                        'desc' => 'userkeys.secretKey desc',
                    ],
                 ]
            ],
         ));
        return $oDataProvider;
    }
}
