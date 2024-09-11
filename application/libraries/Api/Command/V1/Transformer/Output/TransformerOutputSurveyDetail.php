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
    private TransformerOutputSurveyGroup $transformerSurveyGroup;
    private TransformerOutputQuestionGroup $transformerQuestionGroup;
    private TransformerOutputQuestionGroupL10ns $transformerQuestionGroupL10ns;
    private TransformerOutputQuestion $transformerQuestion;
    private TransformerOutputQuestionL10ns $transformerQuestionL10ns;
    private TransformerOutputQuestionAttribute $transformerQuestionAttribute;
    private TransformerOutputAnswer $transformerAnswer;
    private TransformerOutputSurveyOwner $transformerSurveyOwner;
    private QuestionService $questionService;
    private TransformerOutputAnswerL10ns $transformerAnswerL10ns;

    /**
     * Construct
     */
    public function __construct(
        TransformerOutputSurvey $transformerOutputSurvey,
        TransformerOutputSurveyGroup $transformerOutputSurveyGroup,
        TransformerOutputQuestionGroup $transformerOutputQuestionGroup,
        TransformerOutputQuestionGroupL10ns $transformerOutputQuestionGroupL10ns,
        TransformerOutputQuestion $transformerOutputQuestion,
        TransformerOutputQuestionL10ns $transformerOutputQuestionL10ns,
        TransformerOutputQuestionAttribute $transformerOutputQuestionAttribute,
        TransformerOutputAnswer $transformerOutputAnswer,
        TransformerOutputAnswerL10ns $transformerOutputAnswerL10ns,
        TransformerOutputSurveyOwner $transformerOutputSurveyOwner,
        QuestionService $questionService
    ) {
        $this->transformerSurvey = $transformerOutputSurvey;
        $this->transformerSurveyGroup = $transformerOutputSurveyGroup;
        $this->transformerQuestionGroup = $transformerOutputQuestionGroup;
        $this->transformerQuestionGroupL10ns = $transformerOutputQuestionGroupL10ns;
        $this->transformerQuestion = $transformerOutputQuestion;
        $this->transformerQuestionL10ns = $transformerOutputQuestionL10ns;
        $this->transformerQuestionAttribute = $transformerOutputQuestionAttribute;
        $this->transformerAnswer = $transformerOutputAnswer;
        $this->transformerAnswerL10ns = $transformerOutputAnswerL10ns;
        $this->transformerSurveyOwner = $transformerOutputSurveyOwner;
        $this->questionService = $questionService;
    }

    /**
     * Transform
     *
     * Returns an array of entity references indexed by the specified key.
     *
     * @param ?mixed $data
     * @param ?mixed $options
     * @return ?mixed
     * @throws \LimeSurvey\Api\Transformer\TransformerException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function transform($data, $options = [])
    {
        if (!$data instanceof Survey) {
            return null;
        }

        $options = $options ?? [];

        $data = $this->setInheritedBetaOptions($data);
        $survey = $this->transformerSurvey->transform($data);
        $survey['templateInherited'] = $data->oOptions->template;
        $survey['formatInherited'] = $data->oOptions->format;
        $survey['languages'] = $data->allLanguages;
        $survey['previewLink'] = App()->createUrl(
            "survey/index",
            array('sid' => $data->sid, 'newtest' => "Y", 'lang' => $data->language)
        );
        $survey['surveyGroup'] = $this->transformerSurveyGroup->transform($data->surveygroup);
        $survey['owner'] = $this->transformerSurveyOwner->transform($data->owner);
        $survey['ownerInherited'] = $this->transformerSurveyOwner->transform($data->oOptions->owner);

        // transformAll() can apply required entity sort so we must retain the sort order going forward
        // - We use a lookup array later to access entities without needing to know their position in the collection
        $survey['questionGroups'] = $this->transformerQuestionGroup->transformAll(
            $data->groups,
            $options
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
                $questionGroupModel->questiongroupl10ns,
                $options
            );

            // transformAll() can apply required entity sort so we must retain the sort order going forward
            // - We use a lookup array later to access entities without needing to know their position in the collection
            $group['questions'] = $this->transformerQuestion->transformAll(
                $questionGroupModel->questions,
                $options
            );
            $questionLookup = $this->createCollectionLookup(
                'qid',
                $group['questions']
            );

            $this->transformQuestions(
                $questionLookup,
                $questionGroupModel->questions,
                $options
            );
        }
        $survey['hasSurveyUpdatePermission'] = $data->hasPermission('surveycontent', 'update');

        return $survey;
    }

    /**
     * Transform Questions
     *
     * Returns an array of entity references indexed by the specified key.
     *
     * @param array $questionLookup
     * @param array $questions
     * @param ?array $options
     * @return void
     */
    private function transformQuestions($questionLookup, $questions, $options = [])
    {
        foreach ($questions as $questionModel) {
            // questions from the model relation may be different than from the transformed data
            // - so we use the lookup to get a reference to the required entity without needing to
            // - know its position in the output array
            // If we don't assign by reference here the, additions to $question will create a new array
            // - rather than modifying the original array
            $question = &$questionLookup[$questionModel->qid];

            $question['l10ns'] = $this->transformerQuestionL10ns->transformAll(
                $questionModel->questionl10ns,
                $options
            );

            $question['attributes'] = $this->transformerQuestionAttribute->transformAll(
                $this->questionService->getQuestionAttributes(
                    $questionModel->qid
                ),
                $options
            );

            if ($questionModel->subquestions) {
                $question['subquestions'] = $this->transformerQuestion->transformAll(
                    $questionModel->subquestions,
                    $options
                );

                $subQuestionLookup = $this->createCollectionLookup(
                    'qid',
                    $question['subquestions']
                );
                $this->transformQuestions(
                    $subQuestionLookup,
                    $questionModel->subquestions,
                    $options
                );
            }

            $question['answers'] = $this->transformerAnswer->transformAll(
                $questionModel->answers,
                $options
            );

            $answerLookup = $this->createCollectionLookup(
                'aid',
                $question['answers']
            );

            $this->transformAnswersL10n(
                $answerLookup,
                $questionModel->answers,
                $options
            );
        }
    }

    /**
     * Adds the language specific data of answer_l10ns to the answers array
     * @param array $answerLookup
     * @param array $answers
     * @param ?array $options
     * @return void
     */
    private function transformAnswersL10n($answerLookup, $answers, $options = [])
    {
        foreach ($answers as $answerModel) {
            $answer = &$answerLookup[$answerModel->aid];

            $answer['l10ns'] = $this->transformerAnswerL10ns->transformAll(
                $answerModel->answerl10ns,
                $options
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

    /**
     * Some survey settings are inherited from the survey group, so we need to
     * replace the inherited info ("I") with the real values.
     * This is a temporary solution until we display the inherit option
     * in the new UI.
     *
     * @param Survey $survey
     * @return Survey $survey
     */
    private function setInheritedBetaOptions(Survey $survey)
    {
        $affectedSettings = [
            'allowprev',
            'showprogress',
            'autoredirect',
            'showwelcome',
            'showxquestions',
            'anonymized',
            'alloweditaftercompletion',
            'format',
            'template'
        ];
        foreach ($affectedSettings as $setting) {
            if (
                isset($survey->$setting)
                && ($survey->$setting === 'I' || $survey->$setting === 'inherit')
            ) {
                if (isset($survey->oOptions->$setting)) {
                    $survey->$setting = $survey->oOptions->$setting;
                }
            }
        }
        return $survey;
    }
}
