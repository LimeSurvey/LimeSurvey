<?php

namespace LimeSurvey\Helpers\Update\triggers;

class LanguageSettingsTriggerBuilder {
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
CREATE TRIGGER languagesettings_last_modified
ON [{$prefix}surveys_languagesettings]
AFTER UPDATE AS BEGIN
    UPDATE s SET s.[{$fieldName}] = GETDATE()
    FROM [{$prefix}surveys] s
    JOIN inserted i ON s.sid = i.surveyls_survey_id;
END;

CREATE TRIGGER languagesettings_last_modified_insert
ON {$prefix}surveys_languagesettings
AFTER INSERT AS BEGIN
    UPDATE s SET s.[{$fieldName}] = GETDATE()
    FROM {$prefix}surveys s
    JOIN inserted i ON s.sid = i.surveyls_survey_id;
END;

ALTER TRIGGER languagesettings_last_modified_delete
ON {$prefix}surveys_languagesettings
After DELETE AS BEGIN
    UPDATE s SET s.[{$fieldName}] = GETDATE()
    FROM {$prefix}surveys s
    JOIN deleted i ON s.sid = i.surveyls_survey_id;
END;
SQL;
    }

    public static function buildPgsql(string $prefix, string $fieldName): string {
        return  <<<SQL
CREATE OR REPLACE FUNCTION languagesettings_last_modified()
RETURNS TRIGGER AS $$ BEGIN
    UPDATE {$prefix}surveys SET {$fieldName} = NOW() 
    WHERE sid = NEW.surveyls_survey_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER languagesettings_last_modified
AFTER UPDATE ON {$prefix}surveys_languagesettings
FOR EACH ROW EXECUTE FUNCTION languagesettings_last_modified();

CREATE OR REPLACE FUNCTION languagesettings_last_modified_insert()
RETURNS TRIGGER AS $$ BEGIN
    UPDATE {$prefix}surveys SET {$fieldName} = NOW() 
    WHERE sid = NEW.surveyls_survey_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER languagesettings_last_modified_insert
AFTER INSERT ON {$prefix}surveys_languagesettings
FOR EACH ROW EXECUTE FUNCTION languagesettings_last_modified_insert();

CREATE OR REPLACE FUNCTION languagesettings_last_modified_delete()
RETURNS TRIGGER AS $$ BEGIN
    UPDATE {$prefix}surveys SET {$fieldName} = NOW() 
    WHERE sid = OLD.surveyls_survey_id;
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER languagesettings_last_modified_delete
BEFORE DELETE ON {$prefix}surveys_languagesettings
FOR EACH ROW EXECUTE FUNCTION languagesettings_last_modified_delete();
SQL;
    }

    public static function buildMysql(string $prefix, string $fieldName): string {
        return  <<<SQL
CREATE TRIGGER `languagesettings_last_modified`
AFTER UPDATE ON {$prefix}surveys_languagesettings
FOR EACH ROW BEGIN
    UPDATE {$prefix}surveys SET {$fieldName} = NOW() 
    WHERE {$prefix}surveys.sid = NEW.surveyls_survey_id;
END;

CREATE TRIGGER languagesettings_last_modified_insert
AFTER INSERT ON {$prefix}surveys_languagesettings
FOR EACH ROW BEGIN
    UPDATE {$prefix}surveys SET {$fieldName} = NOW() 
    WHERE {$prefix}surveys.sid = NEW.surveyls_survey_id;
END

CREATE TRIGGER languagesettings_last_modified_delete
BEFORE DELETE ON {$prefix}surveys_languagesettings
FOR EACH ROW BEGIN
    UPDATE {$prefix}surveys SET {$fieldName} = NOW() 
    WHERE {$prefix}surveys.sid = OLD.surveyls_survey_id;
END

SQL;
    }
}
