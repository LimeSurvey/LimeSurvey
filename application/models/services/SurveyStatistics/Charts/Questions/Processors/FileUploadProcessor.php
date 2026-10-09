<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\Processors;

/**
 * File upload. It emits an empty plan only to claim a card slot in the
 * statistics list; the client lists the uploaded files per response.
 */
class FileUploadProcessor extends AbstractQuestionProcessor
{
    #[\Override]
    public function process()
    {
        return [
            'title' => $this->question['question'],
            'legend' => [],
            'data' => [],
        ];
    }
}
