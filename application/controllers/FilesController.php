<?php
namespace ls\controllers;
use \Yii;
use CFileHelper, CClientScript, CHtml;
use \elFinder, elFinderConnector;
class FilesController extends Controller
{
    public function actionManage($cmd = null, $dialog = false)
    {
        foreach (App()->log->routes as $route) {
            if ($route instanceof \CWebLogRoute) {
                $route->enabled = false;
            }
        }
        $dir = __DIR__ . '/../vendor/studio-42/elfinder/';
        $url = App()->assetManager->publish($dir);

        if (!isset($cmd)) {
            $clientOptions = [
                'url' => App()->createUrl('files/manage'),
                'lang' => 'en',
                'resizable' => false,
                'loadTmbs' => 100,
                'onlyMimes' =>  ["image"]


            ];
            if ($dialog) {
                $this->layout = 'bare';
                $clientOptions['getFileCallback'] = new \CJavaScriptExpression('function(file) {
                debugger;
                    top.tinymce.activeEditor.windowManager.getParams().callback(file.url, {
                        alt: file.name,
                        width: file.width,
                        height: file.height

                    });
                    top.tinymce.activeEditor.windowManager.close();

                }');
            }
            $dir = __DIR__ . '/../vendor/studio-42/elfinder/';
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
            App()->clientScript->registerScript('init', "$('#elfinder').elfinder(" . \CJavaScript::encode($clientOptions) . ");",
                CClientScript::POS_READY);
            $this->renderText(CHtml::tag('div', [
                'class' => 'col-md-12'
            ], CHtml::tag('div', [
                'id' => 'elfinder',
                'style' => 'height: 200px;'
            ])));
        } else {

            $finder = new elFinder([
                'debug' => true,
                'roots' => $this->getRoots($url)
//                [
//                    [
//                        'driver' => 'LocalFileSystem',
//                        'tmbPath' => '/tmp',
//                        'tmbURL' => '/tmp',
//                        'path'   => Yii::getPathOfAlias('webroot'),
//                        'URL' => '/files'
//                    ],
//                    [
//                        'driver' => 'LocalFileSystem',
//                        'tmbPath' => App()->assetManager->basePath . '/thumbs',
//                        'tmbURL' => App()->assetManager->baseUrl . '/thumbs',
//                        'URL' => '/protected/messages',
//                        'path'   => Yii::getPathOfAlias('application') . '/messages',
//
//                    ],

//                ],
            ]);

            $connector = new elFinderConnector($finder, true);
            $connector->run();
        }
    }


    protected function getRoots($url)
    {
        $result = [];
        // Get accessible surveys.
        foreach (\Survey::model()->accessible()->findAll() as $survey) {
            if (!is_dir(Yii::getPathOfAlias('webroot') . "/upload/surveys/{$survey->primaryKey}")) {
                mkdir(Yii::getPathOfAlias('webroot') . "/upload/surveys/{$survey->primaryKey}");
            }
            $result[] = [
                'alias' => "Survey ({$survey->primaryKey})",
                'driver' => 'LocalFileSystem',
//                'tmbPath' => App()->assetManager->basePath . "$url/thumbs",
//                'tmbURL' => $url . '/thumbs',
                'path'   => Yii::getPathOfAlias('webroot') . "/upload/surveys/{$survey->primaryKey}",
                'URL' => App()->baseUrl . "/upload/surveys/{$survey->primaryKey}",
                'icon' => "$url/img/volume_icon_local.png",
                'accessControl' => 'access',
                'attributes' => array(
                    array(// hide anything else
                        'pattern' => '!^/\..*$!',
                        'hidden' => true
                    )
                )
            ];
        }

        return $result;
    }
}