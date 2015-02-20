<?php
namespace ls\core\plugins;
use ls\pluginmanager\PluginBase;
use \ls\pluginmanager\PluginEvent;
use \Yii;
use \CHtml;

class AuthDb extends PluginBase implements \ls\pluginmanager\iAuthenticationPlugin
{
    use \ls\pluginmanager\InternalUserDbTrait;
    protected $storage = 'DbStorage';
    protected $_onepass = null;

    // Now the export part:
    public function eventListExportOptions(PluginEvent $event)
    {
        $type = $event->get('type');
        
        switch ($type) {
            case 'csv':
                $event->set('label', gT("CSV"));
                $event->set('default', true);
                break;
            case 'xls':
                $label = gT("Microsoft Excel");
                if (!function_exists('iconv')) {
                    $label .= '<font class="warningtitle">'.gT("(Iconv Library not installed)").'</font>';
                }
                $event->set('label', $label);
                break;
            case 'doc':
                $event->set('label', gT("Microsoft Word"));
                $event->set('onclick', 'document.getElementById("answers-long").checked=true;document.getElementById("answers-short").disabled=true;');
                break;
            case 'pdf':
                $event->set('label', gT("PDF"));
                break;
            case 'html':
                $event->set('label', gT("HTML"));
                break;
            case 'json':    // Not in the interface, only for RPC
            default:
                break;
        }
    }

    /**
     * Registers this export type
     */
    public function eventListExportPlugins(PluginEvent $event)
    {
        $event = $this->getEvent();
        $exports = $event->get('exportplugins');

        // Yes we overwrite existing classes if available
        $className = get_class();
        $exports['csv'] = $className;
        $exports['xls'] = $className;
        $exports['pdf'] = $className;
        $exports['html'] = $className;
        $exports['json'] = $className;
        $exports['doc'] = $className;

        $event->set('exportplugins', $exports);
    }

    /**
     * Returns the required IWriter
     */
    public function eventNewExport()
    {
        $event = $this->getEvent();
        $type = $event->get('type');

        switch ($type) {
            case "doc":
                $writer = new DocWriter();
                break;
            case "xls":
                $writer = new ExcelWriter();
                break;
            case "pdf":
                $writer = new PdfWriter();
                break;
            case "html":
                $writer = new HtmlWriter();
                break;
            case "json":
                $writer = new JsonWriter();
                break;
            case "csv":
            default:
                $writer = new CsvWriter();
                break;
        }

        $event->set('writer', $writer);
    }
    
    /**
     * This function performs username password configuration.
     * @param \CHttpRequest $request
     */
    public function authenticate(\CHttpRequest $request) {
        if ($request->isPostRequest) {
            $username = $request->getParam('username');
            $password = $request->getParam('password');
            $user = \User::model()->findByAttributes(['users_name' => $username]);
            if (isset($user) && $user->validatePassword($password)) {
                return $user;
            }
        }
    }
    
    /**
     * @return boolean True if all users for this authenticator can be listed.
     */
    public function enumerable() {
        return true;
    }
    
    
    
    
}
