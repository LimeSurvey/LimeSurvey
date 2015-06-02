<?php
namespace ls\controllers;
use ls\models\forms\ParticipantDatabaseSettings;
use ls\models\forms\Settings;
use Participant;

class ParticipantsController extends Controller
{
    public $menus = [
        'participant' => []
    ];
    public function accessRules()
    {
        return array_merge([
            ['allow', 'roles' => ['participantpanel']]
        ],
        parent::accessRules());

    }

    public function actionIndex() {
        $dataProvider = new \CActiveDataProvider(Participant::model()->accessibleTo(App()->user->id)->with('surveyCount'));
        $this->render('index', ['dataProvider' => $dataProvider]);
    }
    public function actionSummary() {
        $data = array(
            'totalrecords' => false && App()->user->checkAccess('superadmin') ? Participant::model()->count() : Participant::model()->accessibleTo(App()->user->id)->count(),
            'owned' => Participant::model()->ownedBy(App()->user->id)->count(),
            'shared' => Participant::model()->ownedBy(App()->user->id)->count(),
//            'attributes' => \ParticipantAttributeName::model()->findAll(),
            'attributecount' => \ParticipantAttributeName::model()->count(),
            'blacklisted' => Participant::model()->ownedBy(App()->user->id)->blacklisted()->count()
        );
        // loads the participant panel and summary view
        $this->render('summary', ['data' => $data]);
    }

    public function actionAttributes($id) {
        $searchModel = \ParticipantAttribute::model();
        $searchModel->dbCriteria->addColumnCondition(['participant_id' => $id]);
        $dataProvider = new \CActiveDataProvider($searchModel);
        if (App()->request->isAjaxRequest) {
            $this->layout = 'bare';
            $this->render('attributes', ['dataProvider'=> $dataProvider]);
        } else {
            $this->render('attributes', ['dataProvider'=> $dataProvider]);
        }
    }


    public function actionImport()
    {
        $this->render('import');
    }


    public function actionAjaxImport(array $items, $querySize = 1000)
    {
        header('Content-Type: application/json');
        // Set response code so on errors (max execution time, memory limit) we don't get http 200.
        http_response_code(501);
        set_time_limit(20);
        ini_set('memory_limit', '92M');
        $return_bytes = function($val) {
            $val = trim($val);
            $last = strtolower($val[strlen($val)-1]);
            switch($last) {
                // The 'G' modifier is available since PHP 5.1.0
                case 'g':
                    $val *= 1024;
                case 'm':
                    $val *= 1024;
                case 'k':
                    $val *= 1024;
            }

            return $val;
        };
        $start = App()->request->psr7->getServerParams()['REQUEST_TIME_FLOAT'];
        $memoryLimit = $return_bytes(ini_get('memory_limit'));





        $participant = new Participant();
        $regularFields = $participant->safeAttributeNames;
        $tableName = $participant->tableName();
        $attributeTableName = \ParticipantAttribute::model()->tableName();

        $fields = array_flip($regularFields);
        $batchInserter = new \Batch(function(array $batch, $category = null) {
            if (!empty($batch)) {
                \Yii::beginProfile('query');
                try {
                    $command = App()->db->commandBuilder->createMultipleInsertCommand($category, $batch);
                } catch (\Exception $e) {
                    echo "Error in query generation.";
                    var_dump($batch);
                }
                $command->execute();
                \Yii::endProfile('query');
            }
        }, 1000, $tableName);


        $initialAttributes = $participant->getAttributes();
        array_map(function($row) use ($batchInserter, $attributeTableName, $participant, $initialAttributes, $fields) {
            \Yii::beginProfile('row');
            $participant->setAttributes($initialAttributes, false);
            \Yii::beginProfile('alternative');
            foreach($row as $key => $value) {
                if (isset($fields[$key])) {
                    $participant->$key = $value;
                }
            }
            \Yii::endProfile('alternative');

            if ($participant->validate()) {
                $batchInserter->add($participant->getAttributes());
                $batchInserter->add($participant->getNewCustomAttributes(), $attributeTableName);
            } else {
                var_dump($participant->errors);

            }

            \Yii::endProfile('row');
        }, $items);
        \Yii::endProfile('import');



        http_response_code(200);
        echo json_encode([
            'memory' => memory_get_peak_usage() / $memoryLimit,
            'time' => (microtime(true) - $start) / ini_get('max_execution_time'),
            'queries' => $batchInserter->commitCount
        ]);


    }


    public function actionSettings() {
        $settings = new ParticipantDatabaseSettings();
        if (App()->request->isPutRequest) {
            $settings->setAttributes(App()->request->getParam(\CHtml::modelName($settings)));
            if ($settings->save()) {
                App()->user->setFlash('success', gT('Settings updated.'));
                $this->refresh();
            }
        }
        $this->render('settings', ['settings' => $settings]);
    }
}