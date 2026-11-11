<?php

namespace LimeSurvey\Api\Command\V1;

use LabelSet;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory,
    ResponseData\ResponseDataError
};
use LimeSurvey\Models\Services\Exception\{
    BadRequestException,
    NotFoundException,
    PermissionDeniedException,
    PersistErrorException
};
use LimeSurvey\Models\Services\LabelSetService;

/**
 * Shared request checks and error handling of LabelSetCreate and LabelSetOverwrite.
 *
 * Body: { name: string (create only), languages: string[], labels: [{code, assessmentValue, l10ns: {lang: title}}] }
 */
abstract class LabelSetSave implements CommandInterface
{
    public const ERROR_LABEL_SET_NOT_FOUND = 'LABEL_SET_NOT_FOUND';
    public const ERROR_LABEL_SET_INVALID = 'LABEL_SET_INVALID';
    public const ERROR_LABEL_SET_NOT_SAVED = 'LABEL_SET_NOT_SAVED';

    protected ResponseFactory $responseFactory;
    protected LabelSetService $labelSetService;

    /**
     * Create the command with its dependencies.
     *
     * @param ResponseFactory $responseFactory Factory for the API responses
     * @param LabelSetService $labelSetService Service that saves the label set
     */
    public function __construct(
        ResponseFactory $responseFactory,
        LabelSetService $labelSetService
    ) {
        $this->responseFactory = $responseFactory;
        $this->labelSetService = $labelSetService;
    }

    /**
     * Create or overwrite the label set.
     *
     * @param Request $request The API request
     * @param string[] $languages Languages to save
     * @param array $labels Labels to save
     * @return LabelSet The saved label set
     * @throws \LimeSurvey\Models\Services\Exception When the label set can't be saved
     */
    abstract protected function save(Request $request, array $languages, array $labels): LabelSet;

    /**
     * Check the request, save the label set and return it with its labels.
     *
     * @param Request $request The API request
     * @return Response The saved label set, or an error response
     */
    public function run(Request $request): Response
    {
        $content = $request->getData('_content');
        // The REST layer merges the body over the route params, so a body "_id"
        // would replace the label set id of the URL.
        if (is_array($content) && array_key_exists('_id', $content)) {
            return $this->makeInvalidRequest('The label set id must not be sent in the request body');
        }

        $languages = $request->getData('languages') ?? [];
        $labels = $request->getData('labels') ?? [];
        if (!is_array($languages) || !is_array($labels)) {
            return $this->makeInvalidRequest('Invalid request');
        }

        try {
            $labelSet = $this->save($request, $languages, $labels);
            return $this->responseFactory->makeSuccess([
                'labelSet' => $this->labelSetService->getLabelSet((int)$labelSet->lid),
            ]);
        } catch (PermissionDeniedException $e) {
            return $this->responseFactory->makeErrorForbidden();
        } catch (NotFoundException $e) {
            return $this->responseFactory->makeErrorNotFound(
                (new ResponseDataError(self::ERROR_LABEL_SET_NOT_FOUND, $e->getMessage()))->toArray()
            );
        } catch (BadRequestException $e) {
            return $this->makeInvalidRequest($e->getMessage());
        } catch (PersistErrorException $e) {
            return $this->responseFactory->makeErrorBadRequest(
                (new ResponseDataError(self::ERROR_LABEL_SET_NOT_SAVED, $e->getMessage()))->toArray()
            );
        }
    }

    /**
     * Make a "bad request" response for invalid input.
     *
     * @param string $message What is wrong with the request
     * @return Response The error response
     */
    protected function makeInvalidRequest(string $message): Response
    {
        return $this->responseFactory->makeErrorBadRequest(
            (new ResponseDataError(self::ERROR_LABEL_SET_INVALID, $message))->toArray()
        );
    }
}
