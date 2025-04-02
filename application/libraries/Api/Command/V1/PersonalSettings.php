<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Auth\AuthSession;

class PersonalSettings implements CommandInterface
{
    /**
     * @var AuthSession
     */
    protected $authSession;

    /**
     * Constructor
     *
     * @param AuthSession $authSession
     */
    public function __construct(AuthSession $authSession)
    {
        $this->authSession = $authSession;
    }

    /**
     * Execute the command and return personal settings
     *
     * @return array
     */
    public function execute()
    {
        // Get the current user
        // $settings = $this->
        
        // Fetch personal settings for the user
        // to retrieve the user's personal settings
        $personalSettings = [

        ];
        
        return $personalSettings;
    }
}