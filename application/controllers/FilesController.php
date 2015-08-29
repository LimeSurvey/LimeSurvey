<?php
namespace ls\controllers;
use \Yii;
use CFileHelper, CClientScript, CHtml;
use \elFinder, elFinderConnector;
class FilesController extends Controller
{
    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * You may override this method to do last-minute preparation for the action.
     * @param CAction $action the action to be executed.
     * @return boolean whether the action should be executed.
     */
    protected function beforeAction($action)
    {
        $result = parent::beforeAction($action);

        foreach (App()->log->routes as $route) {
            if ($route instanceof \CWebLogRoute) {
                $route->enabled = false;
            }
        }
        return $result;
    }

    /**
     * Shows the file manager.
     * @param bool|false $dialog
     * @param null $surveyId
     */
    public function actionManage($dialog = false, $surveyId = null, $imageOnly = false)
    {
        $clientOptions = [
            'url' => App()->createUrl('files/browse', ['surveyId' => $surveyId]),
            'lang' => App()->language,
//            'resizable' => !$dialog,
            'loadTmbs' => 100,
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
        App()->clientScript->registerScript('init',
            "$('#elfinder').elfinder(" . \CJavaScript::encode($clientOptions) . ");",
            CClientScript::POS_READY);
        $this->renderText(CHtml::tag('div', [
            'class' => 'col-md-12'
        ], CHtml::tag('div', [
            'id' => 'elfinder',
            'style' => 'height: 200px;'
        ])));
    }

    /**
     * The browse function used by elFinder.
     */
    public function actionBrowse($surveyId = null) {

        $finder = new elFinder([
            'debug' => true,
            'roots' => is_numeric($surveyId) ? [$this->getRootForSurvey($surveyId)] : $this->getRoots()

        ]);

        $connector = new elFinderConnector($finder, false);
        $connector->run();
    }


    /**
     * @param int $surveyId
     * @return array
     * @todo Add permission check and set appropriate rights.
     */
    protected function getRootForSurvey($surveyId) {

        $relative = "/upload/surveys/$surveyId";
        $dir = Yii::getPathOfAlias('webroot') . $relative;
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        return [
            'alias' => "Survey ({$surveyId})",
            'driver' => 'LocalFileSystem',
            'path'   => $dir,
            'URL' => App()->baseUrl . $relative,
            'icon' => "{$this->getAssetsUrl()}/img/volume_icon_local.png",
            'accessControl' => 'access',
            'attributes' => [
                [
                    // hide anything else
                    'pattern' => '!^/\..*$!',
                    'hidden' => true
                ]
            ]
        ];
    }

    protected function getAssetsUrl() {
        $dir = __DIR__ . '/../vendor/studio-42/elfinder/';
        $url = App()->assetManager->getPublishedUrl($dir);
        return $url;
    }
    protected function getRoots()
    {

        $result = [];
        // Get accessible surveys.
        foreach (\Survey::model()->accessible()->findAll() as $survey) {
            $result[] = $this->getRootForSurvey($survey->primaryKey);
        }
//        vdd($result);
//        $result[] = [
//            'alias' => "All surveys",
//            'driver' => 'LocalFileSystem',
////            'path'   => Yii::getPathOfAlias('webroot'),
////            'URL' => '/files'
//            'path'   => Yii::getPathOfAlias('webroot') . "/upload/surveys",
//            'URL' => App()->baseUrl . "/upload/surveys",
//            'attributes' => [
//                [
//                    // hide anything else
//                    'pattern' => '!^/\..*$!',
//                    'hidden' => true
//                ]
//            ]
//        ];
//        vdd($result);
        return $result;
    }
}