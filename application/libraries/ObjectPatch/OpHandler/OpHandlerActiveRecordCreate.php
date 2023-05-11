<?php

namespace LimeSurvey\ObjectPatch\OpHandler;

use CModel;
use LimeSurvey\Api\Transformer\TransformerInterface;
use LimeSurvey\ObjectPatch\{
    OpHandler\OpHandlerInterface,
    Op\OpInterface,
    OpType\OpTypeCreate
};

class OpHandlerActiveRecordCreate implements OpHandlerInterface
{
    protected $entity = null;
    protected $model = null;
    protected $transformer = null;

    public function __construct($entity, CModel $model, TransformerInterface $transformer = null)
    {
        $this->entity = $entity;
        $this->model = $model;
        $this->transformer = $transformer;
    }

    public function canHandle(OpInterface $op): bool
    {
        return $op->getEntityType() == $this->entity
            && $op->getType()->getId() == OpTypeCreate::ID;
    }

    public function handle(OpInterface $op)
    {
        $props = $this->transformer
            ? $this->transformer->transform(
                $op->getProps()
            )
            : $op->getProps();
        $class = get_class($this->model);
        $record = new $class;
        $record->setAttributes($props, true);
        //var_dump($props); exit;
        if (!$record->save()) {
            throw new OpHandlerException(
                sprintf(
                    'Failed saving %s with props %s',
                    $this->entity,
                    json_encode($op->getProps())
                )
            );
        }
    }
}
