<?php
namespace ls\migrations;
use \CDbMigration;
class m150511_124830_group_remove_language extends CDbMigration
{
	/**
     * Describe the migration in this PHPDOC.
     * Implement it below.
     * @return boolean True if migration was a success.
     */
    public function safeUp()
	{
        // We are removing languages from the answer table and moving it to the translation table.
        $table = \QuestionGroup::model()->tableName();
//        $this->renameColumn($table, 'gid', 'id');
        $this->dbConnection->schema->getTable($table, true);
        $groups = \QuestionGroup::model()->findAll();
        $deleted = $created = 0;
        /** @var \QuestionGroup $group */
        foreach ($groups as $group) {
            $behaviorConfig = $group->behaviors()['translatable'];
            // Get the base language
            $baseLanguage = isset($behaviorConfig['translatable']['baseLanguage']) ? $behaviorConfig['translatable']['baseLanguage'] : 'en';
            if ($baseLanguage instanceof \Closure) {
                $baseLanguage = $baseLanguage($group);
            }
            if ($baseLanguage != $group->language) {
                // Create translation if not base language.
                $translation = new \Translation();
                $translation->language = $group->language;
                $translation->model = 'Group'; // We have Single Table Inheritance so we use the base class.
                $translation->model_id = $group->id;
                foreach ($group->translatableAttributes as $attribute) {
                    $translation->$attribute = $group->$attribute;
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
                if ($group->delete()) {
                    $deleted++;
                }
            }
        }
        if ($deleted !== $created) {
            throw new \Exception("Upgrade did not create expected number of translations. Aborted to prevent data loss.");

        }
        // Create index for unique answer code (per question).
        $this->dropColumn($table, 'language');
    }
    
    /**
     * @return boolean True if migration was a success.
     */

	public function safeDown()
	{
		echo "m150511_124830_group_remove_language does not support migration down.\\n";
		return false;
        
	}

}