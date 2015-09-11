<?php
class EventTest extends PluginBase
{

    protected $storage = 'DbStorage';
    static protected $description = 'EventTest';


    public function __construct(PluginManager $manager, $id)
    {
        parent::__construct($manager, $id);


        /**
         * Here you should handle subscribing to the events your plugin will handle
         */
        $this->subscribe('modifyStartpage');
    }

    /**
     * change the view for admin homepage
     */

    public function modifyStartpage()
    {

        Yii::setPathOfAlias('EventTest', dirname(__FILE__));

        $test = Yii::getPathOfAlias('EventTest');

        var_dump($test);
        $sViewPath = '/../../plugins/xolair/assets/views';
        $sViewName = 'myView';

        $event = $this->getEvent();
        $event->set('viewName', $sViewName);
        $event->set('viewUrl', $sViewPath);


    }
}
