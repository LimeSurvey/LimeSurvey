<nav>
<?php 
echo TbHtml::tag('h3', [],"Survey navigator");
$items = [];
foreach ($this->survey->groups as $group) {
    $items[] = [
        'label' => "{$group->title} - {$group->group_name}",
        'url' => ['groups/view', 'id' => $group->gid],
        'class' => 'group',
        'active' => $this->group->gid === $group->gid && !isset($this->question)
    ];
    foreach ($group->questions as $question) {
        $items[] = [
        'label' => "{$question->title} - {$question->question}",
        'url' => ['questions/view', 'id' => $question->qid],
        'class' => 'question',
        'active' => isset($this->question) && $this->question->qid === $question->qid
    ];
    }
    $items[] = TbHtml::menuDivider();
}
$this->widget('TbNav', [
    'type' => TbHtml::NAV_TYPE_PILLS,
    'stacked' => true,
    'items' => $items
]);
?>
</nav>