<?php
/**
 * Plugin to add List-Unsubscribe to token-email
 *
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2019 LimeSurvey team <https://www.limesurvey.org>
 * @license GPL v3
 * @version 1.0.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
class listUnsubscribeHeader extends PluginBase
{
    static protected $name = 'listUnsubscribeHeader';
    static protected $description = 'Add List-Unsubscribe to token invite, remind and register email.';

    public function init()
    {
        $this->subscribe('beforeTokenEmail','addListUnsubscribeHeader');
    }

    /**
     * beforeTokenEmail event registred function, adding List-Unsuscibe header if needed
     * @return @void
     */
    public function addListUnsubscribeHeader()
    {
        $emailType = $this->getEvent()->get('type');
        if( !in_array($emailType,['invite','remind','register']) ) {
            return;
        }
        $surveyId = $this->getEvent()->get('survey');
        /* get the token */
        $aTokenAttributes = $this->getEvent()->get('token');
        if(empty($aTokenAttributes['token'])) {
            /* What happenn ? An error ? We can‘t generate optouturl … */
            return;
        }
        $optOutUrl = $this->api->createUrl("/optout/tokens", array("surveyid"=>$surveyId, "token"=>$aTokenAttributes['token'],"langcode"=>$aTokenAttributes['language']));
        $this->getEvent()->get('mailer')->addCustomHeader('List-Unsubscribe',$optOutUrl);
    }
}
