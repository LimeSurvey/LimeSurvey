<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use Answer;
use LimeSurvey\ObjectPatch\{ObjectPatchException,
    Op\OpStandard,
    OpHandler\OpHandlerActiveRecordUpdate,
    Patcher
};
use LimeSurvey\Api\Command\V1\Transformer\Input\{
    TransformerInputAnswer
};
use DI\FactoryInterface;
use Psr\Container\ContainerInterface;

class PatcherSurvey extends Patcher
{
    protected TempIdMapping $tempIdMapping;

    /**
     * Constructor
     *
     * @param FactoryInterface $diFactory
     * @param ContainerInterface $diContainer
     * @param TempIdMapping $tempIdMapping
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function __construct(
        FactoryInterface $diFactory,
        ContainerInterface $diContainer,
        TempIdMapping $tempIdMapping
    ) {
        $this->tempIdMapping = $tempIdMapping;
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerSurveyUpdate::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerLanguageSettingsUpdate::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionGroup::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionGroupL10n::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionDelete::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionCreate::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionUpdate::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionL10nUpdate::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerAnswer::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionAttributeUpdate::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionGroupReorder::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerSubquestionDelete::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerAnswerDelete::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerSubQuestion::class
            )
        );
    }

    /**
     * Apply patch
     *
     * @throws ObjectPatchException
     */
    public function applyPatch($patch, $context = []): array
    {
        if (is_array($patch) && !empty($patch)) {
            foreach ($patch as $patchOpData) {
                $op = OpStandard::factory(
                    $patchOpData['entity'] ?? null,
                    $patchOpData['op'] ?? null,
                    $patchOpData['id'] ?? null,
                    $patchOpData['props'] ?? null,
                    $context ?? null
                );
                $this->tempIdMapping->incrementOperationsApplied();
                $handleResponse = $this->handleOp($op);
                foreach ($handleResponse as $groupName => $mappingItem) {
                    $this->addTempIdMapItem($mappingItem, $groupName);
                }
            }
        }
        return (array)$this->tempIdMapping->getMappingResponseObject();
    }

    /**
     * Recursive function to extract TempIdMapItems from the $mappingItem
     * @param $mappingItem
     * @param $groupName
     * @return void
     * @throws \LimeSurvey\ObjectPatch\OpHandler\OpHandlerException
     */
    private function addTempIdMapItem($mappingItem, $groupName)
    {
        if ($mappingItem instanceof TempIdMapItem) {
            $this->tempIdMapping->addTempIdMapItem(
                $mappingItem,
                $groupName
            );
        } elseif (is_array($mappingItem)) {
            foreach ($mappingItem as $item) {
                $this->addTempIdMapItem($item, $groupName);
            }
        }
    }
}
