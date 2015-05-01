<?php
echo get_class($question);
var_dump($question->translations);
$this->widget(WhDetailView::class, [
    'data' => $question
]);
