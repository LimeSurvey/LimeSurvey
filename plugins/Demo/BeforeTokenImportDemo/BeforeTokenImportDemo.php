<?php
/**
 * BeforeTokenImportDemo Plugin
 *
 * Demo plugin for beforeTokenImport event
 */
class BeforeTokenImportDemo extends \PluginBase
{
    static protected $description = 'BeforeTokenImportDemo';
    static protected $name = 'BeforeTokenImportDemo';

    public function init()
    {
        $this->subscribe('beforeTokenImport');
    }

    public function beforeTokenImport()
    {
        $oEvent = $this->event;

        // Retrieve token data from event
        $tokenData = $oEvent->get('token');

        // Reject tokens without Last Name
        if (empty($tokenData['lastname'])) {
            $oEvent->set('errorMessage', '%s records with empty Last Name ignored');
            $oEvent->set('tokenSpecificErrorMessage', "Record ".$oEvent->get('recordCount')." doesn't have a last name");
            $oEvent->set('importValid', false);
            return;
        } 

        // Replace Last Name by its first letter
        $tokenData['lastname'] = strtoupper(substr($tokenData['lastname'], 0, 1));
        $oEvent->set('token', $tokenData);
        $oEvent->set('importValid', true);
    }

}