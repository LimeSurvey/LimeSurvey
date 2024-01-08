<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Output;

use Survey;
use LimeSurvey\Models\Services\QuestionAggregateService\QuestionService;
use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

/**
 * TransformerOutputSurveyDetail
 */
class TransformerOutputSurveyDetail extends TransformerOutputActiveRecord
{
    private TransformerOutputSurvey $transformerSurvey;
    private TransformerOutputQuestionGroup $transformerQuestionGroup;
    private TransformerOutputQuestionGroupL10ns $transformerQuestionGroupL10ns;
    private TransformerOutputQuestion $transformerQuestion;
    private TransformerOutputQuestionL10ns $transformerQuestionL10ns;
    private TransformerOutputQuestionAttribute $transformerQuestionAttribute;
    private TransformerOutputAnswer $transformerAnswer;
    private QuestionService $questionService;
    private TransformerOutputAnswerL10ns $transformerAnswerL10ns;

    /**
     * Construct
     */
    public function __construct(
        TransformerOutputSurvey $transformerOutputSurvey,
        TransformerOutputQuestionGroup $transformerOutputQuestionGroup,
        TransformerOutputQuestionGroupL10ns $transformerOutputQuestionGroupL10ns,
        TransformerOutputQuestion $transformerOutputQuestion,
        TransformerOutputQuestionL10ns $transformerOutputQuestionL10ns,
        TransformerOutputQuestionAttribute $transformerOutputQuestionAttribute,
        TransformerOutputAnswer $transformerOutputAnswer,
        TransformerOutputAnswerL10ns $transformerOutputAnswerL10ns,
        QuestionService $questionService
    ) {
        $this->transformerSurvey = $transformerOutputSurvey;
        $this->transformerQuestionGroup = $transformerOutputQuestionGroup;
        $this->transformerQuestionGroupL10ns = $transformerOutputQuestionGroupL10ns;
        $this->transformerQuestion = $transformerOutputQuestion;
        $this->transformerQuestionL10ns = $transformerOutputQuestionL10ns;
        $this->transformerQuestionAttribute = $transformerOutputQuestionAttribute;
        $this->transformerAnswer = $transformerOutputAnswer;
        $this->transformerAnswerL10ns = $transformerOutputAnswerL10ns;
        $this->questionService = $questionService;
    }

    /**
     * Transform
     *
     * Returns an array of entity references indexed by the specified key.
     */
    public function transform($data)
    {
        if (!$data instanceof Survey) {
            return null;
        }

        $survey = $this->transformerSurvey->transform($data);
        $survey['templateInherited'] = $data->oOptions->template;
        $survey['formatInherited'] = $data->oOptions->format;
        $survey['languages'] = $data->allLanguages;

        // transformAll() can apply required entity sort so we must retain the sort order going forward
        // - We use a lookup array later to access entities without needing to know their position in the collection
        $survey['questionGroups'] = $this->transformerQuestionGroup->transformAll(
            $data->groups
        );

        // An array of groups indexed by gid for easy look up
        // - helps us to retain sort order when looping over models
        $groupLookup = $this->createCollectionLookup(
            'gid',
            $survey['questionGroups']
        );


        foreach ($data->groups as $questionGroupModel) {
            // Order of groups from the model relation may be different than from the transformed data
            // - so we use the lookup to get a reference to the required entity without needing to
            // - know its position in the output array
            // If we don't assign by reference here the, additions to $group will create a new array
            // - rather than modifying the original array
            $group = &$groupLookup[$questionGroupModel->gid];

            $group['l10ns'] = $this->transformerQuestionGroupL10ns->transformAll(
                $questionGroupModel->questiongroupl10ns
            );

            // transformAll() can apply required entity sort so we must retain the sort order going forward
            // - We use a lookup array later to access entities without needing to know their position in the collection
            $group['questions'] = $this->transformerQuestion->transformAll(
                $questionGroupModel->questions
            );
            $questionLookup = $this->createCollectionLookup(
                'qid',
                $group['questions']
            );

            $this->transformQuestions(
                $questionLookup,
                $questionGroupModel->questions
            );
        }

        return $survey;
    }

    /**
     * Transform Questions
     *
     * Returns an array of entity references indexed by the specified key.
     *
     * @param array $questionLookup
     * @param array $questions
     * @return void
     */
    private function transformQuestions($questionLookup, $questions)
    {
        foreach ($questions as $questionModel) {
            // questions from the model relation may be different than from the transformed data
            // - so we use the lookup to get a reference to the required entity without needing to
            // - know its position in the output array
            // If we don't assign by reference here the, additions to $question will create a new array
            // - rather than modifying the original array
            $question = &$questionLookup[$questionModel->qid];

            $question['l10ns'] = $this->transformerQuestionL10ns->transformAll(
                $questionModel->questionl10ns
            );

            $question['attributes'] = $this->transformerQuestionAttribute->transformAll(
                $this->questionService->getQuestionAttributes(
                    $questionModel->qid
                )
            );

            if ($questionModel->subquestions) {
                $question['subquestions'] = $this->transformerQuestion->transformAll(
                    $questionModel->subquestions
                );

                $subQuestionLookup = $this->createCollectionLookup(
                    'qid',
                    $question['subquestions']
                );
                $this->transformQuestions(
                    $subQuestionLookup,
                    $questionModel->subquestions
                );
            }

            $question['answers'] = $this->transformerAnswer->transformAll(
                $questionModel->answers
            );

            $answerLookup = $this->createCollectionLookup(
                'aid',
                $question['answers']
            );

            $this->transformAnswersL10n(
                $answerLookup,
                $questionModel->answers
            );
        }
    }

    /**
     * Adds the language specific data of answer_l10ns to the answers array
     * @param array $answerLookup
     * @param array $answers
     * @return void
     */
    private function transformAnswersL10n($answerLookup, $answers)
    {
        foreach ($answers as $answerModel) {
            $answer = &$answerLookup[$answerModel->aid];

            $answer['l10ns'] = $this->transformerAnswerL10ns->transformAll(
                $answerModel->answerl10ns
            );
        }
    }

    /**
     * Create collection lookup
     *
     * Returns an array of entity references indexed by the specified key.
     *
     * @param string $key
     * @param array $entityArray
     * @return array Entity reference
     */
    private function createCollectionLookup($key, &$entityArray)
    {
        $output = [];
        foreach ($entityArray as &$entity) {
            if (is_array($entity) && isset($entity[$key])) {
                $output[$entity[$key]] = &$entity;
            }
        }
        return $output;
    }
}
