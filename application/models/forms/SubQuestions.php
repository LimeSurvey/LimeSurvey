<?php
namespace ls\models\forms;


/**
 * Class SubQuestions
 * This model handles validation of subquestions.
 */
class SubQuestions extends \CFormModel
{
    public $titles = [];
    /**
     * @var \Question
     */
    public $question;
    public $questions;
    public function rules() {
        return [
            ['titles', 'validateUnique', 'clientValidate' => 'debugger;']
        ];
    }


    public function getInstance() {
        return new \Question('insertsub');
    }
    public function __construct(\Question $question, $scenario = '')
    {
        parent::__construct($scenario);
        $this->question = $question;
        foreach($this->question->subQuestions as $subQuestion) {
            $this->titles[] = $subQuestion->title;
            $question = [];
            foreach ($question->survey->languages as $language) {
                $subQuestion->language = $language;
                $question[$language] = $subQuestion->question;
            }
            $this->questions[] = $question;
        }
        if (empty($this->titles)) {
            $this->titles[] = "SQ001";
        }
    }


    public function validateUnique($attribute, $params) {
        $length = count($this->$attribute);
        for ($i = 0; $i < $length - 1; $i++) {
            for ($j = $i + 1; $j < $length; $j++) {
                   if (strcasecmp($this->$attribute[$i], $this->$attribute[$j]) == 0) {
                       $this->addError($attribute, "{attribute} Must contain unique values only.");
                       return;
                   }
            }
        }
    }
}