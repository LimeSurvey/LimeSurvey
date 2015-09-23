<?php
namespace ls\core\plugins;
use ls\pluginmanager\DbStorage;
use ls\pluginmanager\PluginBase;
use ls\pluginmanager\PluginEvent;

class ExportDefaults extends PluginBase {
    
    protected $storage = DbStorage::class;
       
    public function init()
    {

    }

    /**
     * Registers this export type
     */
    public function eventListExportPlugins(PluginEvent $event)
    {
        /**
         * @todo Remove the override, since we have no structured order of loading plugins it doesn't make sense.
         */
        // Yes we overwrite existing classes if available
        $exports = array_merge($event->get('exportplugins', []), [
            'csv' => [
                'label' => gT("CSV"),
                'class' => __CLASS__,
                'default' => true
            ],
            'xls' => [
                'class' => __CLASS__,
                'label' => gT("Microsoft Excel"),
                'default' => true
            ],
            'doc' => [
                'class' => __CLASS__,
                'label' => gT("Microsoft Word"),
                'default' => true,
                /**
                 * @todo This needs to be removed, assumption about the DOM are dangerous and can break the plugin if any changes are made to the layout.
                 */
                'onclick' => 'document.getElementById("answers-long").checked=true;document.getElementById("answers-short").disabled=true;'
            ],
            'pdf' => [
                'class' => __CLASS__,
                'label' => gT("PDF"),
                'default' => true
            ],
            'json' => [
                'class' => __CLASS__,
                'label' => gT("JSON"),
                'default' => true
            ],
            'html' => [
                'class' => __CLASS__,
                'label' => gT("HTML"),
                'default' => true
            ],
        ]);

        $event->set('exportplugins', $exports);
    }
    
    /**
     * Returns the required IWriter
     */
    public function eventNewExport(PluginEvent $event)
    {
        $type = $event->get('type');

        switch ($type) {
            case "doc":
                $writer = \DocWriter::class;
                break;
            case "xls":
                $writer = \ExcelWriter::class;
                break;
            case "pdf":
                $writer = \PdfWriter::class;
                break;
            case "html":
                $writer = \HtmlWriter::class;
                break;
            case "json":
                $writer = \JsonWriter::class;
                break;
            case "csv":
            default:
                $writer = \CsvWriter::class;
                break;
        }

        $event->set('writer', $writer);
    }
}