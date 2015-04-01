<?php
    App()->clientScript->registerPackage('jqueryui');
    echo getHeader();
    /** @var Survey $survey */
//    echo $survey->localizedWelcomeText;
    $sTemplatePath = __DIR__ . '/../../../templates/default/';
    App()->setConfig('surveyID', $survey->sid);
    $navigator = TbHtml::beginForm(['surveys/run']);
    $navigator .= TbHtml::submitButton(gT('Start survey'));
    $navigator .= TbHtml::hiddenField('surveyId', $survey->sid);
    $navigator .= TbHtml::hiddenField('responseId', $id);
    $navigator .= TbHtml::endForm();

    $redata = [
//        'TEMPLATEURL' => '/templates/basic/',
        'totalquestions' => count($survey->questions),
        'navigator' => $navigator
    ];
    echo templatereplace(file_get_contents($sTemplatePath."startpage.pstpl"), array(), $redata, 'SubmitStartpageI', false, NULL, array(), true );
    echo templatereplace(file_get_contents($sTemplatePath."welcome.pstpl"), array(), $redata) . "\n";
    if ($survey->bool_anonymized) {
        echo templatereplace(file_get_contents($sTemplatePath."privacy.pstpl"), array(), $redata) . "\n";
    }

    echo "\n\n<!-- PRESENT THE NAVIGATOR -->\n";
    echo templatereplace(file_get_contents($sTemplatePath."navigator.pstpl"), array(), $redata);
    echo "\n";
    echo templatereplace(file_get_contents($sTemplatePath."endpage.pstpl"), array(), $redata, 'SubmitStartpageI', false, NULL, array(), true );