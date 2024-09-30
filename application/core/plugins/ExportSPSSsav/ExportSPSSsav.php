<?php
class ExportSPSSsav extends \LimeSurvey\PluginManager\PluginBase
{

    protected $storage = 'DbStorage';

    static protected $description = 'Core: Export survey results to an SPSS sav file';
    static protected $name = 'SPSS Export';

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
        'spssfileversion' => array(
            'type' => 'select',
            'label' => 'Export for SPSS',
            'options' => array('16' => 'versions 14 and above', '13'  => 'version 13 and below (limited string length)'),
            'default' => '16',
            'submitonchange'=> false
            )
        );

    public function listExportOptions()
    {
        $event = $this->getEvent();
        $type = $event->get('type');

        switch ($type) {
            case 'spsssav':
                $event->set('label', gT("SPSS (.sav)"));
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
        $exports['spsssav'] = get_class($this);
        $event->set('exportplugins', $exports);
    }

    /**
     * Returns the required IWriter
     */
    public function newExport()
    {
        $event = $this->getEvent();

        $pluginsettings = $this->getPluginSettings(true);
        $writer = new SPSSWriter($pluginsettings);
        $event->set('writer', $writer);
    }
}
