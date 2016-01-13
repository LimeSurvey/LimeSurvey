<?php
namespace ls\models;
use CException;
use CHttpException;
use DateTime;
use ls\models\Dynamic;
use ls\models\Response;
use ls\models\Survey;
use Yii;

/**
 *
 * For code completion we add the available scenario's here
 * Attributes
 * @property int $tid
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property string $emailstatus
 * @property string $token
 * @property string $language
 * @property string $blacklisted
 * @property string $sent
 * @property string $remindersent
 * @property int $remindercount
 * @property string $completed
 * @property int $usesleft
 * @property DateTime $validfrom
 * @property DateTime $validuntil
 *
 * Relations
 * @property Survey $survey The survey this token belongs to.
 *
 * Scopes
 * @method Token incomplete() incomplete() Select only uncompleted tokens
 * @method Token usable() usable() Select usable tokens: valid daterange and userleft > 0
 *
 *
 * @method-static Token model()
 */
abstract class Token extends Dynamic
{
    /**
     * @var string Captcha used in registration scenario.
     */
    public $captcha;

    public function attributeLabels()
    {
        $labels = [
            'tid' => gT('Token ID'),
            'partcipant' => gt('Participant ID'),
            'firstname' => gT('First name'),
            'lastname' => gT('Last name'),
            'email' => gT('Email address'),
            'emailstatus' => gT('Email status'),
            'token' => gT('Token'),
            'language' => gT('Language code'),
            'blacklisted' => gT('Blacklisted'),
            'sent' => gT('Invitation sent date'),
            'remindersent' => gT('Last reminder sent date'),
            'remindercount' => gT('Total numbers of sent reminders'),
            'completed' => gT('Completed'),
            'usesleft' => gT('Uses left'),
            'validfrom' => gT('Valid from'),
            'validuntil' => gT('Valid until'),
        ];

        if (is_array($attributeDescriptions = json_decode($this->survey->attributedescriptions, true))) {
            foreach($attributeDescriptions as $key => $info) {
                $labels[$key] = $info['description'];
            }
        }
        return $labels;
    }



    public function init()
    {
        parent::init();
        if ($this->getScenario() == 'insert') {
            $this->usesleft = 1;
        }
    }

    public function beforeDelete()
    {
        $result = parent::beforeDelete();
        if ($result && isset($this->surveylink)) {
            if (!$this->surveylink->delete()) {
                throw new CException('Could not delete survey link. ls\models\Token was not deleted.');
            }

            return true;
        }

        return $result;
    }

    public static function createTable($surveyId, array $extraFields = [])
    {
        $surveyId = intval($surveyId);
        $fields = [
            'tid' => 'pk',
            'participant_id' => 'string(50)',
            'firstname' => 'string(40)',
            'lastname' => 'string(40)',
            'email' => 'text',
            'emailstatus' => 'text',
            'token' => "string(35)",
            'language' => 'string(25)',
            'blacklisted' => 'string(17)',
            'sent' => "string(17) DEFAULT 'N'",
            'remindersent' => "string(17) DEFAULT 'N'",
            'remindercount' => 'integer DEFAULT 0',
            'completed' => "string(17) DEFAULT 'N'",
            'usesleft' => 'integer DEFAULT 1',
            'validfrom' => 'datetime',
            'validuntil' => 'datetime',
            'mpid' => 'integer'
        ];
        foreach ($extraFields as $extraField) {
            $fields[$extraField] = 'text';
        }

        // create fields for the custom token attributes associated with this survey
        $customAttributeNames = Survey::model()->findByPk($surveyId)->getTokenAttributes();
        foreach ($customAttributeNames as $name => $details) {
            if (!isset($fields[$name])) {
                $fields[$name] = 'string(255)';
            }
        }

        /** @var \CDbConnection $db */
        $db = \Yii::app()->db;
        $tableName = self::constructTableName($surveyId);

        $db->createCommand()->createTable($tableName, $fields);
        /**
         * Random not needed for:
         * - PostgreSQL
         * - MySQL
         * - MSSQL
         *
         */
        $db->createCommand()->createIndex("idx_token_token_{$surveyId}_" . rand(1, 50000), $tableName, 'token', true);

        // Refresh schema cache just in case the table existed in the past, and return if table exist
        return $db->schema->getTable($tableName, true);
    }

    public function findByToken($token)
    {
        return $this->findByAttributes([
            'token' => $token
        ]);
    }

    /**
     * Generates a token for this object.
     * @throws CHttpException
     */
    public function generateToken()
    {
        $length = $this->survey->tokenlength;
        $this->token = \Yii::app()->securityManager->generateRandomString($length);
        $counter = 0;
        while (!$this->validate(['token'])) {
            $this->token = \Yii::app()->securityManager->generateRandomString($length);
            $counter++;
            // This is extremely unlikely.
            if ($counter > 10) {
                throw new CHttpException(500, 'Failed to create unique token in 10 attempts.');
            }
        }
    }

    /**
     * Generates a token for all token objects in this survey.
     * Syntax: ls\models\Token::model(12345)->generateTokens();
     */
    public function generateTokens($length = 15)
    {
        if ($this->scenario != '') {
            throw new \Exception("This function should only be called like: Token::model(12345)->generateTokens(20)");
        }
        /**
         * @todo Generate tokens in SQL.
         */
        $value = new \CDbExpression("(SELECT SUBSTRING(CONCAT(MD5(RAND()), MD5(RAND())), 1, $length))");

        $surveyId = $this->dynamicId;
        return $this->updateAll([
            'token' => $value
        ], 'token IS NULL');
    }

    public function relations()
    {
        $result = [
            'survey' => [self::BELONGS_TO, Survey::class, '', 'on' => "sid = {$this->dynamicId}"],
            'surveylink' => [
                self::BELONGS_TO,
                SurveyLink::class,
                ['participant_id' => 'participant_id'],
                'on' => "survey_id = {$this->dynamicId}"
            ]
        ];

        if (Response::valid($this->dynamicId)) {
            $result['responses'] = [self::HAS_MANY, 'Response_' . $this->dynamicId, ['token' => 'token']];
        }

        return $result;
    }

    /**
     * This function is used when the survey is not active and thus the relation above is not added.
     * It is protected since you should use ->responses to make sure you get the relation if it is available.
     * @return array
     */
    protected function getResponses()
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getResponseCount()
    {
        return Response::valid($this->dynamicId) ? Response::model($this->dynamicId)->countByAttributes(['token' => $this->token]) : 0;
    }

    public function rules()
    {
        $rules = [
            ['token', 'unique', 'allowEmpty' => true],

            ['firstname', 'length', 'max' => 40],
            ['lastname', 'length', 'max' => 40],
            ['remindercount', 'numerical', 'integerOnly' => true, 'allowEmpty' => true],
            [
                'email',
                \CEmailValidator::class,
                'allowEmpty' => true,
                'except' => 'allowinvalidemail'
            ],
            ['usesleft', 'numerical', 'integerOnly' => true, 'allowEmpty' => true],
            ['mpid', 'numerical', 'integerOnly' => true, 'allowEmpty' => true],
            ['blacklisted', 'in', 'range' => ['Y', 'N'], 'allowEmpty' => true],
            ['emailstatus', 'default', 'value' => 'OK'],
            ['email', 'email', 'on' => 'register'],
            ['email', 'unique', 'on' => 'register'],
            [['lastname', 'firstname'], 'safe', 'on' => 'register'],
            ['captcha', 'captcha', 'on' => 'register'],

            ['token', \CDefaultValueValidator::class, 'value' => null]

        ];
        if (is_array($attributeDescriptions = json_decode($this->survey->attributedescriptions, true))) {
            foreach($attributeDescriptions as $key => $info) {
                $rules[] = [$key, 'required'];
            }
        }
        return $rules;
    }

    public function scopes()
    {
        $now = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig("timeadjust"));

        return [
            'incomplete' => [
                'condition' => "completed = 'N'"
            ],
            'usable' => [
                'condition' => "COALESCE(validuntil, '$now') >= '$now' AND COALESCE(validfrom, '$now') <= '$now'"
            ],
            'editable' => [
                'condition' => "COALESCE(validuntil, '$now') >= '$now' AND COALESCE(validfrom, '$now') <= '$now'"
            ],
            'empty' => [
                'condition' => 'token is null or token = ""'
            ]
        ];
    }

    public function summary()
    {
        $criteria = $this->getDbCriteria();
        $criteria->select = [
            "COUNT(*) as count",
            "COUNT(CASE WHEN (token IS NULL OR token='') THEN 1 ELSE NULL END) as invalid",
            "COUNT(CASE WHEN (sent!='N' AND sent<>'') THEN 1 ELSE NULL END) as sent",
            "COUNT(CASE WHEN (emailstatus LIKE 'OptOut%') THEN 1 ELSE NULL END) as optout",
            "COUNT(CASE WHEN (completed!='N' and completed<>'') THEN 1 ELSE NULL END) as completed",
            "COUNT(CASE WHEN (completed='Q') THEN 1 ELSE NULL END) as screenout",
        ];
        $command = $this->getCommandBuilder()->createFindCommand($this->getTableSchema(), $criteria);

        return $command->queryRow();
    }

    public static function constructTableName($id)
    {
        return '{{token_' . $id . '}}';
    }

    public function getSurveyId()
    {
        return $this->dynamicId;
    }

    public function getIsExpired()
    {
        return !empty($this->expires)
        && (new DateTime($this->expires)) < new DateTime()
        && (new DateTime($this->validfrom)) > new DateTime();
    }


    public function customAttributeNames()
    {
        return array_filter($this->attributeNames(), function ($attribute) {
            return strncmp("attribute_", $attribute, 10) === 0;
        });
    }


}
