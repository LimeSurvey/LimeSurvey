<?php

namespace LimeSurvey\Models\Services;

use Survey;

/**
 * The response field names of the questions of a survey, as used by the
 * condition designer of the editor: The survey-questions-fieldname endpoint
 * returns them, and the survey patch returns them after creating questions.
 */
class QuestionFieldnames
{
    /**
     * Returns the field names of the questions of the survey, by question ID.
     * Comment and file count fields are left out: Conditions don't use them.
     *
     * @param Survey $survey The survey whose questions are listed
     * @return array<int, array<int, array{fieldname: string, sid: int, gid: int, qid: int, sqid: ?int, aid: ?string, title: string, scale_id: ?int}>>
     *   The fields of each question, keyed by question ID
     */
    public function getBySurvey(Survey $survey): array
    {
        $aDuplicateQIDs = [];
        $fullFieldMap = createFieldMap(
            $survey,
            'full',
            true,
            false,
            $survey->language,
            $aDuplicateQIDs,
            [],
            true
        );

        $structuredFieldnames = [];
        foreach ($fullFieldMap as $fieldCode => $fieldMeta) {
            if (
                !str_starts_with($fieldCode, 'Q')
                || str_contains($fieldMeta['fieldname'], 'comment')
                || str_contains($fieldMeta['fieldname'], 'filecount')
            ) {
                continue;
            }

            $structuredFieldnames[$fieldMeta['qid']][] = [
                'fieldname' => $fieldMeta['fieldname'],
                'sid'       => $fieldMeta['sid'],
                'gid'       => $fieldMeta['gid'],
                'qid'       => $fieldMeta['qid'],
                'sqid'      => $fieldMeta['sqid'] ?? null,
                'aid'       => $fieldMeta['aid'] ?? null,
                'title'     => $fieldMeta['title'],
                'scale_id'  => $fieldMeta['scale_id'] ?? null,
            ];
        }

        return $structuredFieldnames;
    }
}
