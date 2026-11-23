<?php

/**
 *
 * For code completion we add the available scenario's here
 * Attributes
 * @property int      $tid
 * @property string   $firstname
 * @property string   $lastname
 * @property string   $email
 * @property string   $emailstatus
 * @property string   $token
 * @property string   $language
 * @property string   $blacklisted
 * @property string   $sent
 * @property string   $remindersent
 * @property int      $remindercount
 * @property string   $completed
 * @property int      $usesleft
 * @property DateTime $validfrom
 * @property DateTime $validuntil
 *
 * Relations
 * @property Survey $survey The survey this token belongs to.
 *
 * Scopes
 * @method Token incomplete() incomplete() Select only uncompleted tokens
 * @method Token usable() usable() Select usable tokens: valid daterange and usesleft > 0
 *
 */

use LimeSurvey\PluginManager\PluginEvent;

/**
 * Class Token
 *
 * @property integer $tid Token ID
 * @property string $participant_id Participant ID
 * @property string $firstname Participant's first name
 * @property string $lastname Participant's last name
 * @property string $email Participant's e-mail address
 * @property string $emailstatus Participant's e-mail address status: OK/bounced/OptOut
 * @property string $token Participant's token
 * @property string $language Participant's language eg: en
 * @property string $blacklisted Whether participant is blocklisted: (Y/N)
 * @property string $sent
 * @property string $remindersent
 * @property integer $remindercount
 * @property string $completed Participant completed status (N:Not completed; Q:Locked with quota; 'YYYY-MM-DD hh:mm': date of completion)
 * @property integer $usesleft How many uses left to fill questionnaire for this participant
 * @property string $validfrom
 * @property string $validuntil
 * @property integer $mpid //TODO Describe me!
 *
 * @property Survey $survey
 * @property SurveyLink $surveylink
 * @property Response[] $responses
 * @property CDbTableSchema $tableSchema
 *
 * @property string[] $standardCols Names of the core columns of the participant table
 * @property array $custom_attributes Columns of the custom attributes
 * @property array $attributesForGrid Columns of the participant list
 */
abstract class Token extends Dynamic
{
    /** @var int Minimum token length */
    const MIN_LENGTH = 5;

    /** @var int Maximum token length */
    const MAX_LENGTH = 36;

    /** @var int Default token length */
    const DEFAULT_LENGTH = 15;

    /**
     * Set defaults
     * @inheritdoc
     */
    public function init()
    {
        // Set the default values
        $this->usesleft = 1;
        $this->completed = "N";
        $this->emailstatus = "OK";
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'tid';
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        $labels = array(
            'tid' => gT('Access code ID'),
            'partcipant_id' => gT('Participant ID'),
            'firstname' => gT('First name'),
            'lastname' => gT('Last name'),
            'email' => gT('Email address'),
            'emailstatus' => gT('Email status'),
            'token' => gT('Access code'),
            'language' => gT('Language code'),
            'blacklisted' => gT('Blocklisted'),
            'sent' => gT('Invitation sent date'),
            'remindersent' => gT('Last reminder sent date'),
            'remindercount' => gT('Total numbers of sent reminders'),
            'completed' => gT('Completed'),
            'usesleft' => gT('Uses left'),
            'validfrom' => gT('Valid from'),
            'validuntil' => gT('Valid until'),
        );
        foreach (decodeTokenAttributes($this->survey->attributedescriptions ?? '') as $key => $info) {
            $labels[$key] = !empty($info['description']) ? $info['description'] : '';
        }
        return $labels;
    }

    /** @inheritdoc
     * Delete related SurveyLink if it's deleted
     */
    public function beforeDelete()
    {
        $result = parent::beforeDelete();
        if ($result && isset($this->surveylink)) {
            if (!$this->surveylink->delete()) {
                throw new CException('Could not delete survey link. Participant was not deleted.');
            }
            return true;
        }
        return $result;
    }

    /**
     * @inheritdoc
     * Deletes the related SurveyLink at the same time
     */
    public function deleteAllByAttributes($attributes, $condition = '', $params = array())
    {
        $linkedTokenIds = $this->findLinkedTokenIds($attributes, $condition, $params);
        $result = parent::deleteAllByAttributes($attributes, $condition, $params);
        if ($result && !empty($linkedTokenIds)) {
            // Participants that were not deleted keep their survey link
            $deletedTokenIds = array_diff($linkedTokenIds, $this->findLinkedTokenIds($attributes, $condition, $params));
            foreach (array_chunk($deletedTokenIds, 1000) as $tokenIds) {
                SurveyLink::model()->deleteAllByAttributes(array('token_id' => $tokenIds, 'survey_id' => $this->dynamicId));
            }
        }
        return $result;
    }

    /**
     * Returns the IDs of the participants matching the attributes and the condition
     * that are linked to a participant of the central participant database.
     * Only the participants that deleteAllByAttributes() deletes are read, not all participants of the survey.
     *
     * @param array $attributes Attribute values (indexed by attribute names) the participants have to match
     * @param mixed $condition Query condition or criteria
     * @param array $params Parameters to be bound to the query
     * @return array The participant IDs (tid)
     */
    private function findLinkedTokenIds($attributes, $condition, $params)
    {
        $builder = $this->getCommandBuilder();
        $table = $this->getTableSchema();
        $alias = $this->getTableAlias(true, false);
        $criteria = $builder->createColumnCriteria($table, $attributes, $condition, $params, $alias . '.');
        $criteria->addCondition($alias . '.participant_id IS NOT NULL');
        $criteria->select = $alias . '.tid';
        return $builder->createFindCommand($table, $criteria)->queryColumn();
    }

    /**
     * @param integer $surveyId
     * @param array $extraFields
     * @return CDbTableSchema
     */
    public static function createTable($surveyId, array $extraFields = array())
    {
        $surveyId = intval($surveyId);
        // Specify case sensitive collations for the token
        $sCollation = '';
        if (Yii::app()->db->driverName == 'mysql' || Yii::app()->db->driverName == 'mysqli') {
            $sCollation = "COLLATE 'utf8mb4_bin'";
        }

        if (
            Yii::app()->db->driverName == 'sqlsrv'
            || Yii::app()->db->driverName == 'dblib'
            || Yii::app()->db->driverName == 'mssql'
        ) {
            $sCollation = "COLLATE SQL_Latin1_General_CP1_CS_AS";
        }
        $fields = array(
            'tid' => 'pk',
            'participant_id' => 'string(50)',
            'firstname' => 'text',
            'lastname' => 'text',
            'email' => 'text',
            'emailstatus' => 'text',
            'token' => "string(" . self::MAX_LENGTH . ") {$sCollation}",
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
        );
        foreach ($extraFields as $extraField) {
            $fields[$extraField] = 'text';
        }

        // create fields for the custom token attributes associated with this survey
        $oSurvey = Survey::model()->findByPk($surveyId);
        foreach ($oSurvey->tokenAttributes as $attrname => $attrdetails) {
            if (!isset($fields[$attrname])) {
                $fields[$attrname] = 'text';
            }
        }

        $db = \Yii::app()->db;
        $sTableName = $oSurvey->tokensTableName;

        try {
            $db->createCommand()->createTable($sTableName, $fields);
            /**
             * The random component in the index name is needed because Postgres is being the dorky kid and
             * complaining about duplicates when renaming the table and trying to use the same index again
             * on a new token table (for example on reactivation)
             */
            $tokenindexname = "idx_token_" .
                trim($sTableName, '{}') . "_" .
                date('YmdHis') . "_" .
                randomChars(12, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
            $db->createCommand()->createIndex($tokenindexname, $sTableName, 'token');
            $mailindexname = "idx_email_" .
                trim($sTableName, '{}') . "_" .
                date('YmdHis') . "_" .
                randomChars(12, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
            // MSSQL does not support indexes on text fields so not needed here
            switch (Yii::app()->db->driverName) {
                case 'mysql':
                case 'mysqli':
                    $db->createCommand()->createIndex($mailindexname, $sTableName, 'email(30)', false);
                    break;
                case 'pgsql':
                    $db->createCommand()->createIndex($mailindexname, $sTableName, 'email', false);
                    break;
            }
        } catch (CDbException $ex) {
            //table already exists, skipping
        }

        // The responses get their relation to the participants only if the participant table exists
        Response::resetMetaData($surveyId);
        // Refresh schema cache just in case the table existed in the past, and return if table exist
        return $db->schema->getTable($sTableName, true);
    }

    /**
     * @param string $token
     * @return Token
     */
    public function findByToken($token)
    {
        return $this->findByAttributes(array(
            'token' => $token
        ));
    }

    /**
     * Get survey token length from survey.
     * Use default if not possible.
     */
    public function getSurveyTokenLength()
    {
        // Use default token length
        $iTokenLength = self::DEFAULT_LENGTH;

        // Use survey token length, if defined
        if (isset($this->survey) && !empty($this->survey->oOptions) && !empty($this->survey->oOptions->tokenlength) && is_numeric($this->survey->oOptions->tokenlength)) {
            $iTokenLength = $this->survey->oOptions->tokenlength;
        }

        return $iTokenLength;
    }

    /**
     * Generates a token for this object.
     * @throws CHttpException
     */
    public function generateToken($iTokenLength = null)
    {
        if (empty($iTokenLength)) {
            $iTokenLength = $this->getSurveyTokenLength();
        }

        $this->token = $this->generateRandomToken($iTokenLength);
        $counter = 0;
        while (!$this->validate(array('token'))) {
            $this->token = $this->generateRandomToken($iTokenLength);
            $counter++;
            // This is extremely unlikely.
            if ($counter > 50) {
                throw new CHttpException(500, 'Failed to create unique access code in 50 attempts.');
            }
        }
    }

    /**
     * Creates a random token string without special characters
     *
     * @param integer $iTokenLength
     * @return string
     */
    private function generateRandomToken($iTokenLength)
    {
        $token = Yii::app()->securityManager->generateRandomString($iTokenLength);
        if ($token === false) {
            throw new CHttpException(500, gT('Failed to generate random string for token. Please check your configuration and ensure that the openssl or mcrypt extension is enabled.'));
        }
        $token = str_replace(array('~', '_'), array('a', 'z'), (string) $token);
        $event = new PluginEvent('afterGenerateToken');
        $event->set('surveyId', $this->getSurveyId());
        $event->set('iTokenLength', $iTokenLength);
        $event->set('oToken', $this);
        $event->set('token', $token);
        App()->pluginManager->dispatchEvent($event);
        $token = $event->get('token');
        return $token;
    }

    /**
     * Sanitize token show to the user (replace sanitize_helper sanitize_token)
     * @param string|null $token to sanitize
     * @return string sanitized token
     */
    public static function sanitizeToken($token)
    {
        if (is_null($token)) {
            return "";
        }
        // According to Yii doc : http://www.yiiframework.com/doc/api/1.1/CSecurityManager#generateRandomString-detail
        $pattern = Yii::app()->getConfig("allowedcharacters_pattern_token");
        return preg_replace($pattern, '', $token);
    }

    /**
     * Sanitize string for any attribute
     * Only strips HTML tags: The value is stored as plain text and encoded on output,
     * encoding it here would show entities like &amp;amp; in the administration and in text emails
     * and add one more level of encoding each time the participant is saved.
     * @param string|null $attribute to sanitize
     * @return string sanitized attribute
     */
    public static function sanitizeAttribute($attribute)
    {
        return strip_tags((string) $attribute);
    }

    /**
     * Generates a token for all token objects in this survey.
     * Syntax: Token::model(12345)->generateTokens();
     * @return integer[]
     * @throws Exception
     */
    public function generateTokens()
    {
        if ($this->scenario != '') {
            throw new \Exception("This function should only be called like: Token::model(12345)->generateTokens");
        }
        $surveyId = $this->dynamicId;
        $iTokenLength = $this->getSurveyTokenLength();

        $tkresult = Yii::app()->db->createCommand("SELECT tid FROM {{tokens_{$surveyId}}} WHERE token IS NULL OR token=''")->queryAll();
        //Exit early if there are not empty tokens
        if (count($tkresult) === 0) {
            return array(0, 0);
        }

        // Do NOT replace the following select with ActiveRecord as it uses too much memory
        $ntresult = Yii::app()->db->createCommand()->select('token')->from($this->tableName())-> where("token IS NOT NULL and token<>''")->queryColumn();
        // select all existing tokens
        foreach ($ntresult as $tkrow) {
            $existingtokens[$tkrow] = true;
        }
        $newtokencount = 0;
        $invalidtokencount = 0;
        $newtoken = null;
        foreach ($tkresult as $tkrow) {
            $bIsValidToken = false;
            while ($bIsValidToken == false && $invalidtokencount < 50) {
                $newtoken = $this->generateRandomToken($iTokenLength);
                if (!isset($existingtokens[$newtoken])) {
                    $existingtokens[$newtoken] = true;
                    $bIsValidToken = true;
                    $invalidtokencount = 0;
                } else {
                    $invalidtokencount++;
                }
            }
            if ($bIsValidToken) {
                $this->updateByPk($tkrow['tid'], array('token' => $newtoken));
                $newtokencount++;
            } else {
                break;
            }
        }

        return array($newtokencount, count($tkresult));
    }
    /**
     * @inheritdoc
     * @return Token
     */
    public static function model($className = null)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /**
     * @param int $id Survey id in this class
     * @param string $scenario
     * @return Token Description
     */
    public static function create($id, $scenario = 'insert')
    {
        return parent::create($id, $scenario);
    }

    /** @inheritdoc */
    public function relations()
    {
        $result = array(
            'responses' => array(self::HAS_MANY, 'Response_' . $this->dynamicId, array('token' => 'token')),
            'survey' =>  array(self::BELONGS_TO, 'Survey', '', 'on' => "sid = {$this->dynamicId}"),
            'surveylink' => array(self::BELONGS_TO, 'SurveyLink', array('participant_id' => 'participant_id'), 'on' => "survey_id = {$this->dynamicId}")
        );
        return $result;
    }

    /** @inheritdoc */
    public function rules()
    {
        $aRules = array(
            array('token', 'unique', 'allowEmpty' => true),
            array('token', 'length', 'min' => 0, 'max' => 36),
            array('token', 'filter', 'filter' => array(self::class, 'sanitizeToken')),
            array('firstname', 'filter', 'filter' => array(self::class, 'sanitizeAttribute')),
            array('lastname', 'filter', 'filter' => array(self::class, 'sanitizeAttribute')),
            array('language', 'LSYii_Validators', 'isLanguage' => true),
            array('language','in','range' => array_merge(array($this->survey->language), explode(' ', $this->survey->additional_languages)),'allowEmpty' => true,'message' => gT('Language code is invalid in this survey')),
            array(implode(',', $this->tableSchema->columnNames ?? []), 'safe'),
            /* pseudo date : force date or specific string ? */
            array('remindersent', 'length', 'min' => 0, 'max' => 17),
            array('remindersent', 'filter', 'filter' => array(self::class, 'sanitizeAttribute')),
            array('completed', 'length', 'min' => 0, 'max' => 17),
            array('remindersent', 'filter', 'filter' => array(self::class, 'sanitizeAttribute')),
            array('remindercount', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
            array('email', 'LSYii_FilterValidator', 'filter' => 'trim', 'skipOnEmpty' => true),
            array('email', 'LSYii_EmailIDNAValidator', 'allowEmpty' => true, 'allowMultiple' => true, 'except' => 'allowinvalidemail'),
            array('emailstatus', 'default', 'value' => 'OK'),
            array('emailstatus', 'filter', 'filter' => array(self::class, 'sanitizeAttribute')),
            array('usesleft', 'numerical', 'integerOnly' => true, 'allowEmpty' => true, 'min' => -2147483647, 'max' => 2147483647),
            array('mpid', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
            array('blacklisted', 'in', 'range' => array('Y', 'N'), 'allowEmpty' => true),
            array('validfrom', 'date','format' => ['yyyy-M-d H:m:s.???','yyyy-M-d H:m:s','yyyy-M-d H:m','yyyy-M-d'],'allowEmpty' => true),
            array('validuntil','date','format' => ['yyyy-M-d H:m:s.???','yyyy-M-d H:m:s','yyyy-M-d H:m','yyyy-M-d'],'allowEmpty' => true),
        );
        foreach (decodeTokenAttributes($this->survey->attributedescriptions ?? '') as $key => $info) {
            $aRules[] = array(
                $key, 'filter',
                'filter' => array(self::class, 'sanitizeAttribute'),
                'on' => 'register'
            );
            $aRules[] = array(
                $key,
                'LSYii_Validators',
                'except' => 'finalSubmit,register'
            );
        }
        return $aRules;
    }

    /** @inheritdoc */
    public function scopes()
    {
        $now = gmdate("Y-m-d H:i:s");
        return array(
            'incomplete' => array(
                'condition' => "completed = 'N'"
            ),
            'usable' => array(
                'condition' => "COALESCE(validuntil, '$now') >= '$now' AND COALESCE(validfrom, '$now') <= '$now' AND usesleft > 0"
            ),
            'editable' => array(
                'condition' => "COALESCE(validuntil, '$now') >= '$now' AND COALESCE(validfrom, '$now') <= '$now'"
            ),
            'empty' => array(
                'condition' => 'token is null or token = ""'
            )
        );
    }

    /**
     * Returns the numbers of the participant summary: All, without access code, invited, opted out, completed and screened out
     *
     * @return array|false The numbers indexed by count, invalid, sent, optout, completed and screenout
     */
    public function summary()
    {
        $criteria = clone $this->getDbCriteria();
        // Like the find methods, this query uses up the scopes of the model.
        // The columns selected here must not get into the next query of the model, e.g. the one of the participant list.
        $this->resetScope(false);
        $criteria->select = array(
            "COUNT(*) as count",
            "COUNT(CASE WHEN (token IS NULL OR token='') THEN 1 ELSE NULL END) as invalid",
            "COUNT(CASE WHEN (sent!='N' AND sent<>'') THEN 1 ELSE NULL END) as sent",
            "COUNT(CASE WHEN (emailstatus LIKE 'OptOut%') THEN 1 ELSE NULL END) as optout",
            "COUNT(CASE WHEN (completed!='N' and completed<>'') THEN 1 ELSE NULL END) as completed",
            "COUNT(CASE WHEN (completed='Q') THEN 1 ELSE NULL END) as screenout",
        );
        $command = $this->getCommandBuilder()->createFindCommand($this->getTableSchema(), $criteria);
        return $command->queryRow();
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{tokens_' . $this->dynamicId . '}}';
    }

    /**
     * Get current surveyId for other model/function
     * @return int
     */
    public function getSurveyId()
    {
        return $this->getDynamicId();
    }

    public static function getDefaultEncryptionOptions()
    {
        $sEncrypted = 'N';
        return array(
                'enabled' => 'N',
                'columns' => array(
                    'firstname' =>  $sEncrypted,
                    'lastname' =>  $sEncrypted,
                    'email' =>  $sEncrypted
                )
        );
    }

    public function onBeforeSave($event)
    {
        // Mark token as "OptOut" if globally blocklisted and 'blacklistnewsurveys' is enabled
        if (Yii::app()->getConfig('blacklistnewsurveys') == "Y" && $this->getIsNewRecord()) {
            $blacklistHandler = new LimeSurvey\Models\Services\ParticipantBlacklistHandler();
            if ($blacklistHandler->isTokenBlacklisted($this)) {
                $this->optOut();
            }
        }
        return parent::onBeforeSave($event);
    }

    /**
     * Get opt out status of token from email status
     * @return bool true if token is opted out, false if not
     */
    public function getOptOutStatus()
    {
        // This must be something for backwards compatibility
        // the query above also has OptOut% in the summary function
        // implying something comes after
        return substr((string) $this->emailstatus, 0, strlen('OptOut')) === 'OptOut';
    }

    /**
     * Opt out the token by updating the email status
     */
    public function optOut()
    {
        $this->emailstatus = 'OptOut';
    }

    /**
     * Opt in the token by updating the email status
     */
    public function optIn()
    {
        $this->emailstatus = 'OK';
    }

    /**
     * @param int[]|bool $aTokenIds
     * @param int $iMaxEmails
     * @param bool $bEmail
     * @param string $SQLemailstatuscondition
     * @param string $SQLremindercountcondition
     * @param string $SQLreminderdelaycondition
     * @param bool $bOnlyCurrentlyValid If true, skip participants whose validity period does not include the current time
     * @return CActiveRecord[]
     */
    public function findUninvited($aTokenIds = false, $iMaxEmails = 0, $bEmail = true, $SQLemailstatuscondition = '', $SQLremindercountcondition = '', $SQLreminderdelaycondition = '', $bOnlyCurrentlyValid = false)
    {
        $command = new CDbCriteria();
        $command->condition = '';
        $command->addCondition("(completed ='N') or (completed='')");
        $command->addCondition("t.token <> ''");
        $command->addCondition("email <> ''");

        if ($bEmail) {
            $command->addCondition("(sent = 'N') or (sent = '')");
        } else {
            $command->addCondition("(sent <> 'N') AND (sent <> '')");
        }

        if ($SQLemailstatuscondition) {
            $command->addCondition($SQLemailstatuscondition);
        }

        if ($SQLremindercountcondition) {
            $command->addCondition($SQLremindercountcondition);
        }

        if ($SQLreminderdelaycondition) {
            $command->addCondition($SQLreminderdelaycondition);
        }

        if ($bOnlyCurrentlyValid) {
            $command->addCondition($this->getCurrentlyValidCondition());
        }

        if ($aTokenIds) {
            $command->addInCondition("tid", $aTokenIds);
        }

        if ($iMaxEmails) {
            $command->limit = $iMaxEmails;
        }

        $command->order = 'tid';

        $oResult = $this->findAll($command);
        foreach ($oResult as $key => $result) {
            $oResult[$key] = $result->decrypt();
        }

        $cpdbBlacklisted = Participant::model()->getBlacklistedParticipantIds();
        $oResult = array_filter($oResult, function ($item) use ($cpdbBlacklisted) {
            return empty($item->participant_id) || !in_array($item->participant_id, $cpdbBlacklisted);
        });

        return $oResult;
    }

    /**
     * Find participant IDs matching email criteria (invitations or reminders)
     * Optimized to fetch only IDs without loading full token records (issue #20465)
     * @param int[]|bool $aTokenIds Optional array of specific token IDs to filter
     * @param int $iMaxEmails Maximum number of records to return
     * @param bool $bEmail If true, find uninvited participants; if false, find reminded participants
     * @param string $SQLemailstatuscondition Additional email status condition
     * @param string $SQLremindercountcondition Reminder count condition
     * @param string $SQLreminderdelaycondition Reminder delay condition
     * @param bool $bOnlyCurrentlyValid If true, skip participants whose validity period does not include the current time
     * @return array Array of token IDs
     */
    public function findParticipantIDs($aTokenIds = false, $iMaxEmails = 0, $bEmail = true, $SQLemailstatuscondition = '', $SQLremindercountcondition = '', $SQLreminderdelaycondition = '', $bOnlyCurrentlyValid = false)
    {
        // Optimized version that only fetches and returns IDs to avoid memory exhaustion (issue #20465)
        // Use CDbCommand for safe SQL generation with proper column quoting
        $db = Yii::app()->db;
        $command = $db->createCommand()
            ->select('tid, participant_id')
            ->from($this->tableName());

        // Build conditions using Yii's safe quoting methods
        $command->andWhere($db->quoteColumnName('completed') . " IN ('N', '')");
        $command->andWhere($db->quoteColumnName('token') . ' <> ' . $db->quoteValue(''));
        $command->andWhere($db->quoteColumnName('email') . ' <> ' . $db->quoteValue(''));

        if ($bEmail) {
            $command->andWhere($db->quoteColumnName('sent') . " IN ('N', '')");
        } else {
            $command->andWhere($db->quoteColumnName('sent') . " NOT IN ('N', '')");
        }

        if ($SQLemailstatuscondition) {
            $command->andWhere($SQLemailstatuscondition);
        }

        if ($SQLremindercountcondition) {
            $command->andWhere($SQLremindercountcondition);
        }

        if ($SQLreminderdelaycondition) {
            $command->andWhere($SQLreminderdelaycondition);
        }

        if ($bOnlyCurrentlyValid) {
            $command->andWhere($this->getCurrentlyValidCondition());
        }

        if ($aTokenIds) {
            $ids = array_map('intval', $aTokenIds);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $command->andWhere($db->quoteColumnName('tid') . " IN ($placeholders)", $ids);
        }

        $command->order('tid');

        if ($iMaxEmails) {
            $command->limit($iMaxEmails);
        }

        $results = $command->queryAll();

        // Filter out blocklisted participants (same approach as findUninvited)
        $cpdbBlocklisted = Participant::model()->getBlacklistedParticipantIds();
        $results = array_filter($results, function ($item) use ($cpdbBlocklisted) {
            return empty($item['participant_id']) || !in_array($item['participant_id'], $cpdbBlocklisted);
        });

        // Extract IDs only
        $ids = array_column($results, 'tid');

        return $ids;
    }

    /**
     * Returns an SQL condition matching participants whose validity period (valid from/valid until) includes the current time
     * @return string
     */
    private function getCurrentlyValidCondition()
    {
        $db = Yii::app()->db;
        $now = $db->quoteValue(gmdate('Y-m-d H:i:s'));
        $validFrom = $db->quoteColumnName('validfrom');
        $validUntil = $db->quoteColumnName('validuntil');
        return "($validFrom IS NULL OR $validFrom <= $now) AND ($validUntil IS NULL OR $validUntil >= $now)";
    }

    /**
     * Whether the survey has at least one response with the given access code
     * @param string $sToken The access code
     * @return boolean
     */
    public function getHasResponses($sToken)
    {
        $oSurvey = Survey::model()->findByPk(intval($this->dynamicId));
        if (!$oSurvey->hasResponsesTable) {
            return false;
        }
        $command = Yii::app()->db->createCommand()
            ->select('COUNT(token)')
            ->from('{{responses_' . intval($this->dynamicId) . '}}')
            ->where('token=:token')
            ->bindParam(':token', $sToken, PDO::PARAM_STR);

        return ((int)$command->queryScalar() > 0);
    }

    /**
     * The names of the core columns of the participant table
     * @return string[]
     */
    public function getStandardCols()
    {
        return array(
            "tid",
            "participant_id",
            "firstname",
            "lastname",
            "email",
            "emailstatus",
            "token",
            "language",
            "blacklisted",
            "sent",
            "remindersent",
            "remindercount",
            "completed",
            "usesleft",
            "validfrom",
            "validuntil",
            "mpid",
        );
    }

    /**
     * The columns of the custom attributes: All columns that are no core columns
     * @return CDbColumnSchema[] indexed by column name
     */
    // phpcs:ignore
    public function getCustom_attributes()
    {
        $columns = $this->getMetaData()->columns;
        $attributes = array();

        foreach ($columns as $sColName => $oColumn) {
            if (!in_array($sColName, $this->standardCols)) {
                $attributes[$sColName] = $oColumn;
            }
        }

        return $attributes;
    }

    /**
     * @return string
     */
    public function getSentFormated()
    {
        $field = $this->sent;
        return $this->getYesNoDateFormated($field);
    }

    /**
     * @return string
     */
    public function getRemindersentFormated()
    {
        $field = $this->remindersent;
        return $this->getYesNoDateFormated($field);
    }

    /**
     * @return string
     */
    public function getCompletedFormated()
    {
        $field = $this->completed;
        return $this->getYesNoDateFormated($field);
    }

    /**
     * @return string
     */
    public function getValidfromFormated()
    {
        $field = $this->validfrom;
        return $this->getYesNoDateFormated($field);
    }

    /**
     * @return string
     */
    public function getValiduntilFormated()
    {
        $field = $this->validuntil;
        return $this->getYesNoDateFormated($field);
    }

    /**
     * Returns the value of a custom date attribute in the display format, for the participant list
     * @param string $attrName Name of the attribute (e.g. attribute_1)
     * @return string
     */
    public function getCustomDateAttrFormatted($attrName)
    {
        $diContainer = \LimeSurvey\DI::getContainer();
        $attributeService = $diContainer->get(
            LimeSurvey\Models\Services\ParticipantAttributeService::class
        );
        $field = $this->{$attrName};
        return $attributeService->convertDateAttributeToDisplayFormat(
            0,
            $field,
            false
        );
    }

    /**
     * @param string $field
     * @return string
     */
    private function getYesNoDateFormated($field)
    {
        if ($field != 'N' && $field != '') {
            if ($field == 'Q') {
                $field     = '<span class="text-danger">' . gT('Quota out') . '</span>';
            } elseif ($field != 'Y') {
                $fieldDate = convertToGlobalSettingFormat($field);
                $field     = '<span class="text-success">' . CHtml::encode($fieldDate) . '</span>';
            } else {
                $field     = '<span class="text-success ri-check-fill"></span>';
            }
        } elseif ($field != '') {
            $field = '<i class="ri-subtract-fill text-danger"></i>';
        }
        return $field;
    }

    /**
     * @return string
     */
    public function getEmailFormated()
    {
        if (substr($this->emailstatus, 0, 7) == "bounced") {
            return '<span class="text-danger"><strong> ' . CHtml::encode($this->email) . '</strong></span>';
        } else {
            return CHtml::encode($this->email);
        }
    }

    /**
     * @return string
     */
    public function getEmailstatusFormated()
    {
        if (substr($this->emailstatus, 0, 7) == "bounced") {
            return '<span class="text-danger"><strong> ' . CHtml::encode($this->emailstatus) . '</strong></span>';
        } else {
            return CHtml::encode($this->emailstatus);
        }
    }

    /**
     * Builds the standard (core) grid columns for a survey participant table.
     *
     * Static so it works for both the populated grid and the empty grid that is
     * shown before the participant table exists.
     *
     * @param int $iSurveyId
     * @param LSActiveRecord $labeler
     * @return array
     */
    public static function buildStandardColsForGrid($iSurveyId, $labeler)
    {
        return [
            [
                'id'                => 'tid',
                'class'             => 'CCheckBoxColumn',
                'selectableRows'    => '100',
                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                'filterHtmlOptions' => ['class' => 'ls-sticky-column'],
                'htmlOptions'       => ['class' => 'ls-sticky-column']
            ],
            [
                'header'            => gT('ID'),
                'name'              => 'tid',
                'value'             => '$data->tid',
                'headerHtmlOptions' => ['class' => ''],
                'htmlOptions'       => ['class' => ' text-end'],
            ],
            [
                'header'            => gT('First name') . $labeler->setEncryptedAttributeLabel($iSurveyId, 'Token', 'firstname'),
                'name'              => 'firstname',
                'value'             => '$data->firstname',
                'headerHtmlOptions' => ['class' => ''],
                'htmlOptions'       => ['class' => ' name'],
            ],
            [
                'header'            => gT('Last name') . $labeler->setEncryptedAttributeLabel($iSurveyId, 'Token', 'lastname'),
                'name'              => 'lastname',
                'value'             => '$data->lastname',
                'headerHtmlOptions' => ['class' => ''],
                'htmlOptions'       => ['class' => ' name'],
            ],
            [
                'header'            => gT('Email address') . $labeler->setEncryptedAttributeLabel($iSurveyId, 'Token', 'email'),
                'name'              => 'email',
                'type'              => 'raw',
                'value'             => '$data->emailFormated',
                'headerHtmlOptions' => ['class' => ''],
                'htmlOptions'       => ['class' => ' name'],
            ],
            [
                'header'            => gT('Email status') . $labeler->setEncryptedAttributeLabel($iSurveyId, 'Token', 'emailstatus'),
                'name'              => 'emailstatus',
                'value'             => '$data->emailstatusFormated',
                'type'              => 'raw',
                'headerHtmlOptions' => ['class' => ''],
                'htmlOptions'       => ['class' => ''],
            ],
            [
                'header'            => gT('Access code'),
                'name'              => 'token',
                'value'             => '$data->token',
                'headerHtmlOptions' => ['class' => ''],
                'htmlOptions'       => ['class' => ''],
            ],
            [
                'header'            => gT('Language'),
                'name'              => 'language',
                'value'             => '$data->language',
                'headerHtmlOptions' => ['class' => ''],
                'htmlOptions'       => ['class' => ''],
            ],
            [
                'header'            => gT('Invitation sent?'),
                'name'              => 'sent',
                'type'              => 'raw',
                'value'             => '$data->sentFormated',
                'headerHtmlOptions' => ['class' => ''],
                'htmlOptions'       => ['class' => '  text-center'],
            ],
            [
                'header'            => gT('Reminder sent?'),
                'name'              => 'remindersent',
                'type'              => 'raw',
                'value'             => '$data->remindersentFormated',
                'headerHtmlOptions' => ['class' => ''],
                'htmlOptions'       => ['class' => ' text-center'],
            ],
            [
                'header'            => gT('Reminder count'),
                'name'              => 'remindercount',
                'value'             => '$data->remindercount',
                'headerHtmlOptions' => ['class' => ''],
                'htmlOptions'       => ['class' => ' text-end'],
            ],
            [
                'header'            => gT('Completed?'),
                'name'              => 'completed',
                'type'              => 'raw',
                'value'             => '$data->completedFormated',
                'headerHtmlOptions' => ['class' => ''],
                'htmlOptions'       => ['class' => ' text-center'],
            ],
            [
                'header'            => gT('Uses left'),
                'name'              => 'usesleft',
                'value'             => '$data->usesleft',
                'headerHtmlOptions' => ['class' => ''],
                'htmlOptions'       => ['class' => ' text-end'],
            ],
            [
                'header'            => gT('Valid from'),
                'name'              => 'validfrom',
                'type'              => 'raw',
                'value'             => '$data->validfromFormated',
                'headerHtmlOptions' => ['class' => ''],
                'htmlOptions'       => ['class' => ' name'],
            ],
            [
                'header'            => gT('Valid until'),
                'type'              => 'raw',
                'name'              => 'validuntil',
                'value'             => '$data->validuntilFormated',
                'headerHtmlOptions' => ['class' => ''],
                'htmlOptions'       => ['class' => ' name'],
            ],
        ];
    }

    /**
     * The columns of the participant list: Core columns, custom attributes and the action column
     * @return array
     */
    public function getAttributesForGrid()
    {
        return self::buildAttributesForGrid($this->dynamicId, $this);
    }

    /**
     * Builds the full grid column set (standard + custom attributes + action column) for a survey.
     *
     * Extracted as static so it can be reused for the empty-state grid rendered before the
     * participant table exists. $labeler is any LSActiveRecord used only for encryption labels.
     *
     * @param int $iSurveyId
     * @param LSActiveRecord $labeler
     * @return array
     */
    public static function buildAttributesForGrid($iSurveyId, $labeler)
    {
        $aCustomAttributesCols = array();

        $oSurvey = Survey::model()->findByAttributes(array("sid" => $iSurveyId));
        $aCustomAttributes = $oSurvey->tokenAttributes;

        // Custom attributes
        foreach ($aCustomAttributes as $sColName => $oColumn) {
            $desc = ($oColumn['description'] != '') ? $oColumn['description'] : $sColName;
            if (array_key_exists('type', $oColumn) && $oColumn['type'] == 'DP') {
                $value = '$data->getCustomDateAttrFormatted(\'' . $sColName . '\')';
                $type = 'raw';
            } else {
                $value = '$data->' . $sColName;
                $type = 'longtext';
            }

            $aCustomAttributesCols[] = array(
                'header' => $desc . $labeler->setEncryptedAttributeLabel($iSurveyId, 'Token', $sColName), // $aAttributedescriptions->$sColName->description,
                'name' => $sColName,
                'type' => $type,
                'value' => $value,
                'headerHtmlOptions' => array('class' => ''),
                'htmlOptions' => array('class' => ''),
            );
        }

        $actionColumn = [
            [
                "name"              => 'actions',
                "type"              => 'raw',
                "header"            => gT("Action"),
                "filter"            => false,
                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                'filterHtmlOptions' => ['class' => 'ls-sticky-column'],
                'htmlOptions'       => ['class' => 'ls-sticky-column']
            ]
        ];

        return array_merge(self::buildStandardColsForGrid($iSurveyId, $labeler), $aCustomAttributesCols, $actionColumn);
    }

    /**
     * Return the buttons columns
     * @see https://www.yiiframework.com/doc/api/1.1/CButtonColumn
     * @see https://bugs.limesurvey.org/view.php?id=14219
     * @see https://bugs.limesurvey.org/view.php?id=14222: When deleting a single response: All page is reloaded (not only grid)
     * @return string HTML of the action dropdown of a row in the participant list
     */
    public function getActions()
    {
        $permission_responses_read = Permission::model()->hasSurveyPermission($this->dynamicId, 'responses', 'read');
        $permission_reponses_create = intval(Permission::model()->hasSurveyPermission($this->dynamicId, 'responses', 'create'));
        $permission_tokens_update = Permission::model()->hasSurveyPermission($this->dynamicId, 'tokens', 'update');
        $permission_tokens_delete = Permission::model()->hasSurveyPermission($this->dynamicId, 'tokens', 'delete');
        $permission_participantpanel_read = Permission::model()->hasGlobalPermission('participantpanel', 'read');

        $dropdownItems = [];
        $dropdownItems[] = [
            'title'            => gT('View response details'),
            'url'              => App()->createUrl("responses/viewbytoken", [
                "surveyId" => $this->dynamicId,
                "token"    => $this->token
            ]),
            'iconClass'        => 'ri-list-unordered',
            'enabledCondition' => $permission_responses_read
                && $this->survey->active === "Y"
                && $this->survey->anonymized !== "Y"
                && $this->getHasResponses($this->token)
        ];
        $dropdownItems[] = [
            'title'            => gT('Preview the survey with this participant'),
            'url'              => App()->createUrl("/survey/index", [
                "sid"     => $this->dynamicId,
                "token"   => $this->token,
                "newtest" => "Y"
            ]),
            'linkAttributes'   => ['target' => "_blank"],
            'iconClass'        => 'ri-eye-fill',
            'enabledCondition' => $permission_reponses_create
                && !$this->survey->isActive
                && $this->canBeUsed()
        ];
        $dropdownItems[] = [
            'title'            => gT('Launch the survey with this participant'),
            'url'              => App()->createUrl("/survey/index", [
                "sid"     => $this->dynamicId,
                "token"   => $this->token,
                "newtest" => "Y"
            ]),
            'linkAttributes'   => ['target' => "_blank"],
            'iconClass'        => 'ri-play-fill',
            'enabledCondition' => $permission_reponses_create
                && $this->survey->isActive
                && $this->canBeUsed()
        ];
        $dropdownItems[] = [
            'title'            => gT('Send email invitation'),
            'url'              => App()->createUrl("/admin/tokens/sa/email", [
                "surveyid" => $this->dynamicId,
                "tokenids" => $this->tid,
            ]),
            'iconClass'        => 'ri-mail-send-fill',
            'enabledCondition' => $permission_tokens_update
                && ($this->sent === "N" || empty($this->sent))
                && $this->canBeEmailed()
        ];
        $dropdownItems[] = [
            'title'            => gT('Send email reminder'),
            'url'              => App()->createUrl("/admin/tokens/sa/email/action/remind", [
                "surveyid" => $this->dynamicId,
                "tokenids" => $this->tid
            ]),
            'iconClass'        => 'ri-mail-send-fill',
            'enabledCondition' => $permission_tokens_update
                && !($this->sent === "N" || empty($this->sent))
                && $this->canBeEmailed()
        ];
        $dropdownItems[] = [
            'title'            => gT('Edit this survey participant'),
            'url'              => App()->createUrl("/admin/tokens/sa/edit", [
                "iSurveyId" => $this->dynamicId,
                "iTokenId"  => $this->tid
            ]),
            'iconClass'        => 'ri-pencil-fill',
            'linkAttributes'    => [
                'onclick'  => "startEditToken(event, $(this))",
                'data-sid' => $this->dynamicId
            ],
            'enabledCondition' => $permission_tokens_update
        ];
        $dropdownItems[] = [
            'title'            => gT('Delete survey participant'),
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'linkAttributes'    => [
                'data-bs-toggle'  => "modal",
                'data-bs-target'  => '#confirmation-modal',
                'data-btnclass' => 'btn-danger',
                'data-message'    => gT('Do you really want to delete this participant'),
                'data-post-url'   => App()->createUrl("/admin/tokens/sa/deleteToken", [
                    "sid"   => $this->dynamicId,
                    "sItem" => $this->tid
                ]),
                'data-btntext'    => gT('Delete'),
            ],
            'enabledCondition' => $permission_tokens_delete
        ];
        $dropdownItems[] = [
            'title'            => gT('View this participant in the central participants database'),
            'url'              => App()->createUrl("admin/participants/sa/displayParticipants", [
                "#" => json_encode(
                    ["searchcondition" => "participant_id||equal||" . $this->participant_id],
                    JSON_FORCE_OBJECT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
                )
            ]),
            'iconClass'        => 'ri-shield-user-line',
            'linkAttributes'    => [
                'onclick' => "window.LS.gridButton.postGridAction(event,$(this))",
            ],
            'enabledCondition' => $permission_participantpanel_read
                && !empty($this->participant_id)
        ];

        return App()->getController()->widget('ext.admin.grid.GridActionsWidget.GridActionsWidget', ['dropdownItems' => $dropdownItems], true);
    }

    /**
     * Search function, used by the grid of the participant list
     * @return CActiveDataProvider
     */
    public function search()
    {
        $pageSizeTokenView = Yii::app()->user->getState('pageSizeTokenView', Yii::app()->params['defaultPageSize']);

        $sort = new CSort();
        $sort->defaultOrder = 'tid ASC';
        $sort->attributes = array(
            'tid' => array(
            'asc' => 'tid',
            'desc' => 'tid desc',
            ),
            'partcipant' => array(
            'asc' => 'partcipant',
            'desc' => 'partcipant desc',
            ),

            'firstname' => array(
            'asc' => 'firstname',
            'desc' => 'firstname desc',
            ),

            'lastname' => array(
            'asc' => 'lastname',
            'desc' => 'lastname desc',
            ),

            'email' => array(
            'asc' => 'email',
            'desc' => 'email desc',
            ),

            'emailstatus' => array(
            'asc' => 'emailstatus',
            'desc' => 'emailstatus desc',
            ),

            'token' => array(
            'asc' => 'token',
            'desc' => 'token desc',
            ),

            'language' => array(
            'asc' => 'language',
            'desc' => 'language desc',
            ),

            'blacklisted' => array(
            'asc' => 'blacklisted',
            'desc' => 'blacklisted desc',
            ),

            'sent' => array(
            'asc' => 'sent',
            'desc' => 'sent desc',
            ),

            'remindersent' => array(
            'asc' => 'remindersent',
            'desc' => 'remindersent desc',
            ),

            'remindercount' => array(
                'asc' => 'remindercount',
                'desc' => 'remindercount desc',
            ),

            'completed' => array(
            'asc' => 'completed',
            'desc' => 'completed desc',
            ),

            'usesleft' => array(
            'asc' => 'usesleft',
            'desc' => 'usesleft desc',
            ),

            'validfrom' => array(
            'asc' => 'validfrom',
            'desc' => 'validfrom desc',
            ),

            'validuntil' => array(
            'asc' => 'validuntil',
            'desc' => 'validuntil desc',
            ),
        );

        // Make sortable custom attributes
        foreach ($this->custom_attributes as $sColName => $oColumn) {
            $sort->attributes[$sColName] = array(
                'asc' => $sColName,
                'desc' => $sColName . ' desc',
            );
        }
        $this->decryptEncryptAttributes('encrypt');

        $criteria = new LSDbCriteria();
        $criteria->compare('tid', $this->tid, false);
        $criteria->compare('token', $this->token, true);
        $criteria->compare('firstname', $this->firstname, true);
        $criteria->compare('lastname', $this->lastname, true);
        $criteria->compare('email', $this->email, true);
        $criteria->compare('emailstatus', $this->emailstatus, true);
        $criteria->compare('token', $this->token, true);
        $criteria->compare('language', $this->language, true);
        $criteria->compare('sent', $this->sent, true);
        $criteria->compare('remindersent', $this->remindersent, true);
        $criteria->compare('remindercount', $this->remindercount, false);
        $criteria->compare('completed', $this->completed, true);
        $criteria->compare('usesleft', $this->usesleft, false);

        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
        if ($this->validfrom) {
            $s = DateTime::createFromFormat($dateformatdetails['phpdate'] . ' H:i', $this->validfrom);
            if (is_object($s)) {
                $s2 = $s->format('Y-m-d H:i');
                $criteria->addCondition('validfrom <= \'' . $s2 . '\'');
            }
        }

        if ($this->validuntil) {
            $s = DateTime::createFromFormat($dateformatdetails['phpdate'] . ' H:i', $this->validuntil);
            if (is_object($s)) {
                $s2 = $s->format('Y-m-d H:i');
                $criteria->addCondition('validuntil >= \'' . $s2 . '\'');
            }
        }

        foreach ($this->custom_attributes as $sColName => $oColumn) {
            $criteria->compare($sColName, $this->$sColName, true);
        }

        $dataProvider = new LSCActiveDataProvider(get_class($this), array(
            'sort' => $sort,
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => $pageSizeTokenView,
            ),
        ));
        $this->decryptEncryptAttributes();

        return $dataProvider;
    }

    /**
     * Returns true if the token can be used
     * @return bool
     */
    public function canBeUsed()
    {
        return !empty($this->token)
            && (
                $this->completed == "N"
                || empty($this->completed)
                || $this->survey->isAllowEditAfterCompletion
            );
    }

    /**
     * Returns true if an invitation or reminder can be sent to this participant
     * @return bool
     */
    public function canBeEmailed()
    {
        return !empty($this->token)
            && $this->emailstatus == "OK"
            && $this->email
            && $this->completed == "N"
            && (
                $this->usesleft > 0
                || $this->survey->isAllowEditAfterCompletion
            );
    }
}
