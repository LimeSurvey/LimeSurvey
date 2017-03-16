<?php

/**
 * Relations
 * @property Token $token
 * @property Survey $survey
 */
abstract class Response extends Dynamic
{
    /** @inheritdoc */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $this->deleteFiles();
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     * @param string|integer $className Either the classname or the survey id.
     * @return Response
     */
    public static function model($className = null)
    {
        return parent::model($className);
    }

    /**
     *
     * @param int $surveyId
     * @param string $scenario
     * @return string Response Description
     */
    public static function create($surveyId, $scenario = 'insert')
    {
        return parent::create($surveyId, $scenario);
    }

    /**
     * Delete all files related to this response.
     * @return array
     */
    public function getFiles()
    {

        $questions = Question::model()->findAllByAttributes(array('sid' => $this->dynamicId,'type' => '|','language'=>getBaseLanguageFromSurveyID($this->dynamicId)));
        $files = array();
        foreach ($questions as $question) {

            $field = "{$question->sid}X{$question->gid}X{$question->qid}";
            $data = json_decode(stripslashes($this->getAttribute($field)), true);
            if (is_array($data)) {
                $files = array_merge($files, $data);
            }
        }

        return $files;
    }

    public function deleteFiles()
    {
        $uploaddir = Yii::app()->getConfig('uploaddir') ."/surveys/{$this->dynamicId}/files/";
        foreach ($this->getFiles() as $fileInfo) {
            @unlink($uploaddir . basename($fileInfo['filename']));
        }
    }

    /** @inheritdoc */
    public function delete($deleteFiles = false)
    {
        if ($deleteFiles) {
            $this->deleteFiles();
        }
        return parent::delete();
    }

    /** @inheritdoc */
    public function relations()
    {
        $t = $this->getTableAlias();
        $result = array(
            'token' => array(self::BELONGS_TO, 'Token_' . $this->dynamicId, array('token' => 'token')),
            'survey' =>  array(self::BELONGS_TO, 'Survey', '', 'on' => "sid = {$this->dynamicId}" )
        );
        return $result;
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{survey_' . $this->dynamicId . '}}';
    }

    /**
     * @return int
     */
    public function getSurveyId()
    {
        return $this->dynamicId;
    }

    public function browse()
    {

    }

    public function search()
    {

    }
}
