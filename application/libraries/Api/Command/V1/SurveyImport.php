<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Command\ResponseData\ResponseDataError;
use LimeSurvey\Models\Services\UploadValidator;

/**
 * Import a complete survey from an uploaded LSS, LSA, TXT or TSV file.
 */
class SurveyImport implements CommandInterface
{
    private const ALLOWED_EXTENSIONS = ['lss', 'lsa', 'txt', 'tsv'];
    private const GROUP_STRATEGIES = ['default', 'from_survey'];

    protected ResponseFactory $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function run(Request $request): Response
    {
        if (!\Permission::model()->hasGlobalPermission('surveys', 'create')) {
            return $this->responseFactory->makeErrorForbidden(
                $this->error('SURVEY_IMPORT_FORBIDDEN', gT('Access denied!'))
            );
        }

        $files = $request->getData('filesGlobal', []);
        $validator = new UploadValidator($_POST, $files);
        $uploadError = $validator->getError('file');
        if ($uploadError !== null) {
            return $this->responseFactory->makeErrorBadRequest(
                $this->error('SURVEY_IMPORT_UPLOAD_ERROR', $uploadError)
            );
        }

        $uploadedFile = $files['file'];
        $extension = strtolower((string) pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            return $this->responseFactory->makeErrorBadRequest(
                $this->error(
                    'SURVEY_IMPORT_INVALID_FILE_TYPE',
                    sprintf(gT("Import failed. You specified an invalid file type '%s'."), $extension)
                )
            );
        }

        $temporaryPath = \Yii::app()->getConfig('tempdir')
            . DIRECTORY_SEPARATOR
            . randomChars(30)
            . '.'
            . $extension;

        if (!move_uploaded_file($uploadedFile['tmp_name'], $temporaryPath)) {
            return $this->responseFactory->makeErrorBadRequest(
                $this->error(
                    'SURVEY_IMPORT_MOVE_FAILED',
                    gT(
                        'An error occurred uploading your file. '
                        . 'This may be caused by incorrect permissions for the application /tmp folder.'
                    )
                )
            );
        }

        try {
            \Yii::app()->loadHelper('admin.import');
            $groupStrategy = (string) $request->getData('surveysgroup', 'default');
            if (!in_array($groupStrategy, self::GROUP_STRATEGIES, true)) {
                $groupStrategy = 'default';
            }

            $result = importSurveyFile(
                $temporaryPath,
                $this->toBoolean($request->getData('translinksfields', '1')),
                null,
                null,
                null,
                $groupStrategy
            );

            if (!is_array($result) || !empty($result['error'])) {
                $message = is_array($result) && !empty($result['error'])
                    ? $result['error']
                    : gT('Unknown error while reading the file, no survey created.');
                return $this->responseFactory->makeErrorBadRequest(
                    $this->error('SURVEY_IMPORT_FAILED', $message)
                );
            }

            if (!empty($result['newsid'])) {
                $this->resetExpressionManager((int) $result['newsid']);
            }

            return $this->responseFactory->makeSuccess($result);
        } catch (\Throwable $exception) {
            return $this->responseFactory->makeErrorBadRequest(
                $this->error('SURVEY_IMPORT_FAILED', $exception->getMessage())
            );
        } finally {
            if (is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
        }
    }

    private function toBoolean($value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'on'], true);
    }

    /**
     * Rebuild expression-manager data just as the core import action does.
     */
    private function resetExpressionManager(int $surveyId): void
    {
        $survey = \Survey::model()->findByPk($surveyId);
        if ($survey === null) {
            return;
        }

        $groups = \QuestionGroup::model()->findAllByAttributes(['sid' => $surveyId]);
        \LimeExpressionManager::SetDirtyFlag();
        \LimeExpressionManager::singleton();
        \LimeExpressionManager::SetSurveyId($surveyId);
        \LimeExpressionManager::RevertUpgradeConditionsToRelevance($surveyId);
        \LimeExpressionManager::UpgradeConditionsToRelevance($surveyId);
        @\LimeExpressionManager::StartSurvey($surveyId, 'survey', $survey->attributes, true);
        \LimeExpressionManager::StartProcessingPage(true, true);
        foreach ($groups as $group) {
            \LimeExpressionManager::StartProcessingGroup(
                $group->gid,
                $survey->anonymized !== 'Y',
                $surveyId
            );
            \LimeExpressionManager::FinishProcessingGroup();
        }
        \LimeExpressionManager::FinishProcessingPage();
    }

    private function error(string $code, string $message): array
    {
        return (new ResponseDataError($code, $message))->toArray();
    }
}
