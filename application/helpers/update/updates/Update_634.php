<?php

namespace LimeSurvey\Helpers\Update;

class Update_634 extends DatabaseUpdateBase
{
    private string $prefix;

    /**
     * @throws \CDbException
     */
    public function up()
    {
        $this->prefix = App()->db->tablePrefix;

        switch ($this->db->driverName) {
            case 'mysql':
            case 'mssql':
            case 'sqlsrv':
                addColumn('{{surveys}}', 'lastModified', 'datetime DEFAULT current_timestamp');
                break;
            case 'pgsql':
                addColumn('{{surveys}}', 'lastModified', 'timestamp current_timestamp');
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
            return <<<SQL
CREATE TRIGGER answers_last_modified
ON [{$this->prefix}answers]
AFTER UPDATE AS
BEGIN
    UPDATE s SET s.lastModified = GETDATE()
    FROM [{$this->prefix}surveys] s
    INNER JOIN [{$this->prefix}questions] q ON s.sid = q.sid
    INNER JOIN inserted i ON q.qid = i.qid;
END;
SQL;
        } elseif ($dbType == 'pgsql') {
            return <<<SQL
CREATE OR REPLACE FUNCTION answers_last_modified()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE {$this->prefix}surveys s SET "lastModified" = NOW()
    FROM {$this->prefix}questions q
    WHERE q.qid = NEW.qid AND q.sid = s.sid;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER answers_last_modified
BEFORE UPDATE ON {$this->prefix}answers
FOR EACH ROW EXECUTE FUNCTION answers_last_modified();
SQL;
        }

        return <<<SQL
CREATE TRIGGER `answers_last_modified` 
BEFORE UPDATE ON {$this->prefix}answers
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys s JOIN {$this->prefix}questions q ON q.sid = s.sid
    SET s.lastModified = NOW() WHERE q.qid = NEW.qid;
END
SQL;
    }

    private function triggerGroupL10ns($dbType): string
    {
        if ($dbType == 'mssql' || $dbType == 'sqlsrv') {
            return <<<SQL
CREATE TRIGGER group_l10ns_last_modified
ON [{$this->prefix}group_l10ns]
AFTER UPDATE AS
BEGIN
    UPDATE s SET s.lastModified = GETDATE()
    FROM [{$this->prefix}surveys] s
    JOIN [{$this->prefix}groups] g ON s.sid = g.sid
    JOIN inserted i ON g.gid = i.gid;
END;
SQL;
        } elseif ($dbType == 'pgsql') {
            return  <<<SQL
CREATE OR REPLACE FUNCTION group_l10ns_last_modified()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE {$this->prefix}surveys s SET "lastModified" = NOW()
    FROM {$this->prefix}groups g WHERE g.gid = NEW.gid AND g.sid = s.sid;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER group_l10ns_last_modified
BEFORE UPDATE ON {$this->prefix}group_l10ns
FOR EACH ROW EXECUTE FUNCTION group_l10ns_last_modified();
SQL;
        }

        return <<<SQL
CREATE TRIGGER `group_l10ns_last_modified`
BEFORE UPDATE ON {$this->prefix}group_l10ns
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys s JOIN {$this->prefix}groups g ON g.sid = s.sid
    SET s.lastModified = NOW() WHERE g.gid = NEW.gid;
END
SQL;
    }

    private function triggerGroups($dbType): string
    {
        if ($dbType == 'mssql' || $dbType == 'sqlsrv') {
            return  <<<SQL
CREATE TRIGGER groups_last_modified
ON [{$this->prefix}groups]
AFTER UPDATE AS
BEGIN
    UPDATE s SET s.lastModified = GETDATE()
    FROM [{$this->prefix}surveys] s
    JOIN inserted i ON s.sid = i.sid;
END;
SQL;
        } elseif ($dbType == 'pgsql') {
            return  <<<SQL
CREATE OR REPLACE FUNCTION groups_last_modified()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE {$this->prefix}surveys SET "lastModified" = NOW() WHERE sid = NEW.sid;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER groups_last_modified
BEFORE UPDATE ON {$this->prefix}groups
FOR EACH ROW EXECUTE FUNCTION groups_last_modified();
SQL;
        }

        return  <<<SQL
CREATE TRIGGER `groups_last_modified`
BEFORE UPDATE ON {$this->prefix}groups
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys SET lastModified = NOW() 
    WHERE {$this->prefix}surveys.sid = NEW.sid;
END;
SQL;
    }

    private function triggerQuestionL10ns($dbType): string
    {
        if ($dbType == 'mssql' || $dbType == 'sqlsrv') {
            return  <<<SQL
CREATE TRIGGER question_l10ns_last_modified
ON [{$this->prefix}question_l10ns]
AFTER UPDATE AS
BEGIN
    UPDATE s SET s.lastModified = GETDATE()
    FROM [{$this->prefix}surveys] s
    JOIN [{$this->prefix}questions] q ON s.sid = q.sid
    JOIN inserted i ON q.qid = i.qid;
END;

SQL;
        } elseif ($dbType == 'pgsql') {
            return  <<<SQL
CREATE OR REPLACE FUNCTION question_l10ns_last_modified()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE {$this->prefix}surveys s SET "lastModified" = NOW()
    FROM {$this->prefix}questions q WHERE q.qid = NEW.qid AND s.sid = q.sid;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER question_l10ns_last_modified
BEFORE UPDATE ON {$this->prefix}question_l10ns
FOR EACH ROW EXECUTE FUNCTION question_l10ns_last_modified();
SQL;
        }

        return  <<<SQL
CREATE TRIGGER `question_l10ns_last_modified`
BEFORE UPDATE ON {$this->prefix}question_l10ns
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys s JOIN {$this->prefix}questions q ON q.sid = s.sid
    SET s.lastModified = NOW() WHERE q.qid = NEW.qid;
END
SQL;
    }

    private function triggerQuestions($dbType): string
    {
        if ($dbType == 'mssql' || $dbType == 'sqlsrv') {
            return  <<<SQL
CREATE TRIGGER questions_last_modified
ON [{$this->prefix}questions]
AFTER UPDATE AS
BEGIN
    UPDATE s SET s.lastModified = GETDATE()
    FROM [{$this->prefix}surveys] s
    JOIN inserted i ON s.sid = i.sid;
END;

SQL;
        } elseif ($dbType == 'pgsql') {
            return  <<<SQL
CREATE OR REPLACE FUNCTION questions_last_modified()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE {$this->prefix}surveys SET "lastModified" = NOW() WHERE sid = NEW.sid;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER questions_last_modified
BEFORE UPDATE ON {$this->prefix}questions
FOR EACH ROW EXECUTE FUNCTION questions_last_modified();
SQL;
        }

        return  <<<SQL
CREATE TRIGGER `questions_last_modified`
BEFORE UPDATE ON {$this->prefix}questions
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys SET lastModified = NOW() 
    WHERE {$this->prefix}surveys.sid = NEW.sid;
END;
SQL;
    }

    private function triggerSurveys($dbType): string
    {
        if ($dbType == 'mssql' || $dbType == 'sqlsrv') {
            return  <<<SQL
CREATE TRIGGER surveys_last_modified
ON [{$this->prefix}surveys]
AFTER UPDATE AS
BEGIN
    UPDATE s SET s.lastModified = GETDATE()
    FROM [{$this->prefix}surveys] s
    JOIN inserted i ON s.sid = i.sid;
END;

SQL;
        } elseif ($dbType == 'pgsql') {
            return  <<<SQL
CREATE OR REPLACE FUNCTION surveys_last_modified()
RETURNS TRIGGER AS $$
BEGIN
    NEW."lastModified" := NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER surveys_last_modified
BEFORE UPDATE ON {$this->prefix}surveys
FOR EACH ROW EXECUTE FUNCTION surveys_last_modified();
SQL;
        }

        return  <<<SQL
CREATE TRIGGER surveys_last_modified
BEFORE UPDATE ON {$this->prefix}surveys
FOR EACH ROW BEGIN
    SET NEW.lastModified = NOW();
END;
SQL;
    }

    private function triggerLanguageSettings($dbType): string
    {
        if ($dbType == 'mssql' || $dbType == 'sqlsrv') {
            return  <<<SQL
CREATE TRIGGER languagesettings_last_modified
ON [{$this->prefix}surveys_languagesettings]
AFTER UPDATE AS
BEGIN
    UPDATE s SET s.lastModified = GETDATE()
    FROM [{$this->prefix}surveys] s
    JOIN inserted i ON s.sid = i.surveyls_survey_id;
END;
SQL;
        } elseif ($dbType == 'pgsql') {
            return  <<<SQL
CREATE OR REPLACE FUNCTION languagesettings_last_modified()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE {$this->prefix}surveys SET "lastModified" = NOW() 
    WHERE sid = NEW.surveyls_survey_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER languagesettings_last_modified
BEFORE UPDATE ON {$this->prefix}surveys_languagesettings
FOR EACH ROW EXECUTE FUNCTION languagesettings_last_modified();
SQL;
        }

        return  <<<SQL
CREATE TRIGGER `languagesettings_last_modified`
BEFORE UPDATE ON {$this->prefix}surveys_languagesettings
FOR EACH ROW BEGIN
    UPDATE {$this->prefix}surveys SET lastModified = NOW() 
    WHERE {$this->prefix}surveys.sid = NEW.surveyls_survey_id;
END;
SQL;
    }
}
