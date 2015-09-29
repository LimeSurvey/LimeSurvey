<?php
namespace ls\models;

use CActiveRecord;
use ls\models\ActiveRecord;

class Assessment extends ActiveRecord
{
    /**
     * Returns the static model of Settings table
     *
     * @static
     * @access public
     * @param string $class
     * @return CActiveRecord
     */
    public static function model($class = __CLASS__)
    {
        return parent::model($class);
    }

    public function rules()
    {
        return array(
            array('name,message', 'LSYii_Validators'),
        );
    }

    /**
     * Returns the setting's table name to be used by the model
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{assessments}}';
    }

    /**
     * Returns the primary key of this table
     *
     * @access public
     * @return string
     */
    public function primaryKey()
    {
        return array('id', 'language');
    }

    public static function insertRecords($data)
    {
        $assessment = new self;

        foreach ($data as $k => $v) {
            $assessment->$k = $v;
        }
        $assessment->save();

        return $assessment;
    }

    public static function updateAssessment($id, $iSurveyID, $language, array $data)
    {
        $assessment = self::model()->findByAttributes(array('id' => $id, 'sid' => $iSurveyID, 'language' => $language));
        if (!is_null($assessment)) {
            foreach ($data as $k => $v) {
                $assessment->$k = $v;
            }
            $assessment->save();
        }
    }
}

