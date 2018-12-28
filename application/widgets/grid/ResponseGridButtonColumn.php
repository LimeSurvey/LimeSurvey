<?php

class ResponseGridButtonColumn extends TbButtonColumn
{
    /* @var Survey $survey */
    public $survey;

    public function init()
    {
        $event = new PluginEvent('modifyResponseGridButtons');
        $event->set('survey', $this->survey);
        $event->set('buttons', $this->buttons);

        Yii::app()->getPluginManager()->dispatchEvent($event);

        $this->buttons = $event->get('buttons');
        $this->template = implode(' ', array_map(function($item) {return '{' . $item . '}';}, array_keys($this->buttons)));

        parent::init();
    }
}
