<?php
namespace ls\import\importers;


use ls\import\BaseElementXmlImport;
use ls\import\BaseImport;
use SamIT\Yii1\Behaviors\TranslatableBehavior;

class Import178 extends BaseElementXmlImport{

    /**
     * @return Survey|false
     * @throws \CDbException
     * @throws \Exception
     */
    public function run()
    {
        $transaction = App()->db->beginTransaction();
        try {
            /** @var \ls\models\Survey $survey */
            $result = $this->importSurvey($this->parsedDocument);
        } catch(\Exception $e) {
            $transaction->rollback();
            throw $e;
        }
        if ($result !== null) {
            $transaction->commit();
        } else {
            $transaction->rollback();
        }
        return $result;



    }

    protected function importTranslation(TranslatableBehavior $translatable, array $data) {
        bP();
        $translatedFields = [];
        foreach ($translatable->attributes as $attribute) {
            if (isset($data[$attribute])) {
                $translatedFields[$attribute] = $data[$attribute];
            }
        }
        if (!empty($translatedFields)) {
            $translation = new \ls\models\Translation();
            $translation->language = $data['language'];
            $translation->model = $translatable->getModel();
            $translation->model_id = $translatable->owner->primaryKey;
            $translation->dataStore = $translatedFields;
            if (!$translation->save()) {
                throw new \Exception("Failed to save group translation.");
            }

        }
        eP();
    }
    protected function prepareGroup(array $data, \ls\models\Survey $survey) {
        // Translate gid.

        $data['id'] = isset($data['gid']) ? $data['gid'] : $data['id'];
        $data['sid'] = $survey->primaryKey;
        unset($data['gid']);
        unset($data['language']);
        return $data;
    }
    protected function importGroup(array $data, \ls\models\Survey $survey, array &$questionMap) {
        $group = new \ls\models\QuestionGroup();
        // Set related model.
        $group->survey = $survey;
        $data = $this->prepareGroup($data, $survey);
        $translations = \TbArray::popValue('translations', $data, []);
        $questions = \TbArray::popValue('questions', $data, []);
        foreach($data as $key => $value) {
            if (!($group->canSetProperty($key) || $group->hasAttribute($key))) {
                throw new \Exception("Could not set property $key");
            }
        }
        $group->setAttributes($data, false);
        $oldKey = $group->primaryKey;
        $group->primaryKey = null;
        if (!$group->save()) {
            throw new \Exception("Could not save group. " . print_r($data, true));
        }
        $group->survey = $survey;
        foreach($translations as $translation) {
            $this->importTranslation($group->translatable, $translation, $group->primaryKey);
        }

        foreach($questions as $question) {
            $this->importQuestion($question, $group, $questionMap);
        }
    }

    /**
     * @param $data
     * @return mixed
     * @throws \Exceptions
     */
    protected function importSurvey($data) {
        $survey = new \ls\models\Survey();

        $surveyTranslations = \TbArray::popValue('languagesettings', $data, []);
        $groups = \TbArray::popValue('groups', $data, []);

        foreach($data as $key => $value) {
            if (!($survey->canSetProperty($key) || $survey->hasAttribute($key))) {
                throw new \Exception("Could not set property $key");
            }
        }
        $survey->setAttributes($data, false);
        $oldKey = $survey->primaryKey;
        $survey->primaryKey = null;
        $questionMap = [];
        // Workaround for unknown templates.
        if (!$survey->validate(['template'])) {
            $survey->template = null;
        }
        if (!$survey->save()) {
            throw new \Exception("Could not save survey." . print_r($survey->errors, true));
        }
        foreach ($surveyTranslations as $surveyTranslation) {
            $this->importSurveyTranslation($surveyTranslation, $survey);
        }

        foreach ($groups as $group) {
            $this->importGroup($group, $survey, $questionMap);
        }

        return $survey;
    }

    protected function importSurveyTranslation($data, \ls\models\Survey $survey) {
        $languageSetting = new \ls\models\SurveyLanguageSetting();
        $languageSetting->survey = $survey;
        $languageSetting->setAttributes($data, false);
        $languageSetting->surveyls_survey_id = $survey->primaryKey;
        if (!$languageSetting->save()) {
            throw new \Exception("Failed to save survey translation.");
        }
    }

    protected function prepareQuestion($data, \ls\models\QuestionGroup $group, \ls\models\Question $parent = null) {
        \Yii::beginProfile('prepareQuestion');
        unset($data['language']);
        $data['gid'] = $group->primaryKey;
        $data['sid'] = $group->sid;
        $model = \ls\models\Question::model();
        $model->title = $data['title'];
        if (!$model->validate(['title'])) {
            $data['title'] = "q" . $data['title'];
        }
        if (isset($parent)) {
            $data['parent_qid'] = $parent->primaryKey;
        }
        \Yii::endProfile('prepareQuestion');
        return $data;
    }

    protected function importCondition($data, \ls\models\Question $question, array $questionMap)
    {
        \Yii::beginProfile('importCondition');
        $oldTargetQid = intval($data['cqid']);
        if (!isset($questionMap[$oldTargetQid])) {
            throw new \Exception("Could not convert condition.");
        }
        $newTargetQid = $questionMap[$oldTargetQid];
        $pattern = '/\d+X\d+X' . $oldTargetQid . '(?<field>.*)/';
        if (preg_match($pattern, $data['cfieldname'], $matches)) {
            $condition = new \Condition();
            $condition->setAttributes($data, false);
            $condition->qid = $question->primaryKey;
            $condition->cqid = $newTargetQid;
            $condition->primaryKey = null;
            $condition->cfieldname = "{$question->sid}X{$question->gid}X{$newTargetQid}{$matches['field']}";

            $result = $condition->save();
        } else {
            throw new \Exception("Pattern '$pattern' does not match {$data['cfieldname']}");
        }
        \Yii::endProfile('importCondition');
        return $result;
    }
    protected function importQuestion(array $data, \ls\models\QuestionGroup $group, array &$questionMap, \ls\models\Question $parent = null) {
        /**
         * If we only have 1 language, use it even if it is not the "base" language.
         */
        bP();
        $translations = \TbArray::popValue('translations', $data, []);
        $subQuestions = \TbArray::popValue('subquestions', $data, []);
        $conditions = \TbArray::popValue('conditions', $data, []);
        $answers = \TbArray::popValue('answers', $data, []);
        $data = $this->prepareQuestion($data, $group, $parent);
        // We want the "correct class".
        $class = \ls\models\Question::resolveClass($data['type']);
        /** @var \ls\models\Question $question */
        $question = new $class('import');
        $question->type = $data['type'];
        // Set related models.
        $question->group = $group;
        $question->survey = $group->survey;
        foreach($data as $key => $value) {
            if (!($question->canSetProperty($key) || $question->hasAttribute($key))) {
                throw new \Exception("Could not set property $key for " . get_class($question));
            } else {
                $question->$key = $value;
            }
        }
        $oldKey = $question->primaryKey;
        $question->primaryKey = null;
        $question->parent_qid = !isset($parent) ? 0 : $parent->primaryKey;
        \Yii::beginProfile('saveQuestion');
        $result = $question->save();
        \Yii::endProfile('saveQuestion');
        if ($result) {
            $question->group = $group;
            $questionMap[$oldKey] = $question->primaryKey;
            foreach($translations as $translation) {
                $this->importTranslation($question->translatable, $translation, $question->primaryKey);
            }

            foreach($subQuestions as $subQuestion) {
                $result = $result && $this->importQuestion($subQuestion, $group, $questionMap, $question);
            }

            foreach($conditions as $condition) {
                $result = $result && $this->importCondition($condition, $question, $questionMap);
            }
            foreach($answers as $answer) {
                $result = $result && $this->importAnswer($answer, $question);
            }
        } else {
            die('failed importing question');
        }
        eP();
        return $result;
    }

    protected function prepareAnswer($data, \ls\models\Question $question) {
        unset($data['qid']);
        $data['question_id'] = $question->primaryKey;
        unset($data['language']);
        return $data;
    }
    protected function importAnswer($data, \ls\models\Question $question)
    {
        \Yii::beginProfile('importAnswer');
        $answer = new \ls\models\Answer('import');
        // Set related model.
        $answer->question = $question;
        $translations = \TbArray::popValue('translations', $data, []);

        $data = $this->prepareAnswer($data, $question);

        foreach($data as $key => $value) {
            if (!($answer->canSetProperty($key) || $answer->hasAttribute($key))) {
                throw new \Exception("Could not set property $key");
            }
            $answer->$key = $value;
        }
//        $answer->setAttributes($data, false);
        $answer->primaryKey = null;
        \Yii::beginProfile('answerQuery');
        if ($result = $answer->save()) {
            foreach ($translations as $translation) {
                $result = $result && $this->importTranslation($answer->translatable, $translation, $answer->primaryKey);
            }
        }

        \Yii::endProfile('answerQuery');
        \Yii::endProfile('importAnswer');
        return $result;
    }
}