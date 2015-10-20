<?php


namespace ls\traits;

use ls\interfaces\DependentRecordInterface;

/**
 * This trait implements the deleteDependent from the DependentRecordInterface.
 * @package ls\traits
 */
trait DependentRecordTrait
{

    abstract public function delete();
    abstract public function dependentRelations();

    public function deleteDependent(\CDbConnection $db, $useTransaction = true)
    {
        if ($useTransaction) {
            $transaction = $db->beginTransaction();
        }

        try {
            $result = 0;
            foreach ($this->dependentRelations() as $relation) {
                /** @var CActiveRecord $record */
                foreach ($this->$relation as $record) {
                    if ($record instanceof DependentRecordInterface) {
                        $result += $record->deleteDependent($db, false);
                    } else {
                        $record->delete();
                        $result++;
                    }
                }
            }
            $this->delete();
            $result++;
            if ($useTransaction) {
                $transaction->commit();
            }
        } catch (\Exception $e) {
            if ($useTransaction) {
                $transaction->rollback();
            }
            throw $e;
        }


        return $result;

    }
}