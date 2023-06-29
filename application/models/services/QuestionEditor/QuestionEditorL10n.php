<?php

namespace LimeSurvey\Models\Services\QuestionEditor;

use QuestionL10n;

use LimeSurvey\Models\Services\Exception\{
    PersistErrorException,
    NotFoundException
};


/**
 * Question Editor L10n
 *
 */
class QuestionEditorL10n
{
    private QuestionL10n $modelQuestionL10n;

    public function __construct(
        QuestionL10n $modelQuestionL10n
    ) {
        $this->modelQuestionL10n = $modelQuestionL10n;
    }

    /**
     * @todo document me
     *
     * @param int $questionId
     * @param array{
     *      ...<array-key, array{
     *          question: string,
     *          help: string,
     *          ?language: string,
     *          ?script: string
     *      }>
     *  } $data
     * @param boolean $createIfNotExists
     * @return void
     * @throws NotFoundException
     * @throws PersistErrorException
     */
    public function save($questionId, $data, $createIfNotExists = true)
    {
        foreach ($data as $language => $l10nBlock) {
            $language = !empty($l10nBlock['language'])
                ? $l10nBlock['language']
                : $language;
            $l10n = $this->modelQuestionL10n
                ->findByAttributes([
                    'qid' => $questionId,
                    'language' => $language
                ]);
            if (empty($l10n)) {
                if ($createIfNotExists) {
                    $l10n = new QuestionL10n();
                } else {
                    throw new NotFoundException(
                        'Found no L10n object'
                    );
                }
            }
            $attributes = array_intersect_key(
                $l10nBlock,
                [
                    'question' => true,
                    'help' => true,
                    'script' => true
                ]
            );
            $l10n->setAttributes(
                $attributes,
                false
            );
            if (!$l10n->save()) {
                throw new PersistErrorException(
                    gT('Could not store translation')
                );
            }
        }
    }
}
