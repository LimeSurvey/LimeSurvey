<?php

return array_merge(
    include __DIR__ . '/v1/survey.php',
    include __DIR__ . '/v1/question-group.php',
    include __DIR__ . '/v1/question.php',
    include __DIR__ . '/v1/session.php',
    include __DIR__ . '/v1/site-settings.php'
);
