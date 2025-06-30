<?php

namespace LimeSurvey\Helpers\Update;

class Update_634 extends DatabaseUpdateBase
{
    protected string $prefix;

    protected string $fieldName;

    /**
     * @throws \CDbException
     */
    public function up()
    {
        $this->prefix = App()->db->tablePrefix;
        $this->fieldName = 'lastmodified';

        switch ($this->db->driverName) {
            case 'mysql':
            case 'mssql':
            case 'sqlsrv':
                addColumn('{{surveys}}', $this->fieldName, 'datetime DEFAULT current_timestamp');
                break;
            case 'pgsql':
                addColumn('{{surveys}}', $this->fieldName, 'timestamp DEFAULT current_timestamp');
                break;
        }

        foreach ($this->getTriggers() as $trigger) {
            $this->db->createCommand($trigger)->execute();
        }
    }

    private function getTriggers(): array
    {
        $dbType = $this->db->driverName;
        return [
            $this->triggerAnswers($dbType),
            $this->triggerGroupL10ns($dbType),
            $this->triggerGroups($dbType),
            $this->triggerQuestionL10ns($dbType),
            $this->triggerQuestions($dbType),
            $this->triggerSurveys($dbType),
            $this->triggerLanguageSettings($dbType),
        ];
    }

    private function triggerAnswers($dbType): string
    {
        if ($dbType == 'mssql' || $dbType == 'sqlsrv') {
            return $this->triggerAnswerMssql();
        } elseif ($dbType == 'pgsql') {
            return $this->triggerAnswerPgsql();
        }
        return $this->triggerAnswerMysql();
    }

    private function triggerAnswerMssql(): string
    {
        return <<<SQL
CREATE TRIGGER answers_last_modified ON [{$this->prefix}answers]
AFTER UPDATE AS BEGIN
    UPDATE s SET s.[{$this->fieldName}] = GETDATE()
    FROM [{$this->prefix}surveys] s
    INNER JOIN {$this->prefix}questions q ON s.sid = q.sid
    INNER JOIN inserted i ON q.qid = i.qid;
END;

CREATE TRIGGER answers_last_modified_update ON [{$this->prefix}answers]
AFTER INSERT AS BEGIN
    UPDATE s SET s.[{$this->fieldName}] = GETDATE()
    FROM [{$this->prefix}surveys] s
    INNER JOIN {$this->prefix}questions q ON s.sid = q.sid
    INNER JOIN inserted i ON q.qid = i.qid;
END;

CREATE TRIGGER answers_last_modified_delete ON [{$this->prefix}answers]
AFTER DELETE AS BEGIN
    UPDATE s SET s.[{$this->fieldName}] = GETDATE()
    FROM [{$this->prefix}surveys] s
    INNER JOIN {$this->prefix}questions q ON s.sid = q.sid
    INNER JOIN deleted i ON q.qid = i.qid;
END;
SQL;
    }

    private function triggerAnswerPgsql(): string
    {
        return <<<SQL
CREATE OR REPLACE FUNCTION answers_last_modified()
RETURNS TRIGGER AS $$ BEGIN
    UPDATE {$this->prefix}surveys s SET {$this->fieldName} = NOW()
    FROM {$this->prefix}questions q
    WHERE q.qid = NEW.qid AND q.sid = s.sid;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER answers_last_modified AFTER UPDATE ON {$this->prefix}answers
FOR EACH ROW EXECUTE FUNCTION answers_last_modified();

CREATE OR REPLACE FUNCTION answers_last_modified_insert()
RETURNS TRIGGER AS $$ BEGIN
    UPDATE {$this->prefix}surveys s SET {$this->fieldName} = NOW()
    FROM {$this->prefix}questions q
    WHERE q.qid = NEW.qid AND q.sid = s.sid;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER answers_last_modified_insert AFTER INSERT ON {$this->prefix}answers
FOR EACH ROW EXECUTE FUNCTION answers_last_modified_insert();

CREATE OR REPLACE FUNCTION answers_last_modified_delete()
RETURNS TRIGGER AS $$ BEGIN
    UPDATE {$this->prefix}surveys s SET {$this->fieldName} = NOW()
    FROM {$this->prefix}questions q
    WHERE q.qid = OLD.qid AND q.sid = s.sid;
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER answers_last_modified_delete BEFORE DELETE ON {$this->prefix}answers
FOR EACH ROW EXECUTE FUNCTION answers_last_modified_delete();
SQL;
    }

    private function triggerAnswerMysql(): string
    {
        return <<<SQL
CREATE TRIGGER `answers_last_modified` AFTER UPDATE ON {$this->prefix}answers
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys s JOIN {$this->prefix}questions q ON q.sid = s.sid
    SET s.{$this->fieldName} = NOW() WHERE q.qid = NEW.qid;
END

CREATE TRIGGER `answers_last_modified_insert` AFTER INSERT ON {$this->prefix}answers
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys s JOIN {$this->prefix}questions q ON q.sid = s.sid
    SET s.{$this->fieldName} = NOW() WHERE q.qid = NEW.qid;
END

CREATE TRIGGER `answers_last_modified_delete` BEFORE DELETE ON {$this->prefix}answers
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys s JOIN {$this->prefix}questions q ON q.sid = s.sid
    SET s.{$this->fieldName} = NOW() WHERE q.qid = OLD.qid;
END
SQL;
    }

    private function triggerGroupL10ns($dbType): string
    {
        if ($dbType == 'mssql' || $dbType == 'sqlsrv') {
            return $this->triggerGroupL10nsMssql();
        } elseif ($dbType == 'pgsql') {
            return $this->triggerGroupL10nsPgsql();
        }
        return $this->triggerGroupL10nsMysql();
    }

    private function triggerGroupL10nsMssql(): string
    {
        return <<<SQL
CREATE TRIGGER group_l10ns_last_modified
ON [{$this->prefix}group_l10ns]
AFTER UPDATE AS
BEGIN
    UPDATE s SET s.{$this->fieldName} = GETDATE()
    FROM [{$this->prefix}surveys] s
    JOIN [{$this->prefix}groups] g ON s.sid = g.sid
    JOIN inserted i ON g.gid = i.gid;
END;

CREATE TRIGGER group_l10ns_last_modified_insert
ON [{$this->prefix}group_l10ns]
AFTER INSERT AS
BEGIN
    UPDATE s SET s.{$this->fieldName} = GETDATE()
    FROM [{$this->prefix}surveys] s
    JOIN [{$this->prefix}groups] g ON s.sid = g.sid
    JOIN inserted i ON g.gid = i.gid;
END;

CREATE TRIGGER group_l10ns_last_modified_delete
ON [{$this->prefix}group_l10ns]
BEFORE DELETE AS
BEGIN
    UPDATE s SET s.{$this->fieldName} = GETDATE()
    FROM [{$this->prefix}surveys] s
    JOIN [{$this->prefix}groups] g ON s.sid = g.sid
    JOIN deleted i ON g.gid = i.gid;
END;
SQL;
    }
    private function triggerGroupL10nsPgsql(): string
    {
        return  <<<SQL
CREATE OR REPLACE FUNCTION group_l10ns_last_modified()
RETURNS TRIGGER AS $$ BEGIN
    UPDATE {$this->prefix}surveys s SET {$this->fieldName} = NOW()
    FROM {$this->prefix}groups g WHERE g.gid = NEW.gid AND g.sid = s.sid;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER group_l10ns_last_modified AFTER UPDATE ON {$this->prefix}group_l10ns
FOR EACH ROW EXECUTE FUNCTION group_l10ns_last_modified();


CREATE OR REPLACE FUNCTION group_l10ns_last_modified_insert()
RETURNS TRIGGER AS $$ BEGIN
    UPDATE {$this->prefix}surveys s SET {$this->fieldName} = NOW()
    FROM {$this->prefix}groups g WHERE g.gid = NEW.gid AND g.sid = s.sid;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER group_l10ns_last_modified_insert AFTER INSERT ON {$this->prefix}group_l10ns
FOR EACH ROW EXECUTE FUNCTION group_l10ns_last_modified_insert();


CREATE OR REPLACE FUNCTION group_l10ns_last_modified_delete()
RETURNS TRIGGER AS $$ BEGIN
    UPDATE {$this->prefix}surveys s SET {$this->fieldName} = NOW()
    FROM {$this->prefix}groups g WHERE g.gid = OLD.gid AND g.sid = s.sid;
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER group_l10ns_last_modified_delete BEFORE DELETE ON {$this->prefix}group_l10ns
FOR EACH ROW EXECUTE FUNCTION group_l10ns_last_modified_delete();
SQL;
    }
    private function triggerGroupL10nsMysql(): string
    {
        return <<<SQL
CREATE TRIGGER `group_l10ns_last_modified`
AFTER UPDATE ON {$this->prefix}group_l10ns
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys s JOIN {$this->prefix}groups g ON g.sid = s.sid
    SET s.{$this->fieldName} = NOW() WHERE g.gid = NEW.gid;
END

CREATE TRIGGER `group_l10ns_last_modified_insert`
AFTER INSERT ON {$this->prefix}group_l10ns
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys s JOIN {$this->prefix}groups g ON g.sid = s.sid
    SET s.{$this->fieldName} = NOW() WHERE g.gid = NEW.gid;
END

CREATE TRIGGER `group_l10ns_last_modified_delete`
BEFORE DELETE ON {$this->prefix}group_l10ns
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys s JOIN {$this->prefix}groups g ON g.sid = s.sid
    SET s.{$this->fieldName} = NOW() WHERE g.gid = OLD.gid;
END
SQL;
    }


    private function triggerGroups($dbType): string
    {
        if ($dbType == 'mssql' || $dbType == 'sqlsrv') {
            return  $this->triggerGroupsMssql();
        } elseif ($dbType == 'pgsql') {
            return  $this->triggerGroupsPgsql();
        }
        return  $this->triggerGroupsMysql();
    }

    private function triggerGroupsMssql(): string
    {
        return  <<<SQL
CREATE TRIGGER groups_last_modified ON [{$this->prefix}groups]
AFTER UPDATE AS BEGIN
    UPDATE s SET s.[{$this->fieldName}] = GETDATE()
    FROM [{$this->prefix}surveys] s
    JOIN inserted i ON s.sid = i.sid;
END;

CREATE TRIGGER groups_last_modified_insert ON [{$this->prefix}groups]
AFTER INSERT AS BEGIN
    UPDATE s SET s.[{$this->fieldName}] = GETDATE()
    FROM [{$this->prefix}surveys] s
    JOIN inserted i ON s.sid = i.sid;
END;

CREATE TRIGGER groups_last_modified_delete ON [{$this->prefix}groups]
AFTER DELETE AS BEGIN
    UPDATE s SET s.[{$this->fieldName}] = GETDATE()
    FROM [{$this->prefix}surveys] s
    JOIN deleted i ON s.sid = i.sid;
END;

SQL;
    }
    private function triggerGroupsPgsql(): string
    {
        return  <<<SQL
CREATE OR REPLACE FUNCTION groups_last_modified()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE {$this->prefix}surveys SET {$this->fieldName} = NOW() WHERE sid = NEW.sid;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER groups_last_modified AFTER UPDATE ON {$this->prefix}groups
FOR EACH ROW EXECUTE FUNCTION groups_last_modified();

CREATE OR REPLACE FUNCTION groups_last_modified_insert()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE {$this->prefix}surveys SET {$this->fieldName} = NOW() WHERE sid = NEW.sid;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER groups_last_modified_insert AFTER INSERT ON {$this->prefix}groups
FOR EACH ROW EXECUTE FUNCTION groups_last_modified_insert();

CREATE OR REPLACE FUNCTION groups_last_modified_delete()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE {$this->prefix}surveys SET {$this->fieldName} = NOW() WHERE sid = OLD.sid;
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER groups_last_modified_delete BEFORE DELETE ON {$this->prefix}groups
FOR EACH ROW EXECUTE FUNCTION groups_last_modified_delete();
SQL;
    }
    private function triggerGroupsMysql(): string
    {
        return  <<<SQL
CREATE TRIGGER `groups_last_modified` AFTER UPDATE ON {$this->prefix}groups
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys SET {$this->fieldName} = NOW() 
    WHERE {$this->prefix}surveys.sid = NEW.sid;
END;

CREATE TRIGGER `groups_last_modified_insert` AFTER INSERT ON {$this->prefix}groups
 FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys SET {$this->fieldName} = NOW() 
    WHERE {$this->prefix}surveys.sid = NEW.sid;
END
 
CREATE TRIGGER `groups_last_modified_delete` BEFORE DELETE ON {$this->prefix}groups
 FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys SET {$this->fieldName} = NOW() 
    WHERE {$this->prefix}surveys.sid = OLD.sid;
END
SQL;
    }

    private function triggerQuestionL10ns($dbType): string
    {
        if ($dbType == 'mssql' || $dbType == 'sqlsrv') {
            $this->triggerQuestionL10nsMssql();
        } elseif ($dbType == 'pgsql') {
            $this->triggerQuestionL10nsPgsql();
        }
        $this->triggerQuestionL10nsMysql();

    }

    private function triggerQuestionL10nsMssql(): string
    {
        return  <<<SQL
CREATE TRIGGER question_l10ns_last_modified
ON [{$this->prefix}question_l10ns]
AFTER UPDATE AS
BEGIN
    UPDATE s SET s.[{$this->fieldName}] = GETDATE()
    FROM [{$this->prefix}surveys] s
    JOIN [{$this->prefix}questions] q ON s.sid = q.sid
    JOIN inserted i ON q.qid = i.qid;
END;

CREATE TRIGGER question_l10ns_last_modified_insert
ON [{$this->prefix}question_l10ns]
AFTER INSERT AS
BEGIN
    UPDATE s SET s.[{$this->fieldName}] = GETDATE()
    FROM [{$this->prefix}surveys] s
    JOIN [{$this->prefix}questions] q ON s.sid = q.sid
    JOIN inserted i ON q.qid = i.qid;
END;

CREATE TRIGGER question_l10ns_last_modified_delete
ON [{$this->prefix}question_l10ns]
AFTER DELETE AS
BEGIN
    UPDATE s SET s.[{$this->fieldName}] = GETDATE()
    FROM [{$this->prefix}surveys] s
    JOIN [{$this->prefix}questions] q ON s.sid = q.sid
    JOIN deleted i ON q.qid = i.qid;
END;

SQL;
    }
    private function triggerQuestionL10nsPgsql(): string
    {
        return  <<<SQL
CREATE OR REPLACE FUNCTION question_l10ns_last_modified()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE {$this->prefix}surveys s SET "[{$this->fieldName}]" = NOW()
    FROM {$this->prefix}questions q WHERE q.qid = NEW.qid AND s.sid = q.sid;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER question_l10ns_last_modified
AFTER UPDATE ON {$this->prefix}question_l10ns
FOR EACH ROW EXECUTE FUNCTION question_l10ns_last_modified();

CREATE OR REPLACE FUNCTION question_l10ns_last_modified_insert()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE {$this->prefix}surveys s SET "[{$this->fieldName}]" = NOW()
    FROM {$this->prefix}questions q WHERE q.qid = NEW.qid AND s.sid = q.sid;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER question_l10ns_last_modified_insert
AFTER INSERT ON {$this->prefix}question_l10ns
FOR EACH ROW EXECUTE FUNCTION question_l10ns_last_modified_insert();

CREATE OR REPLACE FUNCTION question_l10ns_last_modified_delete()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE {$this->prefix}surveys s SET "[{$this->fieldName}]" = NOW()
    FROM {$this->prefix}questions q WHERE q.qid = OLD.qid AND s.sid = q.sid;
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER question_l10ns_last_modified_delete
BEFORE DELETE ON {$this->prefix}question_l10ns
FOR EACH ROW EXECUTE FUNCTION question_l10ns_last_modified_delete();
SQL;
    }
    private function triggerQuestionL10nsMysql(): string
    {
        return  <<<SQL
CREATE TRIGGER `question_l10ns_last_modified`
BEFORE UPDATE ON {$this->prefix}question_l10ns
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys s JOIN {$this->prefix}questions q ON q.sid = s.sid
    SET s.{$this->fieldName} = NOW() WHERE q.qid = NEW.qid;
END

CREATE TRIGGER `question_l10ns_last_modified_delete`
BEFORE DELETE ON {$this->prefix}question_l10ns
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys s JOIN {$this->prefix}questions q ON q.sid = s.sid
    SET s.{$this->fieldName} = NOW() WHERE q.qid = OLD.qid;
END

CREATE TRIGGER `question_l10ns_last_modified_insert`
AFTER INSERT ON {$this->prefix}question_l10ns
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys s JOIN {$this->prefix}questions q ON q.sid = s.sid
    SET s.{$this->fieldName} = NOW() WHERE q.qid = NEW.qid;
END
SQL;
    }

    private function triggerQuestions($dbType): string
    {
        if ($dbType == 'mssql' || $dbType == 'sqlsrv') {
            return $this->triggerQuestionsMssql();
        } elseif ($dbType == 'pgsql') {
            return $this->triggerQuestionsPgsql();
        }
        return $this->triggerQuestionsMysql();
    }

    private function triggerQuestionsMssql(): string
    {
        return  <<<SQL
CREATE TRIGGER questions_last_modified
ON [{$this->prefix}questions]
AFTER UPDATE AS
BEGIN
    UPDATE s SET s.[{$this->fieldName}] = GETDATE()
    FROM [{$this->prefix}surveys] s
    JOIN inserted i ON s.sid = i.sid;
END;

CREATE TRIGGER questions_last_modified_insert
ON [{$this->prefix}questions]
AFTER INSERT AS
BEGIN
    UPDATE s SET s.[{$this->fieldName}] = GETDATE()
    FROM [{$this->prefix}surveys] s
    JOIN inserted i ON s.sid = i.sid;
END;

CREATE TRIGGER questions_last_modified_delete
ON [{$this->prefix}questions]
AFTER DELETE AS
BEGIN
    UPDATE s SET s.[{$this->fieldName}] = GETDATE()
    FROM [{$this->prefix}surveys] s
    JOIN deleted i ON s.sid = i.sid;
END;

SQL;
    }
    private function triggerQuestionsPgsql(): string
    {
        return  <<<SQL
CREATE OR REPLACE FUNCTION questions_last_modified()
RETURNS TRIGGER AS $$ BEGIN
    UPDATE {$this->prefix}surveys SET "[{$this->fieldName}]" = NOW() WHERE sid = NEW.sid;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER questions_last_modified 
AFTER UPDATE ON {$this->prefix}questions
FOR EACH ROW EXECUTE FUNCTION questions_last_modified();

CREATE OR REPLACE FUNCTION questions_last_modified_insert()
RETURNS TRIGGER AS $$ BEGIN
    UPDATE {$this->prefix}surveys SET "[{$this->fieldName}]" = NOW() WHERE sid = NEW.sid;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER questions_last_modified_insert 
AFTER INSERT ON {$this->prefix}questions
FOR EACH ROW EXECUTE FUNCTION questions_last_modified_insert();

CREATE OR REPLACE FUNCTION questions_last_modified_delete()
RETURNS TRIGGER AS $$ BEGIN
    UPDATE {$this->prefix}surveys SET "[{$this->fieldName}]" = NOW() WHERE sid = OLD.sid;
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER questions_last_modified_delete 
BEFORE DELETE ON {$this->prefix}questions
FOR EACH ROW EXECUTE FUNCTION questions_last_modified_delete();
SQL;
    }
    private function triggerQuestionsMysql(): string
    {
        return  <<<SQL
CREATE TRIGGER `questions_last_modified`
AFTER UPDATE ON {$this->prefix}questions
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys SET [{$this->fieldName}] = NOW() 
    WHERE {$this->prefix}surveys.sid = NEW.sid;
END;

CREATE TRIGGER `questions_last_modified_insert`
AFTER INSERT ON {$this->prefix}questions
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys SET [{$this->fieldName}] = NOW() 
    WHERE {$this->prefix}surveys.sid = NEW.sid;
END;

CREATE TRIGGER `questions_last_modified_delete`
BEFORE DELETE ON {$this->prefix}questions
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys SET [{$this->fieldName}] = NOW() 
    WHERE {$this->prefix}surveys.sid = OLD.sid;
END;
SQL;
    }

    private function triggerSurveys($dbType): string
    {
        if ($dbType == 'mssql' || $dbType == 'sqlsrv') {
            return $this->triggerSurveysMssql();
        } elseif ($dbType == 'pgsql') {
            return $this->triggerSurveysPgsql();
        }
        return $this->triggerSurveysMysql();
    }

    private function triggerSurveysMssql(): string
    {
        return  <<<SQL
CREATE TRIGGER surveys_last_modified
ON [{$this->prefix}surveys]
AFTER UPDATE AS
BEGIN
    UPDATE s SET s.[{$this->fieldName}] = GETDATE()
    FROM [{$this->prefix}surveys] s
    JOIN inserted i ON s.sid = i.sid;
END;
SQL;
    }
    private function triggerSurveysPgsql(): string
    {
        return  <<<SQL
CREATE OR REPLACE FUNCTION surveys_last_modified()
RETURNS TRIGGER AS $$
BEGIN
    NEW.{$this->fieldName} := NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER surveys_last_modified
AFTER UPDATE ON {$this->prefix}surveys
FOR EACH ROW EXECUTE FUNCTION surveys_last_modified();
SQL;
    }
    private function triggerSurveysMysql(): string
    {
        return  <<<SQL
CREATE TRIGGER surveys_last_modified
BEFORE UPDATE ON {$this->prefix}surveys
FOR EACH ROW BEGIN
    SET NEW.{$this->fieldName} = NOW();
END;
SQL;
    }

    private function triggerLanguageSettings($dbType): string
    {
        if ($dbType == 'mssql' || $dbType == 'sqlsrv') {
            return $this->triggerLanguageSettingsMssql();
        } elseif ($dbType == 'pgsql') {
            return $this->triggerLanguageSettingsPgsql();
        }
        return $this->triggerLanguageSettingsMysql();
    }

    private function triggerLanguageSettingsMssql(): string
    {
        return  <<<SQL
CREATE TRIGGER languagesettings_last_modified
ON [{$this->prefix}surveys_languagesettings]
AFTER UPDATE AS BEGIN
    UPDATE s SET s.[{$this->fieldName}] = GETDATE()
    FROM [{$this->prefix}surveys] s
    JOIN inserted i ON s.sid = i.surveyls_survey_id;
END;

CREATE TRIGGER languagesettings_last_modified_insert
ON {$this->prefix}surveys_languagesettings
AFTER INSERT AS BEGIN
    UPDATE s SET s.[{$this->fieldName}] = GETDATE()
    FROM {$this->prefix}surveys s
    JOIN inserted i ON s.sid = i.surveyls_survey_id;
END;

ALTER TRIGGER languagesettings_last_modified_delete
ON {$this->prefix}surveys_languagesettings
After DELETE AS BEGIN
    UPDATE s SET s.[{$this->fieldName}] = GETDATE()
    FROM {$this->prefix}surveys s
    JOIN deleted i ON s.sid = i.surveyls_survey_id;
END;
SQL;
    }
    private function triggerLanguageSettingsPgsql(): string
    {
        return  <<<SQL
CREATE OR REPLACE FUNCTION languagesettings_last_modified()
RETURNS TRIGGER AS $$ BEGIN
    UPDATE {$this->prefix}surveys SET {$this->fieldName} = NOW() 
    WHERE sid = NEW.surveyls_survey_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER languagesettings_last_modified
AFTER UPDATE ON {$this->prefix}surveys_languagesettings
FOR EACH ROW EXECUTE FUNCTION languagesettings_last_modified();

CREATE OR REPLACE FUNCTION languagesettings_last_modified_insert()
RETURNS TRIGGER AS $$ BEGIN
    UPDATE {$this->prefix}surveys SET {$this->fieldName} = NOW() 
    WHERE sid = NEW.surveyls_survey_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER languagesettings_last_modified_insert
AFTER INSERT ON {$this->prefix}surveys_languagesettings
FOR EACH ROW EXECUTE FUNCTION languagesettings_last_modified_insert();

CREATE OR REPLACE FUNCTION languagesettings_last_modified_delete()
RETURNS TRIGGER AS $$ BEGIN
    UPDATE {$this->prefix}surveys SET {$this->fieldName} = NOW() 
    WHERE sid = OLD.surveyls_survey_id;
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER languagesettings_last_modified_delete
BEFORE DELETE ON {$this->prefix}surveys_languagesettings
FOR EACH ROW EXECUTE FUNCTION languagesettings_last_modified_delete();
SQL;
    }
    private function triggerLanguageSettingsMysql(): string
    {
        return  <<<SQL
CREATE TRIGGER `languagesettings_last_modified`
AFTER UPDATE ON {$this->prefix}surveys_languagesettings
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys SET {$this->fieldName} = NOW() 
    WHERE {$this->prefix}surveys.sid = NEW.surveyls_survey_id;
END;

CREATE TRIGGER languagesettings_last_modified_insert
AFTER INSERT ON {$this->prefix}surveys_languagesettings
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys SET {$this->fieldName} = NOW() 
    WHERE {$this->prefix}surveys.sid = NEW.surveyls_survey_id;
END

CREATE TRIGGER languagesettings_last_modified_delete
BEFORE DELETE ON {$this->prefix}surveys_languagesettings
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys SET {$this->fieldName} = NOW() 
    WHERE {$this->prefix}surveys.sid = OLD.surveyls_survey_id;
END

SQL;
    }
}
