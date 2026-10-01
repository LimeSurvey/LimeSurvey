<?php

namespace LimeSurvey\Models\Services\QuestionAggregateService;

use QuestionL10n;
use LimeSurvey\DI;
use LimeSurvey\Models\Services\Exception\{
    PersistErrorException,
    NotFoundException
};

/**
 * Question Aggregate L10n
 *
 */
class L10nService
{
    private QuestionL10n $modelQuestionL10n;

    public function __construct(
        QuestionL10n $modelQuestionL10n
    ) {
        $this->modelQuestionL10n = $modelQuestionL10n;
    }

    /**
     * Creates or updates the localized texts (question, help, script) of a question.
     *
     * All blocks are always stored for $questionId. A 'qid' key inside a block
     * is ignored, so the target question cannot be switched through the data.
     * The caller is responsible for verifying that $questionId belongs to a
     * survey the current user is allowed to update.
     *
     * @param int $questionId ID of the question the texts belong to
     * @param array {
     *      ...<array-key, array{
     *          ?qid: int,
     *          question: string,
     *          help: string,
     *          ?language: string,
     *          ?script: string
     *      }>
     *  } $data Localized texts, keyed by language code
     * @param boolean $createIfNotExists Create a missing L10n record instead of throwing
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
                    $l10n = DI::getContainer()
                        ->make(QuestionL10n::class);
                    $l10n->qid = $questionId;
                    $l10n->language = $language;
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
                    sprintf(
                        'Could not store translation %s',
                        json_encode($l10n->getErrors())
                    )
                );
            }
        }
    }
}
