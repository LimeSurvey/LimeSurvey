<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use LimeSurvey\Models\Services\TranslationMoToJson;
use Permission;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;

class I18nMissing implements CommandInterface
{
    use AuthPermissionTrait;

    protected ResponseFactory $responseFactory;
    protected Permission $permission;

    /**
     * Constructor
     *
     * @param ResponseFactory $responseFactory
     * @param Permission $permission
     */
    public function __construct(
        ResponseFactory $responseFactory,
        Permission $permission
    ) {
        $this->responseFactory = $responseFactory;
        $this->permission = $permission;
    }

    /**
     * Handle missing translations
     * This will get an array of missing strings which need translation.
     * Those will be written in the file /application/helpers/newEditorTranslations
     * as gT('example');
     * The function checks if each string already exists in the file to avoid duplicates.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $keys = $request->getData('keys');

        if (empty($keys) || !is_array($keys)) {
            return $this->responseFactory
                ->makeError('Missing or invalid translation keys');
        }

        $filename = '/helpers/editorTranslations.php';
        $absolutePath = App()->getBasePath() . $filename;

        $updatedKeys = [];
        $existingKeys = [];
        $message = '';
        // fetching german to see what is already translated in the core app:
        App()->setLanguage('de');
        $transLateService = new TranslationMoToJson('de');
        $translations = $transLateService->translateMoToJson();
        if (!file_exists($absolutePath)) {
            // Create new file with header
            $content = "<?php\n// Translation-source for new editor. \n// Updated on "
                . date('Y-m-d H:i:s') . "\n\n";
            file_put_contents($absolutePath, $content);
        }

        try {
            // Make the file writable
            chmod($absolutePath, 0664);

            // Read existing content
            $content = file_get_contents($absolutePath);

            foreach ($keys as $key) {
                if (empty($key)) {
                    continue;
                }
                if (
                    is_array($translations)
                    && array_key_exists(
                        $key,
                        $translations
                    )
                ) {
                    $existingKeys[] = $key;
                    continue;
                }

                $newLine = "gT('" . addslashes($key) . "');";

                if (strpos($content, $newLine) === false) {
                    $content = rtrim($content, "\n") . "\n" . $newLine;
                    $updatedKeys[] = $key;
                } else {
                    $existingKeys[] = $key;
                }
            }

            if (!empty($updatedKeys)) {
                // Update the timestamp
                $content = preg_replace(
                    '/\/\/ Updated on .*/',
                    '// Updated on ' . date('Y-m-d H:i:s'),
                    $content
                );

                $success = file_put_contents($absolutePath, $content);
                if ($success === false) {
                    throw new \RuntimeException(
                        "Failed to write to file: $absolutePath"
                    );
                }
            }
        } catch (\Exception $e) {
            // Clear the updated keys as they weren't actually saved
            $updatedKeys = [];

            // Add error message
            $message = "Error: Failed to update translations. "
                . $e->getMessage();
        }

        if (!empty($updatedKeys)) {
            $message .= "Translation keys saved: " . implode(', ', $updatedKeys)
                . ". ";
        }
        if (!empty($existingKeys)) {
            $message .= "Translation keys already exist: " . implode(', ', $existingKeys) . ".";
        }

        return $this->responseFactory
            ->makeSuccess(['message' => $message]);
    }
}
