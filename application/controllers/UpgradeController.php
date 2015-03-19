<?php
namespace ls\controllers;

/**
 * This class will handle limesurvey upgrades.
 */
class UpgradeController extends Controller 
{
    public $layout = 'minimal';
    public $defaultAction = 'notice';
    
    protected function getFileCached($url, $params) {
        $key = md5(json_encode([$url, $params]));
        $path = App()->runtimePath . '/upgrade';
        if (!is_dir($path)) {
            mkdir($path);
        }
        $file = "$path/$key";
        
        if (!file_exists($file)) {
            $handle = fopen($file, 'w');
            $client = new \GuzzleHttp\Client();
            $res = $client->get($url, $params);
            stream_copy_to_stream($res->getBody()->detach(), $handle);
            fclose($handle);
        }
        
        return fopen($file, 'r');
    }
    
    protected function getFileContentsCached($url, $params) {
        return stream_get_contents($this->getFileCached($url, $params));
    }
    public function accessRules() {
        return array_merge([
            [
                'allow', 
                'actions' => ['notice', 'database'],
            ],
        ], parent::accessRules());
    }
    
    public function actionIndex() {
        $this->layout = 'main';
        $versions = json_decode($this->getFileContentsCached(App()->params['updateServer'] . 'list', ['query' => ['from' => App()->params['version']]]), true);
        return $this->render('index', ['versions' => $versions]);
    }
    
    /**
     * The main upgrade screen.
     * @param type $version
     * @param type $check
     */
    public function actionInfo($version) {
        $this->layout = 'main';
        $versions = json_decode($this->getFileContentsCached(App()->params['updateServer'] . 'list', ['query' => ['from' => App()->params['version']]]), true);
        if (!in_array($version, $versions)) {
            throw new \CHttpException(404, "Version not found.");
        }
        $this->render('info', ['version' => $version]);
    }
    public function actionNotice() 
    {
        // Disable any weblogroutes.
        foreach (App()->log->routes as $route) {
            if ($route instanceof \CWebLogRoute) {
                $route->enabled = false;
            }
        }
        $this->render('notice');
    }
    
    /** 
     * This function applies any remaning migrations. 
     * @return type
     */
    public function actionDatabase($upgrade) 
    {
        $migrations  = App()->migrationManager->newMigrations;
        if (empty($migrations)) {
            App()->maintenanceMode = false;
            $this->redirect(['users/login']);
        }
        switch ($upgrade) {
            case 'run': 
                
                foreach ($migrations as $i => $migration) {
                    $result = App()->migrationManager->migrateUp($migration);
                    if (!$result) {
                        echo "One migration failed, aborting other migrations.";
                    }
                }

                if ($result) {
                    App()->maintenanceMode = false;
                }
                break;
            case 'abort': 
                App()->maintenanceMode = false;
                $this->redirect(['users/login']);
                break;
            default:
                // Disable any weblogroutes.
                foreach (App()->log->routes as $route) {
                    if ($route instanceof \CWebLogRoute) {
                        $route->enabled = false;
                    }
                }
                $this->render('database');
        }
    }
    
    protected function runPreCheck($version)
    {
        $preUpdate = new \SamIT\AutoUpdater\Executor\PreUpdate([
            'basePath' => dirname(App()->basePath)
        ]);
        $json = $this->getFileContentsCached(App()->params['updateServer'] . 'prepare', [
            'query' => ['from' => App()->params['version'], 'to' => $version]
        ]);
        $preUpdate->loadFromString($json, null);
        return ['success' => $preUpdate->run(), 'messages' => $preUpdate->getMessages(), 'changeLog' => $preUpdate->getChangeLog()];
    }
    public function actionPreCheck($version) {
        
        $result = $this->runPreCheck($version);
        $result['step'] = 'precheck';
        header('Content-type: application/json');
        echo json_encode($result, JSON_PRETTY_PRINT);
    }
    public function actionChangeLog($version) {
        header('Content-type: application/json');
        
        $json = $this->getFileCached(App()->params['updateServer'] . 'prepare', [
            'query' => ['from' => App()->params['version'], 'to' => $version]
        ]);
        $preUpdate->loadFromString($json);
        echo json_encode($result, JSON_PRETTY_PRINT);
    }
    public function actionDownload($version) 
    {
        $result = $this->getFileCached(App()->params['updateServer'] . 'download', [
            'query' => ['from' => App()->params['version'], 'to' => $version]
        ]);
    if (is_resource($result)) {
        header('Content-type: application/json');
        echo json_encode([
            'step' => 'download',
            'success' => true,
            'messages' => [
                'Downloaded file.',
                'File size: ' . fstat($result)['size']
            ]
        ], JSON_PRETTY_PRINT);
        } else {
            echo json_encode([
                'step' => 'download',
                'success' => false,
                'messages' => [
                    'Error during download.'
                ]
            ], JSON_PRETTY_PRINT);
        }
    }
    
    
    public function actionExecute($version) 
    {
        $result = $this->getFileCached(App()->params['updateServer'] . 'download', [
            'query' => ['from' => App()->params['version'], 'to' => $version]
        ]);
        $update = new \SamIT\AutoUpdater\Executor\Update([
            'basePath' => dirname(App()->basePath)
        ]);
        $update->loadFromFile(stream_get_meta_data($result)['uri'], null);
        if ($update->run()) {
        header('Content-type: application/json');
        echo json_encode([
            'step' => 'execute',
            'success' => true,
            'messages' => $update->getMessages()
        ], JSON_PRETTY_PRINT);
        } else {
            echo json_encode([
                'step' => 'execute',
                'success' => false,
                'messages' => $update->getMessages()
            ], JSON_PRETTY_PRINT);
        }
    }
    
    public function filters()
    {
        return array_merge(parent::filters(), ['accessControl']);
    }
    
    public function init() {
        parent::init();
        if (App()->request->getParam('upgrade') !== null) {
            $this->defaultAction = 'database';
        }
    }
   
    
    
}