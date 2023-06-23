<?php


namespace ls\tests;


use LimeSurvey\Datavalueobjects\SimpleSurveyValues;
use LimeSurvey\Models\Services\CreateSurvey;
use PHPUnit\Framework\TestCase;



class BaseModelTestCase extends TestCase
{
    /** @var string $modelClassName */
    protected $modelClassName;

    public function testRelationsUseExistingClasses()
    {
        /** @var \CActiveRecord $model */
        $model = new $this->modelClassName();
        $relations = $model->relations();
        if (empty($relations)) {
            // make a dummy test not to be marked as "risky"
            $this->assertTrue(true);
            return;
        }
        foreach ($relations as $relation) {
            $this->assertTrue(class_exists($relation[1]), "Relation class {$relation[1]} does not exist");
        }
    }

}
