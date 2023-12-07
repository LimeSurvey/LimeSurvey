<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\Response;

use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;

class TempIdMapping
{
    private int $operationsApplied = 0;
    private array $itemGroupNames = [
        'questionGroupsMap',
        'questionsMap',
        'subquestionsMap',
        'answersMap'
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
        $this->mapItems[$itemGroupName][] = $tempIdMapItem;
    }

    /**
     * Returns the whole response array including all the added tempId mappings
     * and the number of applied operations.
     * @return array
     */
    public function getMappingResponseObject(): array
    {
        return array_merge(
            [
                'operationsApplied' => $this->operationsApplied,
            ],
            $this->mapItems
        );
    }


    public function incrementOperationsApplied(): void
    {
        $this->operationsApplied++;
    }
}
