<?php

/**
 * Controller model for TFA users secret keys
 *
 * @property integer uid
 * @property integer secretKey
 * @property string authType
 * @property integer firstLogin
 * @property string forceNewFirstLogin
 */

class TFAUserKey extends LSActiveRecord
{
    /**
     * Descriptions for the possible auth type options
     *
     * @var array
     */
    public static $authTypeOptions = [
        'google' => 'Google Authenticator',
        'authy' => 'Authy',
        'yubi' => 'YubiKey',
        'auplus' => 'Authenticator Plus',
        'duo' => 'Duo',
        'hde' => 'HDE OTP',
        'other' => 'Other solution'
    ];

    /** @inheritdoc */
    public function tableName()
    {
        return '{{twoFactorUsers}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'uid';
    }

    /** @inheritdoc */
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
     * @inheritdoc
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /** @inheritdoc */
    public function relations()
    {
        return array(
            'user'  => array(self::BELONGS_TO, 'TFAUser', array('uid')),
        );
    }

    /** @inheritdoc */
    public function getButtons()
    {
        $buttons = "<div class='icon-btn-row'>";
        $buttons .= '<button class="btn btn-icon btn-sm"><i class="ri-refresh-line"></i></button>'
            . '<button class="btn btn-icon btn-sm"><i class="ri-file-edit-line"></i></button>'
            . '<button class="btn btn-icon btn-sm"><i class="ri-delete-bin-fill text-danger"></i></button>';
        $buttons .= "</div>";
        return $buttons;
    }
    /**
     * Get the description for the current auth type
     *
     * @return string
     */
    public function getAuthTypeDescription()
    {
        return self::$authTypeOptions[$this->authType];
    }

    /** @inheritdoc */
    public function getColums()
    {
        // TODO should be static
        $cols = array(
            array(
                "name" => 'buttons',
                "type" => 'raw',
                "filter" => false,
                "header" => gT("Action")
            ),
            array(
                "name" => 'user.users_name',
                "header" => gT("Username"),
                "filter" => TbHtml::textField('user_users_name', Yii::app()->request->getParam('user_users_name')),
            ),
            array(
                "name" => 'user.full_name',
                "header" => gT("Full name"),
                "filter" => TbHtml::textField('user_full_name', Yii::app()->request->getParam('user_full_name')),
            ),
            array(
                "name" => 'user.email',
                "header" => gT("Email"),
                "filter" => TbHtml::textField('user_email', Yii::app()->request->getParam('user_email')),
            ),
            array(
                "name" => 'authType',
                "header" => gT("2FA method")
            ),
        );
        return $cols;
    }

     /** @inheritdoc */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.
        $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
        $criteria = new CDbCriteria();
        $criteria->with = 'user';
        $criteria->compare('user_users_name', Yii::app()->request->getParam('user_users_name'));
        $criteria->compare('user_full_name', Yii::app()->request->getParam('user_full_name'));
        $criteria->compare('user_email', Yii::app()->request->getParam('user_email'));
        $oDataProvider =  new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'sort' => [
                'attributes' => [
                   'user.users_name' => [
                       'asc' => 'user.users_name',
                       'desc' => 'user.users_name desc',
                   ],
                   'user.full_name' => [
                       'asc' => 'user.full_name asc',
                       'desc' => 'user.full_name desc',
                   ],
                   'user.email' => [
                       'asc' => 'user.email asc',
                       'desc' => 'user.email desc',
                   ],
                   'authType' => [
                       'asc' => 'authType asc',
                       'desc' => 'authType desc',
                   ],
                ]
               ],
            'pagination' => array(
                'pageSize' => $pageSize
            )
        ));
        return $oDataProvider;
    }
}
