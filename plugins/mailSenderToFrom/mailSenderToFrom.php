<?php
/**
 * mailSenderToFrom : Set the smtp user to sender and from
 * Needed for some smtp server, see mantis issue #10529 <https://bugs.limesurvey.org/view.php?id=10529>
 *
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2019 Denis Chenu <http://www.sondages.pro>
 * @license MIT
 * @version 1.0.0
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * The MIT License
 */
class mailSenderToFrom extends PluginBase
{
    static protected $description = 'Set sender to the SMTP user.';
    static protected $name = 'mailSenderToFrom';

    
    public function init()
    {
        $this->subscribe('beforeEmail','beforeEmail');
        $this->subscribe('beforeSurveyEmail','beforeEmail');
        $this->subscribe('beforeTokenEmail','beforeEmail');
    }

    /**
     * Set From and Bounce of PHPmailer to siteadminemail
     * @link https://manual.limesurvey.org/BeforeTokenEmail
     */
    public function beforeEmail()
    {
        $emailsmtpuser = Yii::app()->getConfig('emailsmtpuser');
        if(empty($emailsmtpuser)) {
            return;
        }
        $limeMailer = $this->getEvent()->get('mailer');
        $limeMailer->AddReplyTo($limeMailer->From, $limeMailer->FromName);
        $limeMailer->From = $emailsmtpuser;
        $limeMailer->Sender = $emailsmtpuser;
        $updateDisable = $this->getEvent()->get('updateDisable');
        $updateDisable['from'] = true;
        $updateDisable['bounce'] = true;
        $this->getEvent()->set('updateDisable',$updateDisable);
    }
}
