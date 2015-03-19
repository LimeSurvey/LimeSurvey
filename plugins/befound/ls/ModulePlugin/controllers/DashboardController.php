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
//            var_dump(App()->basePath);
//            var_dump(App()->user->name);
//            var_dump(App()->authManager);
                   
//            var_dump($this->module->user);
//            echo 'nice';
            $this->render('index');
        }
        
        public function actionView() {
            
        }
        
        public function actionLogin() {
            $model = new \befound\ls\ModulePlugin\models\LoginForm('login');
            $form = new \TbForm('views.loginForm', $model);
            if ($form->submitted() && $model->validate()) {
                echo 'ok';
            }
            $this->render('login', ['form' => $form]);
        }
    }
