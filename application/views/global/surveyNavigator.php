<nav>
<?php 
echo TbHtml::link(TbHtml::tag('h3', [], "{$survey->localizedTitle} ({$survey->sid})"), ['surveys/update', 'id' => $survey->sid]);
$items = [];
/** @var QuestionGroup $group */
foreach ($survey->groups as $group) {
    $items[] = [
        'label' => $group->title,
        'url' => ['groups/view', 'id' => $group->id],
        'class' => 'group',
        'active' => isset($this->group) && $this->group->id === $group->id && !isset($this->question)
    ];
    foreach ($group->questions as $question) {
        $title = '';
        if ($question->hasSubQuestions && $question->hasAnswers) {
            $title .= TbHtml::icon('th');
        } elseif ($question->hasSubQuestions) {
            $title .= TbHtml::icon('th-list');
        } elseif ($question->hasAnswers) {
            $title .= TbHtml::icon('tasks');
        } else {
            $title .= TbHtml::icon('pencil');
        }
        $title .= ' ' . \Cake\Utility\Text::truncate($question->displayLabel, 30);

        $items[] = [
        'label' => $title,
        'title' => $question->getDisplayLabel(),
        'url' => ['questions/update', 'id' => $question->qid],
        'class' => 'question',
        'active' => isset($this->question) && $this->question->qid === $question->qid,
        'style' => 'word-break: break-all;'
    ];
    }
    $items[] = TbHtml::menuDivider();
}
$this->widget(TbNav::class, [
    'type' => TbHtml::NAV_TYPE_PILLS,
    'stacked' => true,
    'items' => $items,
    'encodeLabel' => false

]);
?>
</nav>