<?php


namespace LimeSurvey\Models\Services;

use LimeSurvey\Datavalueobjects\CopyQuestionValues;

/**
 * Class CopyQuestion
 *
 * This class is responsible for the copy question process.
 *
 * @package LimeSurvey\Models\Services
 */
class CopyQuestion
{

    /**
     * @var CopyQuestionValues values needed to copy a question
     */
    private $copyQuestionValues;

    /**
     * @var \Question the new question
     */
    private $newQuestion;

    /**
     * CopyQuestion constructor.
     *
     * @param CopyQuestionValues $copyQuestionValues
     */
    public function __construct($copyQuestionValues){
        $this->copyQuestionValues = $copyQuestionValues;
        $this->newQuestion = null;
    }

    /**
     * @param array $copyOptions has the following boolean elements ''
     */
    public function copyQuestion($copyOptions){

        if($this->createNewCopiedQuestion()){
            //copy languages if necessecary
            $successCopiedQuestionLanguages = $this->copyQuestionLanguages();

            //copy subquestions
            $iscopySubquestions = (int)Yii::app()->request->getParam('copysubquestions');
            if($iscopySubquestions !== null && $iscopySubquestions===1){
                $subquestions = Question::model()->findAllByAttributes(['parent_qid'=> $questionIdToCopy]);
                foreach ($subquestions as $subquestion){
                    $copiedSubquestion = new Question();
                    $copiedSubquestion->attributes = $subquestion->attributes;
                    $copiedSubquestion->sid = null; //new question id needed ...
                    $copiedSubquestion->save();
                }
            }
            //copy answer options
            $iscopyAnswerOptions = (int)Yii::app()->request->getParam('copyanswers');
            if($iscopyAnswerOptions){
                $answerOptions = Answer::model()->findAllByAttributes(['qid' => $questionIdToCopy]);
                foreach ($answerOptions as $answerOption){
                    $copiedAnswerOption = new Answer();
                    $copiedAnswerOption->attributes = $answerOption->attributes;
                    $copiedAnswerOption->aid = null;
                    if($copiedAnswerOption->save()){
                        //copy the languages
                        foreach ($answerOption->answerl10ns as $answerLanguage) {
                            $copiedAnswerOptionLanguage = new AnswerL10n();
                            $copiedAnswerOptionLanguage-> attributes = $answerLanguage->attributes;
                            $copiedAnswerOptionLanguage->id = null;
                            $copiedAnswerOptionLanguage->save();
                        }
                    }
                }
            }
            //copy default answers
            $iscopyDefaultAnswer = (int)Yii::app()->request->getParam('copydefaultanswers');
            if($iscopyDefaultAnswer){
                $defaultAnswers = DefaultValue::model()->findAllByAttributes(['qid' => $questionIdToCopy]);
                foreach ($defaultAnswers as $defaultAnswer){
                    $copiedDefaultAnswer = new DefaultValue();
                    $copiedDefaultAnswer->attributes = $defaultAnswer->attributes;
                    $copiedDefaultAnswer->dvid = null;
                    if($copiedDefaultAnswer->save()){
                        //copy languages if needed
                        foreach ($copiedDefaultAnswer->defaultvalueL10ns as $defaultAnswerL10n){
                            $copieDefaultAnswerLanguage = new DefaultValueL10n();
                            $copieDefaultAnswerLanguage->attributes = $defaultAnswerL10n->attributes;
                            $copieDefaultAnswerLanguage->id = null;
                            $copieDefaultAnswerLanguage->save();
                        }
                    }
                }
            }
    }
}


    /**
     * Creates a new question copying the values from questionToCopy
     *
     * @param string $questionCode
     * @param int $groupId
     * @param \Question $questionToCopy the question that should be copied
     *
     * @return bool true if question could be saved, false otherwise
     */
    public function createNewCopiedQuestion($questionCode, $groupId, $questionToCopy)
    {
        $this->newQuestion = new \Question();
        $this->newQuestion->attributes = $questionToCopy->attributes;
        $this->newQuestion->title = $questionCode;
        $this->newQuestion->gid = $groupId;
        $this->newQuestion->qid = null;

        return $this->newQuestion->save();
    }

    private function copyQuestionLanguages(){
        $i10N = [];
        if($this->copyQuestionValues->getOSurvey() !== null) {
            $allLanguagesAreCopied = true;
            foreach ($this->copyQuestionValues->getOSurvey()->questionl10ns as $sLanguage) {
                $i10N[$sLanguage] = new \QuestionL10n();

                $i10N[$sLanguage]->setAttributes(
                    [
                        'qid' => $this->newQuestion->qid,
                        'language' => $sLanguage,
                        'question' => '',
                        'help' => '',
                    ],
                    false
                );
                $i10N[$sLanguage]->save();
            }
        }else{
            return false;
        }
    }

    /**
     * Returns the new created question or null if question was not copied.
     *
     * @return \Question|null
     */
    public function getNewCopiedQuestion(){
        return $this->newQuestion;
    }
}