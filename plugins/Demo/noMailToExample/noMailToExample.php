<?php
/**
 * noMailToExample : just don't send email to example.org or example.com
 * http://example.org/ is a great tool for demonstration and test, but sending an email to user@example.org: you receive 4 hour after a notification
 * This plugin just disable sending email to this website, then you can use it when testing syste.
 *
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2016 Denis Chenu <http://www.sondages.pro>
 * @license MIT
 * @version 1.0.0
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * The MIT License
 */
class noMailToExample extends PluginBase
{
    static protected $description = 'Don\t send email to example.(com|org)';
    static protected $name = 'noMailToExample';

    
    public function init()
    {
        $this->subscribe('beforeEmail','beforeEmail');
        $this->subscribe('beforeSurveyEmail','beforeEmail');
        $this->subscribe('beforeTokenEmail','beforeEmail');
    }

    /**
     * Set event send to false when sending an email to example.(com|org)
     * @link https://manual.limesurvey.org/BeforeTokenEmail
     */
    public function beforeEmail()
    {
        $emailTos=$this->getEvent()->get("to");
        if(empty($emailTos)) {
            return;
        }
        if(is_string($emailTos)) {
            $emailTos=array($emailTos);
        }

        /* @var string[] no example.(org|com) from the list */
        $cleanedEmailTos=array();
        foreach($emailTos as $emailTo){
            $emailOnly=$emailTo[0];
            /* @var string only domain from email */
            $domainName = strtolower(substr(strrchr($emailOnly, "@"), 1));

            if($domainName=='example.com' || $domainName=='example.org'){
                /* temporary set send to false : deactivate sending email on all emails */
                $this->getEvent()->set("send",false);
                $this->getEvent()->set("error","No email was sent to example.(org|com)");
            }else{
                $cleanedEmailTos[]=$emailTo;
            }
        }
        /* If we have a list of email with some example.(org|com) and other : set new list to cleaned list */
        if($this->event->get("send",true)===false && !empty($cleanedEmailTos)){
            $this->event->set("send",true);
            $this->event->set("to",$cleanedEmailTos);
        }
    }
}
