<?php

class QuestionGroupDeleteEventsPlugin extends PluginBase
{
    protected static $description = 'Dummy plugin for testing the question group delete events';
    protected static $name = 'QuestionGroupDeleteEventsPlugin';

    /** @var array<string, int[]> Number of questions still in the group, indexed by event name, one entry per dispatch */
    public $questionCounts = [];

    /**
     * @return void
     */
    public function init()
    {
        $this->subscribe('beforeQuestionGroupDelete');
        $this->subscribe('afterQuestionGroupDelete');
    }

    /**
     * @return void
     */
    public function beforeQuestionGroupDelete()
    {
        $this->recordQuestionCount();
    }

    /**
     * @return void
     */
    public function afterQuestionGroupDelete()
    {
        $this->recordQuestionCount();
    }

    /**
     * Record how many questions the deleted group still has when the current event is dispatched.
     *
     * @return void
     */
    private function recordQuestionCount()
    {
        $event = $this->getEvent();
        $gid = $event->get('model')->gid;
        $this->questionCounts[$event->getEventName()][] = (int) Question::model()->countByAttributes(['gid' => $gid]);
    }
}
