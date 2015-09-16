<?php
$tree = [];
/** @var Survey $survey */
foreach($survey->questions as $question) {
    // Check subquestions.
    if ($question->hasSubQuestions) {
        $subQuestions = [];
        /** @var \ls\interfaces\iSubQuestion $subQuestion */
        foreach($question->getSubQuestions() as $subQuestion) {
            if ($question->hasAnswers) {
                $children = array_values(array_map(function (\ls\interfaces\iAnswer $answer) use ($subQuestion) {
                    return [
                        'text' => $answer->getLabel(),
                        'icon' => 'asterisk',
                        'tags' => ["{$subQuestion->getCode()} == \"{$answer->getCode()}\""]
                    ];

                }, $question->getAnswers()));
            }

            $node = [
                'text' => $subQuestion->getLabel(),
                'children' => $question->hasAnswers ? $children : null,
                'icon' => !$question->hasAnswers ? 'pencil' : 'th-list'
            ];

            $subQuestions[] = $node;
        }
        $tree[] = [
            'text' => $question->displayLabel,
            'children' => $subQuestions
        ];
    } elseif ($question->hasAnswers) {
        $children = array_values(array_map(function (\ls\interfaces\iAnswer $answer) use ($question) {
            return [
                'text' => $answer->getLabel(),
                'icon' => 'asterisk',
                'tags' => ["{$question->title} == \"{$answer->getCode()}\""]
            ];

        }, $question->getAnswers()));
        $tree[] = [
            'text' => $question->getDisplayLabel(),
            'children' => $children,

        ];

    }

    else {
        $tree[] = [
            'text' => $question->getDisplayLabel(),
            'icon' => 'pencil'
        ];

    }
}

//vdd($tree);
//
$this->widget(\SamIT\Yii1\Widgets\BootstrapTreeView::class, [
    'data' => [[
        'text' => 'Survey',
        'children' => $tree,
    ]],
    'enableLinks' => false
]);