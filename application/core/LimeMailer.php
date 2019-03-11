<?php

require_once(APPPATH.'/third_party/phpmailer/load_phpmailer.php');

/**
 * WIP
 */
class LimeMailer extends \PHPMailer\PHPMailer\PHPMailer
{
    /**
     * Singleton
     * @var LimeMailer
     */
    private static $instance = null;

    /* Current survey id */
    public $surveyId;
    /* Current language */
    public $mailLanguage;
    /* Current email use html */
    public $html = true;
    /* email must be sent */
    private $sent = false;

    /* Current token object */
    public $oToken;

    /* Array for barebone url and url */
    public $aUrlsPlaceholders = array('OPTOUT', 'OPTIN', 'SURVEY');
    /**
     * Current email type, used for updating email raw subject and body
     * for survey : invite, 
     **/
    public $emailType = 'unknow';
    /* Current attachements */
    public $aAttachements = array();
    /**
     * The Raw Subject of the message. before any update
     * @var string
     */
    public $rawSubject = '';

    /**
     * The Rw Body of the message, before any update
     * @var string
     */
    public $rawBody = '';

    /**
     * Charset of Body and Subject
     */
    public $BodySubjectCharset = 'utf-8';

    /* var string */
    private $eventName = 'sendEmail';
    private $eventParam = array(
        'send' => true,
    );

    /* replacement fields */
    private $aReplacements = array();

    /* @todo */
    private $debug = array();

    /**
     * WIP Set all needed fixed in params
     */
    public function __construct()
    {
        parent::__construct(false);
        /* Global configuration for ALL email of this LimeSurvey instance */
        $emailmethod = Yii::app()->getConfig('emailmethod');
        $emailsmtphost = Yii::app()->getConfig("emailsmtphost");
        $emailsmtpuser = Yii::app()->getConfig("emailsmtpuser");
        $emailsmtppassword = Yii::app()->getConfig("emailsmtppassword");
        $emailsmtpdebug = Yii::app()->getConfig("emailsmtpdebug");
        $emailsmtpssl = Yii::app()->getConfig("emailsmtpssl");
        $defaultlang = Yii::app()->getConfig("defaultlang");
        $emailcharset = Yii::app()->getConfig("emailcharset");

        /* Set language for errors */
        if (!$this->SetLanguage(Yii::app()->getConfig("defaultlang"),APPPATH.'/third_party/phpmailer/language/')) {
            $this->SetLanguage('en',APPPATH.'/third_party/phpmailer/language/');
        }

        $this->mailLanguage = Yii::app()->getLanguage();

        $this->SMTPDebug = Yii::app()->getConfig("emailsmtpdebug");
        $this->Debugoutput = function($str, $level) {
            $this->addDebug($str);
        };

        if (Yii::app()->getConfig('demoMode')) {
            return;
        }

        $this->CharSet = Yii::app()->getConfig("emailcharset");

        /* Don't check tls by default : allow own sign certificate */
        $this->SMTPAutoTLS = false;

        switch ($emailmethod) {
            case "qmail":
                $this->IsQmail();
                break;
            case "smtp":
                $this->IsSMTP();
                if ($emailsmtpdebug > 0) {
                    $this->SMTPDebug = $emailsmtpdebug;
                }
                if (strpos($emailsmtphost, ':') > 0) {
                    $this->Host = substr($emailsmtphost, 0, strpos($emailsmtphost, ':'));
                    $this->Port = (int) substr($emailsmtphost, strpos($emailsmtphost, ':') + 1);
                } else {
                    $this->Host = $emailsmtphost;
                }
                if ($emailsmtpssl === 1) {
                    $this->SMTPSecure = "ssl";
                } elseif(!empty($emailsmtpssl)) {
                    $this->SMTPSecure = $emailsmtpssl;
                }
                $this->Username = $emailsmtpuser;
                $this->Password = $emailsmtppassword;
                if (trim($emailsmtpuser) != "") {
                    $this->SMTPAuth = true;
                }
                break;
            case "sendmail":
                $this->IsSendmail();
                break;
            default:
                $this->IsMail();
        }

        /* set default return path */
        if(!empty(Yii::app()->getConfig('siteadminbounce'))) {
            $this->Sender = Yii::app()->getConfig('siteadminbounce');
        }

        $this->addCustomHeader("X-Surveymailer",Yii::app()->getConfig("sitename")." Emailer (LimeSurvey.org)");
    }

    /**
     * needed by Yii::app() ?
     */
    public function init()
    {

    }
    /**
     * To get a singleton : some part are not needed to do X times
     * @param boolean reset partially $this
     * return self
     */
    public static function getInstance($reset=true)
    {
        Yii::log("Call instance", 'info', 'application.Mailer.LimeMailer.getInstance');
        if (empty(self::$instance)) {
            Yii::log("New mailer instance", 'info', 'application.Mailer.LimeMailer.getInstance');
            self::$instance = new self;
            /* no need to reset if new */
            return self::$instance;
        }
        Yii::log("Existing mailer instance", 'info', 'application.Mailer.LimeMailer.getInstance');
        self::$instance->send = true;
        if($reset) {
            self::$instance->clearAddresses(); // Unset only $this->to recepient
            self::$instance->clearAttachments(); // Unset attachments (maybe only under condition ?
        }
        return self::$instance;
    }

    /**
     * Set email for this survey
     * @param integer surveyid
     */
    public function setSurvey($surveyId)
    {
        $this->surveyId = $surveyId;
        $this->eventParam['survey'] = $surveyId;
        $oSurvey = Survey::model()->findByPk($surveyId);
        if(!empty($oSurvey->oOptions->adminemail)) {
            $this->setFrom($oSurvey->oOptions->adminemail,$oSurvey->oOptions->admin);
        }
        if(!empty($oSurvey->oOptions->bounce_email)) {
            // Check what for N : dis we leave default or not (if it's set and valid ?)
            $this->Sender = $oSurvey->oOptions->bounce_email;
        }
        $this->addCustomHeader("X-surveyid",$surveyId);
        $this->isHtml($oSurvey->getIsHtmlEmail());
        if(!in_array($this->mailLanguage,$oSurvey->getAllLanguages())) {
            $this->mailLanguage = $oSurvey->language;
        }
    }

    /**
     * Set email for this survey
     * @param string token
     * @throw CException
     */
    public function setToken($token)
    {
        if(empty($this->surveyId)) {
            throw new \CException("Survey must be set before set token");
        }
        /* Did need to check all here ? */
        $oToken =  \Token::model($this->surveyId)->findByToken($token);
        if(empty($oToken)) {
            throw new \CException("Invalid token");
        }
        $this->oToken = $oToken;
        $this->mailLanguage = Survey::model()->findByPk($this->surveyId)->language;
        if(in_array($oToken->language,Survey::model()->findByPk($this->surveyId)->getAllLanguages())) {
            $this->mailLanguage = $oToken->language;
        }
        $this->eventName = 'beforeTokenEmail';
        $aEmailaddresses = preg_split("/(,|;)/", $this->oToken->email);
        foreach ($aEmailaddresses as $sEmailaddress) {
            $this->addAddress($sEmailaddress,$oToken->firstname." ".$oToken->lastname);
        }
        $this->addCustomHeader("X-tokenid",$oToken->token);
    }

    /**
     * @inheritdoc
     * Fix first parameters if he had email + name ( Name <email> format)
      */
    public function setFrom($from,$fromname = "",$auto = true)
    {
        $fromemail = $from;
        if (strpos($from, '<')) {
            $fromemail = substr($from, strpos($from, '<') + 1, strpos($from, '>') - 1 - strpos($from, '<'));
            if(empty($fromname)) {
                $fromname = trim(substr($from, 0, strpos($from, '<') - 1));
            }
        }
        parent::setFrom($fromemail, $fromname, $auto);
    }

    /**
     * @inheritdoc
     * Fix first parameters if he had email + name ( Name <email> format)
     */
    public function addAddress($addressTo, $name = '')
    {
        $address = $addressTo;
        if (strpos($address, '<')) {
            $address = substr($addressTo, strpos($addressTo, '<') + 1, strpos($addressTo, '>') - 1 - strpos($addressTo, '<'));
            if(empty($name)) {
                $name = trim(substr($addressTo, 0, strpos($addressTo, '<') - 1));
            }
        }
        return parent::addAddress($address, $name);
    }

    /**
     * Get from
     * @return string from (email + name)
     */
    public function getFrom()
    {
        if(empty($this->FromName)) {
            return $this->From;
        }
        return $this->FromName." <".$this->From.">";
    }

    public function addDebug($str, $level = 0) {
        $this->debug[] = $str;
    }
    /**
     * Hate to use global var
     * maybe add format : raw (array of errors), html : clean html etc …
     * @param string $format
     * @return null|string|array
     */
    public function getDebug($format='')
    {
        if(empty($this->debug)) {
            return null;
        }
        switch ($format) {
            case 'html':
                $debug = array_map('CHtml::encode',$this->debug);
                return CHtml::tag("pre",array('class'=>'maildebug'),implode("",$debug));
                break;
            default:
                return $this->debug;
        }
    }
    /**
     * Hate to use global var
     * maybe add format : raw (array of errors), html : clean html etc …
     */
    public function getError()
    {
        return $this->ErrorInfo;
    }

    /**
     * Launch the needed event : beforeTokenEmail, beforeSurveyEmail, beforeGlobalEmail
     * and update this according to action
     * return boolean : stop sending or not
     */
    private function manageEvent($eventParams=array())
    {
        $model = $this->emailType;
        switch($this->emailType) {
            case 'invite':
                $model = 'invitation';
                break;
            case 'remind':
                $model = 'reminder';
                break;
            default:
                // $model == $this->emailType
        }
        $eventBaseParams = array(
            'survey'=>$this->surveyId,
            'type'=>$this->emailType,
            'model'=>$model,
            'to'=>$this->to, // To review for multiple tokens
            'subject'=>$this->Subject,
            'body'=>$this->Body,
            'from'=>$this->getFrom(),
            'bounce'=>$this->Sender,
        );
        if(!empty($this->oToken)) {
            $eventBaseParams['token'] = $this->oToken->getAttributes();
        }
        $eventParams = array_merge($eventBaseParams,$eventParams);
        $event = new PluginEvent($this->eventName);
        /**
         * plugin can get this mailer with $oEvent->get('mailer')
         * This allow udpate of anythings : $this->getEvent()->get('mailer')->addCC or $this->getEvent()->get('mailer')->addCustomHeader etc …
         **/
        $event->set('mailer',$this); //  no need to add other event param
        /* Previous plugin compatibility … */
        foreach($eventParams as $param=>$value) {
            $event->set($param, $value);
        }
        App()->getPluginManager()->dispatchEvent($event);
        /* Manage what can be updated */
        $this->Subject = $event->get('subject');
        $this->Body = (string) $event->get('body');
        $this->setFrom($event->get('from'));
        $this->to = $event->get('to');
        $this->Sender = $event->get('bounce');
        if($event->get('send', true) == false) {
            $this->sent = $event->get('error') == null;
            $this->ErrorInfo = $event->get('error');
            return $this->sent;
        }
        return false;
    }

    public function sendMessage()
    {
        if (Yii::app()->getConfig('demoMode')) {
            $this->setError(gT('Email was not sent because demo-mode is activated.'));
            return false;
        }
        if(!empty($this->rawSubject)) {
            $this->Subject = $this->doReplacements($this->rawSubject);
        }
        if(!empty($this->rawBody)) {
            $this->Body = $this->doReplacements($this->rawBody);
        }
        if($this->CharSet != $this->BodySubjectCharset) {
            /* Must test this … */
            $this->Subject = mb_convert_encoding($this->Subject,$this->CharSet,$this->BodySubjectCharset);
            $this->Body = mb_convert_encoding($this->Body,$this->CharSet,$this->BodySubjectCharset);
        }
        $this->setCoreAttachements();
        /* All core done, next are done for all survey */
        $eventResult = $this->manageEvent();
        if($eventResult) {
            return $this->sent;
        }
        /* Fix body according to HTML on/off */
        if($this->ContentType == 'text/html') {
            if (strpos($this->Body, "<html>") === false) {
                $this->Body = "<html>".$this->Body."</html>";
            }
            $this->msgHTML($this->Body, App()->getConfig("publicdir")); // This allow embedded image if we remove the servername from image
            if(empty($this->AltBody)) {
                $html = new \Html2Text\Html2Text($body);
                $this->AltBody = $this->getText();
            }
        }
        
        $this->sent = $this->Send();
        return $this->sent;
    }

    /**
     * Surely need to extend parent
     */
    public function Send()
    {
        if (Yii::app()->getConfig('demoMode')) {
            $this->setError(gT('Email was not sent because demo-mode is activated.'));
            return false;
        }
        return parent::Send();
    }

    /**
     * Get the replacements for token.
     * @return string[]
     */
    public function getTokenReplacements() {
        $aTokenReplacements = array();
        if(empty($this->oToken)) { // Did need to check if sent to token ?
            return $aTokenReplacements;
        }
        $language = Yii::app()->getLanguage();
        if(!in_array($language,Survey::model()->findByPk($this->surveyId)->getAllLanguages())) {
            $language = Survey::model()->findByPk($this->surveyId)->language;
        }
        $token = $this->oToken->token;
        if(!empty($this->oToken->language)) {
            $language = trim($this->oToken->language);
        }
        LimeExpressionManager::singleton()->loadTokenInformation($this->surveyId, $this->oToken->token);
        if($this->emailType == 'invite' or $this->emailType == 'remind') {
            foreach ($this->oToken->attributes as $attribute => $value) {
                $aTokenReplacements[strtoupper($attribute)] = $value;
            }
        }
        $aTokenReplacements["OPTOUTURL"] = App()->getController()
            ->createAbsoluteUrl("/optout/tokens", array("surveyid"=>$this->surveyId, "token"=>$token,"langcode"=>$language));
        $aTokenReplacements["OPTINURL"] = App()->getController()
            ->createAbsoluteUrl("/optin/tokens", array("surveyid"=>$this->surveyId, "token"=>$token,"langcode"=>$language));
        $aTokenReplacements["SURVEYURL"] = App()->getController()
            ->createAbsoluteUrl("/survey/index", array("sid"=>$this->surveyId, "token"=>$token,"lang"=>$language));
        return $aTokenReplacements;
    }

    /**
     * Do the replacements
     * @param string
     * @return string
     */
    public function doReplacements($string)
    {
        $aReplacements = array();
        if($this->surveyId) {
            $aReplacements["SID"] = $this->surveyId;
            $aReplacements["EXPIRY"] = Survey::model()->findByPk($this->surveyId)->expires;
        }
        $aReplacements = array_merge($aReplacements,$this->getTokenReplacements());
        /* Fix Url replacements : by option ? */
        foreach ($this->aUrlsPlaceholders as $urlPlaceholder) {
            if(!empty($aReplacements["{$urlPlaceholder}URL"])) {
                $url = $aReplacements["{$urlPlaceholder}URL"];
                $string = str_replace("@@{$urlPlaceholder}URL@@", $url, $string);
                $aReplacements["{$urlPlaceholder}URL"] = Chtml::link("{$urlPlaceholder}URL","{$urlPlaceholder}URL");
            }
        }
        $aReplacements = array_merge($this->aReplacement,$aReplacements);
        return LimeExpressionManager::ProcessString($string, null, $aReplacements, 3, 1, false, false, true);
    }

    /**
     * Set the attahchments according to current survey,language and emailtype
     * @param string
     * @return string
     */
    public function setCoreAttachements()
    {
        if(empty($this->surveyId)) {
            return;
        }
        $oSurveyLanguageSetting = SurveyLanguageSetting::model()->findByPk(array('surveyls_survey_id'=>$this->surveyId, 'surveyls_language'=>$this->mailLanguage));
        if(!empty($oSurveyLanguageSetting->attachments) ) {
            $aAttachments = unserialize($oSurveyLanguageSetting->attachments);
            if(!empty($aAttachments[$this->emailType])) {
                if($this->oToken) {
                    LimeExpressionManager::singleton()->loadTokenInformation($this->surveyId, $this->oToken->token);
                }
                foreach ($aAttachments[$sTemplate] as $aAttachment) {
                    if (LimeExpressionManager::singleton()->ProcessRelevance($aAttachment['relevance'])) {
                        $this->addAttachment($aAttachment['url']);
                    }
                }
            }
        }
        
    }
}
