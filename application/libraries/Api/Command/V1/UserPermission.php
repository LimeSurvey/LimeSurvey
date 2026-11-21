<?php

namespace LimeSurvey\Api\Command\V1;

use Permission;
use LimeSurvey\Api\Command\{CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory};
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputUserPermissions;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use LimeSurvey\Models\Services\LabelSetService;

class UserPermission implements CommandInterface
{
    use AuthPermissionTrait;

    protected Permission $permission;
    protected TransformerOutputUserPermissions $transformOutputUserPermissions;
    protected ResponseFactory $responseFactory;
    protected LabelSetService $labelSetService;

    /**
     * Constructor
     *
     * @param Permission $permission
     * @param TransformerOutputUserPermissions $transformOutputUserPermissions
     * @param ResponseFactory $responseFactory
     * @param LabelSetService $labelSetService
     */
    public function __construct(
        Permission $permission,
        TransformerOutputUserPermissions $transformOutputUserPermissions,
        ResponseFactory $responseFactory,
        LabelSetService $labelSetService
    ) {
        $this->permission = $permission;
        $this->transformOutputUserPermissions = $transformOutputUserPermissions;
        $this->responseFactory = $responseFactory;
        $this->labelSetService = $labelSetService;
    }

    /**
     * Run user permission command
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $userId = App()->user->getId();

        $permissions = $this->permission->getPermissions($userId);
        $responseData = $this->transformOutputUserPermissions->transform($permissions);

        $surveyId = (int) $request->getData('surveyId');
        if ($surveyId > 0) {
            $responseData['effective'] = array_merge(
                $this->getEffectiveSurveyPermissions($surveyId),
                $this->getEffectiveLabelSetPermissions()
            );
        }

        return $this->responseFactory
            ->makeSuccess(['permissions' => $responseData]);
    }

    /**
     * Whether the user may save the question editor's answers as a new label set,
     * or overwrite at least one existing label set.
     *
     * Update rights can also be granted per label set (or by a plugin), and the global
     * right is no use without a set to overwrite, so the sets are checked like the overwrite list.
     *
     * @return array<string, bool>
     */
    private function getEffectiveLabelSetPermissions(): array
    {
        return [
            'labelSetsCreate' => $this->permission->hasGlobalPermission('labelsets', 'create'),
            'labelSetsUpdate' => $this->labelSetService->hasUpdatableLabelSet(),
        ];
    }

    /**
     * Resolved flags incl. owner, global and survey group inheritance.
     *
     * @param int $surveyId
     * @return array<string, bool>
     */
    private function getEffectiveSurveyPermissions(int $surveyId): array
    {
        $can = fn(string $permission, string $crud): bool =>
            $this->permission->hasSurveyPermission($surveyId, $permission, $crud);

        return [
            'surveyRead' => $can('survey', 'read'),
            'surveyUpdate' => $can('survey', 'update'),
            'responsesRead' => $can('responses', 'read')
                || $can('responses', 'update')
                || $can('statistics', 'read'),
            'responsesUpdate' => $can('responses', 'update'),
        ];
    }
}
