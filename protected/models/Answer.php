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
namespace ls\models;

use ls\models\ActiveRecord;
use ls\models\Question;
use ls\models\QuestionGroup;

/**
 * Class ls\models\Answer
 * @property string $answer
 * @property Question $question
 * @property string $code
 */
class Answer extends ActiveRecord implements \ls\interfaces\iAnswer
{
    /**
     * Returns the setting's table name to be used by the model
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{answers}}';
    }


    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'translatable' => [
                'class' => \SamIT\Yii1\Behaviors\TranslatableBehavior::class,
                'translationModel' => Translation::class,
                'model' => __CLASS__, // See TranslatableBehavior comments.
                'attributes' => ['answer'],
                /**
                 * @todo Refactor this so we don't get a lot of queries. Alternatively cache the query.
                 */
                'baseLanguage' => function (Answer $answer) {
                    return $answer->question->survey->language;
                }
            ]
        ]);
    }

    /**
     * Defines the relations for this model
     *
     * @access public
     * @return array
     */
    public function relations()
    {
        return [
            'question' => [self::BELONGS_TO, Question::class, 'question_id'],
            'groups' => [self::BELONGS_TO, QuestionGroup::class, 'gid', 'through' => 'question']
        ];
    }

    /**
     * Returns this model's validation rules
     *
     */
    public function rules()
    {
        return array(
            [
                'question_id',
                \CExistValidator::class,
                'className' => Question::class,
                'attributeName' => 'qid',
                'on' => ['update', 'insert']
            ],
            ['code', 'length', 'min' => 1, 'max' => 5, 'allowEmpty' => false],
            ['code', 'required'],
            [
                'code',
                'match',
                'pattern' => '/^[a-z0-9]*$/i',
                'message' => gT('ls\models\Answer codes may only contain alphanumeric characters.')
            ],
            ['sortorder', 'numerical', 'integerOnly' => true, 'allowEmpty' => true],
            ['answer', 'length'],
            array('assessment_value', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
            array('scale_id', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
        );
    }

    public function init()
    {
        $this->code = 'A1';
    }

    /**
     * Return the key=>value answer for a given $qid
     *
     * @staticvar array $answerCache
     * @param type $qid
     * @param type $code
     * @param type $sLanguage
     * @param type $iScaleID
     * @return array
     */
    function getAnswerFromCode($qid, $code, $sLanguage, $iScaleID = 0)
    {
        static $answerCache = array();

        if (array_key_exists($qid, $answerCache)
            && array_key_exists($code, $answerCache[$qid])
            && array_key_exists($sLanguage, $answerCache[$qid][$code])
            && array_key_exists($iScaleID, $answerCache[$qid][$code][$sLanguage])
        ) {
            // We have a hit :)
            return $answerCache[$qid][$code][$sLanguage][$iScaleID];
        } else {
            $answerCache[$qid][$code][$sLanguage][$iScaleID] = Yii::app()->db->cache(6)->createCommand()
                ->select('answer')
                ->from(self::tableName())
                ->where(array('and', 'qid=:qid', 'code=:code', 'scale_id=:scale_id', 'language=:lang'))
                ->bindParam(":qid", $qid, PDO::PARAM_INT)
                ->bindParam(":code", $code, PDO::PARAM_STR)
                ->bindParam(":lang", $sLanguage, PDO::PARAM_STR)
                ->bindParam(":scale_id", $iScaleID, PDO::PARAM_INT)
                ->query()->readAll();

            return $answerCache[$qid][$code][$sLanguage][$iScaleID];
        }
    }

    public function oldNewInsertansTags($newsid, $oldsid)
    {
        $criteria = new CDbCriteria;
        $criteria->compare('questions.sid', $newsid);
        $criteria->compare('answer', '{INSERTANS::' . $oldsid . 'X');

        return $this->with('questions')->findAll($criteria);
    }

    /**
     * Updates sort order of answers inside a question
     *
     * @static
     * @access public
     * @param int $qid
     * @param string $lang
     * @return void
     */
    public static function updateSortOrder($qid, $lang)
    {
        $data = self::model()->findAllByAttributes(array('qid' => $qid, 'language' => $lang),
            array('order' => 'sortorder asc'));
        $position = 0;

        foreach ($data as $row) {
            $row->sortorder = $position++;
            $row->save();
        }
    }

    public function getAnswerQuery($surveyid, $lang, $return_query = true)
    {
        $query = Yii::app()->db->createCommand();
        $query->select("{{answers}}.*, {{questions}}.gid");
        $query->from("{{answers}}, {{questions}}");
        $query->where("{{questions}}.sid = :surveyid AND {{questions}}.qid = {{answers}}.qid AND {{questions}}.language = {{answers}}.language AND {{questions}}.language = :lang");
        $query->order('qid, code, sortorder');
        $query->bindParams(":surveyid", $surveyid, PDO::PARAM_INT);
        $query->bindParams(":lang", $lang, PDO::PARAM_STR);

        return ($return_query) ? $query->queryAll() : $query;
    }

    function getAllRecords($condition, $order = false)
    {
        $command = Yii::app()->db->createCommand()->select('*')->from($this->tableName())->where($condition);
        if ($order != false) {
            $command->order($order);
        }

        return $command->query();
    }

    public function getQuestionsForStatistics($fields, $condition, $orderby)
    {
        return self::getDbConnection()->createCommand()
            ->select($fields)
            ->from(self::tableName())
            ->where($condition)
            ->order($orderby)
            ->queryAll();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->answer;
        // TODO: Implement getLabel() method.
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}

