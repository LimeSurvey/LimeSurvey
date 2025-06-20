<?php

namespace LimeSurvey\Helpers\Update;

class Update_632 extends DatabaseUpdateBase
{
    /**
     * @throws \CDbException
     */
    public function up()
    {
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
        $prefix = App()->db->tablePrefix;
        if ($dbType == 'mysql') {
            return <<<SQL
CREATE TRIGGER `answers_last_modified` BEFORE UPDATE ON {$prefix}answers
FOR EACH ROW BEGIN
    DECLARE survey_id INT;
    SELECT sid INTO survey_id FROM {$prefix}questions WHERE {$prefix}questions.qid = NEW.qid;
    UPDATE {$prefix}surveys SET lastModified = NOW() WHERE {$prefix}surveys.sid = survey_id;
END;
SQL;
        } elseif ($dbType == 'mssql' || $dbType == 'sqlsrv') {
            return <<<SQL
CREATE TRIGGER answers_last_modified
ON [{$prefix}answers]
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @survey_id INT;

    SELECT TOP 1 @survey_id = q.sid
    FROM [{$prefix}questions] q
    JOIN inserted i ON q.qid = i.qid;

    UPDATE [{$prefix}surveys]
    SET lastModified = GETDATE()
    WHERE sid = @survey_id;
END;
SQL;
        } elseif ($dbType == 'pgsql') {
            return <<<SQL
CREATE OR REPLACE FUNCTION update_survey_last_modified_from_answers()
RETURNS TRIGGER AS \$\$
DECLARE
    survey_id INT;
BEGIN
    SELECT sid INTO survey_id FROM {$prefix}questions WHERE qid = NEW.qid;
    IF survey_id IS NOT NULL THEN
        UPDATE {$prefix}surveys SET "lastModified" = NOW() WHERE sid = survey_id;
    END IF;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

CREATE TRIGGER answers_last_modified
BEFORE UPDATE ON {$prefix}answers
FOR EACH ROW
EXECUTE FUNCTION update_survey_last_modified_from_answers();
SQL;
        }
    }

    private function triggerGroupL10ns($dbType): string
    {
        $prefix = App()->db->tablePrefix;
        if ($dbType == 'mysql') {
            return <<<SQL
CREATE TRIGGER `group_l10ns_last_modified` BEFORE UPDATE ON {$prefix}group_l10ns
FOR EACH ROW BEGIN
    DECLARE survey_id INT;
    SELECT sid INTO survey_id FROM {$prefix}groups WHERE {$prefix}groups.gid = NEW.gid;
    UPDATE {$prefix}surveys SET lastModified = NOW() WHERE {$prefix}surveys.sid = survey_id;
END;
SQL;
        } elseif ($dbType == 'mssql' || $dbType == 'sqlsrv') {
            return <<<SQL
            CREATE TRIGGER group_l10ns_last_modified
ON [{$prefix}group_l10ns]
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE s
    SET s.lastModified = GETDATE()
    FROM [{$prefix}surveys] s
    JOIN [{$prefix}groups] g ON s.sid = g.sid
    JOIN inserted i ON g.gid = i.gid;
END;
SQL;
        } elseif ($dbType == 'pgsql') {
            return  <<<SQL
CREATE OR REPLACE FUNCTION update_survey_last_modified_from_group_l10ns()
RETURNS TRIGGER AS \$\$
DECLARE
    survey_id INT;
BEGIN
    SELECT sid INTO survey_id FROM {$prefix}groups WHERE gid = NEW.gid;
    IF survey_id IS NOT NULL THEN
        UPDATE {$prefix}surveys SET "lastModified" = NOW() WHERE sid = survey_id;
    END IF;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

CREATE TRIGGER group_l10ns_last_modified
BEFORE UPDATE ON {$prefix}group_l10ns
FOR EACH ROW
EXECUTE FUNCTION update_survey_last_modified_from_group_l10ns();
SQL;
        }
    }

    private function triggerGroups($dbType): string
    {
        $prefix = App()->db->tablePrefix;
        if ($dbType == 'mysql') {
            return  <<<SQL
CREATE TRIGGER `groups_last_modified` BEFORE UPDATE ON {$prefix}groups
FOR EACH ROW BEGIN
    UPDATE {$prefix}surveys SET lastModified = NOW() WHERE {$prefix}surveys.sid = NEW.sid;
END;
SQL;
        } elseif ($dbType == 'mssql' || $dbType == 'sqlsrv') {
            return  <<<SQL
CREATE TRIGGER groups_last_modified
ON [{$prefix}groups]
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE s
    SET s.lastModified = GETDATE()
    FROM [{$prefix}surveys] s
    JOIN inserted i ON s.sid = i.sid;
END;
SQL;
        } elseif ($dbType == 'pgsql') {
            return  <<<SQL
CREATE OR REPLACE FUNCTION update_survey_last_modified_from_groups()
RETURNS TRIGGER AS \$\$
BEGIN
    UPDATE {$prefix}surveys SET "lastModified" = NOW() WHERE sid = NEW.sid;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

CREATE TRIGGER groups_last_modified
BEFORE UPDATE ON {$prefix}groups
FOR EACH ROW
EXECUTE FUNCTION update_survey_last_modified_from_groups();
SQL;
        }
    }

    private function triggerQuestionL10ns($dbType): string
    {
        $prefix = App()->db->tablePrefix;
        if ($dbType == 'mysql') {
            return  <<<SQL
CREATE TRIGGER `question_l10ns_last_modified` BEFORE UPDATE ON {$prefix}question_l10ns
FOR EACH ROW BEGIN
    DECLARE survey_id INT;
    SELECT sid INTO survey_id FROM {$prefix}questions WHERE {$prefix}questions.qid = NEW.qid;
    UPDATE surveys SET lastModified = NOW() WHERE {$prefix}surveys.sid = survey_id;
END;
SQL;
        } elseif ($dbType == 'mssql' || $dbType == 'sqlsrv') {
            return  <<<SQL
CREATE TRIGGER question_l10ns_last_modified
ON [{$prefix}question_l10ns]
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE s
    SET s.lastModified = GETDATE()
    FROM [{$prefix}surveys] s
    JOIN [{$prefix}questions] q ON s.sid = q.sid
    JOIN inserted i ON q.qid = i.qid;
END;

SQL;
        } elseif ($dbType == 'pgsql') {
            return  <<<SQL
CREATE OR REPLACE FUNCTION update_survey_last_modified_from_question_l10ns()
RETURNS TRIGGER AS \$\$
DECLARE
    survey_id INT;
BEGIN
    SELECT sid INTO survey_id FROM {$prefix}questions WHERE qid = NEW.qid;
    IF survey_id IS NOT NULL THEN
        UPDATE {$prefix}surveys SET "lastModified" = NOW() WHERE sid = survey_id;
    END IF;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

CREATE TRIGGER question_l10ns_last_modified
BEFORE UPDATE ON {$prefix}question_l10ns
FOR EACH ROW
EXECUTE FUNCTION update_survey_last_modified_from_question_l10ns();
SQL;
        }
    }

    private function triggerQuestions($dbType): string
    {
        $prefix = App()->db->tablePrefix;
        if ($dbType == 'mysql') {
            return  <<<SQL
CREATE TRIGGER `questions_last_modified` BEFORE UPDATE ON {$prefix}questions
FOR EACH ROW BEGIN
    UPDATE {$prefix}surveys SET lastModified = NOW() WHERE {$prefix}surveys.sid = NEW.sid;
END;
SQL;
        } elseif ($dbType == 'mssql' || $dbType == 'sqlsrv') {
            return  <<<SQL
CREATE TRIGGER questions_last_modified
ON [{$prefix}questions]
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE s
    SET s.lastModified = GETDATE()
    FROM [{$prefix}surveys] s
    JOIN inserted i ON s.sid = i.sid;
END;

SQL;
        } elseif ($dbType == 'pgsql') {
            return  <<<SQL
CREATE OR REPLACE FUNCTION update_survey_last_modified_from_questions()
RETURNS TRIGGER AS \$\$
BEGIN
    UPDATE {$prefix}surveys SET "lastModified" = NOW() WHERE sid = NEW.sid;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

CREATE TRIGGER questions_last_modified
BEFORE UPDATE ON {$prefix}questions
FOR EACH ROW
EXECUTE FUNCTION update_survey_last_modified_from_questions();
SQL;
        }
    }

    private function triggerSurveys($dbType): string
    {
        $prefix = App()->db->tablePrefix;
        if ($dbType == 'mysql') {
            return  <<<SQL
CREATE TRIGGER {$prefix}surveys_last_modified BEFORE UPDATE ON {$prefix}surveys
FOR EACH ROW BEGIN
    SET NEW.lastModified = NOW();
END;
SQL;
        } elseif ($dbType == 'mssql' || $dbType == 'sqlsrv') {
            return  <<<SQL
CREATE TRIGGER [{$prefix}surveys_last_modified]
ON [{$prefix}surveys]
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE s
    SET 
        s.lastModified = GETDATE()
    FROM [{$prefix}surveys] s
    JOIN inserted i ON s.sid = i.sid;
END;

SQL;
        } elseif ($dbType == 'pgsql') {
            return  <<<SQL
CREATE OR REPLACE FUNCTION update_last_modified()
RETURNS TRIGGER AS \$\$
BEGIN
    NEW."lastModified" := NOW();
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

CREATE TRIGGER surveys_last_modified
BEFORE UPDATE ON {$prefix}surveys
FOR EACH ROW
EXECUTE FUNCTION update_last_modified();
SQL;
        }
    }

    private function triggerLanguageSettings($dbType): string
    {
        $prefix = App()->db->tablePrefix;
        if ($dbType == 'mysql') {
            return  <<<SQL
CREATE TRIGGER `surveys_languagesettings_last_modified` BEFORE UPDATE ON {$prefix}surveys_languagesettings
FOR EACH ROW BEGIN
    UPDATE {$prefix}surveys SET lastModified = NOW() WHERE {$prefix}surveys.sid = NEW.surveyls_survey_id;
END;
SQL;
        } elseif ($dbType == 'mssql' || $dbType == 'sqlsrv') {
            return  <<<SQL
CREATE TRIGGER surveys_languagesettings_last_modified
ON [{$prefix}surveys_languagesettings]
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE s
    SET s.lastModified = GETDATE()
    FROM [{$prefix}surveys] s
    JOIN inserted i ON s.sid = i.surveyls_survey_id;
END;
SQL;
        } elseif ($dbType == 'pgsql') {
            return  <<<SQL
CREATE OR REPLACE FUNCTION update_survey_last_modified()
RETURNS TRIGGER AS \$\$
BEGIN
    UPDATE {$prefix}surveys SET "lastModified" = NOW() WHERE sid = NEW.surveyls_survey_id;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

CREATE TRIGGER surveys_languagesettings_last_modified
BEFORE UPDATE ON {$prefix}surveys_languagesettings
FOR EACH ROW
EXECUTE FUNCTION update_survey_last_modified();
SQL;
        }
    }
}
