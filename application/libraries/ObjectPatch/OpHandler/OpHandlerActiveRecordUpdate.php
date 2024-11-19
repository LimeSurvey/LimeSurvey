<?php

namespace LimeSurvey\ObjectPatch\OpHandler;

use CModel;
use LimeSurvey\Api\Transformer\TransformerInterface;
use LimeSurvey\ObjectPatch\{
    OpHandler\OpHandlerInterface,
    OpHandler\OpHandlerException,
    Op\OpInterface,
    OpType\OpTypeUpdate
};

class OpHandlerActiveRecordUpdate implements OpHandlerInterface
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
            && $op->getType()->getId() == OpTypeUpdate::ID;
    }

    public function handle(OpInterface $op)
    {
        $record = is_array($op->getEntityId())
            ? $this->model->findByAttributes(
                $this->transformer->transform(
                    $op->getEntityId()
                )
            )
            : $this->model->findByPk(
                $op->getEntityId()
            );
        if (!$record) {
            throw new OpHandlerException(
                printf(
                    '%s with id "%s" not found',
                    $this->entity,
                    $op->getEntityId()
                )
            );
        }

        $props = $this->transformer
            ? $this->transformer->transform(
                $op->getProps()
            )
            : $op->getProps();
        if (is_array($props)) {
            foreach ($props as $prop => $v) {
                $record->{$prop} = $v;
            }
        } else {
            throw new OpHandlerException(
                printf(
                    'Invalid value for %s with id "%s"',
                    $this->entity,
                    print_r($op->getEntityId(), true)
                )
            );
        }

        $record->save();
    }

    public function validateOperation(OpInterface $op): array
    {
        // TODO: Implement validateOperation() method.
        return [];
    }
}
