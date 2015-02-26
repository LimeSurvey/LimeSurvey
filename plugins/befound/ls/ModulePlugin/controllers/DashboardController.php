<?php
    namespace befound\ls\ModulePlugin\controllers;
    
    class DashboardController extends Controller {
        public function __construct($id, $module = null) {
            parent::__construct($id, $module);
//            die('ok');
        }
        public function accessRules() {
            return array_merge([
                ['allow', 'users' => ['@']],
                ['allow', 'actions' => ['login']]
            ], parent::accessRules());
        }
        public function actionIndex() {
            var_dump(App()->basePath);
            var_dump(App()->user);
            var_dump(App()->user->name);
//            var_dump(App()->authManager);
                   
//            var_dump($this->module->user);
            echo 'nice';
        }
        
        public function actionView() {
            
        }
        
        public function actionLogin() {
            $model = new \befound\ls\ModulePlugin\models\LoginForm;
            $form = new \TbForm('views.loginForm', $model);
            $this->render('login', ['form' => $form]);
        }
    }
