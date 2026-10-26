<?php

/**
 * Fixes CMssqlPdoAdapter (used by Yii for the 'mssql' and 'dblib' PDO drivers) so that
 * PDO::inTransaction() reflects the transaction actually opened on the server.
 *
 * CMssqlPdoAdapter issues raw "BEGIN TRANSACTION" / "COMMIT TRANSACTION" /
 * "ROLLBACK TRANSACTION" statements via exec() instead of calling the parent PDO
 * transaction methods, because the mssql/dblib PDO drivers don't support PDO's native
 * transaction API. Since parent::beginTransaction() is never called, PDO's own
 * transaction bookkeeping is never updated and the inherited PDO::inTransaction()
 * permanently returns false.
 *
 * CDbTransaction::commit() and ::rollback() only forward to the PDO instance when
 * inTransaction() is true, so with the unpatched adapter the real COMMIT/ROLLBACK
 * TRANSACTION is silently skipped: the transaction stays open on the server and is
 * rolled back by SQL Server once the connection closes, discarding everything that was
 * written inside it without raising any PHP exception (see bug #19016 - a dblib
 * install "succeeds" while creating zero tables).
 */
class LimeSurveyMssqlPdoAdapter extends CMssqlPdoAdapter
{
    /** @var bool */
    private $lsInTransaction = false;

    /**
     * @return bool
     */
    #[ReturnTypeWillChange]
    public function beginTransaction()
    {
        $result = parent::beginTransaction();
        $this->lsInTransaction = true;
        return $result;
    }

    /**
     * @return bool
     */
    #[ReturnTypeWillChange]
    public function commit()
    {
        $result = parent::commit();
        $this->lsInTransaction = false;
        return $result;
    }

    /**
     * @return bool
     */
    #[ReturnTypeWillChange]
    public function rollBack()
    {
        $result = parent::rollBack();
        $this->lsInTransaction = false;
        return $result;
    }

    /**
     * @return bool
     */
    #[ReturnTypeWillChange]
    public function inTransaction()
    {
        return $this->lsInTransaction;
    }
}
