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
     * This will get single missing strings which miss a translation.
     * Those will be written in the file /application/helpers/newEditorTranslations
     * as gT('example');
     * The function checks if this string already exists in the file to avoid duplicates.
     * Before pushing the file it should be checked if the newly added strings
     * are worthy to be pushed.
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $key = $request->getData('key');

        if (empty($key)) {
            return $this->responseFactory
                ->makeError('Missing translation key');
        }

        $filename = '/helpers/newEditorTranslations.php';
        $absolutePath = App()->getBasePath() . $filename;

        $newLine = "gT('" . addslashes($key) . "');";
        $updated = false;
        $success = false;

        if (!file_exists($absolutePath)) {
            // Create new file with header
            $content = "<?php\n// Translation-source for new editor. \n// Updated on " . date('Y-m-d H:i:s') . "\n\n" . $newLine . "\n";
            $success = file_put_contents($absolutePath, $content);
            $updated = $success !== false;
        } else {
            // Read existing content
            $content = file_get_contents($absolutePath);

            // Check if the key already exists
            if (strpos($content, $newLine) === false) {
                // Append new line to the file
                $content = rtrim($content, "\n") . "\n" . $newLine . "\n";

                // Update the timestamp
                $content = preg_replace(
                    '/\/\/ Updated on .*/',
                    '// Updated on ' . date('Y-m-d H:i:s'),
                    $content
                );

                file_put_contents($absolutePath, $content);
                $updated = true;
            }
        }

        if ($updated) {
            return $this->responseFactory
                ->makeSuccess(['message' => "Translation key saved: $key"]);
        } else {
            return $this->responseFactory
                ->makeSuccess(['message' => "Translation key already exists: $key"]);
        }
    }
}
