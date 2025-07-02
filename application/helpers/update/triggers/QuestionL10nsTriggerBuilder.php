<?php

namespace LimeSurvey\Helpers\Update\triggers;

class QuestionL10nsTriggerBuilder {
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
                CREATE TRIGGER question_l10ns_last_modified
                ON [{$prefix}question_l10ns]
                AFTER UPDATE AS
                BEGIN
                    UPDATE s SET s.[{$fieldName}] = GETDATE()
                    FROM [{$prefix}surveys] s
                    JOIN [{$prefix}questions] q ON s.sid = q.sid
                    JOIN inserted i ON q.qid = i.qid;
                END;
             SQL,
            <<<SQL
                CREATE TRIGGER question_l10ns_last_modified_insert
                ON [{$prefix}question_l10ns]
                AFTER INSERT AS
                BEGIN
                    UPDATE s SET s.[{$fieldName}] = GETDATE()
                    FROM [{$prefix}surveys] s
                    JOIN [{$prefix}questions] q ON s.sid = q.sid
                    JOIN inserted i ON q.qid = i.qid;
                END;
             SQL,
            <<<SQL
                CREATE TRIGGER question_l10ns_last_modified_delete
                ON [{$prefix}question_l10ns]
                AFTER DELETE AS
                BEGIN
                    UPDATE s SET s.[{$fieldName}] = GETDATE()
                    FROM [{$prefix}surveys] s
                    JOIN [{$prefix}questions] q ON s.sid = q.sid
                    JOIN deleted i ON q.qid = i.qid;
                END;
            SQL
        ];
    }

    public static function buildPgsql(string $prefix, string $fieldName): array
    {
        return [
            <<<SQL
                CREATE OR REPLACE FUNCTION question_l10ns_last_modified()
                RETURNS TRIGGER AS $$
                BEGIN
                    UPDATE {$prefix}surveys s SET "[{$fieldName}]" = NOW()
                    FROM {$prefix}questions q WHERE q.qid = NEW.qid AND s.sid = q.sid;
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
                CREATE TRIGGER question_l10ns_last_modified
                AFTER UPDATE ON {$prefix}question_l10ns
                FOR EACH ROW EXECUTE FUNCTION question_l10ns_last_modified();
             SQL,
            <<<SQL
                CREATE OR REPLACE FUNCTION question_l10ns_last_modified_insert()
                RETURNS TRIGGER AS $$
                BEGIN
                    UPDATE {$prefix}surveys s SET "[{$fieldName}]" = NOW()
                    FROM {$prefix}questions q WHERE q.qid = NEW.qid AND s.sid = q.sid;
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
                CREATE TRIGGER question_l10ns_last_modified_insert
                AFTER INSERT ON {$prefix}question_l10ns
                FOR EACH ROW EXECUTE FUNCTION question_l10ns_last_modified_insert();
             SQL,
            <<<SQL
                CREATE OR REPLACE FUNCTION question_l10ns_last_modified_delete()
                RETURNS TRIGGER AS $$
                BEGIN
                    UPDATE {$prefix}surveys s SET "[{$fieldName}]" = NOW()
                    FROM {$prefix}questions q WHERE q.qid = OLD.qid AND s.sid = q.sid;
                    RETURN OLD;
                END;
                $$ LANGUAGE plpgsql;
                CREATE TRIGGER question_l10ns_last_modified_delete
                BEFORE DELETE ON {$prefix}question_l10ns
                FOR EACH ROW EXECUTE FUNCTION question_l10ns_last_modified_delete();
            SQL
        ];
    }

    public static function buildMysql(string $prefix, string $fieldName): array
    {
        return [
            <<<SQL
                CREATE TRIGGER `question_l10ns_last_modified`
                BEFORE UPDATE ON {$prefix}question_l10ns
                FOR EACH ROW BEGIN
                    UPDATE {$prefix}surveys s JOIN {$prefix}questions q ON q.sid = s.sid
                    SET s.{$fieldName} = NOW() WHERE q.qid = NEW.qid;
                END
             SQL,
            <<<SQL
                CREATE TRIGGER `question_l10ns_last_modified_delete`
                BEFORE DELETE ON {$prefix}question_l10ns
                FOR EACH ROW BEGIN
                    UPDATE {$prefix}surveys s JOIN {$prefix}questions q ON q.sid = s.sid
                    SET s.{$fieldName} = NOW() WHERE q.qid = OLD.qid;
                END
             SQL,
            <<<SQL
                CREATE TRIGGER `question_l10ns_last_modified_insert`
                AFTER INSERT ON {$prefix}question_l10ns
                FOR EACH ROW BEGIN
                    UPDATE {$prefix}surveys s JOIN {$prefix}questions q ON q.sid = s.sid
                    SET s.{$fieldName} = NOW() WHERE q.qid = NEW.qid;
                END
            SQL
        ];
    }
}
