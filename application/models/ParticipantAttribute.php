<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
 * LimeSurvey
 * Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * 	$Id: SurveyLink.php 11999 2012-01-12 10:26:32Z gautamgupta $
 * 	Files Purpose: lots of common functions
 */

/**
 * Class ParticipantAttribute
 *
 * @property integer $participant_id
 * @property integer $attribute_id
 * @property string $value
 *
 * @property Participant $participant
 * @property ParticipantAttributeName $participant_attribute_name
 */
class ParticipantAttribute extends LSActiveRecord
{

    /**
     * @inheritdoc
     * @return ParticipantAttribute
     */
    public static function model($class = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($class);
        return $model;
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{participant_attribute}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return array('participant_id', 'attribute_id');
    }

    /** @inheritdoc */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'participant' => array(self::HAS_ONE, 'Participant', 'participant_id'),
            'participant_attribute_name'=>array(self::BELONGS_TO, 'ParticipantAttributeName', 'attribute_id')
        );
    }

    /**
     * @param string $participantid
     * @return array
     */
    public function getAttributeInfo($participantid)
    {
        return self::model()->findAllByAttributes(array('participant_id' => $participantid));
    }

    /**
     * @param array $data
     * @return void
     */
    public function updateParticipantAttributeValue($data)
    {
        $query = Yii::app()->db->createCommand()
            ->select('*')
            ->where("participant_id='".$data['participant_id']."' AND attribute_id = ".$data['attribute_id'])
            ->from('{{participant_attribute}}')
            ->queryAll();
        if (count($query) > 0) {
            Yii::app()->db->createCommand()
                    ->update('{{participant_attribute}}', $data, "participant_id = '".$data['participant_id']."' AND attribute_id = ".$data['attribute_id']);
        } else {
            Yii::app()->db->createCommand()
                    ->insert('{{participant_attribute}}', $data);
        }
    }

}
