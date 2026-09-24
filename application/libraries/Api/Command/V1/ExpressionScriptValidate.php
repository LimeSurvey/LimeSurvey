<?php

namespace LimeSurvey\Api\Command\V1;

use LimeExpressionManager;
use Permission;
use Question;
use Survey;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory,
    ResponseData\ResponseDataError
};

/**
 * Validate an ExpressionScript expression with LimeSurvey's Expression Manager.
 */
class ExpressionScriptValidate implements CommandInterface
{
    public const ERROR_SURVEY_NOT_FOUND = 'SURVEY_NOT_FOUND';
    public const ERROR_QUESTION_NOT_FOUND = 'QUESTION_NOT_FOUND';

    protected Permission $permission;
    protected ResponseFactory $responseFactory;
    protected Survey $survey;
    protected Question $question;

    public function __construct(
        Permission $permission,
        ResponseFactory $responseFactory,
        Survey $survey,
        Question $question
    ) {
        $this->permission = $permission;
        $this->responseFactory = $responseFactory;
        $this->survey = $survey;
        $this->question = $question;
    }

    public function run(Request $request): Response
    {
        $surveyId = (int) $request->getData('_id');

        if (!$this->permission->hasSurveyPermission($surveyId, 'surveycontent', 'read')) {
            return $this->responseFactory->makeErrorForbidden();
        }

        $survey = $this->survey->findByPk($surveyId);
        if (!$survey) {
            return $this->responseFactory->makeErrorNotFound(
                (new ResponseDataError(self::ERROR_SURVEY_NOT_FOUND, 'Survey not found'))->toArray()
            );
        }

        $questionId = (int) $request->getData('questionId');
        $question = $this->question->findByPk($questionId);
        if (!$question || $question->sid !== $surveyId) {
            return $this->responseFactory->makeErrorNotFound(
                (new ResponseDataError(self::ERROR_QUESTION_NOT_FOUND, 'Question not found'))->toArray()
            );
        }

        $expression = (string) ($request->getData('expression') ?? '');
        if (trim($expression) === '') {
            return $this->responseFactory->makeSuccess([
                'valid' => true,
                'diagnostics' => [],
            ]);
        }

        $diagnostics = $this->validate(
            $expression,
            $surveyId,
            $questionId,
            $survey->language
        );
        $hasErrors = array_filter(
            $diagnostics,
            static fn (array $diagnostic): bool => $diagnostic['severity'] === 'error'
        );

        return $this->responseFactory->makeSuccess([
            'valid' => count($hasErrors) === 0,
            'diagnostics' => $diagnostics,
        ]);
    }

    /**
     * @return array<int, array{from: int, to: int, severity: string, message: string}>
     */
    private function validate(
        string $expression,
        int $surveyId,
        int $questionId,
        string $language
    ): array {
        SetSurveyLanguage($surveyId, $language);
        LimeExpressionManager::StartSurvey(
            $surveyId,
            'survey',
            [
                'previewmode' => 'logic',
                'hyperlinkSyntaxHighlighting' => false,
                'startlanguage' => $language,
            ],
            true
        );

        [$validationExpression, $byteOffset] = $this->unwrapExpression($expression);
        $validationResult = LimeExpressionManager::validateExpression($validationExpression, $questionId);
        $diagnostics = [];

        foreach ($validationResult['errors'] as $error) {
            $diagnostics[] = $this->makeDiagnostic(
                $expression,
                $byteOffset,
                $error[1] ?? null,
                (string) ($error[0] ?? 'Invalid expression'),
                'error'
            );
        }

        foreach ($validationResult['warnings'] as $warning) {
            $diagnostics[] = $this->makeDiagnostic(
                $expression,
                $byteOffset,
                $warning->getToken(),
                (string) $warning->getMessage(),
                'warning'
            );
        }

        return $diagnostics;
    }

    /**
     * Expression Manager token offsets are UTF-8 byte offsets; CodeMirror uses UTF-16 offsets.
     *
     * @param array|null $token Expression Manager token [value, byte offset, type]
     * @return array{from: int, to: int, severity: string, message: string}
     */
    private function makeDiagnostic(
        string $expression,
        int $byteOffset,
        ?array $token,
        string $message,
        string $severity
    ): array {
        if ($token === null) {
            return [
                'from' => 0,
                'to' => $this->utf16Length($expression),
                'severity' => $severity,
                'message' => $message,
            ];
        }

        $tokenByteOffset = $byteOffset + (int) ($token[1] ?? 0);
        $tokenValue = (string) ($token[0] ?? '');
        $from = $this->utf16Length(substr($expression, 0, $tokenByteOffset));
        $tokenLength = ($token[2] ?? null) === 'OTHER'
            ? 1
            : $this->utf16Length($tokenValue);

        return [
            'from' => $from,
            'to' => $from + max(1, $tokenLength),
            'severity' => $severity,
            'message' => $message,
        ];
    }

    /**
     * ExpressionScript fields may contain one pair of outer delimiters.
     * Return the expression to validate and its byte offset in the original value.
     *
     * @return array{string, int}
     */
    private function unwrapExpression(string $expression): array
    {
        if (preg_match('/^(\s*)\{([\s\S]*)\}(\s*)$/', $expression, $matches) === 1) {
            return [$matches[2], strlen($matches[1]) + 1];
        }

        return [$expression, 0];
    }

    private function utf16Length(string $value): int
    {
        if (function_exists('mb_convert_encoding')) {
            return (int) (strlen(mb_convert_encoding($value, 'UTF-16LE', 'UTF-8')) / 2);
        }

        return strlen($value);
    }
}
