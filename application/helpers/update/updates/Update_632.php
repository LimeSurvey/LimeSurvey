<?php

namespace LimeSurvey\Helpers\Update;

class Update_632 extends DatabaseUpdateBase
{
    public function up()
    {
        $this->db->createCommand()->addColumn('{{surveys}}', 'lastModified', 'datetime NULL');
        foreach ($this->getTriggers() as $trigger) {
            $this->db->createCommand($trigger)->execute();
        }
    }

    private function getTriggers(): array
    {
        return App()->db->driverName === 'pgsql'
            ? $this->getPostgresTriggers()
            : $this->getMySqlTriggers();
    }

    private function getPostgresTriggers(): array
    {
        return [
            <<<SQL
CREATE OR REPLACE FUNCTION update_survey_last_modified_from_answers()
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

CREATE TRIGGER lime_answers_last_modified
BEFORE UPDATE ON lime_answers
FOR EACH ROW
EXECUTE FUNCTION update_survey_last_modified_from_answers();
SQL,

            <<<SQL
CREATE OR REPLACE FUNCTION update_survey_last_modified_from_group_l10ns()
RETURNS TRIGGER AS \$\$
DECLARE
    survey_id INT;
BEGIN
    SELECT sid INTO survey_id FROM lime_groups WHERE gid = NEW.gid;
    IF survey_id IS NOT NULL THEN
        UPDATE lime_surveys SET "lastModified" = NOW() WHERE sid = survey_id;
    END IF;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

CREATE TRIGGER lime_group_l10ns_last_modified
BEFORE UPDATE ON lime_group_l10ns
FOR EACH ROW
EXECUTE FUNCTION update_survey_last_modified_from_group_l10ns();
SQL,

            <<<SQL
CREATE OR REPLACE FUNCTION update_survey_last_modified_from_groups()
RETURNS TRIGGER AS \$\$
BEGIN
    UPDATE lime_surveys SET "lastModified" = NOW() WHERE sid = NEW.sid;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

CREATE TRIGGER lime_groups_last_modified
BEFORE UPDATE ON lime_groups
FOR EACH ROW
EXECUTE FUNCTION update_survey_last_modified_from_groups();
SQL,

            <<<SQL
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
SQL,

            <<<SQL
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
SQL,

            <<<SQL
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
SQL,

            <<<SQL
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
SQL,
        ];
    }

    private function getMySqlTriggers(): array
    {
        return [
            <<<SQL
CREATE TRIGGER `lime_answers_last_modified` BEFORE UPDATE ON `lime_answers`
FOR EACH ROW BEGIN
    DECLARE survey_id INT;
    SELECT sid INTO survey_id FROM lime_questions WHERE lime_questions.qid = NEW.qid;
    UPDATE lime_surveys SET lastModified = NOW() WHERE lime_surveys.sid = survey_id;
END;
SQL,

            <<<SQL
CREATE TRIGGER `lime_group_l10ns_last_modified` BEFORE UPDATE ON `lime_group_l10ns`
FOR EACH ROW BEGIN
    DECLARE survey_id INT;
    SELECT sid INTO survey_id FROM lime_groups WHERE lime_groups.gid = NEW.gid;
    UPDATE lime_surveys SET lastModified = NOW() WHERE lime_surveys.sid = survey_id;
END;
SQL,

            <<<SQL
CREATE TRIGGER `lime_groups_last_modified` BEFORE UPDATE ON `lime_groups`
FOR EACH ROW BEGIN
    UPDATE lime_surveys SET lastModified = NOW() WHERE lime_surveys.sid = NEW.sid;
END;
SQL,

            <<<SQL
CREATE TRIGGER `lime_question_l10ns_last_modified` BEFORE UPDATE ON `lime_question_l10ns`
FOR EACH ROW BEGIN
    DECLARE survey_id INT;
    SELECT sid INTO survey_id FROM lime_questions WHERE lime_questions.qid = NEW.qid;
    UPDATE lime_surveys SET lastModified = NOW() WHERE lime_surveys.sid = survey_id;
END;
SQL,

            <<<SQL
CREATE TRIGGER `lime_questions_last_modified` BEFORE UPDATE ON `lime_questions`
FOR EACH ROW BEGIN
    UPDATE lime_surveys SET lastModified = NOW() WHERE lime_surveys.sid = NEW.sid;
END;
SQL,

            <<<SQL
CREATE TRIGGER `lime_surveys_last_modified` BEFORE UPDATE ON `lime_surveys`
FOR EACH ROW BEGIN
    SET NEW.lastModified = NOW();
END;
SQL,

            <<<SQL
CREATE TRIGGER `lime_surveys_languagesettings_last_modified` BEFORE UPDATE ON `lime_surveys_languagesettings`
FOR EACH ROW BEGIN
    UPDATE lime_surveys SET lastModified = NOW() WHERE lime_surveys.sid = NEW.surveyls_survey_id;
END;
SQL,
        ];
    }
}
