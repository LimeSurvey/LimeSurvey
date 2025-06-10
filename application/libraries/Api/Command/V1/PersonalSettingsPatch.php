<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\ResponseFactory;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use LimeSurvey\Api\Command\Response\Response;

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

        // Validate that userId is not null
        if ($userId === null) {
            return $this->responseFactory
                ->makeError('User ID is required');
        }

        // Get the settings data from the request
        $settingsData = ['showQuestionCodes' => $request->getData('showQuestionCodes', false),];

        // Update the user's personal settings
        try {
            $result = $this->updatePersonalSettings($userId, $settingsData);

            if (!$result) {
                return $this->responseFactory
                    ->makeError('Error updating personal settings');
            }

            // Return a success response
            return $this->responseFactory
                ->makeSuccess([
                'status' => 'success',
                'message' => 'Personal settings updated successfully'
            ]);
        } catch (\Exception $e) {
            return $this->responseFactory
                ->makeError($e->getMessage());
        }
    }

    /**
     * Update the personal settings for a user
     * As of now only showQuestionCodes setting is possible
     *
     * @param int $userId User ID (must not be null)
     * @param array $settingsData
     * @return boolean
     */
    private function updatePersonalSettings(int $userId, array $settingsData)
    {
        $result = false;
        if (isset($settingsData['showQuestionCodes'])) {
            $result = \SettingsUser::setUserSetting(
                'showQuestionCodes',
                $settingsData['showQuestionCodes'] ? 1 : 0,
                $userId
            );
        }
        return $result;
    }
}
