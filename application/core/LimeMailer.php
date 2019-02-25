<?php

//~ namespace LimeSurvey\Mailer;
//~ use \Yii;
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
    /* Current token object */
    public $oToken;

    /* var boolean */
    public $mailDisable = false;

    /* var string */
    private $eventName = 'sendGlobalEmail';
    private $eventParam = array(
        'subject' => '',
        'body' => '',
        'type' => 'unknow',
        'survey' => null,
        'send' => true,
    );

    /* replacement fields */
    private $aReplacements = array();

    /* @todo */
    private $errors;
    private $debug;
    private $debugbody;

    /**
     * WIP Set all needed fixed in params
     */
    public function __construct()
    {
        /* Global configuration for ALL email of this LimeSurvey instance */
        $emailmethod = Yii::app()->getConfig('emailmethod');
        $emailsmtphost = Yii::app()->getConfig("emailsmtphost");
        $emailsmtpuser = Yii::app()->getConfig("emailsmtpuser");
        $emailsmtppassword = Yii::app()->getConfig("emailsmtppassword");
        $emailsmtpdebug = Yii::app()->getConfig("emailsmtpdebug");
        $emailsmtpssl = Yii::app()->getConfig("emailsmtpssl");
        $defaultlang = Yii::app()->getConfig("defaultlang");
        $emailcharset = Yii::app()->getConfig("emailcharset");
        if (Yii::app()->getConfig('demoMode')) {
            $this->mailDisable = true;
            return;
        }
        /* language and charset */
        if (!$this->SetLanguage(Yii::app()->getConfig("defaultlang"),APPPATH.'/third_party/phpmailer/language/'))
        {
            $this->SetLanguage('en',APPPATH.'/third_party/phpmailer/language/');
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

        /* @todo : set default return path */
    }

    /**
     * needed by Yii::app() ?
     */
    public function init()
    {

    }
    /**
     * To get a singleton : some part are not needed to do X times
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
        if($reset) {
            self::$instance->clearAddresses();
            /* @todo : set default return path after check this reset */
            self::$instance->clearCustomHeaders();
            self::$instance->clearAttachments();
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
    }

    /**
     * Set email for this survey
     * @param string token
     * @throw CException
     */
    public function setToken($token)
    {
        if(empty($this->surveyId)) {
            throw new \CException("Survey is must be set before set token");
        }
        /* Did need to check all here ? */
        $oToken =  \Token::model($this->surveyId)->findByToken($token);
        if(empty($oToken)) {
            throw new \CException("Invalid token");
        }
        $this->oToken = $oToken;
        $this->eventParam['token'] = $oToken->getAttributes();
        $this->eventName = 'beforeTokenEmail';
    }

    /**
     * Set from
     * @param string from
     */
    public function setFrom($from)
    {
        $fromemail = $from;
        $fromname = "";
        if (strpos($from, '<')) {
            $fromemail = substr($from, strpos($from, '<') + 1, strpos($from, '>') - 1 - strpos($from, '<'));
            $fromname = trim(substr($from, 0, strpos($from, '<') - 1));
        }
        /* @todo : validate email format */
        parent::SetFrom($fromemail, $fromname);
        $mail->Sender = $fromemail;
    }

    /**
     * Set bounce
     * @param string from
     */
    public function setBounce($bounce)
    {
        /* @todo : validate email format */
        $mail->Sender = $bounce;
    }

    public function setRawSubject($subject)
    {

    }

    public function setRawBody($subject)
    {

    }

    public function getDebug($format='html')
    {

    }
    /**
     * Launch the needed event : beforeTokenEmail, beforeSurveyEmail, beforeGlobalEmail
     * and update this according to action
     */
    private function manageEvent()
    {
        $event = new PluginEvent($this->eventName);
        $event->set('survey', $iSurveyId);
        $event->set('type', $sTemplate);
        $event->set('model', $sSubAction);
        $event->set('subject', $modsubject);
        $event->set('to', $to);
        $event->set('body', $modmessage);
        $event->set('from', $from);
        $event->set('bounce', getBounceEmail($iSurveyId));
        $event->set('token', $emrow);
        App()->getPluginManager()->dispatchEvent($event);
        /* Manage what can be updated */
        $subject = $event->get('subject');
        $message = (string) $event->get('body');
        $to = $event->get('to');
        $from = $event->get('from');
        $bounce = $event->get('bounce');
    }

    /**
     * Set replacements (by language)
     */
    
    /**
     * Surely need to extend parent
     */
    public function Send()
    {
        if($this->mailDisable) {
            // @todo : add error
            return;
        }
        return parent::Send();
    }

    /**
     * Hate to use global var
     * maybe add format : raw (array of errors), html : clean html etc …
     */
    public function getErrors()
    {
        // @todo
    }
    /**
     * Hate to use global var
     */
    public function getDebug()
    {
        // @todo
    }
}
