<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
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
 * @property string $value;
 * @property int $attribute_id;
 * @property string $participant_id;
 */
class ParticipantAttribute extends LSActiveRecord
{

    /**
     * Returns the setting's table name to be used by the model
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{participant_attribute}}';
    }

    /**
     * Returns the primary key of this table
     *
     * @access public
     * @return string
     */
    public function primaryKey()
    {
        return ['participant_id', 'attribute_id'];
    }

    public function relations()
    {
        return [
            'name' => [self::BELONGS_TO, ParticipantAttributeName::class, 'attribute_id']
        ];
    }

    function getAttributeInfo($participantid)
    {
        return self::model()->findAllByAttributes(array('participant_id' => $participantid));
    }
    function updateParticipantAttributeValue($data)
    {
        $query = Yii::app()->db->createCommand()->select('*')->where('participant_id="'.$data['participant_id'].'" AND attribute_id = '.$data['attribute_id'])->from('{{participant_attribute}}')->queryAll();
        if (count($query) > 0)
        {
            Yii::app()->db->createCommand()
                  ->update('{{participant_attribute}}', $data, 'participant_id = "'.$data['participant_id'].'" AND attribute_id = '.$data['attribute_id']);
        } else {
            Yii::app()->db->createCommand()
                  ->insert('{{participant_attribute}}', $data);
        }
    }

}

?>
