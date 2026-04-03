<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\Response;

use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;

/**
 * This class is responsible for handling and returning tempId <-> real Id
 * mapping of newly created entities
 * as part of the response of PatcherSurvey.
 */
class TempIdMapping
{
    private array $itemGroupNames = [
        'questionGroupsMap',
        'questionsMap',
        'subquestionsMap',
        'answersMap',
        'conditionsMap'
    ];
    private array $mapItems = [];

    /**
     * Adds tempIdMapItems to the mapItems array organized by itemGroupNames.
     * Throws an exception if the passed itemGroupName is not known.
     * @param TempIdMapItem $tempIdMapItem
     * @param string $itemGroupName
     * @return void
     * @throws OpHandlerException
     */
    public function addTempIdMapItem(
        TempIdMapItem $tempIdMapItem,
        string $itemGroupName
    ): void {
        if (!in_array($itemGroupName, $this->itemGroupNames)) {
            throw new OpHandlerException(
                sprintf(
                    'Invalid map item group name "%s"',
                    $itemGroupName
                )
            );
        }
        $this->mapItems['tempIdMapping'][$itemGroupName][] = $tempIdMapItem;
    }

    /**
     * Recursive function to extract TempIdMapItems from the $mappingItem
     * @param TempIdMapItem|array $mappingItem
     * @param string $groupName
     * @return void
     * @throws \LimeSurvey\ObjectPatch\OpHandler\OpHandlerException
     */
    public function addTempIdMapItems($mappingItem, string $groupName)
    {
        if ($mappingItem instanceof TempIdMapItem) {
            $this->addTempIdMapItem(
                $mappingItem,
                $groupName
            );
        } else {
            foreach ($mappingItem as $item) {
                $this->addTempIdMapItems($item, $groupName);
            }
        }
    }

    /**
     * @return array
     */
    public function getMappingResponseObject(): array
    {
        return $this->mapItems;
    }
}
