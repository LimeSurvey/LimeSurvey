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
     * @method Token usable() usable() Select usable tokens: valid daterange and userleft > 0
     *
     */
    abstract class Token extends Dynamic
    {
        /**
         * @var string Captcha used in registration scenario.
         */
        public $captcha;
        public function attributeLabels() {
            $labels = array(
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
                'remindercount' =>gT('Total numbers of sent reminders'),
                'completed' => gT('Completed'),
                'usesleft' => gT('Uses left'),
                'validfrom' => gT('Valid from'),
                'validuntil' => gT('Valid until'),
            );
			foreach (decodeTokenAttributes($this->survey->attributedescriptions) as $key => $info)
            {
                $labels[$key] = $info['description'];
            }
            return $labels;
        }

        public function beforeDelete() {
            $result = parent::beforeDelete();
            if ($result && isset($this->surveylink))
            {
                if (!$this->surveylink->delete())
                {
                    throw new CException('Could not delete survey link. Token was not deleted.');
                }
                return true;
            }
            return $result;
        }

        public static function createTable($surveyId, array $extraFields = [])
        {
            $surveyId=intval($surveyId);
            // Specify case sensitive collations for the token
            $sCollation='';
            if  (Yii::app()->db->driverName=='mysqli' | Yii::app()->db->driverName=='mysqli'){
                $sCollation="COLLATE 'utf8_bin'";
            }
            if  (Yii::app()->db->driverName=='sqlsrv' | Yii::app()->db->driverName=='dblib' | Yii::app()->db->driverName=='mssql'){
                $sCollation="COLLATE SQL_Latin1_General_CP1_CS_AS";
            }             
            $fields = [
                'tid' => 'pk',
                'participant_id' => 'string(50)',
                'firstname' => 'string(40)',
                'lastname' => 'string(40)',
                'email' => 'text',
                'emailstatus' => 'text',
                'token' => "string(35) {$sCollation}",
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
            $tokenattributefieldnames = Survey::model()->findByPk($surveyId)->tokenAttributes;
            foreach($tokenattributefieldnames as $attrname=>$attrdetails)
            {
                if (!isset($fields[$attrname])) {
                    $fields[$attrname] = 'string(255)';
                }
            }

            $db = \Yii::app()->db;
            $sTableName = self::constructTableName($surveyId);

            $db->createCommand()->createTable($sTableName, $fields);
            /**
             * Random not needed for:
             * - PostgreSQL
             * - MySQL
             * - MSSQL
             *
             */
            $db->createCommand()->createIndex("idx_token_token_{$surveyId}_".rand(1,50000),  $sTableName,'token');
            
            // Refresh schema cache just in case the table existed in the past, and return if table exist
            return $db->schema->getTable($sTableName, true);
        }
        public function findByToken($token)
        {
            return $this->findByAttributes(array(
                'token' => $token
            ));
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
            while (!$this->validate(['token']))
            {
                $this->token = \Yii::app()->securityManager->generateRandomString($length);
                $counter++;
                // This is extremely unlikely.
                if ($counter > 10)
                {
                    throw new CHttpException(500, 'Failed to create unique token in 10 attempts.');
                }
            }
        }

        /**
         * Generates a token for all token objects in this survey.
         * Syntax: Token::model(12345)->generateTokens();
         */
        public function generateTokens() {
            if ($this->scenario != '') {
                throw new \Exception("This function should only be called like: Token::model(12345)->generateTokens");
            }
            /**
             * @todo Generate tokens in SQL.
             */
            //$sql = "SUBSTRING(CONCAT(MD5(RAND()), MD5(RAND())), 1, 15)";

            $surveyId = $this->dynamicId;
            $tokenLength = isset($this->survey) && is_numeric($this->survey->tokenlength) ? $this->survey->tokenlength : 15;

            $tkresult = Yii::app()->db->createCommand("SELECT tid FROM {{tokens_{$surveyId}}} WHERE token IS NULL OR token=''")->queryAll();
            //Exit early if there are not empty tokens
            if (count($tkresult)===0) return array(0,0);

            //get token length from survey settings
            $tlrow = Survey::model()->findByAttributes(array("sid"=>$surveyId));

            //Add some criteria to select only the token field
            $criteria = $this->getDbCriteria();
            $criteria->select = 'token';
            $ntresult = $this->findAllAsArray($criteria);   //Use AsArray to skip active record creation

            // select all existing tokens
            foreach ($ntresult as $tkrow)
            {
                $existingtokens[$tkrow['token']] = true;
            }

            $newtokencount = 0;
            $invalidtokencount=0;
            foreach ($tkresult as $tkrow)
            {
                $bIsValidToken = false;
                while ($bIsValidToken == false && $invalidtokencount<50)
                {
                    $newtoken = App()->securityManager->generateRandomString($tokenLength);
                    if (!isset($existingtokens[$newtoken]))
                    {
                        $existingtokens[$newtoken] = true;
                        $bIsValidToken = true;
                        $invalidtokencount=0;
                    }
                    else
                    {
                        $invalidtokencount ++;
                    }
                }
                if($bIsValidToken)
                {
                    $itresult = $this->updateByPk($tkrow['tid'], ['token' => $newtoken]);
                    $newtokencount++;
                }
                else
                {
                    break;
                }
            }

            return array($newtokencount,count($tkresult));

        }
        /**
         *
         * @param mixed $className Either the classname or the survey id.
         * @return Token
         */
        public static function model($className = null) {
            return parent::model($className);
        }

        /**
         *
         * @param int $surveyId
         * @param string $scenario
         * @return Token Description
         */
        public static function create($surveyId, $scenario = 'insert') {
            return parent::create($surveyId, $scenario);
        }

        public function relations()
        {
            $result = [
                'survey' =>  [self::BELONGS_TO, 'Survey', '', 'on' => "sid = {$this->dynamicId}"],
                'surveylink' => [self::BELONGS_TO, 'SurveyLink', ['participant_id' => 'participant_id'], 'on' => "survey_id = {$this->dynamicId}"]
            ];

            if (\Response::valid($this->dynamicId)) {
                $result['responses'] = [self::HAS_MANY, 'Response_' . $this->dynamicId, ['token' => 'token']];
            }
            return $result;
        }

        /**
         * This function is used when the survey is not active and thus the relation above is not added.
         * It is protected since you should use ->responses to make sure you get the relation if it is available.
         * @return array
         */
        protected function getResponses() {
            return [];
        }

        /**
         * @return array
         */
        protected function getResponseCount() {
            return Response::valid($this->dynamicId) ? Response::model($this->dynamicId)->countByAttributes(['token' => $this->token]) : 0;
        }
        public function rules()
        {
            $aRules= array(
                array('token', 'unique', 'allowEmpty' => true),
                array('firstname','LSYii_Validators'),
                array('lastname','LSYii_Validators'),
                array(implode(',', $this->tableSchema->columnNames), 'safe'),
                array('remindercount','numerical', 'integerOnly'=>true,'allowEmpty'=>true),
                array('email','filter','filter'=>'trim'),
                array('email','LSYii_EmailIDNAValidator', 'allowEmpty'=>true, 'allowMultiple'=>true,'except'=>'allowinvalidemail'),
                array('usesleft','numerical', 'integerOnly'=>true,'allowEmpty'=>true),
                array('mpid','numerical', 'integerOnly'=>true,'allowEmpty'=>true),
                array('blacklisted', 'in','range'=>array('Y','N'), 'allowEmpty'=>true),
                array('emailstatus', 'default', 'value' => 'OK'),

                ['email', 'email', 'on' => 'register'],
                ['email', 'unique', 'on' => 'register'],
                [['lastname', 'firstname'], 'safe', 'on' => 'register'],
                ['captcha', 'captcha', 'on' => 'register'],

            );
            foreach (decodeTokenAttributes($this->survey->attributedescriptions) as $key => $info)
            {
                 $aRules[]=array($key,'LSYii_Validators');
            }
            return $aRules;
        }

        public function scopes()
        {
            $now = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig("timeadjust"));
            return array(
                'incomplete' => array(
                    'condition' => "completed = 'N'"
                ),
                'usable' => array(
                    'condition' => "COALESCE(validuntil, '$now') >= '$now' AND COALESCE(validfrom, '$now') <= '$now'"
                ),
                'editable' => array(
                    'condition' => "COALESCE(validuntil, '$now') >= '$now' AND COALESCE(validfrom, '$now') <= '$now'"
                ),
                'empty' => array(
                    'condition' => 'token is null or token = ""'
                )
            );
        }

        public function summary()
        {
            $criteria = $this->getDbCriteria();
            $criteria->select = array(
                "COUNT(*) as count",
                "COUNT(CASE WHEN (token IS NULL OR token='') THEN 1 ELSE NULL END) as invalid",
                "COUNT(CASE WHEN (sent!='N' AND sent<>'') THEN 1 ELSE NULL END) as sent",
                "COUNT(CASE WHEN (emailstatus LIKE 'OptOut%') THEN 1 ELSE NULL END) as optout",
                "COUNT(CASE WHEN (completed!='N' and completed<>'') THEN 1 ELSE NULL END) as completed",
                "COUNT(CASE WHEN (completed='Q') THEN 1 ELSE NULL END) as screenout",
            );
            $command = $this->getCommandBuilder()->createFindCommand($this->getTableSchema(),$criteria);
            return $command->queryRow();
        }

        public static function constructTableName($id)
        {
            return '{{token_' . $id . '}}';
        }

        public function getSurveyId() {
            return $this->dynamicId;
        }

        public function getIsExpired() {
            return !empty($this->expires)
            && (new DateTime($this->expires)) < new DateTime()
            && (new DateTime($this->validfrom)) > new DateTime();
        }


        public function customAttributeNames() {
            return array_filter($this->attributeNames(), function($attribute) {
                return strncmp("attribute_", $attribute, 10) === 0;
            });
        }
    }

?>
