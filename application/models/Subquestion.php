<?php
if (!defined('BASEPATH')) {
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
*/

use \LimeSurvey\Helpers\questionHelper;

/**
 * Class Question
 *
 * @property integer $qid Question ID.
 * @property integer $sid Survey ID
 * @property integer $gid QuestionGroup ID where question is displayed
 * @property string $type
 * @property string $title Question Code
 * @property string $preg
 * @property string $other Other option enabled for question (Y/N)
 * @property string $mandatory Whether question is mandatory (Y/S/N)
 * @property string $encrypted Whether question is encrypted (Y/N)
 * @property integer $question_order Question order in greoup
 * @property integer $parent_qid Questions parent question ID eg for subquestions
 * @property integer $scale_id  The scale ID
 * @property integer $same_default Saves if user set to use the same default value across languages in default options dialog
 * @property string $relevance Questions relevane equation
 * @property string $modulename
 *
 * @property Survey $survey
 * @property QuestionGroup $groups  //@TODO should be singular
 * @property Question $parents      //@TODO should be singular
 * @property Question[] $subquestions
 * @property QuestionAttribute[] $questionAttributes NB! returns all QuestionArrtibute Models fot this QID regardless of the specified language
 * @property QuestionL10n[] $questionL10ns Question Languagesettings indexd by language code
 * @property string[] $quotableTypes Question types that can be used for quotas
 * @property Answer[] $answers
 * @property QuestionType $questionType
 * @property array $allSubQuestionIds QID-s of all question sub-questions, empty array returned if no sub-questions
 * @inheritdoc
 */
class Subquestion extends LSActiveRecord
{
    /** @var string $group_name Stock the active group_name for questions list filtering */
    public $group_name;
    public $gid;

    /**
     * @inheritdoc
     * @return Question
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
        return '{{questions}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'qid';
    }

    /** @inheritdoc */
    public function relations()
    {
        return array(
            'survey' => array(self::BELONGS_TO, 'Survey', 'sid'),
            'group' => array(self::BELONGS_TO, 'QuestionGroup', 'gid', 'together' => true),
            'parent' => array(self::HAS_ONE, 'Question', array("qid" => "parent_qid")),
            'questionAttributes' => array(self::HAS_MANY, 'QuestionAttribute', 'qid'),
            'questionL10ns' => array(self::HAS_MANY, 'QuestionL10n', 'qid', 'together' => true),
            'conditions' => array(self::HAS_MANY, 'Condition', 'qid'),
        );
    }
}