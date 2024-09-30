<?php

class ExportR extends \LimeSurvey\PluginManager\PluginBase
{
    
    protected $storage = 'DbStorage';

    protected static $description = 'Core: R-export';
    protected static $name = 'Export results to R';

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
    
    public function listExportOptions()
    {
        $event = $this->getEvent();
        $type = $event->get('type');
        
        switch ($type) {
            case 'rsyntax':
                $tooltip = CHtml::openTag('ol');
                $tooltip .= CHtml::tag('li', array(), gT("Download the data and the syntax file."));
                $tooltip .= CHtml::tag('li', array(), gT("Save both of them on the R working directory (use getwd() and setwd() on the R command window to get and set it)"));
                $tooltip .= CHtml::tag('li', array(), gT("digit:       source(\"filename\", encoding = \"UTF-8\")        on the R command window, replace filename with the actual filename"));
                $tooltip .= CHtml::closeTag('ol');
                $tooltip .= CHtml::tag('br');
                $tooltip .= gT("Your data should be imported now, the data.frame is named \"data\", the variable.labels are attributes of data (\"attributes(data)\$variable.labels\"), like for foreign:read.spss.");
                $event->set('tooltip', $tooltip);
                $event->set('label', gT("R (syntax file)"));
                break;
            
            case 'rdata':
            default:
                $event->set('label', gT("R (data file)"));
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
        $exports['rsyntax'] = get_class($this);
        $exports['rdata'] = get_class($this);
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
