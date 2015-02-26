<?php
namespace ls\controllers;
use \Yii;
class DevController extends \CController {
    
    public function actionIndex() {
        return 'noo';
        $timings = [];
        for($r = 0; $r < 10000; $r++) {
            for ($l = 1; $l < 5; $l = $l + 5) {
                
                // Create 1000 random arrays.
                $input = [];
                for ($i = 0; $i < 1000; $i++) {
                    $row = [];
                    for ($f = 0; $f <= $l; $f++) {
                        $row[$f] = md5(mt_rand(0, 1000) . microtime());
                    }
                    $input[$i] = $row;
                }

                $json = array_map('json_encode', $input);


                $start = microtime(true);
                $jsonData = [];
                foreach($json as $i => $jsonString) {
                    // We test json decoding the data.
                    // We also create an object to make it fairer since yii woudl create one object for the model as well.
                    $c = new \stdClass();
                    $c->value = $jsonString;
                    $c->key = $i;
                    $jsonData[] = json_decode($jsonString, true);
                }
                $timings['json'][$l][] = microtime(true) - $start;

                $start = microtime(true);
                $objData = [];
                foreach($input as $i => $arrayData) {
                    foreach($arrayData as $key => $value) {
                        /*
                         * We test instantiating an object, in reality this will be slower because:
                         * - Yii uses more complicated objects
                         * - Yii uses __get and __set to store the values in an array.
                         */
                        $c = new \stdClass();
                        $c->value = $value;
                        $c->key = $key;
                        $objData[] = $c;
                    }

                }

                $timings['obj'][$l][] = microtime(true) - $start;
                
            }

        }
        echo "<h1>Executed $r repetitions:</h1>";
        echo '<table>';
        echo '<tr>';
        echo \CHtml::tag('th', [], 'Type');
        echo \CHtml::tag('th', [], 'Number of attributes');
        echo \CHtml::tag('th', [], 'Average');
        echo \CHtml::tag('th', [], 'Maximum');
        echo \CHtml::tag('th', [], 'Minimum');
        echo '</tr>';
        foreach($timings as $type => $details) {
            foreach ($details as $size => $durations) {
                echo '<tr>';
                echo \CHtml::tag('td', [], $type);
                echo \CHtml::tag('td', [], $size);
                echo \CHtml::tag('td', [], number_format(array_sum($durations) / count($durations), 4));
                echo \CHtml::tag('td', [], number_format(max($durations), 4));
                echo \CHtml::tag('td', [], number_format(min($durations), 4));
                echo '</tr>';
            }
        }
        echo '</table>';
        
        
    }
    public function actionMigrateTest() {
        var_dump(App()->migrationManager->migrationHistory);
        $migrations  = App()->migrationManager->newMigrations;
        if (!empty($migrations)) {
            App()->migrationManager->migrateUp($migrations[0]);
        }
        
        
        return;
        $commandPath = Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'commands';
        $runner = new \CConsoleCommandRunner();
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