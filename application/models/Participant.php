<?php

/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

use LimeSurvey\Exceptions\CPDBException;

/**
 * This is the model class for table "{{participants}}".
 *
 * The following are the available columns in table '{{participants}}':
 * @property string $participant_id Primary Key
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property string $language
 * @property string $blacklisted
 * @property integer $owner_uid
 * @property integer $created_by
 * @property string $created Date-time of creation
 * @property string $modified Date-time of modification
 *
 * @property User $owner
 * @property SurveyLink[] $surveylinks
 * @property ParticipantAttribute[] $participantAttributes
 * @property ParticipantShare[] $shares
 *
 * @property string $buttons
 * @property string $checkbox
 * @property array $allExtraAttributes
 * @property integer|string $countActiveSurveys
 * @property string $blacklistSwitchButton
 * @property array $columns
 * @property string $ownersList
 */
class Participant extends LSActiveRecord
{
    public $extraCondition;
    public $countActiveSurveys;
    public $id;

    /** @var int $sid */
    protected static $sid = 0;

    /**
     * @inheritdoc
     * @return Participant
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{participants}}';
    }

    /** @inheritdoc */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('participant_id, blacklisted, owner_uid', 'required'),
            array('owner_uid', 'numerical', 'integerOnly' => true),
            array('participant_id', 'length', 'max' => 50),
            array('firstname, lastname', 'length', 'max' => 150),
            array('language', 'length', 'max' => 40),
            array('firstname, lastname, language', 'LSYii_Validators'),
            array('email', 'length', 'max' => 254),
            array('blacklisted', 'length', 'max' => 1),
            // Please remove those attributes that should not be searched.
            array('participant_id, firstname, lastname, email, language, countActiveSurveys, blacklisted, owner.full_name', 'safe', 'on' => 'search'),
        );
    }

    /** @inheritdoc */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'owner' => array(self::HAS_ONE, 'User', array('uid' => 'owner_uid')),
            'surveylinks' => array(self::HAS_MANY, 'SurveyLink', 'participant_id'),
            'participantAttributes' => array(self::HAS_MANY, 'ParticipantAttribute', 'participant_id'),
            'shares' => array(self::HAS_MANY, 'ParticipantShare', 'participant_id')
        );
    }

    // @todo do we need this?
    // public function getCountActiveSurveys(){

    //     $count =  count($this->surveylinks);
    //     return $count ;
    //     return ($count!==0 ? $count : '');
    // }

    /**
     * Returns buttons for grid view
     * @return string
     */
    public function getButtons()
    {
        $permission_superadmin_read = Permission::model()->hasGlobalPermission('superadmin', 'read');
        $permission_participantpanel_delete = Permission::model()->hasGlobalPermission('participantpanel', 'delete');
        $userId = App()->user->id;


        $dropdownItems = [];
        $dropdownItems[] = [
            'title'            => gT('Edit this participant'),
            'linkClass'        => 'action_participant_editModal',
            'iconClass'        => 'ri-pencil-fill',
            'enabledCondition' => $this->userHasPermissionToEdit(),
            'linkAttributes'   => [
                'data-participant-id' => $this->participant_id
            ],
        ];
        $dropdownItems[] = [
            'title'            => gT('Add participant to survey'),
            'linkClass'        => 'action_participant_addToSurvey',
            'iconClass'        => 'ri-user-add-fill',
            'enabledCondition' => $this->userHasPermissionToEdit(),
            'linkAttributes'   => [
                'data-participant-id' => $this->participant_id
            ],
        ];
        $dropdownItems[] = [
            'title'            => gT('List active surveys'),
            'linkClass'        => 'action_participant_infoModal',
            'iconClass'        => 'ri-search-line',
            'enabledCondition' => $this->userHasPermissionToEdit(),
            'linkAttributes'   => [
                'data-participant-id' => $this->participant_id
            ],
        ];
        $dropdownItems[] = [
            'title'            => gT('Share this participant'),
            'linkClass'        => 'action_participant_shareParticipant',
            'iconClass'        => 'ri-share-forward-fill',
            'enabledCondition' => $this->userHasPermissionToEdit(),
            'linkAttributes'   => [
                'data-participant-id' => $this->participant_id
            ],
        ];
        $dropdownItems[] = [
            'title'            => gT('Delete this participant'),
            'linkClass'        => 'action_participant_deleteModal',
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'enabledCondition' =>
                ($this->userHasPermissionToEdit()
                    && ($this->owner_uid == $userId
                        || $permission_superadmin_read
                        || $permission_participantpanel_delete
                    )
                )
                || $permission_participantpanel_delete,
            'linkAttributes'   => [
                'data-participant-id' => $this->participant_id
            ],
        ];

        return App()->getController()->widget('ext.admin.grid.GridActionsWidget.GridActionsWidget', ['dropdownItems' => $dropdownItems], true);
    }

    /**
     * @return string html
     */
    public function getCheckbox()
    {
        return "<input type='checkbox' class='selector_participantCheckbox' name='selectedParticipant[]' value='" . $this->id . "' >";
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $returnArray = array(
            'participant_id' => gT('Participant') . $this->setEncryptedAttributeLabel(0, 'Participant', 'participant_id'),
            'firstname' => gT('First name') . $this->setEncryptedAttributeLabel(0, 'Participant', 'firstname'),
            'lastname' => gT('Last name') . $this->setEncryptedAttributeLabel(0, 'Participant', 'lastname'),
            'email' => gT('Email address') . $this->setEncryptedAttributeLabel(0, 'Participant', 'email'),
            'language' => gT('Language') . $this->setEncryptedAttributeLabel(0, 'Participant', 'language'),
            'blacklisted' => gT('Blocklisted') . $this->setEncryptedAttributeLabel(0, 'Participant', 'blacklisted'),
            'owner_uid' => gT('Owner ID') . $this->setEncryptedAttributeLabel(0, 'Participant', 'owner_uid'),
            'surveyid' => gT('Active survey ID') . $this->setEncryptedAttributeLabel(0, 'Participant', 'surveyid'),
            'created' => gT('Created on') . $this->setEncryptedAttributeLabel(0, 'Participant', 'created')
        );
        foreach ($this->allExtraAttributes as $name => $attribute) {
            $returnArray[$name] = $attribute['defaultname'];
        }
        return $returnArray;
    }

    /**
     * @return array
     */
    public function getAllExtraAttributes()
    {
        $allAttributes = ParticipantAttributeName::model()->getAllAttributes();
        $extraAttributes = array();
        foreach ($allAttributes as $attribute) {
            $extraAttributes["ea_" . $attribute['attribute_id']] = $attribute;
        }
        return $extraAttributes;
    }

    /**
     * Get options for a drop-down attribute
     * @param string $attribute_id
     * @return array
     */
    public function getOptionsForAttribute($attribute_id)
    {

        //if ($this->attribute_type != 'DD') {
        //throw new \CInvalidArgumentException('Only drop-down attributes have options');
        //}

        //$attribute_id = $this->attribute_id;
        $result = Yii::app()->db->createCommand()
            ->select('*')
            ->from('{{participant_attribute_values}}')
            ->where('attribute_id=:attribute_id', array('attribute_id' => $attribute_id))
            ->queryAll();
        return $result;
    }

    public function getAllUsedLanguagesWithRealName()
    {
        $lang_array = array();
        $languages = $this->findAll(array(
            'select' => 't.language',
            'group' => 't.language',
            'distinct' => true,
        ));
        foreach ($languages as $language) {
            $lang_array[$language['language']] = getLanguageNameFromCode($language['language'], false);
        }
        return $lang_array;
    }

    /**
     * @param string $attributeTextId E.g. ea_145
     * @param mixed $attribute_id
     * @return string
     */
    public function getParticipantAttribute($attributeTextId, $attribute_id = false)
    {
        if ($attribute_id == false) {
            [, $attribute_id] = explode('_', $attributeTextId);
        }

        $participantAttributes = ParticipantAttribute::model()->getAttributeInfo($this->participant_id);
        foreach ($participantAttributes as $singleAttribute) {
            if ($singleAttribute['attribute_id'] == $attribute_id) {
                return $singleAttribute->decrypt()['value'];
            }
        }
        return "";
    }

    /**
     * @return int|string
     */
    public function getCountActiveSurveys()
    {
        $activeSurveys = $this->surveylinks;
        return count($activeSurveys) > 0 ? count($activeSurveys) : "";
    }

    /**
     * @return string HTML
     */
    public function getBlacklistSwitchbutton()
    {
        if ($this->userHasPermissionToEdit()) {
            $inputHtml = App()->getController()->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                'name'          => 'blacklisted_' . $this->participant_id,
                'checkedOption' => $this->blacklisted === "Y" ? "1" : "0",
                'selectOptions' => [
                    '1' => gT('Yes'),
                    '0' => gT('No'),
                ],
                'htmlOptions'   => [
                    'class' => 'action_changeBlacklistStatus'
                ]
            ], true);
            return $inputHtml;
        }

        if ($this->blacklisted === 'Y') {
            return gT('Yes');
        }

        return gT('No');
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        $cols = [
            [
                "name"              => 'checkbox',
                "type"              => 'raw',
                "header"            => "<input type='checkbox' id='action_toggleAllParticipant' />",
                "filter"            => false,
                'filterHtmlOptions' => ['class' => 'ls-sticky-column'],
                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                'htmlOptions'       => ['class' => 'ls-sticky-column'],
            ],
            [
                "name" => 'lastname',
            ],
            [
                "name" => 'firstname',
            ],
            [
                "name" => 'email',
            ],
            [
                "name"   => 'language',
                "value"  => 'getLanguageNameFromCode($data->language, false)',
                'filter' => $this->allUsedLanguagesWithRealName
            ],
            [
                "name"        => 'countActiveSurveys',
                "value"       => '$data->getCountActiveSurveys()',
                "header"      => gT("Active surveys"),
                "htmlOptions" => ['width' => '80px']
            ],
            [
                "name"   => 'owner.full_name',
                "header" => gT("Owner"),
                'value' => '$data->owner ? $data->owner->full_name : gT("(Deleted user)")',
                "filter" => $this->getOwnersList($this->owner_uid)
            ],
            [
                "name"   => 'blacklisted',
                "value"  => '$data->getBlacklistSwitchbutton()',
                "type"   => "raw",
                "filter" => ['N' => gT("No"), 'Y' => gT('Yes')]
            ],
            [
                'name'  => 'created',
                'value' => '$data->createdFormatted',
                'type'  => 'raw',
            ],
        ];

        $extraAttributeParams = Yii::app()->request->getParam('extraAttribute');
        foreach ($this->allExtraAttributes as $name => $attribute) {
            if ($attribute['visible'] === "FALSE") {
                continue;
            }
            if (!isset($extraAttributeParams[$name])) {
                $extraAttributeParams[$name] = '';
            }
            $col_array = [
                "value"  => '$data->getParticipantAttribute($this->id)',
                "id"     => $name,
                "header" => $attribute['defaultname'] . $this->setEncryptedAttributeLabel(0, 'Participant', $attribute['defaultname']),
                "type"   => "html",
            ];
            //textbox
            if ($attribute['attribute_type'] === "TB") {
                $col_array["filter"] = TbHtml::textField("extraAttribute[" . $name . "]", $extraAttributeParams[$name]);
            } elseif ($attribute['attribute_type'] === "DD") {
                //dropdown
                $options_raw = $this->getOptionsForAttribute($attribute['attribute_id']);
                $options_array = [
                    '' => ''
                ];
                foreach ($options_raw as $option) {
                    $options_array[$option['value']] = $option['value'];
                }

                $col_array["filter"] = TbHtml::dropDownList("extraAttribute[" . $name . "]", $extraAttributeParams[$name], $options_array);
            } elseif ($attribute['attribute_type'] === "DP") {
                //date -> still a text field, too many errors with the gridview
                $col_array["filter"] = TbHtml::textField("extraAttribute[" . $name . "]", $extraAttributeParams[$name]);
            }
            $cols[] = $col_array;
        }
        $cols[] = [
            "name"              => 'buttons',
            "type"              => 'raw',
            "header"            => gT("Action"),
            "filter"            => false,
            'filterHtmlOptions' => ['class' => 'ls-sticky-column'],
            'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
            'htmlOptions'       => ['class' => 'ls-sticky-column'],
        ];
        return $cols;
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $encryptedAttributes = $this->getParticipantsEncryptionOptions();
        $encryptedAttributesColums = isset($encryptedAttributes) && isset($encryptedAttributes['columns'])
            ? $encryptedAttributes['columns']
            : [];
        $encryptedAttributesColums = array_filter($encryptedAttributesColums, function ($column) {
            return $column === 'Y';
        });
        $encryptedAttributesColums = array_keys($encryptedAttributesColums);

        $sort = new CSort();
        $sort->defaultOrder = 'lastname';
        $sortAttributes = array(
            'lastname' => array(
                'asc' => 't.lastname',
                'desc' => 't.lastname desc',
            ),
            'firstname' => array(
                'asc' => 't.firstname',
                'desc' => 't.firstname desc',
            ),
            'email' => array(
                'asc' => 't.email',
                'desc' => 't.email desc',
            ),
            'language' => array(
                'asc' => 't.language',
                'desc' => 't.language desc',
            ),
            'owner.full_name' => array(
                'asc' => 'owner.full_name',
                'desc' => 'owner.full_name desc',
            ),
            'blacklisted' => array(
                'asc' => 't.blacklisted',
                'desc' => 't.blacklisted desc',
            ),
            'countActiveSurveys' => array(
                'asc' => 'countActiveSurveys',
                'desc' => 'countActiveSurveys desc',
            ),
            'created' => array(
                'asc' => 't.created asc',
                'desc' => 't.created desc'
            )
        );
        $this->decryptEncryptAttributes('encrypt');

        if (!empty($encryptedAttributesColums)) {
            foreach ($encryptedAttributesColums as $encryptedColum) {
                if (isset($sortAttributes[$encryptedColum])) {
                    unset($sortAttributes[$encryptedColum]);
                }
            }
        }

        $criteria = new LSDbCriteria();
        $criteria->join = 'LEFT JOIN {{users}} as owner on uid=owner_uid ' .
            'LEFT JOIN {{participant_shares}} AS shares ON t.participant_id = shares.participant_id AND (shares.share_uid = ' . Yii::app()->user->id . ' OR shares.share_uid = -1) ';
        $criteria->compare('t.participant_id', $this->participant_id, true, 'AND', true);
        $criteria->compare('t.firstname', $this->firstname, true, 'AND', true);
        $criteria->compare('t.lastname', $this->lastname, true, 'AND', true);
        $criteria->compare('t.email', $this->email, true, 'AND', true);
        $criteria->compare('t.language', $this->language, true);
        $criteria->compare('t.blacklisted', $this->blacklisted, true);
        $criteria->compare('t.owner_uid', $this->owner_uid);


        //Todo - rewrite the search functionalities for the extra attributes !
        $extraAttributeParams = Yii::app()->request->getParam('extraAttribute');
        $extraAttributeValues = array();

        //Create the filter for the extra attributes
        foreach ($this->allExtraAttributes as $name => $attribute) {
            if (isset($extraAttributeParams[$name]) && $extraAttributeParams[$name]) {
                $extraAttributeValues[$name] = $extraAttributeParams[$name];
            }
        }

        // Include a query for each extra attribute to filter
        foreach ($extraAttributeValues as $attributeId => $value) {
            $attributeType = $this->allExtraAttributes[$attributeId]['attribute_type'];
            $attributeId = (int) substr($attributeId, 3);

            /** @var string Param name to bind in prepared statement */
            $ParticipantAttributesCriteria = new LSDbCriteria();
            $ParticipantAttributesCriteria->select = 'pa.participant_id';
            $ParticipantAttributesCriteria->distinct = true;
            $ParticipantAttributesCriteria->alias = 'pa';
            $ParticipantAttributesCriteria->compare('attribute_id', $attributeId);
            // Use "LIKE" for text-box, equal for other types
            $ParticipantAttributesCriteria->compare('value', $value, $attributeType === 'TB');
            $callParticipantAttributes = ParticipantAttribute::model()->getCommandBuilder()->createFindCommand(ParticipantAttribute::model()->getTableSchema(), $ParticipantAttributesCriteria);
            $criteria->addCondition('t.participant_id IN (' . $callParticipantAttributes->getText() . ')');
            $criteria->params = array_merge($criteria->params, $ParticipantAttributesCriteria->params);
        }

        $DBCountActiveSurveys = SurveyLink::model()->tableName();
        $sqlCountActiveSurveys = "(SELECT COUNT(*) FROM " . $DBCountActiveSurveys . " cas WHERE cas.participant_id = t.participant_id )";

        $criteria->select = array(
            't.*',
            'shares.share_uid',
            'shares.date_added',
            'shares.can_edit',
            $sqlCountActiveSurveys . ' AS countActiveSurveys',
            // NB: This is need to avoid confusion between t.participant_id and shares.participant_id
            't.participant_id AS id',
        );
        if ($this->extraCondition) {
            $criteria->mergeWith($this->extraCondition);
        }
        $sort->attributes = $sortAttributes;
        $sort->defaultOrder = 't.lastname ASC';

        // Users can only see:
        // 1) Participants they own;
        // 2) participants shared with them;
        // 3) participants shared with everyone
        // 4) all participants if they have global permission
        // Superadmins can see all users.
        $isSuperAdmin = Permission::model()->hasGlobalPermission('superadmin', 'read');
        $readAllPermission = Permission::model()->hasGlobalPermission('participantpanel', 'read');
        if (!$isSuperAdmin && !$readAllPermission) {
            $criteria->addCondition('t.owner_uid = ' . App()->user->id . ' OR ' . Yii::app()->user->id . ' = shares.share_uid OR shares.share_uid = -1');
        }

        $pageSize = Yii::app()->user->getState('pageSizeParticipantView', Yii::app()->params['defaultPageSize']);
        $this->decryptEncryptAttributes();

        return new LSCActiveDataProvider($this, array(
            'criteria' => $criteria,
            'sort' => $sort,
            'pagination' => array(
                'pageSize' => $pageSize
            )
        ));
    }

    /**
     * @param int $selected Owner id
     * @return string HTML
     */
    public function getOwnersList($selected)
    {
        $owner_ids = Yii::app()->db->createCommand()
            ->selectDistinct('owner_uid')
            ->from('{{participants}}')
            ->queryAll();
        $ownerList = array('' => "");
        foreach ($owner_ids as $id) {
            /** @var User $oUser */
            $oUser = User::model()->findByPk($id['owner_uid']);
            $ownerList[$id['owner_uid']] = $oUser ? $oUser->full_name : gT("(Deleted user)");
        }
        return TbHtml::dropDownList('Participant[owner_uid]', $selected, $ownerList);
    }

    /**
     * Add Survey Filter
     * @param $conditions
     */
    public function addSurveyFilter($conditions)
    {
        $this->extraCondition = $this->getParticipantsSearchMultipleCondition($conditions);
    }

    /**
     * Function for generation of unique id
     * @return string
     */
    public static function genUuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * This function is responsible for adding the participant to the database
     * @param array $aData Participant data
     * @return string|Participant Error message on failure, participant object on success
     */
    public function insertParticipant($aData)
    {
        $oParticipant = new self();
        foreach ($aData as $sField => $sValue) {
            $oParticipant->$sField = $sValue;
        }

        try {
            $result = $oParticipant->encryptSave(true);
            if (!$result) {
                return $this->flattenErrorMessages($oParticipant->getErrors());
            }
            return $oParticipant;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Takes result from model->getErrors() and creates a
     * long string of all messages.
     * @param array $errors
     * @return string
     */
    private function flattenErrorMessages(array $errors)
    {
        $result = '';
        foreach ($errors as $error) {
            $result .= $error[0] . ' ';
        }
        return $result;
    }

    /**
     * Returns the primary key of this table
     *
     * @access public
     * @return string
     */
    public function primaryKey()
    {
        return 'participant_id';
    }

    /**
     * This function updates the data edited in the view
     *
     * @param array $data
     * @return void
     */
    public function updateRow($data)
    {
        $record = $this->findByPk($data['participant_id']);
        foreach ($data as $key => $value) {
            $record->$key = $value;
        }
        $record->save();
    }

    /**
     * This function returns a list of participants who are either owned or shared
     * with a specific user
     *
     * @param int $userid The ID of the user that we are listing participants for
     *
     * @return Participant[] objects containing all the users
     */
    public function getParticipantsOwner($userid)
    {
        $subquery = Yii::app()->db->createCommand()
            ->select('{{participants}}.participant_id,{{participant_shares}}.can_edit')
            ->from('{{participants}}')
            ->leftJoin('{{participant_shares}}', ' {{participants}}.participant_id={{participant_shares}}.participant_id')
            ->where('owner_uid = :userid1 OR share_uid = :userid2')
            ->group('{{participants}}.participant_id,{{participant_shares}}.can_edit');

        $command = Yii::app()->db->createCommand()
            ->select('p.*, ps.can_edit')
            ->from('{{participants}} p')
            ->join('(' . $subquery->getText() . ') ps', 'ps.participant_id = p.participant_id')
            ->bindParam(":userid1", $userid, PDO::PARAM_INT)
            ->bindParam(":userid2", $userid, PDO::PARAM_INT);

        return $command->queryAll();
    }

    /**
     * @param integer $userid
     * @return int
     */
    public function getParticipantsOwnerCount($userid)
    {
        $command = Yii::app()->db->createCommand()
            ->select('count(*)')
            ->from('{{participants}} p')
            ->leftJoin('{{participant_shares}} ps', 'ps.participant_id = p.participant_id')
            ->where('p.owner_uid = :userid1 OR ps.share_uid = :userid2')
            ->bindParam(":userid1", $userid, PDO::PARAM_INT)
            ->bindParam(":userid2", $userid, PDO::PARAM_INT);
        return $command->queryScalar();
    }

    /**
     * Get the number of participants, no restrictions
     *
     * @return string
     */
    public function getParticipantsCountWithoutLimit()
    {
        return Participant::model()->count();
    }

    /**
     * @return Participant[]
     */
    public function getParticipantsWithoutLimit()
    {
        return Yii::app()->db->createCommand()->select('*')->from('{{participants}}')->queryAll();
    }

    /**
     * This function combines the shared participant and the central participant
     * table and searches for any reference of owner id in the combined record
     * of the two tables
     *
     * @param  int $userid The id of the owner
     * @return int The number of participants owned by $userid who are shared
     */
    public function getParticipantsSharedCount($userid)
    {
        $count = Yii::app()->db->createCommand()->select('count(*)')->from('{{participants}}')->join('{{participant_shares}}', '{{participant_shares}}.participant_id = {{participants}}.participant_id')->where('owner_uid = :userid')->bindParam(":userid", $userid, PDO::PARAM_INT)->queryScalar();
        return $count;
    }

    /**
     * @param integer $page
     * @param integer $limit
     * @param array $attid
     * @param string|null $order
     * @param CDbCriteria $search
     * @param integer $userid
     * @return array
     * @throws CException
     */
    public function getParticipants($page, $limit, $attid, $order = null, $search = null, $userid = null)
    {
        $data = $this->getParticipantsSelectCommand(false, $attid, $search, $userid, $page, $limit, $order);

        $allData = $data->queryAll();

        $participants = array();
        foreach ($allData as $key => $data) {
            $participants[$key] = Participant::model()->findByPk($data['participant_id'])->decrypt()->attributes;
            $attributes = ParticipantAttribute::model()->findAll(array('condition' => "participant_id = '" . $data['participant_id'] . "'"));
            foreach ($attributes as $attribute) {
                if (array_key_exists('a' . $attribute['attribute_id'], $data)) {
                    $participants[$key]['a' . $attribute['attribute_id']] = $attribute->decrypt()->attributes['value'];
                }
            }
        }
        return $participants;
    }

    /**
     * Duplicated from getparticipants, only to have a count
     *
     * @param array $attid
     * @param CDbCriteria $search
     * @param int $userid
     * @return int
     */
    public function getParticipantsCount($attid, $search = null, $userid = null)
    {
        $data = $this->getParticipantsSelectCommand(true, $attid, $search, $userid);

        return $data->queryScalar();
    }

    /**
     * @param bool $count
     * @param array $attid
     * @param CDbCriteria $search
     * @param integer $userid
     * @param integer $page
     * @param integer $limit
     * @param string|null $order
     * @return CDbCommand
     */
    private function getParticipantsSelectCommand($count, $attid, $search = null, $userid = null, $page = null, $limit = null, $order = null)
    {
        $selectValue = array();
        $joinValue = array();

        $selectValue[] = "p.*";
        $selectValue[] = "luser.full_name as ownername";
        $selectValue[] = "luser.users_name as username";

        $aAllAttributes = ParticipantAttributeName::model()->getAllAttributes();
        foreach ($aAllAttributes as $aAttribute) {
            if (!is_null($search) && strpos((string) $search->condition, 'attribute' . $aAttribute['attribute_id']) !== false) {
                $attid[$aAttribute['attribute_id']] = $aAttribute;
            }
        }
        // Add survey count subquery
        $subQuery = Yii::app()->db->createCommand()
            ->select('count(*) survey')
            ->from('{{survey_links}} sl')
            ->where('sl.participant_id = p.participant_id');
        $selectValue[] = sprintf('(%s) survey', $subQuery->getText());
        array_push($joinValue, "left join {{users}} luser ON luser.uid=p.owner_uid");
        foreach ($attid as $iAttributeID => $aAttributeDetails) {
            if ($iAttributeID == 0) {
                continue;
            }
            $sDatabaseType = Yii::app()->db->getDriverName();
            if ($sDatabaseType == 'mssql' || $sDatabaseType == "sqlsrv" || $sDatabaseType == 'dblib') {
                $selectValue[] = "cast(attribute" . $iAttributeID . ".value as varchar(max)) as a" . $iAttributeID;
            } else {
                $selectValue[] = "attribute" . $iAttributeID . ".value as a" . $iAttributeID;
            }
            array_push($joinValue, "LEFT JOIN {{participant_attribute}} attribute" . $iAttributeID . " ON attribute" . $iAttributeID . ".participant_id=p.participant_id AND attribute" . $iAttributeID . ".attribute_id=" . $iAttributeID);
        }

        $aConditions = array(); // this wil hold all conditions
        $aParams = array();
        if (!is_null($userid)) {
            // We are not superadmin so we need to limit to our own or shared with us
            $selectValue[] = '{{participant_shares}}.can_edit';
            $joinValue[]   = 'LEFT JOIN {{participant_shares}} ON p.participant_id={{participant_shares}}.participant_id';
            $aConditions[] = 'p.owner_uid = :userid1 OR {{participant_shares}}.share_uid = :userid2 OR {{participant_shares}}.share_uid = 0';
        }

        if ($count) {
            $selectValue = 'count(*) as cnt';
        }

        $data = Yii::app()->db->createCommand()
            ->select($selectValue)
            ->from('{{participants}} p');
        $data->setJoin($joinValue);

        if (!empty($search)) {
            $aSearch = $search->toArray();
            $aConditions[] = $aSearch['condition'];
            $aParams = $aSearch['params'];
        }
        if (Yii::app()->getConfig('hideblacklisted') == 'Y') {
            $aConditions[] = "blacklisted<>'Y'";
        }
        $condition = ''; // This will be the final condition
        foreach ($aConditions as $idx => $newCondition) {
            if ($idx > 0) {
                $condition .= ' AND ';
            }
            $condition .= '(' . $newCondition . ')';
        }

        if (!empty($condition)) {
            $data->setWhere($condition);
        }

        if (!$count) {
            // Apply order and limits
            if (!empty($order)) {
                $data->setOrder($order);
            }

            if ($page <> 0) {
                $offset = ($page - 1) * $limit;
                $data->offset($offset)
                    ->limit($limit);
            }
        }

        $data->bindValues($aParams);

        if (!is_null($userid)) {
            $data->bindParam(":userid1", $userid, PDO::PARAM_INT)
                ->bindParam(":userid2", $userid, PDO::PARAM_INT);
        }

        return $data;
    }

    /**
     * @return int
     */
    public function getSurveyCount($participant_id)
    {
        $count = Yii::app()->db->createCommand()->select('count(*)')->from('{{survey_links}}')->where('participant_id = :participant_id')->bindParam(":participant_id", $participant_id, PDO::PARAM_INT)->queryScalar();
        return $count;
    }

    /**
     * This function deletes the participant from the participant list,
     * references in the survey_links table (but not in matching tokens tables)
     * and then all the participants attributes.
     * @param string $rows Participants ID separated by comma
     * @param bool $bFilter
     * @return int number of deleted participants
     */
    public function deleteParticipants($rows, $bFilter = true)
    {
        // Converting the comma separated IDs to an array and assign chunks of 100 entries to have a reasonable query size
        $aParticipantsIDChunks = array_chunk(explode(",", $rows), 100);
        $deletedParticipants = 0;
        foreach ($aParticipantsIDChunks as $aParticipantsIDs) {
            if ($bFilter) {
                $aParticipantsIDs = $this->filterParticipantIDs($aParticipantsIDs);
            }
            foreach ($aParticipantsIDs as $aID) {
                $oParticipant = Participant::model()->findByPk($aID);
                if ($oParticipant) {
                    $oParticipant->delete();
                    $deletedParticipants++;
                }
                $oParticipantShare = ParticipantShare::model()->findByAttributes(array(
                    'participant_id' => $aID
                ));
                if ($oParticipantShare) {
                    $oParticipantShare->delete();
                }
            }

            Yii::app()->db->createCommand()->delete(Participant::model()->tableName(), array('in', 'participant_id', $aParticipantsIDs));

            // Delete survey links
            Yii::app()->db->createCommand()->delete(SurveyLink::model()->tableName(), array('in', 'participant_id', $aParticipantsIDs));
            // Delete participant attributes
            Yii::app()->db->createCommand()->delete(ParticipantAttribute::model()->tableName(), array('in', 'participant_id', $aParticipantsIDs));
        }
        return $deletedParticipants;
    }


    /**
     * Filter an array of participants IDs according to permissions of the person being logged in
     *
     * @param mixed $aParticipantIDs
     * @return int[]
     */
    public function filterParticipantIDs($aParticipantIDs)
    {
        // If not super admin filter the participant IDs first to owner only
        if (
            !Permission::model()->hasGlobalPermission('superadmin', 'read')
            && !Permission::model()->hasGlobalPermission('participantpanel', 'delete')
        ) {
            $aCondition = array('and', 'owner_uid=:owner_uid', array('in', 'participant_id', $aParticipantIDs));
            $aParameter = array(':owner_uid' => Yii::app()->session['loginID']);
            $aParticipantIDs = Yii::app()->db->createCommand()
                ->select('participant_id')
                ->from(Participant::model()->tableName())
                ->where($aCondition, $aParameter)
                ->queryColumn();
        }
        return $aParticipantIDs;
    }

    /**
     * Deletes CPDB participants identified by their participant ID from survey participant lists
     *
     * @param string $sParticipantsIDs
     *
     * @return integer Number of deleted participants
     * @throws CException
     */
    public function deleteParticipantToken($sParticipantsIDs)
    {
        /* This function deletes the participant from the participant list,
           the participant from any tokens table they're in (using the survey_links table to find them)
           and then all the participants attributes. */
        $aParticipantsIDChunks = array_chunk(explode(",", $sParticipantsIDs), 100);
        $iDeletedParticipants = 0;

        foreach ($aParticipantsIDChunks as $aParticipantsIDChunk) {
            $aParticipantsIDs = $this->filterParticipantIDs($aParticipantsIDChunk);

            // get all surveys with participant IDs
            $aSurveyIDs = App()->db->createCommand()
                ->selectDistinct('survey_id')
                ->from(SurveyLink::model()->tableName())
                ->where(['in', 'participant_id', $aParticipantsIDs])
                ->queryColumn();

            foreach ($aSurveyIDs as $aSurveyID) {
                if (Permission::model()->hasSurveyPermission($aSurveyID, 'tokens', 'delete')) {
                    $survey = Survey::model()->findByPk($aSurveyID);
                    $tokensAndIds = App()->db->createCommand()
                        ->select('token, participant_id')
                        ->from($survey->tokensTableName)
                        ->where(['in', 'participant_id', $aParticipantsIDs])
                        ->queryAll();

                    foreach ($tokensAndIds as $tokenAndId) {
                        // Delete the participant token
                        if (!empty($tokenAndId['participant_id'])) {
                            /** @var Token $token */
                            $token = Token::model($aSurveyID)->find('participant_id = :pid', [':pid' => $tokenAndId['participant_id']]);
                            $token->delete();
                        }
                    }
                    $iDeletedParticipants += $this->deleteParticipants($sParticipantsIDs, false);
                }
            }
        }
        return $iDeletedParticipants;
    }

    /**
     * This function deletes the participant from the participant list,
     * the participant from any tokens table they're in (using the survey_links table to find them),
     * all responses in surveys they've been linked to,
     * and then all the participants attributes.
     *
     * @param string $sParticipantsIDs
     *
     * @return integer Number of deleted participants
     * @throws CDbException
     * @throws CException
     */
    public function deleteParticipantTokenAnswer($sParticipantsIDs)
    {
        $aParticipantsIDChunks = array_chunk(explode(",", $sParticipantsIDs), 100);
        $iDeletedParticipants = 0;

        foreach ($aParticipantsIDChunks as $aParticipantsIDChunk) {
            $aParticipantsIDs = $this->filterParticipantIDs($aParticipantsIDChunk);

            // get all surveys with participant IDs
            $aSurveyIDs = App()->db->createCommand()
                ->selectDistinct('survey_id')
                ->from(SurveyLink::model()->tableName())
                ->where(['in', 'participant_id', $aParticipantsIDs])
                ->queryColumn();

            foreach ($aSurveyIDs as $aSurveyID) {
                $survey = Survey::model()->findByPk($aSurveyID);
                $tokenTable = $survey->tokensTableName;
                $surveyTable = $survey->responsesTableName;
                $tokenTableExists = App()->db->schema->getTable($tokenTable) ? true : false;
                $surveyTableExists = App()->db->schema->getTable($surveyTable) ? true : false;
                $tokensAndIds = App()->db->createCommand()
                    ->select('token, participant_id')
                    ->from($survey->tokensTableName)
                    ->where(['in', 'participant_id', $aParticipantsIDs])
                    ->queryAll();

                if (!isset($tokensAndIds)) {
                    continue;
                }

                if (
                    Permission::model()->hasSurveyPermission($aSurveyID, 'responses', 'delete')
                    || Permission::model()->hasSurveyPermission($aSurveyID, 'tokens', 'delete')
                ) {
                    foreach ($tokensAndIds as $tokenAndId) {
                        //Make sure we have a token value, and that tokens are used to link to the survey
                        // Delete all Responses
                        if (!empty($tokenAndId['token']) && $tokenTableExists) {
                            $oResponses = Response::model($aSurveyID)->findAll("token = :token", [":token" => $tokenAndId['token']]);
                            foreach ($oResponses as $oResponse) {
                                /** @var Response $oResponse */
                                $oResponse->delete(true);
                            }
                        }
                        // Delete the participant token
                        if (!empty($tokenAndId['participant_id']) && $surveyTableExists) {
                            /** @var Token $token */
                            $token = Token::model($aSurveyID)->find('participant_id = :pid', [':pid' => $tokenAndId['participant_id']]);
                            if (!is_null($token)) {
                                $token->delete();
                            }
                        }
                        $iDeletedParticipants = $this->deleteParticipants($sParticipantsIDs, false);
                    }
                }
            }
        }
        return $iDeletedParticipants;
    }

    /**
     * Function builds a select query for searches through participants using the $condition field passed
     * which is in the format "firstfield||sqloperator||value||booleanoperator||secondfield||sqloperator||value||booleanoperator||etc||etc||etc"
     * for example: "firstname||equal||Jason||and||lastname||equal||Cleeland" will produce SQL along the lines of "WHERE firstname = 'Jason' AND lastname=='Cleeland'"
     *
     * @param array $condition an array containing the search string exploded using || so that "firstname||equal||jason" is $condition(1=>'firstname', 2=>'equal', 3=>'jason')
     * @param int $page Which page number to display
     * @param int $limit The limit/number of reords to return
     *
     * @return array $output
     */
    public function getParticipantsSearchMultiple($condition, $page, $limit)
    {
        //http://localhost/limesurvey_yii/admin/participants/getParticipantsResults_json/search/email||contains||gov||and||firstname||contains||AL
        //First contains fieldname, second contains method, third contains value, fourth contains BOOLEAN SQL and, or

        //As we iterate through the conditions we build up the $command query by adding conditions to it
        //
        $i = 0;
        $start = $limit * $page - $limit;
        $command = new CDbCriteria();
        $command->condition = '';

        //The following code performs an IN-SQL order, but this only works for standard participant fields
        //For the time being, lets stick to just sorting the collected results, some thinking
        //needs to be done about how we can sort the actual fullo query when combining with calculated
        //or attribute based fields. I've switched this off, but left the code for future reference. JC
        if (1 == 2) {
            $sord = Yii::app()->request->getPost('sord'); //Sort order
            $sidx = Yii::app()->request->getPost('sidx'); //Sort index
            if (is_numeric($sidx) || $sidx == "survey") {
                $sord = "";
                $sidx = "";
            }
            if (!empty($sidx)) {
                $sortorder = "$sidx $sord";
            } else {
                $sortorder = "";
            }
            if (!empty($sortorder)) {
                $command->order = $sortorder;
            }
        }

        $con = count($condition);
        while ($i < $con && $con > 2) {
            //Special set just for the first query/condition
            if ($i < 3) {
                if (is_numeric($condition[2])) {
                    $condition[2] = intval($condition[2]);
                }
                switch ($condition[1]) {
                    case 'equal':
                        $operator = "=";
                        break;
                    case 'contains':
                        $operator = "LIKE";
                        $condition[2] = "%" . $condition[2] . "%";
                        break;
                    case 'beginswith':
                        $operator = "LIKE";
                        $condition[2] = $condition[2] . "%";
                        break;
                    case 'notequal':
                        $operator = "!=";
                        break;
                    case 'notcontains':
                        $operator = "NOT LIKE";
                        $condition[2] = "%" . $condition[2] . "%";
                        break;
                    case 'greaterthan':
                        $operator = ">";
                        break;
                    case 'lessthan':
                        $operator = "<";
                }
                if ($condition[0] == "survey") {
                    $lang = Yii::app()->session['adminlang'];
                    $command->addCondition('participant_id IN (SELECT distinct {{survey_links}}.participant_id FROM {{survey_links}}, {{surveys_languagesettings}} WHERE {{survey_links}}.survey_id = {{surveys_languagesettings}}.surveyls_survey_id AND {{surveys_languagesettings}}.surveyls_language=:lang AND {{survey_links}}.survey_id ' . $operator . ' :param)');
                    $command->params = array(':lang' => $lang, ':param' => $condition[2]);
                } elseif ($condition[0] == "surveys") {
                    //Search by quantity of linked surveys
                    $addon = ($operator == "<") ? " OR participant_id NOT IN (SELECT distinct participant_id FROM {{survey_links}})" : "";
                    $command->addCondition('participant_id IN (SELECT participant_id FROM {{survey_links}} GROUP BY participant_id HAVING count(*) ' . $operator . ' :param2 ORDER BY count(*))' . $addon);
                    $command->params = array(':param2' => $condition[2]);
                } elseif ($condition[0] == "owner_name") {
                    $userid = Yii::app()->db->createCommand()
                        ->select('uid')
                        ->where('full_name ' . $operator . ' :condition_2')
                        ->from('{{users}}')
                        ->bindParam("condition_2", $condition[2], PDO::PARAM_STR)
                        ->queryAll();
                    $uid = $userid[0];
                    $command->addCondition('owner_uid = :uid');
                    $command->params = array(':uid' => $uid['uid']);
                } elseif (is_numeric($condition[0])) {
                    //Searching for an attribute
                    $command->addCondition('participant_id IN (SELECT distinct {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = :condition_0 AND {{participant_attribute}}.value ' . $operator . ' :condition_2)');
                    $command->params = array(':condition_0' => $condition[0], ':condition_2' => $condition[2]);
                } else {
                    $command->addCondition($condition[0] . ' ' . $operator . ' :condition_2');
                    $command->params = array(':condition_2' => $condition[2]);
                }

                $i += 3;
            } elseif ($condition[$i] != '') {
                //This section deals with subsequent filter conditions that have boolean joiner
                if (is_numeric($condition[$i + 3])) {
                    $condition[$i + 3] = intval($condition[$i + 3]);
                }
                //Force the type of numeric values to be numeric
                $booloperator = strtoupper((string) $condition[$i]);
                $condition1name = ":condition_" . ($i + 1);
                $condition2name = ":condition_" . ($i + 3);
                switch ($condition[$i + 2]) {
                    case 'equal':
                        $operator = "=";
                        break;
                    case 'contains':
                        $operator = "LIKE";
                        $condition[$i + 3] = "%" . $condition[$i + 3] . "%";
                        break;
                    case 'beginswith':
                        $operator = "LIKE";
                        $condition[$i + 3] = $condition[$i + 3] . "%";
                        break;
                    case 'notequal':
                        $operator = "!=";
                        break;
                    case 'notcontains':
                        $operator = "NOT LIKE";
                        $condition[$i + 3] = "%" . $condition[$i + 3] . "%";
                        break;
                    case 'greaterthan':
                        $operator = ">";
                        break;
                    case 'lessthan':
                        $operator = "<";
                }
                if ($condition[$i + 1] == "survey") {
                    $lang = Yii::app()->session['adminlang'];
                    $command->addCondition('participant_id IN (SELECT distinct {{survey_links}}.participant_id FROM {{survey_links}}, {{surveys_languagesettings}} WHERE {{survey_links}}.survey_id = {{surveys_languagesettings}}.surveyls_survey_id AND {{surveys_languagesettings}}.surveyls_language=:lang AND ({{surveys_languagesettings}}.surveyls_title ' . $operator . ' ' . $condition2name . '1 OR {{survey_links}}.survey_id ' . $operator . ' ' . $condition2name . '2))', $booloperator);
                    $command->params = array_merge($command->params, array(':lang' => $lang, $condition2name . '1' => $condition[$i + 3], $condition2name . '2' => $condition[$i + 3]));
                } elseif ($condition[$i + 1] == "surveys") {
                    //search by quantity of linked surveys
                    $addon = ($operator == "<") ? " OR participant_id NOT IN (SELECT distinct participant_id FROM {{survey_links}})" : "";
                    $command->addCondition('participant_id IN (SELECT participant_id FROM {{survey_links}} GROUP BY participant_id HAVING count(*) ' . $operator . ' ' . $condition2name . ' ORDER BY count(*))' . $addon);
                    $command->params = array_merge($command->params, array($condition2name => $condition[$i + 3]));
                } elseif ($condition[$i + 1] == "owner_name") {
                    $userid = Yii::app()->db->createCommand()
                        ->select('uid')
                        ->where('full_name ' . $operator . ' ' . $condition2name)
                        ->from('{{users}}')
                        ->bindParam($condition2name, $condition[$i + 3], PDO::PARAM_STR)
                        ->queryAll();
                    $uid = array();
                    foreach ($userid as $row) {
                        $uid[] = $row['uid'];
                    }
                    $command->addInCondition('owner_uid', $uid, $booloperator);
                } elseif (is_numeric($condition[$i + 1])) {
                    //Searching for an attribute
                    $command->addCondition('participant_id IN (SELECT distinct {{participant_attribute}}.participant_id FROM {{participant_attribute}} WHERE {{participant_attribute}}.attribute_id = ' . $condition1name . ' AND {{participant_attribute}}.value ' . $operator . ' ' . $condition2name . ')', $booloperator);
                    $command->params = array_merge($command->params, array($condition1name => $condition[$i + 1], $condition2name => $condition[$i + 3]));
                } else {
                    $command->addCondition($condition[$i + 1] . ' ' . $operator . ' ' . $condition2name, $booloperator);
                    $command->params = array_merge($command->params, array($condition2name => $condition[$i + 3]));
                }

                $i = $i + 4;
            } else {
                $i = $i + 4;
            }
        }

        if ($page == 0 && $limit == 0) {
            $arr = Participant::model()->findAll($command);
            $data = array();
            foreach ($arr as $t) {
                $data[$t->participant_id] = $t->attributes;
            }
        } else {
            $command->limit = $limit;
            $command->offset = $start;
            $arr = Participant::model()->findAll($command);
            $data = array();
            foreach ($arr as $t) {
                $data[$t->participant_id] = $t->attributes;
            }
        }

        return $data;
    }

    /**
     * Function builds a select query for searches through participants using the $condition field passed
     * which is in the format "firstfield||sqloperator||value||booleanoperator||secondfield||sqloperator||value||booleanoperator||etc||etc||etc"
     * for example: "firstname||equal||Jason||and||lastname||equal||Cleeland" will produce SQL along the lines of "WHERE firstname = 'Jason' AND lastname=='Cleeland'"
     *
     * @param array $condition an array containing the search string exploded using || so that "firstname||equal||jason" is $condition(1=>'firstname', 2=>'equal', 3=>'jason')
     *
     * @return CDbCriteria $output
     */
    public function getParticipantsSearchMultipleCondition($condition)
    {
        //http://localhost/limesurvey_yii/admin/participants/getParticipantsResults_json/search/email||contains||gov||and||firstname||contains||AL
        //First contains fieldname, second contains method, third contains value, fourth contains BOOLEAN SQL and, or

        //As we iterate through the conditions we build up the $command query by adding conditions to it
        //
        $i = 0;
        $command = new CDbCriteria();
        $command->condition = '';
        $aParams = array();

        $iNumberOfConditions = (count($condition) + 1) / 4;
        while ($i < $iNumberOfConditions) {
            $sFieldname = $condition[$i * 4];
            $sOperator = $condition[($i * 4) + 1];
            $sValue = $condition[($i * 4) + 2];
            $param = ':condition_' . $i;
            switch ($sOperator) {
                case 'equal':
                    $operator = '=';
                    $aParams[$param] = $sValue;
                    break;
                case 'contains':
                    $operator = 'LIKE';
                    $aParams[$param] = '%' . $sValue . '%';
                    break;
                case 'beginswith':
                    $operator = 'LIKE';
                    $aParams[$param] = $sValue . '%';
                    break;
                case 'notequal':
                    $operator = '!=';
                    $aParams[$param] = $sValue;
                    break;
                case 'notcontains':
                    $operator = 'NOT LIKE';
                    $aParams[$param] = '%' . $sValue . '%';
                    break;
                case 'greaterthan':
                    $operator = '>';
                    $aParams[$param] = $sValue;
                    break;
                case 'lessthan':
                    $operator = '<';
                    $aParams[$param] = $sValue;
                    break;
            }
            if (isset($condition[(($i - 1) * 4) + 3])) {
                $booloperator = strtoupper((string) $condition[(($i - 1) * 4) + 3]);
            } else {
                $booloperator = 'AND';
            }

            if ($sFieldname == "email") {
                $command->addCondition('p.email ' . $operator . ' ' . $param, $booloperator);
            } elseif ($sFieldname == "survey") {
                $subQuery = Yii::app()->db->createCommand()
                    ->select('sl.participant_id')
                    ->from('{{survey_links}} sl')
                    ->join('{{surveys_languagesettings}} sls', 'sl.survey_id = sls.surveyls_survey_id')
                    ->where('sls.surveyls_title ' . $operator . ' ' . $param)
                    ->group('sl.participant_id');
                $command->addCondition('t.participant_id IN (' . $subQuery->getText() . ')', $booloperator);
            } elseif ($sFieldname == "surveyid") {
                $subQuery = Yii::app()->db->createCommand()
                    ->select('sl.participant_id')
                    ->from('{{survey_links}} sl')
                    ->where('sl.survey_id ' . $operator . ' ' . $param)
                    ->group('sl.participant_id');
                $command->addCondition('t.participant_id IN (' . $subQuery->getText() . ')', $booloperator);
            } elseif ($sFieldname == "surveys") {
                //Search by quantity of linked surveys
                $subQuery = Yii::app()->db->createCommand()
                    ->select('sl.participant_id')
                    ->from('{{survey_links}} sl')
                    ->having('count(*) ' . $operator . ' ' . $param)
                    ->group('sl.participant_id');
                $command->addCondition('t.participant_id IN (' . $subQuery->getText() . ')', $booloperator);
            } elseif ($sFieldname == "owner_name") {
                $command->addCondition('full_name ' . $operator . ' ' . $param, $booloperator);
            } elseif ($sFieldname == "participant_id") {
                $command->addCondition('t.participant_id ' . $operator . ' ' . $param, $booloperator);
            } elseif (is_numeric($sFieldname)) {
                //Searching for an attribute
                $command->addCondition('attribute' . $sFieldname . '.value ' . $operator . ' ' . $param, $booloperator);
            } else {
                // Check if fieldname exists to prevent SQL injection
                $aSafeFieldNames = array(
                    'firstname',
                    'lastname',
                    'email',
                    'blacklisted',
                    'surveys',
                    'survey',
                    'language',
                    'owner_uid',
                    'owner_name'
                );
                if (!in_array($sFieldname, $aSafeFieldNames)) {
                    // Skip invalid fieldname
                    continue;
                }
                $command->addCondition(Yii::app()->db->quoteColumnName($sFieldname) . ' ' . $operator . ' ' . $param, $booloperator);
            }

            $i++;
        }

        if (count($aParams) > 0) {
            $command->params = $aParams;
        }

        return $command;
    }

    /**
     * Returns true if participant_id has ownership or shared rights over this participant false if not
     *
     * @param string $participant_id
     * @return bool
     */
    public function isOwner($participant_id)
    {
        // Superadmins can edit all participants
        if (Permission::model()->hasGlobalPermission('superadmin')) {
            return true;
        }

        $userid = Yii::app()->session['loginID'];

        $isOwner = Yii::app()
            ->db
            ->createCommand()
            ->select('count(*)')
            ->where('participant_id = :participant_id AND owner_uid = :userid')
            ->from('{{participants}}')
            ->bindParam(":participant_id", $participant_id, PDO::PARAM_STR)
            ->bindParam(":userid", $userid, PDO::PARAM_INT)
            ->queryScalar();

        $is_shared = Yii::app()
            ->db
            ->createCommand()
            ->select('count(*)')
            ->where('participant_id = :participant_id AND ( share_uid = :userid OR share_uid = 0)')
            ->from('{{participant_shares}}')
            ->bindParam(":participant_id", $participant_id, PDO::PARAM_STR)
            ->bindParam(":userid", $userid, PDO::PARAM_INT)
            ->queryScalar();

        if ($is_shared > 0 || $isOwner > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This function is responsible for showing all the participant's shared by a particular user based on the user id
     * @param int $userid
     * @return array
     */
    public function getParticipantShared($userid)
    {
        return Yii::app()->db->createCommand()->select('{{participants}}.*, {{participant_shares}}.*')->from('{{participants}}')->join('{{participant_shares}}', '{{participant_shares}}.participant_id = {{participants}}.participant_id')->where('owner_uid = :userid')->bindParam(":userid", $userid, PDO::PARAM_INT)->queryAll();
    }

    /**
     * This function is responsible for showing all the participant's shared to the superadmin
     * @return array
     */
    public function getParticipantSharedAll()
    {
        return Yii::app()->db->createCommand()->select('{{participants}}.*,{{participant_shares}}.*')->from('{{participants}}')->join('{{participant_shares}}', '{{participant_shares}}.participant_id = {{participants}}.participant_id')->queryAll();
    }

    /**
     * Updates the token attribute properties of a survey to include the mapping to Central Participant Database (CPDB) attributes.
     * If automapping is enabled, this function updates the token field properties with the mapped CPDB field ID.
     *
     * @param int   $surveyId         The ID of the survey.
     * @param array $mappedAttributes An associative array where keys are token attribute field names and values are the corresponding CPDB attribute IDs.
     * @return void
     */
    private function updateTokenFieldProperties($surveyId, array $mappedAttributes)
    {
        $tokenAttributes = Survey::model()->findByPk($surveyId)->tokenattributes;
        $attributesChanged = false;
        foreach ($mappedAttributes as $key => $iIDAttributeCPDB) {
            if (is_numeric($iIDAttributeCPDB)) {
                /* Update the attribute descriptions info */
                $tokenAttributes[$key]['cpdbmap'] = $iIDAttributeCPDB;
                $attributesChanged = true;
            }
        }
        if ($attributesChanged) {
                Yii::app()->db
                    ->createCommand()
                    ->update('{{surveys}}', array("attributedescriptions" => json_encode($tokenAttributes)), 'sid = ' . $surveyId);
        }
    }

    /**
     * Check for column duplicates from CPDB to token attributes
     * Throws error message if an attribute already exists; otherwise false.
     *
     * @param int $surveyId
     * @param string[] $newAttributes Array of CPDB attributes ids like ['42', '32', ...]
     * @return boolean
     * @throws CPDBException with error message
     */
    private function checkColumnDuplicates(int $surveyId, array $newAttributes)
    {
        $tokenTableSchema = Yii::app()->db
            ->schema
            ->getTable("{{tokens_$surveyId}}");

        foreach ($tokenTableSchema->columns as $columnName => $columnObject) {
            if (strpos((string) $columnName, 'attribute_') !== false) {
                $id = substr((string) $columnName, 10);
                if (in_array($id, $newAttributes)) {
                    $name = ParticipantAttributeName::model()->getAttributeName($id, $_SESSION['adminlang']);
                    if (empty($name)) {
                        $name = array('attribute_name' => '[Found no name]');
                    }
                    throw new CPDBException(sprintf("Participant attribute already exists: %s", $name['attribute_name']));
                }
            }
        }

        return false;
    }

    /**
     * Handles the creation of new token attributes by adding them to survey descriptions
     * and creating corresponding columns.
     *
     * This function orchestrates the process of adding new participant attributes to a survey by:
     * 1. Adding the attributes to the survey's attribute descriptions in the database
     * 2. Creating corresponding columns in the survey's token table
     *
     * @param int $surveyId The ID of the survey to which the new token attributes will be added.
     * @param array $newAttributes An array of participant attribute IDs from the CPDB to be added as token attributes.
     *
     * @return array A two-element array containing:
     *               - [0] array $addedAttributes: Array of participant attribute IDs that were successfully added.
     *               - [1] array $addedAttributeIds: Array of token field names (e.g., 'attribute_1', 'attribute_2') for the added attributes.
     */
    private function handleNewTokenAttributes(int $surveyId, array $newAttributes): array
    {
        [
            $addedAttributes,
            $addedAttributeIds,
        ] = $this->addNewAttributesToSurveysAttributeDescriptions(
            $surveyId,
            $newAttributes
        );
        $this->createColumnsInTokenTable($addedAttributeIds, $surveyId);

        return [$addedAttributes, $addedAttributeIds];
    }

    /**
     * Adds new participant attributes to the survey's attribute descriptions.
     *
     * This function retrieves participant attribute information from the CPDB (Central Participant Database),
     * creates field definitions for new token attributes, and updates the survey's attributedescriptions field
     * in the database. It handles attribute names in multiple languages and supports dropdown type attributes
     * with their associated options.
     *
     * @param int $surveyId The ID of the survey to which attributes will be added.
     * @param array $newAttributes An array of participant attribute IDs from the CPDB to be added as token attributes.
     *
     * @return array A two-element array containing:
     *               - [0] array $addedAttributes: Array of participant attribute IDs that were successfully added.
     *               - [1] array $addedAttributeIds: Array of token field names (e.g., 'attribute_1', 'attribute_2') for the added attributes.
     */
    private function addNewAttributesToSurveysAttributeDescriptions(int $surveyId, array $newAttributes): array
    {
        if (empty($newAttributes)) {
            return [[], []];
        }

        // Get default language
        $surveyInfo = getSurveyInfo($surveyId);
        $defaultSurveyLang = $surveyInfo['surveyls_language'];
        $adminLang = App()->session['adminlang'];

        // Fetch all attribute data in a single query
        $attributeData = $this->fetchAttributeData($newAttributes);

        // Fetch dropdown options for all DD type attributes in a single query
        $dropdownOptions = $this->fetchDropdownOptions($newAttributes);

        // Build field contents
        $fieldContents = [];
        $addedAttributes = [];
        $addedAttributeIds = [];

        foreach ($newAttributes as $attributeId) {
            if (!isset($attributeData[$attributeId])) {
                continue; // Skip if attribute data not found
            }

            $newFieldName = 'attribute_' . $attributeId;
            $languages = $attributeData[$attributeId]['languages'];
            $attributeType = $attributeData[$attributeId]['type'];

            // Determine the best language match
            $newName = $this->selectBestLanguageName($languages, $defaultSurveyLang, $adminLang);

            // Get dropdown options if applicable
            $dropdownOptionsJson = ($attributeType === 'DD' && isset($dropdownOptions[$attributeId]))
                ? json_encode($dropdownOptions[$attributeId])
                : '[]';

            $fieldContents[$newFieldName] = [
                'description' => $newName,
                'mandatory' => 'N',
                'encrypted' => 'N',
                'show_register' => 'N',
                'type' => $attributeType,
                'type_options' => $dropdownOptionsJson
            ];

            $addedAttributeIds[] = $newFieldName;
            $addedAttributes[] = $attributeId;
        }

        // Update the survey's attribute descriptions
        $this->updateSurveyAttributeDescriptions($surveyId, $fieldContents);

        return [$addedAttributes, $addedAttributeIds];
    }

    /**
     * Fetches attribute data for multiple attributes in a single query.
     *
     * @param array $attributeIds Array of attribute IDs to fetch.
     * @return array Associative array with attribute IDs as keys and their data as values.
     */
    private function fetchAttributeData(array $attributeIds): array
    {
        $attributeNames = App()->db
            ->createCommand()
            ->select([
                '{{participant_attribute_names}}.attribute_id',
                '{{participant_attribute_names}}.attribute_type',
                '{{participant_attribute_names_lang}}.attribute_name',
                '{{participant_attribute_names_lang}}.lang'
            ])
            ->from('{{participant_attribute_names}}')
            ->join(
                '{{participant_attribute_names_lang}}',
                '{{participant_attribute_names}}.attribute_id = {{participant_attribute_names_lang}}.attribute_id'
            )
            ->where(['in', '{{participant_attribute_names}}.attribute_id', $attributeIds])
            ->queryAll();

        $result = [];
        foreach ($attributeNames as $row) {
            $attrId = $row['attribute_id'];
            if (!isset($result[$attrId])) {
                $result[$attrId] = [
                    'type' => $row['attribute_type'],
                    'languages' => []
                ];
            }
            $result[$attrId]['languages'][$row['lang']] = $row['attribute_name'];
        }

        return $result;
    }

    /**
     * Fetches dropdown options for multiple attributes in a single query.
     *
     * @param array $attributeIds Array of attribute IDs to fetch dropdown options for.
     * @return array Associative array with attribute IDs as keys and arrays of option values.
     */
    private function fetchDropdownOptions(array $attributeIds): array
    {
        $options = App()->db
            ->createCommand()
            ->select(['attribute_id', 'value'])
            ->from('{{participant_attribute_values}}')
            ->where(['in', 'attribute_id', $attributeIds])
            ->queryAll();

        $result = [];
        foreach ($options as $option) {
            $result[$option['attribute_id']][] = $option['value'];
        }

        return $result;
    }

    /**
     * Selects the best language name based on priority: default survey language, admin language, or first available.
     *
     * @param array $languages Associative array of language codes to attribute names.
     * @param string $defaultSurveyLang The default survey language code.
     * @param string $adminLang The admin language code.
     * @return string The selected attribute name.
     */
    private function selectBestLanguageName(array $languages, string $defaultSurveyLang, string $adminLang): string
    {
        if (isset($languages[$defaultSurveyLang])) {
            return $languages[$defaultSurveyLang];
        }

        if (isset($languages[$adminLang])) {
            return $languages[$adminLang];
        }

        return reset($languages) ?: '';
    }

    /**
     * Updates the survey's attribute descriptions with new field contents.
     *
     * @param int $surveyId The survey ID.
     * @param array $fieldContents New field contents to merge with existing attributes.
     * @return void
     */
    private function updateSurveyAttributeDescriptions(int $surveyId, array $fieldContents): void
    {
        $tokenAttributes = App()->db
            ->createCommand()
            ->select('attributedescriptions')
            ->from('{{surveys}}')
            ->where('sid = :sid', [':sid' => $surveyId])
            ->queryScalar();

        $tokenAttributes = decodeTokenAttributes($tokenAttributes ?? '');
        $tokenAttributes = array_merge($tokenAttributes, $fieldContents);

        App()->db
            ->createCommand()
            ->update(
                '{{surveys}}',
                ['attributedescriptions' => json_encode($tokenAttributes)],
                'sid = :sid',
                [':sid' => $surveyId]
            );
    }

    /**
     * Creates new columns in the survey's token table for participant attributes.
     *
     * This function adds new attribute columns to the tokens table for a specific survey,
     * then refreshes the database schema cache and model metadata to ensure the new
     * columns are recognized by the application.
     *
     * @param array $addedAttributeIds An array of attribute field names (e.g., 'attribute_1', 'attribute_2') to be added as columns in the token table.
     * @param int $surveyId The ID of the survey whose token table will be modified.
     *
     * @return void
     */
    private function createColumnsInTokenTable(array $addedAttributeIds, int $surveyId): void
    {
        App()->loadHelper('update.updatedb');
        foreach ($addedAttributeIds as $attributeId) {
            addColumn("{{tokens_$surveyId}}", $attributeId, 'string');
        }
        App()->db->schema->getTable("{{tokens_$surveyId}}", true); // Refresh schema cache just
        Token::model($surveyId)->refreshMetaData(); // Refresh model meta data
    }

    /**
     * Write participants as tokens or something
     *
     * @param int $surveyId
     * @param array $participantIds
     * @param array $mappedAttributes
     * @param array $newAttributes
     * @param array $addedAttributes ?? Result from createColumnsInTokenTable
     * @param array $addedAttributeIds ?? Result from createColumnsInTokenTable
     * @param array $options As in calling function
     * @return integer[] (success, duplicate, blacklistSkipped)
     * @throws Exception
     */
    private function writeParticipantsToTokenTable(
        $surveyId,
        array $participantIds,
        array $mappedAttributes,
        array $newAttributes,
        array $addedAttributes,
        array $addedAttributeIds,
        array $options
    ) {
        $duplicate = 0;
        $successful = 0;
        $blacklistSkipped = 0;

        $oParticipants = Participant::model()->findAllByPk($participantIds);
        $oTokens = TokenDynamic::model($surveyId)->findAll();

        foreach ($oParticipants as $oParticipant) {
            if (
                Yii::app()->getConfig('blockaddingtosurveys') == 'Y'
                && $oParticipant->blacklisted == 'Y'
            ) {
                $blacklistSkipped++;
                continue;
            }
            $oParticipant->decrypt();
            $isDuplicate = array_reduce($oTokens, function ($carry, $oToken) use ($oParticipant) {
                return $carry ? $carry : ($oToken->participant_id == $oParticipant->participant_id);
            }, false);
            if ($isDuplicate) {
                //Participant already exists in survey participant list - don't copy
                $duplicate++;

                // Here is where we can put code for overwriting the attribute data if so required
                if ($options['overwriteauto'] == "true") {
                    //If there are new attributes created, add those values to the token entry for this participant
                    if (!empty($newAttributes)) {
                        $numberofattributes = count($addedAttributes);
                        for ($a = 0; $a < $numberofattributes; $a++) {
                            Participant::model()->updateTokenAttributeValue($surveyId, $oParticipant->participant_id, $addedAttributes[$a], $addedAttributeIds[$a]);
                        }
                    }
                    //If there are automapped attributes, add those values to the token entry for this participant
                    foreach ($mappedAttributes as $key => $value) {
                        if ($key[10] == 'c') {
                            //We know it's automapped because the 11th letter is 'c'
                            Participant::model()->updateTokenAttributeValue($surveyId, $oParticipant->participant_id, $value, $key);
                        }
                    }
                }
                if ($options['overwriteman'] == "true") {
                    //If there are any manually mapped attributes, add those values to the token entry for this participant
                    foreach ($mappedAttributes as $key => $value) {
                        if ($key[10] != 'c' && $key[9] == '_') {
                            //It's not an auto field because it's 11th character isn't 'c'
                            Participant::model()->updateTokenAttributeValue($surveyId, $oParticipant->participant_id, $value, $key);
                        }
                    }
                }
                if ($options['overwritest'] == "true") {
                    foreach ($mappedAttributes as $key => $value) {
                        if ((strlen($key) > 8 && $key[10] != 'c' && $key[9] != '_') || strlen($key) < 9) {
                            Participant::model()->updateTokenAttributeValue($surveyId, $oParticipant->participant_id, $value, $key);
                        }
                    }
                }
            } else {
                //Create a new token entry for this participant
                $oToken = Token::create($surveyId);
                $oToken->participant_id = $oParticipant->participant_id;
                $oToken->firstname = $oParticipant->firstname;
                $oToken->lastname = $oParticipant->lastname;
                $oToken->email = $oParticipant->email;
                $oToken->language = $oParticipant->language;
                if (!$oToken->encryptSave(true)) {
                    throw new Exception(CHtml::errorSummary($oToken));
                }
                $insertedtokenid = $oToken->tid;

                //Create a survey link for the new token entry
                $oSurveyLink = new SurveyLink();
                $oSurveyLink->participant_id = $oParticipant->participant_id;
                $oSurveyLink->token_id = $insertedtokenid;
                $oSurveyLink->survey_id = $surveyId;
                $oSurveyLink->date_created = date('Y-m-d H:i:s', time());
                try {
                    $oSurveyLink->save();
                } catch (Exception $e) {
                    throw new Exception(gT("Could not update participant attribute value: " . $e->getMessage()));
                }

                //If there are new attributes created, add those values to the token entry for this participant
                if (!empty($newAttributes)) {
                    $numberofattributes = count($addedAttributes);
                    for ($a = 0; $a < $numberofattributes; $a++) {
                        try {
                            Participant::model()->updateTokenAttributeValue($surveyId, $oParticipant->participant_id, $addedAttributes[$a], $addedAttributeIds[$a]);
                        } catch (Exception $e) {
                            throw new Exception(gT("Could not update participant attribute value: " . $e->getMessage()));
                        }
                    }
                }
                //If there are any automatically mapped attributes, add those values to the token entry for this participant
                foreach ($mappedAttributes as $key => $value) {
                    try {
                        // $value can be 'attribute_<number>' here
                        // TODO: Weird...
                        if (strpos((string) $value, 'attribute_') !== false) {
                            $value = substr((string) $value, 10);
                        }

                        Participant::model()->updateTokenAttributeValue($surveyId, $oParticipant->participant_id, $value, $key);
                    } catch (Exception $e) {
                        throw new Exception(gT("Could not update participant attribute value: " . $e->getMessage()));
                    }
                }
                $successful++;
            }
        }

        return array($successful, $duplicate, $blacklistSkipped);
    }

    /**
     * Copies central attributes/participants to an individual survey survey participant list
     *
     * @param int $surveyId The survey ID
     * @param string $participantIds Array containing the participant ids of the participants we are adding
     * @param array $mappedAttributes An array containing a list of /mapped attributes in the form of "token_field_name" => "participant_attribute_id"
     * @param array $newAttributes An array containing new attributes to create in the tokens table
     * @param array $options Array with following options:
     *                overwriteauto - If true, overwrite automatically mapped data
     *                overwriteman - If true, overwrite manually mapped data
     *                overwritest - If true, overwrite standard fields (ie: names, email, participant_id, token)
     *                createautomap - If true, rename the fieldnames of automapped attributes so that in future they are automatically mapped
     * @return array
     */
    public function copyCPDBAttributesToTokens(int $surveyId, array $participantIds, array $mappedAttributes, array $newAttributes, array $options)
    {
        Yii::app()->loadHelper('common');


        // If automapping is enabled then update the token field properties with the mapped CPDB field ID
        if ($options['createautomap']) {
            $this->updateTokenFieldProperties($surveyId, $mappedAttributes);
        }

        // Add existing attribute columns to mappedAttributes. TODO: Why?
        // TODO: What is id here? Could it overwrite something?
        // Existing token attribute columns, from table tokens_{surveyId}
        //$tokenAttributeColumns = $this->getTokenAttributes($surveyId); // (Removed in same commit as this, since unused)
        //foreach ($tokenAttributeColumns as $id => $columnName)
        //{
        //$mappedAttributes[$id] = $columnName;  // $name is 'attribute_1', which will clash with postgres
        //}

        // Check for duplicates. Will throw CPDBException if duplicate is found.
        $this->checkColumnDuplicates($surveyId, $newAttributes);

        [$addedAttributes, $addedAttributeIds] = $this->handleNewTokenAttributes($surveyId, $newAttributes);

        //Write each participant to the survey survey participant list
        [$successful, $duplicate, $blacklistSkipped] = $this->writeParticipantsToTokenTable(
            $surveyId,
            $participantIds,
            $mappedAttributes,
            $newAttributes,
            $addedAttributes,
            $addedAttributeIds,
            $options
        );

        $returndata = [
            'success'          => $successful,
            'duplicate'        => $duplicate,
            'blacklistskipped' => $blacklistSkipped,
            'overwriteauto'    => $options['overwriteauto'],
            'overwriteman'     => $options['overwriteman']
        ];
        return $returndata;
    }

    /**
     * Updates a field in the survey participant list with a value from the participant attributes table
     *
     * @param int $surveyId Survey ID number
     * @param string $participantId unique key for the participant
     * @param int $participantAttributeId the unique key for the participant_attribute table
     * @param int $tokenFieldname fieldname in the survey participant list
     *
     * @return bool true/false
     */
    public function updateTokenAttributeValue($surveyId, $participantId, $participantAttributeId, $tokenFieldname)
    {
        // OBS: intval returns 0 at fail, but also at intval("0"). lolphp.
        if (intval($participantAttributeId) === 0) {
            throw new InvalidArgumentException(sprintf('$participantAttributeId has to be an integer. Given: %s (%s)', gettype($participantAttributeId), $participantAttributeId));
        }

        //Get the value from the participant_attribute field
        $val = Yii::app()->db
            ->createCommand()
            ->select('value')
            ->where('participant_id = :participant_id AND attribute_id = :attrid')
            ->from('{{participant_attribute}}')
            ->bindParam("participant_id", $participantId, PDO::PARAM_STR)
            ->bindParam("attrid", $participantAttributeId, PDO::PARAM_INT);
        $value = $val->queryRow();

        //Update the token entry with those values
        if (isset($value['value'])) {
            $data = array($tokenFieldname => $value['value']);
            Yii::app()->db
                ->createCommand()
                ->update("{{tokens_$surveyId}}", $data, "participant_id = '$participantId'");
        }
        return true;
    }

    /**
     * Updates or creates a field in the survey participant list with a value from the participant attributes table
     *
     * @param int $surveyId Survey ID number
     * @param int $participantId unique key for the participant
     * @param int $participantAttributeId the unique key for the participant_attribute table
     * @param int $tokenFieldname fieldname in the survey participant list
     *
     * @return boolean|null true/false
     */
    public function updateAttributeValueToken($surveyId, $participantId, $participantAttributeId, $tokenFieldname)
    {
        $survey = Survey::model()->findByPk($surveyId);
        $val = Yii::app()->db
            ->createCommand()
            ->select($tokenFieldname)
            ->where('participant_id = :participant_id')
            ->from($survey->tokensTableName)
            ->bindParam("participant_id", $participantId, PDO::PARAM_STR);
        $value2 = $val->queryRow();

        if (!empty($value2[$tokenFieldname])) {
            $data = array(
                'participant_id' => $participantId,
                'value' => $value2[$tokenFieldname],
                'attribute_id' => $participantAttributeId
            );
            //Check if value already exists
            $test = Yii::app()->db
                ->createCommand()
                ->select('count(*) as count')
                ->from('{{participant_attribute}}')
                ->where('participant_id = :participant_id AND attribute_id= :attribute_id')
                ->bindParam(":participant_id", $participantId, PDO::PARAM_STR)
                ->bindParam(":attribute_id", $participantAttributeId, PDO::PARAM_INT)
                ->queryRow();
            if ($test['count'] > 0) {
                Yii::app()->db
                    ->createCommand()
                    ->update('{{participant_attribute}}', array("value" => $value2[$tokenFieldname]), "participant_id='$participantId' AND attribute_id=$participantAttributeId");
            } else {
                Yii::app()->db
                    ->createCommand()
                    ->insert('{{participant_attribute}}', $data);
            }
        }
    }

    /**
     * Copies token participants to the central participant list, and also copies
     * token attribute values where applicable. It checks for matching entries using
     * firstname/lastname/email combination.
     *
     * TODO: Most of this belongs in the participantsaction.php controller file, not
     *       here in the model file. Portions of this should be moved out at some stage.
     *
     * @param int $surveyid The id of the survey, used to find the appropriate tokens table
     * @param array $aAttributesToBeCreated An array containing the names of token attributes that have to be created in the cpdb
     * @param array $aMapped An array containing the names of token attributes that are to be mapped to an existing cpdb attribute
     * @param bool $overwriteauto If true, overwrites existing automatically mapped attribute values
     * @param bool $overwriteman If true, overwrites manually mapped attribute values (where token fieldname=attribute_n)
     * @param bool $createautomap If true, updates tokendescription field with new mapping
     * @return array An array contaning list of successful and list of failed ids
     */
    public function copyToCentral($surveyid, $aAttributesToBeCreated, $aMapped, $overwriteauto = false, $overwriteman = false, $createautomap = true)
    {
        $survey = Survey::model()->findByPk($surveyid);
        $tokenid_string = Yii::app()->session['participantid']; //List of token_id's to add to participant list
        $tokenids = json_decode((string) $tokenid_string, true);
        $duplicate = 0;
        $sucessfull = 0;
        $attid = []; //Will store the CPDB attribute_id of new or existing attributes keyed by CPDB at

        $aTokenAttributes = decodeTokenAttributes($survey->attributedescriptions ?? '');
        $aAutoMapped = $survey->getCPDBMappings();

        $diContainer = \LimeSurvey\DI::getContainer();
        $attributeService = $diContainer->get(
            LimeSurvey\Models\Services\ParticipantsAttributeService::class
        );

        /* Create CPDB attributes */
        if (!empty($aAttributesToBeCreated)) {
            foreach ($aAttributesToBeCreated as $key => $value) {
                $attid[$key] = $attributeService->saveParticipantAttribute($aTokenAttributes[$key], urldecode((string) $value));
            }
        }

        /* Add the participants to the CPDB = Iterate through each $tokenid and create the new CPDB id*/
        if (!is_array($tokenids)) {
            $tokenids = (array)$tokenids;
        }
        foreach ($tokenids as $tid) {
            if (is_numeric($tid) && $tid != "") {
                /* Get the data for this participant from the tokens table */
                $oTokenDynamic = TokenDynamic::model($survey->sid)->findByPk($tid);
                if (isset($oTokenDynamic) && $oTokenDynamic) {
                    $oTokenDynamic->decrypt();
                }

                /* See if there are any existing CPDB entries that match on firstname,lastname and email */
                $participantCriteria = new CDbCriteria();
                $participantCriteria->addCondition('firstname = :firstname');
                $participantCriteria->addCondition('lastname = :lastname');
                $participantCriteria->addCondition('email = :email');
                $participantCriteria->params = [
                    ":firstname" => $oTokenDynamic->firstname,
                    ":lastname"  => $oTokenDynamic->lastname,
                    ":email"     => $oTokenDynamic->email,
                ];
                $existing = Participant::model()->find($participantCriteria);
                /* If there is already an existing entry, add to the duplicate count */
                if ($existing != null) {
                    $duplicate++;
                    if ($overwriteman && !empty($aMapped)) {
                        foreach ($aMapped as $cpdbatt => $tatt) {
                            Participant::model()->updateAttributeValueToken($surveyid, $existing->participant_id, $cpdbatt, $tatt);
                        }
                    }
                    if ($overwriteauto && !empty($aAutoMapped)) {
                        foreach ($aAutoMapped as $cpdbatt => $tatt) {
                            Participant::model()->updateAttributeValueToken($surveyid, $existing->participant_id, $cpdbatt, $tatt);
                        }
                    }
                } /* If there isn't an existing entry, create one! */ else {
                    /* Create entry in participant list */
                    $black = !empty($oTokenDynamic->blacklisted) ? $oTokenDynamic->blacklisted : 'N';
                    $pid = !empty($oTokenDynamic->participant_id) ? $oTokenDynamic->participant_id : $this->genUuid();

                    $writearray = [
                        'participant_id' => $pid,
                        'firstname'      => $oTokenDynamic->firstname,
                        'lastname'       => $oTokenDynamic->lastname,
                        'email'          => $oTokenDynamic->email,
                        'language'       => $oTokenDynamic->language,
                        'blacklisted'    => $black,
                        'owner_uid'      => Yii::app()->session['loginID'],
                        'created_by'     => Yii::app()->session['loginID'],
                        'created'        => date('Y-m-d H:i:s', time())
                    ];
                    $oParticipant = new Participant();
                    $oParticipant->setAttributes($writearray, false);
                    $oParticipant->encryptSave();

                    //Update survey participant list and insert the new UUID
                    $oTokenDynamic->participant_id = $pid;
                    $oTokenDynamic->encryptSave();

                    /* Now add any new attribute values */
                    if (!empty($aAttributesToBeCreated)) {
                        foreach ($aAttributesToBeCreated as $key2 => $value) {
                            Participant::model()->updateAttributeValueToken($surveyid, $pid, $attid[$key2], $key2);
                        }
                    }
                    /* Now add mapped attribute values */
                    if (!empty($aMapped)) {
                        foreach ($aMapped as $cpdbatt => $tatt) {
                            Participant::model()->updateAttributeValueToken($surveyid, $pid, $cpdbatt, $tatt);
                        }
                    }
                    $sucessfull++;

                    /* Create a survey_link */
                    $oSurveyLink = new SurveyLink();
                    $data = [
                        'participant_id' => $pid,
                        'token_id'       => $tid,
                        'survey_id'      => $surveyid,
                        'date_created'   => date('Y-m-d H:i:s', time())
                    ];
                    $oSurveyLink->setAttributes($data, false);
                    $oSurveyLink->save(false);
                }
            }
        }

        if ($createautomap == "true") {
            $aAttributes = Survey::model()->findByPk($surveyid)->tokenattributes;
            if (!empty($aAttributesToBeCreated)) {
                // If automapping is enabled then update the token field properties with the mapped CPDB field ID
                foreach ($aAttributesToBeCreated as $tatt => $cpdbatt) {
                    $aAttributes[$tatt]['cpdbmap'] = $cpdbatt;
                }
                Yii::app()->db
                    ->createCommand()
                    ->update('{{surveys}}', ["attributedescriptions" => json_encode($aAttributes)], 'sid = ' . $surveyid);
            }
            if (!empty($aMapped)) {
                foreach ($aMapped as $cpdbatt => $tatt) {
                    // Update the attributedescriptions so future mapping can be done automatically
                    $aAttributes[$tatt]['cpdbmap'] = $cpdbatt;
                }
                Yii::app()->db
                    ->createCommand()
                    ->update('{{surveys}}', ["attributedescriptions" => json_encode($aAttributes)], 'sid = ' . $surveyid);
            }
        }
        $returndata = ['success' => $sucessfull, 'duplicate' => $duplicate, 'overwriteauto' => $overwriteauto, 'overwriteman' => $overwriteman];
        return $returndata;
    }

    /**
     * The purpose of this function is to check for duplicate in participants
     * @param string $fields
     * @param string $output
     * @return string
     */
    public function checkforDuplicate($fields, $output = "bool")
    {
        $query = Yii::app()->db->createCommand()
            ->select('participant_id')
            ->where($fields)
            ->from('{{participants}}')
            ->queryAll();
        if (count($query) > 0) {
            if ($output == "bool") {
                return true;
            }
            return $query[0][$output];
        } else {
            return false;
        }
    }

    /**
     * @param array $data
     * @return void
     */
    public function insertParticipantCSV($data)
    {
        $insertData = array(
            'participant_id' => $data['participant_id'],
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'language' => $data['language'],
            'blacklisted' => $data['blacklisted'],
            'created_by' => $data['owner_uid'],
            'owner_uid' => $data['owner_uid'],
            'created' => date('Y-m-d H:i:s', time())
        );
        Yii::app()->db->createCommand()->insert('{{participants}}', $insertData);
    }

    /**
     * Returns true if logged in user has edit rights to this participant
     * @return boolean
     */
    public function userHasPermissionToEdit()
    {
        $userId = Yii::app()->user->id;

        $shared = ParticipantShare::model()->findByAttributes(
            ['participant_id' => $this->participant_id],
            'share_uid = :userid AND can_edit = :can_edit',
            [':userid' => $userId, ':can_edit' => '1']
        );
        $owner = $this->owner_uid == $userId;

        if (Permission::model()->hasGlobalPermission('superadmin') || (Permission::model()->hasGlobalPermission('participantpanel', 'update'))) {
            // Superadmins can do anything and users with global edit permission can to edit all participants
            return true;
        } elseif ($shared && $shared->share_uid == -1 && $shared->can_edit) {
            // -1 = shared with everyone
            return true;
        } elseif ($shared && $shared->exists('share_uid = :userid', [':userid' => $userId]) && $shared->can_edit) {
            // Shared with this particular user
            return true;
        } elseif ($owner) {
            // User owns this participant
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns true if user is either owner of this participant or superadmin
     * Used to decide is user can change owner of participant
     * @return boolean
     */
    public function isOwnerOrSuperAdmin()
    {
        $userId = Yii::app()->user->id;
        $owner = $this->owner_uid == $userId;
        $isSuperAdmin = Permission::model()->hasGlobalPermission('superadmin');

        return $owner || $isSuperAdmin;
    }

    /**
     * 'created' field formatted; empty string if no timestamp in database
     * @return string
     */
    public function getCreatedFormatted()
    {
        if ($this->created) {
            $timestamp = strtotime($this->created);
            $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
            $date = date($dateformatdetails['phpdate'], $timestamp);
            return $date;
        } else {
            return '';
        }
    }

    /**
     * Get Language Options.
     **/
    public function getLanguageOptions()
    {
        $allLanguages = getLanguageData();
        $restrictToLanguages = Yii::app()->getConfig('restrictToLanguages');
        if (empty($restrictToLanguages)) {
            return array_map(function ($lngArr) {
                return $lngArr['description'];
            }, $allLanguages);
        }
        $usedLanguages = explode(' ', (string) $restrictToLanguages);
        $returner = [];
        array_walk($usedLanguages, function ($lngKey) use (&$returner, $allLanguages) {
            $returner[$lngKey] = $allLanguages[$lngKey]['description'];
        });
        return $returner;
    }

    /**
     * Get Owner Options
     **/
    public function getOwnerOptions()
    {
        return CHtml::listData(User::model()->findAll(), 'uid', 'full_name');
    }

    /**
     * Get current surveyId for other model/function
     * @return int
     */
    public function getSurveyId()
    {
        return self::$sid;
    }

    /**
     * decodes the tokenencryptionoptions to be used anywhere necessary
     * @return Array
     */
    public static function getParticipantsEncryptionOptions()
    {
        $aOptions = ParticipantAttributeName::model()->findAll();
        if (empty($aOptions)) {
            $aOptions = Participant::getDefaultEncryptionOptions();
            return $aOptions;
        }

        $aOptionReturn['enabled'] = 'Y';
        foreach ($aOptions as $key => $value) {
            $aOptionReturn['columns'][$value['defaultname']] = $value['encrypted'];
        }
        return $aOptionReturn;
    }

    /**
     * Return Default Encryption Options
     * @return array
     */
    public static function getDefaultEncryptionOptions()
    {
        return array(
            'enabled' => 'N',
            'columns' => array(
                'firstname' =>  'N',
                'lastname' =>  'N',
                'email' =>  'N'
            )
        );
    }

    /**
     * Checks Permissions for given $aActions and returns them as array
     *
     * @param array $aActions
     * @param array $permissions
     *
     * @return array
     */
    public function permissionCheckedActionsArray(array $aActions, array $permissions): array
    {
        $checkedActions = [];
        foreach ($aActions as $aAction) {
            if (isset($aAction['action'])) {
                switch ($aAction['action']) {
                    case 'delete':
                        if ($permissions['participantpanel']['isOwner'] || $permissions['superadmin']['read'] || $permissions['participantpanel']['delete'] || !$permissions['participantpanel']['sharedParticipantExists']) {
                            array_push($checkedActions, $aAction);
                        }
                        break;
                    case 'batchEdit':
                        if (
                            $permissions['participantpanel']['isOwner'] || $permissions['superadmin']['read']
                            || $permissions['participantpanel']['update']
                            || $permissions['participantpanel']['editSharedParticipants']
                            || !$permissions['participantpanel']['sharedParticipantExists']
                        ) {
                            array_push($checkedActions, $aAction);
                        }
                        break;
                    case 'export':
                        if ($permissions['participantpanel']['isOwner'] || $permissions['superadmin']['read'] || $permissions['participantpanel']['export'] || !$permissions['participantpanel']['sharedParticipantExists']) {
                            array_push($checkedActions, $aAction);
                        }
                        break;
                    case 'share':
                        if (
                            $permissions['participantpanel']['isOwner']
                            || $permissions['superadmin']['read']
                            || $permissions['participantpanel']['update']
                            || !$permissions['participantpanel']['sharedParticipantExists']
                            || $permissions['participantpanel']['editSharedParticipants']
                        ) {
                            array_push($checkedActions, $aAction);
                        }
                        break;
                    case 'add-to-survey':
                        array_push($checkedActions, $aAction);
                        break;
                    default:
                }
            } elseif (isset($aAction['type']) && isset(end($checkedActions)['action'])) {
                if ($aAction['type'] === 'separator' && end($checkedActions)['action'] === 'delete') {
                    array_push($checkedActions, $aAction);
                }
            }
        }
        return $checkedActions;
    }

    /**
     * Returns the list of blocklisted participant IDs
     * @return string[]
     */
    public function getBlacklistedParticipantIds()
    {
        $command = new CDbCriteria();
        $command->condition = '';
        $command->addCondition("blacklisted = 'Y'");

        $oResult = $this->getCommandBuilder()
            ->createFindCommand($this->getTableSchema(), $command)
            ->select('participant_id')
            ->queryColumn();
        return $oResult;
    }
}
