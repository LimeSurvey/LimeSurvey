<?php
namespace ls\controllers;

/**
 * This class will handle limesurvey upgrades.
 */
class UpgradeController extends Controller 
{
    public $layout = 'minimal';
    public $defaultAction = 'notice';
    
    public function accessRules() {
        return array_merge([
            [
                'allow', 
                'actions' => ['notice', 'database'],
            ],
        ], parent::accessRules());
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
    
    public function actionStart() 
    {
        
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