<?php
echo get_class($question);
$this->widget(WhDetailView::class, [
    'data' => $question
]);
