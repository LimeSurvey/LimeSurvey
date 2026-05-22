<?php

namespace LimeSurvey\Api\Command\V1;

use LimeExpressionManager;
use LSYii_Validators;
use Permission;
use Survey;
use Yii;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory,
    ResponseData\ResponseDataError
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;

/**
 * API Command to retrieve the survey logic overview as rendered HTML.
 * Will be reworked to return proper json output once design is ready for it.
 */
class SurveyLogic implements CommandInterface
{
    use AuthPermissionTrait;

    /** Error code returned when the requested survey does not exist. */
    public const ERROR_SURVEY_NOT_FOUND = 'SURVEY_NOT_FOUND';

    protected Permission $permission;
    protected ResponseFactory $responseFactory;
    protected Survey $survey;

    /**
     * Constructor
     *
     * @param Permission $permission
     * @param ResponseFactory $responseFactory
     * @param Survey $survey
     */
    public function __construct(
        Permission $permission,
        ResponseFactory $responseFactory,
        Survey $survey
    ) {
        $this->permission = $permission;
        $this->responseFactory = $responseFactory;
        $this->survey = $survey;
    }

    /**
     * Run survey logic command
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $surveyId = (int)$request->getData('_id');

        if (!$this->permission->hasSurveyPermission($surveyId, 'surveycontent', 'read')) {
            return $this->responseFactory->makeErrorForbidden();
        }

        $survey = $this->survey->findByPk($surveyId);
        if (!$survey) {
            return $this->responseFactory->makeErrorNotFound(
                (new ResponseDataError(
                    self::ERROR_SURVEY_NOT_FOUND,
                    'Survey not found'
                ))->toArray()
            );
        }

        $gid = $request->getData('gid');
        $gid = $gid !== null && $gid !== '' ? (int)$gid : null;

        $qid = $request->getData('qid');
        $qid = $qid !== null && $qid !== '' ? (int)$qid : null;

        $language = $request->getData('lang');
        if ($language !== null && $language !== '') {
            $language = LSYii_Validators::languageCodeFilter($language);
        } else {
            // SetSurveyLanguage() treats an empty string the same as "no language given".
            $language = '';
        }

        $assessmentsParam = $request->getData('assessments');
        $assessments = $assessmentsParam === null
            ? null
            : in_array($assessmentsParam, ['Y', 'true', '1', 1, true], true);

        // Match the debug level the admin controller applies when no override is given.
        $debugLevel = ((Yii::app()->getConfig('debug') > 0) ? LEM_DEBUG_TIMING : 0)
            + LEM_DEBUG_VALIDATION_SUMMARY
            + LEM_DEBUG_VALIDATION_DETAIL
            + LEM_PRETTY_PRINT_ALL_SYNTAX;

        SetSurveyLanguage($surveyId, $language);
        killSurveySession($surveyId);
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        Yii::app()->setLanguage(Yii::app()->session['adminlang']);

        $result = LimeExpressionManager::ShowSurveyLogicFile(
            $surveyId,
            $gid,
            $qid,
            $debugLevel,
            $assessments
        );

        $errors = $result['errors'] ?? 0;

        return $this->responseFactory->makeSuccess([
            'surveyLogic' => [
                'html'   => $result['html'] ?? '',
                'errors' => is_array($errors) ? array_sum($errors) : (int)$errors,
            ],
        ]);
    }
}
