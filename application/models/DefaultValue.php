<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
     *	Files Purpose: lots of common functions
*/

class DefaultValue extends LSActiveRecord
{
    /* Default value when create (from DB) , leave some because add rules */
    public $specialtype='';
    public $scale_id='';
    public $sqid=0;
    public $language='';// required ?

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

    /**
     * Returns the setting's table name to be used by the model
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{defaultvalues}}';
    }

    /**
     * Returns the primary key of this table
     *
     * @access public
     * @return string[]
     */
    public function primaryKey()
    {
        return array('qid', 'specialtype', 'scale_id', 'sqid', 'language');
    }

    /**
    * Relations with questions
    *
    * @access public
    * @return array
    */
    public function relations()
    {
        $alias = $this->getTableAlias();
        return array(
            'question' => array(self::HAS_ONE, 'Question', '',
            'on' => "$alias.qid = question.qid",
            ),
        );
    }
    /**
    * Returns this model's validation rules
    *
    */
    public function rules()
    {
        return array(
            array('qid', 'required'),
            array('qid', 'numerical','integerOnly'=>true),
            array('qid', 'unique', 'criteria'=>array(
                    'condition'=>'specialtype=:specialtype and scale_id=:scale_id and sqid=:sqid and language=:language',
                    'params'=>array(
                        ':specialtype'=>$this->specialtype,
                        ':scale_id'=>$this->scale_id,
                        ':sqid'=>$this->sqid,
                        ':language'=>$this->language,
                    )
                ),
                'message'=>'{attribute} "{value}" is already in use.'),
        );
    }
    function insertRecords($data)
    {
        $oRecord = new self;
        foreach ($data as $k => $v)
            $oRecord->$k = $v;
        if($oRecord->validate())
            return $oRecord->save();
        tracevar($oRecord->getErrors());
    }
}
?>
