<?php

namespace LimeSurvey\Api\Command\V1;

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
 * Create a label set (POST v1/label-sets) or overwrite an existing one (PUT v1/label-sets/$id).
 *
 * Body: { name: string (create only), languages: string[], labels: [{code, assessmentValue, l10ns: {lang: title}}] }
 */
class LabelSetSave implements CommandInterface
{
    public const ERROR_LABEL_SET_NOT_FOUND = 'LABEL_SET_NOT_FOUND';
    public const ERROR_LABEL_SET_INVALID = 'LABEL_SET_INVALID';
    public const ERROR_LABEL_SET_NOT_SAVED = 'LABEL_SET_NOT_SAVED';

    protected ResponseFactory $responseFactory;
    protected LabelSetService $labelSetService;

    public function __construct(
        ResponseFactory $responseFactory,
        LabelSetService $labelSetService
    ) {
        $this->responseFactory = $responseFactory;
        $this->labelSetService = $labelSetService;
    }

    public function run(Request $request): Response
    {
        $lid = (int)$request->getData('_id');
        $languages = $request->getData('languages') ?? [];
        $labels = $request->getData('labels') ?? [];
        if (!is_array($languages) || !is_array($labels)) {
            return $this->responseFactory->makeErrorBadRequest(
                (new ResponseDataError(self::ERROR_LABEL_SET_INVALID, 'Invalid request'))->toArray()
            );
        }

        try {
            $labelSet = $lid
                ? $this->labelSetService->overwrite($lid, $languages, $labels)
                : $this->labelSetService->create((string)$request->getData('name'), $languages, $labels);
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
            return $this->responseFactory->makeErrorBadRequest(
                (new ResponseDataError(self::ERROR_LABEL_SET_INVALID, $e->getMessage()))->toArray()
            );
        } catch (PersistErrorException $e) {
            return $this->responseFactory->makeErrorBadRequest(
                (new ResponseDataError(self::ERROR_LABEL_SET_NOT_SAVED, $e->getMessage()))->toArray()
            );
        }
    }
}
