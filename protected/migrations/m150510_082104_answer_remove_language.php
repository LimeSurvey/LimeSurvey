<?php
namespace ls\migrations;
use \CDbMigration;

class m150510_082104_answer_remove_language extends CDbMigration
{
	/**
     * Describe the migration in this PHPDOC.
     * Implement it below.
     * @return boolean True if migration was a success.
     */
    public function safeUp()
	{
        // We are removing languages from the answer table and moving it to the translation table.
        $table = \ls\models\Answer::model()->tableName();
        $this->dropPrimaryKey($table . '_pkey', $table);
        $this->addColumn(\ls\models\Answer::model()->tableName(), 'id', 'pk');
        $this->renameColumn($table, 'qid', 'question_id');
        $this->dbConnection->schema->getTable($table, true);
        $answers = \ls\models\Answer::model()->findAll();
        $deleted = $created = 0;
        /** @var \ls\models\Answer $answer */
        foreach ($answers as $answer) {
            $behaviorConfig = $answer->behaviors()['translatable'];
            // Get the base language
            $baseLanguage = isset($behaviorConfig['translatable']['baseLanguage']) ? $behaviorConfig['translatable']['baseLanguage'] : 'en';
            if ($baseLanguage instanceof \Closure) {
                $baseLanguage = $baseLanguage($answer);
            }
            if ($baseLanguage != $answer->language) {
                // Create translation if not base language.
                $translation = new \ls\models\Translation();
                $translation->language = $answer->language;
                $translation->model = 'ls\models\Answer'; // We have Single Table Inheritance so we use the base class.
                $translation->model_id = $answer->id;
                foreach ($answer->translatableAttributes as $attribute) {
                    $translation->$attribute = $answer->$attribute;
                }
                try {
                    if ($translation->save()) {
                        $created++;
                    }
                } catch (\Exception $e) {
                    $translation->setIsNewRecord(false);
                    if ($translation->save()) {
                        $created++;
                    }
                }
                if ($answer->delete()) {
                    $deleted++;
                }
            }
        }
        if ($deleted !== $created) {
            throw new \Exception("Upgrade did not create expected number of translations. Aborted to prevent data loss.");

        }
        // Create index for unique answer code (per question).
        $this->createIndex('unique_question_code', $table, ['question_id', 'code'], true);
        // Now we can drop the language column.
        $this->dropColumn($table, 'language');




	}
    
    /**
     * @return boolean True if migration was a success.
     */

	public function safeDown()
	{
		echo "m150510_082104_answer_remove_language does not support migration down.\\n";
		return true;
        
	}

}