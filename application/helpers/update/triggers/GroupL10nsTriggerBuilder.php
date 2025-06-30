<?php

namespace LimeSurvey\Helpers\Update\triggers;

class GroupL10nsTriggerBuilder {
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
                CREATE TRIGGER group_l10ns_last_modified
                ON [{$prefix}group_l10ns]
                AFTER UPDATE AS
                BEGIN
                    UPDATE s SET s.{$fieldName} = GETDATE()
                    FROM [{$prefix}surveys] s
                    JOIN [{$prefix}groups] g ON s.sid = g.sid
                    JOIN inserted i ON g.gid = i.gid;
                END;
            SQL,
            <<<SQL
                CREATE TRIGGER group_l10ns_last_modified_insert
                ON [{$prefix}group_l10ns]
                AFTER INSERT AS
                BEGIN
                    UPDATE s SET s.{$fieldName} = GETDATE()
                    FROM [{$prefix}surveys] s
                    JOIN [{$prefix}groups] g ON s.sid = g.sid
                    JOIN inserted i ON g.gid = i.gid;
                END;
            SQL,
            <<<SQL
                CREATE TRIGGER group_l10ns_last_modified_delete
                ON [{$prefix}group_l10ns]
                BEFORE DELETE AS
                BEGIN
                    UPDATE s SET s.{$fieldName} = GETDATE()
                    FROM [{$prefix}surveys] s
                    JOIN [{$prefix}groups] g ON s.sid = g.sid
                    JOIN deleted i ON g.gid = i.gid;
                END;
            SQL
        ];
    }

    public static function buildPgsql(string $prefix, string $fieldName): array
    {
        return [
            <<<SQL
                CREATE OR REPLACE FUNCTION group_l10ns_last_modified()
                RETURNS TRIGGER AS $$ BEGIN
                    UPDATE {$prefix}surveys s SET {$fieldName} = NOW()
                    FROM {$prefix}groups g WHERE g.gid = NEW.gid AND g.sid = s.sid;
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
                CREATE TRIGGER group_l10ns_last_modified AFTER UPDATE ON {$prefix}group_l10ns
                FOR EACH ROW EXECUTE FUNCTION group_l10ns_last_modified();
            SQL,
            <<<SQL
                CREATE OR REPLACE FUNCTION group_l10ns_last_modified_insert()
                RETURNS TRIGGER AS $$ BEGIN
                    UPDATE {$prefix}surveys s SET {$fieldName} = NOW()
                    FROM {$prefix}groups g WHERE g.gid = NEW.gid AND g.sid = s.sid;
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
                CREATE TRIGGER group_l10ns_last_modified_insert AFTER INSERT ON {$prefix}group_l10ns
                FOR EACH ROW EXECUTE FUNCTION group_l10ns_last_modified_insert();
            SQL,
            <<<SQL
                CREATE OR REPLACE FUNCTION group_l10ns_last_modified_delete()
                RETURNS TRIGGER AS $$ BEGIN
                    UPDATE {$prefix}surveys s SET {$fieldName} = NOW()
                    FROM {$prefix}groups g WHERE g.gid = OLD.gid AND g.sid = s.sid;
                    RETURN OLD;
                END;
                $$ LANGUAGE plpgsql;
                CREATE TRIGGER group_l10ns_last_modified_delete BEFORE DELETE ON {$prefix}group_l10ns
                FOR EACH ROW EXECUTE FUNCTION group_l10ns_last_modified_delete();
            SQL
        ];
    }

    public static function buildMysql(string $prefix, string $fieldName): array
    {
        return [
            <<<SQL
                CREATE TRIGGER `group_l10ns_last_modified`
                AFTER UPDATE ON {$prefix}group_l10ns
                FOR EACH ROW BEGIN
                    UPDATE {$prefix}surveys s JOIN {$prefix}groups g ON g.sid = s.sid
                    SET s.{$fieldName} = NOW() WHERE g.gid = NEW.gid;
                END;
            SQL,
            <<<SQL
                CREATE TRIGGER `group_l10ns_last_modified_insert`
                AFTER INSERT ON {$prefix}group_l10ns
                FOR EACH ROW BEGIN
                    UPDATE {$prefix}surveys s JOIN {$prefix}groups g ON g.sid = s.sid
                    SET s.{$fieldName} = NOW() WHERE g.gid = NEW.gid;
                END;
            SQL,
            <<<SQL
                CREATE TRIGGER `group_l10ns_last_modified_delete`
                BEFORE DELETE ON {$prefix}group_l10ns
                FOR EACH ROW BEGIN
                    UPDATE {$prefix}surveys s JOIN {$prefix}groups g ON g.sid = s.sid
                    SET s.{$fieldName} = NOW() WHERE g.gid = OLD.gid;
                END;
            SQL
        ];
    }
}
