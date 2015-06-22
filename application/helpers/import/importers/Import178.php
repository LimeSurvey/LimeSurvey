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

    public function run()
    {
//        var_dump($this->parsedDocument);
        $transaction = App()->db->beginTransaction();

        try {
            $groupMap = [];
            /** @var \Survey $survey */
            list($oldKey, $survey) = $this->importSurvey($this->parsedDocument['surveys']['rows']['row']);
            $groups = [];
            foreach($this->parsedDocument['groups']['rows']['row'] as $group) {
                if ($group['language'] != $survey->language) {
                    $groups[$group['gid']]['translations'][] = $group;
                } else {
                    $groups[$group['gid']] = array_merge(isset($groups[$group['gid']]) ? $groups[$group['gid']] : [], $group);
                }
            }

            foreach($groups as $group) {
                list($oldId, $group) = $this->importGroup($group, $survey);
                $groupMap[$oldId] = $group->primaryKey;
            }


            $questions = [];
            foreach($this->parsedDocument['questions']['rows']['row'] as $question) {
                if ($question['language'] != $survey->language) {
                    $questions[$question['qid']]['translations'][] = $question;
                } else {
                    $questions[$question['gid']] = array_merge(isset($questions[$question['gid']]) ? $questions[$question['gid']] : [], $question);
                }
            }

            foreach($questions as $question) {
                list($oldId, $question) = $this->importQuestion($question, $groupMap, $survey);
                $questionMap[$oldId] = $question->primaryKey;
            }


        } catch(\Exception $e) {
            $transaction->rollback();
            throw $e;
        }
        // during testing always rollback
        $transaction->rollback();




    }

    protected function importTranslation(TranslatableBehavior $translatable, array $data, $groupId) {
        $translatedFields = [];
        foreach ($translatable->attributes as $attribute) {
            if (isset($data[$attribute])) {
                $translatedFields = $data[$attribute];
            }
        }
        if (!empty($translatedFields)) {
            $translation = new \Translation();
            $translation->model = $translatable->getModel();
            $translation->model_id = $groupId;
            $translation->dataStore = $translatedFields;
            if (!$translation->save()) {
                throw new \Exception("Failed to save group translation.");
            }

        }
    }
    protected function prepareGroup(array $data, \Survey $survey) {
        // Translate gid.
        $data['id'] = $data['gid'];
        unset($data['gid']);
        unset($data['language']);
        return $data;
    }
    protected function importGroup(array $data, \Survey $survey) {
        $group = new \QuestionGroup();
        $data = $this->prepareGroup($data, $survey);
        $translations = \TbArray::popValue('translations', $data, []);
        if (is_array($data)) {
            foreach($data as $key => $value) {
                if (!($group->canSetProperty($key) || $group->hasAttribute($key))) {
                    throw new \Exception("Could not set property $key");
                }
            }
            $group->setAttributes($data, false);
            $group->sid = $survey->primaryKey;
            $result[0] = $group->primaryKey;
            $group->primaryKey = null;
            if (!$group->save()) {
                throw new \Exception('Group could not be validated or saved.');
            }
            $result[1] = $group;
            foreach($translations as $translation) {
                $this->importTranslation($group->translatable, $translation, $group->primaryKey);
            }
            return $result;
        }
    }
    protected function importSurvey($data) {
        $survey = new \Survey();

        foreach($data as $key => $value) {
            if (!($survey->canSetProperty($key) || $survey->hasAttribute($key))) {
                throw new \Exception("Could not set property $key");
            }
        }
        $survey->setAttributes($data, false);
        $result[0] = $survey->primaryKey;
        $survey->primaryKey = null;
        if (!$survey->save()) {
            throw new \Exception('Survey could not be validated or saved.');
        }
        $result[1] = $survey;

        return $result;


//        var_dump($survey);
//        var_dump($surveyNode->chC
    }

    protected function prepareQuestion($data, $groupMap, \Survey $survey) {
        // Translate gid.
//        $data['id'] = $data['gid'];
//        unset($data['gid']);
        unset($data['language']);
        $data['gid'] = $groupMap[$data['gid']];
        $data['sid'] = $survey->sid;
        return $data;
    }
    protected function importQuestion($data, $groupMap, \Survey $survey) {
        /**
         * If we only have 1 language, use it even if it is not the "base" language.
         */
        if (count($data) == 1 && isset($data['translations']) && count($data['translations']) == 1) {
            $data = $data['translations'][0];
        }
        $translations = \TbArray::popValue('translations', $data, []);
        $data = $this->prepareQuestion($data, $groupMap, $survey);
        $question = new \Question();
        foreach($data as $key => $value) {
            if (!($question->canSetProperty($key) || $question->hasAttribute($key))) {
                throw new \Exception("Could not set property $key");
            }
        }
        $question->setAttributes($data, false);
        $result[0] = $question->primaryKey;
        $question->primaryKey = null;
        $question->parent_qid = 0;
        if (!$question->save()) {
            throw new \Exception('Question could not be validated or saved.');
        }
        $result[1] = $question;
        foreach($translations as $translation) {
            $this->importTranslation($question->translatable, $translation, $question->primaryKey);
        }
        return $result;
    }
}