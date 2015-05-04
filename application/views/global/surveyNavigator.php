<nav>
<?php 
echo TbHtml::link(TbHtml::tag('h3', [], "{$this->survey->localizedTitle} ({$this->survey->sid})"), ['surveys/update', 'id' => $this->survey->sid]);
$items = [];
foreach ($this->survey->groups as $group) {
    $items[] = [
        'label' => "{$group->title} - {$group->group_name}",
        'url' => ['groups/view', 'id' => $group->gid],
        'class' => 'group',
        'active' => isset($this->group) && $this->group->gid === $group->gid && !isset($this->question)
    ];
    foreach ($group->questions as $question) {
        $items[] = [
        'label' => $question->displayLabel,
        'url' => ['questions/update', 'id' => $question->qid],
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