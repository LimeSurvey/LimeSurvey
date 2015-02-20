<?php
namespace ls\controllers;
use \Yii;
class DevController extends CController {
    
    public function actionIndex() {
        $commandPath = Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'commands';
        $runner = new CConsoleCommandRunner();
        $runner->addCommands($commandPath);
        $commandPath = Yii::getFrameworkPath() . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'commands';
        $runner->addCommands($commandPath);
        $args = array('web', 'migrate', '--interactive=0');
        ob_start();
        $result = $runner->run($args);
        echo '<pre>' . htmlentities(ob_get_clean(), null, Yii::app()->charset);
        echo "Exit code: $result";
    }
    
    public function actionModule() {
        $id = array_keys(App()->modules)[0];
//        die(App()->createUrl("$id/dashboard"));
    }
}