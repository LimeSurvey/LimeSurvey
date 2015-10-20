<?php


namespace ls\interfaces;

/**
 * Interface DependentRecordInterface
 * This interface exposes methods for removing an ActiveRecords' dependent relations inside a single transaction.
 * @package ls\interfaces
 */
interface DependentRecordInterface
{

    public function deleteDependent(\CDbConnection $db, $useTransaction = true);

    public function dependentRelations();
}