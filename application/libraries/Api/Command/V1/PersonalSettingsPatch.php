<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Auth\AuthSession;
use LimeSurvey\Api\Command\Request;
use LimeSurvey\Api\Command\Response;

/**
 * Command to update personal settings
 */
class PersonalSettingsPatch implements CommandInterface
{
    /**
     * @var AuthSession
     */
    private $authSession;

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
     * Execute the command
     * 
     * @param Request $request
     * @return Response
     */
    public function execute(Request $request): Response
    {
        // Get the user ID from the auth session
        $userId = $this->authSession->getUserId();
        
        // Get the settings data from the request
        $settingsData = $request->getBody();
        
        // Update the user's personal settings
        $result = $this->updatePersonalSettings($userId, $settingsData);
        
        // Return a success response
        return new Response([
            'status' => 'success',
            'message' => 'Personal settings updated successfully'
        ]);
    }

    /**
     * Update the personal settings for a user
     * 
     * @param int $userId
     * @param array $settingsData
     * @return bool
     */
    private function updatePersonalSettings($userId, $settingsData)
    {
        // Get the user model
        $user = \User::model()->findByPk($userId);
        
        if (!$user) {
            throw new \Exception('User not found');
        }
        
        // Update user settings based on the provided data
        // This is a simplified example - you'll need to adapt this to your actual settings structure
        if (isset($settingsData['answeroptionprefix'])) {
            $user->email = $settingsData['answeroptionprefix'];
        }
        
        if (isset($settingsData['subquestionprefix'])) {
            $user->full_name = $settingsData['subquestionprefix'];
        }
        
        if (isset($settingsData['showQuestionCodes'])) {
            $user->lang = $settingsData['showQuestionCodes'];
        }
        
        // Add more settings as needed
        
        // Save the changes
        return $user->save();
    }
}