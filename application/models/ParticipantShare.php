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

use ls\ajax\AjaxHelper;

/**
 * This is the model class for table "{{participant_shares}}".
 *
 * The following are the available columns in table '{{participant_shares}}':
 * @property string $participant_id
 * @property integer $share_uid
 * @property string $date_added
 * @property string $can_edit
 *
 * @property Participant $participant
 * @property User $shared_by
 * @property SurveyLink $survey_links //TODO should be singular
 * @property ParticipantAttribute[] $participantAttributes
 */
class ParticipantShare extends LSActiveRecord
{
    public $ownerName;

    /**
     * @inheritdoc
     * @return ParticipantShare
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
        return '{{participant_shares}}';
    }

    /** @inheritdoc */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('participant_id, share_uid, date_added, can_edit', 'required'),
            array('share_uid', 'numerical', 'integerOnly' => true),
            array('participant_id', 'length', 'max' => 50),
            array('can_edit', 'length', 'max' => 5),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('participant_id, participant.firstname, participant.lastname, participant.email, share_uid, date_added, can_edit', 'safe', 'on' => 'search'),
        );
    }

    /** @inheritdoc */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'participant' => array(self::HAS_ONE, 'Participant', array('participant_id' => 'participant_id')),
            'shared_by' => array(self::HAS_ONE, 'User', array('uid' => 'share_uid')),
            'surveylinks' => array(self::HAS_ONE, 'SurveyLink', 'participant_id'),
            'participantAttributes' => array(self::HAS_MANY, 'ParticipantAttribute', 'participant_id', 'with' => 'participant_attribute_names', 'joinType' => 'LEFT JOIN')
        );
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return array(
            'participant_id' => 'Participant',
            'share_uid' => 'Share Uid',
            'date_added' => 'Date Added',
            'can_edit' => 'Can Edit',
        );
    }

    /**
     * @param integer $selected
     * @return string html dropdown
     */
    public function getSharedByList($selected)
    {
        $share_uids = Yii::app()->db->createCommand()
            ->selectDistinct('share_uid')
            ->from('{{participant_shares}}')
            ->queryAll();

        $shareList = array(
            '' => '', // No filter
            '-1' => gT('Everybody')
        );

        foreach ($share_uids as $id) {
            if ($id['share_uid'] == -1) {
                continue;
            }
            /** @var User $oUser */
            $oUser = User::model()->findByPk($id['share_uid']);
            $shareList[$id['share_uid']] = $oUser->full_name;
        }
        return TbHtml::dropDownList('ParticipantShare[share_uid]', $selected, $shareList);
    }

    /**
     * @return string
     */
    public function getSharedBy()
    {
        if ($this->share_uid == -1) {
            return gT('Everybody');
        } else {
            return $this->shared_by['full_name'];
        }
    }

    /**
     * @return string HTML
     */
    public function getCanEditHtml()
    {
        $loggedInUser = yii::app()->user->getId();
        if ($this->participant->owner_uid == $loggedInUser) {
            $inputHtml = App()->getController()->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                'name'          => 'canedithtml_' . $this->participant_id . "_" . $this->share_uid,
                'checkedOption' => $this->can_edit ? "1" : "0",
                'selectOptions' => [
                    '1' => gT('Yes'),
                    '0' => gT('No'),
                ],
                'htmlOptions'   => [
                    'class' => 'action_changeEditableStatus'
                ]
            ], true);
            return $inputHtml;
        }

        return ($this->can_edit ? gT("Yes") : gT('No'));
    }

    /**
     * Action buttons
     * @return string HTML
     */
    public function getButtons()
    {
        $permission_superadmin_read = Permission::model()->hasGlobalPermission('superadmin', 'read');
        $userId = App()->user->id;
        $isOwner = $this->participant->owner_uid == $userId;
            $url = App()->createUrl(
                'admin/participants/sa/deleteSingleParticipantShare',
                [
                    'participantId' => urlencode($this->participant_id),
                    'shareUid'      => $this->share_uid
                ]
            );

        $dropdownItems = [];
        $dropdownItems[] = [
            'title' => gT('Delete sharing'),
            'linkClass' => 'action_delete_shareParticipant',
            'iconClass' => 'ri-delete-bin-fill text-danger',
            'linkAttributes' => [
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#confirmation-modal',
                'data-title'     => gT('Unshare this participant'),
                'data-btntext'   => gT('Unshare'),
                'data-message'   => gT('Do you really want to unshare this participant?'),
                'data-onclick'   => "(function() { LS.CPDB.deleteSingleParticipantShare(\"$url\")})",
            ],
            'enabledCondition' => $isOwner || $permission_superadmin_read
        ];

        return App()->getController()->widget('ext.admin.grid.GridActionsWidget.GridActionsWidget', ['dropdownItems' => $dropdownItems], true);
    }

    /**
     * Massive action checkbox
     * @return string html
     */
    public function getCheckbox()
    {
        $userId = Yii::app()->user->id;
        $participant = Participant::model()->findByPk($this->participant_id);
        $isOwner = $participant->owner_uid == $userId;
        $isSuperAdmin = Permission::model()->hasGlobalPermission('superadmin', 'read');

        // Primary key for ParticipantShare
        $participantIdAndShareUid = $this->participant_id . ',' . $this->share_uid;

        if ($isOwner || $isSuperAdmin) {
            $html = "<input type='checkbox' class='selector_participantShareCheckbox' name='selectedParticipantShare[]' value='" . $participantIdAndShareUid . "' >";
        } else {
            $html = '';
        }

        return $html;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        $participantFilter = yii::app()->request->getPost('Participant');
        $cols = [
            [
                "name"              => 'checkbox',
                "type"              => 'raw',
                "header"            => "<input type='checkbox' id='action_toggleAllParticipantShare' />",
                "filter"            => false,
                'filterHtmlOptions' => ['class' => 'ls-sticky-column'],
                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                'htmlOptions'       => ['class' => 'ls-sticky-column'],
            ],
            [
                "name"   => 'participant.lastname',
                "header" => gT("Last name"),
                "filter" => TbHtml::textField("Participant[lastname]", $participantFilter['lastname'] ?? '')
            ],
            [
                "name"   => 'participant.firstname',
                "header" => gT("First name"),
                "filter" => TbHtml::textField("Participant[firstname]", $participantFilter['firstname'] ?? '')
            ],
            [
                "name"   => 'participant.email',
                "header" => gT("Email address"),
                "filter" => TbHtml::textField("Participant[email]", $participantFilter['email'] ?? '')
            ],
            [
                "name"   => 'share_uid',
                "value"  => '$data->sharedBy',
                "type"   => 'raw',
                "header" => gT("Shared with"),
                "filter" => $this->getSharedByList($this->share_uid)
            ],
            [
                'name'   => 'ownerName',
                'value'  => '$data->getOwnerName()',
                'header' => 'Owner'
            ],
            [
                "name"   => 'date_added',
                "header" => gT("Date added")
            ],
            [
                "name"   => 'can_edit',
                "value"  => '$data->getCanEditHtml()',
                "header" => gT("Can edit?"),
                "filter" => [1 => gT('Yes'), 0 => gT('No')],
                "type"   => "raw"
            ],
            [
                "name"              => 'buttons',
                "type"              => 'raw',
                "header"            => gT("Action"),
                "filter"            => false,
                'filterHtmlOptions' => ['class' => 'ls-sticky-column'],
                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                'htmlOptions'       => ['class' => 'ls-sticky-column'],
            ],
        ];
        return $cols;
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.
        $sort = new CSort();
        $sortAttributes = array(
            'participant.firstname' => array(
                'asc' => 'participant.firstname asc',
                'desc' => 'participant.firstname desc',
            ),
            'participant.lastname' => array(
                'asc' => 'participant.lastname asc',
                'desc' => 'participant.lastname desc',
            ),
            'participant.email' => array(
                'asc' => 'participant.email asc',
                'desc' => 'participant.email desc',
            ),
            'share_uid' => array(
                'asc' => 'shared_by.full_name asc',
                'desc' => 'shared_by.full_name desc',
            ),
            'date_added' => array(
                'asc' => 'date_added asc',
                'desc' => 'date_added desc',
            ),
            'can_edit' => array(
                'asc' => 'can_edit asc',
                'desc' => 'can_edit desc',
            ),
        );
        $sort->attributes = $sortAttributes;
        $sort->defaultOrder = 'participant.firstname ';

        $participantFilter = Yii::app()->request->getPost('Participant');

        $criteria = new CDbCriteria();
        $criteria->with = array('participant', 'shared_by');

        // This condition is necessary to filter out participants that got deleted, but the share entry is not
        $criteria->addCondition('participant.participant_id = t.participant_id');

        $criteria->compare('share_uid', $this->share_uid);
        $criteria->compare('date_added', $this->date_added, true);
        $criteria->compare('can_edit', $this->can_edit, true);
        if (!empty($participantFilter)) {
            $criteria->compare('participant.lastname', $participantFilter['lastname'], true);
            $criteria->compare('participant.firstname', $participantFilter['firstname'], true);
            $criteria->compare('participant.email', $participantFilter['email'], true);
        }

        $pageSize = Yii::app()->user->getState('pageSizeShareParticipantView', Yii::app()->params['defaultPageSize']);
        return new LSCActiveDataProvider($this, array(
            'criteria' => $criteria,
            'sort' => $sort,
            'pagination' => array(
                'pageSize' => $pageSize
            )
        ));
    }

    /**
     * @param array $data
     * @param array $permission
     *
     * @return void
     * @throws CException
     */
    public function storeParticipantShare($data, $permission)
    {
        $hasUpdatePermission = $permission['hasUpdatePermission'] ?? false;
        $isSuperAdmin = $permission['isSuperAdmin'] ?? false;
        $userId = App()->user->getId();
        $ownerid = App()->db->createCommand()->select('*')->from('{{participants}}')->where('participant_id = :participant_id')->bindParam(":participant_id", $data['participant_id'], PDO::PARAM_STR)->queryRow();

        // Check if share already exists
        $arShare = $this->findByPk(['participant_id' => $data['participant_id'], 'share_uid' => $data['share_uid']]);
        $canEditShared = $this->canEditSharedParticipant($data['participant_id']);
        $isOwner = $ownerid['owner_uid'] == $userId;

        if ($ownerid['owner_uid'] == $data['share_uid'] || (!$permission && !$canEditShared && !$isOwner && !$isSuperAdmin && !$hasUpdatePermission)) {
            ls\ajax\AjaxHelper::outputNoPermission();
            return;
        }
        if (is_null($arShare)) {
            // A check to ensure that the participant is not added to it's owner
            $oParticipantShare = new ParticipantShare();
            $oParticipantShare->setAttributes($data);
            $oParticipantShare->save();
        } else {
            $this->updateShare($data);
        }
    }

    /**
     * @param array $data
     * @return void
     */
    public function updateShare($data)
    {
        if (strpos((string) $data['participant_id'], '--') !== false) {
            list($participantId, $shareuid) = explode("--", (string) $data['participant_id']);
            $data = array("participant_id" => $participantId, "share_uid" => $shareuid, "can_edit" => $data['can_edit']);
        }
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['participant_id' => $data['participant_id'],'share_uid' => $data['share_uid']]);
        ParticipantShare::model()->updateAll($data, $criteria);
    }

    /**
     * @param string $rows Comma-separated list of something
     * @return void
     */
    public function deleteRow($rows)
    {
        // Converting the comma separated id's to an array to delete multiple rows
        $rowid = explode(",", $rows);
        foreach ($rowid as $row) {
            list($participantId, $uId) = explode("--", $row);
            Yii::app()->db
                ->createCommand()
                ->delete(
                    '{{participant_shares}}',
                    sprintf(
                        "participant_id = '%d' AND share_uid = %d",
                        (int) $participantId,
                        (int) $uId
                    )
                );
        }
    }

    /**
     * Full name of the owner of the participant that is shared
     * @return string
     */
    public function getOwnerName()
    {
        return $this->participant->owner->full_name;
    }

    /**
     * Returns true if the user is allowed to edit the participant
     *
     * @param $participent_id
     *
     * @return boolean
     */
    public function canEditSharedParticipant($participent_id)
    {
        $participent = $this->findByAttributes(
            ['participant_id' => $participent_id],
            'can_edit = :can_edit AND share_uid = :userid',
            [
                ':userid' => App()->user->id,
                ':can_edit' => '1'
            ]
        );
        if ($participent) {
            return true;
        }
        return false;
    }
}
