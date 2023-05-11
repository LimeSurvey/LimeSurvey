<?php

namespace LimeSurvey\ObjectPatch\OpHandler;

use CModel;
use LimeSurvey\Api\Transformer\TransformerInterface;
use LimeSurvey\ObjectPatch\{
    OpHandler\OpHandlerInterface,
    OpHandler\OpHandlerException,
    Op\OpInterface,
    OpType\OpTypeDelete
};

class OpHandlerActiveRecordDelete implements OpHandlerInterface
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
            && $op->getType()->getId() == OpTypeDelete::ID;
    }

    private function getEntityId(OpInterface $op)
    {
        return is_array($op->getEntityId()) && $this->transformer
            ? $this->transformer->transform($op->getEntityId())
            : $op->getEntityId();
    }

    public function handle(OpInterface $op)
    {
        $record = is_array($op->getEntityId())
            ? $this->model->findByAttributes(
                $this->getEntityId($op)
            )
            : $this->model->findByPk(
                $op->getEntityId()
            );

        if (!$record) {
            throw new OpHandlerException(
                sprintf(
                    '%s with id "%s" not found',
                    $this->entity,
                    json_encode($op->getEntityId())
                )
            );
        }

        $record->delete();
    }
}
