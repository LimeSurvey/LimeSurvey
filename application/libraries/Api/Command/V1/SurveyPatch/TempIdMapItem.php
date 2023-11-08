<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

class TempIdMapItem
{
    /**
     * @var ?string|int the temporary id received from the client
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

    public function __construct($tempId, $id, $field = 'id')
    {
        $this->tempId = $tempId;
        $this->id = $id;
        $this->field = $field;
    }
}
