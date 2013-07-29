<?php
class ExportR extends PluginBase {
    
    protected $storage = 'DbStorage';
       
    static protected $description = 'Core: R-export';
    static protected $name = 'Export results to R';
    
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
        $exports['rsyntax'] = get_class();
        $exports['rdata'] = get_class();
        $event->set('exportplugins', $exports);
    }
    
    /**
     * Returns the required IWriter
     */
    public function newExport()
    {
        $event = $this->getEvent();
        $type = $event->get('type');
                
        switch ($type) {
            case 'rsyntax':
                $writer = new RSyntaxWriter();
                break;
            
            case 'rdata':
            default:
                $writer = new RDataWriter();
                break;
        }
        
        $event->set('writer', $writer);
    }
}