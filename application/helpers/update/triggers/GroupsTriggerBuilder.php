<?php

namespace LimeSurvey\Helpers\Update\triggers;

class GroupsTriggerBuilder {
    public static function build(string $dbType, string $prefix, string $fieldName): string {
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
    public static function buildMssql(string $prefix, string $fieldName): string {
        return  <<<SQL
CREATE TRIGGER groups_last_modified ON [{$prefix}groups]
AFTER UPDATE AS BEGIN
    UPDATE s SET s.[{$fieldName}] = GETDATE()
    FROM [{$prefix}surveys] s
    JOIN inserted i ON s.sid = i.sid;
END;

CREATE TRIGGER groups_last_modified_insert ON [{$prefix}groups]
AFTER INSERT AS BEGIN
    UPDATE s SET s.[{$fieldName}] = GETDATE()
    FROM [{$prefix}surveys] s
    JOIN inserted i ON s.sid = i.sid;
END;

CREATE TRIGGER groups_last_modified_delete ON [{$prefix}groups]
AFTER DELETE AS BEGIN
    UPDATE s SET s.[{$fieldName}] = GETDATE()
    FROM [{$prefix}surveys] s
    JOIN deleted i ON s.sid = i.sid;
END;

SQL;
    }

    public static function buildPgsql(string $prefix, string $fieldName): string {
        return  <<<SQL
CREATE OR REPLACE FUNCTION groups_last_modified()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE {$prefix}surveys SET {$fieldName} = NOW() WHERE sid = NEW.sid;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER groups_last_modified AFTER UPDATE ON {$prefix}groups
FOR EACH ROW EXECUTE FUNCTION groups_last_modified();

CREATE OR REPLACE FUNCTION groups_last_modified_insert()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE {$prefix}surveys SET {$fieldName} = NOW() WHERE sid = NEW.sid;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER groups_last_modified_insert AFTER INSERT ON {$prefix}groups
FOR EACH ROW EXECUTE FUNCTION groups_last_modified_insert();

CREATE OR REPLACE FUNCTION groups_last_modified_delete()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE {$prefix}surveys SET {$fieldName} = NOW() WHERE sid = OLD.sid;
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER groups_last_modified_delete BEFORE DELETE ON {$prefix}groups
FOR EACH ROW EXECUTE FUNCTION groups_last_modified_delete();
SQL;
    }

    public static function buildMysql(string $prefix, string $fieldName): string {
        return  <<<SQL
CREATE TRIGGER `groups_last_modified` AFTER UPDATE ON {$prefix}groups
FOR EACH ROW BEGIN
    UPDATE {$prefix}surveys SET {$fieldName} = NOW() 
    WHERE {$prefix}surveys.sid = NEW.sid;
END;

CREATE TRIGGER `groups_last_modified_insert` AFTER INSERT ON {$prefix}groups
 FOR EACH ROW BEGIN
    UPDATE {$prefix}surveys SET {$fieldName} = NOW() 
    WHERE {$prefix}surveys.sid = NEW.sid;
END
 
CREATE TRIGGER `groups_last_modified_delete` BEFORE DELETE ON {$prefix}groups
 FOR EACH ROW BEGIN
    UPDATE {$prefix}surveys SET {$fieldName} = NOW() 
    WHERE {$prefix}surveys.sid = OLD.sid;
END
SQL;
    }
}
