<?php
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
namespace ls\models;

use ls\models\ActiveRecord;
use ls\models\Survey;

/**
 * Class ls\models\QuestionGroup
 * @property string $randomization_group
 * @property Question[] $questions
 * @property string $grelevance
 * @property Survey $survey
 */
class QuestionGroup extends ActiveRecord
{
    public $before;

    /**
     * Returns the setting's table name to be used by the model
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{groups}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'translatable' => [
                'class' => \SamIT\Yii1\Behaviors\TranslatableBehavior::class,
                'translationModel' => Translation::class,
                'attributes' => ['group_name', 'description'],
                'baseLanguage' => function (QuestionGroup $group) {
                    return $group->survey->language;
                }
            ]
        ]);
    }

    /**
     * Returns this model's validation rules
     *
     */
    public function rules()
    {
        return [
            ['group_name,description', 'required'],
            ['group_name', 'required']
        ];
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
            'questions' => [
                self::HAS_MANY,
                Question::class,
                'gid',
                'on' => 'parent_qid = 0',
                'order' => 'questions.question_order',
                'index' => 'qid'
            ],
            'assessments' => [self::HAS_MANY, Assessment::class, 'gid'],
            'survey' => [self::BELONGS_TO, 'ls\models\Survey', 'sid']
        ];

    }

    public function getQuestionCount()
    {
        return Question::model()->countByAttributes(['gid' => $this->id, 'parent_qid' => 0]);
    }

    function updateGroupOrder($sid, $lang, $position = 0)
    {
        $data = Yii::app()->db->createCommand()->select('gid')
            ->where(array('and', 'sid=:sid', 'language=:language'))
            ->order('group_order, group_name ASC')
            ->from('{{groups}}')
            ->bindParam(':sid', $sid, PDO::PARAM_INT)
            ->bindParam(':language', $lang, PDO::PARAM_STR)
            ->query();

        $position = intval($position);
        foreach ($data->readAll() as $row) {
            Yii::app()->db->createCommand()->update($this->tableName(), array('group_order' => $position),
                'gid=' . $row['gid']);
            $position++;
        }
    }


    /**
     * This function is here to support proper naming of entity attributes.
     * Since surveys have a title, I have decided all non-person entities have a title not a name.
     * @return string
     */
    public function getTitle()
    {
        return $this->group_name;
    }

    public function getDisplayLabel()
    {
        return $this->group_name;
    }


    /**
     * Returns the relations that map to dependent records.
     * Dependent records should be deleted when this object gets deleted.
     * @return string[]
     */
    public function dependentRelations()
    {
        $result = [
            'questions',
            'assessments'
        ];
        if (isset($this->metaData->relations['translations'])) {
            $result[] = 'translations';
        }

        return $result;

    }

    /**
     * Deletes this record and all dependent records.
     * @throws CDbException
     */
    public function deleteDependent()
    {
        if (App()->db->getCurrentTransaction() == null) {
            $transaction = App()->db->beginTransaction();
        }
        foreach ($this->dependentRelations() as $relation) {
            /** @var CActiveRecord $record */
            foreach ($this->$relation as $record) {
                if (method_exists($record, 'deleteDependent')) {
                    $record->deleteDependent();
                } else {
                    $record->delete();
                }
            }
        }
        $this->delete();

        if (isset($transaction)) {
            $transaction->commit();
        }
    }

    public function isRelevant(\ls\interfaces\iResponse $response)
    {
        if (empty($this->grelevance)) {
            $result = true;
        } else {
            throw new \Exception("Group relevance not yet supported.");
        }

        return $result;
    }

    public function getRelevanceScript()
    {
        return true;
    }

    /**
     * Gets the replacements for group info.
     * @return array
     */
    public function getReplacements()
    {
        return [
            'GROUPNAME' => $this->title,
            'GROUPDESCRIPTION' => $this->description
        ];
    }
}

