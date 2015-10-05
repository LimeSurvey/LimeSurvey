<?php
// Build some data for the replacements.

$data = ['surveyId' => $surveyId];
//Present the clear all page using clearall.pstpl template
echo \ls\helpers\Replacements::templatereplace(file_get_contents($templatePath . '/clearall.pstpl'), [], $data, null, $session);

