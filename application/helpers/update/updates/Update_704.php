<?php

namespace LimeSurvey\Helpers\Update;

/**
 * Add a unique index on the email column of the users table.
 * This enforces email uniqueness at the database level to prevent
 * race conditions that the model-level validation cannot catch.
 *
 * - Empty emails are converted to NULL (multiple NULLs are allowed
 *   in unique indexes on MySQL and PostgreSQL).
 * - For MSSQL, a filtered index is used since it does not allow
 *   multiple NULLs in a standard unique index.
 * - Existing duplicate non-empty emails are deduplicated by appending
 *   the user's uid before the index is created.
 */
class Update_704 extends DatabaseUpdateBase
{
    public function up()
    {
        $this->convertEmptyEmailsToNull();
        $this->deduplicateEmails();
        $this->dropOldEmailIndex();
        $this->createUniqueEmailIndex();
    }

    /**
     * Convert empty email strings to NULL.
     */
    private function convertEmptyEmailsToNull()
    {
        $this->db->createCommand()
            ->setText("UPDATE {{users}} SET email = NULL WHERE email = ''")
            ->execute();
    }

    /**
     * Deduplicate existing non-empty duplicate emails by appending '+uid'
     * to all but the lowest uid for each duplicate.
     */
    private function deduplicateEmails()
    {
        $duplicates = $this->db->createCommand()
            ->setText("SELECT email FROM {{users}} WHERE email IS NOT NULL GROUP BY email HAVING COUNT(*) > 1")
            ->queryColumn();

        foreach ($duplicates as $dupEmail) {
            $rows = $this->db->createCommand()
                ->setText("SELECT uid FROM {{users}} WHERE email = :email ORDER BY uid ASC")
                ->bindValue(':email', $dupEmail)
                ->queryColumn();
            // Keep the first one, rename the rest.
            array_shift($rows);
            foreach ($rows as $uid) {
                $newEmail = $dupEmail . '+' . $uid;
                $this->db->createCommand()
                    ->setText("UPDATE {{users}} SET email = :newEmail WHERE uid = :uid")
                    ->bindValue(':newEmail', $newEmail)
                    ->bindValue(':uid', $uid)
                    ->execute();
            }
        }
    }

    /**
     * Drop the existing non-unique index on email.
     */
    private function dropOldEmailIndex()
    {
        try {
            $this->db->createCommand()->dropIndex('{{idx2_users}}', '{{users}}');
        } catch (\Exception $e) {
            // Index may not exist in all installations.
        }
    }

    /**
     * Create the unique index on email (database-specific for MSSQL).
     */
    private function createUniqueEmailIndex()
    {
        switch ($this->db->driverName) {
            case 'sqlsrv':
            case 'dblib':
            case 'mssql':
                // MSSQL does not allow multiple NULLs in a unique index, so use a filtered index that only covers non-NULL emails.
                $tableName = $this->db->tablePrefix
                    ? str_replace('{{', $this->db->tablePrefix, str_replace('}}', '', '{{users}}'))
                    : 'users';
                $this->db->createCommand()
                    ->setText(
                        "CREATE UNIQUE NONCLUSTERED INDEX [{$tableName}_email_unique] "
                        . "ON [{$tableName}] ([email]) WHERE [email] IS NOT NULL"
                    )
                    ->execute();
                break;
            default:
                // MySQL and PostgreSQL allow multiple NULLs in unique indexes.
                $this->db->createCommand()->createIndex(
                    '{{idx2_users}}',
                    '{{users}}',
                    'email',
                    true
                );
                break;
        }
    }
}
