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
class Update_706 extends DatabaseUpdateBase
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
     * Deduplicate existing non-empty duplicate emails by inserting a unique
     * sub-address tag into the local part so the result is still a valid
     * RFC 5322 address (e.g. user+migration5@example.com).
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
                $candidate = $this->buildUniqueEmail($dupEmail, $uid);
                $this->db->createCommand()
                    ->setText("UPDATE {{users}} SET email = :newEmail WHERE uid = :uid")
                    ->bindValue(':newEmail', $candidate)
                    ->bindValue(':uid', $uid)
                    ->execute();
            }
        }
    }

    /**
     * Build a valid, unique email by inserting a tag into the local part.
     *
     * @param string $email  Original duplicate email address.
     * @param int    $uid    User id used as the primary differentiator.
     * @return string A unique email address.
     */
    private function buildUniqueEmail(string $email, int $uid): string
    {
        $atPos = strrpos($email, '@');
        if ($atPos !== false) {
            $local  = substr($email, 0, $atPos);
            $domain = substr($email, $atPos); // includes '@'
        } else {
            // Malformed address – treat the whole value as local part.
            $local  = $email;
            $domain = '';
        }

        $candidate = $local . '+migration' . $uid . $domain;
        $suffix = 0;

        while ($this->emailExists($candidate)) {
            $suffix++;
            $candidate = $local . '+migration' . $uid . '_' . $suffix . $domain;
        }

        return $candidate;
    }

    /**
     * Check whether an email already exists in the users table.
     *
     * @param string $email
     * @return bool
     */
    private function emailExists(string $email): bool
    {
        $count = (int) $this->db->createCommand()
            ->setText("SELECT COUNT(*) FROM {{users}} WHERE email = :email")
            ->bindValue(':email', $email)
            ->queryScalar();
        return $count > 0;
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
