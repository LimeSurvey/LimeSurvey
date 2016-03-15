<?php
namespace ls\models;

use \Yii;

/**
 * Relations
 * @property Token $tokenObject
 * @property int $surveyId
 * @property Question[] $questions
 * @property Survey $survey
 */
abstract class Response extends Dynamic implements \ls\interfaces\ResponseInterface
{
    private $_attributeLabels = [];
    private $_questions;


    public function __set($name, $value)
    {
        if ($value instanceof \Psr\Http\Message\UploadedFileInterface) {
            $this->setFile($name, $value);
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * Stores a file with the response.
     * @param $field
     * @param \Psr\Http\Message\UploadedFileInterface[] $file
     */
    public function setFiles($field, array $files)
    {
        // First check if the question type for the field is actually an upload question.
        // Get the question id from the field name.

        if (preg_match('/^\\d+X\\d+X(\\d+)$/', $field, $matches)) {
            $question = Question::model()->findByPk($matches[1]);
            if ($question->type == Question::TYPE_UPLOAD) {
                $directory = App()->runtimePath . "/responses/{$this->dynamicId}";
                if (!is_dir($directory)) {
                    vd(mkdir($directory, null, true));
                }
                $base = "$directory/{$this->getId()}_";
                /** @var \Psr\Http\Message\UploadedFileInterface $file */
                $meta = [];
                foreach ($files as $file) {
                    if ($file->getSize() > 0) {
                        $extension = pathinfo($file->getClientFilename())['extension'];
                        $targetPath = $base . App()->securityManager->generateRandomString(10) . '.' . strtolower($extension);
                        $file->moveTo($targetPath);
                        $meta[] = [
                            'filename' => $targetPath,
                            'size' => $file->getSize(),
                            'name' => $file->getClientFilename()
                        ];
                    }
                }
                // Set count.
                $this->setAttribute($field . "_filecount", count($meta));
                // Set metadata
                $this->setAttribute($field, json_encode($meta));
            }
        }

    }

    /**
     * @return Question[]
     */
    public function getQuestions()
    {
        if (!isset($this->_questions)) {
            $this->_questions = Question::model()->findAllByAttributes([
                'sid' => $this->surveyId,
                'parent_qid' => 0
            ], ['index' => 'title']);
        }

        return $this->_questions;
    }

    /**
     * This adds support for translating SGQA to a label that uses the question code.
     * This is not really efficient since it gets all question codes individually.
     * @param string $attribute
     * @return string
     */
    public function attributeLabels()
    {
        if (empty($this->_attributeLabels)) {
            foreach ($this->survey->groups as $group) {
                foreach ($group->questions as $question) {
                    foreach ($question->fields as $field) {
                        $this->_attributeLabels[$field->name] = $field->code;
                    }
                }
            }
        }

        return $this->_attributeLabels;
    }


    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $this->deleteFiles();

            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getSurveyId()
    {
        return $this->dynamicId;
    }


    /**
     * Delete all files related to this response.
     */
    public function getFiles()
    {
        $questions = Question::model()->findAllByAttributes([
            'sid' => $this->dynamicId,
            'type' => Question::TYPE_UPLOAD
        ]);
        $files = [];
        foreach ($questions as $question) {
            if (false !== $data = json_decode(stripslashes($this->getAttribute($question->sgqa)), true)) {
                $files = array_merge($files, $data);
            }
        }

        return $files;
    }

    public function deleteFiles()
    {
        $dir = Yii::app()->getConfig('uploaddir') . "/surveys/{$this->dynamicId}/files/";
        foreach ($this->getFiles() as $fileInfo) {
            @unlink($dir . basename($fileInfo['filename']));
        }
    }

    public function delete($deleteFiles = false)
    {
        if ($deleteFiles) {
            $this->deleteFiles();
        }

        return parent::delete();
    }

    public function relations()
    {
        return [
            // Since we have a field named token as well.
            'tokenObject' => [self::BELONGS_TO, Token::class . '_' . $this->dynamicId, ['token' => 'token']],
            'survey' => [self::BELONGS_TO, Survey::class, '', 'on' => "sid = {$this->dynamicId}"],
        ];
    }

    public function rules()
    {
        $rules = [
            ['id', 'default', 'value' => \Cake\Utility\Text::uuid()],
        ];
        if ($this->hasAttribute('series_id')) {
            $rules[] = ['series_id', 'default', 'value' => \Cake\Utility\Text::uuid()];
        }
        if ($this->hasAttribute('datestamp')) {

        }


        $rules = array_merge($rules, $this->surveyRules);


        return $rules;
    }

    /**
     * An array containing validation rules for the response data.
     */
    protected function getSurveyRules()
    {
        // For now mark all as safe.
        $attributes = array_filter($this->attributeNames(), function ($attribute) {
            return preg_match('/\d+X\d+X\d+.*/', $attribute);
        });

        return [[$attributes, 'safe']];
    }

    public function scopes()
    {
        return [
            'complete' => [
                'condition' => 'submitdate IS NOT NULL',
            ],
            'incomplete' => [
                'condition' => 'submitdate IS NULL'
            ]
        ];
    }

    public static function constructTableName($id)
    {
        return '{{survey_' . $id . '}}';
    }


    public static function createTable(Survey $survey, &$messages = [])
    {
        $db = App()->db;
        //Check for any additional fields for this survey and create necessary fields (token and datestamp)
        $columns = $survey->columns;
        $tableName = "{{survey_{$survey->sid}}}";

        $createdTables = [];
        // Check if table exists with same column names.
        if ((null !== $table = $db->schema->getTable($tableName,
                    true)) && Response::model($survey->sid)->count() == 0
        ) {
            // Table exists but is empty..
            $db->createCommand()->dropTable($tableName);
            unset($table);
            $messages[] = gT("Old empty response table deleted.");
        } elseif (isset($table) && $table->columnNames !== array_keys($columns)) {
            // Table exists, but does not have the same columns / same order. This is fatal.
            throw new \Exception("The response table already exists and does not have the same columns as the one we were trying to create.");
        }

        if (!isset($table)) {
            // Table does not exist, create it.
            $db->createCommand()->createTable($tableName, $columns);
            $db->createCommand()->addPrimaryKey('', $tableName, ['id']);
            $createdTables[] = $tableName;
            $messages[] = gT("Response table created.");
        }

        if ($survey->bool_usetokens) {
            App()->db->createCommand()->createIndex("token_{$survey->sid}", $tableName, ['token']);
        }

        // Refresh the table schema
        App()->db->schema->getTable($tableName, true);
    }

    /**
     * Create a new response that belongs to the same series.
     * If copy is true then the response data from this response is copied to the new one.
     * @return self
     */
    public function append($copy = false)
    {
        $result = self::create($this->surveyId);
        $result->series_id = $this->series_id;
        if ($copy) {
            $result->setAttributes($this->attributes, false);
            // Unset progress information.
            $result->setAttributes([
                'submitdate' => null,
                'lastpage' => null,
                'id' => null
            ], false);
        }

        return $result;


    }


    /**
     * Returns the full answer for the question that matches $fieldName
     * and the answer that matches the $answerCode.  If a match cannot
     * be made then false is returned.
     *
     * The name of the variable $answerCode is not strictly an answerCode
     * but could also be a comment entered by a participant.
     *
     * @param string $fieldName
     * @param string $answerCode
     * @param string $language
     * @return string
     */
    public function getLongAnswer($fieldName)
    {
        if (!preg_match('/\^d+X\d+X\d+.*$/', $fieldName)) {
            return $this->$fieldName;
        }
        $fullAnswer = null;
        $fieldType = $this->survey;
        $question = $this->fieldMap[$fieldName];
        $questionId = $question['qid'];
        $answer = null;
        if ($questionId) {
            $answers = $this->getAnswers($questionId);
            if (isset($answers[$answerCode])) {
                $answer = $answers[$answerCode]['answer'];
            }
        }

        //echo "\n$fieldName: $fieldType = $answerCode";
        switch ($fieldType) {
            case 'R':   //RANKING TYPE
                $fullAnswer = $answer;
                break;

            case '1':   //Array dual scale
                if (mb_substr($fieldName, -1) == 0) {
                    $answers = $this->getAnswers($questionId, 0);
                } else {
                    $answers = $this->getAnswers($questionId, 1);
                }
                if (array_key_exists($answerCode, $answers)) {
                    $fullAnswer = $answers[$answerCode]['answer'];
                } else {
                    $fullAnswer = null;
                }
                break;

            case 'L':   //DROPDOWN LIST
            case '!':
                if (mb_substr($fieldName, -5, 5) == 'other') {
                    $fullAnswer = $answerCode;
                } else {
                    if ($answerCode == '-oth-') {
                        $fullAnswer = $translator->translate('Other', $sLanguageCode);
                    } else {
                        $fullAnswer = $answer;
                    }
                }
                break;

            case 'O':   //DROPDOWN LIST WITH COMMENT
                if (isset($answer)) {
                    //This is one of the dropdown list options.
                    $fullAnswer = $answer;
                } else {
                    //This is a comment.
                    $fullAnswer = $answerCode;
                }
                break;

            case 'Y':   //YES/NO
                switch ($answerCode) {
                    case 'Y':
                        $fullAnswer = $translator->translate('Yes', $sLanguageCode);
                        break;

                    case 'N':
                        $fullAnswer = $translator->translate('No', $sLanguageCode);
                        break;

                    default:
                        $fullAnswer = $translator->translate('N/A', $sLanguageCode);
                }
                break;

            case 'G':
                switch ($answerCode) {
                    case 'M':
                        $fullAnswer = $translator->translate('Male', $sLanguageCode);
                        break;

                    case 'F':
                        $fullAnswer = $translator->translate('Female', $sLanguageCode);
                        break;

                    default:
                        $fullAnswer = $translator->translate('N/A', $sLanguageCode);
                }
                break;

            case 'M':   //MULTIOPTION
            case 'P':
                if (mb_substr($fieldName, -5, 5) == 'other' || mb_substr($fieldName, -7, 7) == 'comment') {
                    //echo "\n -- Branch 1 --";
                    $fullAnswer = $answerCode;
                } else {
                    if ($answerCode == 'Y') {
                        $fullAnswer = $translator->translate('Yes', $sLanguageCode);
                    } elseif ($answerCode == 'N' || $answerCode === '')   // Strict check for empty string to find null values
                    {
                        $fullAnswer = $translator->translate('No', $sLanguageCode);
                    } else {
                        $fullAnswer = $translator->translate('N/A', $sLanguageCode);
                    }
                }
                break;

            case 'C':
                switch ($answerCode) {
                    case 'Y':
                        $fullAnswer = $translator->translate('Yes', $sLanguageCode);
                        break;

                    case 'N':
                        $fullAnswer = $translator->translate('No', $sLanguageCode);
                        break;

                    case 'U':
                        $fullAnswer = $translator->translate('Uncertain', $sLanguageCode);
                        break;
                }
                break;

            case 'E':
                switch ($answerCode) {
                    case 'I':
                        $fullAnswer = $translator->translate('Increase', $sLanguageCode);
                        break;

                    case 'S':
                        $fullAnswer = $translator->translate('Same', $sLanguageCode);
                        break;

                    case 'D':
                        $fullAnswer = $translator->translate('Decrease', $sLanguageCode);
                        break;
                }
                break;

            case 'F':
            case 'H':
                $answers = $this->getAnswers($questionId, 0);
                $fullAnswer = (isset($answers[$answerCode])) ? $answers[$answerCode]['answer'] : "";
                break;

            default:

                $fullAnswer .= $answerCode;
        }

        return $fullAnswer;
    }

    /**
     * Sets the value for a specific question code to the passed value.
     * If code cannot be resolved to a column will call the $invalidCode callback.
     *
     * @param string $code
     * @param string $value
     * @param callable $invalidCode
     * @return boolean True if the value was set, false if it was not set (invalid code or invalid value);
     */
    public function setAnswer($code, $value, callable $invalidCode = null)
    {
        // Get column.
        $column = $this->getColumn($code);
        if (!$this->hasAttribute($column)) {
            $invalidCode();
            $result = false;
        } else {
            /**
             * @todo Validate the value.
             */
            $result = $this->setAttribute($column, $value);
        }

        return $result;

    }

    public function getColumn($code)
    {
        if (strpos($code, '_') !== false) {
            list($title, $rest) = explode('_', $code);
            $rest = "_" . $rest;
        } else {
            $title = $code;
            $rest = '';
        }
        // Get all questions.
        if (isset($this->questions[$title])) {
            $question = $this->questions[$title];
            // Check if what column we need.
            $column = "{$this->surveyId}X{$question->gid}X{$question->qid}";

            return $column;
        }

    }

    public function markAsFinished()
    {
        $this->submitdate = date("Y-m-d H:i:s");
    }


    public function markAsUnFinished()
    {
        $this->submitdate = null;
    }

    /**
     * Lists the behaviors of this model
     *
     * Below is a list of all behaviors we register:
     * @see CTimestampBehavior
     * @see PluginEventBehavior
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        if ($this->hasAttribute('datestamp') && $this->hasAttribute('startdate')) {
            $behaviors['ResponseTimeStamp'] = [
                'class' => 'zii.behaviors.CTimestampBehavior',
                'createAttribute' => 'startdate',
                'updateAttribute' => 'datestamp',
                'setUpdateOnCreate' => true
            ];
        }

        return $behaviors;
    }

    /**
     * Must be implemented because of iResponse.
     * @return string The UUID for this response.
     */
    public function getId()
    {
        return $this->getAttribute('id');
    }

    /**
     * This is from the iResponse interface.
     * @param string $id
     * @return static
     * @throws Exception
     */
    public static function loadById($id)
    {
        return static::model()->findByPk($id);
    }

    public function getToken()
    {
        if ($this->hasAttribute('token')) {
            return $this->getAttribute('token');
        }
    }

    public function getIsFinished()
    {
        return isset($this->submitdate);
    }

    public function setResponseValue($key, $value)
    {
        if ($this->isAttributeSafe($key)) {
            $this->$key = $value;
            return true;
        }
        return false;
    }
}


