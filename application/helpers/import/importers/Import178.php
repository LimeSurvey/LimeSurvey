<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 6/18/15
 * Time: 1:45 PM
 */

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
            /** @var \Survey $survey */
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
        $translatedFields = [];
        foreach ($translatable->attributes as $attribute) {
            if (isset($data[$attribute])) {
                $translatedFields[$attribute] = $data[$attribute];
            }
        }
        if (!empty($translatedFields)) {
            $translation = new \Translation();
            $translation->language = $data['language'];
            $translation->model = $translatable->getModel();
            $translation->model_id = $translatable->owner->primaryKey;
            $translation->dataStore = $translatedFields;
            if (!$translation->save()) {
                throw new \Exception("Failed to save group translation.");
            }

        }
        return true;
    }
    protected function prepareGroup(array $data, \Survey $survey) {
        // Translate gid.
        $data['id'] = $data['gid'];
        $data['sid'] = $survey->primaryKey;
        unset($data['gid']);
        unset($data['language']);
        return $data;
    }
    protected function importGroup(array $data, \Survey $survey, array &$questionMap) {
        $group = new \QuestionGroup();
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
        if ($result = $group->save()) {
            $group->survey = $survey;
            foreach($translations as $translation) {
                $result = $result && $this->importTranslation($group->translatable, $translation, $group->primaryKey);
            }
            foreach($questions as $question) {
                $result = $result && $this->importQuestion($question, $group, $questionMap);
            }

        } else {
            var_dump($group->errors);
            die();
        }
        return $result;

    }

    /**
     * @param $data
     * @return mixed
     * @throws \Exceptions
     */
    protected function importSurvey($data) {
        $survey = new \Survey();

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
        if ($result = $survey->save()) {

            foreach ($surveyTranslations as $surveyTranslation) {
                $result = $result && $this->importSurveyTranslation($surveyTranslation, $survey);
            }
            foreach ($groups as $group) {
                $result = $result && $this->importGroup($group, $survey, $questionMap);
            }
        }

        return $result ? $survey : null;
    }

    protected function importSurveyTranslation($data, \Survey $survey) {
        $languageSetting = new \SurveyLanguageSetting();
        $languageSetting->setAttributes($data, false);
        $languageSetting->surveyls_survey_id = $survey->primaryKey;
        if (false === $result = $languageSetting->save()) {
            throw new \Exception("Failed to save survey translation.");
        }
        return $result;
    }

    protected function prepareQuestion($data, \QuestionGroup $group, \Question $parent = null) {
        // Translate gid.
//        $data['id'] = $data['gid'];
//        unset($data['gid']);
        unset($data['language']);
        $data['gid'] = $group->primaryKey;
        $data['sid'] = $group->sid;
        $model = \Question::model();
        $model->title = $data['title'];
        if (!$model->validate(['title'])) {
            $data['title'] = "q" . $data['title'];
        }
        if (isset($parent)) {
            $data['parent_qid'] = $parent->primaryKey;
        }
        return $data;
    }

    protected function importCondition($data, \Question $question, array &$questionMap)
    {
        throw new \Exception('Condition import not finished');
        return true;
        $data['qid'] = $question->primaryKey;
        $data['cqid'] = $questionMap[$data['cqid']];
        return $data;
    }
    protected function importQuestion(array $data, \QuestionGroup $group, array &$questionMap, \Question $parent = null) {
        /**
         * If we only have 1 language, use it even if it is not the "base" language.
         */
        $translations = \TbArray::popValue('translations', $data, []);
        $subQuestions = \TbArray::popValue('subquestions', $data, []);
        $conditions = \TbArray::popValue('conditions', $data, []);
        $answers = \TbArray::popValue('answers', $data, []);
        $data = $this->prepareQuestion($data, $group, $parent);
        // We want the "correct class".
        $class = \Question::resolveClass($data['type']);
        /** @var \Question $question */
        $question = new $class();
        $question->type = $data['type'];
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

        if ($result = $question->save()) {
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
            var_dump($data);
            var_dump($question->attributes);
            var_dump($question->errors);
            die('failed importing question');
        }
        return $result;
    }

    protected function prepareAnswer($data, \Question $question) {
        unset($data['qid']);
        $data['question_id'] = $question->primaryKey;
        unset($data['language']);
        return $data;
    }
    protected function importAnswer($data, \Question $question)
    {
        $answer = new \Answer();
        $translations = \TbArray::popValue('translations', $data, []);

        $data = $this->prepareAnswer($data, $question);

        foreach($data as $key => $value) {
            if (!($answer->canSetProperty($key) || $answer->hasAttribute($key))) {
                throw new \Exception("Could not set property $key");
            }
        }
        $answer->setAttributes($data, false);
        $answer->primaryKey = null;
        if ($result = $answer->save()) {
            $answer->question = $question;

            foreach ($translations as $translation) {
                $result = $result && $this->importTranslation($answer->translatable, $translation, $answer->primaryKey);
            }
        } else {
            var_dump($answer->errors);
            die();
        }
        if (!$result) {
            echo "Failed to import answer";
        }
        return $result;
    }
}