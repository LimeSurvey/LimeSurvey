<?php
/**
 * Example plugin that add a simple function
 */
class demoAddEmFunction extends PluginBase
{
    static protected $description = 'Demo: adding a new function';
    static protected $name = 'demoAddEmFunction';

    public function init()
    {
        $this->subscribe('newValidFunctions');
    }

    public function newValidFunctions()
    {
        Yii::setPathOfAlias(get_class($this),dirname(__FILE__));
        //~ Yii::import(get_class($this).".exampleFunctions");
        $newFunctions = array(
            'doHtmlList' => array(
                '\demoAddEmFunction\exampleFunctions::doHtmlList', // PHP function, no need Class if function is directly added here
                'doHtmlList', // Javascript function
                $this->gT("Show an html list with elements"), // Description for admin
                'string doHtmlList(arg1, arg2, ... argN)', // Extra description
                'https://www.limesurvey.org', // Help url
                -1, // Number of argument , here any number, no forced
            ),
            'sayHello' => array(
                '\demoAddEmFunction\exampleFunctions::sayHello', // PHP function, no need Class if function is directly added here
                'sayHello', // Javascript function
                $this->gT("Say hello"), // Description for admin
                'string sayHello(string)', // Extra description
                'https://www.limesurvey.org', // Help url
                1, // Number of argument (time to make a good description of EM …
            ),
        );
        $this->getEvent()->append('functions', $newFunctions);
        /* For the js file : maybe add a helper on parent ? */
        $newPackage = array(
            'demoAddEmFunction'=> array(
                //~ 'devBaseUrl' => '/plugins/'.get_class($this)."/assets",
                'basePath' => get_class($this).".assets",
                // Can be an external url with baseUrl
                'js' => array(get_class($this).".js"),
            ),
        );
        $this->getEvent()->append('packages', $newPackage);
    }

    /**
     * If we use beforeControllerAction to register file and package :
     * we don't need the event again when loading em core from $_SESSION
     *
     */
    public function beforeControllerAction() {
        Yii::setPathOfAlias('demoAddEmFunction',dirname(__FILE__));
        $newPackage = array(
            'demoAddEmFunction'=> array(
                'basePath' => "demoAddEmFunction.assets",
                'js' => array("demoAddEmFunction.js"),
            ),
        );
        $this->getEvent()->append('packages', $newPackage);
    }
}
