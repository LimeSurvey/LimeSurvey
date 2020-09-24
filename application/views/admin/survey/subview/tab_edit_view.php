<?php

/**
 * @var $aTabTitles
 * @var $aTabContents
 * @var $has_permissions
 * @var $surveyid
 * @var $surveyls_language
 */

if (isset($data)) {
    extract($data);
}

if (isset($scripts)) {
    echo $scripts;

    $iSurveyID = App()->request->getParam('surveyid');
    App()->session['FileManagerContent'] = "edit:survey:{$iSurveyID}";
    initKcfinder();
}

$cs = Yii::app()->getClientScript();
$cs->registerPackage('bootstrap-select2');

$adminlang = Yii::app()->session['adminlang'];

?>