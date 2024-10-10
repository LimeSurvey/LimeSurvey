<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\Response;

class TempIdMapItem
{
    /**
     * @var string|int|null the temporary id received from the client
     */
    public $tempId;
    /**
     * @var int|null the actual id
     */
    public ?int $id = null;
    /**
     * @var string the field name of the actual id
     */
    public string $field = 'id';

    /**
     * @param string|int|null $tempId
     * @param int|null $id
     * @param string $field
     */
    public function __construct(
        $tempId,
        ?int $id,
        string $field = 'id'
    ) {
        $this->tempId = $tempId;
        $this->id = $id;
        $this->field = $field;
    }
}
