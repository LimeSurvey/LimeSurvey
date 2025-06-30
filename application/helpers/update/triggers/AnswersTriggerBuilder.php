<?php

namespace LimeSurvey\Helpers\Update\triggers;

class AnswersTriggerBuilder {
    public static function build(string $dbType, string $prefix, string $fieldName): array
    {
        switch ($dbType) {
            case 'mssql':
            case 'sqlsrv':
                return self::buildMssql($prefix, $fieldName);
            case 'pgsql':
                return self::buildPgsql($prefix, $fieldName);
            default:
                return self::buildMysql($prefix, $fieldName);
        }
    }
    public static function buildMssql(string $prefix, string $fieldName): array
    {
        return [
            <<<SQL
                CREATE TRIGGER answers_last_modified ON [{$prefix}answers]
                AFTER UPDATE AS BEGIN
                    UPDATE s SET s.[{$fieldName}] = GETDATE()
                    FROM [{$prefix}surveys] s
                    INNER JOIN {$prefix}questions q ON s.sid = q.sid
                    INNER JOIN inserted i ON q.qid = i.qid;
                END;
            SQL,
            <<<SQL
                CREATE TRIGGER answers_last_modified_update ON [{$prefix}answers]
                AFTER INSERT AS BEGIN
                    UPDATE s SET s.[{$fieldName}] = GETDATE()
                    FROM [{$prefix}surveys] s
                    INNER JOIN {$prefix}questions q ON s.sid = q.sid
                    INNER JOIN inserted i ON q.qid = i.qid;
                END;
            SQL,
            <<<SQL
                CREATE TRIGGER answers_last_modified_delete ON [{$prefix}answers]
                AFTER DELETE AS BEGIN
                    UPDATE s SET s.[{$fieldName}] = GETDATE()
                    FROM [{$prefix}surveys] s
                    INNER JOIN {$prefix}questions q ON s.sid = q.sid
                    INNER JOIN deleted i ON q.qid = i.qid;
                END;
            SQL
        ];
    }

    public static function buildPgsql(string $prefix, string $fieldName): array
    {
        return [
            <<<SQL
                CREATE OR REPLACE FUNCTION answers_last_modified()
                RETURNS TRIGGER AS $$ BEGIN
                    UPDATE {$prefix}surveys s SET {$fieldName} = NOW()
                    FROM {$prefix}questions q
                    WHERE q.qid = NEW.qid AND q.sid = s.sid;
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
                CREATE TRIGGER answers_last_modified AFTER UPDATE ON {$prefix}answers
                FOR EACH ROW EXECUTE FUNCTION answers_last_modified();
            SQL,
            <<<SQL
                CREATE OR REPLACE FUNCTION answers_last_modified_insert()
                RETURNS TRIGGER AS $$ BEGIN
                    UPDATE {$prefix}surveys s SET {$fieldName} = NOW()
                    FROM {$prefix}questions q
                    WHERE q.qid = NEW.qid AND q.sid = s.sid;
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
                CREATE TRIGGER answers_last_modified_insert AFTER INSERT ON {$prefix}answers
                FOR EACH ROW EXECUTE FUNCTION answers_last_modified_insert();
            SQL,
            <<<SQL
                CREATE OR REPLACE FUNCTION answers_last_modified_delete()
                RETURNS TRIGGER AS $$ BEGIN
                    UPDATE {$prefix}surveys s SET {$fieldName} = NOW()
                    FROM {$prefix}questions q
                    WHERE q.qid = OLD.qid AND q.sid = s.sid;
                    RETURN OLD;
                END;
                $$ LANGUAGE plpgsql;
                CREATE TRIGGER answers_last_modified_delete BEFORE DELETE ON {$prefix}answers
                FOR EACH ROW EXECUTE FUNCTION answers_last_modified_delete();
            SQL
        ];
    }

    public static function buildMysql(string $prefix, string $fieldName): array
    {
        return [
            <<<SQL
                CREATE TRIGGER `answers_last_modified` AFTER UPDATE ON {$prefix}answers
                FOR EACH ROW BEGIN
                    UPDATE {$prefix}surveys s JOIN {$prefix}questions q ON q.sid = s.sid
                    SET s.{$fieldName} = NOW() WHERE q.qid = NEW.qid;
                END
            SQL,
            <<<SQL
                CREATE TRIGGER `answers_last_modified_insert` AFTER INSERT ON {$prefix}answers
                FOR EACH ROW BEGIN
                    UPDATE {$prefix}surveys s JOIN {$prefix}questions q ON q.sid = s.sid
                    SET s.{$fieldName} = NOW() WHERE q.qid = NEW.qid;
                END
            SQL,
            <<<SQL
                CREATE TRIGGER `answers_last_modified_delete` BEFORE DELETE ON {$prefix}answers
                FOR EACH ROW BEGIN
                    UPDATE {$prefix}surveys s JOIN {$prefix}questions q ON q.sid = s.sid
                    SET s.{$fieldName} = NOW() WHERE q.qid = OLD.qid;
                END
            SQL
        ];
    }
}
