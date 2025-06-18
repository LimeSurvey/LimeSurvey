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
                addColumn('{{surveys}}', 'lastModified', 'datetime current_timestamp');
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
CREATE TRIGGER `lime_answers_last_modified` BEFORE UPDATE ON {$prefix}lime_answers
FOR EACH ROW BEGIN
    DECLARE survey_id INT;
    SELECT sid INTO survey_id FROM {$prefix}lime_questions WHERE lime_questions.qid = NEW.qid;
    UPDATE {$prefix}lime_surveys SET lastModified = NOW() WHERE {$prefix}lime_surveys.sid = survey_id;
END;
SQL;
        }
        elseif ($dbType == 'mssql')
        {
        }
        elseif ($dbType == 'pgsql')
        {
            return <<<SQL
CREATE OR REPLACE FUNCTION update_survey_last_modified_from_answers()
RETURNS TRIGGER AS \$\$
DECLARE
    survey_id INT;
BEGIN
    SELECT sid INTO survey_id FROM {$prefix}lime_questions WHERE qid = NEW.qid;
    IF survey_id IS NOT NULL THEN
        UPDATE {$prefix}lime_surveys SET "lastModified" = NOW() WHERE sid = survey_id;
    END IF;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

CREATE TRIGGER lime_answers_last_modified
BEFORE UPDATE ON {$prefix}lime_answers
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
CREATE TRIGGER `lime_group_l10ns_last_modified` BEFORE UPDATE ON {$prefix}lime_group_l10ns
FOR EACH ROW BEGIN
    DECLARE survey_id INT;
    SELECT sid INTO survey_id FROM {$prefix}lime_groups WHERE {$prefix}lime_groups.gid = NEW.gid;
    UPDATE {$prefix}lime_surveys SET lastModified = NOW() WHERE {$prefix}lime_surveys.sid = survey_id;
END;
SQL;
        }
        elseif ($dbType == 'mssql')
        {

        }
        elseif ($dbType == 'pgsql')
        {
            return  <<<SQL
CREATE OR REPLACE FUNCTION update_survey_last_modified_from_group_l10ns()
RETURNS TRIGGER AS \$\$
DECLARE
    survey_id INT;
BEGIN
    SELECT sid INTO survey_id FROM {$prefix}lime_groups WHERE gid = NEW.gid;
    IF survey_id IS NOT NULL THEN
        UPDATE {$prefix}lime_surveys SET "lastModified" = NOW() WHERE sid = survey_id;
    END IF;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

CREATE TRIGGER lime_group_l10ns_last_modified
BEFORE UPDATE ON {$prefix}lime_group_l10ns
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
CREATE TRIGGER `lime_groups_last_modified` BEFORE UPDATE ON {$prefix}lime_groups
FOR EACH ROW BEGIN
    UPDATE {$prefix}lime_surveys SET lastModified = NOW() WHERE lime_surveys.sid = NEW.sid;
END;
SQL;
        }
        elseif ($dbType == 'mssql')
        {

        }
        elseif ($dbType == 'pgsql')
        {
            return  <<<SQL
CREATE OR REPLACE FUNCTION update_survey_last_modified_from_groups()
RETURNS TRIGGER AS \$\$
BEGIN
    UPDATE {$prefix}lime_surveys SET "lastModified" = NOW() WHERE sid = NEW.sid;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

CREATE TRIGGER lime_groups_last_modified
BEFORE UPDATE ON {$prefix}lime_groups
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
CREATE TRIGGER `lime_question_l10ns_last_modified` BEFORE UPDATE ON `lime_question_l10ns`
FOR EACH ROW BEGIN
    DECLARE survey_id INT;
    SELECT sid INTO survey_id FROM lime_questions WHERE lime_questions.qid = NEW.qid;
    UPDATE lime_surveys SET lastModified = NOW() WHERE lime_surveys.sid = survey_id;
END;
SQL;
        }
        elseif ($dbType == 'mssql')
        {

        }
        elseif ($dbType == 'pgsql')
        {
            return  <<<SQL
CREATE OR REPLACE FUNCTION update_survey_last_modified_from_question_l10ns()
RETURNS TRIGGER AS \$\$
DECLARE
    survey_id INT;
BEGIN
    SELECT sid INTO survey_id FROM lime_questions WHERE qid = NEW.qid;
    IF survey_id IS NOT NULL THEN
        UPDATE lime_surveys SET "lastModified" = NOW() WHERE sid = survey_id;
    END IF;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

CREATE TRIGGER lime_question_l10ns_last_modified
BEFORE UPDATE ON lime_question_l10ns
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
CREATE TRIGGER `lime_questions_last_modified` BEFORE UPDATE ON `lime_questions`
FOR EACH ROW BEGIN
    UPDATE lime_surveys SET lastModified = NOW() WHERE lime_surveys.sid = NEW.sid;
END;
SQL;
        }
        elseif ($dbType == 'mssql')
        {

        }
        elseif ($dbType == 'pgsql')
        {
            return  <<<SQL
CREATE OR REPLACE FUNCTION update_survey_last_modified_from_questions()
RETURNS TRIGGER AS \$\$
BEGIN
    UPDATE lime_surveys SET "lastModified" = NOW() WHERE sid = NEW.sid;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

CREATE TRIGGER lime_questions_last_modified
BEFORE UPDATE ON lime_questions
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
CREATE TRIGGER `lime_surveys_last_modified` BEFORE UPDATE ON `lime_surveys`
FOR EACH ROW BEGIN
    SET NEW.lastModified = NOW();
END;
SQL;}
        elseif ($dbType == 'mssql')
        {

        }
        elseif ($dbType == 'pgsql')
        {
            return  <<<SQL
CREATE OR REPLACE FUNCTION update_last_modified()
RETURNS TRIGGER AS \$\$
BEGIN
    NEW."lastModified" := NOW();
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

CREATE TRIGGER lime_surveys_last_modified
BEFORE UPDATE ON lime_surveys
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
CREATE TRIGGER `lime_surveys_languagesettings_last_modified` BEFORE UPDATE ON `lime_surveys_languagesettings`
FOR EACH ROW BEGIN
    UPDATE lime_surveys SET lastModified = NOW() WHERE lime_surveys.sid = NEW.surveyls_survey_id;
END;
SQL;}
        elseif ($dbType == 'mssql')
        {

        }
        elseif ($dbType == 'pgsql')
        {
            return  <<<SQL
CREATE OR REPLACE FUNCTION update_survey_last_modified()
RETURNS TRIGGER AS \$\$
BEGIN
    UPDATE lime_surveys SET "lastModified" = NOW() WHERE sid = NEW.surveyls_survey_id;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

CREATE TRIGGER lime_surveys_languagesettings_last_modified
BEFORE UPDATE ON lime_surveys_languagesettings
FOR EACH ROW
EXECUTE FUNCTION update_survey_last_modified();
SQL;
        }
    }
}
