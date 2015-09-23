<?php
namespace ls\migrations;
use \CDbMigration;
class m150501_084027_question_remove_language extends CDbMigration
{
	/**
     * Describe the migration in this PHPDOC.
     * Implement it below.
     * @return boolean True if migration was a success.
     */
    public function safeUp()
	{
        // We are removing languages from the question table and moving it to the translation table.
        $questions = \Question::model()->findAll();
        $deleted = $created = 0;
        /** @var \Question $question */
        foreach ($questions as $question) {
            $behaviorConfig = $question->behaviors()['translatable'];
            // Get the base language
            $baseLanguage = isset($behaviorConfig['translatable']['baseLanguage']) ? $behaviorConfig['translatable']['baseLanguage'] : 'en';

            if ($baseLanguage instanceof \Closure) {
                $baseLanguage = $baseLanguage($question);
            }
            if ($baseLanguage != $question->language) {
                // Create translation if not base language.
                $translation = new \Translation();
                $translation->language = $question->language;
                $translation->model = 'Question'; // We have Single Table Inheritance so we use the base class.
                $translation->model_id = $question->qid;
                foreach ($question->translatableAttributes as $attribute) {
                    $translation->$attribute = $question->$attribute;
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
                if ($question->delete()) {
                    $deleted++;
                }
            }
        }
        if ($deleted !== $created) {
            throw new \Exception("Upgrade did not create expected number of translations. Aborted to prevent data loss.");

        }

        // Now we can drop the language column.
        $this->dropColumn(\Question::model()->tableName(), 'language');




	}
    
    /**
     * @return boolean True if migration was a success.
     */

	public function safeDown()
	{
		echo "m150501_084027_question_remove_language does not support migration down.\\n";
		return true;
        
	}

}