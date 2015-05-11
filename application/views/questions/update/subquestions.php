<?php
/** @var \ls\models\questions\ChoiceQuestion $question */
if (is_subclass_of($question, \ls\models\questions\ChoiceQuestion::class) && $question->hasSubQuestions) {
    echo '@todo';
//    var_dump($question->answers);

}