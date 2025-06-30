<?php

namespace LimeSurvey\Helpers\Update\triggers;

class SurveysTriggerBuilder {
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
                CREATE TRIGGER surveys_last_modified
                ON [{$prefix}surveys]
                AFTER UPDATE AS
                BEGIN
                    UPDATE s SET s.[{$fieldName}] = GETDATE()
                    FROM [{$prefix}surveys] s
                    JOIN inserted i ON s.sid = i.sid;
                END;
            SQL
        ];
    }

    public static function buildPgsql(string $prefix, string $fieldName): array
    {
        return [
            <<<SQL
                CREATE OR REPLACE FUNCTION surveys_last_modified()
                RETURNS TRIGGER AS $$
                BEGIN
                    NEW.{$fieldName} := NOW();
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
                
                CREATE TRIGGER surveys_last_modified
                AFTER UPDATE ON {$prefix}surveys
                FOR EACH ROW EXECUTE FUNCTION surveys_last_modified();
            SQL
        ];
    }

    public static function buildMysql(string $prefix, string $fieldName): array
    {
        return [
            <<<SQL
                CREATE TRIGGER surveys_last_modified
                BEFORE UPDATE ON {$prefix}surveys
                FOR EACH ROW BEGIN
                    SET NEW.{$fieldName} = NOW();
                END;
            SQL
        ];
    }
}
