<?php

namespace LimeSurvey\Helpers\Update;

class Update_640 extends DatabaseUpdateBase
{
    /**
     * Looking for question attribute random_order for questions of type M and P.
     * If the attribute value is 1, then the new subquestion_order attribute
     * will be inserted with the value 'random' and random_order will be set to 0.
     *
     * @return void
     */
    public function up()
    {
        // Handle questions with random_order = 1
        $this->db->createCommand(
            "INSERT INTO {{question_attributes}} (qid, "
            . $this->db->quoteColumnName("attribute") . ", "
            . $this->db->quoteColumnName("value") . ")
             SELECT
                qa.qid,
                'subquestion_order' AS " . $this->db->quoteColumnName(
                "attribute"
            ) . ",
                'random' AS " . $this->db->quoteColumnName("value") . "
             FROM {{question_attributes}} qa
             JOIN {{questions}} q ON qa.qid = q.qid
             WHERE
                " . $this->db->quoteColumnName("attribute") . " = 'random_order'
                AND " . $this->db->quoteColumnName("value") . " = 1
                AND q.type IN ('M', 'P')"
        )->execute();

        // Update the random_order attribute to 0 for the same questions
        $this->db->createCommand(
            "UPDATE {{question_attributes}} 
             SET " . $this->db->quoteColumnName("value") . " = 0
             WHERE " . $this->db->quoteColumnName("attribute") . " = 'random_order'
             AND " . $this->db->quoteColumnName("value") . " = 1
             AND qid IN (
                SELECT q.qid 
                FROM {{questions}} q 
                WHERE q.type IN ('M', 'P')
             )"
        )->execute();
    }
}
