<?php

namespace LimeSurvey\Helpers\Update;

class Update_632 extends DatabaseUpdateBase
{
    public function up()
    {
        try {
            setTransactionBookmark();
            $this->db->createCommand()->addColumn('{{surveys}}', 'lastModified', 'datetime NULL');
            foreach ($this->getTriggers() as $trigger) {
                $this->db->createCommand($trigger)->execute();
            }
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        }
    }

    private function getTriggers(): array
    {
        $triggers = [];

        $triggers[] = <<<SQL
CREATE TRIGGER `lime_answers_last_modified` BEFORE UPDATE ON `lime_answers`
FOR EACH ROW BEGIN
    DECLARE survey_id INT;

    SELECT sid INTO survey_id
    FROM lime_questions
    WHERE lime_questions.qid = NEW.qid;

    UPDATE lime_surveys
    SET lastModified = NOW()
    WHERE lime_surveys.sid = survey_id;
END;
SQL;

        $triggers[] = <<<SQL
CREATE TRIGGER `lime_group_l10ns_last_modified` BEFORE UPDATE ON `lime_group_l10ns`
FOR EACH ROW BEGIN
    DECLARE survey_id INT;

    SELECT sid INTO survey_id
    FROM lime_groups
    WHERE lime_groups.gid = NEW.gid;

    UPDATE lime_surveys
    SET lastModified = NOW()
    WHERE lime_surveys.sid = survey_id;
END;
SQL;

        $triggers[] = <<<SQL
CREATE TRIGGER `lime_groups_last_modified` BEFORE UPDATE ON `lime_groups`
FOR EACH ROW BEGIN
    UPDATE lime_surveys SET lastModified = NOW() WHERE lime_surveys.sid = NEW.sid;
END;
SQL;

        $triggers[] = <<<SQL
CREATE TRIGGER `lime_question_l10ns_last_modified` BEFORE UPDATE ON `lime_question_l10ns`
FOR EACH ROW BEGIN
    DECLARE survey_id INT;

    SELECT sid INTO survey_id
    FROM lime_questions
    WHERE lime_questions.qid = NEW.qid;

    UPDATE lime_surveys
    SET lastModified = NOW()
    WHERE lime_surveys.sid = survey_id;
END;
SQL;

        $triggers[] = <<<SQL
CREATE TRIGGER `lime_questions_last_modified` BEFORE UPDATE ON `lime_questions`
FOR EACH ROW BEGIN
    UPDATE lime_surveys SET lastModified = NOW() WHERE lime_surveys.sid = NEW.sid;
END;
SQL;

        $triggers[] = <<<SQL
CREATE TRIGGER `lime_surveys_last_modified` BEFORE UPDATE ON `lime_surveys`
FOR EACH ROW BEGIN
    SET NEW.lastModified = NOW();
END;
SQL;


        $triggers[] = <<<SQL
CREATE TRIGGER `lime_surveys_languagesettings_last_modified` BEFORE UPDATE ON `lime_surveys_languagesettings`
 FOR EACH ROW BEGIN
    UPDATE lime_surveys SET lastModified = NOW() WHERE lime_surveys.sid = NEW.surveyls_survey_id;
END
SQL;

        return $triggers;
    }
}
