<?php

namespace LimeSurvey\Helpers\Update;

use LimeSurvey\Models\Services\GenderQuestionConverter;

class Update_720 extends DatabaseUpdateBase
{
    /**
     * Remove the Gender question type: convert all Gender questions into List (radio)
     * questions with the answer options F (Female) and M (Male) and remove the Gender
     * question themes.
     *
     * @return void
     */
    #[\Override]
    public function up()
    {
        $converted = (new GenderQuestionConverter($this->db))->convert();
        $this->widenResponseColumns($converted);

        $this->db->createCommand()->delete(
            '{{question_themes}}',
            'question_type = :type',
            [':type' => GenderQuestionConverter::LEGACY_TYPE]
        );
    }

    /**
     * Widen the response columns of converted questions in active surveys from the
     * single character the Gender question type used to the size of a List (radio) answer.
     *
     * @param array<int, int> $converted The converted question IDs as qid => sid
     * @return void
     */
    private function widenResponseColumns(array $converted): void
    {
        foreach ($converted as $qid => $sid) {
            $table = $this->db->schema->getTable("{{responses_{$sid}}}", true);
            if ($table === null) {
                continue;
            }
            $column = $table->getColumn("Q{$qid}");
            // Encrypted questions use a text column, which needs no change
            if ($column === null || $column->type !== 'string' || $column->size === null || $column->size >= 5) {
                continue;
            }
            $this->db->createCommand()->alterColumn("{{responses_{$sid}}}", "Q{$qid}", 'string(5)');
        }
    }
}
