<?php

class ExportSTATAxml extends \LimeSurvey\PluginManager\PluginBase
{
    
    protected $storage = 'DbStorage';
       
    protected static $description = 'Core: Export survey results to a STATA xml file';
    protected static $name = 'STATA Export';

    /** @inheritdoc this plugin didn't have any public method */
    public $allowedPublicMethods = array();

    public function init()
    {
        
        /**
         * Here you should handle subscribing to the events your plugin will handle
         */
        $this->subscribe('listExportPlugins');
        $this->subscribe('listExportOptions');
        $this->subscribe('newExport');
    }
    
    protected $settings = array(
        'statafileversion' => array(
            'type' => 'select',
            'label' => 'Export for Stata',
            'options' => array('113' => 'version 8 through 12', '117'  => 'version 13 and up'),
            'default' => '113',
            'submitonchange' => false
            )
        );

    public function listExportOptions()
    {
        $event = $this->getEvent();
        $type = $event->get('type');
        
        switch ($type) {
            case 'stataxml':
                $event->set('label', gT("STATA (.xml)"));
                $event->set('onclick', '
				 document.getElementById("answers-short").checked=true;
				 document.getElementById("answers-long").disabled=true;
                     document.getElementById("converty").checked=true;
                     document.getElementById("convertn").checked=true;
                     document.getElementById("convertnto").value=0;
                     document.getElementById("convertyto").value=1;
                     document.getElementById("headstyle-code").disabled=true;
                     document.getElementById("headstyle-abbreviated").disabled=true;
                     document.getElementById("headstyle-full").checked=true;
                     document.getElementById("headstyle-codetext").disabled=true;
				 ');
                break;

            default:
                break;
        }
    }
    
    /**
     * Registers this export type
     */
    public function listExportPlugins()
    {
        $event = $this->getEvent();
        $exports = $event->get('exportplugins');
        
        // Yes we overwrite existing classes if available
        $exports['stataxml'] = get_class();
        $event->set('exportplugins', $exports);
    }
    
    /**
     * Returns the required IWriter
     */
    public function newExport()
    {
        $event = $this->getEvent();

        $pluginsettings = $this->getPluginSettings(true);
        $writer = new STATAxmlWriter($pluginsettings);
        $event->set('writer', $writer);
    }
}
