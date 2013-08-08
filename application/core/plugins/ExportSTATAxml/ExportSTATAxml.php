<?php
error_reporting(E_ALL);
ini_set('display_errors', True);

class ExportSTATAxml extends PluginBase {
    
    protected $storage = 'DbStorage';
       
    static protected $description = 'Core: Export survey results to a STATA xml file';
    static protected $name = 'STATA Export';
    
    public function __construct(PluginManager $manager, $id) {
        parent::__construct($manager, $id);
        
        /**
         * Here you should handle subscribing to the events your plugin will handle
         */
        $this->subscribe('listExportPlugins');
        $this->subscribe('newExport');
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
        $type = $event->get('type');
                
        $writer = new STATAxmlWriter();
        $event->set('writer', $writer);
    }
}