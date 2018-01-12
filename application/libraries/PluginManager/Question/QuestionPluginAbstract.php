<?php
namespace LimeSurvey\PluginManager;
abstract class QuestionPluginAbstract extends PluginBase implements iQuestionPlugin
{
           
    public function registerEvents()
    {
        $this->subscribe('getAvailablePlugins');
    }
    
    /**
     * Should add it's name to the questionPlugins array in the event
     * 
     * By reading this array, LimeSurvey knows which question plugins are 
     * available
     * 
     * @param PluginEvent $event
     */
    public function getAvailablePlugins(PluginEvent $event)
    {
        $event->questionPlugins[] = get_class($this);
    }
    
    /**
     * Handles loading a question, populating it with the given data
     * 
     * This will return a clean copy of the plugin, populated with the values
     * from the data array
     * 
     * @param array $data
     * @return \self
     */
    public function loadQuestion($data)
    {
        $question = new self($this->pluginManager, $this->id);
        $question->populate($data);
        
        $question->isQuestion(true); // Signal this is not the plugin, but a question object
        
        return $question;
    }
    
    public function populate($data)
    {
        foreach ($data as $key => $value) {
            $setter = 'set'.ucfirst($key);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            } else {
                $this->$key = $value;
            }
        }
    }
}