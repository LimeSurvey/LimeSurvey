<!--<nav>-->
<?php 
$items = [];
/** @var QuestionGroup $group */
foreach ($survey->groups as $group) {
    $item = [
        'text' => $group->title,
        'url' => ['groups/view', 'id' => $group->id],
        'class' => 'group',
        'icon' => null,
        'active' => isset($this->menus['group']) && $this->menus['group']->id === $group->id && !isset($this->menus['question']),
        'tags' => [(string) count($group->questions)]
    ];
    foreach ($group->questions as $question) {
        if ($question->hasSubQuestions && $question->hasAnswers) {
            $icon = 'th';
        } elseif ($question->hasSubQuestions) {
            $icon = 'th-list';
        } elseif ($question->hasAnswers) {
            $icon = 'tasks';
        } elseif (in_array($question->type, [Question::TYPE_EQUATION, Question::TYPE_DISPLAY])) {
            $icon = 'eye-open';
        } else {
            $icon = 'option-horizontal';
        }

        $item['children'][] = [
            'text' => \Cake\Utility\Text::truncate($question->displayLabel, 30),
            'title' => $question->getDisplayLabel(),
            'icon' => $icon,
            'url' => ['questions/update', 'id' => $question->qid],
            'class' => 'question',
            'active' => isset($this->menus['question']) && $this->menus['question']->qid === $question->qid,
            'style' => 'word-break: break-all;'
    ];
    }
    $items[] = $item;

}

echo TbHtml::tag('h4', [], (TbHtml::link("{$survey->localizedTitle} ({$survey->sid})", ['surveys/update', 'id' => $survey->sid])));
$this->widget(\SamIT\Yii1\Widgets\BootstrapTreeView::class, [
    'data' => $items,
    'nodeIcon' => null,
]);
?>
<!--</nav>-->