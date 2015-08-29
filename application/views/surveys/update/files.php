<?php
$clientOptions = [
    'url' => App()->createUrl('files/browse', ['surveyId' => $survey->primaryKey]),
    'lang' => App()->language,
//            'resizable' => !$dialog,
    'loadTmbs' => 100,
];
$dir = App()->basePath . '/vendor/studio-42/elfinder/';
$url = App()->assetManager->publish($dir);
App()->clientScript->registerScriptFile($url . '/js/elFinder.js');
App()->clientScript->registerPackage('jqueryui');
App()->clientScript->registerCssFile($url . '/jquery/ui-themes/smoothness/jquery-ui-1.10.1.custom.min.css');

foreach (CFileHelper::findFiles($dir . '/js', [
    'fileTypes' => ['js'],
    'absolutePaths' => false
]) as $file) {
    App()->clientScript->registerScriptFile($url . '/js/' . $file);
}

foreach (CFileHelper::findFiles($dir . '/css', [
    'fileTypes' => ['css'],
    'absolutePaths' => false
]) as $file) {
    App()->clientScript->registerCssFile($url . '/css/' . $file);
}
App()->clientScript->registerScript('init',
    "$('#elfinder').elfinder(" . \CJavaScript::encode($clientOptions) . ");",
    CClientScript::POS_READY);
echo CHtml::tag('div', [
    'class' => 'col-md-12'
], CHtml::tag('div', [
    'id' => 'elfinder',
    'style' => 'min-height: 200px; padding-bottom: 20px;'

]));