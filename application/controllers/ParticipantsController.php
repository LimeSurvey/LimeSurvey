<?php
namespace ls\controllers;
use Participant;

class ParticipantsController extends Controller
{
    public function accessRules()
    {
        return array_merge([
            ['allow', 'roles' => ['participantpanel']]
        ],
        parent::accessRules());

    }

    public function actionIndex() {
        $dataProvider = new \CActiveDataProvider(Participant::model()->accessibleTo(App()->user->id)->with('surveyCount'));
        $this->menus['participant'] = [];
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


    public function actionImport() {
        $start = microtime(true);
        $participant = new Participant();
        $regularFields = $participant->safeAttributeNames;
        $tableName = $participant->tableName();
        $attributeTableName = \ParticipantAttribute::model()->tableName();
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

        $fields = array_flip($regularFields);
        if (isset(App()->request->psr7->getParsedBody()['items'])) {
            if (isset(App()->request->psr7->getParsedBody()['querySize'])) {
                $batchInserter->batchSize = App()->request->psr7->getParsedBody()['querySize'];
            }
            \Yii::beginProfile('import');
            $count = 0;
            // Custom validation for better performance.
//            $validators = $participant->getValidators();
            $initialAttributes = $participant->getAttributes();
            array_map(function($row) use ($batchInserter, $attributeTableName, $participant, $initialAttributes) {
                \Yii::beginProfile('row');
              $participant->setAttributes($initialAttributes, false);
//                 Manual for better performance.
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
            }, App()->request->psr7->getParsedBody()['items']);
            unset($batchInserter);
            \Yii::endProfile('import');

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
            header('Content-Type: application/json');
            echo json_encode([
                'memory' => memory_get_peak_usage() / $return_bytes(ini_get('memory_limit')),
                'time' => microtime(true) - $start
            ]);


        } else {
            $this->render('import');
        }
    }

}