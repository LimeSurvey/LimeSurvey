<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\ResponseFactory;
use LSYii_Application;
use Permission;
use Survey;

class SurveyQuestionsFieldname implements CommandInterface
{
    protected Permission $permission;

    protected LSYii_Application $app;

    protected ResponseFactory $responseFactory;


    /**
     * Constructor
     * @param LSYii_Application $app
     * @param Permission $permission
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        LSYii_Application $app,
        Permission $permission,
        ResponseFactory $responseFactory
    ) {
        $this->app = $app;
        $this->permission = $permission;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Run survey questions fieldname command.
     *
     * Supports GET requests with the survey ID (`sid`) at the end of the endpoint,
     * looking like: rest/v1/survey-questions-fieldname/571271
     *
     * This endpoint returns a structured mapping of fieldnames for all relevant
     * questions in the survey.
     *
     * Filtering rules:
     * - Only fields whose code starts with "Q" are included.
     * - Fields containing "comment" in their fieldname are excluded.
     * - Fields containing "filecount" in their fieldname are excluded.
     *
     * Response structure:
     * The response is a JSON object keyed by question ID, where each value is an
     * array of field definitions containing:
     * - `fieldname` (string) — The internal field name.
     * - `sid` (string|int) — Survey ID.
     * - `gid` (string|int) — Group ID.
     * - `qid` (string|int) — Question ID.
     * - `sqid` (string|int|null) — Subquestion ID (nullable).
     * - `aid` (string) — Answer ID.
     * - `title` (string) — Question title/code.
     * - `scale_id` (string|int|null) — Scale ID (nullable).
     *
     * Example response:
     * {
     *     "135": [
     *         {
     *             "fieldname": "Q135",
     *             "sid": "571271",
     *             "gid": "1",
     *             "qid": "135",
     *             "sqid": null,
     *             "aid": null,
     *             "title": "Q033",
     *             "scale_id": null
     *         }
     *     ]
     * }
     *
     * @param Request $request Request object containing survey ID.
     * @return Response JSON response with the structured fieldname mapping.
     */
    public function run(Request $request): Response
    {
        $surveyId = (string) $request->getData('_id');


        if (!$this->permission->hasSurveyPermission($surveyId, 'fieldnames')) {
            return $this->responseFactory->makeErrorUnauthorised();
        }

        $survey = Survey::model()->findByPk($surveyId);

        if ($survey === null) {
            return $this->responseFactory->makeErrorNotFound();
        }

        $aDuplicateQIDs = [];
        $surveyLanguage = $survey->language;
        $fullFieldMap = createFieldMap($survey, 'full', true, false, $surveyLanguage, $aDuplicateQIDs, [], true);

        $questionsFieldMap = [];

        foreach ($fullFieldMap as $fieldCode => $fieldMeta) {
            if (strpos($fieldCode, 'Q') !== 0) {
                continue;
            }

            if (strpos($fieldMeta['fieldname'], 'comment') !== false) {
                continue;
            }

            if (strpos($fieldMeta['fieldname'], 'filecount') !== false) {
                continue;
            }

            $questionsFieldMap[$fieldMeta['qid']][] = $fieldMeta;
        }

        $structuredFieldnames = [];

        foreach ($questionsFieldMap as $questionId => $questionFields) {
            foreach ($questionFields as $field) {
                $structuredFieldnames[$questionId][] = [
                    'fieldname' => $field['fieldname'],
                    'sid'       => $field['sid'],
                    'gid'       => $field['gid'],
                    'qid'       => $field['qid'],
                    'sqid'      => $field['sqid'] ?? null,
                    'aid'       => $field['aid'] ?? null,
                    'title'     => $field['title'],
                    'scale_id'  => $field['scale_id'] ?? null,
                ];
            }
        }

        return $this->responseFactory->makeSuccess($structuredFieldnames);
    }
}
