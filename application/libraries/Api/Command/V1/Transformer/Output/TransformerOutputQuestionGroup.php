<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Output;

use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputQuestionGroup extends TransformerOutputActiveRecord
{
    /**
     * Initializes the transformer and defines the output field mapping for question groups.
     *
     * Maps source fields to output keys and types:
     * - `gid` -> `gid` (int)
     * - `sid` -> `sid` (int)
     * - `group_order` -> `sortOrder` (int)
     * - `randomization_group` -> `randomizationGroup`
     * - `grelevance` -> `gRelevance`
     */
    public function __construct()
    {
        $this->setDataMap([
            'gid' => ['type' => 'int'],
            'sid' => ['type' => 'int'],
            'group_order' => ['key' => 'sortOrder', 'type' => 'int'],
            'randomization_group' => 'randomizationGroup',
            'grelevance' => 'gRelevance',
        ]);
    }

    /**
     * Transform a collection of question-group records and return them sorted by `sortOrder`.
     *
     * @param array $collection Input collection of records to transform.
     * @param ?array $options Optional transformation options.
     * @return array The transformed collection sorted in ascending order by the `sortOrder` field.
     */
    public function transformAll($collection, $options = null)
    {
        $collection = parent::transformAll($collection, $options);

        usort(
            $collection,
            function ($a, $b) {
                return (int)(
                    (int)$a['sortOrder'] > (int)$b['sortOrder']
                );
            }
        );

        return $collection;
    }
}
