<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Auth\AuthSession;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\ResponseFactory;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;

/**
 * Command to update personal settings
 */
class PersonalSettingsPatch implements CommandInterface
{
    use AuthPermissionTrait;

    protected ResponseFactory $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Execute the command
     * 
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        // Get the user IDs
        $userId = $request->getData('_id');
        
        // Get the settings data from the request
        $settingsData = ['showQuestionCodes' => $request->getData('showQuestionCodes', false),];
        
        // Update the user's personal settings
        $result = $this->updatePersonalSettings($userId, $settingsData);
        
        // Return a success response
        return $this->responseFactory
            ->makeSuccess([
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
            \SettingsUser::setUserSetting('showQuestionCodes', $settingsData['showQuestionCodes'] ? 1 : 0, $userId);
        }
        
        // Add more settings as needed
        
        // Save the changes
        return $user->save();
    }
}