<?php
namespace LimeSurvey\PluginManager;
use PluginSetting;

class DbStorage implements iPluginStorage
{

    
    public function __construct()
    {
    }
    /**
     * 
     * @param iPlugin $plugin
     * @param string $key Key for the setting; passing null will return all keys.
     * @param string $model Optional model name to which the data was attached.
     * @param int $id Optional id of the model instance to which the data was attached.
     * @param mixed $default Default value to return if key could not be found.
     * @param string $language Optional language identifier used for retrieving the setting.
     * @return mixed Returns the value from the database or null if not set.
     */
    public function get(iPlugin $plugin, $key = null, $model = null, $id = null, $default = null, $language = null)
    {
        $functionName = 'get'.ucfirst($model);
        if ($model == null || !method_exists($this, $functionName)) {
            return $this->getGeneric($plugin, $key, $model, $id, $default);
        } else {
            return $this->$functionName($plugin, $key, $model, $id, $default, $language);
        }
    }

    /**
     * 
     * @param iPlugin $plugin
     * @param string $key
     * @param string $model Optional model name to which the data was attached.
     * @param int $id Optional id of the model instance to which the data was attached.
     * @param mixed $default Default value to return if key could not be found.
     * @return mixed Returns the value from the database or null if not set.
     */
    protected function getGeneric(iPlugin $plugin, $key, $model, $id, $default)
    {
        $attributes = array(
            'plugin_id' => $plugin->getId(),
            'model'     => $model,
            'model_id'  => $id,
        );
        if ($key != null) {
            $attributes['key'] = $key;
        }
        
        $records = \PluginSetting::model()->findAllByAttributes($attributes);
        if (count($records) > 1) {
            foreach ($records as $record) {
                $result[] = json_decode($record->value, true);
            }
        } elseif (count($records) == 1) {
            $result = json_decode($records[0]->value, true);
        } else {
            $result = $default;
        }
        return $result;
    }

    /**
     * This function retrieves plugin data related to the Question model.
     * LS saves this data in a question_attributes EAV table; therefore
     * the 'Question' model is treated specially.
     * @param iPlugin $plugin
     * @param type $key
     * @param type $model
     * @param type $id
     * @param type $default
     * @param type $language
     */
    protected function getQuestion(iPlugin $plugin, $key, $model, $id, $default, $language)
    {
        $baseAttributes = array(
            'sid',
            'code',
            'qid',
            'gid',
            'sortorder',
            'relevance',
            'questiontype',
            'randomization'
        );
        
        // Some keys are stored in the actual question table not in the attributes table.
        if (in_array($key, $baseAttributes)) {
            $result = $this->getQuestionBase($id, $key, $default);
        } else {
            $attributes = array('qid' => $id);
            // If * is passed we retrieve all languages.
            if ($language != '*') {
                    $attributes['language'] = $language;
            }
            if ($key != null) {
                $attributes['attribute'] = $key;
            }

            $records = QuestionAttribute::model()->findAllByAttributes($attributes);
            if (count($records) > 0) {
                foreach ($records as $record) {
                    if ($record->serialized) {
                        $value = json_decode($record->value, true);
                    } else {
                        $value = $record->value;
                    }
                    if ($record->language != null && ($language == '*' || is_array($language))) {
                        $result[$record->language][] = $value;
                    } else {
                        $result[] = $value;
                    }
                }
                if ($language == '*' || is_array($language) && is_array($result)) {
                    foreach ($result as &$item) {
                        if (count($item) == 1) {
                            $item = $item[0];
                        }
                    }
                } elseif (count($result) == 1) {
                    $result = $result[0];
                }
            } else {
                $result = $default;
            }
        }
        return $result;
    }

    /**
     * Gets a field from the question table.
     * @param int $questionId
     * @param string $key
     * @param mixed $default Default value in case key could not be found.
     */
    protected function getQuestionBase($questionId, $key, $default)
    {
        $question = Question::model()->findByPk($questionId);
        if ($question != null && isset($question->attributes[$key])) {
            return $question->attributes[$key];
        }
        
        return $default;
    }
    
    /**
     * 
     * @param iPlugin $plugin
     * @param string $key
     * @param mixed data Default value to return if key could not be found.
     * @param string $model Optional model name to which the data was attached.
     * @param int $id Optional id of the model instance to which the data was attached.
     * @param string $language Optional language identifier used for storing the setting.
     * 
     * @return boolean
     */    
    public function set(iPlugin $plugin, $key, $data, $model = null, $id = null, $language = null)
    {
        
        $functionName = 'set'.ucfirst($model);
        if ($model == null || !method_exists($this, $functionName)) {
            return $this->setGeneric($plugin, $key, $data, $model, $id, $language);
        } else {
            return $this->$functionName($plugin, $key, $data, $model, $id, $language);
        }
    }
    /**
     * 
     * @param iPlugin $plugin
     * @param string $key
     * @param mixed data Default value to return if key could not be found.
     * @param string $model Optional model name to which the data was attached.
     * @param int $id Optional id of the model instance to which the data was attached.
     * @param string $language Optional language identifier used for storing the setting.
     * 
     * @return boolean
     */
    protected function setGeneric(iPlugin $plugin, $key, $data, $model, $id, $language)
    {
        
        if ($id == null && $model != null) {
            throw new \Exception("DbStorage::set cannot store setting for model $model without valid id.");
        }
        $attributes = array(
            'plugin_id' => $plugin->getId(),
            'model'     => $model,
            'model_id'  => $id,
            'key'       => $key
        );
        $record = PluginSetting::model()->findByAttributes($attributes);
        if (is_null($record)) {
            // New setting
            $record = PluginSetting::model()->populateRecord($attributes);
            $record->setIsNewRecord(true);
        } 
        $record->value = json_encode($data);
        $result = $record->save();
               
        return $result;
    }
    
    
    
    /**
     * 
     * @param iPlugin $plugin
     * @param string $key
     * @param mixed data Default value to return if key could not be found.
     * @param string $model Optional model name to which the data was attached.
     * @param int $id Optional id of the model instance to which the data was attached.
     * @param string $language Optional language identifier used for storing the setting.
     * 
     * @return boolean
     */
    protected function setQuestion(iPlugin $plugin, $key, $data, $model, $id, $language)
    {
        $baseAttributes = array(
            'sid',
            'code',
            'qid',
            'gid',
            'sortorder',
            'relevance',
            'questiontype',
            'randomization'
        );
        // Some keys are stored in the actual question table not in the attributes table.
        if (in_array($key, $baseAttributes)) {
            if ($data == '') {
                $data = null;
            }
            $result = $this->setQuestionBase($id, $key, $data);
        } else {
            $attributes = array(
                'qid'  => $id,
                'attribute'       => $key,
                'language' => $language
            );
            $record = QuestionAttribute::model()->findByAttributes($attributes);
            if (is_null($record)) {
                // New setting
                $record = QuestionAttribute::model()->populateRecord($attributes);
                $record->setIsNewRecord(true);
            } 

            // Serialize arrays and objects only for question attributes..
            if (is_array($data) || is_object($data)) {
                $record->value = json_encode($data);           
                $record->serialized = true;
            } else {
                $record->value = $data;
                $record->serialized = false;
            }
            $result = $record->save();
        }
        return $result;
    }
    
        /**
         * Sets a field from the question table.
         * @param int $questionId
         * @param string $key
         * @param mixed $data Data to be saved.
         */
    protected function setQuestionBase($questionId, $key, $data)
    {
        $question = Question::model()->findByPk($questionId);
        if ($question != null && $question->setAttribute($key, $data)) {
            return $question->save();
        }
        return false;
    }
}
