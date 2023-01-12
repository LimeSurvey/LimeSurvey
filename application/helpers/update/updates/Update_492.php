<?php

namespace LimeSurvey\Helpers\Update;

class Update_492 extends DatabaseUpdateBase
{
    public function up()
    {
        // Handle questions with random_order = 1
        $this->db->createCommand(
            "INSERT INTO {{question_attributes}} (qid, " . $this->db->quoteColumnName("attribute") . ", " . $this->db->quoteColumnName("value") . ")
             SELECT
                qa.qid,
                'answer_order' AS " . $this->db->quoteColumnName("attribute") . ",
                'random' AS " . $this->db->quoteColumnName("value") . "
             FROM {{question_attributes}} qa
             JOIN {{questions}} q ON qa.qid = q.qid
             WHERE
                " . $this->db->quoteColumnName("attribute") . " = 'random_order'
                AND " . $this->db->quoteColumnName("value") . " = '1'
                AND q.type IN ('!', 'L', 'O', 'R')"
        )->execute();

        // Handle questions with alphasort = 1 and random_order = 0
        $this->db->createCommand(
            "INSERT INTO {{question_attributes}} (qid, " . $this->db->quoteColumnName("attribute") . ", " . $this->db->quoteColumnName("value") . ")
             SELECT
                a.qid,
                'answer_order' AS " . $this->db->quoteColumnName("attribute") . ",
                'alphabetical' AS " . $this->db->quoteColumnName("value") . "
             FROM (
                SELECT *
                FROM {{question_attributes}}
                WHERE " . $this->db->quoteColumnName("attribute") . " = 'alphasort'
                    AND " . $this->db->quoteColumnName("value") . " = '1'
             ) a LEFT JOIN (
                SELECT
                    qid,
                    " . $this->db->quoteColumnName("value") . " AS random_order
                FROM {{question_attributes}}
                WHERE " . $this->db->quoteColumnName("attribute") . " = 'random_order'
             ) r ON a.qid = r.qid WHERE random_order = '0' OR random_order IS NULL"
        )->execute();
    }
}
