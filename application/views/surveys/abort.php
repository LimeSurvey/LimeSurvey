<?php
doHeader();
// Build some data for the replacements.

$data = [];
if (isset($surveyId)) {
    $data['thissurvey'] = [
        'sid' => $surveyId
    ];
}
echo templatereplace(file_get_contents($templatePath.'/startpage.pstpl'), [], $data);
//Present the clear all page using clearall.pstpl template
echo templatereplace(file_get_contents($templatePath.'/clearall.pstpl'), [], $data);

echo templatereplace(file_get_contents($templatePath.'/endpage.pstpl'), [], $data);
doFooter();